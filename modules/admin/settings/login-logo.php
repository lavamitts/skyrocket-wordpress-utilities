<?php
/**
 * Admin Login Logo Settings Section
 *
 * @package Skyrocket
 */

defined('ABSPATH') || exit;

$options = get_option('swu_options', []);
$logo_id = absint($options['login_logo_id'] ?? 0);
$logo_url = $logo_id ? wp_get_attachment_image_url($logo_id, 'full') : false;
?>

<h2>Login Logo</h2>
<table class="form-table" role="presentation">
    <tbody>
        <tr>
            <th scope="row">
                <label for="swu-login-logo-id">
                    WordPress login logo
                </label>
            </th>

            <td>
                <div class="swu-image-picker">

                    <input type="hidden" id="swu-login-logo-id" name="swu_options[login_logo_id]"
                        value="<?php echo esc_attr($logo_id); ?>">

                    <div class="swu-image-preview">
                        <?php if ($logo_url): ?>
                            <img src="<?php echo esc_url($logo_url); ?>" alt=""
                                style="max-width: 200px; max-height: 100px;">
                        <?php else: ?>
                            <span>No logo selected</span>
                        <?php endif; ?>
                    </div>

                    <p>
                        <button type="button" class="button swu-select-image">
                            Select logo
                        </button>

                        <button type="button" class="button swu-remove-image" <?php disabled(!$logo_id); ?>>
                            Remove logo
                        </button>
                    </p>

                    <p class="description">
                        Select the image to display above the WordPress login form.
                    </p>

                </div>
            </td>
        </tr>
    </tbody>
</table>