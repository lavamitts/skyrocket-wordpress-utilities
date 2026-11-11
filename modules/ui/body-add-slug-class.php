<?php
/**
 * Add the page or post slug as a class to the body tag.
 */
add_filter('body_class', function ($classes) {

    if (is_singular()) {
        global $post;

        if ($post && !empty($post->post_name)) {
            $classes[] = sanitize_html_class($post->post_name);
        }
    }

    return $classes;
});