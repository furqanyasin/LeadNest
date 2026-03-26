<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $wpdb;

$options  = LeadNest_DB::get_options();
$site_key = $options['site_key'];

// Active live agent sessions
$live_sessions = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT s.*, (SELECT COUNT(*) FROM {$wpdb->prefix}leadnest_chats c WHERE c.session_id = s.id) as msg_count
         FROM {$wpdb->prefix}leadnest_sessions s
         WHERE s.site_key = %s AND s.live_agent = 1
         ORDER BY s.updated_at DESC
         LIMIT 20",
        $site_key
    )
);

// Sessions needing attention (high uncertainty, not yet taken over)
$escalation_sessions = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT DISTINCT s.id, s.session_token, s.ip, s.country, s.device, s.page_url, s.created_at, s.live_agent,
                (SELECT COUNT(*) FROM {$wpdb->prefix}leadnest_chats c WHERE c.session_id = s.id) as msg_count,
                (SELECT m.ask_count FROM {$wpdb->prefix}leadnest_missed_questions m WHERE m.session_id = s.id AND m.resolved = 0 ORDER BY m.ask_count DESC LIMIT 1) as uncertainty_count
         FROM {$wpdb->prefix}leadnest_sessions s
         INNER JOIN {$wpdb->prefix}leadnest_missed_questions mq ON mq.session_id = s.id AND mq.resolved = 0
         WHERE s.site_key = %s AND s.live_agent = 0
         GROUP BY s.id
         ORDER BY s.created_at DESC
         LIMIT 20",
        $site_key
    )
);
?>
<div class="wrap ln-wrap">
    <h1 class="ln-page-title">
        <span class="dashicons dashicons-businessman"></span>
        <?php esc_html_e( 'Live Agent', 'leadnest' ); ?>
    </h1>

    <!-- Settings -->
    <div class="ln-card">
        <div class="ln-card-header">
            <h2><?php esc_html_e( 'Handoff Settings', 'leadnest' ); ?></h2>
        </div>
        <div class="ln-card-body">
            <form id="ln-live-agent-form" method="post">
                <table class="ln-form-table">
                    <tr>
                        <th><?php esc_html_e( 'Enable Live Handoff', 'leadnest' ); ?></th>
                        <td>
                            <label style="display:flex;align-items:center;gap:8px;">
                                <input type="checkbox" name="live_agent_enabled" value="1"
                                       <?php checked( ! empty( $options['live_agent_enabled'] ) ); ?>>
                                <?php esc_html_e( 'Allow visitors to request a human agent', 'leadnest' ); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'Escalation Keywords', 'leadnest' ); ?></th>
                        <td>
                            <input type="text" name="live_agent_keywords"
                                   value="<?php echo esc_attr( $options['live_agent_keywords'] ); ?>"
                                   class="large-text" style="max-width:460px;"
                                   placeholder="human,agent,real person,manager">
                            <p class="description">
                                <?php esc_html_e( 'Comma-separated words. When a visitor types any of these, the bot offers to connect them with a human.', 'leadnest' ); ?>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'Uncertainty Threshold', 'leadnest' ); ?></th>
                        <td>
                            <input type="number" name="live_agent_uncertainty_threshold" class="small-text"
                                   value="<?php echo esc_attr( $options['live_agent_uncertainty_threshold'] ); ?>"
                                   min="1" max="10">
                            <?php esc_html_e( 'unanswered questions before offering handoff', 'leadnest' ); ?>
                        </td>
                    </tr>
                </table>
                <div class="ln-form-actions">
                    <button type="submit" id="ln-live-agent-save-btn" class="button button-primary">
                        <?php esc_html_e( 'Save Settings', 'leadnest' ); ?>
                    </button>
                    <span id="ln-live-agent-save-status" class="ln-save-status"></span>
                </div>
            </form>
        </div>
    </div>

    <!-- Active Live Sessions -->
    <div class="ln-card">
        <div class="ln-card-header">
            <h2>
                <?php esc_html_e( 'Active Live Sessions', 'leadnest' ); ?>
                <?php if ( ! empty( $live_sessions ) ) : ?>
                    <span class="ln-missed-count"><?php echo esc_html( count( $live_sessions ) ); ?></span>
                <?php endif; ?>
            </h2>
        </div>
        <div class="ln-card-body" style="padding:0;">
            <?php if ( empty( $live_sessions ) ) : ?>
                <div class="ln-empty-state">
                    <span class="dashicons dashicons-yes-alt"></span>
                    <h3><?php esc_html_e( 'No active live sessions', 'leadnest' ); ?></h3>
                    <p><?php esc_html_e( 'When you take over a session, it will appear here with a real-time chat interface.', 'leadnest' ); ?></p>
                </div>
            <?php else : ?>
                <table class="ln-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'Session', 'leadnest' ); ?></th>
                            <th><?php esc_html_e( 'Country', 'leadnest' ); ?></th>
                            <th><?php esc_html_e( 'Device', 'leadnest' ); ?></th>
                            <th><?php esc_html_e( 'Messages', 'leadnest' ); ?></th>
                            <th><?php esc_html_e( 'Page', 'leadnest' ); ?></th>
                            <th><?php esc_html_e( 'Actions', 'leadnest' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $live_sessions as $sess ) : ?>
                        <tr id="ln-live-row-<?php echo esc_attr( $sess->id ); ?>">
                            <td><code><?php echo esc_html( substr( $sess->session_token, 0, 12 ) . '...' ); ?></code></td>
                            <td><?php echo esc_html( $sess->country ?: '—' ); ?></td>
                            <td><?php echo esc_html( $sess->device ?: '—' ); ?></td>
                            <td><?php echo esc_html( $sess->msg_count ); ?></td>
                            <td style="max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                <?php if ( $sess->page_url ) : ?>
                                    <a href="<?php echo esc_url( $sess->page_url ); ?>" target="_blank" rel="noopener noreferrer" style="font-size:12px;">
                                        <?php echo esc_html( wp_parse_url( $sess->page_url, PHP_URL_PATH ) ?: '/' ); ?>
                                    </a>
                                <?php else : ?>
                                    —
                                <?php endif; ?>
                            </td>
                            <td class="ln-table-actions">
                                <button class="button button-primary button-small ln-open-live-chat-btn"
                                        data-session-id="<?php echo esc_attr( $sess->id ); ?>">
                                    <?php esc_html_e( 'Chat', 'leadnest' ); ?>
                                </button>
                                <button class="button button-small ln-release-session-btn"
                                        data-session-id="<?php echo esc_attr( $sess->id ); ?>">
                                    <?php esc_html_e( 'Return to AI', 'leadnest' ); ?>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- Sessions Needing Attention -->
    <div class="ln-card">
        <div class="ln-card-header">
            <h2>
                <?php esc_html_e( 'Sessions Needing Attention', 'leadnest' ); ?>
                <?php if ( ! empty( $escalation_sessions ) ) : ?>
                    <span class="ln-missed-count"><?php echo esc_html( count( $escalation_sessions ) ); ?></span>
                <?php endif; ?>
            </h2>
        </div>
        <div class="ln-card-body" style="padding:0;">
            <?php if ( empty( $escalation_sessions ) ) : ?>
                <div class="ln-empty-state">
                    <span class="dashicons dashicons-yes-alt"></span>
                    <h3><?php esc_html_e( 'All clear!', 'leadnest' ); ?></h3>
                    <p><?php esc_html_e( 'Sessions where the bot couldn\'t answer multiple questions will appear here.', 'leadnest' ); ?></p>
                </div>
            <?php else : ?>
                <table class="ln-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'Session', 'leadnest' ); ?></th>
                            <th><?php esc_html_e( 'Country', 'leadnest' ); ?></th>
                            <th><?php esc_html_e( 'Messages', 'leadnest' ); ?></th>
                            <th><?php esc_html_e( 'Uncertainty', 'leadnest' ); ?></th>
                            <th><?php esc_html_e( 'Date', 'leadnest' ); ?></th>
                            <th><?php esc_html_e( 'Actions', 'leadnest' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $escalation_sessions as $sess ) : ?>
                        <tr>
                            <td><code><?php echo esc_html( substr( $sess->session_token, 0, 12 ) . '...' ); ?></code></td>
                            <td><?php echo esc_html( $sess->country ?: '—' ); ?></td>
                            <td><?php echo esc_html( $sess->msg_count ); ?></td>
                            <td>
                                <span class="ln-missed-count"><?php echo esc_html( $sess->uncertainty_count ); ?>x</span>
                            </td>
                            <td><?php echo esc_html( wp_date( 'M j, Y', strtotime( $sess->created_at ) ) ); ?></td>
                            <td class="ln-table-actions">
                                <button class="button button-small ln-view-chat-btn"
                                        data-session-id="<?php echo esc_attr( $sess->id ); ?>">
                                    <?php esc_html_e( 'View', 'leadnest' ); ?>
                                </button>
                                <button class="button button-primary button-small ln-takeover-btn"
                                        data-session-id="<?php echo esc_attr( $sess->id ); ?>">
                                    <?php esc_html_e( 'Take Over', 'leadnest' ); ?>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Live Chat Modal -->
