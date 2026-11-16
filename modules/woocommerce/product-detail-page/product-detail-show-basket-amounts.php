<?php
// Total quantity of a product in the cart (variations are summed under the parent product)
function mp_cart_qty_for_product($product_id)
{
    if (!WC()->cart)
        return 0;
    $qty = 0;
    foreach (WC()->cart->get_cart() as $item) {
        if ((int) $item['product_id'] === (int) $product_id) {
            $qty += (int) $item['quantity'];
        }
    }
    return $qty;
}

// Output above the quantity / add to basket form on the PDP
add_action('woocommerce_before_add_to_cart_form', function () {
    global $product;
    if (!$product)
        return;

    $qty = mp_cart_qty_for_product($product->get_id());
    printf(
        '<p class="mp-in-basket" data-product-id="%d"%s>In your basket: <strong class="mp-in-basket-qty">%d</strong></p>',
        $product->get_id(),
        $qty ? '' : ' hidden',
        $qty
    );
    // Holder that the fragment refresh keeps up to date
    echo mp_cart_counts_fragment_html();
});

function mp_cart_counts_fragment_html()
{
    $counts = array();
    if (WC()->cart) {
        foreach (WC()->cart->get_cart() as $item) {
            $id = $item['product_id'];
            $counts[$id] = ($counts[$id] ?? 0) + (int) $item['quantity'];
        }
    }
    return '<span class="mp-cart-counts" hidden data-counts="' . esc_attr(wp_json_encode((object) $counts)) . '"></span>';
}

// Refresh the counts whenever WooCommerce refreshes cart fragments
add_filter('woocommerce_add_to_cart_fragments', function ($fragments) {
    $fragments['span.mp-cart-counts'] = mp_cart_counts_fragment_html();
    return $fragments;
});

// JS to update the visible line
add_action('wp_footer', function () {
    if (!is_product())
        return;
    ?>
    <script>
        jQuery(function ($) {
            function update() {
                var $el = $('.mp-in-basket');
                if (!$el.length) return;
                var counts = {};
                try { counts = JSON.parse($('.mp-cart-counts').attr('data-counts') || '{}'); } catch (e) { }
                var qty = parseInt(counts[$el.data('product-id')] || 0, 10);
                $el.find('.mp-in-basket-qty').text(qty);
                $el.prop('hidden', qty < 1);
            }
            // setTimeout lets WooCommerce finish swapping fragments first
            $(document.body).on(
                'added_to_cart removed_from_cart updated_cart_totals wc_fragments_refreshed wc_fragments_loaded',
                function () { setTimeout(update, 0); }
            );
        });
    </script>
    <?php
}, 99);