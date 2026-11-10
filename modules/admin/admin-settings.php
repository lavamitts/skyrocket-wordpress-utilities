<?php
/**
 * Skyrocket Utilities settings screen.
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
 * Sanitize plugin options.
 *
 * @param mixed $input Submitted settings.
 * @return array
 */
function swu_sanitize_options($input)
{
    $options = [];

    if (is_array($input)) {
        $options['login_logo_id'] = absint(
            $input['login_logo_id'] ?? 0
        );
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
        SWU_URL . 'assets/js/admin-settings.js',
        ['jquery'],
        filemtime(SWU_PATH . 'assets/js/admin-settings.js'),
        true
    );
}
add_action('admin_enqueue_scripts', 'swu_settings_enqueue_assets');
