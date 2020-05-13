(function ($) {

    // Check if "acf" is available
    if (typeof acf === 'undefined') {
        return;
    }

    /**
     * Define variables
     */
    var fonts  = acf.get('custom_fonts');
    var colors = acf.get('custom_colors');
    var styles = acf.get('custom_styles');

    /**
     * Colors
     * @returns {*}
     */
    var get_custom_colors = function () {
        return $.map(colors, function (color, key) {
            return {
                name: 'pip-text-' + key,
                value: 'pip-text-' + key,
                text: color.name,
                textStyle: key,
                format: {
                    inline: 'span',
                    classes: color.classes,
                    wrapper: true,
                    deep: true,
                    split: true,
                }
            };
        });
    };

    /**
     * Fonts
     * @returns {*}
     */
    var get_custom_fonts = function () {
        return $.map(fonts, function (font, key) {
            return {
                name: 'pip-font-' + key,
                value: 'pip-font-' + key,
                text: font.name,
                textStyle: 'font-family:' + font.name,
                format: {
                    inline: 'span',
                    classes: font.classes,
                    wrapper: true,
                    deep: true,
                    split: true,
                }
            };
        });
    };

    /**
     * Styles
     * @returns {*}
     */
    var get_custom_styles = function () {
        return $.map(styles, function (style, key) {
            return {
                name: 'pip-style-' + key,
                value: 'pip-style-' + key,
                text: style.name,
                textStyle: key,
                format: {
                    block: 'span',
                    classes: style.classes,
                    wrapper: true,
                    deep: true,
                    split: true,
                }
            };
        });
    };

    /**
     * Customize TinyMCE Editor
     */
    acf.addFilter('wysiwyg_tinymce_settings', function (init) {

        init.elementpath             = false;
        init.block_formats           = '<p>=p;<h1>=h1;<h2>=h2;<h3>=h3;<h4>=h4;<h5>=h5;<h6>=h6;<address>=address;<pre>=pre';
        init.valid_elements          = '*[*]';
        init.extended_valid_elements = '*[*]';

        return init;
    });

    // Wait for TinyMCE to be ready
    $(document).on('tinymce-editor-setup', function (event, editor) {

        // Register custom commands
        Commands.register(editor);

        /**
         * Add colors menu button
         */
        if (get_custom_colors().length > 0) {
            editor.addButton('pip_colors', function () {
                return {
                    type: 'listbox',
                    text: 'Colors',
                    tooltip: 'Colors',
                    values: get_custom_colors(),
                    fixedWidth: true,
                    onPostRender: custom_list_box_change_handler(editor, get_custom_colors()),
                    onselect: function (event) {
                        if (event.control.settings.value) {
                            event.control.settings.type = 'colors';
                            editor.execCommand('add_custom_style', false, event.control.settings);
                        }
                    },
                };
            });
        }

        /**
         * Add fonts menu button
         */
        if (get_custom_fonts().length > 0) {
            editor.addButton('pip_fonts', function () {
                return {
                    type: 'listbox',
                    text: 'Fonts',
                    tooltip: 'Fonts',
                    values: get_custom_fonts(),
                    fixedWidth: true,
                    onPostRender: custom_list_box_change_handler(editor, get_custom_fonts()),
                    onselect: function (event) {
                        if (event.control.settings.value) {
                            event.control.settings.type = 'fonts';
                            editor.execCommand('add_custom_style', false, event.control.settings);
                        }
                    },
                };
            });
        }

        /**
         * Add styles menu button
         */
        if (get_custom_styles().length > 0) {
            editor.addButton('pip_styles', function () {
                return {
                    type: 'listbox',
                    text: 'Styles',
                    tooltip: 'Styles',
                    values: get_custom_styles(),
                    fixedWidth: true,
                    onPostRender: custom_list_box_change_handler(editor, get_custom_styles()),
                    onselect: function (event) {
                        if (event.control.settings.value) {
                            event.control.settings.type = 'styles';
                            editor.execCommand('add_custom_style', false, event.control.settings);
                        }
                    },
                };
            });
        }

        /**
         * Register custom formats
         */
        editor.on('init', function () {
            get_custom_colors().map(function (item) {
                editor.formatter.register(item.name, item.format);
            });

            get_custom_fonts().map(function (item) {
                editor.formatter.register(item.name, item.format);
            });

            get_custom_styles().map(function (item) {
                editor.formatter.register(item.name, item.format);
            });
        });

    });

    /**
     * Register custom commands
     * @param editor
     */
    var register = function (editor) {
        editor.addCommand('add_custom_style', function (command, item) {

            // Get style to remove
            var to_remove = Array();
            if (item.type === 'styles') {
                to_remove = get_custom_styles();
            } else if (item.type === 'colors') {
                to_remove = get_custom_colors();
            } else if (item.type === 'fonts') {
                to_remove = get_custom_fonts();
            }

            // Remove old style
            $.each(to_remove, function (key, style_item) {
                if (style_item.name !== item.name) {
                    editor.formatter.remove(style_item.name);
                }
            });

            // Apply selected style
            editor.formatter.toggle(item.name);
            editor.nodeChanged();
        });
    };
    var Commands = { register: register };

    /**
     * Set active on menu item
     * @param editor
     * @param items
     * @returns {function(...[*]=)}
     */
    var custom_list_box_change_handler = function (editor, items) {
        return function () {
            var self = this;
            self.value(null);

            editor.on('nodeChange', function (e) {

                // Get value
                var current_value = null, current_style = null;
                $.map(items, function (item) {
                    if (editor.formatter.match(item.name)) {
                        current_value = item.value;
                        current_style = item.textStyle;
                    }
                });

                // Update value
                self.value(current_value);
                self.$el.find('span').attr('style', current_style);

            });
        };
    };

})(jQuery);
