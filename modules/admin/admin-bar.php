<?php
add_action('admin_bar_menu', 'add_custom_admin_bar_links', 60);

function add_custom_admin_bar_links($wp_admin_bar)
{
    // Only allow users with administrator privileges to see these links
    if (!current_user_can('manage_options')) {
        return;
    }

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



    // // Store management heading
    // $wp_admin_bar->add_node(array(
    //     'id' => 'custom-store-management',
    //     'title' => '<strong>Store management</strong>',
    //     'parent' => $parent,
    //     'href' => false,
    // ));

    // // Add Orders
    // $wp_admin_bar->add_node(array(
    //     'id' => 'custom-orders',
    //     'title' => 'Orders',
    //     'parent' => $parent,
    //     'href' => admin_url('admin.php?page=wc-orders'),
    // ));

    // // Add Products
    // $wp_admin_bar->add_node(array(
    //     'id' => 'custom-products',
    //     'title' => 'Products',
    //     'parent' => $parent,
    //     'href' => admin_url('edit.php?post_type=product'),
    // ));

    // // Add Product reviews
    // $wp_admin_bar->add_node(array(
    //     'id' => 'custom-product-reviews',
    //     'title' => 'Product reviews',
    //     'parent' => $parent,
    //     'href' => admin_url('edit.php?post_type=product&page=product-reviews'),
    // ));

    // // Add separator
    // $wp_admin_bar->add_node(array(
    //     'id' => 'custom-separator3',
    //     'title' => '──────────',
    //     'parent' => $parent,
    //     'href' => false,
    // ));

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

    // Add Events
    $wp_admin_bar->add_node(array(
        'id' => 'custom-events',
        'title' => 'Events',
        'parent' => $parent,
        'href' => admin_url('edit.php?post_type=pavilion_event'),
    ));

    // Add Media
    $wp_admin_bar->add_node(array(
        'id' => 'custom-media',
        'title' => 'Media',
        'parent' => $parent,
        'href' => admin_url('upload.php'),
    ));

    // Menus
    $wp_admin_bar->add_node(array(
        'id' => 'custom-menus',
        'title' => 'Menus',
        'parent' => $parent,
        'href' => admin_url('nav-menus.php'),
    ));

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

        // Customise
        $wp_admin_bar->add_node(array(
            'id' => 'custom-customize',
            'title' => 'Customise theme',
            'parent' => $parent,
            'href' => admin_url('customize.php?return=%2Fwp-admin%2Findex.php'),
        ));

        // Admin heading
        $wp_admin_bar->add_node(array(
            'id' => 'custom-admin',
            'title' => '<strong>Admin tools</strong>',
            'parent' => $parent,
            'href' => false,
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
    }
}

