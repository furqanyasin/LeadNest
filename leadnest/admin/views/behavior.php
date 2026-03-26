<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$options = LeadNest_DB::get_options();
?>
<div class="wrap ln-wrap">
    <h1 class="ln-page-title">
        <span class="dashicons dashicons-admin-settings"></span>
        <?php esc_html_e( 'Behavior', 'leadnest' ); ?>
    </h1>

    <form id="ln-behavior-form" method="post">
        <!-- Greeting & Widget Text -->
        <div class="ln-card">
            <div class="ln-card-header">
                <h2><?php esc_html_e( 'Widget Text', 'leadnest' ); ?></h2>
            </div>
            <div class="ln-card-body">
                <table class="ln-form-table">
                    <tr>
                        <th><?php esc_html_e( 'Greeting Message', 'leadnest' ); ?></th>
                        <td>
                            <input type="text" name="greeting_message"
                                   value="<?php echo esc_attr( $options['greeting_message'] ); ?>"
                                   class="regular-text" placeholder="Hello! How can I help you today?">
                            <p class="description"><?php esc_html_e( 'First message shown when the chat opens.', 'leadnest' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'Input Placeholder', 'leadnest' ); ?></th>
                        <td>
                            <input type="text" name="input_placeholder"
                                   value="<?php echo esc_attr( $options['input_placeholder'] ); ?>"
                                   class="regular-text" placeholder="Type your message...">
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Session Settings -->
        <div class="ln-card">
            <div class="ln-card-header">
                <h2><?php esc_html_e( 'Session Settings', 'leadnest' ); ?></h2>
            </div>
            <div class="ln-card-body">
                <table class="ln-form-table">
                    <tr>
                        <th><?php esc_html_e( 'Session Mode', 'leadnest' ); ?></th>
                        <td>
                            <select name="session_mode">
                                <option value="tab"    <?php selected( $options['session_mode'], 'tab' ); ?>>
                                    <?php esc_html_e( 'Per Tab (new chat on each browser tab)', 'leadnest' ); ?>
                                </option>
                                <option value="browser" <?php selected( $options['session_mode'], 'browser' ); ?>>
                                    <?php esc_html_e( 'Per Browser (same session across tabs)', 'leadnest' ); ?>
                                </option>
                                <option value="none"   <?php selected( $options['session_mode'], 'none' ); ?>>
                                    <?php esc_html_e( 'No persistence (new chat on every page load)', 'leadnest' ); ?>
                                </option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'Session Timeout', 'leadnest' ); ?></th>
                        <td>
                            <input type="number" name="session_timeout"
                                   value="<?php echo esc_attr( $options['session_timeout'] ); ?>"
                                   min="5" max="1440" class="small-text">
                            <?php esc_html_e( 'minutes', 'leadnest' ); ?>
                            <p class="description"><?php esc_html_e( 'After this period of inactivity a new session is started.', 'leadnest' ); ?></p>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- WooCommerce -->
        <?php if ( class_exists( 'WooCommerce' ) ) : ?>
        <div class="ln-card">
            <div class="ln-card-header">
                <h2><?php esc_html_e( 'WooCommerce', 'leadnest' ); ?></h2>
            </div>
            <div class="ln-card-body">
                <table class="ln-form-table">
                    <tr>
                        <th><?php esc_html_e( 'Conversion Tracking', 'leadnest' ); ?></th>
                        <td>
                            <label style="display:flex;align-items:center;gap:8px;">
                                <input type="checkbox" name="woocommerce_enabled" value="1"
                                       <?php checked( ! empty( $options['woocommerce_enabled'] ) ); ?>>
                                <?php esc_html_e( 'Track WooCommerce orders per chat session', 'leadnest' ); ?>
                            </label>
                            <p class="description">
                                <?php esc_html_e( 'Records the order ID and revenue for sessions where a purchase was completed. Shown in the dashboard.', 'leadnest' ); ?>
                            </p>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <div class="ln-form-actions">
            <button type="submit" id="ln-behavior-save-btn" class="button button-primary">
                <?php esc_html_e( 'Save Behavior', 'leadnest' ); ?>
            </button>
            <span id="ln-behavior-save-status" class="ln-save-status"></span>
        </div>
    </form>
</div>
