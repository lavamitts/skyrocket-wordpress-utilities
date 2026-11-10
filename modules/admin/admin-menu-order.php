<?php
/**
 * Control the order of the WordPress admin menu.
 */

defined('ABSPATH') || exit;

/**
 * Return the default preferred admin menu order.
 */
function swu_default_admin_menu_order()
{
    return [
        'index.php',                    // Dashboard
        'woocommerce',                  // WooCommerce
        'edit.php?post_type=product',   // Products
        'edit.php?post_type=page',      // Pages
        'edit.php',                     // Posts
        'upload.php',                   // Media
        'themes.php',                   // Appearance
        'plugins.php',                  // Plugins
        'users.php',                    // Users
        'tools.php',                    // Tools
        'options-general.php',          // Settings
    ];
}
/**
 * Get the preferred order (saved or default).
 */
function swu_get_preferred_menu_order()
{
    $options = get_option('swu_options', []);
    $order = $options['admin_menu_order'] ?? [];

    if (!is_array($order) || empty($order)) {
        $order = swu_default_admin_menu_order();
    }

    return $order;
}

/**
 * Tell WordPress we want to control menu order.
 */
add_filter('custom_menu_order', '__return_true', PHP_INT_MAX);

/**
 * Apply our order via the menu_order filter.
 *
 * @param array $current_order Menu slugs in the current order.
 * @return array
 */
function swu_filter_menu_order($current_order)
{
    $preferred = swu_get_preferred_menu_order();
    $ordered = [];

    foreach ($preferred as $slug) {
        $index = array_search($slug, $current_order, true);

        if ($index !== false) {
            $ordered[] = $slug;
            unset($current_order[$index]);
        }
    }

    // Anything not explicitly ordered goes at the end, in its existing order.
    // return array_merge($ordered, array_values($current_order));
    return array_values(array_unique(array_merge($ordered, array_values($current_order))));
}
add_filter('menu_order', 'swu_filter_menu_order', PHP_INT_MAX);

