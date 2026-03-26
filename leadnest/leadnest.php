<?php
/**
 * Plugin Name: LeadNest — AI Lead Capture Chatbot
 * Plugin URI:  https://leadnest.ai
 * Description: AI-powered lead capture chatbot for WordPress. Engage visitors, capture leads, and grow your business automatically.
 * Version:     2.0.0
 * Requires at least: 6.0
 * Requires PHP: 8.0
 * Author:      Furqan
 * Author URI:  https://leadnest.ai
 * License:     GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: leadnest
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Plugin constants
define( 'LEADNEST_VERSION',  '2.0.0' );
define( 'LEADNEST_PATH',     plugin_dir_path( __FILE__ ) );
define( 'LEADNEST_URL',      plugin_dir_url( __FILE__ ) );
define( 'LEADNEST_BASENAME', plugin_basename( __FILE__ ) );

// Activation hook
register_activation_hook( __FILE__, 'leadnest_activate' );
function leadnest_activate() {
    require_once LEADNEST_PATH . 'includes/class-leadnest-db.php';
    LeadNest_DB::install();
    flush_rewrite_rules();
}

// Deactivation hook
register_deactivation_hook( __FILE__, 'leadnest_deactivate' );
function leadnest_deactivate() {
    wp_clear_scheduled_hook( 'leadnest_auto_recrawl' );
    wp_clear_scheduled_hook( 'leadnest_send_reminders' );
    flush_rewrite_rules();
}

// Load includes
function leadnest_load_includes() {
    require_once LEADNEST_PATH . 'includes/class-leadnest-db.php';
    require_once LEADNEST_PATH . 'includes/class-leadnest-api.php';
    require_once LEADNEST_PATH . 'includes/class-leadnest-admin.php';
    require_once LEADNEST_PATH . 'includes/class-leadnest-gcal.php';
    require_once LEADNEST_PATH . 'includes/class-leadnest-sms.php';
    require_once LEADNEST_PATH . 'includes/class-leadnest-license.php';
}
add_action( 'plugins_loaded', 'leadnest_load_includes' );

// Init REST API
add_action( 'rest_api_init', function () {
    if ( class_exists( 'LeadNest_API' ) ) {
        $api = new LeadNest_API();
        $api->register_routes();
        $api->add_cors_headers();
    }
} );

// Init admin
add_action( 'plugins_loaded', function () {
    if ( is_admin() && class_exists( 'LeadNest_Admin' ) ) {
        $admin = new LeadNest_Admin();
        $admin->init();
    }
} );

// WP Cron: auto-recrawl handler
add_action( 'leadnest_auto_recrawl', 'leadnest_run_auto_recrawl' );
function leadnest_run_auto_recrawl() {
    $options = get_option( 'leadnest_options', array() );
    $url     = isset( $options['crawl_url'] ) ? $options['crawl_url'] : '';

    if ( empty( $url ) || ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
        return;
    }

    require_once LEADNEST_PATH . 'includes/class-leadnest-crawler.php';

    $site_key = isset( $options['site_key'] ) ? $options['site_key'] : '';
    $crawler  = new LeadNest_Crawler();
    $crawler->crawl_site( $url, $site_key, 20 );
}

// WP Cron: SMS booking reminders
add_action( 'leadnest_send_reminders', 'leadnest_run_send_reminders' );
function leadnest_run_send_reminders() {
    $options = get_option( 'leadnest_options', array() );
    if ( empty( $options['sms_reminder_enabled'] ) ) {
        return;
    }

    if ( ! class_exists( 'LeadNest_SMS' ) || ! LeadNest_SMS::is_configured() ) {
        return;
    }

    global $wpdb;

    $hours_ahead = absint( $options['sms_reminder_hours'] ?? 24 );
    $from_time   = current_time( 'mysql' );
    $to_time     = gmdate( 'Y-m-d H:i:s', strtotime( '+' . $hours_ahead . ' hours', strtotime( $from_time ) ) );

    $bookings = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}leadnest_bookings
             WHERE status IN ('pending','confirmed')
               AND reminder_sent = 0
               AND phone != ''
               AND CONCAT(booking_date, ' ', booking_time) BETWEEN %s AND %s
             LIMIT 20",
            $from_time,
            $to_time
        )
    );

    foreach ( $bookings as $booking ) {
        $result = LeadNest_SMS::send_booking_reminder( $booking );
        if ( ! is_wp_error( $result ) ) {
            $wpdb->update(
                $wpdb->prefix . 'leadnest_bookings',
                array( 'reminder_sent' => 1 ),
                array( 'id' => $booking->id ),
                array( '%d' ),
                array( '%d' )
            );
        }
    }
}

// Schedule SMS reminders cron on activation
register_activation_hook( __FILE__, 'leadnest_schedule_reminders' );
function leadnest_schedule_reminders() {
    if ( ! wp_next_scheduled( 'leadnest_send_reminders' ) ) {
        wp_schedule_event( time(), 'hourly', 'leadnest_send_reminders' );
    }
}

// WooCommerce conversion tracking
add_action( 'woocommerce_thankyou', 'leadnest_track_conversion', 10, 1 );
function leadnest_track_conversion( $order_id ) {
    if ( ! $order_id ) {
        return;
    }

    $order = wc_get_order( $order_id );
    if ( ! $order || $order->get_meta( '_leadnest_tracked' ) ) {
        return;
    }

    $options = get_option( 'leadnest_options', array() );
    if ( empty( $options['woocommerce_enabled'] ) ) {
        return;
    }

    // Try to find a session token from the cookie/storage
    $session_token = '';
    $site_key      = isset( $options['site_key'] ) ? $options['site_key'] : '';

    // Match by customer email in recent sessions
    $billing_email = $order->get_billing_email();
    if ( ! empty( $billing_email ) ) {
        global $wpdb;

        // Find session linked to a lead with this email
        $session_id = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT l.session_id FROM {$wpdb->prefix}leadnest_leads l
                 WHERE l.email = %s
                 ORDER BY l.created_at DESC LIMIT 1",
                $billing_email
            )
        );

        if ( $session_id ) {
            $revenue = (float) $order->get_total();
            $wpdb->update(
                $wpdb->prefix . 'leadnest_sessions',
                array(
                    'conversion_order_id' => (string) $order_id,
                    'conversion_revenue'  => $revenue,
                    'updated_at'          => current_time( 'mysql' ),
                ),
                array( 'id' => $session_id ),
                array( '%s', '%f', '%s' ),
                array( '%d' )
            );

            $order->update_meta_data( '_leadnest_tracked', '1' );
            $order->save();
        }
    }
}

// Enqueue widget script on frontend
add_action( 'wp_enqueue_scripts', 'leadnest_enqueue_frontend' );
function leadnest_enqueue_frontend() {
    $options  = get_option( 'leadnest_options', LeadNest_DB::get_default_options() );
    $site_key = isset( $options['site_key'] ) ? $options['site_key'] : '';

    // Only enqueue if a site key exists
    if ( empty( $site_key ) ) {
        // Generate one if missing
        $site_key = wp_generate_password( 32, false );
        $options['site_key'] = $site_key;
        update_option( 'leadnest_options', $options );
    }

    $widget_url = rest_url( 'leadnest/v1/widget.js' ) . '?key=' . rawurlencode( $site_key );
    wp_enqueue_script(
        'leadnest-widget',
        $widget_url,
        array(),
        LEADNEST_VERSION,
        true
    );
}

// Enqueue admin assets
add_action( 'admin_enqueue_scripts', 'leadnest_enqueue_admin' );
function leadnest_enqueue_admin( $hook ) {
    $page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';
    if ( strpos( $page, 'leadnest' ) !== 0 ) {
        return;
    }

    wp_enqueue_style(
        'leadnest-admin',
        LEADNEST_URL . 'admin/css/leadnest-admin.css',
        array(),
        LEADNEST_VERSION
    );

    wp_enqueue_media();
    wp_enqueue_style( 'wp-color-picker' );
    wp_enqueue_script( 'wp-color-picker' );

    wp_enqueue_script(
        'leadnest-admin',
        LEADNEST_URL . 'admin/js/leadnest-admin.js',
        array( 'jquery', 'wp-color-picker' ),
        LEADNEST_VERSION,
        true
    );

    wp_localize_script( 'leadnest-admin', 'leadnestAdmin', array(
        'restUrl'  => esc_url_raw( rest_url( 'leadnest/v1/' ) ),
        'ajaxUrl'  => esc_url_raw( admin_url( 'admin-ajax.php' ) ),
        'nonce'    => wp_create_nonce( 'leadnest_admin_nonce' ),
        'restNonce'=> wp_create_nonce( 'wp_rest' ),
        'version'  => LEADNEST_VERSION,
    ) );
}
