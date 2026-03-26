<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $wpdb;

$per_page    = 20;
$current_page = max( 1, absint( $_GET['paged'] ?? 1 ) );
$offset      = ( $current_page - 1 ) * $per_page;
$search      = sanitize_text_field( wp_unslash( $_GET['s'] ?? '' ) );

$where_sql  = '';
$where_args = array();

if ( ! empty( $search ) ) {
    $like          = '%' . $wpdb->esc_like( $search ) . '%';
    $where_sql     = 'WHERE s.ip LIKE %s OR s.country LIKE %s OR s.session_token LIKE %s';
    $where_args    = array( $like, $like, $like );
}

$query_args = array_merge( $where_args, array( $per_page, $offset ) );

if ( ! empty( $where_sql ) ) {
    // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
    $sessions = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT s.*, (SELECT COUNT(*) FROM {$wpdb->prefix}leadnest_chats c WHERE c.session_id = s.id) as msg_count
             FROM {$wpdb->prefix}leadnest_sessions s
             {$where_sql}
             ORDER BY s.created_at DESC LIMIT %d OFFSET %d",
            ...$query_args
        )
    );
    // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
    $total = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}leadnest_sessions s {$where_sql}", ...$where_args ) );
} else {
    $sessions = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT s.*, (SELECT COUNT(*) FROM {$wpdb->prefix}leadnest_chats c WHERE c.session_id = s.id) as msg_count
             FROM {$wpdb->prefix}leadnest_sessions s ORDER BY s.created_at DESC LIMIT %d OFFSET %d",
            $per_page, $offset
        )
    );
    $total = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}leadnest_sessions" );
}

