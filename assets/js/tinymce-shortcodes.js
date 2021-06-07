(
    function ( $ ) {

        // Check if "acf" is available
        if ( typeof acf === 'undefined' ) {
            return;
        }

        var buttons            = acf.get( 'custom_buttons' );
        var get_custom_buttons = function () {
            return $.map(
                buttons,
                function ( button, key ) {

                    // Skip if not add to editor
                    if ( button.add_to_editor === false ) {
                        return;
                    }

                    return { text: button.name, value: button.class_name };
                },
            );
        };

        // Wait for TinyMCE to be ready
        $( document ).on(
            'tinymce-editor-setup',
            function ( event, editor ) {

                // Button shortcode
                var pip_button = {
                    text: 'Button',
                    tag: 'pip_button',
                    name: 'Add button',
                    body: [
                        {
                            label: 'Text',
                            name: 'text',
                            type: 'textbox',
                            value: '',
                            tooltip: 'To add HTML, replace double quotes by simple quotes.',
                        },
                        {
                            label: 'Type',
                            name: 'type',
                            type: 'listbox',
                            values: get_custom_buttons(),
                        },
                        {
                            label: 'Alignment',
                            name: 'alignment',
                            type: 'listbox',
                            values: [
                                { text: 'Left', value: 'text-left' },
                                { text: 'Center', value: 'text-center' },
                                { text: 'Right', value: 'text-right' },
                            ],
                        },
                        {
                            label: 'Target',
                            name: 'target',
                            type: 'listbox',
                            values: [
                                { text: 'Same page', value: '_self' },
                                { text: 'New page', value: '_blank' },
                            ],
                        },
                        {
                            label: 'Download',
                            name: 'download',
                            type: 'checkbox',
                        },
                        {
                            label: 'Download name',
                            name: 'download_name',
                            type: 'textbox',
                            value: '',
                        },
                        {
                            label: 'Extra class',
                            name: 'xclass',
                            type: 'textbox',
                            value: '',
                        },
                        {
                            label: 'Link',
                            name: 'link',
                            type: 'textbox',
                            value: '#',
                        },
                        {
                            label: 'Button group',
                            name: 'nodiv',
                            type: 'checkbox',
                        },
                    ],
                    onclick: function ( event ) {
                        var attributes = event.control.settings;

                        // If no tag, return
                        if ( _.isUndefined( attributes.tag ) ) {
                            return;
                        }

                        // Get attributes
                        var window_title = !_.isUndefined( attributes.name ) ? attributes.name : 'Add shortcode';

                        // Modal
                        editor.windowManager.open(
                            {
                                title: window_title,
                                body: attributes.body,
                                onsubmit: function ( event ) {
                                    editor.insertContent( build_shortcode( event, attributes ) );
                                },
                            },
                        );
                    },
                };

                // Breadcrumb shortcode
                var pip_breadcrumb = {
                    text: 'Breadcrumb',
                    tag: 'pip_breadcrumb',
                    name: 'Add breadcrumb',
                    onclick: function () {
                        editor.insertContent( '[pip_breadcrumb]' );
                    },
                };

                // Title shortcode
                var pip_title = {
                    text: 'Title',
                    tag: 'pip_title',
                    name: 'Add title',
                    onclick: function () {
                        editor.insertContent( '[pip_title]' );
                    },
                };

                // ACF Field shortcode
                var pip_field = {
                    text: 'ACF Field',
                    tag: 'acf',
                    name: 'Add field',
                    body: [
                        {
                            label: 'Field name',
                            name: 'field',
                            type: 'textbox',
                            value: '',
                        },
                        {
                            label: 'Post ID',
                            name: 'post_id',
                            type: 'textbox',
                            value: '',
                        },
                    ],
                    onclick: function ( event ) {
                        // Get attributes
                        var attributes   = event.control.settings;
                        var window_title = !_.isUndefined( attributes.name ) ? attributes.name : 'Add shortcode';

                        // Modal
                        editor.windowManager.open(
                            {
                                title: window_title,
                                body: attributes.body,
                                onsubmit: function ( event ) {
                                    editor.insertContent( build_shortcode( event, attributes ) );
                                },
                            },
                        );
                    },
                };

                // Thumbnail shortcode
                var pip_thumbnail = {
                    text: 'Thumbnail',
                    tag: 'pip_thumbnail',
                    name: 'Add thumbnail',
                    body: [
                        {
                            label: 'Size',
                            name: 'size',
                            type: 'listbox',
                            values: [
                                { text: 'Thumbnail', value: 'thumbnail' },
                                { text: 'Medium', value: 'medium' },
                                { text: 'Large', value: 'large' },
                                { text: 'Full', value: 'full' },
                            ],
                        },
                    ],
                    onclick: function ( event ) {
                        // Get attributes
                        var attributes   = event.control.settings;
                        var window_title = !_.isUndefined( attributes.name ) ? attributes.name : 'Add shortcode';

                        // Modal
                        editor.windowManager.open(
                            {
                                title: window_title,
                                body: attributes.body,
                                onsubmit: function ( event ) {
                                    editor.insertContent( build_shortcode( event, attributes ) );
                                },
                            },
                        );
                    },
                };

                // Spacer shortcode
                var pip_spacer = {
                    text: 'Spacer',
                    tag: 'pip_spacer',
                    name: 'Add spacer',
                    body: [
                        {
                            label: 'Spacer',
                            name: 'spacer',
                            type: 'textbox',
                            value: '',
                        },
                    ],
                    onclick: function ( event ) {
                        // Get attributes
                        var attributes   = event.control.settings;
                        var window_title = !_.isUndefined( attributes.name ) ? attributes.name : 'Add shortcode';

                        // Modal
                        editor.windowManager.open(
                            {
                                title: window_title,
                                body: attributes.body,
                                onsubmit: function ( event ) {
                                    editor.insertContent( build_shortcode( event, attributes ) );
                                },
                            },
                        );
                    },
                };

                // Button group shortcode
                var pip_button_group = {
                    text: 'Button group',
                    tag: 'pip_button_group',
                    name: 'Add button group',
                    inside: 'pip_button',
                    body: [
                        {
                            label: 'Number of buttons',
                            name: 'number',
                            type: 'listbox',
                            values: [
                                { text: '2', value: 2 },
                                { text: '3', value: 3 },
                                { text: '4', value: 4 },
                                { text: '5', value: 5 },
                                { text: '6', value: 6 },
                            ],
                        },
                        {
                            label: 'Alignment',
                            name: 'alignment',
                            type: 'listbox',
                            values: [
                                { text: 'Left', value: 'text-left' },
                                { text: 'Center', value: 'text-center' },
                                { text: 'Right', value: 'text-right' },
                            ],
                        },
                    ],
                    onclick: function ( event ) {
                        // Get attributes
                        var attributes   = event.control.settings;
                        var window_title = !_.isUndefined( attributes.name ) ? attributes.name : 'Add shortcode';

                        // Modal
                        editor.windowManager.open(
                            {
                                title: window_title,
                                body: attributes.body,
                                onsubmit: function ( event ) {
                                    editor.insertContent( build_btn_group_shortcode( event, attributes ) );
                                },
                            },
                        );
                    },
                };

                // Add shortcode menu list
                var pip_shortcodes_menu_items = [
                    pip_field,
                    pip_breadcrumb,
                    pip_button,
                    pip_button_group,
                    pip_spacer,
                    pip_title,
                    pip_thumbnail,
                ];

                // Add filter to allow 3rd party to add their own shortcodes
                pip_shortcodes_menu_items = acf.applyFilters( 'pip/tinymce/shortcodes', pip_shortcodes_menu_items, event, editor );

                editor.addButton(
                    'pip_shortcodes',
                    function () {
                        return {
                            type: 'menubutton',
                            text: 'Shortcodes',
                            tooltip: 'Shortcodes',
                            menu: pip_shortcodes_menu_items,
                            fixedWidth: true,
                        };
                    },
                );

                // Register button view
                window.wp.mce.views.register(
                    'pip_button',
                    {

                        initialize: function () {
                            // Get attributes
                            var button       = get_button_attributes( this.text );
                            var btn_disabled = '';

                            // Build button class
                            var btn_class = '';
                            if ( button.type ) {
                                btn_class += button.type;
                            }
                            if ( button.xclass ) {
                                btn_class += ' ' + button.xclass;
                            }

                            // Build button
                            var html = '';
                            if ( button.text ) {
                                if ( !button.nodiv ) {
                                    html = '<div class="' + button.alignment + '">';
                                }
                                console.log( button.text );

                                var download_option = '';
                                if ( button.download ) {
                                    button.download_name = button.download_name ? button.download_name : 'download';
                                    download_option      = 'download="' + button.download_name + '"';
                                }

                                html += '<a href="' + button.link + '" target="' + button.target + '" class="' + _.escape( btn_class ) + '" ' + btn_disabled + ' ' + download_option + '>';
                                html += button.text + '</a>';

                                if ( !button.nodiv ) {
                                    html += '</div>';
                                }
                            }

                            // Render button
                            this.render( html );
                        },

                        edit: function ( text, update ) {
                            // Get current button values from shortcode text
                            var button = get_button_attributes( text );

                            // Update body to show current values
                            $.each(
                                button,
                                function ( button_key, button_value ) {
                                    // If undefined, skip
                                    if ( _.isUndefined( button_value ) ) {
                                        return true;
                                    }

                                    // Update value
                                    $.each(
                                        pip_button.body,
                                        function ( key, item ) {
                                            if ( item.name === button_key ) {
                                                if ( item.type === 'checkbox' ) {
                                                    if ( button_value !== 'true' ) {
                                                        button_value = '';
                                                    }
                                                    item.checked = button_value;
                                                } else {
                                                    item.value = button_value;
                                                }
                                            }
                                        },
                                    );

                                },
                            );

                            // Modal
                            editor.windowManager.open(
                                {
                                    title: 'Edit button',
                                    body: pip_button.body,
                                    onsubmit: function ( event ) {
                                        update( build_shortcode( event, pip_button ) );
                                    },
                                },
                            );
                        },

                    },
                );


                // Register breadcrumb view
                window.wp.mce.views.register(
                    'pip_breadcrumb',
                    {
                        initialize: function () {
                            this.render( 'You > Are > Here' );
                        },
                        edit: function ( text, update ) {
                            editor.windowManager.open(
                                {
                                    title: 'Breadcrumb',
                                    body: [
                                        {
                                            name: 'Breadcrumb',
                                            type: 'container',
                                            html: '<p>Breadcrumb is auto-generated by Yoast SEO.</p><p>Modify Yoast SEO configurations to change breadcrumb display.</p>',
                                        },
                                    ],
                                },
                            );
                        },
                    },
                );

                // Register ACF field view
                window.wp.mce.views.register(
                    'acf',
                    {

                        initialize: function () {
                            // Get ACF field value
                            var field_name = getAttr( this.text, 'field' );
                            var post_id    = getAttr( this.text, 'post_id' );

                            // Render button
                            this.render( 'Field "' + field_name + '" in post ' + post_id );
                        },

                        edit: function ( text, update ) {
                            // Get current ACF field name from shortcode text
                            var field_name = getAttr( this.text, 'field' );

                            // Update value
                            $.each(
                                pip_field.body,
                                function ( key, item ) {
                                    if ( item.name === 'field' ) {
                                        item.value = field_name;
                                    }
                                },
                            );

                            // Modal
                            editor.windowManager.open(
                                {
                                    title: 'Edit field',
                                    body: pip_field.body,
                                    onsubmit: function ( event ) {
                                        update( build_shortcode( event, pip_field ) );
                                    },
                                },
                            );
                        },

                    },
                );

                // Register thumbnail view
                window.wp.mce.views.register(
                    'pip_thumbnail',
                    {

                        initialize: function () {
                            // Get size
                            var size       = getAttr( this.text, 'size' );
                            var image_size = acf.get( 'image_sizes' )[size];

                            // Custom style
                            var p_css   = 'vertical-align: middle;';
                            var div_css = 'width: ' + image_size.width + 'px;';

                            div_css += 'height: ' + image_size.height + 'px;';
                            div_css += 'line-height: ' + image_size.height + 'px;';
                            div_css += 'background-color: #F4F4F4;';
                            div_css += 'text-align: center;';
                            div_css += 'border: 1px solid #000;';

                            // Render button
                            this.render( '<div style="' + div_css + '"><p style="' + p_css + '">' + image_size.width + ' x ' + image_size.height + '</p></div>' );
                        },

                        edit: function ( text, update ) {
                            // Get current size from shortcode text
                            var size = getAttr( this.text, 'size' );

                            // Update value
                            $.each(
                                pip_thumbnail.body,
                                function ( key, item ) {
                                    if ( item.name === 'size' ) {
                                        item.value = size;
                                    }
                                },
                            );

                            // Modal
                            editor.windowManager.open(
                                {
                                    title: 'Edit thumbnail',
                                    body: pip_thumbnail.body,
                                    onsubmit: function ( event ) {
                                        update( build_shortcode( event, pip_thumbnail ) );
                                    },
                                },
                            );
                        },

                    },
                );

                // Register spacer view
                window.wp.mce.views.register(
                    'pip_spacer',
                    {

                        initialize: function () {
                            // Get size
                            var spacer = getAttr( this.text, 'spacer' );

                            // Render button
                            this.render( '<div class="' + spacer + ' text-center"><span> - spacer (' + spacer + ') - </span></div>' );
                        },

                        edit: function ( text, update ) {
                            // Get current spacer from shortcode text
                            var spacer = getAttr( this.text, 'spacer' );

                            // Update value
                            $.each(
                                pip_spacer.body,
                                function ( key, item ) {
                                    if ( item.name === 'spacer' ) {
                                        item.value = spacer;
                                    }
                                },
                            );

                            // Modal
                            editor.windowManager.open(
                                {
                                    title: 'Edit spacer',
                                    body: pip_spacer.body,
                                    onsubmit: function ( event ) {
                                        update( build_shortcode( event, pip_spacer ) );
                                    },
                                },
                            );
                        },

                    },
                );

            },
        );

        /**
         * Get button attributes from shortcode text
         *
         * @param item
         *
         * @returns {{}}
         */
        var get_button_attributes = function ( item ) {
            var button = {};

            button.text          = getAttr( item, 'text' );
            button.type          = getAttr( item, 'type' );
            button.alignment     = getAttr( item, 'alignment' );
            button.xclass        = getAttr( item, 'xclass' );
            button.link          = getAttr( item, 'link' );
            button.target        = getAttr( item, 'target' );
            button.download      = getAttr( item, 'download' );
            button.download_name = getAttr( item, 'download_name' );
            button.nodiv         = getAttr( item, 'nodiv' );

            return button;
        };

        /**
         * Build shortcode
         *
         * @param event
         *
         * @param attributes
         * @returns {string}
         */
        var build_shortcode = function ( event, attributes ) {
            // Open shortcode
            var out = '[' + attributes.tag;

            // Add attributes to shortcode
            $.each(
                event.data,
                function ( key, value ) {
                    if ( value === false ) {
                        value = '';
                    }
                    out += ' ' + key + '="' + value + '"';
                },
            );

            // Close shortcode
            out += ']';

            return out;
        };

        /**
         * Build button group shortcode
         *
         * @param event
         *
         * @param attributes
         * @returns {string}
         */
        var build_btn_group_shortcode = function ( event, attributes ) {
            var i;
            var nb_buttons = 0;

            // Open shortcode
            var out = '[' + attributes.tag;

            // Add attributes to shortcode
            $.each(
                event.data,
                function ( key, value ) {
                    if ( value === false ) {
                        value = '';
                    }
                    if ( key === 'number' ) {
                        nb_buttons = parseInt( value );
                        return;
                    }
                    out += ' ' + key + '="' + value + '"';
                },
            );
            out += ']';

            // Add buttons with default values
            for ( i = 0; i < nb_buttons; i ++ ) {
                out += '[' + attributes.inside + ' text="Button" type="" alignment="text-left" target="_self" xclass="mr-2" nodiv="true"]';
            }

            // Close shortcode
            out += '[/' + attributes.tag + ']';

            return out;
        };

        /**
         * Get attribute from shortcode text
         *
         * @param str
         *
         * @param name
         * @returns {string}
         */
        var getAttr = function ( str, name ) {
            name = new RegExp( name + '=\"([^\"]+)\"' ).exec( str );
            return name ? window.decodeURIComponent( name[1] ) : '';
        };

    }
)( jQuery );
