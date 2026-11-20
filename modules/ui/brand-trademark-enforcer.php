<?php
/**
 * Brand Trademark Enforcer
 *
 * Processes the complete frontend HTML output and automatically appends
 * registered trademark symbols to specified brand terms.
 *
 * Features:
 * - Processes the complete frontend HTML, not just post content.
 * - Normalises existing registered trademark symbols into superscripts.
 * - Adds missing registered trademark symbols to specified brand terms.
 * - Prevents duplicate trademark symbols.
 * - Removes consecutive duplicate trademark superscripts.
 * - Supports case-insensitive brand matching.
 * - Excludes elements carrying the do-not-enforce-brand-trademark class.
 * - Preserves original HTML markup.
 *
 * @package Skyrocket
 */

defined('ABSPATH') || exit;


/**
 * Brand terms requiring registered trademark symbols.
 *
 * @return array
 */
function swu_brand_trademark_terms()
{
    return [
        'Sudometrics',
        'Sudopad',
        'Neuropad',
        'Feet4Life',
    ];
}


/**
 * Check whether an HTML fragment is a tag.
 *
 * @param string $part HTML fragment.
 * @return array|false Tag information or false.
 */
function swu_brand_trademark_get_tag_info($part)
{
    if (
        !preg_match(
            '/^<\s*(\/?)\s*([a-zA-Z][a-zA-Z0-9:-]*)\b/',
            $part,
            $match
        )
    ) {
        return false;
    }

    return [
        'closing' => $match[1] === '/',
        'tag' => strtolower($match[2]),
    ];
}


/**
 * Check whether an opening HTML tag carries the exclusion class.
 *
 * The class can appear alongside any number of other CSS classes.
 *
 * @param string $tag HTML opening tag.
 * @return bool
 */
function swu_brand_trademark_is_excluded($tag)
{
    if (
        !preg_match(
            '/\bclass\s*=\s*(?:"([^"]*)"|\'([^\']*)\'|([^\s>]+))/i',
            $tag,
            $match
        )
    ) {
        return false;
    }

    $class_string = $match[1] ?? '';

    if ($class_string === '' && isset($match[2])) {
        $class_string = $match[2];
    }

    if ($class_string === '' && isset($match[3])) {
        $class_string = $match[3];
    }

    $classes = preg_split('/\s+/', trim($class_string));

    return in_array(
        'do-not-enforce-brand-trademark',
        $classes,
        true
    );
}


/**
 * Update the HTML element stack.
 *
 * Tracks nested elements and their exclusion status.
 *
 * @param string $part HTML fragment.
 * @param array  $stack Current element stack.
 * @return array Updated element stack.
 */
function swu_brand_trademark_update_stack($part, &$stack)
{
    $info = swu_brand_trademark_get_tag_info($part);

    if (!$info) {
        return false;
    }

    $tag = $info['tag'];

    // Handle closing tags.
    if ($info['closing']) {

        // Find the corresponding opening element.
        for ($i = count($stack) - 1; $i >= 0; $i--) {

            if ($stack[$i]['tag'] === $tag) {

                // Remove this element and any unclosed descendants.
                $stack = array_slice($stack, 0, $i);

                break;
            }
        }

        return true;
    }

    // HTML elements that cannot contain child elements.
    $void_tags = [
        'area',
        'base',
        'br',
        'col',
        'embed',
        'hr',
        'img',
        'input',
        'link',
        'meta',
        'param',
        'source',
        'track',
        'wbr',
    ];

    // Don't add self-closing or void elements to the stack.
    if (
        in_array($tag, $void_tags, true) ||
        preg_match('/\/\s*>$/', $part)
    ) {
        return true;
    }

    // Record the opening element and its exclusion status.
    $stack[] = [
        'tag' => $tag,
        'excluded' => swu_brand_trademark_is_excluded($part),
    ];

    return true;
}


