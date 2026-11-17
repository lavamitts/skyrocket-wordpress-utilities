<?php
/**
 * Customise the WordPress admin toolbar navigation.
 *
 * Adds a collection of custom navigation links to the site-title dropdown
 * menu in the WordPress admin toolbar, providing convenient access to
 * frequently used administration and content management screens.
 *
 *
 * Features:
 *
 * - Dashboard:
 *   Adds a direct link to the WordPress administration dashboard.
 *
 * - WooCommerce management:
 *   When WooCommerce is active, adds links to Orders, Products and
 *   Product reviews, grouped under a Store management heading.
 *
 * - Content management:
 *   Adds links to Pages, Posts and Media.
 *   Also includes links to Events when the `pavilion_event` post type
 *   is registered, and Menus when the active theme supports navigation
 *   menus or registered navigation menus are available.
 *
 * - Block patterns:
 *   Adds a link to the My Patterns section of the WordPress Site Editor.
 *
 * - Administration tools:
 *   Adds a separate section containing links to the Theme Customiser,
 *   Site users, Plugins and the Skyrocket customisation settings page.
 *
 * Conditional integrations:
 *
 * - WooCommerce:
 *   Store management links are only displayed when the WooCommerce
 *   class is available.
 *
 * - Events:
 *   The Events link is only displayed when the `pavilion_event`
 *   custom post type is registered.
 *
 * - Navigation menus:
 *   The Menus link is only displayed when the active theme supports
 *   menus or registered navigation menus are available.
 *
 * Hooks:
 *
 * - `admin_bar_menu`:
 *   Registers the custom navigation links in the WordPress admin toolbar
 *   at priority 60.
 *
 * Dependencies:
 *
 * - WordPress core admin toolbar API (`WP_Admin_Bar`).
 * - WordPress capability and theme support APIs.
 * - Optional WooCommerce integration.
 * - Optional `pavilion_event` custom post type.
 *
 * @package Skyrocket
 */


add_action('admin_bar_menu', 'swu_add_custom_admin_bar_links', 60);

function swu_add_custom_admin_bar_links($wp_admin_bar)
{
    // The parent ID for the site-title dropdown menu
    $parent = 'site-name';

    // Add Dashboard
    $wp_admin_bar->add_node(array(
        'id' => 'custom-dashboard',
        'title' => 'Dashboard',
        'parent' => $parent,
        'href' => admin_url('index.php'),
    ));

    // Add separator
    $wp_admin_bar->add_node(array(
        'id' => 'custom-separator',
        'title' => '──────────',
        'parent' => $parent,
        'href' => false,
    ));

    if (class_exists('WooCommerce')) {
        // Store management heading
        $wp_admin_bar->add_node(array(
            'id' => 'custom-store-management',
            'title' => '<strong>Store management</strong>',
            'parent' => $parent,
            'href' => false,
        ));

        // Add Orders
        $wp_admin_bar->add_node(array(
            'id' => 'custom-orders',
            'title' => 'Orders',
            'parent' => $parent,
            'href' => admin_url('admin.php?page=wc-orders'),
        ));

        // Add Products
        $wp_admin_bar->add_node(array(
            'id' => 'custom-products',
            'title' => 'Products',
            'parent' => $parent,
            'href' => admin_url('edit.php?post_type=product'),
        ));

        // Add Product reviews
        $wp_admin_bar->add_node(array(
            'id' => 'custom-product-reviews',
            'title' => 'Product reviews',
            'parent' => $parent,
            'href' => admin_url('edit.php?post_type=product&page=product-reviews'),
        ));

        // Add separator
        $wp_admin_bar->add_node(array(
            'id' => 'custom-separator3',
            'title' => '──────────',
            'parent' => $parent,
            'href' => false,
        ));
    }

    // Content management heading
    $wp_admin_bar->add_node(array(
        'id' => 'custom-content-management',
        'title' => '<strong>Content management</strong>',
        'parent' => $parent,
        'href' => false,
    ));

    // Add Pages
    $wp_admin_bar->add_node(array(
        'id' => 'custom-pages',
        'title' => 'Pages',
        'parent' => $parent,
        'href' => admin_url('edit.php?post_type=page'),
    ));

    // Add Posts
    $wp_admin_bar->add_node(array(
        'id' => 'custom-posts',
        'title' => 'Posts',
        'parent' => $parent,
        'href' => admin_url('edit.php'),
    ));

    if (post_type_exists('pavilion_event')) {
        // Add Events
        $wp_admin_bar->add_node(array(
            'id' => 'custom-events',
            'title' => 'Events',
            'parent' => $parent,
            'href' => admin_url('edit.php?post_type=pavilion_event'),
        ));
    }

    // Add Media
    $wp_admin_bar->add_node(array(
        'id' => 'custom-media',
        'title' => 'Media',
        'parent' => $parent,
        'href' => admin_url('upload.php'),
    ));

    if (current_theme_supports('menus') || !empty(get_registered_nav_menus())) {
        // Menus
        $wp_admin_bar->add_node(array(
            'id' => 'custom-menus',
            'title' => 'Menus',
            'parent' => $parent,
            'href' => admin_url('nav-menus.php'),
        ));
    }

    // My Patterns
    $wp_admin_bar->add_node(array(
        'id' => 'custom-patterns',
        'title' => 'My Patterns',
        'parent' => $parent,
        'href' => admin_url('site-editor.php?p=%2Fpattern&postType=wp_block&categoryId=my-patterns'),
    ));

    if (current_user_can('manage_options')) {
        // Add separator
        $wp_admin_bar->add_node(array(
            'id' => 'custom-separator2',
            'title' => '──────────',
            'parent' => $parent,
            'href' => false,
        ));

        // Admin heading
        $wp_admin_bar->add_node(array(
            'id' => 'custom-admin',
            'title' => '<strong>Admin tools</strong>',
            'parent' => $parent,
            'href' => false,
        ));

        // Customise
        $wp_admin_bar->add_node(array(
            'id' => 'custom-customize',
            'title' => 'Customise theme',
            'parent' => $parent,
            'href' => admin_url('customize.php?return=%2Fwp-admin%2Findex.php'),
        ));

        $wp_admin_bar->add_node(array(
            'id' => 'custom-users',
            'title' => 'Site users',
            'parent' => $parent,
            'href' => admin_url('users.php'),
        ));

        $wp_admin_bar->add_node(array(
            'id' => 'custom-plugins',
            'title' => 'Plugins',
            'parent' => $parent,
            'href' => admin_url('plugins.php'),
        ));

        $wp_admin_bar->add_node(array(
            'id' => 'custom-skyrocket-customisations',
            'title' => 'Skyrocket customisations',
            'parent' => $parent,
            'href' => admin_url('options-general.php?page=swu-settings'),
        ));
    }
}

