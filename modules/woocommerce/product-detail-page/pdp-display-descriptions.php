<?php
/**
 * Display the full product description beneath the product title and price,
 * instead of the short description.
 *
 * Remove the full description from its normal position further down
 * the WooCommerce single product page.
 */

// Replace the short description with the long description.

add_action('woocommerce_single_product_summary', function () {
    global $product;

    if (!$product) {
        return;
    }

    // Remove the short description.
    remove_action(
        'woocommerce_single_product_summary',
        'woocommerce_template_single_excerpt',
        20
    );

    // Display the full description in its place.
    echo '<div class="woocommerce-product-details__long-description">';
    echo apply_filters('the_content', $product->get_description());
    echo '</div>';

}, 21);


// Remove the normal long description from the bottom of the product page.
add_filter('woocommerce_product_tabs', function ($tabs) {
    unset($tabs['description']);

    return $tabs;
}, 98);