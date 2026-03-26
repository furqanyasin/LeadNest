<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class LeadNest_Admin {

    /**
     * Initialize admin hooks.
     */
    public function init() {
        add_action( 'admin_menu', array( $this, 'register_menus' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );

        // AJAX handlers
        add_action( 'wp_ajax_leadnest_save_appearance',  array( $this, 'ajax_save_appearance' ) );
        add_action( 'wp_ajax_leadnest_save_ai_settings', array( $this, 'ajax_save_ai_settings' ) );
        add_action( 'wp_ajax_leadnest_save_behavior',    array( $this, 'ajax_save_behavior' ) );
        add_action( 'wp_ajax_leadnest_update_lead',      array( $this, 'ajax_update_lead' ) );
        add_action( 'wp_ajax_leadnest_delete_lead',      array( $this, 'ajax_delete_lead' ) );
        add_action( 'wp_ajax_leadnest_bulk_delete_chats',array( $this, 'ajax_bulk_delete_chats' ) );
        add_action( 'wp_ajax_leadnest_export_leads',     array( $this, 'ajax_export_leads' ) );
        add_action( 'wp_ajax_leadnest_get_session_chats',array( $this, 'ajax_get_session_chats' ) );
        add_action( 'wp_ajax_leadnest_save_qa',          array( $this, 'ajax_save_qa' ) );
        add_action( 'wp_ajax_leadnest_delete_qa',        array( $this, 'ajax_delete_qa' ) );
        add_action( 'wp_ajax_leadnest_import_qa_csv',    array( $this, 'ajax_import_qa_csv' ) );
        add_action( 'wp_ajax_leadnest_export_qa_csv',    array( $this, 'ajax_export_qa_csv' ) );
        add_action( 'wp_ajax_leadnest_save_knowledge',   array( $this, 'ajax_save_knowledge' ) );
        add_action( 'wp_ajax_leadnest_delete_knowledge', array( $this, 'ajax_delete_knowledge' ) );
        add_action( 'wp_ajax_leadnest_resolve_missed',        array( $this, 'ajax_resolve_missed' ) );
        add_action( 'wp_ajax_leadnest_crawl_url',             array( $this, 'ajax_crawl_url' ) );
        add_action( 'wp_ajax_leadnest_save_crawl_settings',   array( $this, 'ajax_save_crawl_settings' ) );
        add_action( 'wp_ajax_leadnest_save_availability',     array( $this, 'ajax_save_availability' ) );
        add_action( 'wp_ajax_leadnest_update_booking',        array( $this, 'ajax_update_booking' ) );
        add_action( 'wp_ajax_leadnest_delete_booking',        array( $this, 'ajax_delete_booking' ) );
        add_action( 'wp_ajax_leadnest_save_channel_settings', array( $this, 'ajax_save_channel_settings' ) );
        add_action( 'wp_ajax_leadnest_save_live_agent',       array( $this, 'ajax_save_live_agent' ) );
        add_action( 'wp_ajax_leadnest_save_gcal_settings',    array( $this, 'ajax_save_gcal_settings' ) );
        add_action( 'wp_ajax_leadnest_save_twilio_settings',  array( $this, 'ajax_save_twilio_settings' ) );
        add_action( 'wp_ajax_leadnest_save_messenger_settings', array( $this, 'ajax_save_messenger_settings' ) );
        add_action( 'wp_ajax_leadnest_save_telegram_settings', array( $this, 'ajax_save_telegram_settings' ) );
        add_action( 'wp_ajax_leadnest_set_telegram_webhook',  array( $this, 'ajax_set_telegram_webhook' ) );
        add_action( 'wp_ajax_leadnest_activate_license',      array( $this, 'ajax_activate_license' ) );
        add_action( 'wp_ajax_leadnest_deactivate_license',    array( $this, 'ajax_deactivate_license' ) );
    }

    /**
     * Register admin menus.
     */
    public function register_menus() {
        add_menu_page(
            __( 'LeadNest', 'leadnest' ),
            __( 'LeadNest', 'leadnest' ),
            'manage_options',
            'leadnest',
            array( $this, 'page_dashboard' ),
            'dashicons-format-chat',
            30
        );

        add_submenu_page(
            'leadnest',
            __( 'Dashboard', 'leadnest' ),
            __( 'Dashboard', 'leadnest' ),
            'manage_options',
            'leadnest',
            array( $this, 'page_dashboard' )
        );

        add_submenu_page(
            'leadnest',
            __( 'Chat Logs', 'leadnest' ),
            __( 'Chat Logs', 'leadnest' ),
            'manage_options',
            'leadnest-chat-logs',
            array( $this, 'page_chat_logs' )
        );

        add_submenu_page(
            'leadnest',
            __( 'Leads', 'leadnest' ),
            __( 'Leads', 'leadnest' ),
            'manage_options',
            'leadnest-leads',
            array( $this, 'page_leads' )
        );

        add_submenu_page(
            'leadnest',
            __( 'Knowledge Base', 'leadnest' ),
            __( 'Knowledge Base', 'leadnest' ),
            'manage_options',
            'leadnest-knowledge',
            array( $this, 'page_knowledge' )
        );

        add_submenu_page(
            'leadnest',
            __( 'Train Bot', 'leadnest' ),
            __( 'Train Bot', 'leadnest' ),
            'manage_options',
            'leadnest-train-bot',
            array( $this, 'page_train_bot' )
        );

        add_submenu_page(
            'leadnest',
            __( 'Appearance', 'leadnest' ),
            __( 'Appearance', 'leadnest' ),
            'manage_options',
            'leadnest-appearance',
            array( $this, 'page_appearance' )
        );

        add_submenu_page(
            'leadnest',
            __( 'AI Settings', 'leadnest' ),
            __( 'AI Settings', 'leadnest' ),
            'manage_options',
            'leadnest-ai-settings',
            array( $this, 'page_ai_settings' )
        );

        add_submenu_page(
            'leadnest',
            __( 'Behavior', 'leadnest' ),
            __( 'Behavior', 'leadnest' ),
            'manage_options',
            'leadnest-behavior',
            array( $this, 'page_behavior' )
        );

        add_submenu_page(
            'leadnest',
            __( 'Bookings', 'leadnest' ),
            __( 'Bookings', 'leadnest' ),
            'manage_options',
            'leadnest-bookings',
            array( $this, 'page_bookings' )
        );

        add_submenu_page(
            'leadnest',
            __( 'Channels', 'leadnest' ),
            __( 'Channels', 'leadnest' ),
            'manage_options',
            'leadnest-channels',
            array( $this, 'page_channels' )
        );

        add_submenu_page(
            'leadnest',
            __( 'Live Agent', 'leadnest' ),
            __( 'Live Agent', 'leadnest' ),
            'manage_options',
            'leadnest-live-agent',
            array( $this, 'page_live_agent' )
        );

        add_submenu_page(
            'leadnest',
            __( 'Setup Guide', 'leadnest' ),
            __( 'Setup Guide', 'leadnest' ),
            'manage_options',
            'leadnest-setup-guide',
            array( $this, 'page_setup_guide' )
        );
    }

    /**
     * Register settings for the Settings API.
     */
    public function register_settings() {
        register_setting(
            'leadnest_settings',
            'leadnest_options',
            array(
                'sanitize_callback' => array( $this, 'sanitize_options' ),
            )
        );
    }

    /**
     * Sanitize all plugin options.
     */
    public function sanitize_options( $input ) {
        $defaults = LeadNest_DB::get_default_options();
        $output   = array();

        // Text fields
        $text_fields = array( 'bot_name', 'color_preset', 'custom_primary_color', 'custom_text_color',
            'header_icon_url', 'cta_button_text', 'footer_text', 'greeting_message',
            'input_placeholder', 'ai_provider', 'model_anthropic', 'model_openai',
            'notification_email', 'session_mode', 'site_key',
            'crawl_url', 'crawl_schedule',
            'whatsapp_phone_id', 'whatsapp_token', 'whatsapp_webhook_secret',
            'live_agent_keywords',
            'gcal_client_id', 'gcal_client_secret', 'gcal_calendar_id',
            'twilio_sid', 'twilio_token', 'twilio_phone',
            'messenger_page_id', 'messenger_page_token', 'messenger_webhook_secret',
            'telegram_bot_token', 'telegram_webhook_secret',
            'license_key', 'license_status',
        );

        foreach ( $text_fields as $field ) {
            $output[ $field ] = isset( $input[ $field ] )
                ? sanitize_text_field( $input[ $field ] )
                : $defaults[ $field ];
        }

        // API keys
        $output['api_key_anthropic'] = isset( $input['api_key_anthropic'] )
            ? sanitize_text_field( $input['api_key_anthropic'] )
            : '';
        $output['api_key_openai'] = isset( $input['api_key_openai'] )
            ? sanitize_text_field( $input['api_key_openai'] )
            : '';

        // Textarea
        $output['system_prompt'] = isset( $input['system_prompt'] )
            ? sanitize_textarea_field( $input['system_prompt'] )
            : $defaults['system_prompt'];

        // Textarea
        $output['booking_confirmation_message'] = isset( $input['booking_confirmation_message'] )
            ? sanitize_textarea_field( $input['booking_confirmation_message'] )
            : $defaults['booking_confirmation_message'];

        // Integer fields
        $int_fields = array(
            'lead_capture_trigger', 'max_history', 'typing_delay', 'session_timeout',
            'booking_duration', 'booking_buffer', 'booking_max_per_day',
            'live_agent_uncertainty_threshold', 'sms_reminder_hours',
        );
        foreach ( $int_fields as $field ) {
            $output[ $field ] = isset( $input[ $field ] ) ? absint( $input[ $field ] ) : $defaults[ $field ];
        }

        // Boolean fields
        $bool_fields = array(
            'lead_capture_enabled', 'collect_name', 'collect_email',
            'collect_phone', 'show_footer', 'woocommerce_enabled',
            'live_agent_enabled', 'sms_reminder_enabled',
        );
        foreach ( $bool_fields as $field ) {
            $output[ $field ] = ! empty( $input[ $field ] );
        }

        // Preserve site_key if not submitted
        if ( empty( $output['site_key'] ) ) {
            $existing = get_option( 'leadnest_options', array() );
            $output['site_key'] = ! empty( $existing['site_key'] ) ? $existing['site_key'] : wp_generate_password( 32, false );
        }

        return $output;
    }

    // -------------------------------------------------------------------------
    // Page renderers (dispatch to view files)
    // -------------------------------------------------------------------------

    public function page_dashboard() {
        include LEADNEST_PATH . 'admin/views/dashboard.php';
    }

    public function page_chat_logs() {
        include LEADNEST_PATH . 'admin/views/chat-logs.php';
    }

    public function page_leads() {
        include LEADNEST_PATH . 'admin/views/leads.php';
    }

    public function page_knowledge() {
        include LEADNEST_PATH . 'admin/views/knowledge-base.php';
    }

    public function page_train_bot() {
        include LEADNEST_PATH . 'admin/views/train-bot.php';
    }

    public function page_appearance() {
        include LEADNEST_PATH . 'admin/views/appearance.php';
    }

    public function page_ai_settings() {
        include LEADNEST_PATH . 'admin/views/ai-settings.php';
    }

    public function page_behavior() {
        include LEADNEST_PATH . 'admin/views/behavior.php';
    }

    public function page_bookings() {
        include LEADNEST_PATH . 'admin/views/bookings.php';
    }

    public function page_channels() {
        include LEADNEST_PATH . 'admin/views/channels.php';
    }

    public function page_live_agent() {
        include LEADNEST_PATH . 'admin/views/live-agent.php';
    }

    public function page_setup_guide() {
        include LEADNEST_PATH . 'admin/views/setup-guide.php';
    }

    // -------------------------------------------------------------------------
    // AJAX handlers
    // -------------------------------------------------------------------------

    private function verify_nonce( $action = 'leadnest_admin_nonce' ) {
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), $action ) ) {
            wp_send_json_error( array( 'message' => 'Security check failed.' ), 403 );
        }
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => 'Insufficient permissions.' ), 403 );
        }
    }

    public function ajax_save_appearance() {
        $this->verify_nonce();

        $options = LeadNest_DB::get_options();

        $options['bot_name']             = sanitize_text_field( wp_unslash( $_POST['bot_name'] ?? '' ) );
        $options['color_preset']         = sanitize_text_field( wp_unslash( $_POST['color_preset'] ?? 'blue' ) );
        $options['custom_primary_color'] = sanitize_text_field( wp_unslash( $_POST['custom_primary_color'] ?? '#2563eb' ) );
        $options['custom_text_color']    = sanitize_text_field( wp_unslash( $_POST['custom_text_color'] ?? '#ffffff' ) );
        $options['header_icon_url']      = esc_url_raw( wp_unslash( $_POST['header_icon_url'] ?? '' ) );
        $options['cta_button_text']      = sanitize_text_field( wp_unslash( $_POST['cta_button_text'] ?? '' ) );
        $options['footer_text']          = sanitize_text_field( wp_unslash( $_POST['footer_text'] ?? '' ) );
        $options['show_footer']          = ! empty( $_POST['show_footer'] );
        $options['typing_delay']         = absint( $_POST['typing_delay'] ?? 500 );

        update_option( 'leadnest_options', $options );

        wp_send_json_success( array( 'message' => 'Appearance settings saved.' ) );
    }

    public function ajax_save_ai_settings() {
        $this->verify_nonce();

        $options = LeadNest_DB::get_options();

        $options['ai_provider']          = sanitize_text_field( wp_unslash( $_POST['ai_provider'] ?? 'anthropic' ) );
        $options['api_key_anthropic']    = sanitize_text_field( wp_unslash( $_POST['api_key_anthropic'] ?? '' ) );
        $options['api_key_openai']       = sanitize_text_field( wp_unslash( $_POST['api_key_openai'] ?? '' ) );
        $options['model_anthropic']      = sanitize_text_field( wp_unslash( $_POST['model_anthropic'] ?? 'claude-3-5-haiku-20241022' ) );
        $options['model_openai']         = sanitize_text_field( wp_unslash( $_POST['model_openai'] ?? 'gpt-4o-mini' ) );
        $options['system_prompt']        = sanitize_textarea_field( wp_unslash( $_POST['system_prompt'] ?? '' ) );
        $options['lead_capture_enabled'] = ! empty( $_POST['lead_capture_enabled'] );
        $options['lead_capture_trigger'] = absint( $_POST['lead_capture_trigger'] ?? 3 );
        $options['collect_name']         = ! empty( $_POST['collect_name'] );
        $options['collect_email']        = ! empty( $_POST['collect_email'] );
        $options['collect_phone']        = ! empty( $_POST['collect_phone'] );
        $options['name_question']        = sanitize_text_field( wp_unslash( $_POST['name_question'] ?? '' ) );
        $options['email_question']       = sanitize_text_field( wp_unslash( $_POST['email_question'] ?? '' ) );
        $options['phone_question']       = sanitize_text_field( wp_unslash( $_POST['phone_question'] ?? '' ) );
        $options['notification_email']   = sanitize_email( wp_unslash( $_POST['notification_email'] ?? '' ) );
        $options['max_history']          = absint( $_POST['max_history'] ?? 20 );

        update_option( 'leadnest_options', $options );

        wp_send_json_success( array( 'message' => 'AI settings saved.' ) );
    }

    public function ajax_save_behavior() {
        $this->verify_nonce();

        $options = LeadNest_DB::get_options();

        $options['session_mode']         = sanitize_text_field( wp_unslash( $_POST['session_mode'] ?? 'tab' ) );
        $options['session_timeout']      = absint( $_POST['session_timeout'] ?? 30 );
        $options['woocommerce_enabled']  = ! empty( $_POST['woocommerce_enabled'] );
        $options['greeting_message']     = sanitize_text_field( wp_unslash( $_POST['greeting_message'] ?? '' ) );
        $options['input_placeholder']    = sanitize_text_field( wp_unslash( $_POST['input_placeholder'] ?? '' ) );

        update_option( 'leadnest_options', $options );

        wp_send_json_success( array( 'message' => 'Behavior settings saved.' ) );
    }

    public function ajax_update_lead() {
        $this->verify_nonce();
        global $wpdb;

        $id     = absint( $_POST['lead_id'] ?? 0 );
        $status = sanitize_text_field( wp_unslash( $_POST['status'] ?? '' ) );
        $notes  = sanitize_textarea_field( wp_unslash( $_POST['notes'] ?? '' ) );

        if ( ! $id ) {
            wp_send_json_error( array( 'message' => 'Invalid lead ID.' ) );
        }

        $allowed_statuses = array( 'new', 'contacted', 'qualified', 'closed' );
        if ( ! empty( $status ) && ! in_array( $status, $allowed_statuses, true ) ) {
            wp_send_json_error( array( 'message' => 'Invalid status.' ) );
        }

        $data = array( 'updated_at' => current_time( 'mysql' ) );
        $fmt  = array( '%s' );

        if ( ! empty( $status ) ) {
            $data['status'] = $status;
            $fmt[]          = '%s';
        }
        $data['notes'] = $notes;
        $fmt[]         = '%s';

        $wpdb->update( $wpdb->prefix . 'leadnest_leads', $data, array( 'id' => $id ), $fmt, array( '%d' ) );

        wp_send_json_success( array( 'message' => 'Lead updated.' ) );
    }

    public function ajax_delete_lead() {
        $this->verify_nonce();
        global $wpdb;

        $id = absint( $_POST['lead_id'] ?? 0 );
        if ( ! $id ) {
            wp_send_json_error( array( 'message' => 'Invalid lead ID.' ) );
        }

        $wpdb->delete( $wpdb->prefix . 'leadnest_leads', array( 'id' => $id ), array( '%d' ) );
        wp_send_json_success( array( 'message' => 'Lead deleted.' ) );
    }

    public function ajax_bulk_delete_chats() {
        $this->verify_nonce();
        global $wpdb;

        $ids = isset( $_POST['session_ids'] ) ? array_map( 'absint', (array) $_POST['session_ids'] ) : array();
        if ( empty( $ids ) ) {
            wp_send_json_error( array( 'message' => 'No sessions selected.' ) );
        }

        $placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}leadnest_chats WHERE session_id IN ($placeholders)", ...$ids ) );
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}leadnest_sessions WHERE id IN ($placeholders)", ...$ids ) );

        wp_send_json_success( array( 'message' => count( $ids ) . ' sessions deleted.' ) );
    }

    public function ajax_export_leads() {
        $this->verify_nonce();
        global $wpdb;

        $leads = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}leadnest_leads ORDER BY created_at DESC" );

        header( 'Content-Type: text/csv; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename="leadnest-leads-' . gmdate( 'Y-m-d' ) . '.csv"' );

        $output = fopen( 'php://output', 'w' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen
        fputcsv( $output, array( 'ID', 'Name', 'Email', 'Phone', 'Need', 'Source Page', 'Status', 'Notes', 'Date' ) );

        foreach ( $leads as $lead ) {
            fputcsv( $output, array(
                $lead->id,
                $lead->name,
                $lead->email,
                $lead->phone,
                $lead->need,
                $lead->source_page,
                $lead->status,
                $lead->notes,
                $lead->created_at,
            ) );
        }

        fclose( $output ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
        exit;
    }

    public function ajax_get_session_chats() {
        $this->verify_nonce();
        global $wpdb;

        $session_id = absint( $_POST['session_id'] ?? 0 );
        if ( ! $session_id ) {
            wp_send_json_error( array( 'message' => 'Invalid session.' ) );
        }

        $chats = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT role, content, created_at FROM {$wpdb->prefix}leadnest_chats
                 WHERE session_id = %d ORDER BY created_at ASC",
                $session_id
            )
        );

        wp_send_json_success( array( 'chats' => $chats ) );
    }

    public function ajax_save_qa() {
        $this->verify_nonce();
        global $wpdb;

        $options  = LeadNest_DB::get_options();
        $site_key = $options['site_key'];
        $id       = absint( $_POST['qa_id'] ?? 0 );
        $question = sanitize_textarea_field( wp_unslash( $_POST['question'] ?? '' ) );
        $answer   = sanitize_textarea_field( wp_unslash( $_POST['answer'] ?? '' ) );

        if ( empty( $question ) || empty( $answer ) ) {
            wp_send_json_error( array( 'message' => 'Question and answer are required.' ) );
        }

        if ( $id ) {
            $wpdb->update(
                $wpdb->prefix . 'leadnest_qa',
                array( 'question' => $question, 'answer' => $answer ),
                array( 'id' => $id ),
                array( '%s', '%s' ),
                array( '%d' )
            );
        } else {
            $wpdb->insert(
                $wpdb->prefix . 'leadnest_qa',
                array(
                    'site_key'   => $site_key,
                    'question'   => $question,
                    'answer'     => $answer,
                    'active'     => 1,
                    'created_at' => current_time( 'mysql' ),
                ),
                array( '%s', '%s', '%s', '%d', '%s' )
            );
        }

        wp_send_json_success( array( 'message' => 'Q&A saved.' ) );
    }

    public function ajax_delete_qa() {
        $this->verify_nonce();
        global $wpdb;

        $id = absint( $_POST['qa_id'] ?? 0 );
        if ( ! $id ) {
            wp_send_json_error( array( 'message' => 'Invalid ID.' ) );
        }
        $wpdb->delete( $wpdb->prefix . 'leadnest_qa', array( 'id' => $id ), array( '%d' ) );
        wp_send_json_success( array( 'message' => 'Q&A deleted.' ) );
    }

    public function ajax_import_qa_csv() {
        $this->verify_nonce();
        global $wpdb;

        if ( empty( $_FILES['qa_csv_file'] ) || $_FILES['qa_csv_file']['error'] !== UPLOAD_ERR_OK ) {
            wp_send_json_error( array( 'message' => 'No file uploaded or upload error.' ) );
        }

        $file = $_FILES['qa_csv_file'];
        $ext  = strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) );

        if ( $ext !== 'csv' ) {
            wp_send_json_error( array( 'message' => 'Only CSV files are accepted.' ) );
        }

        $options  = LeadNest_DB::get_options();
        $site_key = $options['site_key'];

        // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen
        $handle = fopen( $file['tmp_name'], 'r' );
        if ( ! $handle ) {
            wp_send_json_error( array( 'message' => 'Could not read the uploaded file.' ) );
        }

        $imported = 0;
        $skipped  = 0;
        $row_num  = 0;

        while ( ( $row = fgetcsv( $handle ) ) !== false ) {
            $row_num++;

            // Skip header row if it looks like one
            if ( $row_num === 1 && isset( $row[0] ) && strtolower( trim( $row[0] ) ) === 'question' ) {
                continue;
            }

            $question = isset( $row[0] ) ? sanitize_textarea_field( trim( $row[0] ) ) : '';
            $answer   = isset( $row[1] ) ? sanitize_textarea_field( trim( $row[1] ) ) : '';

            if ( empty( $question ) || empty( $answer ) ) {
                $skipped++;
                continue;
            }

            // Check for duplicate
            $exists = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT id FROM {$wpdb->prefix}leadnest_qa WHERE site_key = %s AND question = %s LIMIT 1",
                    $site_key,
                    $question
                )
            );

            if ( $exists ) {
                $wpdb->update(
                    $wpdb->prefix . 'leadnest_qa',
                    array( 'answer' => $answer ),
                    array( 'id' => $exists ),
                    array( '%s' ),
                    array( '%d' )
                );
            } else {
                $wpdb->insert(
                    $wpdb->prefix . 'leadnest_qa',
                    array(
                        'site_key'   => $site_key,
                        'question'   => $question,
                        'answer'     => $answer,
                        'active'     => 1,
                        'created_at' => current_time( 'mysql' ),
                    ),
                    array( '%s', '%s', '%s', '%d', '%s' )
                );
            }

            $imported++;
        }

        fclose( $handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose

        wp_send_json_success( array(
            'message' => sprintf( '%d Q&A pair(s) imported, %d skipped.', $imported, $skipped ),
            'imported' => $imported,
            'skipped'  => $skipped,
        ) );
    }

    public function ajax_export_qa_csv() {
        $this->verify_nonce();
        global $wpdb;

        $options  = LeadNest_DB::get_options();
        $site_key = $options['site_key'];

        $qa_pairs = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT question, answer, use_count, created_at FROM {$wpdb->prefix}leadnest_qa WHERE site_key = %s ORDER BY created_at DESC",
                $site_key
            )
        );

        header( 'Content-Type: text/csv; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename="leadnest-qa-' . gmdate( 'Y-m-d' ) . '.csv"' );

        $output = fopen( 'php://output', 'w' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen
        fputcsv( $output, array( 'Question', 'Answer', 'Uses', 'Date Added' ) );

        foreach ( $qa_pairs as $qa ) {
            fputcsv( $output, array( $qa->question, $qa->answer, $qa->use_count, $qa->created_at ) );
        }

        fclose( $output ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
        exit;
    }

    public function ajax_save_knowledge() {
        $this->verify_nonce();
        global $wpdb;

        $options    = LeadNest_DB::get_options();
        $site_key   = $options['site_key'];
        $id         = absint( $_POST['kb_id'] ?? 0 );
        $url        = esc_url_raw( wp_unslash( $_POST['kb_url'] ?? '' ) );
        $page_title = sanitize_text_field( wp_unslash( $_POST['page_title'] ?? '' ) );
        $content    = sanitize_textarea_field( wp_unslash( $_POST['content'] ?? '' ) );
        $word_count = str_word_count( $content );

        if ( $id ) {
            $wpdb->update(
                $wpdb->prefix . 'leadnest_knowledge',
                array(
                    'url'          => $url,
                    'page_title'   => $page_title,
                    'content'      => $content,
                    'word_count'   => $word_count,
                    'last_crawled' => current_time( 'mysql' ),
                ),
                array( 'id' => $id ),
                array( '%s', '%s', '%s', '%d', '%s' ),
                array( '%d' )
            );
        } else {
            $wpdb->insert(
                $wpdb->prefix . 'leadnest_knowledge',
                array(
                    'site_key'     => $site_key,
                    'url'          => $url,
                    'page_title'   => $page_title,
                    'content'      => $content,
                    'word_count'   => $word_count,
                    'active'       => 1,
                    'last_crawled' => current_time( 'mysql' ),
                    'created_at'   => current_time( 'mysql' ),
                ),
                array( '%s', '%s', '%s', '%s', '%d', '%d', '%s', '%s' )
            );
        }

        wp_send_json_success( array( 'message' => 'Knowledge base entry saved.' ) );
    }

    public function ajax_delete_knowledge() {
        $this->verify_nonce();
        global $wpdb;

        $id = absint( $_POST['kb_id'] ?? 0 );
        if ( ! $id ) {
            wp_send_json_error( array( 'message' => 'Invalid ID.' ) );
        }
        $wpdb->delete( $wpdb->prefix . 'leadnest_knowledge', array( 'id' => $id ), array( '%d' ) );
        wp_send_json_success( array( 'message' => 'Entry deleted.' ) );
    }

    public function ajax_resolve_missed() {
        $this->verify_nonce();
        global $wpdb;

        $id = absint( $_POST['missed_id'] ?? 0 );
        if ( ! $id ) {
            wp_send_json_error( array( 'message' => 'Invalid ID.' ) );
        }

        $wpdb->update(
            $wpdb->prefix . 'leadnest_missed_questions',
            array( 'resolved' => 1 ),
            array( 'id' => $id ),
            array( '%d' ),
            array( '%d' )
        );

        wp_send_json_success( array( 'message' => 'Marked as resolved.' ) );
    }

    // ── v1.4 Crawler ─────────────────────────────────────────────────────────

    public function ajax_crawl_url() {
        $this->verify_nonce();

        $url       = esc_url_raw( wp_unslash( $_POST['crawl_url'] ?? '' ) );
        $max_pages = absint( $_POST['max_pages'] ?? 20 );

        if ( empty( $url ) ) {
            wp_send_json_error( array( 'message' => 'URL is required.' ) );
        }

        // Validate URL format
        if ( ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
            wp_send_json_error( array( 'message' => 'Invalid URL format.' ) );
        }

        require_once LEADNEST_PATH . 'includes/class-leadnest-crawler.php';

        $options  = LeadNest_DB::get_options();
        $site_key = $options['site_key'];

        $crawler = new LeadNest_Crawler();
        $count   = $crawler->crawl_site( $url, $site_key, $max_pages );

        // Save crawl URL to options for auto-recrawl
        $options['crawl_url'] = $url;
        update_option( 'leadnest_options', $options );

        wp_send_json_success( array(
            'message' => sprintf( '%d page(s) crawled and saved to knowledge base.', $count ),
            'count'   => $count,
        ) );
    }

    public function ajax_save_crawl_settings() {
        $this->verify_nonce();

        $options  = LeadNest_DB::get_options();
        $schedule = sanitize_text_field( wp_unslash( $_POST['crawl_schedule'] ?? 'manual' ) );

        if ( ! in_array( $schedule, array( 'manual', 'daily', 'weekly' ), true ) ) {
            $schedule = 'manual';
        }

        $old_schedule = $options['crawl_schedule'];
        $options['crawl_schedule'] = $schedule;
        update_option( 'leadnest_options', $options );

        // Reschedule cron if needed
        wp_clear_scheduled_hook( 'leadnest_auto_recrawl' );
        if ( $schedule === 'daily' ) {
            wp_schedule_event( time(), 'daily', 'leadnest_auto_recrawl' );
        } elseif ( $schedule === 'weekly' ) {
            wp_schedule_event( time(), 'weekly', 'leadnest_auto_recrawl' );
        }

        wp_send_json_success( array( 'message' => 'Crawl settings saved.' ) );
    }

    // ── v1.7 Availability & Bookings ─────────────────────────────────────────

    public function ajax_save_availability() {
        $this->verify_nonce();
        global $wpdb;

        $options  = LeadNest_DB::get_options();
        $site_key = $options['site_key'];

        // Parse active days
        $active_days = isset( $_POST['avail_active'] ) ? array_map( 'absint', (array) $_POST['avail_active'] ) : array();

        // Save availability settings
        $options['booking_duration']             = absint( $_POST['booking_duration'] ?? 60 );
        $options['booking_buffer']               = absint( $_POST['booking_buffer'] ?? 15 );
        $options['booking_max_per_day']          = absint( $_POST['booking_max_per_day'] ?? 10 );
        $options['booking_confirmation_message'] = sanitize_textarea_field( wp_unslash( $_POST['booking_confirmation_message'] ?? '' ) );
        update_option( 'leadnest_options', $options );

        // Delete existing availability for this site_key and re-insert
        $wpdb->delete( $wpdb->prefix . 'leadnest_availability', array( 'site_key' => $site_key ), array( '%s' ) );

        $avail_start = isset( $_POST['avail_start'] ) ? (array) $_POST['avail_start'] : array();
        $avail_end   = isset( $_POST['avail_end'] )   ? (array) $_POST['avail_end']   : array();

        for ( $dow = 0; $dow <= 6; $dow++ ) {
            $active = in_array( $dow, $active_days, true ) ? 1 : 0;
            $start  = isset( $avail_start[ $dow ] ) ? sanitize_text_field( $avail_start[ $dow ] ) : '09:00';
            $end    = isset( $avail_end[ $dow ] )   ? sanitize_text_field( $avail_end[ $dow ] )   : '17:00';

            // Validate time format
            if ( ! preg_match( '/^\d{2}:\d{2}$/', $start ) ) { $start = '09:00'; }
            if ( ! preg_match( '/^\d{2}:\d{2}$/', $end ) )   { $end   = '17:00'; }

            $wpdb->insert(
                $wpdb->prefix . 'leadnest_availability',
                array(
                    'site_key'    => $site_key,
                    'day_of_week' => $dow,
                    'start_time'  => $start . ':00',
                    'end_time'    => $end . ':00',
                    'active'      => $active,
                ),
                array( '%s', '%d', '%s', '%s', '%d' )
            );
        }

        wp_send_json_success( array( 'message' => 'Availability saved.' ) );
    }

    public function ajax_update_booking() {
        $this->verify_nonce();
        global $wpdb;

        $id     = absint( $_POST['booking_id'] ?? 0 );
        $status = sanitize_text_field( wp_unslash( $_POST['status'] ?? '' ) );

        if ( ! $id ) {
            wp_send_json_error( array( 'message' => 'Invalid booking ID.' ) );
        }

        $allowed = array( 'pending', 'confirmed', 'cancelled', 'completed' );
        if ( ! in_array( $status, $allowed, true ) ) {
            wp_send_json_error( array( 'message' => 'Invalid status.' ) );
        }

        $wpdb->update(
            $wpdb->prefix . 'leadnest_bookings',
            array( 'status' => $status ),
            array( 'id' => $id ),
            array( '%s' ),
            array( '%d' )
        );

        wp_send_json_success( array( 'message' => 'Booking updated.' ) );
    }

    public function ajax_delete_booking() {
        $this->verify_nonce();
        global $wpdb;

        $id = absint( $_POST['booking_id'] ?? 0 );
        if ( ! $id ) {
            wp_send_json_error( array( 'message' => 'Invalid ID.' ) );
        }

        $wpdb->delete( $wpdb->prefix . 'leadnest_bookings', array( 'id' => $id ), array( '%d' ) );
        wp_send_json_success( array( 'message' => 'Booking deleted.' ) );
    }

    // ── v1.8 Channels ────────────────────────────────────────────────────────

    public function ajax_save_channel_settings() {
        $this->verify_nonce();

        $options = LeadNest_DB::get_options();

        $options['whatsapp_phone_id']       = sanitize_text_field( wp_unslash( $_POST['whatsapp_phone_id']       ?? '' ) );
        $options['whatsapp_token']          = sanitize_text_field( wp_unslash( $_POST['whatsapp_token']          ?? '' ) );
        $options['whatsapp_webhook_secret'] = sanitize_text_field( wp_unslash( $_POST['whatsapp_webhook_secret'] ?? '' ) );

        update_option( 'leadnest_options', $options );

        wp_send_json_success( array( 'message' => 'Channel settings saved.' ) );
    }

    // ── v1.9 Live Agent ──────────────────────────────────────────────────────

    public function ajax_save_live_agent() {
        $this->verify_nonce();

        $options = LeadNest_DB::get_options();

        $options['live_agent_enabled']               = ! empty( $_POST['live_agent_enabled'] );
        $options['live_agent_keywords']              = sanitize_text_field( wp_unslash( $_POST['live_agent_keywords']              ?? '' ) );
        $options['live_agent_uncertainty_threshold'] = absint( $_POST['live_agent_uncertainty_threshold'] ?? 2 );

        update_option( 'leadnest_options', $options );

        wp_send_json_success( array( 'message' => 'Live agent settings saved.' ) );
    }

    // ── v1.7 Google Calendar ─────────────────────────────────────────────────

    public function ajax_save_gcal_settings() {
        $this->verify_nonce();

        $options = LeadNest_DB::get_options();

        $options['gcal_client_id']     = sanitize_text_field( wp_unslash( $_POST['gcal_client_id']     ?? '' ) );
        $options['gcal_client_secret'] = sanitize_text_field( wp_unslash( $_POST['gcal_client_secret'] ?? '' ) );
        $options['gcal_calendar_id']   = sanitize_text_field( wp_unslash( $_POST['gcal_calendar_id']   ?? 'primary' ) );

        update_option( 'leadnest_options', $options );

        wp_send_json_success( array( 'message' => 'Google Calendar settings saved.' ) );
    }

    // ── v1.7 Twilio SMS ──────────────────────────────────────────────────────

    public function ajax_save_twilio_settings() {
        $this->verify_nonce();

        $options = LeadNest_DB::get_options();

        $options['twilio_sid']            = sanitize_text_field( wp_unslash( $_POST['twilio_sid']   ?? '' ) );
        $options['twilio_token']          = sanitize_text_field( wp_unslash( $_POST['twilio_token'] ?? '' ) );
        $options['twilio_phone']          = sanitize_text_field( wp_unslash( $_POST['twilio_phone'] ?? '' ) );
        $options['sms_reminder_enabled']  = ! empty( $_POST['sms_reminder_enabled'] );
        $options['sms_reminder_hours']    = absint( $_POST['sms_reminder_hours'] ?? 24 );

        update_option( 'leadnest_options', $options );

        wp_send_json_success( array( 'message' => 'Twilio settings saved.' ) );
    }

    // ── v1.8 Messenger ───────────────────────────────────────────────────────

    public function ajax_save_messenger_settings() {
        $this->verify_nonce();

        $options = LeadNest_DB::get_options();

        $options['messenger_page_id']        = sanitize_text_field( wp_unslash( $_POST['messenger_page_id']        ?? '' ) );
        $options['messenger_page_token']     = sanitize_text_field( wp_unslash( $_POST['messenger_page_token']     ?? '' ) );
        $options['messenger_webhook_secret'] = sanitize_text_field( wp_unslash( $_POST['messenger_webhook_secret'] ?? '' ) );

        update_option( 'leadnest_options', $options );

        wp_send_json_success( array( 'message' => 'Messenger settings saved.' ) );
    }

    // ── v1.8 Telegram ────────────────────────────────────────────────────────

    public function ajax_save_telegram_settings() {
        $this->verify_nonce();

        $options = LeadNest_DB::get_options();

        $options['telegram_bot_token']      = sanitize_text_field( wp_unslash( $_POST['telegram_bot_token']      ?? '' ) );
        $options['telegram_webhook_secret'] = sanitize_text_field( wp_unslash( $_POST['telegram_webhook_secret'] ?? '' ) );

        update_option( 'leadnest_options', $options );

        wp_send_json_success( array( 'message' => 'Telegram settings saved.' ) );
    }

    public function ajax_set_telegram_webhook() {
        $this->verify_nonce();

        $options   = LeadNest_DB::get_options();
        $bot_token = $options['telegram_bot_token'] ?? '';

        if ( empty( $bot_token ) ) {
            wp_send_json_error( array( 'message' => 'Telegram bot token not configured.' ) );
        }

        $webhook_url = rest_url( 'leadnest/v1/telegram/webhook' );
        $secret      = $options['telegram_webhook_secret'] ?? '';

        $body = array( 'url' => $webhook_url );
        if ( ! empty( $secret ) ) {
            $body['secret_token'] = $secret;
        }

        $response = wp_remote_post(
            'https://api.telegram.org/bot' . rawurlencode( $bot_token ) . '/setWebhook',
            array(
                'timeout' => 15,
                'body'    => $body,
            )
        );

        if ( is_wp_error( $response ) ) {
            wp_send_json_error( array( 'message' => 'Failed to reach Telegram: ' . $response->get_error_message() ) );
        }

        $data = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( ! empty( $data['ok'] ) ) {
            wp_send_json_success( array( 'message' => 'Telegram webhook set successfully.' ) );
        } else {
            wp_send_json_error( array( 'message' => 'Telegram error: ' . ( $data['description'] ?? 'Unknown error' ) ) );
        }
    }

    // ── v2.0 License ─────────────────────────────────────────────────────────

    public function ajax_activate_license() {
        $this->verify_nonce();

        $license_key = sanitize_text_field( wp_unslash( $_POST['license_key'] ?? '' ) );

        if ( empty( $license_key ) ) {
            wp_send_json_error( array( 'message' => 'Please enter a license key.' ) );
        }

        if ( ! class_exists( 'LeadNest_License' ) ) {
            wp_send_json_error( array( 'message' => 'License system not available.' ) );
        }

        $result = LeadNest_License::activate( $license_key );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( array( 'message' => $result->get_error_message() ) );
        }

        wp_send_json_success( $result );
    }

    public function ajax_deactivate_license() {
        $this->verify_nonce();

        if ( ! class_exists( 'LeadNest_License' ) ) {
            wp_send_json_error( array( 'message' => 'License system not available.' ) );
        }

        $result = LeadNest_License::deactivate();

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( array( 'message' => $result->get_error_message() ) );
        }

        wp_send_json_success( $result );
    }
}