<div id="ln-live-chat-modal" class="ln-modal" style="display:none;">
    <div class="ln-modal-backdrop"></div>
    <div class="ln-modal-box" style="max-width:520px;height:600px;display:flex;flex-direction:column;">
        <div class="ln-modal-header" style="background:#1e293b;color:#fff;">
            <h2 style="margin:0;font-size:15px;">
                <span class="dashicons dashicons-businessman" style="margin-right:6px;"></span>
                <?php esc_html_e( 'Live Chat — Agent Mode', 'leadnest' ); ?>
                <span id="ln-live-session-label" style="opacity:.7;font-weight:normal;font-size:12px;margin-left:8px;"></span>
            </h2>
            <button class="ln-modal-close" style="color:#fff;" aria-label="<?php esc_attr_e( 'Close', 'leadnest' ); ?>">&times;</button>
        </div>
        <div id="ln-live-chat-messages" style="flex:1;overflow-y:auto;padding:16px;background:#f8fafc;display:flex;flex-direction:column;gap:8px;">
        </div>
        <div style="padding:12px;border-top:1px solid #e2e8f0;display:flex;gap:8px;background:#fff;">
            <textarea id="ln-live-chat-input" rows="1" style="flex:1;resize:none;border:1px solid #e2e8f0;border-radius:8px;padding:8px 12px;font-size:14px;font-family:inherit;"
                      placeholder="<?php esc_attr_e( 'Type your reply...', 'leadnest' ); ?>"></textarea>
            <button id="ln-live-chat-send" class="button button-primary">
                <?php esc_html_e( 'Send', 'leadnest' ); ?>
            </button>
        </div>
    </div>
