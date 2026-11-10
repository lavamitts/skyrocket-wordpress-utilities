<?php

defined('ABSPATH') || exit;

$options = get_option('swu_options', []);

$logo_id = absint($options['login_logo_id'] ?? 0);

$logo_url = $logo_id
    ? wp_get_attachment_image_url($logo_id, 'full')
    : false;

?>

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



<?php
/**
 * Admin menu ordering settings.
 */

global $menu;

?>

<h2>Admin menu order</h2>

<p>
    Drag the menu items into your preferred order.
    Items at the top of this list will appear first in the WordPress admin menu.
</p>

<ul id="swu-admin-menu-sortable" class="swu-admin-menu-sortable">

    <?php foreach ($menu as $item): ?>

        <?php
        $slug = (string) ($item[2] ?? '');

        if ($slug === '') {
            continue;
        }

        $label = wp_strip_all_tags($item[0] ?? '');

        if ($label === '') {
            $label = $slug;
        }
        ?>

        <li class="swu-admin-menu-order-item">

            <input type="hidden" name="swu_options[admin_menu_order][]" value="<?php echo esc_attr($slug); ?>">

            <span class="swu-menu-order-handle" title="Drag to reorder" aria-label="Drag to reorder">
                <span class="dashicons dashicons-menu"></span>
            </span>

            <span class="swu-menu-order-label">
                <?php echo esc_html($label); ?>
            </span>

        </li>

    <?php endforeach; ?>

</ul>