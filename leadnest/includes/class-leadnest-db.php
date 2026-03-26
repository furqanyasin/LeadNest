<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class LeadNest_DB {

    /**
     * Create/upgrade all plugin tables using dbDelta.
     */
    public static function install() {
        global $wpdb;
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $charset = $wpdb->get_charset_collate();

        // leadnest_sessions
        $sql_sessions = "CREATE TABLE {$wpdb->prefix}leadnest_sessions (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            session_token VARCHAR(64) NOT NULL DEFAULT '',
            site_key VARCHAR(64) NOT NULL DEFAULT '',
            ip VARCHAR(45) NOT NULL DEFAULT '',
            country VARCHAR(100) NOT NULL DEFAULT '',
            city VARCHAR(100) NOT NULL DEFAULT '',
            device VARCHAR(50) NOT NULL DEFAULT '',
            browser VARCHAR(100) NOT NULL DEFAULT '',
            page_url TEXT,
            user_agent TEXT,
            conversion_order_id VARCHAR(100) NOT NULL DEFAULT '',
            conversion_revenue DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            live_agent TINYINT(1) NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY idx_token (session_token),
            KEY idx_site (site_key)
        ) $charset;";

        // leadnest_chats
        $sql_chats = "CREATE TABLE {$wpdb->prefix}leadnest_chats (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            session_id BIGINT UNSIGNED NOT NULL,
            role ENUM('user','assistant') NOT NULL,
            content LONGTEXT NOT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_session (session_id)
        ) $charset;";

        // leadnest_leads
        $sql_leads = "CREATE TABLE {$wpdb->prefix}leadnest_leads (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            session_id BIGINT UNSIGNED NOT NULL,
            site_key VARCHAR(64) NOT NULL DEFAULT '',
            name VARCHAR(255) NOT NULL DEFAULT '',
            email VARCHAR(255) NOT NULL DEFAULT '',
            phone VARCHAR(50) NOT NULL DEFAULT '',
            need TEXT,
            source_page TEXT,
            status ENUM('new','contacted','qualified','closed') NOT NULL DEFAULT 'new',
            notes TEXT,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_email (email),
            KEY idx_status (status),
            KEY idx_site (site_key)
        ) $charset;";

        // leadnest_knowledge
        $sql_knowledge = "CREATE TABLE {$wpdb->prefix}leadnest_knowledge (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            site_key VARCHAR(64) NOT NULL DEFAULT '',
            url TEXT,
            page_title VARCHAR(500) NOT NULL DEFAULT '',
            content LONGTEXT,
            word_count INT NOT NULL DEFAULT 0,
            active TINYINT(1) NOT NULL DEFAULT 1,
            last_crawled DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_site (site_key)
        ) $charset;";

        // leadnest_qa
        $sql_qa = "CREATE TABLE {$wpdb->prefix}leadnest_qa (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            site_key VARCHAR(64) NOT NULL DEFAULT '',
            question TEXT NOT NULL,
            answer TEXT NOT NULL,
            active TINYINT(1) NOT NULL DEFAULT 1,
            use_count INT NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_site (site_key)
        ) $charset;";

        // leadnest_missed_questions
        $sql_missed = "CREATE TABLE {$wpdb->prefix}leadnest_missed_questions (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            site_key VARCHAR(64) NOT NULL DEFAULT '',
            question TEXT NOT NULL,
            bot_reply TEXT,
            session_id BIGINT UNSIGNED,
            ask_count INT NOT NULL DEFAULT 1,
            resolved TINYINT(1) NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_site (site_key)
        ) $charset;";

        // leadnest_sites
        $sql_sites = "CREATE TABLE {$wpdb->prefix}leadnest_sites (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            site_key VARCHAR(64) NOT NULL DEFAULT '',
            site_name VARCHAR(255),
            site_url TEXT,
            settings LONGTEXT,
            active TINYINT(1) NOT NULL DEFAULT 1,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY site_key (site_key)
        ) $charset;";

        // leadnest_bookings
        $sql_bookings = "CREATE TABLE {$wpdb->prefix}leadnest_bookings (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            site_key VARCHAR(64) NOT NULL DEFAULT '',
            session_id BIGINT UNSIGNED,
            lead_id BIGINT UNSIGNED,
            name VARCHAR(255) NOT NULL DEFAULT '',
            email VARCHAR(255) NOT NULL DEFAULT '',
            phone VARCHAR(50) NOT NULL DEFAULT '',
            service_type VARCHAR(255) NOT NULL DEFAULT '',
            booking_date DATE NOT NULL,
            booking_time TIME NOT NULL,
            duration_mins INT NOT NULL DEFAULT 60,
            status ENUM('pending','confirmed','cancelled','completed') NOT NULL DEFAULT 'pending',
            notes TEXT,
            google_event_id VARCHAR(255) NOT NULL DEFAULT '',
            reminder_sent TINYINT(1) NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_site (site_key),
            KEY idx_date (booking_date)
        ) $charset;";

        // leadnest_availability
        $sql_availability = "CREATE TABLE {$wpdb->prefix}leadnest_availability (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            site_key VARCHAR(64) NOT NULL DEFAULT '',
            day_of_week TINYINT NOT NULL,
            start_time TIME NOT NULL,
            end_time TIME NOT NULL,
            active TINYINT(1) NOT NULL DEFAULT 1,
            PRIMARY KEY (id),
            KEY idx_site (site_key)
        ) $charset;";

        // leadnest_licenses
        $sql_licenses = "CREATE TABLE {$wpdb->prefix}leadnest_licenses (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            license_key VARCHAR(128) NOT NULL DEFAULT '',
            domain VARCHAR(255) NOT NULL DEFAULT '',
            tier ENUM('personal','pro','agency') NOT NULL DEFAULT 'personal',
            status ENUM('active','expired','suspended') NOT NULL DEFAULT 'active',
            sites_allowed INT NOT NULL DEFAULT 1,
            expires_at DATETIME,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY license_key (license_key),
            KEY idx_domain (domain)
        ) $charset;";

        dbDelta( $sql_sessions );
        dbDelta( $sql_chats );
        dbDelta( $sql_leads );
        dbDelta( $sql_knowledge );
        dbDelta( $sql_qa );
        dbDelta( $sql_missed );
        dbDelta( $sql_sites );
        dbDelta( $sql_bookings );
        dbDelta( $sql_availability );
        dbDelta( $sql_licenses );

        update_option( 'leadnest_db_version', LEADNEST_VERSION );

        // Ensure site key exists in options
        $options = get_option( 'leadnest_options', array() );
        if ( empty( $options['site_key'] ) ) {
            $options = array_merge( self::get_default_options(), $options );
            $options['site_key'] = wp_generate_password( 32, false );
            update_option( 'leadnest_options', $options );
        }
    }

    /**
     * Default plugin options.
     *
     * @return array
     */
    public static function get_default_options() {
        return array(
            'ai_provider'            => 'anthropic',
            'api_key_anthropic'      => '',
            'api_key_openai'         => '',
            'model_anthropic'        => 'claude-3-5-haiku-20241022',
            'model_openai'           => 'gpt-4o-mini',
            'system_prompt'          => 'You are a helpful AI assistant for this website. Your goal is to help visitors find information, answer questions, and capture their contact details if they are interested in our services. Be friendly, concise, and professional. If you cannot answer a question, say so politely and offer to connect them with a human representative.',
            'bot_name'               => 'LeadNest',
            'color_preset'           => 'blue',
            'custom_primary_color'   => '#2563eb',
            'custom_text_color'      => '#ffffff',
            'header_icon_url'        => '',
            'cta_button_text'        => 'Chat with us',
            'lead_capture_enabled'   => true,
            'lead_capture_trigger'   => 3,
            'collect_name'           => true,
            'collect_email'          => true,
            'collect_phone'          => true,
            'name_question'          => 'What is your name?',
            'email_question'         => 'What is your email address?',
            'phone_question'         => 'What is your phone number?',
            'notification_email'     => get_option( 'admin_email' ),
            'woocommerce_enabled'    => true,
            'session_mode'           => 'tab',
            'session_timeout'        => 30,
            'max_history'            => 20,
            'typing_delay'           => 500,
            'show_footer'            => true,
            'footer_text'            => 'Powered by LeadNest',
            'greeting_message'       => 'Hello! How can I help you today?',
            'input_placeholder'      => 'Type your message...',
            'site_key'               => '',
            // v1.4 Crawler
            'crawl_url'              => '',
            'crawl_schedule'         => 'manual',
            // v1.7 Booking
            'booking_duration'       => 60,
            'booking_buffer'         => 15,
            'booking_max_per_day'    => 10,
            'booking_confirmation_message' => 'Your appointment is confirmed! We will send a reminder before your scheduled time.',
            // v1.7 Google Calendar
            'gcal_client_id'         => '',
            'gcal_client_secret'     => '',
            'gcal_refresh_token'     => '',
            'gcal_calendar_id'       => 'primary',
            // v1.7 Twilio SMS
            'twilio_sid'             => '',
            'twilio_token'           => '',
            'twilio_phone'           => '',
            'sms_reminder_enabled'   => false,
            'sms_reminder_hours'     => 24,
            // v1.8 Channels
            'whatsapp_phone_id'      => '',
            'whatsapp_token'         => '',
            'whatsapp_webhook_secret'=> '',
            'messenger_page_id'      => '',
            'messenger_page_token'   => '',
            'messenger_webhook_secret'=> '',
            'telegram_bot_token'     => '',
            'telegram_webhook_secret'=> '',
            // v1.9 Live Agent
            'live_agent_enabled'     => false,
            'live_agent_keywords'    => 'human,agent,real person,talk to someone,manager,operator',
            'live_agent_uncertainty_threshold' => 2,
            // v2.0 License
            'license_key'            => '',
            'license_status'         => '',
        );
    }

    /**
     * Retrieve a merged options array with defaults.
     *
     * @return array
     */
    public static function get_options() {
        $saved    = get_option( 'leadnest_options', array() );
        $defaults = self::get_default_options();
        return array_merge( $defaults, $saved );
    }

    /**
     * Get a single option value.
     *
     * @param string $key
     * @param mixed  $fallback
     * @return mixed
     */
    public static function get_option( $key, $fallback = null ) {
        $options = self::get_options();
        if ( isset( $options[ $key ] ) ) {
            return $options[ $key ];
        }
        return $fallback;
    }

    /**
     * Color preset map.
     *
     * @return array
     */
    public static function get_color_presets() {
        return array(
            'blue'   => array( 'primary' => '#2563eb', 'text' => '#ffffff' ),
            'green'  => array( 'primary' => '#16a34a', 'text' => '#ffffff' ),
            'purple' => array( 'primary' => '#7c3aed', 'text' => '#ffffff' ),
            'orange' => array( 'primary' => '#ea580c', 'text' => '#ffffff' ),
            'red'    => array( 'primary' => '#dc2626', 'text' => '#ffffff' ),
            'teal'   => array( 'primary' => '#0d9488', 'text' => '#ffffff' ),
            'dark'   => array( 'primary' => '#1e293b', 'text' => '#ffffff' ),
            'pink'   => array( 'primary' => '#db2777', 'text' => '#ffffff' ),
        );
    }

    /**
     * Resolve the active colors based on settings.
     *
     * @param array $options
     * @return array {primary, text}
     */
    public static function resolve_colors( $options ) {
        $preset  = isset( $options['color_preset'] ) ? $options['color_preset'] : 'blue';
        $presets = self::get_color_presets();

        if ( $preset === 'custom' ) {
            return array(
                'primary' => ! empty( $options['custom_primary_color'] ) ? $options['custom_primary_color'] : '#2563eb',
                'text'    => ! empty( $options['custom_text_color'] )    ? $options['custom_text_color']    : '#ffffff',
            );
        }

        if ( isset( $presets[ $preset ] ) ) {
            return $presets[ $preset ];
        }

        return $presets['blue'];
    }
}
