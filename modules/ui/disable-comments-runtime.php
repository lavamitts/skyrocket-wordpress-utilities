<?php
/**
 * Disable Comments Runtime Behaviour
 *
 * Hooks into WordPress when `disable_comments_enabled` is active in
 * `swu_options`.
 *
 * @package Skyrocket
 */

defined('ABSPATH') || exit;

/**
 * Check whether global comment disabling is enabled.
 *
 * @return bool
 */
function swu_is_comments_disabled()
{
    $options = get_option('swu_options', []);

    return is_array($options) && !empty($options['disable_comments_enabled']);
}

add_action('init', function () {
    if (!swu_is_comments_disabled()) {
        return;
    }

    // Remove comment & trackback support from all currently registered post types
    // (including custom post types and WooCommerce products/reviews if desired—
    // adjust post types here if you want to keep product reviews).
    foreach (get_post_types([], 'names') as $post_type) {
        if (post_type_supports($post_type, 'comments')) {
            remove_post_type_support($post_type, 'comments');
        }
        if (post_type_supports($post_type, 'trackbacks')) {
            remove_post_type_support($post_type, 'trackbacks');
        }
    }

    // Remove comments icon from the admin bar.
    if (is_admin_bar_showing()) {
        remove_action('admin_bar_menu', 'wp_admin_bar_comments_menu', 60);
    }
}, 999);

add_action('wp_loaded', function () {
    if (!swu_is_comments_disabled()) {
        return;
    }

    // Catch any post types registered late in `init`.
    foreach (get_post_types([], 'names') as $post_type) {
        if (post_type_supports($post_type, 'comments')) {
            remove_post_type_support($post_type, 'comments');
        }
        if (post_type_supports($post_type, 'trackbacks')) {
            remove_post_type_support($post_type, 'trackbacks');
        }
    }
}, 999);

// Close comments and pings on the front end.
add_filter('comments_open', function ($open, $post_id) {
    return swu_is_comments_disabled() ? false : $open;
}, 20, 2);

add_filter('pings_open', function ($open, $post_id) {
    return swu_is_comments_disabled() ? false : $open;
}, 20, 2);

// Hide existing comments on the front end.
add_filter('comments_array', function ($comments, $post_id) {
    return swu_is_comments_disabled() ? [] : $comments;
}, 10, 2);

// Prevent WP_Query from pre-fetching comment counts / threads where possible.
// add_filter('comments_pre_query', function ($comments, $query) {
//     return swu_is_comments_disabled() ? [] : $comments;
// }, 10, 2);

// Prevent ordinary comment queries when comments are disabled,
// but preserve WooCommerce product review queries.
add_filter('comments_pre_query', function ($comments, $query) {
    if (!swu_is_comments_disabled()) {
        return $comments;
    }

    // Never interfere with WooCommerce product review queries.
    if (
        isset($query->query_vars['post_type']) &&
        'product' === $query->query_vars['post_type']
    ) {
        return $comments;
    }

    return [];
}, 10, 2);

// Block direct comment submission attempts (wp-comments-post.php).
add_action('pre_comment_on_post', function ($comment_post_ID) {
    if (swu_is_comments_disabled()) {
        wp_die(
            esc_html__('Comments are disabled on this site.', 'skyrocket'),
            esc_html__('Comments Disabled', 'skyrocket'),
            ['response' => 403]
        );
    }
}, 1);

// Disable XML-RPC pingback methods.
add_filter('xmlrpc_methods', function ($methods) {
    if (!swu_is_comments_disabled()) {
        return $methods;
    }

    unset($methods['pingback.ping'], $methods['pingback.extensions.getPingbacks']);

    return $methods;
});

// Disable comment feeds and return 404/empty response if requested directly.
add_filter('feed_links_show_comments_feed', function ($show) {
    return swu_is_comments_disabled() ? false : $show;
});

add_action('do_feed_rss2', function ($for_comments) {
    if ($for_comments && swu_is_comments_disabled()) {
        wp_die(
            esc_html__('Comment feeds are disabled.', 'skyrocket'),
            '',
            ['response' => 404]
        );
    }
}, 1);

add_action('do_feed_atom', function ($for_comments) {
    if ($for_comments && swu_is_comments_disabled()) {
        wp_die(
            esc_html__('Comment feeds are disabled.', 'skyrocket'),
            '',
            ['response' => 404]
        );
    }
}, 1);

// Disable REST API comment endpoints.
add_filter('rest_endpoints', function ($endpoints) {
    if (!swu_is_comments_disabled()) {
        return $endpoints;
    }

    foreach (array_keys($endpoints) as $route) {
        if (preg_match('#^/wp/v2/comments(?:/.*)?$#', $route)) {
            unset($endpoints[$route]);
        }
    }

    return $endpoints;
});

// Unregister Recent Comments widget.
add_action('widgets_init', function () {
    if (swu_is_comments_disabled()) {
        unregister_widget('WP_Widget_Recent_Comments');
    }
});

// Admin cleanup, menu removal, and page blocking.
add_action('admin_init', function () {
    if (!swu_is_comments_disabled()) {
        return;
    }

    global $pagenow;

    $blocked_pages = [
        'edit-comments.php',
        'comment.php',
        'options-discussion.php',
    ];

    if (in_array($pagenow, $blocked_pages, true)) {
        wp_safe_redirect(admin_url());
        exit;
    }

    // Remove Recent Comments dashboard widget (both normal and side contexts).
    remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal');
    remove_meta_box('dashboard_recent_comments', 'dashboard', 'side');

    // Remove Discussion meta boxes from standard post types.
    foreach (['post', 'page'] as $post_type) {
        remove_meta_box('commentstatusdiv', $post_type, 'normal');
        remove_meta_box('commentsdiv', $post_type, 'normal');
        remove_meta_box('trackbacksdiv', $post_type, 'normal');
    }
});

add_action('admin_menu', function () {
    if (!swu_is_comments_disabled()) {
        return;
    }

    remove_menu_page('edit-comments.php');
    remove_submenu_page('options-general.php', 'options-discussion.php');
}, 999);

add_action('admin_enqueue_scripts', function ($hook) {
    if (!swu_is_comments_disabled()) {
        return;
    }

    // Hide inline/bulk comment row actions on post/page screens if a theme/plugin adds them.
    if (in_array($hook, ['edit.php', 'post.php', 'post-new.php'], true)) {
        wp_add_inline_style(
            'wp-admin',
            '.column-comments, .column-response, .comment-count, .row-actions .reply { display: none !important; }'
        );
    }
});