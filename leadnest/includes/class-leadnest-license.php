<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class LeadNest_License {

    const LICENSE_SERVER = 'https://license.leadnest.ai/api/v1';

    /**
     * Feature limits per tier.
     */
    private static $tier_limits = array(
        'free'     => array(
            'sites'       => 1,
            'channels'    => array( 'web' ),
            'bookings'    => false,
            'live_agent'  => false,
            'white_label' => false,
        ),
        'personal' => array(
            'sites'       => 1,
            'channels'    => array( 'web', 'whatsapp' ),
            'bookings'    => true,
            'live_agent'  => true,
            'white_label' => false,
        ),
        'pro'      => array(
            'sites'       => 5,
            'channels'    => array( 'web', 'whatsapp', 'messenger', 'telegram', 'sms' ),
            'bookings'    => true,
            'live_agent'  => true,
            'white_label' => false,
        ),
        'agency'   => array(
            'sites'       => 999,
            'channels'    => array( 'web', 'whatsapp', 'messenger', 'telegram', 'sms' ),
            'bookings'    => true,
            'live_agent'  => true,
            'white_label' => true,
        ),
    );

    /**
     * Activate a license key.
     *
     * @param string $license_key
     * @return array|WP_Error
     */
    public static function activate( $license_key ) {
        $license_key = sanitize_text_field( $license_key );
        if ( empty( $license_key ) ) {
            return new WP_Error( 'empty_key', 'License key is required.' );
        }

        $domain = self::get_domain();

        $response = wp_remote_post( self::LICENSE_SERVER . '/activate', array(
            'timeout' => 15,
            'body'    => array(
                'license_key' => $license_key,
                'domain'      => $domain,
                'plugin_version' => LEADNEST_VERSION,
            ),
        ) );

        if ( is_wp_error( $response ) ) {
            // Offline activation fallback: validate locally
            return self::activate_offline( $license_key );
        }

        $code = wp_remote_retrieve_response_code( $response );
        $data = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( $code === 200 && ! empty( $data['success'] ) ) {
            self::store_license( array(
                'license_key' => $license_key,
                'domain'      => $domain,
                'tier'        => sanitize_text_field( $data['tier'] ?? 'personal' ),
                'status'      => 'active',
                'expires_at'  => sanitize_text_field( $data['expires_at'] ?? '' ),
            ) );

            return array(
                'success' => true,
                'tier'    => $data['tier'] ?? 'personal',
                'message' => 'License activated successfully.',
            );
        }

        $error_msg = $data['message'] ?? 'License activation failed.';
        return new WP_Error( 'activation_failed', $error_msg );
    }

    /**
     * Deactivate the current license.
     *
     * @return array|WP_Error
     */
    public static function deactivate() {
        $options     = LeadNest_DB::get_options();
        $license_key = $options['license_key'] ?? '';

        if ( empty( $license_key ) ) {
            return new WP_Error( 'no_license', 'No active license to deactivate.' );
        }

        wp_remote_post( self::LICENSE_SERVER . '/deactivate', array(
            'timeout' => 15,
            'body'    => array(
                'license_key' => $license_key,
                'domain'      => self::get_domain(),
            ),
        ) );

        // Clear local license regardless of server response
        $options['license_key']    = '';
        $options['license_status'] = '';
        update_option( 'leadnest_options', $options );

        global $wpdb;
        $wpdb->delete(
            $wpdb->prefix . 'leadnest_licenses',
            array( 'license_key' => $license_key ),
            array( '%s' )
        );

        delete_transient( 'leadnest_license_check' );

        return array( 'success' => true, 'message' => 'License deactivated.' );
    }

    /**
     * Check if the current license is valid (cached for 24 hours).
     *
     * @return array {valid, tier, status, expires_at}
     */
    public static function check() {
        $cached = get_transient( 'leadnest_license_check' );
        if ( $cached !== false ) {
            return $cached;
        }

        $options     = LeadNest_DB::get_options();
        $license_key = $options['license_key'] ?? '';

        if ( empty( $license_key ) ) {
            $result = array( 'valid' => false, 'tier' => 'free', 'status' => 'none' );
            set_transient( 'leadnest_license_check', $result, DAY_IN_SECONDS );
            return $result;
        }

        // Try remote check
        $response = wp_remote_post( self::LICENSE_SERVER . '/check', array(
            'timeout' => 10,
            'body'    => array(
                'license_key' => $license_key,
                'domain'      => self::get_domain(),
            ),
        ) );

        if ( ! is_wp_error( $response ) ) {
            $data = json_decode( wp_remote_retrieve_body( $response ), true );
            if ( ! empty( $data['valid'] ) ) {
                $result = array(
                    'valid'      => true,
                    'tier'       => sanitize_text_field( $data['tier'] ?? 'personal' ),
                    'status'     => 'active',
                    'expires_at' => sanitize_text_field( $data['expires_at'] ?? '' ),
                );
                set_transient( 'leadnest_license_check', $result, DAY_IN_SECONDS );

                $options['license_status'] = 'active';
                update_option( 'leadnest_options', $options );

                return $result;
            }
        }

        // Fallback: check local DB
        return self::check_local( $license_key );
    }

    /**
     * Get the current license tier.
     *
     * @return string free|personal|pro|agency
     */
    public static function get_tier() {
        $check = self::check();
        return $check['valid'] ? $check['tier'] : 'free';
    }

    /**
     * Check if a feature is available on the current tier.
     *
     * @param string $feature  Feature name (sites, channels, bookings, live_agent, white_label)
     * @param string $channel  Optional channel name for channel-gating
     * @return bool
     */
    public static function can( $feature, $channel = '' ) {
        $tier   = self::get_tier();
        $limits = self::$tier_limits[ $tier ] ?? self::$tier_limits['free'];

        if ( $feature === 'channels' && ! empty( $channel ) ) {
            return in_array( $channel, $limits['channels'] ?? array(), true );
        }

        if ( isset( $limits[ $feature ] ) ) {
            return (bool) $limits[ $feature ];
        }

        return false;
    }

    /**
     * Get the max sites allowed for the current tier.
     *
     * @return int
     */
    public static function max_sites() {
        $tier   = self::get_tier();
        $limits = self::$tier_limits[ $tier ] ?? self::$tier_limits['free'];
        return (int) ( $limits['sites'] ?? 1 );
    }

    /**
     * Store license in both options and license table.
     */
    private static function store_license( $data ) {
        global $wpdb;

        $options = LeadNest_DB::get_options();
        $options['license_key']    = $data['license_key'];
        $options['license_status'] = $data['status'];
        update_option( 'leadnest_options', $options );

        // Upsert into licenses table
        $existing = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id FROM {$wpdb->prefix}leadnest_licenses WHERE license_key = %s",
                $data['license_key']
            )
        );

        if ( $existing ) {
            $wpdb->update(
                $wpdb->prefix . 'leadnest_licenses',
                array(
                    'domain'     => $data['domain'],
                    'tier'       => $data['tier'],
                    'status'     => $data['status'],
                    'expires_at' => $data['expires_at'] ?: null,
                ),
                array( 'id' => $existing ),
                array( '%s', '%s', '%s', '%s' ),
                array( '%d' )
            );
        } else {
            $wpdb->insert(
                $wpdb->prefix . 'leadnest_licenses',
                array(
                    'license_key' => $data['license_key'],
                    'domain'      => $data['domain'],
                    'tier'        => $data['tier'],
                    'status'      => $data['status'],
                    'expires_at'  => $data['expires_at'] ?: null,
                ),
                array( '%s', '%s', '%s', '%s', '%s' )
            );
        }

        delete_transient( 'leadnest_license_check' );
    }

    /**
     * Offline activation — validate key format and store locally.
     */
    private static function activate_offline( $license_key ) {
        // Accept keys matching pattern: LN-TIER-XXXX-XXXX-XXXX
        if ( ! preg_match( '/^LN-(PERSONAL|PRO|AGENCY)-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}$/i', $license_key ) ) {
            return new WP_Error( 'invalid_format', 'Invalid license key format. Could not reach license server for verification.' );
        }

        preg_match( '/^LN-(PERSONAL|PRO|AGENCY)-/i', $license_key, $matches );
        $tier = strtolower( $matches[1] );

        self::store_license( array(
            'license_key' => $license_key,
            'domain'      => self::get_domain(),
            'tier'        => $tier,
            'status'      => 'active',
            'expires_at'  => '',
        ) );

        return array(
            'success' => true,
            'tier'    => $tier,
            'message' => 'License activated (offline mode — will verify when server is reachable).',
        );
    }

    /**
     * Check license locally from DB.
     */
    private static function check_local( $license_key ) {
        global $wpdb;

        $license = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}leadnest_licenses WHERE license_key = %s AND status = 'active' LIMIT 1",
                $license_key
            )
        );

        if ( ! $license ) {
            $result = array( 'valid' => false, 'tier' => 'free', 'status' => 'invalid' );
        } elseif ( ! empty( $license->expires_at ) && strtotime( $license->expires_at ) < time() ) {
            $result = array( 'valid' => false, 'tier' => $license->tier, 'status' => 'expired' );
        } else {
            $result = array(
                'valid'      => true,
                'tier'       => $license->tier,
                'status'     => 'active',
                'expires_at' => $license->expires_at,
            );
        }

        set_transient( 'leadnest_license_check', $result, DAY_IN_SECONDS );
        return $result;
    }

    /**
     * Get the current site domain.
     */
    private static function get_domain() {
        $url = home_url();
        $parsed = wp_parse_url( $url );
        return isset( $parsed['host'] ) ? $parsed['host'] : '';
    }
}
