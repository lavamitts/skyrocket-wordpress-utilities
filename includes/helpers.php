<?php

/**
 * Output a level-one heading.
 *
 * @param string $text The heading text to display.
 * @return void
 */
function swu_h1($text): void
{
    // Output the text inside an H1 element.
    echo ("<h1>{$text}</h1>");
}

/**
 * Output a level-two heading.
 *
 * @param string $text The heading text to display.
 * @return void
 */
function swu_h2($text): void
{
    // Output the text inside an H2 element.
    echo ("<h2>{$text}</h2>");
}

/**
 * Output text inside a preformatted HTML element.
 *
 * @param string $text The text to display.
 * @return void
 */
function swu_pre($text): void
{
    // Preserve whitespace and line breaks using a PRE element.
    echo ("<pre>{$text}</pre>");
}

/**
 * Output an array in a readable, preformatted layout.
 *
 * Useful for inspecting array contents during development.
 *
 * @param array $array The array to display.
 * @return void
 */
function swu_print_array($array): void
{
    echo '<pre>';

    // Print the array in a human-readable format.
    print_r($array);

    echo '</pre>';
}

/**
 * Get the HTML content of a synced WordPress pattern by its exact title.
 *
 * Retrieves a published synced pattern (wp_block), then processes its
 * block markup to return the rendered HTML.
 *
 * @param string $pattern_title The pattern's exact title.
 * @return string The rendered HTML, or an empty string if not found.
 */
function get_wp_pattern_html($pattern_title)
{
    // Find the first published synced pattern matching the supplied title.
    $patterns = get_posts([
        'post_type' => 'wp_block',
        'post_status' => 'publish',
        'title' => $pattern_title,
        'posts_per_page' => 1,
    ]);

    // Return an empty string if no matching pattern exists.
    if (empty($patterns)) {
        return '';
    }

    // Parse and render the pattern's block content.
    return do_blocks($patterns[0]->post_content);
}

/**
 * Get the HTML content of a WordPress synced pattern by slug.
 *
 * Retrieves a synced pattern using its slug, then processes its block
 * markup to return the rendered HTML.
 *
 * @param string $pattern_slug The pattern's slug.
 * @return string The pattern HTML, or an empty string if not found.
 */
function get_wp_pattern_html_by_slug($pattern_slug)
{
    // Retrieve the synced pattern using its sanitised slug.
    $pattern = get_page_by_path(
        sanitize_title($pattern_slug),
        OBJECT,
        'wp_block'
    );

    // Return an empty string if the pattern cannot be found.
    if (!$pattern) {
        return '';
    }

    // Parse and render the pattern's block content.
    return do_blocks($pattern->post_content);
}

/**
 * Display submitted POST and GET data for debugging.
 *
 * Outputs request data in a readable HTML layout.
 * Sensitive values are masked, and all displayed values are escaped
 * to prevent submitted HTML or scripts from being executed.
 *
 * @return void
 */
function swu_debug_form_data(): void
{
    // Check whether the request contains any POST data.
    if (empty($_POST)) {
        echo '<div class="post-debug-empty">No POST data.</div>';

    } else {

        // Keys whose submitted values should never be displayed.
        $sensitive_keys = [
            'password',
            'password_confirmation',
            'pass',
            'token',
            'csrf_token',
        ];

        echo '<div class="post-debug">';

        // Loop through each submitted POST field.
        foreach ($_POST as $key => $value) {

            // Escape the field name before outputting it as HTML.
            $safe_key = htmlspecialchars((string) $key, ENT_QUOTES, 'UTF-8');

            // Mask sensitive values to avoid exposing credentials or tokens.
            if (in_array(strtolower((string) $key), $sensitive_keys, true)) {
                $safe_value = '••••••••';

                // Convert array values into readable text before escaping.
            } elseif (is_array($value)) {
                $safe_value = htmlspecialchars(
                    print_r($value, true),
                    ENT_QUOTES,
                    'UTF-8'
                );

                // Escape ordinary submitted values before displaying them.
            } else {
                $safe_value = htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
            }

            // Output the field name and its corresponding value.
            echo '<div class="post-debug-row">';
            echo '<div class="post-debug-key">' . $safe_key . '</div>';
            echo '<div class="post-debug-value">' . nl2br($safe_value) . '</div>';
            echo '</div>';
        }

        echo '</div>';
    }

    // Check whether the request contains any GET data.
    if (empty($_GET)) {
        echo '<div class="post-debug-empty">No GET data.</div>';

    } else {

        // Keys whose submitted values should never be displayed.
        $sensitive_keys = [
            'password',
            'password_confirmation',
            'pass',
            'token',
            'csrf_token',
        ];

        echo '<div class="post-debug">';

        // Loop through each submitted GET parameter.
        foreach ($_GET as $key => $value) {

            // Escape the parameter name before outputting it as HTML.
            $safe_key = htmlspecialchars((string) $key, ENT_QUOTES, 'UTF-8');

            // Mask sensitive values to avoid exposing credentials or tokens.
            if (in_array(strtolower((string) $key), $sensitive_keys, true)) {
                $safe_value = '••••••••';

                // Convert array values into readable text before escaping.
            } elseif (is_array($value)) {
                $safe_value = htmlspecialchars(
                    print_r($value, true),
                    ENT_QUOTES,
                    'UTF-8'
                );

                // Escape ordinary parameter values before displaying them.
            } else {
                $safe_value = htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
            }

            // Output the parameter name and its corresponding value.
            echo '<div class="post-debug-row">';
            echo '<div class="post-debug-key">' . $safe_key . '</div>';
            echo '<div class="post-debug-value">' . nl2br($safe_value) . '</div>';
            echo '</div>';
        }

        echo '</div>';
    }
}

/**
 * Log incoming POST request data to the WordPress debug log.
 *
 * Runs early on the init hook and only logs requests using POST.
 */
add_action('init', function () {

    // Ignore requests that are not POST submissions.
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return;
    }

    // Encode the submitted POST data as JSON and write it to the PHP error log.
    error_log('POST received: ' . wp_json_encode($_POST));

}, 1);

/**
 * Check whether the Slim SEO plugin is active.
 *
 * Checks the list of active plugins stored in WordPress options.
 *
 * @return bool True if Slim SEO is active, otherwise false.
 */
function swu_is_slim_seo_active()
{
    return in_array(
        'slim-seo/slim-seo.php',
        (array) get_option('active_plugins', array()),
        true
    );
}

/**
 * Format a date as a friendly, human-readable string.
 *
 * Expects a date in YYYY-MM-DD format and converts it to a format
 * such as 23 Oct 2026.
 *
 * @param mixed $date The date to format.
 * @return string Formatted date, or an empty string if invalid.
 */
function swu_format_friendly_date(mixed $date): string
{
    // Only accept non-empty string values.
    if (!is_string($date) || trim($date) === '') {
        return '';
    }

    // Parse the date, resetting the time portion to midnight.
    $date_object = DateTimeImmutable::createFromFormat('!Y-m-d', trim($date));

    // Reject invalid dates and values that do not match the expected format.
    if (!$date_object || $date_object->format('Y-m-d') !== trim($date)) {
        return '';
    }

    // Return the date using a day, abbreviated month and four-digit year.
    return $date_object->format('j M Y');
}
