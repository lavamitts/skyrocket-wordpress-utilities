<?php
/**
 * Bellevue Bake Co. — Store help email
 *
 * Adds a reusable help email address to:
 * WooCommerce > Settings > Emails
 */

/**
 * Add the field to WooCommerce email settings.
 */
add_filter('woocommerce_email_settings', function ($settings) {

    $field = array(
        'title' => 'Store help email',
        'desc' => 'Email address displayed in customer-facing email help text. Used by the {store_help_email} placeholder.',
        'id' => 'bbc_store_help_email',
        'type' => 'email',
        'css' => 'min-width: 300px;',
        'default' => '',
        'autoload' => false,
    );

    // Insert directly after the standard From address field.
    $insert_after = 'woocommerce_email_from_address';
    $result = array();
    $inserted = false;

    foreach ($settings as $setting) {
        $result[] = $setting;

        if (
            isset($setting['id'])
            && $setting['id'] === $insert_after
        ) {
            $result[] = $field;
            $inserted = true;
        }
    }

    if (!$inserted) {
        $result[] = $field;
    }

    return $result;
});


/**
 * Make {store_help_email} available in the standard email footer.
 */
add_filter('woocommerce_email_footer_text', function ($text) {

    $help_email = sanitize_email(
        get_option('bbc_store_help_email', '')
    );

    return str_replace(
        '{store_help_email}',
        esc_html($help_email),
        $text
    );
});


/**
 * Also support {store_help_email} in individual email headings,
 * subjects and additional content.
 */
add_filter('woocommerce_email_format_string', function ($string, $email) {

    $help_email = sanitize_email(
        get_option('bbc_store_help_email', '')
    );

    return str_replace(
        '{store_help_email}',
        $help_email,
        $string
    );
}, 10, 2);
