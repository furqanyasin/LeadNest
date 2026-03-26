<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $wpdb;

$options  = LeadNest_DB::get_options();
$site_key = $options['site_key'];

$kb_entries = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}leadnest_knowledge WHERE site_key = %s ORDER BY last_crawled DESC",
        $site_key
    )
);

$total_words = array_sum( wp_list_pluck( $kb_entries, 'word_count' ) );
?>
<div class="wrap ln-wrap">
    <h1 class="ln-page-title">
        <span class="dashicons dashicons-search"></span>
        <?php esc_html_e( 'Knowledge Base', 'leadnest' ); ?>
    </h1>

    <!-- Crawler -->
    <div class="ln-card">
        <div class="ln-card-header">
            <h2><?php esc_html_e( 'Website Crawler', 'leadnest' ); ?></h2>
        </div>
        <div class="ln-card-body">
            <p class="description" style="margin-bottom:14px;">
                <?php esc_html_e( 'Enter your website URL and LeadNest will crawl all pages automatically, extracting content for the bot to use. Works on any public website — not just WordPress.', 'leadnest' ); ?>
            </p>
            <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;margin-bottom:12px;">
                <input type="url" id="ln-crawl-url"
                       value="<?php echo esc_attr( $options['crawl_url'] ); ?>"
                       class="regular-text"
                       placeholder="https://yourdomain.com"
                       style="flex:1;min-width:260px;max-width:420px;">
                <select id="ln-crawl-max-pages" style="width:120px;">
                    <option value="10">10 pages</option>
                    <option value="20" selected>20 pages</option>
                    <option value="50">50 pages</option>
                </select>
                <button type="button" id="ln-crawl-btn" class="button button-primary">
                    <span class="dashicons dashicons-search" style="vertical-align:middle;margin-top:-2px;"></span>
                    <?php esc_html_e( 'Crawl Now', 'leadnest' ); ?>
                </button>
            </div>
            <div id="ln-crawl-status" style="display:none;"></div>

            <table class="ln-form-table" style="margin-top:10px;">
                <tr>
                    <th><?php esc_html_e( 'Auto-Recrawl', 'leadnest' ); ?></th>
                    <td>
                        <select id="ln-crawl-schedule" name="crawl_schedule">
                            <option value="manual" <?php selected( $options['crawl_schedule'], 'manual' ); ?>><?php esc_html_e( 'Manual only', 'leadnest' ); ?></option>
                            <option value="daily"  <?php selected( $options['crawl_schedule'], 'daily' ); ?> ><?php esc_html_e( 'Daily', 'leadnest' ); ?></option>
                            <option value="weekly" <?php selected( $options['crawl_schedule'], 'weekly' ); ?>><?php esc_html_e( 'Weekly', 'leadnest' ); ?></option>
                        </select>
                        <button type="button" id="ln-save-crawl-settings-btn" class="button button-secondary" style="margin-left:6px;">
                            <?php esc_html_e( 'Save', 'leadnest' ); ?>
                        </button>
                        <p class="description"><?php esc_html_e( 'Keep knowledge up to date automatically via WP Cron.', 'leadnest' ); ?></p>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <div class="ln-notice ln-notice-info" style="margin-bottom:16px;">
        <span class="dashicons dashicons-info"></span>
        <?php esc_html_e( 'Add content here to teach the bot about your business. You can paste page content manually below.', 'leadnest' ); ?>
        <?php printf(
            /* translators: word count */
            esc_html__( 'Current total: %s words.', 'leadnest' ),
            '<strong>' . esc_html( number_format( $total_words ) ) . '</strong>'
        ); ?>
    </div>

    <!-- Add New Entry -->
    <div class="ln-card">
        <div class="ln-card-header">
            <h2><?php esc_html_e( 'Add Knowledge Entry', 'leadnest' ); ?></h2>
        </div>
        <div class="ln-card-body">
            <form id="ln-kb-add-form" method="post">
                <table class="ln-form-table">
                    <tr>
                        <th><?php esc_html_e( 'Page Title', 'leadnest' ); ?></th>
                        <td>
                            <input type="text" name="page_title" class="regular-text"
                                   placeholder="<?php esc_attr_e( 'e.g. About Us', 'leadnest' ); ?>" required>
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'Page URL', 'leadnest' ); ?></th>
                        <td>
                            <input type="url" name="kb_url" class="regular-text"
                                   placeholder="https://yoursite.com/about">
                            <p class="description"><?php esc_html_e( 'Optional. For reference only.', 'leadnest' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'Content', 'leadnest' ); ?></th>
                        <td>
                            <textarea name="content" rows="8" class="large-text"
                                      placeholder="<?php esc_attr_e( 'Paste the page content here. The bot will use this to answer questions about your business.', 'leadnest' ); ?>"
                                      required></textarea>
                            <p class="description">
                                <?php esc_html_e( 'Tip: paste plain text, not HTML. Each entry is limited to ~2,000 tokens.', 'leadnest' ); ?>
                            </p>
                        </td>
                    </tr>
                </table>
                <div class="ln-form-actions">
                    <button type="submit" id="ln-kb-add-btn" class="button button-primary">
                        <?php esc_html_e( 'Add Entry', 'leadnest' ); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Existing Entries -->
    <div class="ln-card">
        <div class="ln-card-header">
            <h2>
                <?php esc_html_e( 'Knowledge Entries', 'leadnest' ); ?>
                <span class="ln-use-count" style="margin-left:8px;"><?php echo esc_html( count( $kb_entries ) ); ?></span>
            </h2>
        </div>
        <div class="ln-card-body">
            <?php if ( empty( $kb_entries ) ) : ?>
                <div class="ln-empty-state">
                    <span class="dashicons dashicons-search"></span>
                    <h3><?php esc_html_e( 'No knowledge entries yet', 'leadnest' ); ?></h3>
                    <p><?php esc_html_e( 'Add content above to teach the bot about your business.', 'leadnest' ); ?></p>
                </div>
            <?php else : ?>
                <table class="ln-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'Title', 'leadnest' ); ?></th>
                            <th><?php esc_html_e( 'URL', 'leadnest' ); ?></th>
                            <th><?php esc_html_e( 'Words', 'leadnest' ); ?></th>
                            <th><?php esc_html_e( 'Status', 'leadnest' ); ?></th>
                            <th><?php esc_html_e( 'Last Updated', 'leadnest' ); ?></th>
                            <th><?php esc_html_e( 'Actions', 'leadnest' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $kb_entries as $entry ) :
                            $url_display = ! empty( $entry->url ) ? wp_parse_url( $entry->url, PHP_URL_HOST ) . wp_parse_url( $entry->url, PHP_URL_PATH ) : '—';
                        ?>
                        <tr>
                            <td><strong><?php echo esc_html( $entry->page_title ); ?></strong></td>
                            <td>
                                <?php if ( $entry->url ) : ?>
                                    <a href="<?php echo esc_url( $entry->url ); ?>" target="_blank" rel="noopener noreferrer"
                                       style="font-size:12px;">
                                        <?php echo esc_html( $url_display ); ?>
                                    </a>
                                <?php else : ?>
                                    <span style="color:#94a3b8;">—</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo esc_html( number_format( $entry->word_count ) ); ?></td>
                            <td>
                                <?php if ( $entry->active ) : ?>
                                    <span class="ln-badge ln-badge-qualified"><?php esc_html_e( 'Active', 'leadnest' ); ?></span>
                                <?php else : ?>
                                    <span class="ln-badge ln-badge-closed"><?php esc_html_e( 'Inactive', 'leadnest' ); ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo esc_html( wp_date( 'M j, Y', strtotime( $entry->last_crawled ) ) ); ?></td>
                            <td class="ln-table-actions">
                                <button class="button button-small ln-delete-kb-btn"
                                        data-kb-id="<?php echo esc_attr( $entry->id ); ?>">
                                    <?php esc_html_e( 'Delete', 'leadnest' ); ?>
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
