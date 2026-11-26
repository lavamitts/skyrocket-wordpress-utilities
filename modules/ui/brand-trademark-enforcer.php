<?php
/**
 * Brand Trademark Enforcer
 *
 * Processes visible text within the frontend HTML body.
 *
 * Features:
 * - Processes the complete frontend body, including menus, headers,
 *   footers, widgets and page content.
 * - Normalises existing registered trademark symbols into superscripts.
 * - Adds missing registered trademark symbols to specified brand terms.
 * - Prevents duplicate trademark symbols.
 * - Supports case-insensitive brand matching.
 * - Excludes elements carrying the do-not-enforce-brand-trademark class.
 * - Ignores HTML attributes, URLs, scripts, styles, JSON and SVG.
 * - Ignores hidden and non-content elements.
 * - Masks script and style blocks during DOM parsing so inline JS template
 *   literals and raw markup inside scripts are never altered.
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
        '10-minute screening test',
    ];
}


/**
 * Mask raw script and style blocks with unique placeholders.
 *
 * Prevents DOMDocument HTML parsing and serialisation from altering
 * inline JavaScript template literals or CSS content.
 *
 * @param string $html  HTML fragment.
 * @param array  $store Reference map of placeholder => original block.
 * @return string Masked HTML fragment.
 */
function swu_brand_trademark_mask_raw_blocks($html, &$store)
{
    $store = [];

    $pattern = '/<(script|style)\b[^>]*>.*?<\/\1\s*>/is';

    $masked = preg_replace_callback(
        $pattern,
        static function ($matches) use (&$store) {
            $token = 'SWURAWBLOCKTOKEN' . count($store) . 'ENDTOKEN';
            $store[$token] = $matches[0];
            return $token;
        },
        $html
    );

    return $masked ?? $html;
}


/**
 * Restore masked script and style blocks.
 *
 * @param string $html  Processed HTML fragment.
 * @param array  $store Map of placeholder => original block.
 * @return string Restored HTML fragment.
 */
function swu_brand_trademark_restore_raw_blocks($html, $store)
{
    if (empty($store)) {
        return $html;
    }

    return strtr($html, $store);
}


/**
 * Retrieve eligible text nodes from the document fragment.
 *
 * Excludes technical, hidden and explicitly excluded elements.
 *
 * @param DOMDocument $dom     Document containing the HTML fragment.
 * @param DOMElement  $wrapper Fragment wrapper.
 * @return array
 */
function swu_brand_trademark_get_text_nodes($dom, $wrapper)
{
    $xpath = new DOMXPath($dom);

    $query = './/text()[
        not(ancestor::script)
        and not(ancestor::style)
        and not(ancestor::noscript)
        and not(ancestor::template)
        and not(ancestor::svg)
        and not(ancestor::code)
        and not(ancestor::pre)
        and not(ancestor::textarea)
        and not(ancestor::select)
        and not(ancestor::option)
        and not(ancestor::canvas)
        and not(ancestor::iframe)
        and not(ancestor::head)
        and not(ancestor::*[@hidden])
        and not(ancestor::*[@aria-hidden="true"])
        and not(ancestor::*[
            contains(
                concat(" ", normalize-space(@class), " "),
                " do-not-enforce-brand-trademark "
            )
        ])
        and not(ancestor::*[
            contains(
                translate(@style, "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz"),
                "display:none"
            )
            or
            contains(
                translate(@style, "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz"),
                "visibility:hidden"
            )
        ])
    ]';

    $nodes = $xpath->query($query, $wrapper);

    if (!$nodes) {
        return [];
    }

    // Return a static array before modifying the DOM.
    $result = [];

    foreach ($nodes as $node) {
        $result[] = $node;
    }

    return $result;
}


/**
 * Replace a text node with a document fragment.
 *
 * @param DOMText            $node     Original text node.
 * @param DOMDocumentFragment $fragment Replacement fragment.
 * @return void
 */
function swu_brand_trademark_replace_node($node, $fragment)
{
    if ($node->parentNode) {
        $node->parentNode->replaceChild($fragment, $node);
    }
}


