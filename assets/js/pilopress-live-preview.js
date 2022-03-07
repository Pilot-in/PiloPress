jQuery( document ).ready(
    function ( $ ) {

        let $typo_classes           = $( '.acf-field-typography-classes' );
        let $simple_color_value     = $( '.acf-field-simple-color-value' );
        let $colors_shades_repeater = $( '.acf-field-colors-shades-shades' );
        let $buttons_wrapper        = $( '.acf-field-pip-button' );
        let $fonts_wrapper          = $( '.acf-field-pip-fonts' );

        // Load previews on page load
        load_previews();

        // Change previews on typing
        previews_on_typing();

        if ( typeof acf !== 'undefined' ) {
            // When a new row is added
            acf.addAction(
                'append',
                function ( $row ) {
                    if ( !$( '.acf-field-typography-message' ).hasClass( 'acf-hidden' ) ) {

                        // Typography
                        $row.find( '.acf-field-typography-classes textarea' ).keyup(
                            function () {
                                apply_styles_to_preview( $( this ), '.acf-field-typography-preview' );
                            },
                        );

                    } else if ( !$( '.acf-field-font-color-message' ).hasClass( 'acf-hidden' ) ) {

                        // Simple colors
                        $row.find( '.acf-field-simple-color-value input[type="text"]' ).keyup(
                            function () {
                                add_css_to_preview( $( this ), '.acf-field-pip-simple-colors-preview', 'background-color' );
                            },
                        );

                        // Colors with shades
                        $row.find( '.acf-field-shade-value input[type="text"]' ).keyup(
                            function () {
                                let rows = $( this ).parents( '.acf-row' );
                                add_css_to_preview_repeater( $( rows[0] ), '.acf-field-colors-shades-shades-preview', 'background-color', $( this ).val() );
                            },
                        );

                    } else if ( !$( '.acf-field-button-message' ).hasClass( 'acf-hidden' ) ) {

                        // Buttons - Main textarea
                        $row.find( '.acf-field-custom-button-classes textarea' ).keyup(
                            function () {
                                let rows = $( this ).parents( '.acf-row' );
                                apply_btn_styles_to_preview( $( rows[0] ), '.acf-field-pip-button-preview', $( this ).val() );
                            },
                        );

                        // Buttons - Every state input
                        $row.find( '.acf-field-custom-button-states .acf-field-state-classes-to-apply input[type="text"]' ).keyup(
                            function () {
                                let rows = $( this ).parents( '.acf-row' );
                                apply_btn_styles_to_preview( $( rows[( rows.length - 1 )] ), '.acf-field-pip-button-preview' );
                            },
                        );

                    }
                },
            );

            // PILO_TODO: update preview when a row is removed
            // When a row is removed
            /*acf.add_action(
             'remove',
             function ( $row ) {

             if ( !$( '.acf-field-button-message' ).hasClass( 'acf-hidden' ) ) {

             let rows = $row.parents( '.acf-row' );
             apply_btn_styles_to_preview( $( rows[0] ), '.acf-field-pip-button-preview' );

             }
             },
             );*/
        }

        /**
         * All actions to load previews on page load
         */
        function load_previews() {
            // Typography
            $typo_classes.each(
                function () {
                    apply_styles_to_preview( $( this ).find( 'textarea' ), '.acf-field-typography-preview' );
                },
            );

            // Simple colors
            $simple_color_value.each(
                function () {
                    add_css_to_preview( $( this ).find( 'input[type="text"]' ), '.acf-field-pip-simple-colors-preview', 'background-color' );
                },
            );

            // Colors with shades
            $colors_shades_repeater.each(
                function () {
                    $( this ).find( '.acf-row' ).each(
                        function () {
                            add_css_to_preview_repeater( $( this ), '.acf-field-colors-shades-shades-preview', 'background-color', $( this ).find( '.acf-field-shade-value input[type="text"]' ).val() );
                        },
                    );
                },
            );

            // Buttons
            $buttons_wrapper.each(
                function () {
                    $( this ).find( '.acf-row' ).each(
                        function () {
                            apply_btn_styles_to_preview( $( this ), '.acf-field-pip-button-preview' );
                        },
                    );
                },
            );

            // Fonts
            $fonts_wrapper.each(
                function () {
                    $( this ).find( '.layout' ).each(
                        function () {
                            // External fonts
                            let external_font_name = $( this ).find( '.acf-field-google-font-class-name input[type="text"]' ).val();
                            $( this ).find( '.acf-field-google-font-preview .pip-live-preview > div' ).addClass( 'font-' + external_font_name );

                            // Custom fonts
                            let custom_font_name = $( this ).find( '.acf-field-custom-font-class-name input[type="text"]' ).val();
                            $( this ).find( '.acf-field-custom-font-preview .pip-live-preview > div' ).addClass( 'font-' + custom_font_name );
                        },
                    );
                },
            );
        }

        /**
         * All actions to change previews on typing
         */
        function previews_on_typing() {
            // Typography
            $typo_classes.find( 'textarea' ).keyup(
                function () {
                    apply_styles_to_preview( $( this ), '.acf-field-typography-preview' );
                },
            );

            // Simple colors
            $simple_color_value.find( 'input[type="text"]' ).keyup(
                function () {
                    add_css_to_preview( $( this ), '.acf-field-pip-simple-colors-preview', 'background-color' );
                },
            );

            // Colors with shades
            $colors_shades_repeater.find( '.acf-row .acf-field-shade-value input[type="text"]' ).keyup(
                function () {
                    let rows = $( this ).parents( '.acf-row' );
                    add_css_to_preview_repeater( $( rows[0] ), '.acf-field-colors-shades-shades-preview', 'background-color', $( this ).val() );
                },
            );

            // Buttons - Main textarea
            $buttons_wrapper.find( '.acf-row .acf-field-custom-button-classes textarea' ).keyup(
                function () {
                    let rows = $( this ).parents( '.acf-row' );
                    apply_btn_styles_to_preview( $( rows[0] ), '.acf-field-pip-button-preview', $( this ).val() );
                },
            );

            // Buttons - Every state select
            $buttons_wrapper.find( '.acf-row .acf-field-custom-button-states .acf-field-state-type select' ).change(
                function () {
                    let rows = $( this ).parents( '.acf-row' );
                    apply_btn_styles_to_preview( $( rows[( rows.length - 1 )] ), '.acf-field-pip-button-preview' );
                },
            );

            // Buttons - Every state input
            $buttons_wrapper.find( '.acf-row .acf-field-custom-button-states .acf-field-state-classes-to-apply input[type="text"]' ).keyup(
                function () {
                    let rows = $( this ).parents( '.acf-row' );
                    apply_btn_styles_to_preview( $( rows[( rows.length - 1 )] ), '.acf-field-pip-button-preview' );
                },
            );
        }

        /**
         * Change classes for live preview
         *
         * @param $input
         * @param wrapper
         */
        function apply_styles_to_preview( $input, wrapper ) {
            $input.parents( '.acf-row' ).find( wrapper ).find( '.pip-live-preview > div' ).removeClass().addClass( $input.val() );
        }

        /**
         * Change buttons classes for live preview
         *
         * @param $acf_row
         * @param wrapper
         */
        function apply_btn_styles_to_preview( $acf_row, wrapper ) {
            let classes       = $acf_row.find( '.acf-field-custom-button-classes textarea' ).val();
            let $live_preview = $acf_row.find( wrapper );
            $live_preview.find( '.pip-live-preview > button' ).removeClass();

            // Get states classes
            $acf_row.find( '.acf-field-custom-button-states' ).each(
                function () {
                    $( this ).find( '.acf-row' ).each(
                        function () {
                            let state_name  = $( this ).find( '.acf-field-state-type' ).find( 'select' );
                            let state_value = $( this ).find( '.acf-field-state-classes-to-apply' ).find( 'input[type="text"]' );

                            if ( state_name.val() && state_value.val() ) {
                                let state_values = state_value.val().split( ' ' );

                                $.each(
                                    state_values,
                                    function ( key, item ) {
                                        classes += ' ' + state_name.val() + ':' + item;
                                    },
                                );
                            }
                        },
                    );
                },
            );

            $live_preview.find( '.pip-live-preview > button' ).addClass( classes );
        }

        /**
         * Add CSS for live preview
         *
         * @param $input
         * @param wrapper
         * @param property
         */
        function add_css_to_preview( $input, wrapper, property ) {
            $input.parents( '.acf-row' ).find( wrapper ).find( '.pip-live-preview > div' ).css( property, $input.val() );
        }

        /**
         * Add CSS for live preview with repeater fields
         *
         * @param $input
         * @param wrapper
         * @param property
         * @param value
         */
        function add_css_to_preview_repeater( $input, wrapper, property, value ) {
            $input.find( wrapper ).find( '.pip-live-preview > div' ).css( property, value );
        }
    },
);
