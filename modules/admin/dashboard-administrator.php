<?php
/**
- Custom Dashboard Widget for Administrators Only (Plugins Tasks)
- Add this to your theme's functions.php or a custom plugin
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