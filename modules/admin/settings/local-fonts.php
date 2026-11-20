<?php
/**
 * Local Google Fonts Settings Tab
 *
 * @package Skyrocket
 */

defined('ABSPATH') || exit;

$options = get_option('swu_options', []);
$is_enabled = !empty($options['local_fonts_enabled']);

// Check if the physical CSS asset exists in the active child theme
$child_css_rel = '/assets/css/local-fonts.css';
$child_css_path = get_stylesheet_directory() . $child_css_rel;
$file_exists = file_exists($child_css_path);
?>

<div class="swu-tab-content">
    <h2>Local Google Fonts Governance</h2>
    <p class="description">
        Eliminate external requests to Google’s font servers by self-hosting font files locally within your child theme.
        When active, Blocksy's remote font stylesheets and preconnect hints are automatically stripped.
    </p>

    <table class="form-table" role="presentation">
        <tr>
            <th scope="row">Enable Local Fonts</th>
            <td>
                <label for="local_fonts_enabled">
                    <input type="checkbox" name="swu_options[local_fonts_enabled]" id="local_fonts_enabled" value="1"
                        <?php checked($is_enabled, 1); ?>>
                    Serve Google Fonts locally from the active child theme
                </label>
                <p class="description">
                    Uncheck this box to instantly fall back to Blocksy’s native remote Google Fonts loader if you are
                    previewing or testing new typefaces in the Customizer.
                </p>
            </td>
        </tr>
        <tr>
            <th scope="row">Asset Status Check</th>
            <td>
                <?php if ($file_exists): ?>
                    <p style="color: #00a32a; font-weight: 600; margin: 0;">
                        ✔ Local stylesheet detected:
                        <code><?php echo esc_html(get_stylesheet_directory_uri() . $child_css_rel); ?></code>
                    </p>
                <?php else: ?>
                    <p style="color: #d63638; font-weight: 600; margin: 0;">
                        ⚠ Missing local stylesheet: Expected at
                        <code><?php echo esc_html(get_stylesheet_directory() . $child_css_rel); ?></code>.
                        The system will gracefully fall back to remote Google Fonts until the file is uploaded.
                    </p>
                <?php endif; ?>
            </td>
        </tr>
    </table>
</div>