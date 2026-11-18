<?php
/**
 * Module Name: Frontend Current Year and Date
 * Description: Provides shortcodes for displaying the current year and date.
 *
 * @package Skyrocket
 */

/**
 * Display the current year.
 *
 * Usage: [current_year]
 *
 * @return string The current year.
 */
function swu_current_year_shortcode(): string
{
    return esc_html(wp_date('Y'));
}
add_shortcode('current_year', 'swu_current_year_shortcode');

/**
 * Display the current date in the requested format.
 *
 * Available formats:
 * - year: 2026
 * - month-year: May 2026
 * - day-month-year: 12 May 2026 (default)
 *
 * Usage:
 * [current_date]
 * [current_date format="year"]
 * [current_date format="month-year"]
 * [current_date format="day-month-year"]
 *
 * @param array|string $atts Shortcode attributes.
 *
 * @return string The formatted current date.
 */
function swu_current_date_shortcode($atts = array()): string
{

    $atts = shortcode_atts(
        array(
            'format' => 'day-month-year',
        ),
        $atts,
        'current_date'
    );

    $formats = array(
        'year' => 'Y',
        'month-year' => 'F Y',
        'day-month-year' => 'j F Y',
    );

    $format = sanitize_key($atts['format']);

    if (!isset($formats[$format])) {
        $format = 'day-month-year';
    }

    return esc_html(wp_date($formats[$format]));
}
add_shortcode('current_date', 'swu_current_date_shortcode');
