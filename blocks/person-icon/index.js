(function (blocks, blockEditor, element) {
    var el = element.createElement;
    var RichText = blockEditor.RichText;
    var useBlockProps = blockEditor.useBlockProps;

    blocks.registerBlockType('swu/person-icon-block', {
        edit: function (props) {
            var attributes = props.attributes;
            var setAttributes = props.setAttributes;

            var blockProps = useBlockProps({
                className: 'person icon wp-block-paragraph'
            });

            return el(RichText, Object.assign({}, blockProps, {
                tagName: 'p',
                value: attributes.personName,
                allowedFormats: [],
                placeholder: 'Enter name of person ...',
                multiline: false, // Disables internal block line splitting
                __unstableDisableLineBreaks: true, // Prevents Enter key and pasted line breaks
                onChange: function (val) {
                    setAttributes({ personName: val });
                }
            }));
        },
        save: function (props) {
            var attributes = props.attributes;

            var blockProps = useBlockProps.save({
                className: 'person icon wp-block-paragraph'
            });

            return el(RichText.Content, Object.assign({}, blockProps, {
                tagName: 'p',
                value: attributes.personName
            }));
        }
    });
})(window.wp.blocks, window.wp.blockEditor, window.wp.element);