/**
 * Determine whether the current position is inside an excluded element.
 *
 * @param array $stack Current element stack.
 * @return bool
 */
function swu_brand_trademark_is_inside_excluded($stack)
{
    foreach ($stack as $element) {
        if ($element['excluded']) {
            return true;
        }
    }

    return false;
}


/**
 * Determine whether the current position is inside a particular element.
 *
 * @param array  $stack Current element stack.
 * @param string $tag   Element name.
 * @return bool
 */
function swu_brand_trademark_is_inside_tag($stack, $tag)
{
    foreach ($stack as $element) {
        if ($element['tag'] === $tag) {
            return true;
        }
    }

    return false;
}


/**
 * Identify an existing trademark superscript immediately following a term.
 *
 * Also identifies any consecutive duplicate trademark superscripts.
 *
 * @param array  $parts     HTML fragments.
 * @param int    $index     Current text fragment index.
 * @param string $remaining Text following the matched term.
 * @return array|false Existing superscript positions or false.
 */
function swu_brand_trademark_find_existing_sup(
    &$parts,
    $index,
    $remaining
) {
    // There must be no additional text after the matched term.
    if (trim($remaining) !== '') {
        return false;
    }

    $count = count($parts);
    $position = $index + 1;

    // Skip whitespace between the term and its superscript.
    while (
        $position < $count &&
        trim($parts[$position]) === '' &&
        !swu_brand_trademark_get_tag_info($parts[$position])
    ) {
        $position++;
    }

    // Look for an opening superscript.
    if (
        !isset($parts[$position]) ||
        !preg_match('/^<sup\b[^>]*>$/i', $parts[$position])
    ) {
        return false;
    }

    $open = $position;
    $text = $position + 1;
    $close = $position + 2;

    // Verify that the superscript contains a registered trademark.
    if (
        !isset($parts[$text], $parts[$close]) ||
        !preg_match(
            '/^\s*(?:®|&reg;|&#0*174;|&#x0*ae;)\s*$/i',
            $parts[$text]
        ) ||
        !preg_match('/^<\/sup\s*>$/i', $parts[$close])
    ) {
        return false;
    }

    $result = [
        'found' => true,
        'duplicates' => [],
    ];

    // Look for immediately consecutive duplicate trademark superscripts.
    $position = $close + 1;

    while ($position < $count) {

        // Preserve whitespace between superscripts.
        $next = $position;

        while (
            $next < $count &&
            trim($parts[$next]) === '' &&
            !swu_brand_trademark_get_tag_info($parts[$next])
        ) {
            $next++;
        }

        // No further superscript.
        if (
            !isset($parts[$next]) ||
            !preg_match('/^<sup\b[^>]*>$/i', $parts[$next])
        ) {
            break;
        }

        // Check its contents.
        if (
            !isset($parts[$next + 1], $parts[$next + 2]) ||
            !preg_match(
                '/^\s*(?:®|&reg;|&#0*174;|&#x0*ae;)\s*$/i',
                $parts[$next + 1]
            ) ||
            !preg_match('/^<\/sup\s*>$/i', $parts[$next + 2])
        ) {
            break;
        }

        // Mark the duplicate superscript for removal.
        $result['duplicates'][] = $next;
        $result['duplicates'][] = $next + 1;
        $result['duplicates'][] = $next + 2;

        $position = $next + 3;
    }

    return $result;
}


/**
 * Normalise existing registered trademark symbols.
 *
 * Wraps standalone trademark symbols in <sup> elements.
 *
 * Existing superscripts and excluded elements are left untouched.
 *
 * @param string $html Complete HTML document.
 * @return string Normalised HTML.
 */
