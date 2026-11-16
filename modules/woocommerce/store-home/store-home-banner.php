<?php
/**
 * Add a pattern at the top of the shop page
 */
add_action('woocommerce_before_shop_loop', 'bbc_shop_intro_pattern', 5);

function bbc_shop_intro_pattern()
{
    if (is_shop() && !is_search()) {
        echo get_wp_pattern_html('Shop page content');
    }
}