$total_pages = (int) ceil( $total / $per_page );
?>
<div class="wrap ln-wrap">
    <h1 class="ln-page-title">
        <span class="dashicons dashicons-format-chat"></span>
        <?php esc_html_e( 'Chat Logs', 'leadnest' ); ?>
    </h1>

    <div class="ln-card">
        <div class="ln-card-header">
            <!-- Search -->
            <form method="get" class="ln-search-form">
                <input type="hidden" name="page" value="leadnest-chat-logs">
                <input type="search" name="s" value="<?php echo esc_attr( $search ); ?>"
                       placeholder="<?php esc_attr_e( 'Search by IP, country, token…', 'leadnest' ); ?>"
                       class="ln-search-input">
                <button type="submit" class="button"><?php esc_html_e( 'Search', 'leadnest' ); ?></button>
                <?php if ( $search ) : ?>
                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=leadnest-chat-logs' ) ); ?>" class="button button-secondary">
                        <?php esc_html_e( 'Clear', 'leadnest' ); ?>
                    </a>
                <?php endif; ?>
            </form>

            <div class="ln-card-actions">
                <button id="ln-bulk-delete-btn" class="button button-secondary" disabled>
                    <?php esc_html_e( 'Delete Selected', 'leadnest' ); ?>
                </button>
                <button id="ln-export-sessions-btn" class="button button-secondary">
                    <?php esc_html_e( 'Export CSV', 'leadnest' ); ?>
                </button>
            </div>
        </div>

        <div class="ln-card-body">
            <?php if ( empty( $sessions ) ) : ?>
                <div class="ln-empty-state">
                    <span class="dashicons dashicons-format-chat"></span>
                    <p><?php esc_html_e( 'No chat sessions found.', 'leadnest' ); ?></p>
                </div>
            <?php else : ?>
                <form id="ln-bulk-form">
                    <table class="ln-table ln-chat-table">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="ln-check-all"></th>
                                <th><?php esc_html_e( 'Session Token', 'leadnest' ); ?></th>
                                <th><?php esc_html_e( 'Country', 'leadnest' ); ?></th>
                                <th><?php esc_html_e( 'Device', 'leadnest' ); ?></th>
                                <th><?php esc_html_e( 'Page', 'leadnest' ); ?></th>
                                <th><?php esc_html_e( 'Messages', 'leadnest' ); ?></th>
                                <th><?php esc_html_e( 'Date', 'leadnest' ); ?></th>
                                <th><?php esc_html_e( 'Actions', 'leadnest' ); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ( $sessions as $session ) :
                                $page_path = ! empty( $session->page_url ) ? wp_parse_url( $session->page_url, PHP_URL_PATH ) : '—';
                            ?>
                            <tr id="ln-session-row-<?php echo esc_attr( $session->id ); ?>">
                                <td><input type="checkbox" class="ln-session-check" value="<?php echo esc_attr( $session->id ); ?>"></td>
                                <td><code class="ln-token"><?php echo esc_html( substr( $session->session_token, 0, 16 ) . '…' ); ?></code></td>
                                <td><?php echo esc_html( $session->country ?: '—' ); ?></td>
                                <td><?php echo esc_html( $session->device ?: '—' ); ?></td>
                                <td>
                                    <?php if ( $session->page_url ) : ?>
                                        <a href="<?php echo esc_url( $session->page_url ); ?>" target="_blank" rel="noopener noreferrer" title="<?php echo esc_attr( $session->page_url ); ?>">
                                            <?php echo esc_html( $page_path ?: '/' ); ?>
                                        </a>
                                    <?php else : ?>
                                        —
                                    <?php endif; ?>
                                </td>
                                <td><?php echo esc_html( $session->msg_count ); ?></td>
                                <td><?php echo esc_html( wp_date( 'M j, Y g:i a', strtotime( $session->created_at ) ) ); ?></td>
                                <td class="ln-table-actions">
                                    <button class="button button-small ln-view-chat-btn"
                                            data-session-id="<?php echo esc_attr( $session->id ); ?>"
                                            data-token="<?php echo esc_attr( $session->session_token ); ?>">
                                        <?php esc_html_e( 'View', 'leadnest' ); ?>
                                    </button>
                                    <button class="button button-small ln-delete-session-btn"
                                            data-session-id="<?php echo esc_attr( $session->id ); ?>">
                                        <?php esc_html_e( 'Delete', 'leadnest' ); ?>
                                    </button>
                                </td>
                            </tr>
                            <!-- Chat Log Expandable Row -->
                            <tr class="ln-chat-expand-row" id="ln-expand-<?php echo esc_attr( $session->id ); ?>" style="display:none;">
                                <td colspan="8">
                                    <div class="ln-chat-expand-content">
                                        <div class="ln-chat-loading"><?php esc_html_e( 'Loading…', 'leadnest' ); ?></div>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </form>

                <!-- Pagination -->
                <?php if ( $total_pages > 1 ) : ?>
                <div class="ln-pagination">
                    <?php
                    $base_url = admin_url( 'admin.php?page=leadnest-chat-logs' . ( $search ? '&s=' . rawurlencode( $search ) : '' ) );
                    for ( $i = 1; $i <= $total_pages; $i++ ) :
                        $class = $i === $current_page ? 'button ln-page-btn ln-page-active' : 'button ln-page-btn';
                    ?>
                        <a href="<?php echo esc_url( $base_url . '&paged=' . $i ); ?>" class="<?php echo esc_attr( $class ); ?>">
                            <?php echo esc_html( $i ); ?>
                        </a>
                    <?php endfor; ?>
                    <span class="ln-pagination-info">
                        <?php
                        printf(
                            /* translators: 1: total sessions */
                            esc_html__( '%d total', 'leadnest' ),
                            (int) $total
                        );
                        ?>
                    </span>
                </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Chat Log Modal -->
<div id="ln-chat-modal" class="ln-modal" style="display:none;">
    <div class="ln-modal-backdrop"></div>
    <div class="ln-modal-box">
        <div class="ln-modal-header">
            <h2><?php esc_html_e( 'Chat Transcript', 'leadnest' ); ?></h2>
            <button class="ln-modal-close">&times;</button>
        </div>
        <div class="ln-modal-body" id="ln-modal-body">
            <div class="ln-chat-loading"><?php esc_html_e( 'Loading…', 'leadnest' ); ?></div>
        </div>
    </div>
</div>
