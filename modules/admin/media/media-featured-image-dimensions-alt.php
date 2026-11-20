<?php
/**
 * Display featured image dimensions and ALT text in the WordPress block editor.
 *
 * Retrieves the featured image dimensions and ALT text from WordPress
 * attachment metadata and displays them beneath the Featured Image panel.
 *
 * The information is updated automatically when the featured image is
 * selected, replaced or removed.
 *
 * @package Skyrocket
 */

defined('ABSPATH') || exit;

/**
 * Enqueue the featured image information functionality.
 *
 * Only loads on supported post editing screens where the block editor is
 * enabled and featured images are supported.
 *
 * @return void
 */
function swu_enqueue_featured_image_dimensions(): void
{

    $screen = get_current_screen();

    // Only load on post editing screens.
    if (
        !$screen ||
        !in_array($screen->base, ['post', 'post-new'], true) ||
        empty($screen->post_type)
    ) {
        return;
    }

    // Check featured image support.
    if (!post_type_supports($screen->post_type, 'thumbnail')) {
        return;
    }

    // Check that the block editor is enabled.
    if (!use_block_editor_for_post_type($screen->post_type)) {
        return;
    }

    // Register the inline JavaScript.
    wp_register_script(
        'swu-featured-image-dimensions',
        false,
        ['wp-data', 'wp-api-fetch', 'wp-dom-ready'],
        null,
        true
    );

    wp_enqueue_script('swu-featured-image-dimensions');

    // Add the JavaScript functionality.
    wp_add_inline_script(
        'swu-featured-image-dimensions',
        <<<'JS'
(function () {
    'use strict';

    let currentAttachmentId = null;
    let requestId = 0;
    let dimensionsElement = null;

    /**
     * Locate the Featured Image panel and create the information element.
     *
     * @return {HTMLElement|null}
     */
    function getDimensionsElement() {

        const panel = document.querySelector(
            '.editor-post-featured-image'
        );

        if (!panel) {
            return null;
        }

        if (!dimensionsElement || !panel.contains(dimensionsElement)) {

            dimensionsElement = document.createElement('div');

            dimensionsElement.className =
                'swu-featured-image-dimensions';

            dimensionsElement.style.margin = '8px 0 0';
            dimensionsElement.style.fontSize = '12px';
            dimensionsElement.style.color = '#757575';
            dimensionsElement.style.lineHeight = '1.6';

            dimensionsElement.setAttribute('aria-live', 'polite');

            panel.appendChild(dimensionsElement);
        }

        return dimensionsElement;
    }

    /**
     * Display the featured image information.
     *
     * @param {number|null} attachmentId WordPress attachment ID.
     * @return {void}
     */
    function updateDimensions(attachmentId) {

        const element = getDimensionsElement();

        if (!element) {
            return;
        }

        // Clear the information when no featured image is assigned.
        if (!attachmentId) {

            currentAttachmentId = null;

            // Invalidate any outstanding requests.
            requestId++;

            element.replaceChildren();

            return;
        }

        // Avoid unnecessary requests for the same attachment.
        if (attachmentId === currentAttachmentId) {
            return;
        }

        currentAttachmentId = attachmentId;

        const thisRequest = ++requestId;

        element.textContent = 'Loading image information…';

        wp.apiFetch({
            path: '/wp/v2/media/' + attachmentId
        })
        .then(function (media) {

            // Ignore responses from previous requests.
            if (thisRequest !== requestId) {
                return;
            }

            const width = media.media_details?.width;
            const height = media.media_details?.height;

            const dimensions = document.createElement('div');
            const altText = document.createElement('div');

            // Display image dimensions.
            if (
                Number.isInteger(width) &&
                Number.isInteger(height) &&
                width > 0 &&
                height > 0
            ) {

                dimensions.textContent =
                    width + ' × ' + height + ' px';

            } else {

                dimensions.textContent =
                    'Dimensions unavailable';

            }

            // Display the ALT text stored in the Media Library.
            altText.textContent =
                'ALT text: ' +
                (
                    typeof media.alt_text === 'string' &&
                    media.alt_text.length > 0
                        ? media.alt_text
                        : '(empty)'
                );

            element.replaceChildren(dimensions, altText);

        })
        .catch(function () {

            if (thisRequest !== requestId) {
                return;
            }

            element.textContent =
                'Image information unavailable';

        });
    }

    /**
     * Initialise the featured image information functionality.
     *
     * @return {void}
     */
    function initialise() {

        // Observe changes to the editor interface.
        const observer = new MutationObserver(function () {

            getDimensionsElement();

        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });

        // Monitor changes to the featured image.
        wp.data.subscribe(function () {

            const editor = wp.data.select('core/editor');

            if (!editor) {
                return;
            }

            const attachmentId =
                editor.getEditedPostAttribute('featured_media');

            updateDimensions(attachmentId || null);

        });

        // Handle the initial editor state.
        const editor = wp.data.select('core/editor');

        if (editor) {

            updateDimensions(
                editor.getEditedPostAttribute('featured_media') || null
            );

        }
    }

    wp.domReady(initialise);

})();
JS
    );
}

add_action(
    'admin_enqueue_scripts',
    'swu_enqueue_featured_image_dimensions'
);
