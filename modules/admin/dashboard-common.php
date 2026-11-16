<?php
/**
 * Custom Dashboard Widget for Specific User Role
 * Add this to your theme's functions.php or a custom plugin
 */

// Enqueue custom dashboard CSS
function enqueue_custom_dashboard_styles()
{
    $screen = get_current_screen();

    // Only load on the main dashboard page
    if ($screen && 'dashboard' === $screen->id) {
        $css_path = '../../assets/css/dashboard/dashboard.css';
        // error_log(plugin_dir_url(__FILE__) . $css_path);

        wp_enqueue_style(
            'custom-dashboard-styles',
            plugin_dir_url(__FILE__) . $css_path,
            array(),
            filemtime(plugin_dir_path(__FILE__) . $css_path) // Cache busting
        );
    }
}

add_action('admin_enqueue_scripts', 'enqueue_custom_dashboard_styles');

// Optional: Remove default dashboard widgets for non admon role
function remove_dashboard_widgets_for_role()
{
    // remove_meta_box('dashboard_right_now', 'dashboard', 'normal');
    remove_meta_box('dashboard_activity', 'dashboard', 'normal');
    remove_meta_box('dashboard_quick_press', 'dashboard', 'side');
    remove_meta_box('dashboard_primary', 'dashboard', 'side');
    remove_meta_box('wpexplorer_dashboard_widget', 'dashboard', 'side');
    remove_meta_box('wc_admin_dashboard_setup', 'dashboard', 'normal');
}
add_action('wp_dashboard_setup', 'remove_dashboard_widgets_for_role', 2000);


// Remove the HSTS plugin meta box from the dashboard
add_action('admin_head', function () {
    if (!current_user_can('administrator')) {
        echo '<style>#wpexplorer_dashboard_widget {display:none;}</style>';
    }
});
?>