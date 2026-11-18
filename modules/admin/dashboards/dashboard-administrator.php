<?php
/**
 * Administrator Dashboard Tasks Widget
 *
 * Adds a custom widget to the WordPress administration dashboard,
 * providing convenient shortcuts to commonly used plugin management,
 * theme customisation and Skyrocket Utilities screens.
 *
 * The widget is registered in the dashboard's sidebar area with a
 * high display priority and is intended for administrator access.
 *
 * Features:
 *
 * - Administrator dashboard widget:
 *   Registers an "Administrator tasks" widget on the WordPress
 *   administration dashboard.
 *
 * - Plugin management shortcuts:
 *   Provides direct links to the installed plugins list and the
 *   plugin installation screen.
 *
 * - Customisation shortcuts:
 *   Provides links to the Widgets screen, the WordPress Theme
 *   Customiser and the Skyrocket Utilities settings page.
 *
 * - Dashboard placement:
 *   Displays the widget in the dashboard sidebar, with high priority
 *   so that it appears near the top of the widget area.
 *
 * Behaviour:
 *
 * - Registers the widget during the `wp_dashboard_setup` action.
 * - Checks the `administrator` capability before registering the widget.
 * - Displays a two-column layout grouping plugin and customisation tasks.
 * - Does not provide any additional styling; presentation depends on
 *   the accompanying admin CSS, if present.
 *
 * Hooks:
 *
 * - `wp_dashboard_setup`:
 *   Registers the custom dashboard widget.
 *
 * Dependencies:
 *
 * - WordPress core dashboard widget API.
 * - The Skyrocket Utilities settings page for the corresponding shortcut.
 *
 * @package Skyrocket
 */

// Add the custom dashboard widget
function add_administrator_dashboard_widget()
{
    // Check if user has the administrator role only
    if (current_user_can('administrator')) {

        wp_add_dashboard_widget(
            'plugins_tasks_widget',                 // Widget slug
            'Administrator tasks',                    // Title
            'display_plugins_tasks_widget_content', // Display function
            null,                                   // Config function (optional)
            null,                                   // Callback args (optional)
            'side',                               // Context (normal, side, column3, column4)
            'high'                                  // Priority (high = top)
        );
    }
}
add_action('wp_dashboard_setup', 'add_administrator_dashboard_widget');

// Display the widget content
function display_plugins_tasks_widget_content()
{
    ?>
    <div class="common-tasks-widget custom-admin-widget">
        <div class="column-one">
            <h2>Plugins</h2>
            <ul>
                <li><a href="/wp-admin/plugins.php">Installed plugins</a></li>
                <li><a href="/wp-admin/plugin-install.php">Add a new plugin</a></li>
                <!-- <li><a href="/wp-admin/plugin-editor.php">Plugin file editor</a></li> -->
            </ul>
        </div>
        <div class="column-two">
            <h2>Customise</h2>
            <ul>
                <li><a href="/wp-admin/widgets.php">Widgets</a></li>
                <li><a href="/wp-admin/customize.php?return=%2Fwp-admin%2Findex.php">Customise theme</a></li>
                <li><a href="/wp-admin/options-general.php?page=swu-settings">Skyrocket utilities</a></li>
                <!-- <li><a href="/wp-admin/plugin-editor.php">Plugin file editor</a></li> -->
            </ul>
        </div>
    </div>
    <?php
}