<?php

defined('ABSPATH') || exit;

/**
 * Enqueue administration CSS.
 */
function swu_enqueue_admin_styles()
{
    error_log("swu_enqueue_admin_styles");
    $file = SWU_PATH . 'assets/css/admin/admin.css';

    if (!file_exists($file)) {
        return;
    }

    wp_enqueue_style(
        'swu-admin',
        SWU_URL . 'assets/css/admin/admin.css',
        [],
        filemtime($file)
    );
}
add_action('admin_enqueue_scripts', 'swu_enqueue_admin_styles');


/**
 * Enqueue frontend CSS.
 */
function swu_enqueue_frontend_styles()
{
    $file = SWU_PATH . 'assets/css/frontend/frontend.css';

    if (!file_exists($file)) {
        return;
    }

    wp_enqueue_style(
        'swu-frontend',
        SWU_URL . 'assets/css/frontend/frontend.css',
        [],
        filemtime($file)
    );
}
add_action('wp_enqueue_scripts', 'swu_enqueue_frontend_styles');


/**
 * Enqueue WooCommerce CSS only on relevant pages.
 */
function swu_enqueue_woocommerce_styles()
{
    if (
        !function_exists('is_woocommerce') ||
        !(
            is_woocommerce() ||
            is_cart() ||
            is_checkout() ||
            is_account_page()
        )
    ) {
        return;
    }

    $file = SWU_PATH . 'assets/css/woocommerce/woocommerce.css';

    if (!file_exists($file)) {
        return;
    }

    wp_enqueue_style(
        'swu-woocommerce',
        SWU_URL . 'assets/css/woocommerce/woocommerce.css',
        [],
        filemtime($file)
    );
}
add_action('wp_enqueue_scripts', 'swu_enqueue_woocommerce_styles');