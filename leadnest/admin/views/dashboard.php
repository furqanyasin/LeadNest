<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $wpdb;

$options = LeadNest_DB::get_options();

// Stats
$total_sessions = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}leadnest_sessions" );
$total_leads    = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}leadnest_leads" );
$new_leads      = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}leadnest_leads WHERE status = 'new'" );
$total_chats    = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}leadnest_chats" );

// WooCommerce stats
$wc_active   = class_exists( 'WooCommerce' );
$conversions = 0;
$revenue     = '0.00';
if ( $wc_active && ! empty( $options['woocommerce_enabled'] ) ) {
    $conversions = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}leadnest_sessions WHERE conversion_order_id != ''" );
    $revenue     = (float) $wpdb->get_var( "SELECT SUM(conversion_revenue) FROM {$wpdb->prefix}leadnest_sessions WHERE conversion_revenue > 0" );
    $revenue     = number_format( $revenue ?: 0, 2 );
}

// Recent leads
$recent_leads = $wpdb->get_results(
    "SELECT * FROM {$wpdb->prefix}leadnest_leads ORDER BY created_at DESC LIMIT 5"
);

// Recent sessions
$recent_sessions = $wpdb->get_results(
    "SELECT s.*, (SELECT COUNT(*) FROM {$wpdb->prefix}leadnest_chats c WHERE c.session_id = s.id) as msg_count
     FROM {$wpdb->prefix}leadnest_sessions s ORDER BY s.created_at DESC LIMIT 5"
);