/**
 * Normalise existing registered trademark symbols.
 *
 * Converts standalone ® symbols into superscript elements.
 *
 * Existing superscript elements are not modified.
 *
 * @param DOMDocument $dom     Document.
 * @param DOMElement  $wrapper Fragment wrapper.
 * @return void
 */
function swu_brand_trademark_normalise($dom, $wrapper)
{
    $nodes = swu_brand_trademark_get_text_nodes($dom, $wrapper);

    foreach ($nodes as $node) {

        $text = $node->nodeValue;

        if (!str_contains($text, '®')) {
            continue;
        }

        // Locate existing registered trademark symbols.
        $matches = [];

        preg_match_all('/®/u', $text, $matches, PREG_OFFSET_CAPTURE);

        if (empty($matches[0])) {
            continue;
        }

        $fragment = $dom->createDocumentFragment();
        $offset = 0;

        foreach ($matches[0] as $match) {

            $position = $match[1];

            // Preserve preceding text.
            if ($position > $offset) {
                $fragment->appendChild(
                    $dom->createTextNode(
                        substr($text, $offset, $position - $offset)
                    )
                );
            }

            // Create the superscript element.
            $sup = $dom->createElement('sup');

            $sup->appendChild(
                $dom->createTextNode('®')
            );

            $fragment->appendChild($sup);

            $offset = $position + strlen($match[0]);
        }

        // Preserve remaining text.
        if ($offset < strlen($text)) {
            $fragment->appendChild(
                $dom->createTextNode(substr($text, $offset))
            );
        }

        swu_brand_trademark_replace_node($node, $fragment);
    }
}


/**
 * Check whether a node is an existing registered trademark superscript.
 *
 * @param DOMNode|null $node Node to inspect.
 * @return bool
 */
function swu_brand_trademark_is_sup($node)
{
    return (
        $node instanceof DOMElement &&
        strtolower($node->tagName) === 'sup' &&
        trim($node->textContent) === '®'
    );
}


/**
 * Find the next significant sibling of a node.
 *
 * Whitespace-only text nodes are ignored.
 *
 * @param DOMNode $node Starting node.
 * @return DOMNode|null
 */
function swu_brand_trademark_next_sibling($node)
{
    $next = $node->nextSibling;

    while (
        $next &&
        $next->nodeType === XML_TEXT_NODE &&
        trim($next->nodeValue) === ''
    ) {
        $next = $next->nextSibling;
    }

    return $next;
}


/**
 * Add missing registered trademark symbols to brand terms.
 *
 * Existing superscripts are recognised and preserved.
 *
 * @param DOMDocument $dom     Document.
 * @param DOMElement  $wrapper Fragment wrapper.
 * @return void
 */
