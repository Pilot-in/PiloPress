(
    function ( $ ) {

        // Check if "acf" is available
        if ( typeof acf === 'undefined' ) {
            return;
        }

        /**
         * Define letiables
         */
        const fonts     = acf.get( 'custom_fonts' );
        const colors    = acf.get( 'custom_colors' );
        const styles    = acf.get( 'custom_styles' );
        const tw_prefix = acf.get( 'tw_prefix' );

        /**
         * Colors
         *
         * @returns {*}
         */
        const get_custom_colors = function () {
            return $.map(
                colors,
                function ( color, key ) {

                    // Skip if not add to editor
                    if ( color.add_to_editor === false ) {
                        return;
                    }

                    // Get text color and background
                    let textStyle = 'color:' + color.value + ';';
                    let bgColor   = getContrast( color.value );
                    if ( bgColor !== 'white' ) {
                        textStyle += 'background-color:' + bgColor + ';';
                    }

                    return {
                        name: 'pip-text-' + key,
                        value: 'pip-text-' + key,
                        text: color.name,
                        textStyle: textStyle,
                        format: {
                            inline: 'span',
                            classes: tw_prefix ? tw_prefix + 'text-' + color.class_name : 'text-' + color.class_name,
                            wrapper: false,
                            deep: true,
                            split: true,
                        },
                    };
                },
            );
        };

        /**
         * Check if color is light or dark
         *
         * @param hexcolor
         * @returns {string}
         */
        const getContrast = function ( hexcolor ) {
            hexcolor = hexcolor.charAt( 0 ) === '#' ? hexcolor.substring( 1, 7 ) : hexcolor;

            let r   = parseInt( hexcolor.substr( 0, 2 ), 16 );
            let g   = parseInt( hexcolor.substr( 2, 2 ), 16 );
            let b   = parseInt( hexcolor.substr( 4, 2 ), 16 );
            let yiq = ( ( r * 299 ) + ( g * 587 ) + ( b * 114 ) ) / 1000;
            return ( yiq >= 128 ) ? '#23282d' : 'white';
        };

        /**
         * Fonts
         *
         * @returns {*}
         */
        const get_custom_fonts = function () {
            return $.map(
                fonts,
                function ( font, key ) {

                    // Skip if not add to editor
                    if ( font.add_to_editor === false ) {
                        return;
                    }

                    return {
                        name: 'pip-font-' + key,
                        value: 'pip-font-' + key,
                        text: font.name,
                        textStyle: 'font-family:' + font.name,
                        format: {
                            inline: 'span',
                            classes: tw_prefix ? tw_prefix + 'font-' + font.class_name : 'font-' + font.class_name,
                            wrapper: false,
                            deep: true,
                            split: true,
                        },
                    };
                },
            );
        };

        /**
         * Styles
         *
         * @returns {*}
         */
        const get_custom_styles = function () {
            return $.map(
                styles,
                function ( style, key ) {

                    // Skip if not add to editor
                    if ( style.add_to_editor === false ) {
                        return;
                    }

                    return {
                        name: 'pip-style-' + key,
                        value: 'pip-style-' + key,
                        text: style.name,
                        textStyle: key,
                        format: {
                            inline: 'span',
                            classes: style.class_name,
                            wrapper: false,
                            deep: true,
                            split: true,
                        },
                    };
                },
            );
        };

        /**
         * Customize TinyMCE Editor
         */
        acf.addFilter(
            'wysiwyg_tinymce_settings',
            function ( init ) {

                init.elementpath             = false;
                init.block_formats           = '<p>=p;<h1>=h1;<h2>=h2;<h3>=h3;<h4>=h4;<h5>=h5;<h6>=h6;<address>=address;<pre>=pre';
                init.valid_elements          = '*[*]';
                init.extended_valid_elements = '*[*]';

                return init;
            },
        );

        // Wait for TinyMCE to be ready
        $( document ).on(
            'tinymce-editor-setup',
            function ( event, editor ) {

                // Register custom commands
                Commands.register( editor );

                /**
                 * Add colors menu button
                 */
                if ( get_custom_colors().length > 0 ) {
                    editor.addButton(
                        'pip_colors',
                        {
                            type: 'listbox',
                            text: 'Colors',
                            tooltip: 'Colors',
                            values: get_custom_colors(),
                            fixedWidth: true,
                            onPostRender: custom_list_box_change_handler( editor, get_custom_colors() ),
                            onselect: function ( event ) {
                                if ( event.control.settings.value ) {
                                    event.control.settings.type = 'colors';
                                    editor.execCommand( 'add_custom_style', false, event.control.settings );
                                }
                            },
                        },
                    );
                }

                /**
                 * Add fonts menu button
                 */
                if ( get_custom_fonts().length > 0 ) {
                    editor.addButton(
                        'pip_fonts',
                        {
                            type: 'listbox',
                            text: 'Fonts',
                            tooltip: 'Fonts',
                            values: get_custom_fonts(),
                            fixedWidth: true,
                            onPostRender: custom_list_box_change_handler( editor, get_custom_fonts() ),
                            onselect: function ( event ) {
                                if ( event.control.settings.value ) {
                                    event.control.settings.type = 'fonts';
                                    editor.execCommand( 'add_custom_style', false, event.control.settings );
                                }
                            },
                        },
                    );
                }

                /**
                 * Add styles menu button
                 */
                if ( get_custom_styles().length > 0 ) {
                    editor.addButton(
                        'pip_styles',
                        {
                            type: 'listbox',
                            text: 'Styles',
                            tooltip: 'Styles',
                            values: get_custom_styles(),
                            fixedWidth: true,
                            onPostRender: custom_list_box_change_handler( editor, get_custom_styles() ),
                            onselect: function ( event ) {
                                if ( event.control.settings.value ) {
                                    event.control.settings.type = 'styles';
                                    editor.execCommand( 'add_custom_style', false, event.control.settings );
                                }
                            },
                        },
                    );
                }

                /**
                 * Add dark mode button
                 */
                editor.addButton(
                    'pip_dark_mode',
                    {
                        type: 'button',
                        icon: 'contrast',
                        tooltip: 'Dark Mode',
                        onClick: function () {
                            let new_color;
                            let dark_mode_value;

                            // Switch background color
                            if ( 'rgb(35, 40, 45)' === editor.getBody().style.backgroundColor ) {
                                new_color       = '#FFFFFF';
                                dark_mode_value = '';
                            } else {
                                new_color       = '#23282d';
                                dark_mode_value = '1';
                            }

                            toggle_dark_mode( editor, dark_mode_value );

                            editor.getBody().style.backgroundColor = new_color;
                        },
                    },
                );

                /**
                 * Register custom formats
                 */
                editor.on(
                    'init',
                    function () {

                        // maybe_activate_dark_mode( editor );

                        const io = new IntersectionObserver(
                            function ( entries ) {
                                const current_item = entries[0];

                                // If visible, check dark mode
                                if ( current_item.intersectionRatio !== 0 ) {
                                    maybe_activate_dark_mode( editor );
                                }
                            },
                        );

                        const wysiwyg_fields = $( 'div.acf-field[data-type="wysiwyg"] .mce-edit-area' );
                        if ( wysiwyg_fields ) {
                            $.each(
                                wysiwyg_fields,
                                function ( key, wysiwyg_field ) {
                                    // Start observation
                                    io.observe( wysiwyg_field );
                                },
                            );
                        }

                        get_custom_colors().map(
                            function ( item ) {
                                editor.formatter.register( item.name, item.format );
                            },
                        );

                        get_custom_fonts().map(
                            function ( item ) {
                                editor.formatter.register( item.name, item.format );
                            },
                        );

                        get_custom_styles().map(
                            function ( item ) {
                                editor.formatter.register( item.name, item.format );
                            },
                        );
                    },
                );

            },
        );

        /**
         * Maybe set dark mode on editor init
         *
         * @param editor
         */
        let maybe_activate_dark_mode = function ( editor ) {

            let acf_field            = get_acf_field_from_editor( editor );
            let field_name           = acf_field.data( 'name' );
            let field_name_dark_mode = field_name + '_dark_mode';

            if ( field_name ) {

                let input_field = acf_field.next( '.acf-field[data-name="' + field_name_dark_mode + '"]' ).find( 'input' );

                if ( input_field.val() === '1' ) {

                    // Set dark mode
                    editor.getBody().style.backgroundColor = '#23282d';

                }
            }

        };

        /**
         * Toggle dark mode value
         *
         * @param editor
         *
         * @param dark_mode_value
         */
        const toggle_dark_mode = function ( editor, dark_mode_value ) {

            let acf_field            = get_acf_field_from_editor( editor );
            let field_name           = acf_field.data( 'name' );
            let field_name_dark_mode = field_name + '_dark_mode';

            // Toggle dark mode value
            if ( field_name ) {

                acf_field.next( '.acf-field[data-name="' + field_name_dark_mode + '"]' ).find( 'input' ).val( dark_mode_value );

            }

        };

        /**
         * Get ACF field from editor
         *
         * @param editor
         *
         * @returns {jQuery}
         */
        const get_acf_field_from_editor = function ( editor ) {
            // Get field name
            let textarea = editor.getElement();

            return $( textarea ).parents( '.acf-field-wysiwyg' );
        };

        /**
         * Register custom commands
         *
         * @param editor
         */
        const register = function ( editor ) {
            editor.addCommand(
                'add_custom_style',
                function ( command, item ) {

                    // Get style to remove
                    let to_remove = Array();
                    if ( item.type === 'styles' ) {
                        to_remove = get_custom_styles();
                    } else if ( item.type === 'colors' ) {
                        to_remove = get_custom_colors();
                    } else if ( item.type === 'fonts' ) {
                        to_remove = get_custom_fonts();
                    }

                    // Remove old style
                    $.each(
                        to_remove,
                        function ( key, style_item ) {
                            if ( style_item.name !== item.name ) {
                                editor.formatter.remove( style_item.name );
                            }
                        },
                    );

                    // Apply selected style
                    editor.formatter.toggle( item.name );
                    editor.nodeChanged();
                },
            );
        };
        const Commands = { register: register };

        /**
         * Set active on menu item
         *
         * @param editor
         * @param items
         *
         * @returns {function(...[*]=)}
         */
        const custom_list_box_change_handler = function ( editor, items ) {
            return function () {
                let self = this;
                self.value( null );

                editor.on(
                    'nodeChange',
                    function ( e ) {

                        // Get value
                        let current_value = null, current_style = null;
                        $.map(
                            items,
                            function ( item ) {
                                if ( editor.formatter.match( item.name ) ) {
                                    current_value = item.value;
                                    current_style = item.textStyle;
                                }
                            },
                        );

                        // Update value
                        self.value( current_value );
                        self.$el.find( 'span' ).attr( 'style', current_style );

                    },
                );
            };
        };

        const hexToRgb = function ( hex ) {
            let result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec( hex );
            return result ? {
                r: parseInt( result[1], 16 ),
                g: parseInt( result[2], 16 ),
                b: parseInt( result[3], 16 ),
            } : null;
        };

    }
)( jQuery );