$status_labels = array(
    'new'       => array( 'label' => 'New',        'class' => 'ln-badge-new' ),
    'contacted' => array( 'label' => 'Contacted',  'class' => 'ln-badge-contacted' ),
    'qualified' => array( 'label' => 'Qualified',  'class' => 'ln-badge-qualified' ),
    'closed'    => array( 'label' => 'Closed',     'class' => 'ln-badge-closed' ),
);
?>
<div class="wrap ln-wrap">
    <h1 class="ln-page-title">
        <span class="dashicons dashicons-format-chat"></span>
        <?php esc_html_e( 'LeadNest Dashboard', 'leadnest' ); ?>
    </h1>

    <!-- Stats Grid -->
    <div class="ln-stats-grid">
        <div class="ln-stat-card">
            <div class="ln-stat-icon ln-icon-sessions">
                <span class="dashicons dashicons-admin-users"></span>
            </div>
            <div class="ln-stat-body">
                <div class="ln-stat-number"><?php echo esc_html( number_format( $total_sessions ) ); ?></div>
                <div class="ln-stat-label"><?php esc_html_e( 'Total Sessions', 'leadnest' ); ?></div>
            </div>
        </div>

        <div class="ln-stat-card">
            <div class="ln-stat-icon ln-icon-leads">
                <span class="dashicons dashicons-email-alt"></span>
            </div>
            <div class="ln-stat-body">
                <div class="ln-stat-number">
                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=leadnest-leads' ) ); ?>">
                        <?php echo esc_html( number_format( $total_leads ) ); ?>
                    </a>
                </div>
                <div class="ln-stat-label"><?php esc_html_e( 'Total Leads', 'leadnest' ); ?></div>
            </div>
        </div>

        <div class="ln-stat-card">
            <div class="ln-stat-icon ln-icon-new">
                <span class="dashicons dashicons-star-filled"></span>
            </div>
            <div class="ln-stat-body">
                <div class="ln-stat-number">
                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=leadnest-leads&status=new' ) ); ?>">
                        <?php echo esc_html( number_format( $new_leads ) ); ?>
                    </a>
                </div>
                <div class="ln-stat-label"><?php esc_html_e( 'New Leads', 'leadnest' ); ?></div>
            </div>
        </div>

        <div class="ln-stat-card">
            <div class="ln-stat-icon ln-icon-chats">
                <span class="dashicons dashicons-format-chat"></span>
            </div>
            <div class="ln-stat-body">
                <div class="ln-stat-number"><?php echo esc_html( number_format( $total_chats ) ); ?></div>
                <div class="ln-stat-label"><?php esc_html_e( 'Total Chats', 'leadnest' ); ?></div>
            </div>
        </div>

        <?php if ( $wc_active && ! empty( $options['woocommerce_enabled'] ) ) : ?>
        <div class="ln-stat-card">
            <div class="ln-stat-icon ln-icon-conversions">
                <span class="dashicons dashicons-cart"></span>
            </div>
            <div class="ln-stat-body">
                <div class="ln-stat-number"><?php echo esc_html( number_format( $conversions ) ); ?></div>
                <div class="ln-stat-label"><?php esc_html_e( 'Conversions', 'leadnest' ); ?></div>
            </div>
        </div>

        <div class="ln-stat-card">
            <div class="ln-stat-icon ln-icon-revenue">
                <span class="dashicons dashicons-money-alt"></span>
            </div>
            <div class="ln-stat-body">
                <div class="ln-stat-number">$<?php echo esc_html( $revenue ); ?></div>
                <div class="ln-stat-label"><?php esc_html_e( 'Total Revenue', 'leadnest' ); ?></div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="ln-dashboard-columns">
        <!-- Recent Leads -->
        <div class="ln-card ln-recent-leads">
            <div class="ln-card-header">
                <h2><?php esc_html_e( 'Recent Leads', 'leadnest' ); ?></h2>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=leadnest-leads' ) ); ?>" class="ln-card-link">
                    <?php esc_html_e( 'View all →', 'leadnest' ); ?>
                </a>
            </div>
            <div class="ln-card-body">
                <?php if ( empty( $recent_leads ) ) : ?>
                    <div class="ln-empty-state">
                        <span class="dashicons dashicons-email-alt"></span>
                        <p><?php esc_html_e( 'No leads captured yet. Your chatbot will capture leads automatically as visitors interact with it.', 'leadnest' ); ?></p>
                    </div>
                <?php else : ?>
                    <table class="ln-table">
                        <thead>
                            <tr>
                                <th><?php esc_html_e( 'Name / Email', 'leadnest' ); ?></th>
                                <th><?php esc_html_e( 'Date', 'leadnest' ); ?></th>
                                <th><?php esc_html_e( 'Status', 'leadnest' ); ?></th>
                                <th><?php esc_html_e( 'Source', 'leadnest' ); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ( $recent_leads as $lead ) :
                                $status_info = $status_labels[ $lead->status ] ?? array( 'label' => ucfirst( $lead->status ), 'class' => '' );
                                $source      = ! empty( $lead->source_page ) ? wp_parse_url( $lead->source_page, PHP_URL_PATH ) : '—';
                            ?>
                            <tr>
                                <td>
                                    <strong><?php echo esc_html( $lead->name ?: 'Unknown' ); ?></strong><br>
                                    <?php if ( $lead->email ) : ?>
                                        <a href="mailto:<?php echo esc_attr( $lead->email ); ?>"><?php echo esc_html( $lead->email ); ?></a>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo esc_html( wp_date( 'M j, Y', strtotime( $lead->created_at ) ) ); ?></td>
                                <td><span class="ln-badge <?php echo esc_attr( $status_info['class'] ); ?>"><?php echo esc_html( $status_info['label'] ); ?></span></td>
                                <td><span title="<?php echo esc_attr( $lead->source_page ); ?>"><?php echo esc_html( $source ); ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Sessions -->
        <div class="ln-card ln-recent-sessions">
            <div class="ln-card-header">
                <h2><?php esc_html_e( 'Recent Sessions', 'leadnest' ); ?></h2>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=leadnest-chat-logs' ) ); ?>" class="ln-card-link">
                    <?php esc_html_e( 'View all →', 'leadnest' ); ?>
                </a>
            </div>
            <div class="ln-card-body">
                <?php if ( empty( $recent_sessions ) ) : ?>
                    <div class="ln-empty-state">
                        <span class="dashicons dashicons-admin-users"></span>
                        <p><?php esc_html_e( 'No sessions yet. The chatbot will start capturing sessions once visitors interact with it.', 'leadnest' ); ?></p>
                    </div>
                <?php else : ?>
                    <table class="ln-table">
                        <thead>
                            <tr>
                                <th><?php esc_html_e( 'Token', 'leadnest' ); ?></th>
                                <th><?php esc_html_e( 'Country', 'leadnest' ); ?></th>
                                <th><?php esc_html_e( 'Device', 'leadnest' ); ?></th>
                                <th><?php esc_html_e( 'Messages', 'leadnest' ); ?></th>
                                <th><?php esc_html_e( 'Date', 'leadnest' ); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ( $recent_sessions as $session ) : ?>
                            <tr>
                                <td><code><?php echo esc_html( substr( $session->session_token, 0, 12 ) . '…' ); ?></code></td>
                                <td><?php echo esc_html( $session->country ?: '—' ); ?></td>
                                <td><?php echo esc_html( $session->device ?: '—' ); ?></td>
                                <td><?php echo esc_html( $session->msg_count ); ?></td>
                                <td><?php echo esc_html( wp_date( 'M j, Y', strtotime( $session->created_at ) ) ); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="ln-card ln-quick-actions">
        <div class="ln-card-header">
            <h2><?php esc_html_e( 'Quick Actions', 'leadnest' ); ?></h2>
        </div>
        <div class="ln-card-body">
            <div class="ln-quick-actions-grid">
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=leadnest-ai-settings' ) ); ?>" class="ln-quick-action-btn">
                    <span class="dashicons dashicons-admin-generic"></span>
                    <?php esc_html_e( 'AI Settings', 'leadnest' ); ?>
                </a>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=leadnest-appearance' ) ); ?>" class="ln-quick-action-btn">
                    <span class="dashicons dashicons-art"></span>
                    <?php esc_html_e( 'Appearance', 'leadnest' ); ?>
                </a>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=leadnest-train-bot' ) ); ?>" class="ln-quick-action-btn">
                    <span class="dashicons dashicons-welcome-learn-more"></span>
                    <?php esc_html_e( 'Train Bot', 'leadnest' ); ?>
                </a>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=leadnest-setup-guide' ) ); ?>" class="ln-quick-action-btn">
                    <span class="dashicons dashicons-info"></span>
                    <?php esc_html_e( 'Setup Guide', 'leadnest' ); ?>
                </a>
            </div>
        </div>
    </div>

    <!-- Site Key Info -->
    <div class="ln-card">
        <div class="ln-card-header">
            <h2><?php esc_html_e( 'Your Site Key', 'leadnest' ); ?></h2>
        </div>
        <div class="ln-card-body">
            <p><?php esc_html_e( 'Use this key to embed the chatbot on external websites:', 'leadnest' ); ?></p>
            <div class="ln-code-block">
                <code id="ln-site-key"><?php echo esc_html( $options['site_key'] ); ?></code>
                <button class="button button-secondary ln-copy-btn" data-copy="ln-site-key">
                    <?php esc_html_e( 'Copy', 'leadnest' ); ?>
                </button>
            </div>
            <p class="description">
                <?php esc_html_e( 'Widget URL:', 'leadnest' ); ?>
                <code><?php echo esc_html( rest_url( 'leadnest/v1/widget.js' ) . '?key=' . $options['site_key'] ); ?></code>
            </p>
        </div>
    </div>
</div>
