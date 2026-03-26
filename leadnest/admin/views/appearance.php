<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$options = LeadNest_DB::get_options();
$colors  = LeadNest_DB::resolve_colors( $options );
$presets = LeadNest_DB::get_color_presets();

$preset_colors = array(
    'blue'   => '#2563eb',
    'green'  => '#16a34a',
    'purple' => '#7c3aed',
    'orange' => '#ea580c',
    'red'    => '#dc2626',
    'teal'   => '#0d9488',
    'dark'   => '#1e293b',
    'pink'   => '#db2777',
);
?>
<div class="wrap ln-wrap">
    <h1 class="ln-page-title">
        <span class="dashicons dashicons-art"></span>
        <?php esc_html_e( 'Appearance', 'leadnest' ); ?>
    </h1>

    <form id="ln-appearance-form" method="post">
        <div class="ln-card">
            <div class="ln-card-header">
                <h2><?php esc_html_e( 'Widget Branding', 'leadnest' ); ?></h2>
            </div>
            <div class="ln-card-body">
                <table class="ln-form-table">
                    <tr>
                        <th><?php esc_html_e( 'Bot Name', 'leadnest' ); ?></th>
                        <td>
                            <input type="text" name="bot_name" id="bot_name"
                                   value="<?php echo esc_attr( $options['bot_name'] ); ?>"
                                   placeholder="LeadNest" class="regular-text">
                            <p class="description"><?php esc_html_e( 'Displayed in the chat header.', 'leadnest' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'Chat Button Text', 'leadnest' ); ?></th>
                        <td>
                            <input type="text" name="cta_button_text" id="cta_button_text"
                                   value="<?php echo esc_attr( $options['cta_button_text'] ); ?>"
                                   placeholder="Chat with us" class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'Header Icon URL', 'leadnest' ); ?></th>
                        <td>
                            <input type="text" name="header_icon_url" id="header_icon_url"
                                   value="<?php echo esc_attr( $options['header_icon_url'] ); ?>"
                                   placeholder="https://…/icon.png" class="regular-text">
                            <p class="description"><?php esc_html_e( 'Optional. Leave blank to use the default chat icon.', 'leadnest' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'Footer Text', 'leadnest' ); ?></th>
                        <td>
                            <input type="text" name="footer_text" id="footer_text"
                                   value="<?php echo esc_attr( $options['footer_text'] ); ?>"
                                   placeholder="Powered by LeadNest" class="regular-text">
                            <label style="display:flex;align-items:center;gap:6px;margin-top:8px;">
                                <input type="checkbox" name="show_footer" value="1"
                                    <?php checked( ! empty( $options['show_footer'] ) ); ?>>
                                <?php esc_html_e( 'Show footer text', 'leadnest' ); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'Typing Delay (ms)', 'leadnest' ); ?></th>
                        <td>
                            <input type="number" name="typing_delay" id="typing_delay"
                                   value="<?php echo esc_attr( $options['typing_delay'] ); ?>"
                                   min="0" max="3000" class="small-text">
                            <p class="description"><?php esc_html_e( 'Simulated typing pause before showing bot reply. 0 to disable.', 'leadnest' ); ?></p>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="ln-card">
            <div class="ln-card-header">
                <h2><?php esc_html_e( 'Color Theme', 'leadnest' ); ?></h2>
            </div>
            <div class="ln-card-body">
                <input type="hidden" name="color_preset" id="color_preset"
                       value="<?php echo esc_attr( $options['color_preset'] ); ?>">

                <p class="description" style="margin-bottom:10px;">
                    <?php esc_html_e( 'Choose a preset or set custom colors:', 'leadnest' ); ?>
                </p>

                <div class="ln-color-presets">
                    <?php foreach ( $preset_colors as $key => $hex ) :
                        $active = $options['color_preset'] === $key ? 'active' : '';
                    ?>
                    <div class="ln-color-preset <?php echo esc_attr( $active ); ?>"
                         data-preset="<?php echo esc_attr( $key ); ?>"
                         data-primary="<?php echo esc_attr( $hex ); ?>"
                         data-text="#ffffff"
                         style="background:<?php echo esc_attr( $hex ); ?>;"
                         title="<?php echo esc_attr( ucfirst( $key ) ); ?>"></div>
                    <?php endforeach; ?>
                    <div class="ln-color-preset <?php echo $options['color_preset'] === 'custom' ? 'active' : ''; ?>"
                         data-preset="custom"
                         style="background: linear-gradient(135deg, #ff6b6b, #4ecdc4, #45b7d1);"
                         title="Custom"></div>
                </div>

                <div class="ln-custom-colors" style="<?php echo $options['color_preset'] !== 'custom' ? 'display:none;' : ''; ?>">
                    <div class="ln-custom-color-field">
                        <label><?php esc_html_e( 'Primary Color', 'leadnest' ); ?></label>
                        <input type="text" id="custom_primary_color" name="custom_primary_color"
                               value="<?php echo esc_attr( $options['custom_primary_color'] ); ?>"
                               class="wp-color-picker-field" data-default-color="#2563eb">
                    </div>
                    <div class="ln-custom-color-field">
                        <label><?php esc_html_e( 'Text Color', 'leadnest' ); ?></label>
                        <input type="text" id="custom_text_color" name="custom_text_color"
                               value="<?php echo esc_attr( $options['custom_text_color'] ); ?>"
                               class="wp-color-picker-field" data-default-color="#ffffff">
                    </div>
                </div>

                <div id="ln-color-preview"
                     style="background:<?php echo esc_attr( $colors['primary'] ); ?>;color:<?php echo esc_attr( $colors['text'] ); ?>;">
                    <span class="dashicons dashicons-format-chat"></span>
                    <?php echo esc_html( $options['bot_name'] ?: 'LeadNest' ); ?>
                </div>
            </div>
        </div>

        <div class="ln-form-actions">
            <button type="submit" id="ln-appearance-save-btn" class="button button-primary">
                <?php esc_html_e( 'Save Appearance', 'leadnest' ); ?>
            </button>
            <span id="ln-appearance-save-status" class="ln-save-status"></span>
        </div>
    </form>
</div>
