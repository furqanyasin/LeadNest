<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $wpdb;

$filter_status = sanitize_text_field( wp_unslash( $_GET['status'] ?? 'all' ) );
$valid_statuses = array( 'all', 'new', 'contacted', 'qualified', 'closed' );
if ( ! in_array( $filter_status, $valid_statuses, true ) ) {
    $filter_status = 'all';
}

// Count per status
$status_counts = array();
foreach ( array( 'new', 'contacted', 'qualified', 'closed' ) as $s ) {
    $status_counts[ $s ] = (int) $wpdb->get_var(
        $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}leadnest_leads WHERE status = %s", $s )
    );
}
$status_counts['all'] = array_sum( $status_counts );

// Fetch leads
$where      = '';
$where_args = array();
if ( $filter_status !== 'all' ) {
    $where        = 'WHERE status = %s';
    $where_args[] = $filter_status;
}

$leads = empty( $where )
    ? $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}leadnest_leads ORDER BY created_at DESC" )
    : $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}leadnest_leads {$where} ORDER BY created_at DESC", ...$where_args ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared

$status_labels = array(
    'new'       => array( 'label' => 'New',       'class' => 'ln-badge-new' ),
    'contacted' => array( 'label' => 'Contacted', 'class' => 'ln-badge-contacted' ),
    'qualified' => array( 'label' => 'Qualified', 'class' => 'ln-badge-qualified' ),
    'closed'    => array( 'label' => 'Closed',    'class' => 'ln-badge-closed' ),
);
?>
<div class="wrap ln-wrap">
    <h1 class="ln-page-title">
        <span class="dashicons dashicons-email-alt"></span>
        <?php esc_html_e( 'Leads', 'leadnest' ); ?>
    </h1>

    <!-- Status Filter Tabs -->
    <div class="ln-status-tabs">
        <?php
        $tab_labels = array(
            'all'       => __( 'All', 'leadnest' ),
            'new'       => __( 'New', 'leadnest' ),
            'contacted' => __( 'Contacted', 'leadnest' ),
            'qualified' => __( 'Qualified', 'leadnest' ),
            'closed'    => __( 'Closed', 'leadnest' ),
        );
        foreach ( $tab_labels as $status_key => $label ) :
            $active  = $filter_status === $status_key ? 'ln-tab-active' : '';
            $tab_url = admin_url( 'admin.php?page=leadnest-leads' . ( $status_key !== 'all' ? '&status=' . $status_key : '' ) );
        ?>
        <a href="<?php echo esc_url( $tab_url ); ?>" class="ln-status-tab <?php echo esc_attr( $active ); ?>">
            <?php echo esc_html( $label ); ?>
            <span class="ln-tab-count"><?php echo esc_html( $status_counts[ $status_key ] ); ?></span>
        </a>
        <?php endforeach; ?>

        <div class="ln-tab-actions">
            <button id="ln-export-leads-btn" class="button button-secondary">
                <span class="dashicons dashicons-download"></span>
                <?php esc_html_e( 'Export CSV', 'leadnest' ); ?>
            </button>
        </div>
    </div>

    <!-- Leads Grid -->
    <?php if ( empty( $leads ) ) : ?>
        <div class="ln-card">
            <div class="ln-card-body">
                <div class="ln-empty-state">
                    <span class="dashicons dashicons-email-alt"></span>
                    <h3><?php esc_html_e( 'No leads found', 'leadnest' ); ?></h3>
                    <p>
                        <?php if ( $filter_status !== 'all' ) : ?>
                            <?php esc_html_e( 'No leads with this status. Try a different filter.', 'leadnest' ); ?>
                        <?php else : ?>
                            <?php esc_html_e( 'Leads will appear here as visitors share their contact details with the chatbot.', 'leadnest' ); ?>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        </div>
    <?php else : ?>
        <div class="ln-leads-grid">
            <?php foreach ( $leads as $lead ) :
                $status_info = $status_labels[ $lead->status ] ?? array( 'label' => ucfirst( $lead->status ), 'class' => '' );
                $need_short  = $lead->need ? wp_trim_words( $lead->need, 15, '…' ) : '—';
                $source_path = $lead->source_page ? ( wp_parse_url( $lead->source_page, PHP_URL_PATH ) ?: '/' ) : '—';
            ?>
            <div class="ln-lead-card" id="ln-lead-<?php echo esc_attr( $lead->id ); ?>">
                <div class="ln-lead-card-header">
                    <div class="ln-lead-info">
                        <div class="ln-lead-avatar">
                            <?php echo esc_html( strtoupper( substr( $lead->name ?: $lead->email ?: '?', 0, 1 ) ) ); ?>
                        </div>
                        <div>
                            <h3 class="ln-lead-name"><?php echo esc_html( $lead->name ?: 'Unknown' ); ?></h3>
                            <span class="ln-badge <?php echo esc_attr( $status_info['class'] ); ?>"><?php echo esc_html( $status_info['label'] ); ?></span>
                        </div>
                    </div>
                    <div class="ln-lead-card-actions">
                        <button class="ln-icon-btn ln-delete-lead-btn" data-lead-id="<?php echo esc_attr( $lead->id ); ?>" title="<?php esc_attr_e( 'Delete lead', 'leadnest' ); ?>">
                            <span class="dashicons dashicons-trash"></span>
                        </button>
                    </div>
                </div>

                <div class="ln-lead-card-body">
                    <div class="ln-lead-details">
                        <?php if ( $lead->email ) : ?>
                        <div class="ln-lead-field">
                            <span class="dashicons dashicons-email-alt"></span>
                            <a href="mailto:<?php echo esc_attr( $lead->email ); ?>"><?php echo esc_html( $lead->email ); ?></a>
                        </div>
                        <?php endif; ?>

                        <?php if ( $lead->phone ) : ?>
                        <div class="ln-lead-field">
                            <span class="dashicons dashicons-phone"></span>
                            <a href="tel:<?php echo esc_attr( preg_replace( '/\D/', '', $lead->phone ) ); ?>"><?php echo esc_html( $lead->phone ); ?></a>
                        </div>
                        <?php endif; ?>

                        <?php if ( $lead->need ) : ?>
                        <div class="ln-lead-field">
                            <span class="dashicons dashicons-format-chat"></span>
                            <span title="<?php echo esc_attr( $lead->need ); ?>"><?php echo esc_html( $need_short ); ?></span>
                        </div>
                        <?php endif; ?>

                        <div class="ln-lead-field">
                            <span class="dashicons dashicons-admin-site"></span>
                            <?php if ( $lead->source_page ) : ?>
                                <a href="<?php echo esc_url( $lead->source_page ); ?>" target="_blank" rel="noopener noreferrer" title="<?php echo esc_attr( $lead->source_page ); ?>">
                                    <?php echo esc_html( $source_path ); ?>
                                </a>
                            <?php else : ?>
                                <span>—</span>
                            <?php endif; ?>
                        </div>

                        <div class="ln-lead-field">
                            <span class="dashicons dashicons-calendar-alt"></span>
                            <span><?php echo esc_html( wp_date( 'M j, Y g:i a', strtotime( $lead->created_at ) ) ); ?></span>
                        </div>
                    </div>

                    <!-- Status Update -->
                    <div class="ln-lead-status-row">
                        <label><?php esc_html_e( 'Status:', 'leadnest' ); ?></label>
                        <select class="ln-status-select" data-lead-id="<?php echo esc_attr( $lead->id ); ?>">
                            <?php foreach ( $status_labels as $sval => $sinfo ) : ?>
                            <option value="<?php echo esc_attr( $sval ); ?>" <?php selected( $lead->status, $sval ); ?>>
                                <?php echo esc_html( $sinfo['label'] ); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Notes -->
                    <div class="ln-lead-notes-row">
                        <label><?php esc_html_e( 'Notes:', 'leadnest' ); ?></label>
                        <textarea class="ln-notes-textarea" data-lead-id="<?php echo esc_attr( $lead->id ); ?>"
                                  placeholder="<?php esc_attr_e( 'Add notes…', 'leadnest' ); ?>"><?php echo esc_textarea( $lead->notes ); ?></textarea>
                        <button class="button button-small ln-save-notes-btn" data-lead-id="<?php echo esc_attr( $lead->id ); ?>">
                            <?php esc_html_e( 'Save Notes', 'leadnest' ); ?>
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
