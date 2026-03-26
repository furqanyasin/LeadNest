<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $wpdb;

$options  = LeadNest_DB::get_options();
$site_key = $options['site_key'];

$qa_pairs = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}leadnest_qa WHERE site_key = %s ORDER BY use_count DESC, created_at DESC",
        $site_key
    )
);

$missed = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}leadnest_missed_questions WHERE site_key = %s ORDER BY ask_count DESC, created_at DESC LIMIT 50",
        $site_key
    )
);

$unresolved_count = (int) $wpdb->get_var(
    $wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->prefix}leadnest_missed_questions WHERE site_key = %s AND resolved = 0",
        $site_key
    )
);
?>
<div class="wrap ln-wrap">
    <h1 class="ln-page-title">
        <span class="dashicons dashicons-welcome-learn-more"></span>
        <?php esc_html_e( 'Train Bot', 'leadnest' ); ?>
    </h1>

    <!-- Q&A Trainer -->
    <div class="ln-card">
        <div class="ln-card-header">
            <h2><?php esc_html_e( 'Q&A Pairs', 'leadnest' ); ?>
                <span class="ln-use-count" style="margin-left:6px;"><?php echo esc_html( count( $qa_pairs ) ); ?></span>
            </h2>
        </div>
        <div class="ln-card-body">
            <p class="description" style="margin-bottom:14px;">
                <?php esc_html_e( 'These Q&A pairs are injected into the bot\'s system prompt and answered exactly as written.', 'leadnest' ); ?>
            </p>

            <!-- CSV Import/Export -->
            <div style="display:flex;gap:10px;align-items:center;margin-bottom:20px;flex-wrap:wrap;">
                <form id="ln-qa-import-form" method="post" enctype="multipart/form-data" style="display:flex;gap:8px;align-items:center;">
                    <input type="file" name="qa_csv_file" id="ln-qa-csv-file" accept=".csv" style="max-width:220px;">
                    <button type="submit" id="ln-qa-import-btn" class="button button-secondary">
                        <span class="dashicons dashicons-upload" style="vertical-align:middle;margin-top:-2px;"></span>
                        <?php esc_html_e( 'Import CSV', 'leadnest' ); ?>
                    </button>
                </form>
                <button type="button" id="ln-qa-export-btn" class="button button-secondary">
                    <span class="dashicons dashicons-download" style="vertical-align:middle;margin-top:-2px;"></span>
                    <?php esc_html_e( 'Export CSV', 'leadnest' ); ?>
                </button>
                <span class="description"><?php esc_html_e( 'CSV format: Question, Answer (one pair per row)', 'leadnest' ); ?></span>
            </div>

            <!-- Add Q&A Form -->
            <form id="ln-qa-add-form" method="post">
                <table class="ln-form-table">
                    <tr>
                        <th><?php esc_html_e( 'Question', 'leadnest' ); ?></th>
                        <td>
                            <input type="text" name="question" class="regular-text"
                                   placeholder="<?php esc_attr_e( 'e.g. What are your business hours?', 'leadnest' ); ?>" required>
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'Answer', 'leadnest' ); ?></th>
                        <td>
                            <textarea name="answer" rows="3" class="large-text"
                                      placeholder="<?php esc_attr_e( 'e.g. We are open Monday to Friday, 9AM to 6PM.', 'leadnest' ); ?>"
                                      required></textarea>
                        </td>
                    </tr>
                </table>
                <div class="ln-form-actions">
                    <button type="submit" id="ln-qa-add-btn" class="button button-primary">
                        <?php esc_html_e( 'Add Q&A', 'leadnest' ); ?>
                    </button>
                </div>
            </form>

            <!-- Q&A Table -->
            <?php if ( ! empty( $qa_pairs ) ) : ?>
            <div style="margin-top:24px;">
                <table class="ln-table ln-qa-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'Question / Answer', 'leadnest' ); ?></th>
                            <th style="width:80px;"><?php esc_html_e( 'Uses', 'leadnest' ); ?></th>
                            <th style="width:90px;"><?php esc_html_e( 'Added', 'leadnest' ); ?></th>
                            <th style="width:70px;"><?php esc_html_e( 'Actions', 'leadnest' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $qa_pairs as $qa ) : ?>
                        <tr id="ln-qa-row-<?php echo esc_attr( $qa->id ); ?>">
                            <td>
                                <div class="ln-qa-question"><?php echo esc_html( $qa->question ); ?></div>
                                <div class="ln-qa-answer"><?php echo esc_html( wp_trim_words( $qa->answer, 20, '…' ) ); ?></div>
                            </td>
                            <td>
                                <span class="ln-use-count"><?php echo esc_html( $qa->use_count ); ?></span>
                            </td>
                            <td>
                                <?php echo esc_html( wp_date( 'M j, Y', strtotime( $qa->created_at ) ) ); ?>
                            </td>
                            <td class="ln-table-actions">
                                <button class="button button-small ln-delete-qa-btn"
                                        data-qa-id="<?php echo esc_attr( $qa->id ); ?>">
                                    <?php esc_html_e( 'Delete', 'leadnest' ); ?>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Missed Questions -->
    <div class="ln-card">
        <div class="ln-card-header">
            <h2>
                <?php esc_html_e( 'Missed Questions', 'leadnest' ); ?>
                <?php if ( $unresolved_count > 0 ) : ?>
                    <span class="ln-missed-count"><?php echo esc_html( $unresolved_count ); ?></span>
                <?php endif; ?>
            </h2>
        </div>
        <div class="ln-card-body">
            <p class="description" style="margin-bottom:14px;">
                <?php esc_html_e( 'Questions the bot couldn\'t confidently answer. Add them to Q&A above to improve bot responses.', 'leadnest' ); ?>
            </p>

            <?php if ( empty( $missed ) ) : ?>
                <div class="ln-empty-state">
                    <span class="dashicons dashicons-yes-alt"></span>
                    <h3><?php esc_html_e( 'No missed questions!', 'leadnest' ); ?></h3>
                    <p><?php esc_html_e( 'When the bot can\'t answer a question, it will appear here so you can add the answer.', 'leadnest' ); ?></p>
                </div>
            <?php else : ?>
                <table class="ln-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'Question', 'leadnest' ); ?></th>
                            <th style="width:60px;"><?php esc_html_e( 'Asked', 'leadnest' ); ?></th>
                            <th style="width:90px;"><?php esc_html_e( 'Date', 'leadnest' ); ?></th>
                            <th style="width:80px;"><?php esc_html_e( 'Status', 'leadnest' ); ?></th>
                            <th style="width:100px;"><?php esc_html_e( 'Actions', 'leadnest' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $missed as $mq ) :
                            $row_class = $mq->resolved ? 'ln-resolved' : '';
                        ?>
                        <tr id="ln-missed-row-<?php echo esc_attr( $mq->id ); ?>" class="<?php echo esc_attr( $row_class ); ?>">
                            <td>
                                <div><?php echo esc_html( $mq->question ); ?></div>
                                <?php if ( $mq->bot_reply ) : ?>
                                    <div style="font-size:11px;color:#94a3b8;margin-top:2px;">
                                        <?php echo esc_html( wp_trim_words( $mq->bot_reply, 12, '…' ) ); ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="ln-use-count"><?php echo esc_html( $mq->ask_count ); ?>×</span>
                            </td>
                            <td>
                                <?php echo esc_html( wp_date( 'M j, Y', strtotime( $mq->created_at ) ) ); ?>
                            </td>
                            <td>
                                <?php if ( $mq->resolved ) : ?>
                                    <span class="ln-badge ln-badge-qualified"><?php esc_html_e( 'Resolved', 'leadnest' ); ?></span>
                                <?php else : ?>
                                    <span class="ln-badge ln-badge-new"><?php esc_html_e( 'Open', 'leadnest' ); ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="ln-table-actions">
                                <?php if ( ! $mq->resolved ) : ?>
                                <button class="button button-small ln-answer-missed-btn"
                                        data-question="<?php echo esc_attr( $mq->question ); ?>">
                                    <?php esc_html_e( 'Answer', 'leadnest' ); ?>
                                </button>
                                <button class="button button-small ln-resolve-missed-btn"
                                        data-missed-id="<?php echo esc_attr( $mq->id ); ?>">
                                    <?php esc_html_e( 'Resolve', 'leadnest' ); ?>
                                </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
jQuery( function( $ ) {
    // Pre-fill Q&A form from missed question
    $( document ).on( 'click', '.ln-answer-missed-btn', function() {
        var question = $( this ).data( 'question' );
        $( '#ln-qa-add-form [name="question"]' ).val( question );
        $( '#ln-qa-add-form [name="answer"]' ).focus();
        $( 'html, body' ).animate( { scrollTop: $( '#ln-qa-add-form' ).offset().top - 80 }, 300 );
    } );
} );
</script>