function swu_normalise_brand_trademarks($html)
{
    if (!is_string($html) || $html === '') {
        return $html;
    }

    $parts = wp_html_split($html);

    if (!$parts) {
        return $html;
    }

    $stack = [];

    foreach ($parts as &$part) {

        // Update the element stack when encountering HTML tags.
        if (swu_brand_trademark_update_stack($part, $stack)) {
            continue;
        }

        // Respect excluded elements.
        if (swu_brand_trademark_is_inside_excluded($stack)) {
            continue;
        }

        // Don't modify existing superscript content.
        if (swu_brand_trademark_is_inside_tag($stack, 'sup')) {
            continue;
        }

        // Normalise standalone registered trademark symbols.
        $part = preg_replace(
            '/(&reg;|&#0*174;|&#x0*ae;|®)/i',
            '<sup>$1</sup>',
            $part
        );
    }

    unset($part);

    return implode('', $parts);
}


/**
 * Enforce registered trademarks on specified brand terms.
 *
 * Existing trademarks are recognised and duplicate superscripts removed.
 *
 * @param string $html Complete HTML document.
 * @return string Processed HTML document.
 */
function swu_enforce_brand_trademarks($html)
{
    if (
        !is_string($html) ||
        $html === '' ||
        !str_contains($html, '<')
    ) {
        return $html;
    }

    // First pass: normalise all existing trademark symbols.
    $html = swu_normalise_brand_trademarks($html);

    // Build the brand matching expression.
    $terms = array_map(
        'preg_quote',
        swu_brand_trademark_terms()
    );

    $pattern = '/\b(' . implode('|', $terms) . ')\b/i';

    // Split the normalised HTML into tags and text.
    $parts = wp_html_split($html);

    if (!$parts) {
        return $html;
    }

    $stack = [];
    $consumed = [];

    // Process each HTML fragment.
    foreach ($parts as $index => &$part) {

        // Remove duplicate superscript fragments identified earlier.
        if (isset($consumed[$index])) {
            $part = '';
            continue;
        }

        // Maintain the HTML element stack.
        if (swu_brand_trademark_update_stack($part, $stack)) {
            continue;
        }

        // Ignore excluded elements.
        if (swu_brand_trademark_is_inside_excluded($stack)) {
            continue;
        }

        // Ignore existing superscript content.
        if (swu_brand_trademark_is_inside_tag($stack, 'sup')) {
            continue;
        }

        // Only process text fragments containing a brand term.
        if (!preg_match($pattern, $part)) {
            continue;
        }

        $text = $part;
        $matches = [];

        preg_match_all(
            $pattern,
            $text,
            $matches,
            PREG_OFFSET_CAPTURE
        );

        $output = '';
        $offset = 0;

        foreach ($matches[0] as $match) {

            $term = $match[0];
            $position = $match[1];

            // Preserve text preceding the matched term.
            $output .= substr(
                $text,
                $offset,
                $position - $offset
            );

            // Preserve the original term and capitalisation.
            $output .= $term;

            $end = $position + strlen($term);

            // Check for an existing trademark superscript.
            $existing = swu_brand_trademark_find_existing_sup(
                $parts,
                $index,
                substr($text, $end)
            );

            if ($existing) {

                // Mark consecutive duplicate superscripts for removal.
                foreach ($existing['duplicates'] as $duplicate_index) {
                    $consumed[$duplicate_index] = true;
                }

            } else {

                // No existing trademark: append one.
                $output .= '<sup>®</sup>';
            }

            $offset = $end;
        }

        // Preserve remaining text.
        $output .= substr($text, $offset);

        $part = $output;
    }

    unset($part);

    return implode('', $parts);
}


/**
 * Start frontend output buffering.
 *
 * Processes the complete HTML response rather than individual WordPress
 * content filters.
 */
function swu_start_brand_trademark_buffer()
{
    if (
        is_admin() ||
        wp_doing_ajax() ||
        is_feed() ||
        is_embed() ||
        (defined('REST_REQUEST') && REST_REQUEST)
    ) {
        return;
    }

    ob_start('swu_enforce_brand_trademarks');
}

add_action(
    'template_redirect',
    'swu_start_brand_trademark_buffer',
    0
);
