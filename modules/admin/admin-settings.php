<?php
/**
 * Skyrocket Utilities Settings
 *
 * Provides the central administration settings interface for the
 * Skyrocket Utilities plugin, including settings registration,
 * sanitisation, tab navigation, module loading and administration
 * asset management.
 *
 * The settings screen is registered under WordPress Settings and
 * uses a tab-based structure to organise configuration options into
 * separate modules. Individual settings sections are loaded from
 * dedicated module files, allowing the settings interface to be
 * extended without substantially changing the central controller.
 *
 * Features:
 *
 * - Settings page:
 *   Registers the Skyrocket Utilities settings screen under the
 *   WordPress Settings menu, restricted to users with the
 *   `manage_options` capability.
 *
 * - WordPress Settings API integration:
 *   Registers the plugin's `swu_options` option using the WordPress
 *   Settings API, with array-based storage and a dedicated
 *   sanitisation callback.
 *
 * - Centralised settings storage:
 *   Stores plugin configuration in a single `swu_options` option,
 *   allowing individual modules to maintain their settings within
 *   a shared options structure.
 *
 * - Settings sanitisation:
 *   Validates and sanitises submitted settings while preserving
 *   existing option values that are not included in the current
 *   submission.
 *
 * - Tab navigation:
 *   Provides a tab-based settings interface, with the active tab
 *   determined by the URL query parameter and validated against
 *   the registered tabs.
 *
 * - Modular settings architecture:
 *   Loads individual settings module files according to the active
 *   tab, keeping module-specific configuration and interface code
 *   separate from the central settings controller.
 *
 * - Administration assets:
 *   Loads the WordPress Media Library, shared settings JavaScript,
 *   jQuery UI Sortable and the dedicated admin menu ordering script
 *   on the plugin settings screen only.
 *
 * Current settings:
 *
 * - `login_logo_id`:
 *   Stores the Media Library attachment ID used for the customised
 *   WordPress login logo.
 *
 * - `admin_menu_order`:
 *   Stores the preferred ordering of WordPress administration menu
 *   items as an array of menu slugs.
 *
 * Hooks:
 *
 * - `admin_menu`:
 *   Registers the Skyrocket Utilities settings page.
 *
 * - `admin_init`:
 *   Registers the plugin settings with the WordPress Settings API.
 *
 * - `admin_enqueue_scripts`:
 *   Loads the scripts and supporting WordPress functionality required
 *   by the settings interface.
 *
 * Dependencies:
 *
 * - WordPress core Settings API, administration menu and media APIs.
 * - WordPress jQuery and jQuery UI Sortable.
 * - `SWU_PATH`, `SWU_URL` and `SWU_VERSION` plugin constants.
 * - `modules/admin/settings/admin-tweaks.php` for the current
 *   Admin tweaks settings tab.
 * - `assets/js/admin/admin-settings.js`.
 * - `assets/js/admin/admin-menu-order.js`.
 *
 * @package Skyrocket
 */

defined('ABSPATH') || exit;

/**
 * Register the settings page under Settings.
 */
function swu_register_settings_page()
{
    add_options_page(
        'Skyrocket Utilities',
        'Skyrocket Utilities',
        'manage_options',
        'swu-settings',
        'swu_render_settings_page'
    );
}
add_action('admin_menu', 'swu_register_settings_page');


/**
 * Register plugin settings.
 */
function swu_register_settings()
{
    register_setting(
        'swu_settings_group',
        'swu_options',
        [
            'type' => 'array',
            'sanitize_callback' => 'swu_sanitize_options',
            'default' => [],
        ]
    );
}
add_action('admin_init', 'swu_register_settings');


/**
 * Sanitize plugin settings.
 */

function swu_sanitize_options($input)
{
    $input = is_array($input) ? wp_unslash($input) : [];

    $options = get_option('swu_options', []);

    if (!is_array($options)) {
        $options = [];
    }

    // Login logo.
    if (array_key_exists('login_logo_id', $input)) {
        $options['login_logo_id'] = absint($input['login_logo_id']);
    }

    // Admin menu order.
    if (isset($input['admin_menu_order']) && is_array($input['admin_menu_order'])) {
        $menu_order = [];

        foreach ($input['admin_menu_order'] as $slug) {
            $slug = sanitize_text_field($slug);

            if ($slug !== '' && !in_array($slug, $menu_order, true)) {
                $menu_order[] = $slug;
            }
        }

        $options['admin_menu_order'] = $menu_order;
    }

    return $options;
}

/**
 * Render the plugin settings page.
 */
function swu_render_settings_page()
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $tabs = [
        'admin-tweaks' => 'Admin tweaks',
    ];

    $active_tab = isset($_GET['tab'])
        ? sanitize_key(wp_unslash($_GET['tab']))
        : 'admin-tweaks';

    if (!isset($tabs[$active_tab])) {
        $active_tab = 'admin-tweaks';
    }

    ?>
    <div class="wrap swu-settings">

        <h1>Skyrocket Utilities</h1>

        <nav class="nav-tab-wrapper">
            <?php foreach ($tabs as $slug => $label): ?>

                <a href="<?php echo esc_url(
                    admin_url('options-general.php?page=swu-settings&tab=' . $slug)
                ); ?>" class="nav-tab <?php echo $active_tab === $slug ? 'nav-tab-active' : ''; ?>">
                    <?php echo esc_html($label); ?>
                </a>

            <?php endforeach; ?>
        </nav>

        <form method="post" action="options.php">

            <?php settings_fields('swu_settings_group'); ?>

            <?php
            if ($active_tab === 'admin-tweaks') {
                require SWU_PATH . 'modules/admin/settings/admin-tweaks.php';
            }
            ?>

            <?php submit_button(); ?>

        </form>

    </div>
    <?php
}


/**
 * Load settings page JavaScript and WordPress media library.
 *
 * @param string $hook Current admin page hook.
 */
function swu_settings_enqueue_assets($hook)
{
    if ($hook !== 'settings_page_swu-settings') {
        return;
    }

    wp_enqueue_media();

    wp_enqueue_script(
        'swu-admin-settings',
        SWU_URL . 'assets/js/admin/admin-settings.js',
        ['jquery'],
        filemtime(SWU_PATH . 'assets/js/admin/admin-settings.js'),
        true
    );

    // Load WordPress's bundled sortable functionality.
    wp_enqueue_script('jquery-ui-sortable');

    // Load the menu ordering script.
    $menu_order_js = SWU_PATH . 'assets/js/admin/admin-menu-order.js';

    wp_enqueue_script(
        'swu-admin-menu-order',
        SWU_URL . 'assets/js/admin/admin-menu-order.js',
        ['jquery', 'jquery-ui-sortable'],
        file_exists($menu_order_js) ? filemtime($menu_order_js) : SWU_VERSION,
        true
    );
}
add_action('admin_enqueue_scripts', 'swu_settings_enqueue_assets');
