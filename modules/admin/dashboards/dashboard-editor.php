<?php
/**
 * Common Tasks and WooCommerce Dashboard Widgets
 *
 * Adds role-restricted dashboard widgets to the WordPress
 * administration dashboard, providing shortcuts to frequently used
 * content management and store management screens.
 *
 * The Common tasks widget provides links for managing pages, posts,
 * media and navigation menus. When WooCommerce is active, an
 * additional Store tasks widget provides shortcuts for managing
 * products, product categories, attributes, reviews and orders.
 *
 * Features:
 *
 * - Common tasks widget:
 *   Registers a dashboard widget containing shortcuts to the Pages,
 *   Media, Posts and Navigation Menus administration screens.
 *
 * - Store tasks widget:
 *   Conditionally registers a second dashboard widget when
 *   WooCommerce is active, providing shortcuts to product management,
 *   product categories, product attributes, product reviews and orders.
 *
 * - WooCommerce detection:
 *   Uses `class_exists('WooCommerce')` to check whether WooCommerce
 *   is active before registering the Store tasks widget.
 *
 * - Dashboard placement:
 *   Displays the Common tasks widget in the main dashboard area and
 *   the Store tasks widget in the sidebar, both with high priority.
 *
 * Behaviour:
 *
 * - Registers the widgets during the `wp_dashboard_setup` action.
 * - Uses capability checks intended to restrict access to
 *   administrators and editors.
 * - Registers the Store tasks widget only when WooCommerce is active.
 * - Provides navigation shortcuts only; it does not implement
 *   additional content or store management functionality.
 *
 * Hooks:
 *
 * - `wp_dashboard_setup`:
 *   Registers the Common tasks and, where applicable, Store tasks
 *   dashboard widgets.
 *
 * Dependencies:
 *
 * - WordPress core dashboard widget and administration APIs.
 * - WooCommerce, for the Store tasks widget.
 * - Appropriate WordPress and WooCommerce permissions for the
 *   linked administration screens.
 *
 * @package Skyrocket
 */

// Add the custom dashboard widget
function add_custom_dashboard_widget_editor()
{
    // Check if user has the specific role (administrator or editor)
    if (current_user_can('administrator') || current_user_can('editor')) {

        wp_add_dashboard_widget(
            'common_tasks_widget',                   // Widget slug
            'Common tasks',                          // Title
            'display_common_tasks_widget_editor',    // Display function
            null,                                    // Config function (optional)
            null,                                    // Callback args (optional)
            'normal',                                // Context (normal, side, column3, column4)
            'high'                                   // Priority (high = top)
        );

        // Check if WooCommerce is installed and active
        if (class_exists('WooCommerce')) {
            wp_add_dashboard_widget(
                'ecommerce_tasks_widget_editor',         // Widget slug
                'Store tasks',                           // Title
                'display_ecommerce_tasks_widget_editor', // Display function
                null,                                    // Config function (optional)
                null,                                    // Callback args (optional)
                'side',                                  // Context (normal, side, column3, column4)
                'high'                                   // Priority (high = top)
            );
        }
    }
}
add_action('wp_dashboard_setup', 'add_custom_dashboard_widget_editor');

// Display the widget content
function display_common_tasks_widget_editor()
{
    ?>
    <div class="common-tasks-widget custom-admin-widget">
        <div class="column-one">
            <h2>Pages</h2>
            <ul>
                <li><a href="/wp-admin/edit.php?post_type=page">View all pages</a></li>
                <li><a href="/wp-admin/post-new.php?post_type=page">Add a new page</a></li>
            </ul>

            <h2>Media</h2>
            <ul>
                <li><a href="/wp-admin/upload.php">View media library</a></li>
                <li><a href="/wp-admin/media-new.php">Add media file</a></li>
            </ul>
        </div>
        <div class="column-two">
            <h2>Posts</h2>
            <ul>
                <li>
                    <a href="/wp-admin/edit.php">View all posts</a>
                </li>
                <li>
                    <a href="/wp-admin/post-new.php">Add a new post</a>
                </li>
            </ul>
            <h2>Menu</h2>
            <ul>
                <li>
                    <a href="/wp-admin/nav-menus.php">Edit the main menu</a>
                </li>
            </ul>
        </div>
    </div>
    <?php
}

// Display the widget content
function display_ecommerce_tasks_widget_editor()
{
    ?>
    <div class="common-tasks-widget custom-admin-widget">
        <div class="column-one">
            <h2>Products</h2>
            <ul>
                <li><a href="/wp-admin/edit.php?post_type=product">View all products</a></li>
                <li><a href="/wp-admin/post-new.php?post_type=product">Add a new product</a></li>
                <li><a href="/wp-admin/edit-tags.php?taxonomy=product_cat&post_type=product">Product categories</a></li>
                <li><a href="/wp-admin/edit.php?post_type=product&page=product_attributes">Product attributes</a></li>
                <li><a href="/wp-admin/edit.php?post_type=product&page=product-reviews">Product reviews</a></li>
            </ul>
        </div>
        <div class="column-two">
            <h2>Orders</h2>
            <ul>
                <!-- <li><a href="/wp-admin/admin.php?page=order-pipeline">Order pipeline</a></li> -->
                <li><a href="/wp-admin/admin.php?page=wc-orders">View all orders</a></li>
            </ul>
        </div>
    </div>
    </div>
    <?php
}