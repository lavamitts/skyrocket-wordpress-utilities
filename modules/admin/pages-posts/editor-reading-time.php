<?php
/**
 * Display word count and estimated reading time in the WordPress block editor.
 *
 * Adds a Reading Time panel to the editor's Document sidebar, displaying
 * the current word count and estimated reading time for the post content.
 *
 * The information updates automatically as the content is edited.
 *
 * Reading time is calculated using an average reading speed of 200 words
 * per minute, rounded up to the next whole minute.
 *
 * @package Skyrocket
 */

defined('ABSPATH') || exit;

/**
 * Enqueue the reading time functionality.
 *
 * Only loads on supported post editing screens where the block editor
 * is enabled.
 *
 * @return void
 */
function swu_enqueue_reading_time(): void
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

    // Check that the block editor is enabled.
    if (!use_block_editor_for_post_type($screen->post_type)) {
        return;
    }

    // Register the inline JavaScript.
    wp_register_script(
        'swu-reading-time',
        false,
        [
            'wp-data',
            'wp-element',
            'wp-plugins',
            'wp-edit-post',
        ],
        null,
        true
    );

    wp_enqueue_script('swu-reading-time');

    // Add the JavaScript functionality.
    wp_add_inline_script(
        'swu-reading-time',
        <<<'JS'
(function (wp) {
    'use strict';

    const {createElement: el} = wp.element;
    const {registerPlugin} = wp.plugins;
    const {PluginDocumentSettingPanel} = wp.editPost;
    const {useSelect} = wp.data;

    const READING_SPEED = 200;

    /**
     * Calculate the word count from HTML content.
     *
     * Removes markup and non-content elements before counting words.
     *
     * @param {string} content Post content.
     * @return {number} Word count.
     */
    function getWordCount(content) {

        if (!content || typeof content !== 'string') {
            return 0;
        }

        // Convert HTML into plain text.
        const document = new DOMParser().parseFromString(
            content,
            'text/html'
        );

        // Remove elements that should not contribute to the word count.
        document.querySelectorAll(
            'script, style, template, noscript'
        ).forEach(function (element) {
            element.remove();
        });

        const text = document.body.textContent
            .replace(/\s+/g, ' ')
            .trim();

        if (!text) {
            return 0;
        }

        // Count words, including words containing apostrophes.
        const words = text.match(
            /[\p{L}\p{N}]+(?:[’'][\p{L}\p{N}]+)*/gu
        );

        return words ? words.length : 0;
    }

    /**
     * Reading time panel component.
     *
     * @return {WPElement} Reading time panel.
     */
    function ReadingTimePanel() {

        // Retrieve the current unsaved editor content.
        const content = useSelect(function (select) {

            const editor = select('core/editor');

            return editor
                ? editor.getEditedPostContent()
                : '';

        }, []);

        const wordCount = getWordCount(content);

        const readingTime = wordCount > 0
            ? Math.ceil(wordCount / READING_SPEED)
            : 0;

        return el(
            PluginDocumentSettingPanel,
            {
                name: 'swu-reading-time',
                title: 'Reading time',
                className: 'swu-reading-time-panel'
            },

            el(
                'div',
                {
                    style: {
                        display: 'flex',
                        flexDirection: 'column',
                        gap: '12px'
                    }
                },

                // Word count.
                el(
                    'div',
                    {
                        style: {
                            display: 'flex',
                            justifyContent: 'space-between',
                            alignItems: 'center'
                        }
                    },

                    el('span', {}, 'Word count'),

                    el(
                        'strong',
                        {},
                        wordCount.toLocaleString()
                    )
                ),

                // Estimated reading time.
                el(
                    'div',
                    {
                        style: {
                            display: 'flex',
                            justifyContent: 'space-between',
                            alignItems: 'center'
                        }
                    },

                    el('span', {}, 'Estimated reading time'),

                    el(
                        'strong',
                        {},

                        readingTime > 0
                            ? readingTime + (
                                readingTime === 1
                                    ? ' minute'
                                    : ' minutes'
                            )
                            : '—'
                    )
                )
            )
        );
    }

    // Register the editor sidebar panel.
    registerPlugin(
        'swu-reading-time',
        {
            render: ReadingTimePanel
        }
    );

})(window.wp);
JS
    );
}

add_action(
    'admin_enqueue_scripts',
    'swu_enqueue_reading_time'
);
