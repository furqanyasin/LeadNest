<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$options   = LeadNest_DB::get_options();
$has_key   = ! empty( $options['api_key_anthropic'] ) || ! empty( $options['api_key_openai'] );
$has_prompt = ! empty( $options['system_prompt'] );
$site_key  = $options['site_key'];
$embed_url = rest_url( 'leadnest/v1/widget.js' ) . '?key=' . rawurlencode( $site_key );
$embed_code = '<script src="' . esc_url( $embed_url ) . '" async></script>';
?>
<div class="wrap ln-wrap">
    <h1 class="ln-page-title">
        <span class="dashicons dashicons-info"></span>
        <?php esc_html_e( 'Setup Guide', 'leadnest' ); ?>
    </h1>

    <div class="ln-notice ln-notice-info" style="margin-bottom:20px;">
        <span class="dashicons dashicons-welcome-learn-more"></span>
        <?php esc_html_e( 'Follow these steps to get LeadNest capturing leads on your website.', 'leadnest' ); ?>
    </div>

    <div class="ln-card">
        <div class="ln-card-header">
            <h2><?php esc_html_e( 'WordPress Setup', 'leadnest' ); ?></h2>
        </div>
        <div class="ln-setup-steps">

            <!-- Step 1 -->
            <div class="ln-setup-step <?php echo $has_key ? 'ln-step-done' : ''; ?>">
                <div class="ln-step-number"><?php echo $has_key ? '✓' : '1'; ?></div>
                <div class="ln-step-content">
                    <h3><?php esc_html_e( 'Add your AI API Key', 'leadnest' ); ?></h3>
                    <p>
                        <?php esc_html_e( 'Go to AI Settings and enter your Anthropic or OpenAI API key. LeadNest uses your own key — you pay directly and keep full control of costs.', 'leadnest' ); ?>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=leadnest-ai-settings' ) ); ?>">
                            <?php esc_html_e( 'Go to AI Settings →', 'leadnest' ); ?>
                        </a>
                    </p>
                </div>
            </div>

            <!-- Step 2 -->
            <div class="ln-setup-step <?php echo $has_prompt ? 'ln-step-done' : ''; ?>">
                <div class="ln-step-number"><?php echo $has_prompt ? '✓' : '2'; ?></div>
                <div class="ln-step-content">
                    <h3><?php esc_html_e( 'Configure System Prompt', 'leadnest' ); ?></h3>
                    <p>
                        <?php esc_html_e( 'Choose an industry template or write a custom system prompt that describes your business. This tells the bot what it should know and how to behave.', 'leadnest' ); ?>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=leadnest-ai-settings' ) ); ?>">
                            <?php esc_html_e( 'Go to AI Settings →', 'leadnest' ); ?>
                        </a>
                    </p>
                </div>
            </div>

            <!-- Step 3 -->
            <div class="ln-setup-step">
                <div class="ln-step-number">3</div>
                <div class="ln-step-content">
                    <h3><?php esc_html_e( 'Add Knowledge (Optional)', 'leadnest' ); ?></h3>
                    <p>
                        <?php esc_html_e( 'Paste content from your About, Services, or FAQ pages into the Knowledge Base. The bot will use this to answer questions accurately.', 'leadnest' ); ?>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=leadnest-knowledge' ) ); ?>">
                            <?php esc_html_e( 'Go to Knowledge Base →', 'leadnest' ); ?>
                        </a>
                    </p>
                </div>
            </div>

            <!-- Step 4 -->
            <div class="ln-setup-step">
                <div class="ln-step-number">4</div>
                <div class="ln-step-content">
                    <h3><?php esc_html_e( 'Customize Appearance', 'leadnest' ); ?></h3>
                    <p>
                        <?php esc_html_e( 'Set your bot name, color theme, and branding to match your website.', 'leadnest' ); ?>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=leadnest-appearance' ) ); ?>">
                            <?php esc_html_e( 'Go to Appearance →', 'leadnest' ); ?>
                        </a>
                    </p>
                </div>
            </div>

            <!-- Step 5 -->
            <div class="ln-setup-step ln-step-done">
                <div class="ln-step-number">✓</div>
                <div class="ln-step-content">
                    <h3><?php esc_html_e( 'Widget is Live on Your WordPress Site', 'leadnest' ); ?></h3>
                    <p>
                        <?php esc_html_e( 'The chat widget is automatically loaded on all pages of your WordPress site. No additional setup needed.', 'leadnest' ); ?>
                    </p>
                </div>
            </div>

        </div>
    </div>

    <!-- Embed on External Sites -->
    <div class="ln-card">
        <div class="ln-card-header">
            <h2><?php esc_html_e( 'Embed on External Websites', 'leadnest' ); ?></h2>
        </div>
        <div class="ln-card-body">
            <p>
                <?php esc_html_e( 'To embed the chatbot on any external website (React, HTML, Laravel, Next.js, etc.), paste this one-line script before the closing </body> tag:', 'leadnest' ); ?>
            </p>

            <div class="ln-code-block">
                <code id="ln-embed-code"><?php echo esc_html( $embed_code ); ?></code>
                <button class="button button-secondary ln-copy-btn" data-copy="ln-embed-code">
                    <?php esc_html_e( 'Copy', 'leadnest' ); ?>
                </button>
            </div>

            <p class="description" style="margin-top:10px;">
                <strong><?php esc_html_e( 'Your Site Key:', 'leadnest' ); ?></strong>
                <code><?php echo esc_html( $site_key ); ?></code>
            </p>

            <p>
                <?php esc_html_e( 'The widget uses Shadow DOM so it will never conflict with your theme or framework CSS. All data flows back to this WordPress backend.', 'leadnest' ); ?>
            </p>
        </div>
    </div>

    <!-- Tips -->
    <div class="ln-card">
        <div class="ln-card-header">
            <h2><?php esc_html_e( 'Pro Tips', 'leadnest' ); ?></h2>
        </div>
        <div class="ln-card-body">
            <ul style="margin:0;padding-left:18px;font-size:13px;line-height:2;color:#374151;">
                <li><?php esc_html_e( 'Use the Home Inspection or Real Estate template if you\'re in a service business — it\'s pre-tuned for lead capture.', 'leadnest' ); ?></li>
                <li><?php esc_html_e( 'Add your pricing, service area, and hours to the Knowledge Base. Visitors ask about these constantly.', 'leadnest' ); ?></li>
                <li><?php esc_html_e( 'Check Missed Questions weekly and add answers to train the bot.', 'leadnest' ); ?></li>
                <li><?php esc_html_e( 'Anthropic Claude with prompt caching reduces API costs by ~90% on repeated conversations.', 'leadnest' ); ?></li>
                <li><?php esc_html_e( 'Set a notification email in AI Settings to get an instant email every time a lead is captured.', 'leadnest' ); ?></li>
            </ul>
        </div>
    </div>
</div>
