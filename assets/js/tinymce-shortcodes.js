(
    function ( $ ) {

        // Check if "acf" is available
        if ( typeof acf === 'undefined' ) {
            return;
        }

        const buttons            = acf.get( 'custom_buttons' );
        const get_custom_buttons = function () {
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
                const pip_button = {
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
                            label: 'Icon',
                            name: 'icon',
                            type: 'textbox',
                            value: '',
                        },
                        {
                            label: 'Icon position',
                            name: 'icon_position',
                            type: 'listbox',
                            values: [
                                { text: 'Left', value: 'left' },
                                { text: 'Right', value: 'right' },
                            ],
                        },
                        {
                            label: 'Button group',
                            name: 'nodiv',
                            type: 'checkbox',
                        },
                    ],
                    onclick: function ( event ) {
                        const attributes = event.control.settings;

                        // If no tag, return
                        if ( _.isUndefined( attributes.tag ) ) {
                            return;
                        }

                        // Get attributes
                        const window_title = !_.isUndefined( attributes.name ) ? attributes.name : 'Add shortcode';

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
                const pip_breadcrumb = {
                    text: 'Breadcrumb',
                    tag: 'pip_breadcrumb',
                    name: 'Add breadcrumb',
                    onclick: function () {
                        editor.insertContent( '[pip_breadcrumb]' );
                    },
                };

                // Title shortcode
                const pip_title = {
                    text: 'Title',
                    tag: 'pip_title',
                    name: 'Add title',
                    onclick: function () {
                        editor.insertContent( '[pip_title]' );
                    },
                };

                // ACF Field shortcode
                const pip_field = {
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
                        const attributes   = event.control.settings;
                        const window_title = !_.isUndefined( attributes.name ) ? attributes.name : 'Add shortcode';

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
                const pip_thumbnail = {
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
                        const attributes   = event.control.settings;
                        const window_title = !_.isUndefined( attributes.name ) ? attributes.name : 'Add shortcode';

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
                const pip_spacer = {
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
                        const attributes   = event.control.settings;
                        const window_title = !_.isUndefined( attributes.name ) ? attributes.name : 'Add shortcode';

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
                const pip_button_group = {
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
                        const attributes   = event.control.settings;
                        const window_title = !_.isUndefined( attributes.name ) ? attributes.name : 'Add shortcode';

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
                let pip_shortcodes_menu_items = [
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
                            const button     = get_button_attributes( this.text );
                            let btn_disabled = '';

                            // Build button class
                            let btn_class = '';
                            if ( button.type ) {
                                btn_class += button.type;
                            }
                            if ( button.xclass ) {
                                btn_class += ' ' + button.xclass;
                            }

                            // Build button
                            let html = '';
                            if ( button.text ) {
                                if ( !button.nodiv ) {
                                    html = '<div class="' + button.alignment + '">';
                                }

                                let download_option = '';
                                if ( button.download ) {
                                    button.download_name = button.download_name ? button.download_name : 'download';
                                    download_option      = 'download="' + button.download_name + '"';
                                }

                                html += '<a href="' + button.link + '" target="' + button.target + '" class="' + _.escape( btn_class ) + '" ' + btn_disabled + ' ' + download_option + '>';

                                if ( button.icon ) {

                                    if ( button.icon_position === 'left' ) {
                                        html += '<i class="' + button.icon + ' mr-2"></i>' + button.text + '</a>';
                                    } else if ( button.icon_position === 'right' ) {
                                        html += button.text + '<i class="' + button.icon + ' ml-2"></i></a>';
                                    }

                                } else {
                                    html += button.text + '</a>';
                                }

                                if ( !button.nodiv ) {
                                    html += '</div>';
                                }
                            }

                            // Render button
                            this.render( html );
                        },

                        edit: function ( text, update ) {
                            // Get current button values from shortcode text
                            const button = get_button_attributes( text );

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
                            let size       = getAttr( this.text, 'size' );
                            let image_size = size !== 'full' ? acf.get( 'image_sizes' )[size] : { width: 100, height: 'auto' };

                            // Custom style
                            const p_css = 'vertical-align: middle;';
                            let div_css = size !== 'full' ? 'width: ' + image_size.width + 'px;' : 'width: 100%;';

                            div_css += size !== 'full' ? 'height: ' + image_size.height + 'px;' : 'height: 50vh;';
                            div_css += size !== 'full' ? 'line-height: ' + image_size.height + 'px;' : 'line-height: 100%;';
                            div_css += 'display: flex; align-items: center; justify-content: center; text-align: center;';
                            div_css += 'color: #000; border: 1px solid #000; background-color: #F4F4F4;';

                            // Render button
                            this.render( '<div style="' + div_css + '"><p style="' + p_css + '">' + ( size !== 'full' ? image_size.width : '100%' ) + ' x ' + ( size !== 'full' ? image_size.height : 'auto' ) + '</p></div>' );
                        },

                        edit: function ( text, update ) {
                            // Get current size from shortcode text
                            const size = getAttr( this.text, 'size' );

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
                            const spacer = getAttr( this.text, 'spacer' );

                            // Render button
                            this.render( '<div class="' + spacer + ' text-center"><span> - spacer (' + spacer + ') - </span></div>' );
                        },

                        edit: function ( text, update ) {
                            // Get current spacer from shortcode text
                            const spacer = getAttr( this.text, 'spacer' );

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
        const get_button_attributes = function ( item ) {
            let button = {};

            button.text          = getAttr( item, 'text' );
            button.type          = getAttr( item, 'type' );
            button.alignment     = getAttr( item, 'alignment' );
            button.xclass        = getAttr( item, 'xclass' );
            button.link          = getAttr( item, 'link' );
            button.target        = getAttr( item, 'target' );
            button.download      = getAttr( item, 'download' );
            button.download_name = getAttr( item, 'download_name' );
            button.nodiv         = getAttr( item, 'nodiv' );
            button.icon          = getAttr( item, 'icon' );
            button.icon_position = getAttr( item, 'icon_position' );

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
        const build_shortcode = function ( event, attributes ) {
            // Open shortcode
            let out = '[' + attributes.tag;

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
        const build_btn_group_shortcode = function ( event, attributes ) {
            let i;
            let nb_buttons = 0;

            // Open shortcode
            let out = '[' + attributes.tag;

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
        const getAttr = function ( str, name ) {
            name = new RegExp( name + '=\"([^\"]+)\"' ).exec( str );
            return name ? window.decodeURIComponent( name[1] ) : '';
        };

    }
)( jQuery );
