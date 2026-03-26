<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class LeadNest_GCal {

    const TOKEN_URL = 'https://oauth2.googleapis.com/token';
    const AUTH_URL  = 'https://accounts.google.com/o/oauth2/v2/auth';
    const API_BASE  = 'https://www.googleapis.com/calendar/v3';

    /**
     * Get the OAuth redirect URI.
     */
    public static function get_redirect_uri() {
        return admin_url( 'admin.php?page=leadnest-bookings&gcal_callback=1' );
    }

    /**
     * Build the OAuth authorization URL.
     */
    public static function get_auth_url() {
        $options = LeadNest_DB::get_options();

        if ( empty( $options['gcal_client_id'] ) ) {
            return '';
        }

        $params = array(
            'client_id'     => $options['gcal_client_id'],
            'redirect_uri'  => self::get_redirect_uri(),
            'response_type' => 'code',
            'scope'         => 'https://www.googleapis.com/auth/calendar.events',
            'access_type'   => 'offline',
            'prompt'        => 'consent',
            'state'         => wp_create_nonce( 'leadnest_gcal_auth' ),
        );

        return self::AUTH_URL . '?' . http_build_query( $params );
    }

    /**
     * Exchange authorization code for tokens.
     *
     * @param string $code
     * @return array|WP_Error
     */
    public static function exchange_code( $code ) {
        $options = LeadNest_DB::get_options();

        $response = wp_remote_post( self::TOKEN_URL, array(
            'timeout' => 30,
            'body'    => array(
                'code'          => $code,
                'client_id'     => $options['gcal_client_id'],
                'client_secret' => $options['gcal_client_secret'],
                'redirect_uri'  => self::get_redirect_uri(),
                'grant_type'    => 'authorization_code',
            ),
        ) );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $data = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( ! empty( $data['refresh_token'] ) ) {
            $options['gcal_refresh_token'] = sanitize_text_field( $data['refresh_token'] );
            update_option( 'leadnest_options', $options );
        }

        if ( ! empty( $data['access_token'] ) ) {
            set_transient( 'leadnest_gcal_access_token', $data['access_token'], ( $data['expires_in'] ?? 3600 ) - 60 );
            return $data;
        }

        $error_msg = isset( $data['error_description'] ) ? $data['error_description'] : 'Unknown OAuth error.';
        return new WP_Error( 'gcal_oauth_error', $error_msg );
    }

    /**
     * Get a valid access token (refresh if needed).
     *
     * @return string|WP_Error
     */
    public static function get_access_token() {
        $token = get_transient( 'leadnest_gcal_access_token' );
        if ( $token ) {
            return $token;
        }

        $options = LeadNest_DB::get_options();
        if ( empty( $options['gcal_refresh_token'] ) || empty( $options['gcal_client_id'] ) || empty( $options['gcal_client_secret'] ) ) {
            return new WP_Error( 'gcal_not_configured', 'Google Calendar is not configured.' );
        }

        $response = wp_remote_post( self::TOKEN_URL, array(
            'timeout' => 15,
            'body'    => array(
                'refresh_token' => $options['gcal_refresh_token'],
                'client_id'     => $options['gcal_client_id'],
                'client_secret' => $options['gcal_client_secret'],
                'grant_type'    => 'refresh_token',
            ),
        ) );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $data = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( ! empty( $data['access_token'] ) ) {
            set_transient( 'leadnest_gcal_access_token', $data['access_token'], ( $data['expires_in'] ?? 3600 ) - 60 );
            return $data['access_token'];
        }

        return new WP_Error( 'gcal_refresh_failed', 'Failed to refresh Google Calendar token.' );
    }

    /**
     * Check if Google Calendar is connected.
     */
    public static function is_connected() {
        $options = LeadNest_DB::get_options();
        return ! empty( $options['gcal_refresh_token'] ) && ! empty( $options['gcal_client_id'] );
    }

    /**
     * Create a Google Calendar event for a booking.
     *
     * @param object $booking  DB row from leadnest_bookings
     * @param array  $options  Plugin options
     * @return string|WP_Error  Google event ID or error
     */
    public static function create_event( $booking, $options = null ) {
        if ( ! $options ) {
            $options = LeadNest_DB::get_options();
        }

        $access_token = self::get_access_token();
        if ( is_wp_error( $access_token ) ) {
            return $access_token;
        }

        $calendar_id = ! empty( $options['gcal_calendar_id'] ) ? $options['gcal_calendar_id'] : 'primary';
        $duration    = absint( $options['booking_duration'] ) ?: 60;

        $start_dt = $booking->booking_date . 'T' . $booking->booking_time;
        $end_ts   = strtotime( $start_dt ) + $duration * 60;
        $end_dt   = gmdate( 'Y-m-d\TH:i:s', $end_ts );

        $event = array(
            'summary'     => 'LeadNest Booking — ' . ( $booking->name ?: $booking->email ?: 'Visitor' ),
            'description' => sprintf(
                "Name: %s\nEmail: %s\nPhone: %s\nService: %s\nBooked via LeadNest",
                $booking->name, $booking->email, $booking->phone, $booking->service_type
            ),
            'start' => array( 'dateTime' => $start_dt, 'timeZone' => wp_timezone_string() ),
            'end'   => array( 'dateTime' => $end_dt,   'timeZone' => wp_timezone_string() ),
        );

        if ( ! empty( $booking->email ) ) {
            $event['attendees'] = array( array( 'email' => $booking->email ) );
        }

        $response = wp_remote_post(
            self::API_BASE . '/calendars/' . rawurlencode( $calendar_id ) . '/events',
            array(
                'timeout' => 15,
                'headers' => array(
                    'Authorization' => 'Bearer ' . $access_token,
                    'Content-Type'  => 'application/json',
                ),
                'body' => wp_json_encode( $event ),
            )
        );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $data = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( ! empty( $data['id'] ) ) {
            return $data['id'];
        }

        $error_msg = isset( $data['error']['message'] ) ? $data['error']['message'] : 'Failed to create calendar event.';
        return new WP_Error( 'gcal_event_error', $error_msg );
    }

    /**
     * Delete a Google Calendar event.
     */
    public static function delete_event( $event_id ) {
        $options     = LeadNest_DB::get_options();
        $access_token = self::get_access_token();

        if ( is_wp_error( $access_token ) ) {
            return $access_token;
        }

        $calendar_id = ! empty( $options['gcal_calendar_id'] ) ? $options['gcal_calendar_id'] : 'primary';

        wp_remote_request(
            self::API_BASE . '/calendars/' . rawurlencode( $calendar_id ) . '/events/' . rawurlencode( $event_id ),
            array(
                'method'  => 'DELETE',
                'timeout' => 15,
                'headers' => array( 'Authorization' => 'Bearer ' . $access_token ),
            )
        );

        return true;
    }
}
