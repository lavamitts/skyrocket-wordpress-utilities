<?php
/**
 * Add active category classes to the WooCommerce Product Categories block
 * when rendered inside a specified WordPress sidebar.
 *
 * Adds:
 * - current-cat
 * - current-cat-parent
 */

// Configure the target sidebar ID.
define('WC_CATEGORY_ACTIVE_SIDEBAR', 'sidebar-woocommerce');

/**
 * Track whether the target sidebar is currently rendering.
 */
function wc_category_active_sidebar_start($index)
{
    if ($index === WC_CATEGORY_ACTIVE_SIDEBAR) {
        $GLOBALS['wc_category_active_sidebar'] = true;
    }
}

function wc_category_active_sidebar_end($index)
{
    if ($index === WC_CATEGORY_ACTIVE_SIDEBAR) {
        $GLOBALS['wc_category_active_sidebar'] = false;
    }
}

add_action('dynamic_sidebar_before', 'wc_category_active_sidebar_start');
add_action('dynamic_sidebar_after', 'wc_category_active_sidebar_end');

/**
 * Add active classes to the relevant category list items.
 */
function wc_category_active_render_block($block_content, $block)
{

    if (
        empty($GLOBALS['wc_category_active_sidebar'])
        || ($block['blockName'] ?? '') !== 'woocommerce/product-categories'
        || !is_tax('product_cat')
        || empty($block_content)
    ) {
        return $block_content;
    }

    $current_term = get_queried_object();

    if (!$current_term instanceof WP_Term) {
        return $block_content;
    }

    // Current category and all its ancestors.
    $active_terms = [
        $current_term->term_id => 'current-cat',
    ];

    $ancestors = get_ancestors(
        $current_term->term_id,
        'product_cat',
        'taxonomy'
    );

    foreach ($ancestors as $ancestor_id) {
        $active_terms[$ancestor_id] = 'current-cat-parent';
    }

    // Build a URL-to-class lookup.
    $active_urls = [];

    foreach ($active_terms as $term_id => $class) {
        $url = get_term_link((int) $term_id, 'product_cat');

        if (!is_wp_error($url)) {
            $active_urls[untrailingslashit($url)] = $class;
        }
    }

    if (empty($active_urls)) {
        return $block_content;
    }

    // Parse the block HTML.
    $dom = new DOMDocument();
    $previous_errors = libxml_use_internal_errors(true);

    $dom->loadHTML(
        '<?xml encoding="utf-8" ?><div id="category-active-root">'
        . $block_content
        . '</div>'
    );

    libxml_clear_errors();
    libxml_use_internal_errors($previous_errors);

    $xpath = new DOMXPath($dom);

    $items = $xpath->query(
        '//*[@id="category-active-root"]//li[a]'
    );

    foreach ($items as $item) {

        $link = $xpath->query('./a', $item)->item(0);

        if (!$link) {
            continue;
        }

        $url = untrailingslashit($link->getAttribute('href'));

        if (!isset($active_urls[$url])) {
            continue;
        }

        $classes = preg_split(
            '/\s+/',
            trim($item->getAttribute('class'))
        );

        $classes[] = $active_urls[$url];

        $item->setAttribute(
            'class',
            implode(' ', array_unique($classes))
        );
    }

    // Return the modified HTML.
    $root = $dom->getElementById('category-active-root');

    if (!$root) {
        return $block_content;
    }

    $output = '';

    foreach ($root->childNodes as $child) {
        $output .= $dom->saveHTML($child);
    }

    return $output;
}

add_filter(
    'render_block',
    'wc_category_active_render_block',
    10,
    2
);
