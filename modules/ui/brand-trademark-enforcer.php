<?php
/**
 * Brand Trademark Enforcer
 *
 * Automatically appends a registered trademark symbol (®) to specified
 * brand terms in WordPress content.
 *
 * Existing trademark symbols are preserved without duplication.
 *
 * @package Skyrocket
 */

defined('ABSPATH') || exit;

/**
 * Append registered trademark symbols to specified brand terms.
 *
 * @param string $content The post content.
 * @return string Modified post content.
 */
function swu_enforce_brand_trademarks($content)
{
    // Only process frontend content.
    if (is_admin() || !is_string($content) || $content === '') {
        return $content;
    }

    // Terms requiring registered trademark symbols.
    $terms = [
        'Sudometrics',
        'Sudopad',
        'Neuropad',
        'feet4life',
        'steps',
    ];

    // Build a case-insensitive regular expression.
    $pattern = '/\b(' . implode('|', array_map('preg_quote', $terms)) . ')\b/i';

    // Parse the content as HTML.
    $dom = new DOMDocument();

    $previous_errors = libxml_use_internal_errors(true);

    $dom->loadHTML(
        '<?xml encoding="UTF-8"><div id="swu-trademark-content">' . $content . '</div>',
        LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
    );

    libxml_clear_errors();
    libxml_use_internal_errors($previous_errors);

    $xpath = new DOMXPath($dom);

    // Find text nodes, excluding technical and form elements.
    $nodes = $xpath->query(
        '//text()[not(ancestor::script)
        and not(ancestor::style)
        and not(ancestor::noscript)
        and not(ancestor::sup)
        and not(ancestor::textarea)
        and not(ancestor::input)
        and not(ancestor::select)
        and not(ancestor::option)]'
    );

    if (!$nodes) {
        return $content;
    }

    // Copy nodes into an array before modifying the DOM.
    $text_nodes = [];

    foreach ($nodes as $node) {
        if (preg_match($pattern, $node->nodeValue)) {
            $text_nodes[] = $node;
        }
    }

    foreach ($text_nodes as $node) {
        $text = $node->nodeValue;

        $fragment = $dom->createDocumentFragment();

        $offset = 0;

        preg_match_all($pattern, $text, $matches, PREG_OFFSET_CAPTURE);

        foreach ($matches[0] as $match) {
            $term = $match[0];
            $position = $match[1];

            // Preserve text preceding the match.
            if ($position > $offset) {
                $fragment->appendChild(
                    $dom->createTextNode(
                        substr($text, $offset, $position - $offset)
                    )
                );
            }

            // Preserve the original matched term.
            $fragment->appendChild(
                $dom->createTextNode($term)
            );

            $end = $position + strlen($term);

            // Check for an existing ® immediately after the term.
            $remaining = substr($text, $end);

            $already_marked = preg_match('/^\s*®/', $remaining);

            if (!$already_marked) {
                $sup = $dom->createElement('sup');
                $sup->appendChild($dom->createTextNode('®'));

                $fragment->appendChild($sup);
            }

            $offset = $end;
        }

        // Preserve remaining text.
        if ($offset < strlen($text)) {
            $fragment->appendChild(
                $dom->createTextNode(substr($text, $offset))
            );
        }

        $node->parentNode->replaceChild($fragment, $node);
    }

    // Extract the processed content from its wrapper.
    $wrapper = $dom->getElementById('swu-trademark-content');

    if (!$wrapper) {
        return $content;
    }

    $output = '';

    foreach ($wrapper->childNodes as $child) {
        $output .= $dom->saveHTML($child);
    }

    return $output;
}

add_filter('the_content', 'swu_enforce_brand_trademarks', 20);
