<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class LeadNest_API {

    const NAMESPACE = 'leadnest/v1';

    /**
     * Phrases that indicate the bot couldn't answer.
     */
    private $missed_phrases = array(
        "i don't know",
        "i do not know",
        "i'm not sure",
        "i am not sure",
        "i don't have information",
        "i do not have information",
        "i cannot answer",
        "i can't answer",
        "i'm unable to",
        "i am unable to",
        "not in my knowledge",
        "outside my knowledge",
        "i don't have that information",
        "please contact",
        "reach out to",
        "i'd recommend contacting",
        "i would recommend contacting",
        "unfortunately, i don't",
        "unfortunately, i do not",
    );

    /**
     * Add CORS headers for cross-origin embed requests.
     */
    public function add_cors_headers() {
        $route = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
        if ( strpos( $route, '/leadnest/v1/' ) === false ) {
            return;
        }

        $options = LeadNest_DB::get_options();
        header( 'Access-Control-Allow-Origin: *' );
        header( 'Access-Control-Allow-Methods: GET, POST, OPTIONS' );
        header( 'Access-Control-Allow-Headers: Content-Type, X-WP-Nonce' );

        if ( isset( $_SERVER['REQUEST_METHOD'] ) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS' ) {
            status_header( 200 );
            exit;
        }
    }

    /**
     * Register all REST routes.
     */
    public function register_routes() {
        register_rest_route( self::NAMESPACE, '/chat', array(
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => array( $this, 'handle_chat' ),
            'permission_callback' => '__return_true',
            'args'                => array(
                'session_token' => array(
                    'required'          => false,
                    'sanitize_callback' => 'sanitize_text_field',
                ),
                'message' => array(
                    'required'          => true,
                    'sanitize_callback' => 'sanitize_textarea_field',
                ),
                'page_url' => array(
                    'required'          => false,
                    'sanitize_callback' => 'esc_url_raw',
                ),
                'site_key' => array(
                    'required'          => false,
                    'sanitize_callback' => 'sanitize_text_field',
                ),
            ),
        ) );

        register_rest_route( self::NAMESPACE, '/session', array(
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => array( $this, 'handle_session' ),
            'permission_callback' => '__return_true',
            'args'                => array(
                'site_key' => array(
                    'required'          => false,
                    'sanitize_callback' => 'sanitize_text_field',
                ),
                'page_url' => array(
                    'required'          => false,
                    'sanitize_callback' => 'esc_url_raw',
                ),
            ),
        ) );

        register_rest_route( self::NAMESPACE, '/leads', array(
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => array( $this, 'get_leads' ),
            'permission_callback' => array( $this, 'admin_permission' ),
        ) );

        register_rest_route( self::NAMESPACE, '/leads/(?P<id>\d+)/status', array(
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => array( $this, 'update_lead_status' ),
            'permission_callback' => array( $this, 'admin_permission' ),
            'args'                => array(
                'id' => array(
                    'required'          => true,
                    'sanitize_callback' => 'absint',
                ),
                'status' => array(
                    'required'          => true,
                    'sanitize_callback' => 'sanitize_text_field',
                ),
            ),
        ) );

        register_rest_route( self::NAMESPACE, '/leads/(?P<id>\d+)/notes', array(
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => array( $this, 'update_lead_notes' ),
            'permission_callback' => array( $this, 'admin_permission' ),
            'args'                => array(
                'id' => array(
                    'required'          => true,
                    'sanitize_callback' => 'absint',
                ),
                'notes' => array(
                    'required'          => false,
                    'sanitize_callback' => 'sanitize_textarea_field',
                ),
            ),
        ) );

        register_rest_route( self::NAMESPACE, '/widget\.js', array(
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => array( $this, 'serve_widget_js' ),
            'permission_callback' => '__return_true',
        ) );

        register_rest_route( self::NAMESPACE, '/widget-config', array(
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => array( $this, 'get_widget_config' ),
            'permission_callback' => '__return_true',
        ) );

        register_rest_route( self::NAMESPACE, '/sessions', array(
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => array( $this, 'get_sessions' ),
            'permission_callback' => array( $this, 'admin_permission' ),
        ) );

        register_rest_route( self::NAMESPACE, '/sessions/(?P<id>\d+)', array(
            'methods'             => WP_REST_Server::DELETABLE,
            'callback'            => array( $this, 'delete_session' ),
            'permission_callback' => array( $this, 'admin_permission' ),
        ) );

        register_rest_route( self::NAMESPACE, '/leads/(?P<id>\d+)', array(
            'methods'             => WP_REST_Server::DELETABLE,
            'callback'            => array( $this, 'delete_lead' ),
            'permission_callback' => array( $this, 'admin_permission' ),
        ) );

        register_rest_route( self::NAMESPACE, '/test-connection', array(
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => array( $this, 'test_connection' ),
            'permission_callback' => array( $this, 'admin_permission' ),
        ) );

        // v1.7 — Bookings
        register_rest_route( self::NAMESPACE, '/bookings', array(
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => array( $this, 'get_bookings' ),
            'permission_callback' => array( $this, 'admin_permission' ),
        ) );

        register_rest_route( self::NAMESPACE, '/bookings', array(
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => array( $this, 'create_booking' ),
            'permission_callback' => '__return_true',
            'args'                => array(
                'site_key'     => array( 'required' => false, 'sanitize_callback' => 'sanitize_text_field' ),
                'name'         => array( 'required' => false, 'sanitize_callback' => 'sanitize_text_field' ),
                'email'        => array( 'required' => false, 'sanitize_callback' => 'sanitize_email' ),
                'phone'        => array( 'required' => false, 'sanitize_callback' => 'sanitize_text_field' ),
                'service_type' => array( 'required' => false, 'sanitize_callback' => 'sanitize_text_field' ),
                'booking_date' => array( 'required' => true,  'sanitize_callback' => 'sanitize_text_field' ),
                'booking_time' => array( 'required' => true,  'sanitize_callback' => 'sanitize_text_field' ),
                'session_id'   => array( 'required' => false, 'sanitize_callback' => 'absint' ),
            ),
        ) );

        register_rest_route( self::NAMESPACE, '/bookings/(?P<id>\d+)/status', array(
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => array( $this, 'update_booking_status' ),
            'permission_callback' => array( $this, 'admin_permission' ),
        ) );

        register_rest_route( self::NAMESPACE, '/available-slots', array(
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => array( $this, 'get_available_slots' ),
            'permission_callback' => '__return_true',
            'args'                => array(
                'site_key' => array( 'required' => false, 'sanitize_callback' => 'sanitize_text_field' ),
                'date'     => array( 'required' => false, 'sanitize_callback' => 'sanitize_text_field' ),
            ),
        ) );

        // Widget poll for live agent messages (public)
        register_rest_route( self::NAMESPACE, '/chat-poll', array(
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => array( $this, 'chat_poll' ),
            'permission_callback' => '__return_true',
            'args'                => array(
                'session_token' => array( 'required' => true, 'sanitize_callback' => 'sanitize_text_field' ),
                'site_key'      => array( 'required' => false, 'sanitize_callback' => 'sanitize_text_field' ),
                'last_count'    => array( 'required' => false, 'sanitize_callback' => 'absint' ),
            ),
        ) );

        // v1.9.1 — Live Agent Takeover
        register_rest_route( self::NAMESPACE, '/live-agent/takeover', array(
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => array( $this, 'live_agent_takeover' ),
            'permission_callback' => array( $this, 'admin_permission' ),
            'args'                => array(
                'session_id' => array( 'required' => true, 'sanitize_callback' => 'absint' ),
            ),
        ) );

        register_rest_route( self::NAMESPACE, '/live-agent/release', array(
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => array( $this, 'live_agent_release' ),
            'permission_callback' => array( $this, 'admin_permission' ),
            'args'                => array(
                'session_id' => array( 'required' => true, 'sanitize_callback' => 'absint' ),
            ),
        ) );

        register_rest_route( self::NAMESPACE, '/live-agent/reply', array(
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => array( $this, 'live_agent_reply' ),
            'permission_callback' => array( $this, 'admin_permission' ),
            'args'                => array(
                'session_id' => array( 'required' => true, 'sanitize_callback' => 'absint' ),
                'message'    => array( 'required' => true, 'sanitize_callback' => 'sanitize_textarea_field' ),
            ),
        ) );

        register_rest_route( self::NAMESPACE, '/live-agent/messages', array(
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => array( $this, 'live_agent_messages' ),
            'permission_callback' => array( $this, 'admin_permission' ),
            'args'                => array(
                'session_id' => array( 'required' => true, 'sanitize_callback' => 'absint' ),
                'after'      => array( 'required' => false, 'sanitize_callback' => 'sanitize_text_field' ),
            ),
        ) );

        // v1.8 — WhatsApp webhook
        register_rest_route( self::NAMESPACE, '/whatsapp/webhook', array(
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => array( $this, 'whatsapp_verify_webhook' ),
            'permission_callback' => '__return_true',
        ) );

        register_rest_route( self::NAMESPACE, '/whatsapp/webhook', array(
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => array( $this, 'whatsapp_receive_message' ),
            'permission_callback' => '__return_true',
        ) );

        // v1.8 — Facebook Messenger webhook
        register_rest_route( self::NAMESPACE, '/messenger/webhook', array(
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => array( $this, 'messenger_verify_webhook' ),
            'permission_callback' => '__return_true',
        ) );

        register_rest_route( self::NAMESPACE, '/messenger/webhook', array(
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => array( $this, 'messenger_receive_message' ),
            'permission_callback' => '__return_true',
        ) );

        // v1.8 — Telegram webhook
        register_rest_route( self::NAMESPACE, '/telegram/webhook', array(
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => array( $this, 'telegram_receive_message' ),
            'permission_callback' => '__return_true',
        ) );

        // v2.0 — License
        register_rest_route( self::NAMESPACE, '/license/activate', array(
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => array( $this, 'license_activate' ),
            'permission_callback' => array( $this, 'admin_permission' ),
            'args'                => array(
                'license_key' => array( 'required' => true, 'sanitize_callback' => 'sanitize_text_field' ),
            ),
        ) );

        register_rest_route( self::NAMESPACE, '/license/deactivate', array(
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => array( $this, 'license_deactivate' ),
            'permission_callback' => array( $this, 'admin_permission' ),
        ) );

        register_rest_route( self::NAMESPACE, '/license/status', array(
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => array( $this, 'license_status' ),
            'permission_callback' => array( $this, 'admin_permission' ),
        ) );
    }

    /**
     * Admin permission check.
     */
    public function admin_permission( $request ) {
        // Check WP REST nonce
        $nonce = $request->get_header( 'X-WP-Nonce' );
        if ( $nonce && wp_verify_nonce( $nonce, 'wp_rest' ) ) {
            return current_user_can( 'manage_options' );
        }
        return current_user_can( 'manage_options' );
    }

    /**
     * POST /chat
     */
    public function handle_chat( $request ) {
        global $wpdb;

        $options       = LeadNest_DB::get_options();
        $session_token = $request->get_param( 'session_token' );
        $message       = $request->get_param( 'message' );
        $page_url      = $request->get_param( 'page_url' );
        $site_key      = $request->get_param( 'site_key' );

        if ( empty( $message ) ) {
            return new WP_Error( 'empty_message', 'Message cannot be empty.', array( 'status' => 400 ) );
        }

        // Find or create session
        $session = null;
        if ( ! empty( $session_token ) ) {
            $session = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT * FROM {$wpdb->prefix}leadnest_sessions WHERE session_token = %s LIMIT 1",
                    $session_token
                )
            );
        }

        if ( ! $session ) {
            // Create new session
            $session_token = $this->generate_session_token();
            $geo           = $this->get_geo_data();
            $user_agent    = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';
            $device_info   = $this->detect_device( $user_agent );

            $wpdb->insert(
                $wpdb->prefix . 'leadnest_sessions',
                array(
                    'session_token' => $session_token,
                    'site_key'      => $site_key ?? $options['site_key'],
                    'ip'            => $geo['ip'],
                    'country'       => $geo['country'],
                    'city'          => $geo['city'],
                    'device'        => $device_info['device'],
                    'browser'       => $device_info['browser'],
                    'page_url'      => $page_url,
                    'user_agent'    => $user_agent,
                    'created_at'    => current_time( 'mysql' ),
                    'updated_at'    => current_time( 'mysql' ),
                ),
                array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
            );

            $session_id = $wpdb->insert_id;
            $session = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT * FROM {$wpdb->prefix}leadnest_sessions WHERE id = %d",
                    $session_id
                )
            );
        }

        if ( ! $session ) {
            return new WP_Error( 'session_error', 'Unable to create session.', array( 'status' => 500 ) );
        }

        // Update page_url if changed
        if ( ! empty( $page_url ) && $page_url !== $session->page_url ) {
            $wpdb->update(
                $wpdb->prefix . 'leadnest_sessions',
                array( 'page_url' => $page_url, 'updated_at' => current_time( 'mysql' ) ),
                array( 'id' => $session->id ),
                array( '%s', '%s' ),
                array( '%d' )
            );
        }

        // Save user message
        $wpdb->insert(
            $wpdb->prefix . 'leadnest_chats',
            array(
                'session_id' => $session->id,
                'role'       => 'user',
                'content'    => $message,
                'created_at' => current_time( 'mysql' ),
            ),
            array( '%d', '%s', '%s', '%s' )
        );

        // If session is in live agent mode, don't call AI — agent replies manually
        if ( ! empty( $session->live_agent ) ) {
            return new WP_REST_Response(
                array(
                    'reply'         => '',
                    'session_token' => $session->session_token,
                    'live_agent'    => true,
                ),
                200
            );
        }

        // Check for live agent keyword triggers
        if ( ! empty( $options['live_agent_enabled'] ) ) {
            $keywords   = array_map( 'trim', explode( ',', strtolower( $options['live_agent_keywords'] ?? '' ) ) );
            $msg_lower  = strtolower( $message );
            $triggered  = false;

            foreach ( $keywords as $kw ) {
                if ( ! empty( $kw ) && strpos( $msg_lower, $kw ) !== false ) {
                    $triggered = true;
                    break;
                }
            }

            if ( $triggered ) {
                $handoff_reply = "I'll connect you with a team member. Please hold on — someone will be with you shortly.";

                $wpdb->insert(
                    $wpdb->prefix . 'leadnest_chats',
                    array( 'session_id' => $session->id, 'role' => 'assistant', 'content' => $handoff_reply, 'created_at' => current_time( 'mysql' ) ),
                    array( '%d', '%s', '%s', '%s' )
                );

                $wpdb->update(
                    $wpdb->prefix . 'leadnest_sessions',
                    array( 'live_agent' => 1, 'updated_at' => current_time( 'mysql' ) ),
                    array( 'id' => $session->id ),
                    array( '%d', '%s' ),
                    array( '%d' )
                );

                // Notify admin
                $admin_email = $options['notification_email'] ?: get_option( 'admin_email' );
                if ( $admin_email ) {
                    $subject = '[LeadNest] Live agent requested — Session #' . $session->id;
                    $body    = "A visitor has requested to speak with a human agent.\n\n";
                    $body   .= "Session: #" . $session->id . "\n";
                    $body   .= "Page: " . $session->page_url . "\n";
                    $body   .= "Message: " . $message . "\n\n";
                    $body   .= "Take over: " . admin_url( 'admin.php?page=leadnest-live-agent' ) . "\n";
                    wp_mail( $admin_email, $subject, $body );
                }

                return new WP_REST_Response(
                    array(
                        'reply'         => $handoff_reply,
                        'session_token' => $session->session_token,
                        'live_agent'    => true,
                    ),
                    200
                );
            }
        }

        // Get conversation history
        $max_history  = isset( $options['max_history'] ) ? absint( $options['max_history'] ) : 20;
        $history      = $this->get_conversation_history( $session->id, $max_history );

        // Build system prompt
        $system_prompt = $this->build_system_prompt( $options, $site_key ?? $options['site_key'] );

        // Call AI
        $ai_response = $this->call_ai( $options, $system_prompt, $history );

        if ( is_wp_error( $ai_response ) ) {
            return new WP_REST_Response(
                array(
                    'reply'         => 'I apologize, I am experiencing technical difficulties. Please try again in a moment.',
                    'session_token' => $session->session_token,
                    'error'         => true,
                ),
                200
            );
        }

        $reply = $ai_response;

        // Save assistant response
        $wpdb->insert(
            $wpdb->prefix . 'leadnest_chats',
            array(
                'session_id' => $session->id,
                'role'       => 'assistant',
                'content'    => $reply,
                'created_at' => current_time( 'mysql' ),
            ),
            array( '%d', '%s', '%s', '%s' )
        );

        // Check for missed questions
        $this->check_missed_question( $reply, $message, $session, $options );

        // Lead extraction
        if ( ! empty( $options['lead_capture_enabled'] ) ) {
            $this->maybe_extract_lead( $session, $options, $site_key ?? $options['site_key'] );
        }

        return new WP_REST_Response(
            array(
                'reply'         => $reply,
                'session_token' => $session->session_token,
            ),
            200
        );
    }

    /**
     * POST /session
     */
    public function handle_session( $request ) {
        global $wpdb;

        $site_key = $request->get_param( 'site_key' );
        $page_url = $request->get_param( 'page_url' );

        $options = LeadNest_DB::get_options();
        if ( empty( $site_key ) ) {
            $site_key = $options['site_key'];
        }

        $user_agent  = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';
        $device_info = $this->detect_device( $user_agent );
        $geo         = $this->get_geo_data();

        $session_token = $this->generate_session_token();

        $wpdb->insert(
            $wpdb->prefix . 'leadnest_sessions',
            array(
                'session_token' => $session_token,
                'site_key'      => $site_key,
                'ip'            => $geo['ip'],
                'country'       => $geo['country'],
                'city'          => $geo['city'],
                'device'        => $device_info['device'],
                'browser'       => $device_info['browser'],
                'page_url'      => $page_url,
                'user_agent'    => $user_agent,
                'created_at'    => current_time( 'mysql' ),
                'updated_at'    => current_time( 'mysql' ),
            ),
            array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
        );

        return new WP_REST_Response(
            array( 'session_token' => $session_token ),
            200
        );
    }

    /**
     * GET /leads
     */
    public function get_leads( $request ) {
        global $wpdb;

        $status   = $request->get_param( 'status' );
        $per_page = absint( $request->get_param( 'per_page' ) ?: 20 );
        $page     = absint( $request->get_param( 'page' ) ?: 1 );
        $offset   = ( $page - 1 ) * $per_page;

        $where = '';
        $args  = array();

        if ( ! empty( $status ) && in_array( $status, array( 'new', 'contacted', 'qualified', 'closed' ), true ) ) {
            $where  = 'WHERE status = %s';
            $args[] = $status;
        }

        $args[] = $per_page;
        $args[] = $offset;

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $leads = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}leadnest_leads {$where} ORDER BY created_at DESC LIMIT %d OFFSET %d",
                ...$args
            )
        );

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $total = $wpdb->get_var(
            empty( $where )
                ? "SELECT COUNT(*) FROM {$wpdb->prefix}leadnest_leads"
                : $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}leadnest_leads {$where}", $status )
        );

        return new WP_REST_Response(
            array(
                'leads'      => $leads,
                'total'      => (int) $total,
                'page'       => $page,
                'per_page'   => $per_page,
                'total_pages'=> (int) ceil( $total / $per_page ),
            ),
            200
        );
    }

    /**
     * POST /leads/{id}/status
     */
    public function update_lead_status( $request ) {
        global $wpdb;

        $id     = absint( $request->get_param( 'id' ) );
        $status = sanitize_text_field( $request->get_param( 'status' ) );

        if ( ! in_array( $status, array( 'new', 'contacted', 'qualified', 'closed' ), true ) ) {
            return new WP_Error( 'invalid_status', 'Invalid status value.', array( 'status' => 400 ) );
        }

        $updated = $wpdb->update(
            $wpdb->prefix . 'leadnest_leads',
            array(
                'status'     => $status,
                'updated_at' => current_time( 'mysql' ),
            ),
            array( 'id' => $id ),
            array( '%s', '%s' ),
            array( '%d' )
        );

        if ( false === $updated ) {
            return new WP_Error( 'update_failed', 'Failed to update lead status.', array( 'status' => 500 ) );
        }

        return new WP_REST_Response( array( 'success' => true, 'status' => $status ), 200 );
    }

    /**
     * POST /leads/{id}/notes
     */
    public function update_lead_notes( $request ) {
        global $wpdb;

        $id    = absint( $request->get_param( 'id' ) );
        $notes = sanitize_textarea_field( $request->get_param( 'notes' ) );

        $updated = $wpdb->update(
            $wpdb->prefix . 'leadnest_leads',
            array(
                'notes'      => $notes,
                'updated_at' => current_time( 'mysql' ),
            ),
            array( 'id' => $id ),
            array( '%s', '%s' ),
            array( '%d' )
        );

        if ( false === $updated ) {
            return new WP_Error( 'update_failed', 'Failed to update notes.', array( 'status' => 500 ) );
        }

        return new WP_REST_Response( array( 'success' => true ), 200 );
    }

    /**
     * DELETE /leads/{id}
     */
    public function delete_lead( $request ) {
        global $wpdb;
        $id = absint( $request->get_param( 'id' ) );
        $wpdb->delete( $wpdb->prefix . 'leadnest_leads', array( 'id' => $id ), array( '%d' ) );
        return new WP_REST_Response( array( 'success' => true ), 200 );
    }

    /**
     * GET /sessions
     */
    public function get_sessions( $request ) {
        global $wpdb;

        $per_page = absint( $request->get_param( 'per_page' ) ?: 20 );
        $page     = absint( $request->get_param( 'page' ) ?: 1 );
        $offset   = ( $page - 1 ) * $per_page;
        $search   = sanitize_text_field( $request->get_param( 'search' ) ?: '' );

        if ( ! empty( $search ) ) {
            $like     = '%' . $wpdb->esc_like( $search ) . '%';
            $sessions = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT s.*, (SELECT COUNT(*) FROM {$wpdb->prefix}leadnest_chats c WHERE c.session_id = s.id) as message_count
                     FROM {$wpdb->prefix}leadnest_sessions s
                     WHERE s.ip LIKE %s OR s.country LIKE %s
                     ORDER BY s.created_at DESC LIMIT %d OFFSET %d",
                    $like, $like, $per_page, $offset
                )
            );
            $total = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT COUNT(*) FROM {$wpdb->prefix}leadnest_sessions WHERE ip LIKE %s OR country LIKE %s",
                    $like, $like
                )
            );
        } else {
            $sessions = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT s.*, (SELECT COUNT(*) FROM {$wpdb->prefix}leadnest_chats c WHERE c.session_id = s.id) as message_count
                     FROM {$wpdb->prefix}leadnest_sessions s
                     ORDER BY s.created_at DESC LIMIT %d OFFSET %d",
                    $per_page, $offset
                )
            );
            $total = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}leadnest_sessions" );
        }

        return new WP_REST_Response(
            array(
                'sessions'    => $sessions,
                'total'       => (int) $total,
                'page'        => $page,
                'per_page'    => $per_page,
                'total_pages' => (int) ceil( $total / $per_page ),
            ),
            200
        );
    }

    /**
     * DELETE /sessions/{id}
     */
    public function delete_session( $request ) {
        global $wpdb;
        $id = absint( $request->get_param( 'id' ) );
        $wpdb->delete( $wpdb->prefix . 'leadnest_chats', array( 'session_id' => $id ), array( '%d' ) );
        $wpdb->delete( $wpdb->prefix . 'leadnest_sessions', array( 'id' => $id ), array( '%d' ) );
        return new WP_REST_Response( array( 'success' => true ), 200 );
    }

    /**
     * GET /widget.js
     */
    public function serve_widget_js( $request ) {
        $site_key = sanitize_text_field( $request->get_param( 'key' ) ?: '' );
        $file     = LEADNEST_PATH . 'widget/leadnest-widget.js';

        if ( ! file_exists( $file ) ) {
            return new WP_Error( 'widget_not_found', 'Widget file not found.', array( 'status' => 404 ) );
        }

        $js = file_get_contents( $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

        // Inject site key and API base if needed (the widget reads its own src, but we can also inject)
        header( 'Content-Type: application/javascript; charset=utf-8' );
        header( 'Cache-Control: public, max-age=3600' );
        echo $js; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        exit;
    }

    /**
     * GET /widget-config
     */
    public function get_widget_config( $request ) {
        $site_key = sanitize_text_field( $request->get_param( 'key' ) ?: '' );
        $options  = LeadNest_DB::get_options();
        $colors   = LeadNest_DB::resolve_colors( $options );

        return new WP_REST_Response(
            array(
                'bot_name'     => esc_html( $options['bot_name'] ),
                'primary_color'=> esc_attr( $colors['primary'] ),
                'text_color'   => esc_attr( $colors['text'] ),
                'greeting'     => esc_html( $options['greeting_message'] ),
                'placeholder'  => esc_attr( $options['input_placeholder'] ),
                'show_footer'  => (bool) $options['show_footer'],
                'footer_text'  => esc_html( $options['footer_text'] ),
                'site_key'     => esc_attr( $site_key ?: $options['site_key'] ),
                'rest_url'     => esc_url_raw( rest_url( 'leadnest/v1' ) ),
            ),
            200
        );
    }

    /**
     * POST /test-connection
     */
    public function test_connection( $request ) {
        $options  = LeadNest_DB::get_options();
        $provider = $request->get_param( 'provider' ) ?: $options['ai_provider'];
        $provider = sanitize_text_field( $provider );

        $test_messages = array(
            array( 'role' => 'user', 'content' => 'Say "Connection successful" and nothing else.' ),
        );

        $result = $this->call_ai( $options, 'You are a test assistant.', $test_messages );

        if ( is_wp_error( $result ) ) {
            return new WP_REST_Response(
                array( 'success' => false, 'message' => $result->get_error_message() ),
                200
            );
        }

        return new WP_REST_Response(
            array( 'success' => true, 'message' => $result ),
            200
        );
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    /**
     * Generate a unique session token.
     */
    private function generate_session_token() {
        return bin2hex( random_bytes( 32 ) );
    }

    /**
     * Get geo data for the current request IP using ip-api.com.
     */
    private function get_geo_data() {
        $ip = $this->get_client_ip();

        $result = array(
            'ip'      => $ip,
            'country' => '',
            'city'    => '',
        );

        // Skip geo lookup for private/local IPs
        if ( empty( $ip ) || $this->is_private_ip( $ip ) ) {
            return $result;
        }

        $response = wp_remote_get(
            'http://ip-api.com/json/' . rawurlencode( $ip ) . '?fields=country,city',
            array( 'timeout' => 5 )
        );

        if ( ! is_wp_error( $response ) && wp_remote_retrieve_response_code( $response ) === 200 ) {
            $body = json_decode( wp_remote_retrieve_body( $response ), true );
            if ( is_array( $body ) ) {
                $result['country'] = isset( $body['country'] ) ? sanitize_text_field( $body['country'] ) : '';
                $result['city']    = isset( $body['city'] )    ? sanitize_text_field( $body['city'] )    : '';
            }
        }

        return $result;
    }

    /**
     * Get the real client IP address.
     */
    private function get_client_ip() {
        $headers = array(
            'HTTP_CF_CONNECTING_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
            'REMOTE_ADDR',
        );

        foreach ( $headers as $header ) {
            if ( ! empty( $_SERVER[ $header ] ) ) {
                $ip = sanitize_text_field( wp_unslash( $_SERVER[ $header ] ) );
                // X-Forwarded-For can contain multiple IPs
                if ( strpos( $ip, ',' ) !== false ) {
                    $ips = explode( ',', $ip );
                    $ip  = trim( $ips[0] );
                }
                if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
                    return $ip;
                }
            }
        }

        return '';
    }

    /**
     * Check if IP is private/local.
     */
    private function is_private_ip( $ip ) {
        return ! filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        );
    }

    /**
     * Detect device type and browser from user agent.
     */
    private function detect_device( $user_agent ) {
        $device  = 'Desktop';
        $browser = 'Unknown';

        if ( empty( $user_agent ) ) {
            return array( 'device' => $device, 'browser' => $browser );
        }

        // Device detection
        if ( preg_match( '/Mobile|Android|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i', $user_agent ) ) {
            $device = preg_match( '/iPad/i', $user_agent ) ? 'Tablet' : 'Mobile';
        } elseif ( preg_match( '/Tablet|iPad/i', $user_agent ) ) {
            $device = 'Tablet';
        }

        // Browser detection
        if ( preg_match( '/Edg\//i', $user_agent ) ) {
            $browser = 'Edge';
        } elseif ( preg_match( '/OPR\//i', $user_agent ) ) {
            $browser = 'Opera';
        } elseif ( preg_match( '/Chrome\//i', $user_agent ) ) {
            $browser = 'Chrome';
        } elseif ( preg_match( '/Firefox\//i', $user_agent ) ) {
            $browser = 'Firefox';
        } elseif ( preg_match( '/Safari\//i', $user_agent ) ) {
            $browser = 'Safari';
        } elseif ( preg_match( '/MSIE|Trident/i', $user_agent ) ) {
            $browser = 'Internet Explorer';
        }

        return array( 'device' => $device, 'browser' => $browser );
    }

    /**
     * Get conversation history for a session, with rolling summarization
     * when conversation exceeds the max_history limit.
     */
    private function get_conversation_history( $session_id, $max_history ) {
        global $wpdb;

        $limit = absint( $max_history ) * 2; // user + assistant pairs

        // Get total message count for this session
        $total_messages = (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}leadnest_chats WHERE session_id = %d",
                $session_id
            )
        );

        // Get the most recent messages
        $rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT role, content FROM {$wpdb->prefix}leadnest_chats
                 WHERE session_id = %d
                 ORDER BY created_at DESC
                 LIMIT %d",
                $session_id,
                $limit
            )
        );

        // Reverse to chronological order
        $rows = array_reverse( $rows );

        $messages = array();

        // If there are older messages beyond what we're including, build a summary
        if ( $total_messages > $limit ) {
            $older_count = $total_messages - $limit;
            $older_rows  = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT role, content FROM {$wpdb->prefix}leadnest_chats
                     WHERE session_id = %d
                     ORDER BY created_at ASC
                     LIMIT %d",
                    $session_id,
                    min( $older_count, 20 ) // Summarize up to 20 older messages
                )
            );

            if ( ! empty( $older_rows ) ) {
                $summary_parts = array();
                foreach ( $older_rows as $old ) {
                    $snippet         = wp_trim_words( $old->content, 20, '...' );
                    $summary_parts[] = ucfirst( $old->role ) . ': ' . $snippet;
                }

                $summary_text = '[Summary of earlier conversation (' . $older_count . ' messages)]: '
                    . implode( ' | ', $summary_parts );

                // Inject summary as a user message at the start
                $messages[] = array(
                    'role'    => 'user',
                    'content' => $summary_text,
                );
                $messages[] = array(
                    'role'    => 'assistant',
                    'content' => 'Understood, I have context from our earlier conversation. How can I continue helping you?',
                );
            }
        }

        foreach ( $rows as $row ) {
            $messages[] = array(
                'role'    => $row->role,
                'content' => $row->content,
            );
        }

        return $messages;
    }

    /**
     * Build the system prompt with Q&A pairs and knowledge base.
     */
    private function build_system_prompt( $options, $site_key ) {
        global $wpdb;

        $base_prompt = isset( $options['system_prompt'] ) ? $options['system_prompt'] : '';

        // Inject Q&A pairs
        $qa_pairs = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT question, answer FROM {$wpdb->prefix}leadnest_qa
                 WHERE site_key = %s AND active = 1
                 ORDER BY use_count DESC
                 LIMIT 50",
                $site_key
            )
        );

        if ( ! empty( $qa_pairs ) ) {
            $qa_text = "\n\n## Frequently Asked Questions\nUse the following Q&A pairs to answer questions:\n\n";
            foreach ( $qa_pairs as $qa ) {
                $qa_text .= "Q: {$qa->question}\nA: {$qa->answer}\n\n";
            }
            $base_prompt .= $qa_text;
        }

        // Inject knowledge base (most recent/active entries, limit content)
        $knowledge = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT page_title, content FROM {$wpdb->prefix}leadnest_knowledge
                 WHERE site_key = %s AND active = 1
                 ORDER BY last_crawled DESC
                 LIMIT 5",
                $site_key
            )
        );

        if ( ! empty( $knowledge ) ) {
            $kb_text = "\n\n## Website Knowledge Base\nUse this information to answer questions about the website:\n\n";
            foreach ( $knowledge as $kb ) {
                $content  = wp_trim_words( $kb->content, 300, '...' );
                $kb_text .= "### {$kb->page_title}\n{$content}\n\n";
            }
            $base_prompt .= $kb_text;
        }

        return $base_prompt;
    }

    /**
     * Call the configured AI provider.
     *
     * @param array  $options
     * @param string $system_prompt
     * @param array  $messages
     * @return string|WP_Error
     */
    private function call_ai( $options, $system_prompt, $messages ) {
        $provider = isset( $options['ai_provider'] ) ? $options['ai_provider'] : 'anthropic';

        if ( $provider === 'openai' ) {
            return $this->call_openai( $options, $system_prompt, $messages );
        }

        return $this->call_anthropic( $options, $system_prompt, $messages );
    }

    /**
     * Call Anthropic Claude API.
     */
    private function call_anthropic( $options, $system_prompt, $messages ) {
        $api_key = isset( $options['api_key_anthropic'] ) ? $options['api_key_anthropic'] : '';
        $model   = isset( $options['model_anthropic'] ) ? $options['model_anthropic'] : 'claude-3-5-haiku-20241022';

        if ( empty( $api_key ) ) {
            return new WP_Error( 'no_api_key', 'Anthropic API key is not configured.' );
        }

        // Build messages for Anthropic (no system in messages array)
        $anthropic_messages = array();
        foreach ( $messages as $msg ) {
            if ( $msg['role'] === 'user' || $msg['role'] === 'assistant' ) {
                $anthropic_messages[] = array(
                    'role'    => $msg['role'],
                    'content' => $msg['content'],
                );
            }
        }

        // Ensure we start with a user message
        if ( empty( $anthropic_messages ) || $anthropic_messages[0]['role'] !== 'user' ) {
            return new WP_Error( 'invalid_messages', 'Messages must start with a user message.' );
        }

        $body = wp_json_encode( array(
            'model'      => $model,
            'max_tokens' => 1024,
            'system'     => array(
                array(
                    'type'          => 'text',
                    'text'          => $system_prompt,
                    'cache_control' => array( 'type' => 'ephemeral' ),
                ),
            ),
            'messages'   => $anthropic_messages,
        ) );

        $response = wp_remote_post(
            'https://api.anthropic.com/v1/messages',
            array(
                'timeout' => 60,
                'headers' => array(
                    'Content-Type'      => 'application/json',
                    'anthropic-version' => '2023-06-01',
                    'x-api-key'         => $api_key,
                    'anthropic-beta'    => 'prompt-caching-2024-07-31',
                ),
                'body'    => $body,
            )
        );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $code = wp_remote_retrieve_response_code( $response );
        $data = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( $code !== 200 ) {
            $error_msg = isset( $data['error']['message'] ) ? $data['error']['message'] : 'Unknown Anthropic API error.';
            return new WP_Error( 'anthropic_error', $error_msg );
        }

        if ( isset( $data['content'][0]['text'] ) ) {
            return $data['content'][0]['text'];
        }

        return new WP_Error( 'anthropic_parse_error', 'Could not parse Anthropic response.' );
    }

    /**
     * Call OpenAI API.
     */
    private function call_openai( $options, $system_prompt, $messages ) {
        $api_key = isset( $options['api_key_openai'] ) ? $options['api_key_openai'] : '';
        $model   = isset( $options['model_openai'] ) ? $options['model_openai'] : 'gpt-4o-mini';

        if ( empty( $api_key ) ) {
            return new WP_Error( 'no_api_key', 'OpenAI API key is not configured.' );
        }

        // Build OpenAI messages with system first
        $openai_messages = array(
            array( 'role' => 'system', 'content' => $system_prompt ),
        );

        foreach ( $messages as $msg ) {
            if ( $msg['role'] === 'user' || $msg['role'] === 'assistant' ) {
                $openai_messages[] = array(
                    'role'    => $msg['role'],
                    'content' => $msg['content'],
                );
            }
        }

        $body = wp_json_encode( array(
            'model'       => $model,
            'messages'    => $openai_messages,
            'max_tokens'  => 1024,
            'temperature' => 0.7,
        ) );

        $response = wp_remote_post(
            'https://api.openai.com/v1/chat/completions',
            array(
                'timeout' => 60,
                'headers' => array(
                    'Content-Type'  => 'application/json',
                    'Authorization' => 'Bearer ' . $api_key,
                ),
                'body'    => $body,
            )
        );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $code = wp_remote_retrieve_response_code( $response );
        $data = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( $code !== 200 ) {
            $error_msg = isset( $data['error']['message'] ) ? $data['error']['message'] : 'Unknown OpenAI API error.';
            return new WP_Error( 'openai_error', $error_msg );
        }

        if ( isset( $data['choices'][0]['message']['content'] ) ) {
            return $data['choices'][0]['message']['content'];
        }

        return new WP_Error( 'openai_parse_error', 'Could not parse OpenAI response.' );
    }

    /**
     * Check if the bot reply indicates a missed/unanswered question.
     */
    private function check_missed_question( $reply, $user_message, $session, $options ) {
        global $wpdb;

        $reply_lower = strtolower( $reply );
        $is_missed   = false;

        foreach ( $this->missed_phrases as $phrase ) {
            if ( strpos( $reply_lower, $phrase ) !== false ) {
                $is_missed = true;
                break;
            }
        }

        if ( ! $is_missed ) {
            return;
        }

        $site_key = $session->site_key;

        // Check if this question was already logged
        $existing = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT id, ask_count FROM {$wpdb->prefix}leadnest_missed_questions
                 WHERE site_key = %s AND question = %s AND resolved = 0
                 LIMIT 1",
                $site_key,
                $user_message
            )
        );

        if ( $existing ) {
            $wpdb->update(
                $wpdb->prefix . 'leadnest_missed_questions',
                array( 'ask_count' => $existing->ask_count + 1 ),
                array( 'id' => $existing->id ),
                array( '%d' ),
                array( '%d' )
            );
        } else {
            $wpdb->insert(
                $wpdb->prefix . 'leadnest_missed_questions',
                array(
                    'site_key'   => $site_key,
                    'question'   => $user_message,
                    'bot_reply'  => $reply,
                    'session_id' => $session->id,
                    'ask_count'  => 1,
                    'resolved'   => 0,
                    'created_at' => current_time( 'mysql' ),
                ),
                array( '%s', '%s', '%s', '%d', '%d', '%d', '%s' )
            );
        }
    }

    /**
     * Scan recent conversation for lead data and create/update a lead record.
     */
    private function maybe_extract_lead( $session, $options, $site_key ) {
        global $wpdb;

        // Check if lead already fully captured for this session
        $existing_lead = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}leadnest_leads WHERE session_id = %d LIMIT 1",
                $session->id
            )
        );

        // Get recent messages for extraction
        $messages = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT role, content FROM {$wpdb->prefix}leadnest_chats
                 WHERE session_id = %d
                 ORDER BY created_at DESC
                 LIMIT 20",
                $session->id
            )
        );

        $full_text = '';
        $first_user_message = '';

        foreach ( array_reverse( $messages ) as $msg ) {
            if ( $msg->role === 'user' ) {
                $full_text .= ' ' . $msg->content;
                if ( empty( $first_user_message ) ) {
                    $first_user_message = $msg->content;
                }
            }
        }

        // Extract email
        $email = '';
        if ( preg_match( '/[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}/', $full_text, $matches ) ) {
            $email = sanitize_email( $matches[0] );
        }

        // Extract phone (digits 7-15 with common separators)
        $phone = '';
        if ( preg_match( '/(?:\+?[\d\s\-\.\(\)]{7,15}\d)/', $full_text, $matches ) ) {
            $phone = sanitize_text_field( trim( $matches[0] ) );
        }

        // Extract name (look for patterns like "my name is X" or "I'm X" or "I am X")
        $name = '';
        if ( preg_match( '/(?:my name is|i\'?m|i am|call me)\s+([A-Z][a-z]+(?:\s+[A-Z][a-z]+)?)/i', $full_text, $matches ) ) {
            $name = sanitize_text_field( $matches[1] );
        }

        // Only create/update lead if we have something useful
        if ( empty( $email ) && empty( $phone ) && empty( $name ) ) {
            return;
        }

        if ( $existing_lead ) {
            // Update with any new info found
            $update_data = array( 'updated_at' => current_time( 'mysql' ) );
            $update_fmt  = array( '%s' );

            if ( ! empty( $email ) && empty( $existing_lead->email ) ) {
                $update_data['email'] = $email;
                $update_fmt[]         = '%s';
            }
            if ( ! empty( $phone ) && empty( $existing_lead->phone ) ) {
                $update_data['phone'] = $phone;
                $update_fmt[]         = '%s';
            }
            if ( ! empty( $name ) && empty( $existing_lead->name ) ) {
                $update_data['name'] = $name;
                $update_fmt[]        = '%s';
            }

            if ( count( $update_data ) > 1 ) {
                $wpdb->update(
                    $wpdb->prefix . 'leadnest_leads',
                    $update_data,
                    array( 'id' => $existing_lead->id ),
                    $update_fmt,
                    array( '%d' )
                );
            }
        } else {
            // Create new lead
            $wpdb->insert(
                $wpdb->prefix . 'leadnest_leads',
                array(
                    'session_id'  => $session->id,
                    'site_key'    => $site_key,
                    'name'        => $name,
                    'email'       => $email,
                    'phone'       => $phone,
                    'need'        => wp_trim_words( $first_user_message, 30, '...' ),
                    'source_page' => $session->page_url,
                    'status'      => 'new',
                    'created_at'  => current_time( 'mysql' ),
                    'updated_at'  => current_time( 'mysql' ),
                ),
                array( '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
            );

            $lead_id = $wpdb->insert_id;

            // Send notification email
            $notification_email = isset( $options['notification_email'] ) ? $options['notification_email'] : get_option( 'admin_email' );
            if ( ! empty( $notification_email ) && ! empty( $email ) ) {
                $this->send_lead_notification( $notification_email, $name, $email, $phone, $first_user_message, $session );
            }
        }
    }

    // -------------------------------------------------------------------------
    // v1.7 — Booking endpoints
    // -------------------------------------------------------------------------

    /**
     * GET /bookings (admin)
     */
    public function get_bookings( $request ) {
        global $wpdb;

        $status   = sanitize_text_field( $request->get_param( 'status' ) ?: '' );
        $per_page = absint( $request->get_param( 'per_page' ) ?: 20 );
        $page     = absint( $request->get_param( 'page' ) ?: 1 );
        $offset   = ( $page - 1 ) * $per_page;

        if ( ! empty( $status ) ) {
            $bookings = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM {$wpdb->prefix}leadnest_bookings WHERE status = %s ORDER BY booking_date DESC LIMIT %d OFFSET %d",
                    $status, $per_page, $offset
                )
            );
            $total = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}leadnest_bookings WHERE status = %s", $status ) );
        } else {
            $bookings = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM {$wpdb->prefix}leadnest_bookings ORDER BY booking_date DESC LIMIT %d OFFSET %d",
                    $per_page, $offset
                )
            );
            $total = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}leadnest_bookings" );
        }

        return new WP_REST_Response( array(
            'bookings'    => $bookings,
            'total'       => (int) $total,
            'page'        => $page,
            'per_page'    => $per_page,
            'total_pages' => (int) ceil( $total / $per_page ),
        ), 200 );
    }

    /**
     * POST /bookings (public — from widget)
     */
    public function create_booking( $request ) {
        global $wpdb;

        $options      = LeadNest_DB::get_options();
        $site_key     = $request->get_param( 'site_key' ) ?: $options['site_key'];
        $booking_date = sanitize_text_field( $request->get_param( 'booking_date' ) );
        $booking_time = sanitize_text_field( $request->get_param( 'booking_time' ) );

        // Basic date/time validation
        if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $booking_date ) ) {
            return new WP_Error( 'invalid_date', 'Invalid booking date format (YYYY-MM-DD required).', array( 'status' => 400 ) );
        }
        if ( ! preg_match( '/^\d{2}:\d{2}(:\d{2})?$/', $booking_time ) ) {
            return new WP_Error( 'invalid_time', 'Invalid booking time format (HH:MM required).', array( 'status' => 400 ) );
        }

        $name         = sanitize_text_field( $request->get_param( 'name' )         ?: '' );
        $email        = sanitize_email(      $request->get_param( 'email' )        ?: '' );
        $phone        = sanitize_text_field( $request->get_param( 'phone' )        ?: '' );
        $service_type = sanitize_text_field( $request->get_param( 'service_type' ) ?: '' );
        $session_id   = absint( $request->get_param( 'session_id' ) ?: 0 );

        $wpdb->insert(
            $wpdb->prefix . 'leadnest_bookings',
            array(
                'site_key'     => $site_key,
                'session_id'   => $session_id ?: null,
                'name'         => $name,
                'email'        => $email,
                'phone'        => $phone,
                'service_type' => $service_type,
                'booking_date' => $booking_date,
                'booking_time' => $booking_time,
                'duration_mins'=> absint( $options['booking_duration'] ),
                'status'       => 'pending',
                'created_at'   => current_time( 'mysql' ),
            ),
            array( '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s' )
        );

        $booking_id = $wpdb->insert_id;

        // Send confirmation email
        if ( ! empty( $email ) ) {
            $subject = sprintf( '[LeadNest] Booking Confirmed — %s %s', $booking_date, substr( $booking_time, 0, 5 ) );
            $body    = $options['booking_confirmation_message'] . "\n\n";
            $body   .= "Date: {$booking_date}\nTime: " . substr( $booking_time, 0, 5 ) . "\n";
            wp_mail( $email, $subject, $body );
        }

        // Notify admin
        $admin_email = $options['notification_email'] ?: get_option( 'admin_email' );
        if ( $admin_email ) {
            $admin_subject = sprintf( '[LeadNest] New Booking — %s on %s', $name ?: $email, $booking_date );
            $admin_body    = "New booking received.\n\nName: {$name}\nEmail: {$email}\nPhone: {$phone}\nDate: {$booking_date}\nTime: " . substr( $booking_time, 0, 5 ) . "\n\nView bookings: " . admin_url( 'admin.php?page=leadnest-bookings' );
            wp_mail( $admin_email, $admin_subject, $admin_body );
        }

        // Google Calendar sync
        if ( class_exists( 'LeadNest_GCal' ) && LeadNest_GCal::is_connected() ) {
            $booking_row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}leadnest_bookings WHERE id = %d", $booking_id ) );
            if ( $booking_row ) {
                $event_id = LeadNest_GCal::create_event( $booking_row, $options );
                if ( ! is_wp_error( $event_id ) ) {
                    $wpdb->update(
                        $wpdb->prefix . 'leadnest_bookings',
                        array( 'google_event_id' => $event_id ),
                        array( 'id' => $booking_id ),
                        array( '%s' ),
                        array( '%d' )
                    );
                }
            }
        }

        return new WP_REST_Response( array(
            'success'    => true,
            'booking_id' => $booking_id,
            'message'    => $options['booking_confirmation_message'],
        ), 200 );
    }

    /**
     * POST /bookings/{id}/status (admin)
     */
    public function update_booking_status( $request ) {
        global $wpdb;

        $id     = absint( $request->get_param( 'id' ) );
        $status = sanitize_text_field( $request->get_param( 'status' ) );

        $allowed = array( 'pending', 'confirmed', 'cancelled', 'completed' );
        if ( ! in_array( $status, $allowed, true ) ) {
            return new WP_Error( 'invalid_status', 'Invalid status.', array( 'status' => 400 ) );
        }

        $wpdb->update(
            $wpdb->prefix . 'leadnest_bookings',
            array( 'status' => $status ),
            array( 'id'     => $id ),
            array( '%s' ),
            array( '%d' )
        );

        return new WP_REST_Response( array( 'success' => true, 'status' => $status ), 200 );
    }

    /**
     * GET /available-slots?site_key=X&date=YYYY-MM-DD
     */
    public function get_available_slots( $request ) {
        global $wpdb;

        $options  = LeadNest_DB::get_options();
        $site_key = $request->get_param( 'site_key' ) ?: $options['site_key'];
        $date     = sanitize_text_field( $request->get_param( 'date' ) ?: date( 'Y-m-d' ) );

        if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) ) {
            return new WP_Error( 'invalid_date', 'Invalid date format.', array( 'status' => 400 ) );
        }

        $day_of_week = (int) date( 'w', strtotime( $date ) );

        // Get availability for that day
        $avail = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}leadnest_availability WHERE site_key = %s AND day_of_week = %d AND active = 1 LIMIT 1",
                $site_key, $day_of_week
            )
        );

        if ( ! $avail ) {
            return new WP_REST_Response( array( 'slots' => array(), 'date' => $date ), 200 );
        }

        $duration = absint( $options['booking_duration'] ) ?: 60;
        $buffer   = absint( $options['booking_buffer'] )   ?: 0;
        $max_day  = absint( $options['booking_max_per_day'] ) ?: 10;

        // Get existing bookings for this date
        $booked_times = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT booking_time FROM {$wpdb->prefix}leadnest_bookings WHERE site_key = %s AND booking_date = %s AND status != 'cancelled'",
                $site_key, $date
            )
        );

        if ( count( $booked_times ) >= $max_day ) {
            return new WP_REST_Response( array( 'slots' => array(), 'date' => $date, 'fully_booked' => true ), 200 );
        }

        // Generate available slots
        $start_ts    = strtotime( $date . ' ' . $avail->start_time );
        $end_ts      = strtotime( $date . ' ' . $avail->end_time );
        $slot_length = ( $duration + $buffer ) * 60;
        $slots       = array();

        for ( $ts = $start_ts; $ts + $duration * 60 <= $end_ts; $ts += $slot_length ) {
            $slot_time = date( 'H:i', $ts );
            if ( ! in_array( $slot_time . ':00', $booked_times, true ) && ! in_array( $slot_time, $booked_times, true ) ) {
                $slots[] = array(
                    'time'  => $slot_time,
                    'label' => date( 'g:i A', $ts ),
                );
            }
        }

        return new WP_REST_Response( array( 'slots' => $slots, 'date' => $date ), 200 );
    }

    // -------------------------------------------------------------------------
    // v1.8 — WhatsApp webhook
    // -------------------------------------------------------------------------

    /**
     * GET /whatsapp/webhook — Meta webhook verification challenge
     */
    public function whatsapp_verify_webhook( $request ) {
        $options        = LeadNest_DB::get_options();
        $verify_token   = $options['whatsapp_webhook_secret'];
        $mode           = sanitize_text_field( $request->get_param( 'hub_mode' )          ?: '' );
        $token          = sanitize_text_field( $request->get_param( 'hub_verify_token' )  ?: '' );
        $challenge      = sanitize_text_field( $request->get_param( 'hub_challenge' )     ?: '' );

        if ( 'subscribe' === $mode && hash_equals( $verify_token, $token ) ) {
            header( 'Content-Type: text/plain' );
            echo esc_html( $challenge );
            exit;
        }

        return new WP_Error( 'forbidden', 'Verification failed.', array( 'status' => 403 ) );
    }

    /**
     * POST /whatsapp/webhook — Receive incoming WhatsApp messages
     */
    public function whatsapp_receive_message( $request ) {
        $options = LeadNest_DB::get_options();

        if ( empty( $options['whatsapp_phone_id'] ) || empty( $options['whatsapp_token'] ) ) {
            return new WP_REST_Response( array( 'status' => 'not_configured' ), 200 );
        }

        $body = $request->get_json_params();
        if ( empty( $body ) ) {
            return new WP_REST_Response( array( 'status' => 'ok' ), 200 );
        }

        // Extract message from WhatsApp payload
        $entry   = $body['entry'][0] ?? null;
        $changes = $entry['changes'][0] ?? null;
        $value   = $changes['value'] ?? null;
        $message = $value['messages'][0] ?? null;

        if ( ! $message || $message['type'] !== 'text' ) {
            return new WP_REST_Response( array( 'status' => 'ok' ), 200 );
        }

        $from         = sanitize_text_field( $message['from'] );
        $user_message = sanitize_textarea_field( $message['text']['text'] ?? '' );

        if ( empty( $user_message ) ) {
            return new WP_REST_Response( array( 'status' => 'ok' ), 200 );
        }

        // Build or retrieve session for this WhatsApp number
        global $wpdb;

        $session = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}leadnest_sessions WHERE session_token = %s LIMIT 1",
                'wa_' . $from
            )
        );

        if ( ! $session ) {
            $wpdb->insert(
                $wpdb->prefix . 'leadnest_sessions',
                array(
                    'session_token' => 'wa_' . $from,
                    'site_key'      => $options['site_key'],
                    'ip'            => '',
                    'country'       => '',
                    'city'          => '',
                    'device'        => 'WhatsApp',
                    'browser'       => 'WhatsApp',
                    'page_url'      => '',
                    'user_agent'    => '',
                    'created_at'    => current_time( 'mysql' ),
                    'updated_at'    => current_time( 'mysql' ),
                ),
                array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
            );
            $session = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}leadnest_sessions WHERE id = %d", $wpdb->insert_id ) );
        }

        // Save user message
        $wpdb->insert(
            $wpdb->prefix . 'leadnest_chats',
            array( 'session_id' => $session->id, 'role' => 'user', 'content' => $user_message, 'created_at' => current_time( 'mysql' ) ),
            array( '%d', '%s', '%s', '%s' )
        );

        // Get AI response
        $history       = $this->get_conversation_history( $session->id, (int) $options['max_history'] );
        $system_prompt = $this->build_system_prompt( $options, $options['site_key'] );
        $reply         = $this->call_ai( $options, $system_prompt, $history );

        if ( is_wp_error( $reply ) ) {
            $reply = "I'm sorry, I'm having technical difficulties right now. Please try again in a moment.";
        }

        // Save reply
        $wpdb->insert(
            $wpdb->prefix . 'leadnest_chats',
            array( 'session_id' => $session->id, 'role' => 'assistant', 'content' => $reply, 'created_at' => current_time( 'mysql' ) ),
            array( '%d', '%s', '%s', '%s' )
        );

        // Send reply via WhatsApp Cloud API
        $wa_body = wp_json_encode( array(
            'messaging_product' => 'whatsapp',
            'to'                => $from,
            'type'              => 'text',
            'text'              => array( 'body' => $reply ),
        ) );

        wp_remote_post(
            'https://graph.facebook.com/v18.0/' . rawurlencode( $options['whatsapp_phone_id'] ) . '/messages',
            array(
                'timeout' => 15,
                'headers' => array(
                    'Authorization' => 'Bearer ' . $options['whatsapp_token'],
                    'Content-Type'  => 'application/json',
                ),
                'body' => $wa_body,
            )
        );

        // Lead extraction + missed question check
        $this->check_missed_question( $reply, $user_message, $session, $options );
        if ( ! empty( $options['lead_capture_enabled'] ) ) {
            $this->maybe_extract_lead( $session, $options, $options['site_key'] );
        }

        return new WP_REST_Response( array( 'status' => 'ok' ), 200 );
    }

    /**
     * GET /chat-poll — Widget polls for new messages during live agent mode
     */
    public function chat_poll( $request ) {
        global $wpdb;

        $session_token = $request->get_param( 'session_token' );
        $last_count    = absint( $request->get_param( 'last_count' ) );

        $session = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT id, live_agent FROM {$wpdb->prefix}leadnest_sessions WHERE session_token = %s LIMIT 1",
                $session_token
            )
        );

        if ( ! $session ) {
            return new WP_REST_Response( array( 'new_messages' => array(), 'live_agent' => false ), 200 );
        }

        $total_count = (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}leadnest_chats WHERE session_id = %d",
                $session->id
            )
        );

        $new_messages = array();
        if ( $total_count > $last_count ) {
            $new_count = $total_count - $last_count;
            $rows = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT role, content, created_at FROM {$wpdb->prefix}leadnest_chats
                     WHERE session_id = %d
                     ORDER BY created_at DESC
                     LIMIT %d",
                    $session->id,
                    $new_count
                )
            );

            $rows = array_reverse( $rows );
            foreach ( $rows as $row ) {
                $new_messages[] = array(
                    'role'    => $row->role,
                    'content' => $row->content,
                );
            }
        }

        return new WP_REST_Response( array(
            'new_messages' => $new_messages,
            'total_count'  => $total_count,
            'live_agent'   => (bool) $session->live_agent,
        ), 200 );
    }

    // -------------------------------------------------------------------------
    // v1.9.1 — Live Agent Takeover
    // -------------------------------------------------------------------------

    /**
     * POST /live-agent/takeover — Admin takes over a session
     */
    public function live_agent_takeover( $request ) {
        global $wpdb;

        $session_id = absint( $request->get_param( 'session_id' ) );
        if ( ! $session_id ) {
            return new WP_Error( 'invalid_session', 'Invalid session ID.', array( 'status' => 400 ) );
        }

        $wpdb->update(
            $wpdb->prefix . 'leadnest_sessions',
            array( 'live_agent' => 1, 'updated_at' => current_time( 'mysql' ) ),
            array( 'id' => $session_id ),
            array( '%d', '%s' ),
            array( '%d' )
        );

        return new WP_REST_Response( array( 'success' => true, 'message' => 'Session taken over by live agent.' ), 200 );
    }

    /**
     * POST /live-agent/release — Return session to AI
     */
    public function live_agent_release( $request ) {
        global $wpdb;

        $session_id = absint( $request->get_param( 'session_id' ) );
        if ( ! $session_id ) {
            return new WP_Error( 'invalid_session', 'Invalid session ID.', array( 'status' => 400 ) );
        }

        $wpdb->update(
            $wpdb->prefix . 'leadnest_sessions',
            array( 'live_agent' => 0, 'updated_at' => current_time( 'mysql' ) ),
            array( 'id' => $session_id ),
            array( '%d', '%s' ),
            array( '%d' )
        );

        return new WP_REST_Response( array( 'success' => true, 'message' => 'Session returned to AI.' ), 200 );
    }

    /**
     * POST /live-agent/reply — Admin sends a reply to a live session
     */
    public function live_agent_reply( $request ) {
        global $wpdb;

        $session_id = absint( $request->get_param( 'session_id' ) );
        $message    = sanitize_textarea_field( $request->get_param( 'message' ) );

        if ( ! $session_id || empty( $message ) ) {
            return new WP_Error( 'invalid_input', 'Session ID and message are required.', array( 'status' => 400 ) );
        }

        // Save the agent reply as an assistant message
        $wpdb->insert(
            $wpdb->prefix . 'leadnest_chats',
            array(
                'session_id' => $session_id,
                'role'       => 'assistant',
                'content'    => $message,
                'created_at' => current_time( 'mysql' ),
            ),
            array( '%d', '%s', '%s', '%s' )
        );

        $wpdb->update(
            $wpdb->prefix . 'leadnest_sessions',
            array( 'updated_at' => current_time( 'mysql' ) ),
            array( 'id' => $session_id ),
            array( '%s' ),
            array( '%d' )
        );

        return new WP_REST_Response( array( 'success' => true ), 200 );
    }

    /**
     * GET /live-agent/messages — Poll for new messages in a live session
     */
    public function live_agent_messages( $request ) {
        global $wpdb;

        $session_id = absint( $request->get_param( 'session_id' ) );
        $after      = sanitize_text_field( $request->get_param( 'after' ) ?: '' );

        if ( ! $session_id ) {
            return new WP_Error( 'invalid_session', 'Session ID required.', array( 'status' => 400 ) );
        }

        $where_after = '';
        $args        = array( $session_id );

        if ( ! empty( $after ) ) {
            $where_after = ' AND created_at > %s';
            $args[]      = $after;
        }

        $messages = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT id, role, content, created_at FROM {$wpdb->prefix}leadnest_chats
                 WHERE session_id = %d{$where_after}
                 ORDER BY created_at ASC
                 LIMIT 50",
                ...$args
            )
        );

        return new WP_REST_Response( array( 'messages' => $messages ), 200 );
    }

    // -------------------------------------------------------------------------
    // v1.8 — Facebook Messenger webhook
    // -------------------------------------------------------------------------

    /**
     * GET /messenger/webhook — Meta webhook verification challenge
     */
    public function messenger_verify_webhook( $request ) {
        $options      = LeadNest_DB::get_options();
        $verify_token = $options['messenger_webhook_secret'] ?? '';
        $mode         = sanitize_text_field( $request->get_param( 'hub_mode' )         ?: '' );
        $token        = sanitize_text_field( $request->get_param( 'hub_verify_token' ) ?: '' );
        $challenge    = sanitize_text_field( $request->get_param( 'hub_challenge' )    ?: '' );

        if ( 'subscribe' === $mode && ! empty( $verify_token ) && hash_equals( $verify_token, $token ) ) {
            header( 'Content-Type: text/plain' );
            echo esc_html( $challenge );
            exit;
        }

        return new WP_Error( 'forbidden', 'Verification failed.', array( 'status' => 403 ) );
    }

    /**
     * POST /messenger/webhook — Receive incoming Messenger messages
     */
    public function messenger_receive_message( $request ) {
        $options = LeadNest_DB::get_options();

        if ( empty( $options['messenger_page_id'] ) || empty( $options['messenger_page_token'] ) ) {
            return new WP_REST_Response( array( 'status' => 'not_configured' ), 200 );
        }

        $body = $request->get_json_params();
        if ( empty( $body ) || ( $body['object'] ?? '' ) !== 'page' ) {
            return new WP_REST_Response( array( 'status' => 'ok' ), 200 );
        }

        foreach ( $body['entry'] ?? array() as $entry ) {
            foreach ( $entry['messaging'] ?? array() as $event ) {
                if ( empty( $event['message']['text'] ) ) {
                    continue;
                }

                $sender_id    = sanitize_text_field( $event['sender']['id'] ?? '' );
                $user_message = sanitize_textarea_field( $event['message']['text'] );

                if ( empty( $sender_id ) || empty( $user_message ) ) {
                    continue;
                }

                // Skip echo messages (sent by the page itself)
                if ( ! empty( $event['message']['is_echo'] ) ) {
                    continue;
                }

                $this->process_messenger_message( $sender_id, $user_message, $options );
            }
        }

        return new WP_REST_Response( array( 'status' => 'ok' ), 200 );
    }

    /**
     * Process a single Messenger message: session, AI, reply.
     */
    private function process_messenger_message( $sender_id, $user_message, $options ) {
        global $wpdb;

        $session_token = 'fb_' . $sender_id;

        $session = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}leadnest_sessions WHERE session_token = %s LIMIT 1",
                $session_token
            )
        );

        if ( ! $session ) {
            $wpdb->insert(
                $wpdb->prefix . 'leadnest_sessions',
                array(
                    'session_token' => $session_token,
                    'site_key'      => $options['site_key'],
                    'ip'            => '',
                    'country'       => '',
                    'city'          => '',
                    'device'        => 'Messenger',
                    'browser'       => 'Messenger',
                    'page_url'      => '',
                    'user_agent'    => '',
                    'created_at'    => current_time( 'mysql' ),
                    'updated_at'    => current_time( 'mysql' ),
                ),
                array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
            );
            $session = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}leadnest_sessions WHERE id = %d", $wpdb->insert_id ) );
        }

        // Save user message
        $wpdb->insert(
            $wpdb->prefix . 'leadnest_chats',
            array( 'session_id' => $session->id, 'role' => 'user', 'content' => $user_message, 'created_at' => current_time( 'mysql' ) ),
            array( '%d', '%s', '%s', '%s' )
        );

        // Get AI response
        $history       = $this->get_conversation_history( $session->id, (int) $options['max_history'] );
        $system_prompt = $this->build_system_prompt( $options, $options['site_key'] );
        $reply         = $this->call_ai( $options, $system_prompt, $history );

        if ( is_wp_error( $reply ) ) {
            $reply = "I'm sorry, I'm having technical difficulties right now. Please try again in a moment.";
        }

        // Save reply
        $wpdb->insert(
            $wpdb->prefix . 'leadnest_chats',
            array( 'session_id' => $session->id, 'role' => 'assistant', 'content' => $reply, 'created_at' => current_time( 'mysql' ) ),
            array( '%d', '%s', '%s', '%s' )
        );

        // Send reply via Messenger Send API
        wp_remote_post(
            'https://graph.facebook.com/v18.0/me/messages?access_token=' . rawurlencode( $options['messenger_page_token'] ),
            array(
                'timeout' => 15,
                'headers' => array( 'Content-Type' => 'application/json' ),
                'body'    => wp_json_encode( array(
                    'recipient' => array( 'id' => $sender_id ),
                    'message'   => array( 'text' => $reply ),
                ) ),
            )
        );

        // Lead extraction + missed question check
        $this->check_missed_question( $reply, $user_message, $session, $options );
        if ( ! empty( $options['lead_capture_enabled'] ) ) {
            $this->maybe_extract_lead( $session, $options, $options['site_key'] );
        }
    }

    // -------------------------------------------------------------------------
    // v1.8 — Telegram webhook
    // -------------------------------------------------------------------------

    /**
     * POST /telegram/webhook — Receive incoming Telegram messages
     */
    public function telegram_receive_message( $request ) {
        $options = LeadNest_DB::get_options();

        if ( empty( $options['telegram_bot_token'] ) ) {
            return new WP_REST_Response( array( 'status' => 'not_configured' ), 200 );
        }

        $body = $request->get_json_params();
        if ( empty( $body ) ) {
            return new WP_REST_Response( array( 'status' => 'ok' ), 200 );
        }

        $message = $body['message'] ?? null;
        if ( ! $message || empty( $message['text'] ) ) {
            return new WP_REST_Response( array( 'status' => 'ok' ), 200 );
        }

        $chat_id      = $message['chat']['id'] ?? '';
        $user_message = sanitize_textarea_field( $message['text'] );

        if ( empty( $chat_id ) || empty( $user_message ) ) {
            return new WP_REST_Response( array( 'status' => 'ok' ), 200 );
        }

        // Skip /start command — send greeting instead
        if ( strpos( $user_message, '/start' ) === 0 ) {
            $user_message = 'Hello';
        }

        global $wpdb;

        $session_token = 'tg_' . $chat_id;

        $session = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}leadnest_sessions WHERE session_token = %s LIMIT 1",
                $session_token
            )
        );

        if ( ! $session ) {
            $tg_name = sanitize_text_field(
                trim( ( $message['from']['first_name'] ?? '' ) . ' ' . ( $message['from']['last_name'] ?? '' ) )
            );

            $wpdb->insert(
                $wpdb->prefix . 'leadnest_sessions',
                array(
                    'session_token' => $session_token,
                    'site_key'      => $options['site_key'],
                    'ip'            => '',
                    'country'       => '',
                    'city'          => '',
                    'device'        => 'Telegram',
                    'browser'       => 'Telegram',
                    'page_url'      => '',
                    'user_agent'    => $tg_name,
                    'created_at'    => current_time( 'mysql' ),
                    'updated_at'    => current_time( 'mysql' ),
                ),
                array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
            );
            $session = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}leadnest_sessions WHERE id = %d", $wpdb->insert_id ) );
        }

        // Save user message
        $wpdb->insert(
            $wpdb->prefix . 'leadnest_chats',
            array( 'session_id' => $session->id, 'role' => 'user', 'content' => $user_message, 'created_at' => current_time( 'mysql' ) ),
            array( '%d', '%s', '%s', '%s' )
        );

        // Get AI response
        $history       = $this->get_conversation_history( $session->id, (int) $options['max_history'] );
        $system_prompt = $this->build_system_prompt( $options, $options['site_key'] );
        $reply         = $this->call_ai( $options, $system_prompt, $history );

        if ( is_wp_error( $reply ) ) {
            $reply = "I'm sorry, I'm having technical difficulties right now. Please try again in a moment.";
        }

        // Save reply
        $wpdb->insert(
            $wpdb->prefix . 'leadnest_chats',
            array( 'session_id' => $session->id, 'role' => 'assistant', 'content' => $reply, 'created_at' => current_time( 'mysql' ) ),
            array( '%d', '%s', '%s', '%s' )
        );

        // Send reply via Telegram Bot API
        wp_remote_post(
            'https://api.telegram.org/bot' . rawurlencode( $options['telegram_bot_token'] ) . '/sendMessage',
            array(
                'timeout' => 15,
                'body'    => array(
                    'chat_id' => $chat_id,
                    'text'    => $reply,
                ),
            )
        );

        // Lead extraction + missed question check
        $this->check_missed_question( $reply, $user_message, $session, $options );
        if ( ! empty( $options['lead_capture_enabled'] ) ) {
            $this->maybe_extract_lead( $session, $options, $options['site_key'] );
        }

        return new WP_REST_Response( array( 'status' => 'ok' ), 200 );
    }

    // -------------------------------------------------------------------------
    // v2.0 — License endpoints
    // -------------------------------------------------------------------------

    /**
     * POST /license/activate
     */
    public function license_activate( $request ) {
        if ( ! class_exists( 'LeadNest_License' ) ) {
            return new WP_Error( 'not_available', 'License system not available.', array( 'status' => 500 ) );
        }

        $result = LeadNest_License::activate( $request->get_param( 'license_key' ) );
        if ( is_wp_error( $result ) ) {
            return $result;
        }

        return new WP_REST_Response( $result, 200 );
    }

    /**
     * POST /license/deactivate
     */
    public function license_deactivate( $request ) {
        if ( ! class_exists( 'LeadNest_License' ) ) {
            return new WP_Error( 'not_available', 'License system not available.', array( 'status' => 500 ) );
        }

        $result = LeadNest_License::deactivate();
        if ( is_wp_error( $result ) ) {
            return $result;
        }

        return new WP_REST_Response( $result, 200 );
    }

    /**
     * GET /license/status
     */
    public function license_status( $request ) {
        if ( ! class_exists( 'LeadNest_License' ) ) {
            return new WP_REST_Response( array( 'valid' => false, 'tier' => 'free' ), 200 );
        }

        return new WP_REST_Response( LeadNest_License::check(), 200 );
    }

    /**
     * Send new lead notification email.
     */
    private function send_lead_notification( $to, $name, $email, $phone, $need, $session ) {
        $subject = sprintf( '[LeadNest] New lead captured — %s', ! empty( $email ) ? $email : 'Unknown' );

        $message  = "A new lead has been captured by LeadNest.\n\n";
        $message .= "Name:    {$name}\n";
        $message .= "Email:   {$email}\n";
        $message .= "Phone:   {$phone}\n";
        $message .= "Need:    {$need}\n";
        $message .= "Page:    {$session->page_url}\n";
        $message .= "Country: {$session->country}\n";
        $message .= "Date:    " . current_time( 'mysql' ) . "\n\n";
        $message .= "View leads: " . admin_url( 'admin.php?page=leadnest-leads' ) . "\n";

        wp_mail( $to, $subject, $message );
    }
}
