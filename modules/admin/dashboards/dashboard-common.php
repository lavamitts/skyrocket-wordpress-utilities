<?php
/**
 * WordPress Admin Dashboard Styling and Widget Management
 *
 * Customises the WordPress administration dashboard by loading a
 * dedicated dashboard stylesheet, removing selected default and
 * plugin-provided dashboard widgets, and conditionally hiding a
 * specific dashboard widget from users who do not pass the
 * administrator capability check.
 *
 * Features:
 *
 * - Dashboard-specific stylesheet:
 *   Enqueues `assets/css/dashboard/dashboard.css` only on the main
 *   WordPress administration dashboard.
 *
 * - Cache-busting:
 *   Uses the stylesheet's file modification time as its version
 *   number so that browsers can retrieve updated CSS after changes.
 *
 * - Dashboard widget removal:
 *   Removes selected WordPress core and plugin dashboard widgets,
 *   including Activity, Quick Draft, WordPress Events and News,
 *   the WooCommerce dashboard widget and a theme-provided widget.
 *
 * - Conditional widget visibility:
 *   Outputs admin-side CSS to hide the theme-provided
 *   `wpexplorer_dashboard_widget` for users who do not pass the
 *   `administrator` capability check.
 *
 * Behaviour:
 *
 * - Loads the custom stylesheet only when the current admin screen
 *   is the main Dashboard.
 * - Removes the specified dashboard widgets for all users; the
 *   removal function does not currently contain a role or capability
 *   restriction.
 * - The default WordPress At a Glance widget removal is commented out
 *   and therefore inactive.
 * - Hides the specified theme widget through CSS rather than
 *   unregistering or removing it.
 *
 * Hooks:
 *
 * - `admin_enqueue_scripts`:
 *   Loads the custom dashboard stylesheet on the Dashboard screen.
 *
 * - `wp_dashboard_setup`:
 *   Removes the selected dashboard widgets.
 *
 * - `admin_head`:
 *   Outputs conditional CSS to hide the specified theme dashboard
 *   widget.
 *
 * Dependencies:
 *
 * - WordPress core dashboard, admin screen and asset enqueue APIs.
 * - `assets/css/dashboard/dashboard.css`, relative to this file's
 *   plugin directory.
 *
 * @package Skyrocket
 */

// Enqueue custom dashboard CSS
function enqueue_custom_dashboard_styles()
{
    $screen = get_current_screen();

    // Only load on the main dashboard page
    if ($screen && 'dashboard' === $screen->id) {
        $css_path = '../../../assets/css/dashboard/dashboard.css';
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