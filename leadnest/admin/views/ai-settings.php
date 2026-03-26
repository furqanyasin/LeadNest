<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$options = LeadNest_DB::get_options();
$provider = $options['ai_provider'] ?? 'anthropic';

$anthropic_models = array(
    'claude-sonnet-4-6'            => 'Claude Sonnet 4.6 (Recommended)',
    'claude-opus-4-6'              => 'Claude Opus 4.6 (Most powerful)',
    'claude-haiku-4-5-20251001'    => 'Claude Haiku 4.5 (Fastest + cheapest)',
    'claude-3-5-sonnet-20241022'   => 'Claude 3.5 Sonnet (Legacy)',
    'claude-3-5-haiku-20241022'    => 'Claude 3.5 Haiku (Legacy)',
);

$openai_models = array(
    'gpt-4o'       => 'GPT-4o (Most capable)',
    'gpt-4o-mini'  => 'GPT-4o Mini (Fast + economical)',
    'gpt-4-turbo'  => 'GPT-4 Turbo',
    'gpt-3.5-turbo'=> 'GPT-3.5 Turbo (Cheapest)',
);

$industry_prompts = array(
    'general'        => array( 'label' => 'General',        'prompt' => 'You are a helpful AI assistant for this website. Your goal is to help visitors find information, answer questions, and capture their contact details if they are interested in our services. Be friendly, concise, and professional.' ),
    'home_inspection'=> array( 'label' => 'Home Inspection', 'prompt' => "You are a friendly assistant for [Company Name], a professional home inspection service.\n\nAsk visitors in order:\n1. Are they buying, selling, or already own the property?\n2. City / zip code of the property?\n3. Property type: house, condo, multi-unit, or commercial?\n4. Timeline: this week, next week, or flexible?\n5. Collect: name, phone, email\n\nKey facts:\n- Standard inspection starts at \$299 (under 2,000 sq ft)\n- Report delivered within 24 hours\n- Available Mon–Sat, 8AM–6PM\n\nAlways offer to schedule directly at the end." ),
    'real_estate'    => array( 'label' => 'Real Estate',    'prompt' => "You are a knowledgeable real estate assistant for [Agency Name]. Help visitors find the right property and capture their contact information.\n\nAsk:\n1. Are they buying or selling?\n2. What area are they interested in?\n3. Budget range?\n4. Timeline?\n5. Collect: name, email, phone" ),
    'healthcare'     => array( 'label' => 'Healthcare',     'prompt' => "You are a friendly patient coordinator for [Clinic Name]. Help visitors with appointment inquiries and information about services.\n\nIMPORTANT: Never provide medical advice. Always direct medical questions to a qualified professional.\n\nHelp with: appointment scheduling, service inquiries, directions, insurance questions (general).\nCollect: name, contact number, reason for visit." ),
    'law_firm'       => array( 'label' => 'Law Firm',       'prompt' => "You are a professional intake specialist for [Law Firm Name]. Help potential clients understand your services and schedule a consultation.\n\nIMPORTANT: Never provide legal advice. Always state that a qualified attorney should be consulted.\n\nCollect: name, email, phone, brief description of legal matter." ),
    'ecommerce'      => array( 'label' => 'E-Commerce',     'prompt' => "You are a helpful shopping assistant for [Store Name]. Help visitors find products, answer questions about shipping, returns, and policies.\n\nBe helpful and knowledgeable about products. If a visitor shows purchase intent, offer to help them complete their order or sign up for updates." ),
);
?>
<div class="wrap ln-wrap">
    <h1 class="ln-page-title">
        <span class="dashicons dashicons-admin-generic"></span>
        <?php esc_html_e( 'AI Settings', 'leadnest' ); ?>
    </h1>

    <form id="ln-ai-settings-form" method="post">
        <!-- AI Provider -->
        <div class="ln-card">
            <div class="ln-card-header">
                <h2><?php esc_html_e( 'AI Provider', 'leadnest' ); ?></h2>
            </div>
            <div class="ln-card-body">
                <input type="hidden" name="ai_provider" id="ai_provider"
                       value="<?php echo esc_attr( $provider ); ?>">

                <div class="ln-provider-tabs" style="margin-bottom:20px;">
                    <button type="button" class="ln-provider-tab <?php echo $provider === 'anthropic' ? 'active' : ''; ?>"
                            data-provider="anthropic">
                        Anthropic Claude
                    </button>
                    <button type="button" class="ln-provider-tab <?php echo $provider === 'openai' ? 'active' : ''; ?>"
                            data-provider="openai">
                        OpenAI GPT
                    </button>
                </div>

                <!-- Anthropic settings -->
                <div id="ln-provider-anthropic"
                     class="ln-provider-section <?php echo $provider === 'anthropic' ? 'active' : ''; ?>">
                    <table class="ln-form-table">
                        <tr>
                            <th><?php esc_html_e( 'API Key', 'leadnest' ); ?></th>
                            <td>
                                <input type="password" name="api_key_anthropic" id="api_key_anthropic"
                                       value="<?php echo esc_attr( $options['api_key_anthropic'] ); ?>"
                                       class="regular-text" autocomplete="new-password" placeholder="sk-ant-…">
                                <p class="description">
                                    <?php esc_html_e( 'Your Anthropic API key. Get one at console.anthropic.com', 'leadnest' ); ?>
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e( 'Model', 'leadnest' ); ?></th>
                            <td>
                                <select name="model_anthropic" id="model_anthropic">
                                    <?php foreach ( $anthropic_models as $model_id => $model_label ) : ?>
                                    <option value="<?php echo esc_attr( $model_id ); ?>"
                                            <?php selected( $options['model_anthropic'], $model_id ); ?>>
                                        <?php echo esc_html( $model_label ); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <p class="description">
                                    <?php esc_html_e( 'Prompt caching is enabled automatically to reduce costs by up to 90%.', 'leadnest' ); ?>
                                </p>
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- OpenAI settings -->
                <div id="ln-provider-openai"
                     class="ln-provider-section <?php echo $provider === 'openai' ? 'active' : ''; ?>">
                    <table class="ln-form-table">
                        <tr>
                            <th><?php esc_html_e( 'API Key', 'leadnest' ); ?></th>
                            <td>
                                <input type="password" name="api_key_openai" id="api_key_openai"
                                       value="<?php echo esc_attr( $options['api_key_openai'] ); ?>"
                                       class="regular-text" autocomplete="new-password" placeholder="sk-…">
                                <p class="description">
                                    <?php esc_html_e( 'Your OpenAI API key. Get one at platform.openai.com', 'leadnest' ); ?>
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e( 'Model', 'leadnest' ); ?></th>
                            <td>
                                <select name="model_openai" id="model_openai">
                                    <?php foreach ( $openai_models as $model_id => $model_label ) : ?>
                                    <option value="<?php echo esc_attr( $model_id ); ?>"
                                            <?php selected( $options['model_openai'], $model_id ); ?>>
                                        <?php echo esc_html( $model_label ); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                    </table>
                </div>

                <div style="margin-top:16px;">
                    <button type="button" id="ln-test-connection-btn" class="button button-secondary">
                        <?php esc_html_e( 'Test Connection', 'leadnest' ); ?>
                    </button>
                    <div id="ln-test-result" style="margin-top:10px;display:none;"></div>
                </div>
            </div>
        </div>

        <!-- System Prompt -->
        <div class="ln-card">
            <div class="ln-card-header">
                <h2><?php esc_html_e( 'System Prompt', 'leadnest' ); ?></h2>
            </div>
            <div class="ln-card-body">
                <p class="description" style="margin-bottom:10px;">
                    <?php esc_html_e( 'Choose an industry template or write your own. Q&A pairs and knowledge base content are injected automatically.', 'leadnest' ); ?>
                </p>

                <div style="margin-bottom:12px;display:flex;gap:8px;flex-wrap:wrap;">
                    <?php foreach ( $industry_prompts as $key => $data ) : ?>
                    <button type="button" class="button button-secondary ln-template-btn"
                            data-prompt="<?php echo esc_attr( $data['prompt'] ); ?>">
                        <?php echo esc_html( $data['label'] ); ?>
                    </button>
                    <?php endforeach; ?>
                </div>

                <textarea name="system_prompt" id="system_prompt" rows="10"
                          style="width:100%;max-width:700px;font-family:monospace;font-size:12px;"><?php echo esc_textarea( $options['system_prompt'] ); ?></textarea>
            </div>
        </div>

        <!-- Lead Capture Settings -->
        <div class="ln-card">
            <div class="ln-card-header">
                <h2><?php esc_html_e( 'Lead Capture', 'leadnest' ); ?></h2>
            </div>
            <div class="ln-card-body">
                <table class="ln-form-table">
                    <tr>
                        <th><?php esc_html_e( 'Enable Lead Capture', 'leadnest' ); ?></th>
                        <td>
                            <label style="display:flex;align-items:center;gap:8px;">
                                <input type="checkbox" name="lead_capture_enabled" value="1"
                                       <?php checked( ! empty( $options['lead_capture_enabled'] ) ); ?>>
                                <?php esc_html_e( 'Automatically capture contact details from conversations', 'leadnest' ); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'Trigger After', 'leadnest' ); ?></th>
                        <td>
                            <input type="number" name="lead_capture_trigger" id="lead_capture_trigger"
                                   value="<?php echo esc_attr( $options['lead_capture_trigger'] ); ?>"
                                   min="1" max="20" class="small-text">
                            <?php esc_html_e( 'messages', 'leadnest' ); ?>
                            <p class="description"><?php esc_html_e( 'Ask for contact info after this many visitor messages.', 'leadnest' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'Collect Fields', 'leadnest' ); ?></th>
                        <td>
                            <div style="display:flex;flex-direction:column;gap:6px;">
                                <label style="display:flex;align-items:center;gap:8px;">
                                    <input type="checkbox" name="collect_name" value="1"
                                           <?php checked( ! empty( $options['collect_name'] ) ); ?>>
                                    <?php esc_html_e( 'Name', 'leadnest' ); ?>
                                </label>
                                <label style="display:flex;align-items:center;gap:8px;">
                                    <input type="checkbox" name="collect_email" value="1"
                                           <?php checked( ! empty( $options['collect_email'] ) ); ?>>
                                    <?php esc_html_e( 'Email', 'leadnest' ); ?>
                                </label>
                                <label style="display:flex;align-items:center;gap:8px;">
                                    <input type="checkbox" name="collect_phone" value="1"
                                           <?php checked( ! empty( $options['collect_phone'] ) ); ?>>
                                    <?php esc_html_e( 'Phone', 'leadnest' ); ?>
                                </label>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'Name Question', 'leadnest' ); ?></th>
                        <td>
                            <input type="text" name="name_question"
                                   value="<?php echo esc_attr( $options['name_question'] ); ?>"
                                   class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'Email Question', 'leadnest' ); ?></th>
                        <td>
                            <input type="text" name="email_question"
                                   value="<?php echo esc_attr( $options['email_question'] ); ?>"
                                   class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'Phone Question', 'leadnest' ); ?></th>
                        <td>
                            <input type="text" name="phone_question"
                                   value="<?php echo esc_attr( $options['phone_question'] ); ?>"
                                   class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'Notification Email', 'leadnest' ); ?></th>
                        <td>
                            <input type="email" name="notification_email"
                                   value="<?php echo esc_attr( $options['notification_email'] ); ?>"
                                   class="regular-text" placeholder="admin@yoursite.com">
                            <p class="description"><?php esc_html_e( 'Receive an email when a new lead is captured. Leave blank to disable.', 'leadnest' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'Max History', 'leadnest' ); ?></th>
                        <td>
                            <input type="number" name="max_history"
                                   value="<?php echo esc_attr( $options['max_history'] ); ?>"
                                   min="5" max="100" class="small-text">
                            <?php esc_html_e( 'message pairs', 'leadnest' ); ?>
                            <p class="description"><?php esc_html_e( 'Number of conversation turns sent to the AI. Lower = cheaper. Recommended: 20.', 'leadnest' ); ?></p>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="ln-form-actions">
            <button type="submit" id="ln-ai-save-btn" class="button button-primary">
                <?php esc_html_e( 'Save AI Settings', 'leadnest' ); ?>
            </button>
            <span id="ln-ai-save-status" class="ln-save-status"></span>
        </div>
    </form>
</div>

<script>
jQuery( function( $ ) {
    $( '.ln-template-btn' ).on( 'click', function() {
        var prompt = $( this ).data( 'prompt' );
        if ( confirm( 'Replace current system prompt with this template?' ) ) {
            $( '#system_prompt' ).val( prompt );
        }
    } );
} );
</script>
