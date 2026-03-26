<?php
/**
 * LeadNest Uninstall
 *
 * Removes all plugin data on uninstall (not deactivation).
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

global $wpdb;

// Drop all plugin tables
$tables = array(
    'leadnest_sessions',
    'leadnest_chats',
    'leadnest_leads',
    'leadnest_knowledge',
    'leadnest_qa',
    'leadnest_missed_questions',
    'leadnest_sites',
    'leadnest_bookings',
    'leadnest_availability',
    'leadnest_licenses',
);

foreach ( $tables as $table ) {
    $wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}{$table}" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
}

// Remove plugin options
delete_option( 'leadnest_options' );
delete_option( 'leadnest_db_version' );

// Clear any cached data
wp_cache_flush();
