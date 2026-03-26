<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$options  = LeadNest_DB::get_options();

$whatsapp_connected  = ! empty( $options['whatsapp_phone_id'] ) && ! empty( $options['whatsapp_token'] );
$messenger_connected = ! empty( $options['messenger_page_id'] ) && ! empty( $options['messenger_page_token'] );
$telegram_connected  = ! empty( $options['telegram_bot_token'] );
$twilio_connected    = class_exists( 'LeadNest_SMS' ) && LeadNest_SMS::is_configured();

$whatsapp_webhook_url  = rest_url( 'leadnest/v1/whatsapp/webhook' );
$messenger_webhook_url = rest_url( 'leadnest/v1/messenger/webhook' );
$telegram_webhook_url  = rest_url( 'leadnest/v1/telegram/webhook' );
?>
<div class="wrap ln-wrap">
    <h1 class="ln-page-title">
        <span class="dashicons dashicons-share"></span>
        <?php esc_html_e( 'Channels', 'leadnest' ); ?>
    </h1>

    <p class="description" style="margin-bottom:20px;">
        <?php esc_html_e( 'Connect LeadNest to messaging platforms. All conversations use the same AI brain, knowledge base, and lead capture system.', 'leadnest' ); ?>
    </p>

    <!-- WhatsApp Business API -->
    <div class="ln-card">
        <div class="ln-card-header">
            <h2>
                <span class="dashicons dashicons-format-chat" style="color:#25D366;"></span>
                <?php esc_html_e( 'WhatsApp Business API', 'leadnest' ); ?>
            </h2>
            <?php if ( $whatsapp_connected ) : ?>
                <span class="ln-badge ln-badge-qualified"><?php esc_html_e( 'Connected', 'leadnest' ); ?></span>
            <?php else : ?>
                <span class="ln-badge ln-badge-closed"><?php esc_html_e( 'Not Connected', 'leadnest' ); ?></span>
            <?php endif; ?>
        </div>
        <div class="ln-card-body">
            <div class="ln-notice ln-notice-info" style="margin-bottom:16px;">
                <strong><?php esc_html_e( 'Setup Steps:', 'leadnest' ); ?></strong>
                <?php esc_html_e( '1. Create a Meta Business app at developers.facebook.com → 2. Add WhatsApp product → 3. Get Phone Number ID and Permanent Token → 4. Set the Webhook URL below in Meta dashboard.', 'leadnest' ); ?>
            </div>

            <form id="ln-whatsapp-form" method="post">
                <table class="ln-form-table">
                    <tr>
                        <th><?php esc_html_e( 'Phone Number ID', 'leadnest' ); ?></th>
                        <td>
                            <input type="text" name="whatsapp_phone_id"
                                   value="<?php echo esc_attr( $options['whatsapp_phone_id'] ); ?>"
                                   class="regular-text" placeholder="1234567890123">
                            <p class="description"><?php esc_html_e( 'Found in WhatsApp → Getting Started in Meta Business Manager.', 'leadnest' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'Permanent Access Token', 'leadnest' ); ?></th>
                        <td>
                            <input type="password" name="whatsapp_token"
                                   value="<?php echo esc_attr( $options['whatsapp_token'] ); ?>"
                                   class="regular-text" autocomplete="new-password"
                                   placeholder="EAAxxxxx…">
                            <p class="description"><?php esc_html_e( 'Create a System User token in Business Settings → System Users.', 'leadnest' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'Webhook Verify Token', 'leadnest' ); ?></th>
                        <td>
                            <input type="text" name="whatsapp_webhook_secret"
                                   value="<?php echo esc_attr( $options['whatsapp_webhook_secret'] ); ?>"
                                   class="regular-text" placeholder="my-secret-token-123">
                            <p class="description"><?php esc_html_e( 'Any secret string you choose — enter the same value in Meta webhook settings.', 'leadnest' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'Webhook URL', 'leadnest' ); ?></th>
                        <td>
                            <div class="ln-code-block">
                                <code id="ln-whatsapp-webhook"><?php echo esc_html( $webhook_url ); ?></code>
                                <button type="button" class="button button-secondary ln-copy-btn" data-copy="ln-whatsapp-webhook">
                                    <?php esc_html_e( 'Copy', 'leadnest' ); ?>
                                </button>
                            </div>
                            <p class="description"><?php esc_html_e( 'Paste this URL in Meta Business Manager → WhatsApp → Configuration → Webhook URL.', 'leadnest' ); ?></p>
                        </td>
                    </tr>
                </table>
                <div class="ln-form-actions">
                    <button type="submit" id="ln-whatsapp-save-btn" class="button button-primary">
                        <?php esc_html_e( 'Save WhatsApp Settings', 'leadnest' ); ?>
                    </button>
                    <span id="ln-whatsapp-save-status" class="ln-save-status"></span>
                </div>
            </form>
        </div>
    </div>

    <!-- Facebook Messenger -->
    <div class="ln-card">
        <div class="ln-card-header">
            <h2>
                <span class="dashicons dashicons-format-chat" style="color:#0084FF;"></span>
                <?php esc_html_e( 'Facebook Messenger', 'leadnest' ); ?>
            </h2>
            <?php if ( $messenger_connected ) : ?>
                <span class="ln-badge ln-badge-qualified"><?php esc_html_e( 'Connected', 'leadnest' ); ?></span>
            <?php else : ?>
                <span class="ln-badge ln-badge-closed"><?php esc_html_e( 'Not Connected', 'leadnest' ); ?></span>
            <?php endif; ?>
        </div>
        <div class="ln-card-body">
            <div class="ln-notice ln-notice-info" style="margin-bottom:16px;">
                <strong><?php esc_html_e( 'Setup Steps:', 'leadnest' ); ?></strong>
                <?php esc_html_e( '1. Create a Meta Business app → 2. Add Messenger product → 3. Subscribe your Page → 4. Generate a Page Access Token → 5. Set the Webhook URL below in Meta dashboard.', 'leadnest' ); ?>
            </div>

            <form id="ln-messenger-form" method="post">
                <table class="ln-form-table">
                    <tr>
                        <th><?php esc_html_e( 'Page ID', 'leadnest' ); ?></th>
                        <td>
                            <input type="text" name="messenger_page_id"
                                   value="<?php echo esc_attr( $options['messenger_page_id'] ?? '' ); ?>"
                                   class="regular-text" placeholder="123456789012345">
                            <p class="description"><?php esc_html_e( 'Your Facebook Page ID (found in Page Settings → Page Transparency).', 'leadnest' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'Page Access Token', 'leadnest' ); ?></th>
                        <td>
                            <input type="password" name="messenger_page_token"
                                   value="<?php echo esc_attr( $options['messenger_page_token'] ?? '' ); ?>"
                                   class="regular-text" autocomplete="new-password"
                                   placeholder="EAAxxxxx...">
                            <p class="description"><?php esc_html_e( 'Generate a long-lived Page Access Token in your Meta app → Messenger → Settings.', 'leadnest' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'Webhook Verify Token', 'leadnest' ); ?></th>
                        <td>
                            <input type="text" name="messenger_webhook_secret"
                                   value="<?php echo esc_attr( $options['messenger_webhook_secret'] ?? '' ); ?>"
                                   class="regular-text" placeholder="my-messenger-secret-123">
                            <p class="description"><?php esc_html_e( 'Any secret string — enter the same value in Meta webhook settings.', 'leadnest' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'Webhook URL', 'leadnest' ); ?></th>
                        <td>
                            <div class="ln-code-block">
                                <code id="ln-messenger-webhook"><?php echo esc_html( $messenger_webhook_url ); ?></code>
                                <button type="button" class="button button-secondary ln-copy-btn" data-copy="ln-messenger-webhook">
                                    <?php esc_html_e( 'Copy', 'leadnest' ); ?>
                                </button>
                            </div>
                            <p class="description"><?php esc_html_e( 'Paste this URL in Meta Business Manager → Messenger → Webhooks. Subscribe to "messages" event.', 'leadnest' ); ?></p>
                        </td>
                    </tr>
                </table>
                <div class="ln-form-actions">
                    <button type="submit" id="ln-messenger-save-btn" class="button button-primary">
                        <?php esc_html_e( 'Save Messenger Settings', 'leadnest' ); ?>
                    </button>
                    <span id="ln-messenger-save-status" class="ln-save-status"></span>
                </div>
            </form>
        </div>
    </div>

    <!-- Telegram -->
    <div class="ln-card">
        <div class="ln-card-header">
            <h2>
                <span class="dashicons dashicons-share" style="color:#2CA5E0;"></span>
                <?php esc_html_e( 'Telegram', 'leadnest' ); ?>
            </h2>
            <?php if ( $telegram_connected ) : ?>
                <span class="ln-badge ln-badge-qualified"><?php esc_html_e( 'Connected', 'leadnest' ); ?></span>
            <?php else : ?>
                <span class="ln-badge ln-badge-closed"><?php esc_html_e( 'Not Connected', 'leadnest' ); ?></span>
            <?php endif; ?>
        </div>
        <div class="ln-card-body">
            <div class="ln-notice ln-notice-info" style="margin-bottom:16px;">
                <strong><?php esc_html_e( 'Setup Steps:', 'leadnest' ); ?></strong>
                <?php esc_html_e( '1. Message @BotFather on Telegram → 2. Create a new bot with /newbot → 3. Copy the bot token → 4. Click "Set Webhook" below after saving.', 'leadnest' ); ?>
            </div>

            <form id="ln-telegram-form" method="post">
                <table class="ln-form-table">
                    <tr>
                        <th><?php esc_html_e( 'Bot Token', 'leadnest' ); ?></th>
                        <td>
                            <input type="password" name="telegram_bot_token"
                                   value="<?php echo esc_attr( $options['telegram_bot_token'] ?? '' ); ?>"
                                   class="regular-text" autocomplete="new-password"
                                   placeholder="123456:ABC-DEF1234ghIkl-zyx57W2v1u123ew11">
                            <p class="description"><?php esc_html_e( 'The token provided by @BotFather when creating your bot.', 'leadnest' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'Webhook Secret', 'leadnest' ); ?></th>
                        <td>
                            <input type="text" name="telegram_webhook_secret"
                                   value="<?php echo esc_attr( $options['telegram_webhook_secret'] ?? '' ); ?>"
                                   class="regular-text" placeholder="my-telegram-secret-123">
                            <p class="description"><?php esc_html_e( 'Optional secret token for webhook verification.', 'leadnest' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'Webhook URL', 'leadnest' ); ?></th>
                        <td>
                            <div class="ln-code-block">
                                <code id="ln-telegram-webhook"><?php echo esc_html( $telegram_webhook_url ); ?></code>
                                <button type="button" class="button button-secondary ln-copy-btn" data-copy="ln-telegram-webhook">
                                    <?php esc_html_e( 'Copy', 'leadnest' ); ?>
                                </button>
                            </div>
                            <?php if ( ! empty( $options['telegram_bot_token'] ) ) : ?>
                            <div style="margin-top:10px;">
                                <button type="button" id="ln-telegram-set-webhook-btn" class="button button-secondary">
                                    <?php esc_html_e( 'Set Webhook with Telegram', 'leadnest' ); ?>
                                </button>
                                <span id="ln-telegram-webhook-status" class="ln-save-status"></span>
                            </div>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
                <div class="ln-form-actions">
                    <button type="submit" id="ln-telegram-save-btn" class="button button-primary">
                        <?php esc_html_e( 'Save Telegram Settings', 'leadnest' ); ?>
                    </button>
                    <span id="ln-telegram-save-status" class="ln-save-status"></span>
                </div>
            </form>
        </div>
    </div>

    <!-- Instagram DM (coming soon) -->
    <div class="ln-card ln-channel-coming-soon">
        <div class="ln-card-header">
            <h2>
                <span class="dashicons dashicons-camera" style="color:#E1306C;"></span>
                <?php esc_html_e( 'Instagram Direct', 'leadnest' ); ?>
            </h2>
            <span class="ln-badge" style="background:#f1f5f9;color:#64748b;"><?php esc_html_e( 'Coming Soon', 'leadnest' ); ?></span>
        </div>
        <div class="ln-card-body">
            <p class="description"><?php esc_html_e( 'Respond to Instagram DMs automatically using your LeadNest bot.', 'leadnest' ); ?></p>
        </div>
    </div>
</div>