function swu_brand_trademark_enforce($dom, $wrapper)
{
    $terms = array_map(
        static fn($term) => preg_quote($term, '/'),
        swu_brand_trademark_terms()
    );

    $pattern = '/(?<![\p{L}\p{N}_])(' .
        implode('|', $terms) .
        ')(?![\p{L}\p{N}_])/iu';

    $nodes = swu_brand_trademark_get_text_nodes($dom, $wrapper);

    foreach ($nodes as $node) {

        $text = $node->nodeValue;

        if (!preg_match($pattern, $text)) {
            continue;
        }

        $matches = [];

        preg_match_all(
            $pattern,
            $text,
            $matches,
            PREG_OFFSET_CAPTURE
        );

        if (empty($matches[0])) {
            continue;
        }

        $fragment = $dom->createDocumentFragment();
        $offset = 0;
        $match_count = count($matches[0]);

        foreach ($matches[0] as $index => $match) {

            $term = $match[0];
            $position = $match[1];

            // Preserve preceding text.
            if ($position > $offset) {
                $fragment->appendChild(
                    $dom->createTextNode(
                        substr($text, $offset, $position - $offset)
                    )
                );
            }

            // Preserve the original term and capitalisation.
            $fragment->appendChild(
                $dom->createTextNode($term)
            );

            $end = $position + strlen($term);

            // Check whether the term is followed by an existing ®.
            $already_marked = false;

            // An existing superscript can follow the original text node
            // when the trademark was normalised in the first pass.
            if (
                $index === $match_count - 1 &&
                trim(substr($text, $end)) === ''
            ) {
                $next = swu_brand_trademark_next_sibling($node);

                $already_marked = swu_brand_trademark_is_sup($next);
            }

            if (!$already_marked) {

                $sup = $dom->createElement('sup');

                $sup->appendChild(
                    $dom->createTextNode('®')
                );

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

        swu_brand_trademark_replace_node($node, $fragment);
    }
}


/**
 * Remove consecutive duplicate registered trademark superscripts.
 *
 * Only operates within eligible body content.
 *
 * @param DOMDocument $dom     Document.
 * @param DOMElement  $wrapper Fragment wrapper.
 * @return void
 */
function swu_brand_trademark_remove_duplicates($dom, $wrapper)
{
    $xpath = new DOMXPath($dom);

    $query = './/sup[
        not(ancestor::*[
            contains(
                concat(" ", normalize-space(@class), " "),
                " do-not-enforce-brand-trademark "
            )
        ])
    ]';

    $nodes = $xpath->query($query, $wrapper);

    if (!$nodes) {
        return;
    }

    // Use a static array because nodes will be removed.
    $superscripts = [];

    foreach ($nodes as $node) {
        if (swu_brand_trademark_is_sup($node)) {
            $superscripts[] = $node;
        }
    }

    foreach ($superscripts as $node) {

        if (!$node->parentNode) {
            continue;
        }

        $previous = $node->previousSibling;

        // Ignore intervening whitespace.
        while (
            $previous &&
            $previous->nodeType === XML_TEXT_NODE &&
            trim($previous->nodeValue) === ''
        ) {
            $previous = $previous->previousSibling;
        }

        // Remove consecutive duplicate superscripts.
        if (swu_brand_trademark_is_sup($previous)) {
            $node->parentNode->removeChild($node);
        }
    }
}


/**
 * Process the HTML body fragment.
 *
 * @param string $html Body HTML.
 * @return string Processed body HTML.
 */
function swu_brand_trademark_process_body($html)
{
    if (!class_exists('DOMDocument')) {
        return $html;
    }

    $masked_blocks = [];
    $prepared_html = swu_brand_trademark_mask_raw_blocks($html, $masked_blocks);

    // Create an isolated document fragment.
    $dom = new DOMDocument('1.0', 'UTF-8');

    $previous_errors = libxml_use_internal_errors(true);

    $loaded = $dom->loadHTML(
        '<?xml encoding="UTF-8"><div id="swu-brand-trademark-wrapper">' .
        $prepared_html .
        '</div>',
        LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
    );

    libxml_clear_errors();

    libxml_use_internal_errors($previous_errors);

    if (!$loaded) {
        return $html;
    }

    $wrapper = $dom->getElementById(
        'swu-brand-trademark-wrapper'
    );

    if (!$wrapper) {
        return $html;
    }

    // Pass 1: normalise existing trademark symbols.
    swu_brand_trademark_normalise($dom, $wrapper);

    // Pass 2: add trademarks to specified brand terms.
    swu_brand_trademark_enforce($dom, $wrapper);

    // Pass 3: remove consecutive duplicate superscripts.
    swu_brand_trademark_remove_duplicates($dom, $wrapper);

    // Extract the processed body contents.
    $output = '';

    foreach ($wrapper->childNodes as $child) {
        $output .= $dom->saveHTML($child);
    }

    return swu_brand_trademark_restore_raw_blocks($output, $masked_blocks);
}


/**
 * Process the complete frontend HTML document.
 *
 * Only the contents of the body element are passed to the processor.
 * The document head, HTML attributes and other document structure
 * remain outside the processing scope.
 *
 * @param string $html Complete HTML document.
 * @return string Processed HTML document.
 */
function swu_enforce_brand_trademarks($html)
{
    if (
        !is_string($html) ||
        $html === '' ||
        !str_contains($html, '<body')
    ) {
        return $html;
    }

    // Isolate the body contents.
    $result = preg_replace_callback(
        '/(<body\b[^>]*>)(.*?)(<\/body\s*>)/is',
        static function ($matches) {

            return (
                $matches[1] .
                swu_brand_trademark_process_body($matches[2]) .
                $matches[3]
            );
        },
        $html,
        1
    );

    return $result ?? $html;
}


/**
 * Start frontend output buffering.
 *
 * Processes the completed frontend HTML response.
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