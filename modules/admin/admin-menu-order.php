<?php
/**
 * Custom WordPress Admin Menu Order
 *
 * Controls the order of items in the WordPress administration menu,
 * allowing a preferred menu structure to be defined centrally and
 * optionally customised through the plugin's settings.
 *
 * The menu order is retrieved from the `swu_options` option. If no
 * valid custom order has been saved, the file uses a predefined
 * default order.
 *
 * Features:
 *
 * - Default menu order:
 * Defines a preferred arrangement of standard WordPress admin menu
 * items, including Dashboard, WooCommerce, Products, Pages, Posts,
 * Media, Appearance, Plugins, Users, Tools and Settings.
 *
 * - Configurable order:
 * Retrieves a saved menu order from the `admin_menu_order` key
 * within the `swu_options` option.
 *
 * - Fallback handling:
 * Uses the default menu order when no saved order exists, or when
 * the stored value is not a valid, non-empty array.
 *
 * - Menu filtering:
 * Applies the preferred order to menu items registered by WordPress
 * and other plugins. Items not explicitly included in the preferred
 * order are retained and appended afterwards in their existing order.
 *
 * - Duplicate prevention:
 * Removes duplicate menu slugs from the resulting order.
 *
 * Behaviour:
 *
 * - Only reorders menu items that are present in the current admin menu.
 * - Ignores preferred menu slugs that are not currently registered.
 * - Preserves the relative order of menu items not explicitly listed.
 * - Does not remove menu items or control their visibility.
 *
 * Settings:
 *
 * - `swu_options['admin_menu_order']`:
 * Optional array of admin menu slugs defining the preferred order.
 * When absent or invalid, the built-in default order is used.
 *
 * Hooks:
 *
 * - `custom_menu_order`:
 * Enables custom menu ordering in WordPress.
 *
 * - `menu_order`:
 * Applies the preferred menu order to the registered admin menu items.
 *
 * Dependencies:
 *
 * - WordPress core admin menu and options APIs.
 *
 * @package Skyrocket
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