</div>

<!-- View-only Chat Modal (reuse) -->
<div id="ln-chat-modal" class="ln-modal" style="display:none;">
    <div class="ln-modal-backdrop"></div>
    <div class="ln-modal-box">
        <div class="ln-modal-header">
            <h2><?php esc_html_e( 'Chat Transcript', 'leadnest' ); ?></h2>
            <button class="ln-modal-close" aria-label="<?php esc_attr_e( 'Close', 'leadnest' ); ?>">&times;</button>
        </div>
        <div id="ln-modal-body" class="ln-modal-body"></div>
    </div>
</div>

<script>
jQuery( function( $ ) {
    var restUrl   = leadnestAdmin.restUrl;
    var restNonce = leadnestAdmin.restNonce;
    var ajax      = leadnestAdmin.ajaxUrl;
    var nonce     = leadnestAdmin.nonce;
    var pollTimer = null;
    var activeSessionId = null;
    var lastMessageTime = '';

    // Take over a session
    $( document ).on( 'click', '.ln-takeover-btn', function() {
        var sessionId = $( this ).data( 'session-id' );
        var $btn = $( this );
        $btn.prop( 'disabled', true ).text( 'Taking over...' );

        $.ajax( {
            url: restUrl + 'live-agent/takeover',
            method: 'POST',
            headers: { 'X-WP-Nonce': restNonce },
            contentType: 'application/json',
            data: JSON.stringify( { session_id: sessionId } )
        } ).done( function() {
            location.reload();
        } ).fail( function() {
            alert( 'Failed to take over session.' );
            $btn.prop( 'disabled', false ).text( 'Take Over' );
        } );
    } );

    // Release session back to AI
    $( document ).on( 'click', '.ln-release-session-btn', function() {
        if ( ! confirm( 'Return this session to the AI bot?' ) ) return;
        var sessionId = $( this ).data( 'session-id' );

        $.ajax( {
            url: restUrl + 'live-agent/release',
            method: 'POST',
            headers: { 'X-WP-Nonce': restNonce },
            contentType: 'application/json',
            data: JSON.stringify( { session_id: sessionId } )
        } ).done( function() {
            $( '#ln-live-row-' + sessionId ).fadeOut( 200, function() { $( this ).remove(); } );
        } );
    } );

    // Open live chat modal
    $( document ).on( 'click', '.ln-open-live-chat-btn', function() {
        activeSessionId = $( this ).data( 'session-id' );
        lastMessageTime = '';
        $( '#ln-live-session-label' ).text( '#' + activeSessionId );
        $( '#ln-live-chat-messages' ).empty();
        $( '#ln-live-chat-modal' ).show();
        loadLiveMessages();
        startPolling();
    } );

    // Close live chat modal
    $( '#ln-live-chat-modal' ).on( 'click', '.ln-modal-close, .ln-modal-backdrop', function() {
        $( '#ln-live-chat-modal' ).hide();
        stopPolling();
        activeSessionId = null;
    } );

    // Send agent reply
    $( '#ln-live-chat-send' ).on( 'click', sendAgentReply );
    $( '#ln-live-chat-input' ).on( 'keydown', function( e ) {
        if ( e.key === 'Enter' && ! e.shiftKey ) {
            e.preventDefault();
            sendAgentReply();
        }
    } );

    function sendAgentReply() {
        var msg = $( '#ln-live-chat-input' ).val().trim();
        if ( ! msg || ! activeSessionId ) return;

        $( '#ln-live-chat-input' ).val( '' );
        $( '#ln-live-chat-send' ).prop( 'disabled', true );

        // Optimistic append
        appendMessage( 'assistant', msg, '' );

        $.ajax( {
            url: restUrl + 'live-agent/reply',
            method: 'POST',
            headers: { 'X-WP-Nonce': restNonce },
            contentType: 'application/json',
            data: JSON.stringify( { session_id: activeSessionId, message: msg } )
        } ).always( function() {
            $( '#ln-live-chat-send' ).prop( 'disabled', false );
        } );
    }

    function loadLiveMessages() {
        $.ajax( {
            url: restUrl + 'live-agent/messages?session_id=' + activeSessionId,
            method: 'GET',
            headers: { 'X-WP-Nonce': restNonce }
        } ).done( function( r ) {
            $( '#ln-live-chat-messages' ).empty();
            if ( r.messages ) {
                r.messages.forEach( function( m ) {
                    appendMessage( m.role, m.content, m.created_at );
                } );
                if ( r.messages.length > 0 ) {
                    lastMessageTime = r.messages[ r.messages.length - 1 ].created_at;
                }
            }
        } );
    }

    function pollMessages() {
        if ( ! activeSessionId ) return;

        var url = restUrl + 'live-agent/messages?session_id=' + activeSessionId;
        if ( lastMessageTime ) {
            url += '&after=' + encodeURIComponent( lastMessageTime );
        }

        $.ajax( {
            url: url,
            method: 'GET',
            headers: { 'X-WP-Nonce': restNonce }
        } ).done( function( r ) {
            if ( r.messages && r.messages.length > 0 ) {
                r.messages.forEach( function( m ) {
                    appendMessage( m.role, m.content, m.created_at );
                } );
                lastMessageTime = r.messages[ r.messages.length - 1 ].created_at;
            }
        } );
    }

    function appendMessage( role, content, time ) {
        var cls  = role === 'user' ? 'ln-chat-msg-user' : 'ln-chat-msg-assistant';
        var label = role === 'user' ? 'Visitor' : 'Agent';
        var timeStr = time ? time.slice( 11, 16 ) : '';
        var html = '<div class="ln-chat-msg ' + cls + '" style="' + ( role === 'user' ? 'align-self:flex-start;' : 'align-self:flex-end;' ) + '">'
            + '<div style="font-size:11px;color:#94a3b8;margin-bottom:2px;">' + escHtml( label ) + ( timeStr ? ' · ' + escHtml( timeStr ) : '' ) + '</div>'
            + '<div class="ln-chat-bubble" style="' + ( role === 'user' ? 'background:#e2e8f0;color:#1e293b;' : 'background:#2563eb;color:#fff;' )
            + 'padding:8px 12px;border-radius:12px;font-size:14px;line-height:1.5;max-width:85%;word-break:break-word;">'
            + escHtml( content ) + '</div></div>';
        $( '#ln-live-chat-messages' ).append( html );
        var el = $( '#ln-live-chat-messages' )[0];
        el.scrollTop = el.scrollHeight;
    }

    function startPolling() {
        stopPolling();
        pollTimer = setInterval( pollMessages, 3000 );
    }

    function stopPolling() {
        if ( pollTimer ) {
            clearInterval( pollTimer );
            pollTimer = null;
        }
    }

    function escHtml( str ) {
        return String( str ).replace( /&/g, '&amp;' ).replace( /</g, '&lt;' ).replace( />/g, '&gt;' ).replace( /"/g, '&quot;' );
    }
} );
</script>
