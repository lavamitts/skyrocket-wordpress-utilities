(function (blocks, blockEditor, element) {
    var el = element.createElement;
    var RichText = blockEditor.RichText;
    var useBlockProps = blockEditor.useBlockProps;

    blocks.registerBlockType('swu/stars-icon-block', {
        edit: function (props) {
            var attributes = props.attributes;
            var setAttributes = props.setAttributes;

            // This hook handles block properties and injects editor styles
            var blockProps = useBlockProps({
                className: 'stars icon wp-block-paragraph'
            });

            return el(RichText, Object.assign({}, blockProps, {
                tagName: 'p',
                value: attributes.starsName,
                allowedFormats: [], // Strips bold/italic tools for plain text
                placeholder: 'Enter content...',
                onChange: function (val) {
                    setAttributes({ starsName: val });
                }
            }));
        },
        save: function (props) {
            var attributes = props.attributes;

            var blockProps = useBlockProps.save({
                className: 'stars icon wp-block-paragraph'
            });

            return el(RichText.Content, Object.assign({}, blockProps, {
                tagName: 'p',
                value: attributes.starsName
            }));
        }
    });
})(window.wp.blocks, window.wp.blockEditor, window.wp.element);