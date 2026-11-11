<?php
function h1($text): void
{
    echo ("<h1>{$text}</h1>");
}
function h2($text): void
{
    echo ("<h2>{$text}</h2>");
}
function pre($text): void
{
    echo ("<pre>{$text}</pre>");
}


/**
 * Get the HTML content of a synced WordPress pattern by its exact title.
 *
 * @param string $pattern_title The pattern's exact title.
 * @return string The rendered HTML, or an empty string if not found.
 */
function get_wp_pattern_html($pattern_title)
{
    $patterns = get_posts([
        'post_type' => 'wp_block',
        'post_status' => 'publish',
        'title' => $pattern_title,
        'posts_per_page' => 1,
    ]);

    if (empty($patterns)) {
        return '';
    }

    return do_blocks($patterns[0]->post_content);
}

/**
 * Get the HTML content of a WordPress synced pattern by slug.
 *
 * @param string $pattern_slug The pattern's slug.
 * @return string The pattern HTML, or an empty string if not found.
 */
function get_wp_pattern_html_by_slug($pattern_slug)
{
    $pattern = get_page_by_path(
        sanitize_title($pattern_slug),
        OBJECT,
        'wp_block'
    );

    if (!$pattern) {
        return '';
    }

    return do_blocks($pattern->post_content);
}
function debug_form_data(): void
{
    if (empty($_POST)) {
        echo '<div class="post-debug-empty">No POST data.</div>';

    } else {

        $sensitive_keys = [
            'password',
            'password_confirmation',
            'pass',
            'token',
            'csrf_token',
        ];

        echo '<div class="post-debug">';

        foreach ($_POST as $key => $value) {
            $safe_key = htmlspecialchars((string) $key, ENT_QUOTES, 'UTF-8');

            if (in_array(strtolower((string) $key), $sensitive_keys, true)) {
                $safe_value = '••••••••';
            } elseif (is_array($value)) {
                $safe_value = htmlspecialchars(
                    print_r($value, true),
                    ENT_QUOTES,
                    'UTF-8'
                );
            } else {
                $safe_value = htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
            }

            echo '<div class="post-debug-row">';
            echo '<div class="post-debug-key">' . $safe_key . '</div>';
            echo '<div class="post-debug-value">' . nl2br($safe_value) . '</div>';
            echo '</div>';
        }

        echo '</div>';
    }
    if (empty($_GET)) {
        echo '<div class="post-debug-empty">No GET data.</div>';

    } else {

        $sensitive_keys = [
            'password',
            'password_confirmation',
            'pass',
            'token',
            'csrf_token',
        ];

        echo '<div class="post-debug">';

        foreach ($_GET as $key => $value) {
            $safe_key = htmlspecialchars((string) $key, ENT_QUOTES, 'UTF-8');

            if (in_array(strtolower((string) $key), $sensitive_keys, true)) {
                $safe_value = '••••••••';
            } elseif (is_array($value)) {
                $safe_value = htmlspecialchars(
                    print_r($value, true),
                    ENT_QUOTES,
                    'UTF-8'
                );
            } else {
                $safe_value = htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
            }

            echo '<div class="post-debug-row">';
            echo '<div class="post-debug-key">' . $safe_key . '</div>';
            echo '<div class="post-debug-value">' . nl2br($safe_value) . '</div>';
            echo '</div>';
        }

        echo '</div>';
    }
}

add_action('init', function () {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return;
    }

    error_log('POST received: ' . wp_json_encode($_POST));
}, 1);

/**
 * Check whether Slim SEO is active.
 */
function swu_is_slim_seo_active()
{
    return in_array(
        'slim-seo/slim-seo.php',
        (array) get_option('active_plugins', array()),
        true
    );
}