<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class LeadNest_SMS {

    /**
     * Send an SMS via Twilio.
     *
     * @param string $to      Phone number (E.164 format)
     * @param string $message SMS body text
     * @return true|WP_Error
     */
    public static function send( $to, $message ) {
        $options = LeadNest_DB::get_options();
        $sid     = $options['twilio_sid']   ?? '';
        $token   = $options['twilio_token'] ?? '';
        $from    = $options['twilio_phone'] ?? '';

        if ( empty( $sid ) || empty( $token ) || empty( $from ) ) {
            return new WP_Error( 'twilio_not_configured', 'Twilio credentials are not configured.' );
        }

        // Normalize phone
        $to = preg_replace( '/[^\d+]/', '', $to );
        if ( empty( $to ) ) {
            return new WP_Error( 'invalid_phone', 'Invalid phone number.' );
        }

        $url = 'https://api.twilio.com/2010-04-01/Accounts/' . rawurlencode( $sid ) . '/Messages.json';

        $response = wp_remote_post( $url, array(
            'timeout' => 15,
            'headers' => array(
                'Authorization' => 'Basic ' . base64_encode( $sid . ':' . $token ), // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
            ),
            'body' => array(
                'To'   => $to,
                'From' => $from,
                'Body' => $message,
            ),
        ) );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $code = wp_remote_retrieve_response_code( $response );
        $data = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( $code >= 200 && $code < 300 ) {
            return true;
        }

        $error_msg = isset( $data['message'] ) ? $data['message'] : 'Twilio API error (HTTP ' . $code . ').';
        return new WP_Error( 'twilio_error', $error_msg );
    }

    /**
     * Send a booking reminder SMS.
     *
     * @param object $booking DB row from leadnest_bookings
     * @return true|WP_Error
     */
    public static function send_booking_reminder( $booking ) {
        if ( empty( $booking->phone ) ) {
            return new WP_Error( 'no_phone', 'No phone number for this booking.' );
        }

        $options  = LeadNest_DB::get_options();
        $bot_name = $options['bot_name'] ?: 'LeadNest';
        $time_str = gmdate( 'g:i A', strtotime( $booking->booking_time ) );
        $date_str = gmdate( 'l, M j', strtotime( $booking->booking_date ) );

        $message = sprintf(
            "Reminder from %s: You have an appointment on %s at %s. Reply CANCEL to cancel.",
            $bot_name,
            $date_str,
            $time_str
        );

        return self::send( $booking->phone, $message );
    }

    /**
     * Check if Twilio is configured.
     */
    public static function is_configured() {
        $options = LeadNest_DB::get_options();
        return ! empty( $options['twilio_sid'] ) && ! empty( $options['twilio_token'] ) && ! empty( $options['twilio_phone'] );
    }
}
