<?php

/**
 * Bellevue Bake Co. — Show each product's basket quantity
 * on the WooCommerce shop and product archive pages.
 */

/**
 * Get the quantity of a product currently in the basket.
 */
function bbc_get_product_cart_quantity($product_id)
{
    if (!function_exists('WC') || !WC()->cart) {
        return 0;
    }

    $quantity = 0;

    foreach (WC()->cart->get_cart() as $cart_item) {
        if ((int) $cart_item['product_id'] === (int) $product_id) {
            $quantity += (int) $cart_item['quantity'];
        }
    }

    return $quantity;
}

/**
 * Add a quantity indicator after each product's add-to-cart button.
 */
add_filter(
    'woocommerce_loop_add_to_cart_link',
    'bbc_shop_product_cart_quantity',
    20,
    3
);

function bbc_shop_product_cart_quantity($html, $product, $args = array())
{
    if (!$product instanceof WC_Product) {
        return $html;
    }

    $quantity = bbc_get_product_cart_quantity($product->get_id());
    $quantity = number_format($quantity);

    $html .= sprintf(
        '<span title="View basket" class="bbc-product-cart-quantity" data-product-id="%1$d"%2$s>(%3$s)</span>',
        $product->get_id(),
        $quantity > 0 ? '' : ' hidden',
        $quantity
    );

    return $html;
}

/**
 * Update the quantity immediately after an AJAX add to cart.
 */
add_action('wp_footer', 'bbc_shop_cart_quantity_script', 99);

function bbc_shop_cart_quantity_script()
{
    // if (!is_shop() && !is_product_taxonomy()) {
    //     return;
    // }

    if (!is_shop() && !is_product_taxonomy() && !is_front_page()) {
        return;
    }
    ?>
    <script>
        jQuery(function ($) {
            $(document.body).on(
                'added_to_cart',
                function (event, fragments, cartHash, $button) {
                    if (!$button || !$button.length) {
                        return;
                    }

                    var productId = parseInt(
                        $button.attr('data-product_id'), 10
                    );

                    var addedQuantity = parseInt(
                        $button.attr('data-quantity') || 1, 10
                    );

                    if (!productId) {
                        return;
                    }

                    $('.bbc-product-cart-quantity').each(function () {
                        var $indicator = $(this);

                        if (
                            parseInt($indicator.attr('data-product-id'), 10)
                            !== productId
                        ) {
                            return;
                        }

                        var currentQuantity = parseInt(
                            $indicator.text().replace(/\D/g, ''), 10
                        ) || 0;

                        var newQuantity = currentQuantity + addedQuantity;

                        $indicator
                            .text('(' + newQuantity.toLocaleString('en-GB') + ')')
                            .prop('hidden', false);
                    });
                }
            );
        });
    </script>
    <?php
}

/**
 * Keep AJAX add-to-cart buttons in their original state after adding a product.
 *
 * Prevents WooCommerce from displaying the "View basket" link beside an
 * add-to-cart button after a successful AJAX add-to-cart operation.
 *
 * Skips the cart and checkout pages, where this behaviour is unnecessary.
 * Listens for WooCommerce's added_to_cart event, removes the button's
 * "added" class, and removes the adjacent "View basket" link.
 *
 * Runs at priority 99 to allow WooCommerce and other scripts to initialise
 * their normal add-to-cart behaviour first.
 */
add_action('wp_footer', function () {
    if (is_cart() || is_checkout())
        return;
    ?>
    <script>
        jQuery(function ($) {
            $(document.body).on('added_to_cart', function (e, fragments, hash, $button) {
                if (!$button) return;
                $button.removeClass('added');
                $button.siblings('.added_to_cart').remove();
            });
        });
    </script>
    <?php
}, 99);



/**
 * Make the product cart quantity clickable to open the basket.
 */
add_action('wp_footer', function () {
    ?>
    <script>
        document.addEventListener('click', function (event) {
            const quantity = event.target.closest('.bbc-product-cart-quantity');

            if (!quantity) {
                return;
            }

            window.location.href = '<?php echo esc_url(wc_get_cart_url()); ?>';
        });
    </script>
    <?php
});
