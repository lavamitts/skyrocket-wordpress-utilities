<?php
/**
 * Admin Menu Order Settings Section
 *
 * @package Skyrocket
 */

defined('ABSPATH') || exit;

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