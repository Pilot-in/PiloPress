(
    function ( $ ) {
        'use strict';

        // The global pip object
        var pip = {};

        // Set as a browser global
        window.pip = pip;

        /**
         * Layout settings: Move default settings
         *
         * @type {acf.Model}
         */
        if ( typeof acf !== 'undefined' ) {
            var move_default_settings = new acf.Model(
                {

                    actions: {
                        'new_field': 'onNewField',
                    },

                    onNewField: function ( field ) {

                        // 'acfe_form', 'label_placement', 'instruction_placement', 'description'
                        var name = field.get( 'name' );

                        if ( name === 'acfe_form' || name === 'label_placement' || name === 'instruction_placement' || name === 'description' ) {

                            var $sibling = $( '[data-name="tab_more"]' ).first();

                            // Bail early if no sibling
                            if ( !$sibling.length ) {
                                return;
                            }

                            $sibling.after( field.$el );

                        }
                    },
                },
            );

            acf.addAction(
                'prepare',
                function () {

                    if ( $.isFunction( acf.getPostbox ) ) {
                        // Hide default field group ACF settings
                        var LayoutSettingsPostBox = acf.getPostbox( 'acf-field-group-options' );

                        if ( typeof LayoutSettingsPostBox !== 'undefined' && $( '#pip_layout_settings' ).length ) {
                            LayoutSettingsPostBox.hide();
                        }
                    }

                    // Layout admin page
                    var $title          = $( '#title' );
                    var $prepend        = $( '.acf-input-prepend span' );
                    var $layoutSlug     = $( '#acf_field_group-_pip_layout_slug' );
                    var $layoutTemplate = $( '#acf_field_group-_pip_render_layout' );
                    var $renderCSS      = $( '#acf_field_group-_pip_render_style' );
                    var $renderScript   = $( '#acf_field_group-_pip_render_script' );
                    var $configFile     = $( '#acf_field_group-_pip_config_file' );
                    var templateSwitch  = false;
                    var cssSwitch       = false;
                    var scriptSwitch    = false;
                    var configSwitch    = false;

                    /**
                     * When something is typed in "template" field
                     */
                    $layoutTemplate.keyup(
                        function () {
                            templateSwitch = true;
                        },
                    );

                    /**
                     * When something is typed in "CSS" field
                     */
                    $renderCSS.keyup(
                        function () {
                            cssSwitch = true;
                        },
                    );

                    /**
                     * When something is typed in "script" field
                     */
                    $renderScript.keyup(
                        function () {
                            scriptSwitch = true;
                        },
                    );

                    /**
                     * When something is typed in "configuration" field
                     */
                    $configFile.keyup(
                        function () {
                            configSwitch = true;
                        },
                    );

                    /**
                     * When something is typed in "title" field
                     */
                    $title.keyup(
                        function () {
                            // Get title
                            var $this = $( this );

                            // If new layout
                            if ( $( '#auto_draft' ).val() === '1' ) {
                                // Change values with sanitized slug
                                change_values( $this );
                            }
                        },
                    );

                    /**
                     * When something is typed in "layout slug" field
                     */
                    $layoutSlug.keyup(
                        function () {
                            // Get layout slug
                            var $this = $( this );

                            // Change values with sanitized slug
                            change_values( $this );
                        },
                    );

                    /**
                     * Change input & span values
                     *
                     * @param $this
                     */
                    function change_values( $this ) {
                        $layoutSlug.val( pip.sanitize_title( $this.val() ) );
                        $prepend.html( pip.sanitize_title( $this.val().replace( /-$/, '' ) ) );

                        updateRenderSettings( $this.val() );

                        if ( !$this.val() ) {
                            $prepend.html( 'layout' );
                        }
                    }

                    /**
                     * Change render settings values
                     *
                     * @param val
                     */
                    function updateRenderSettings( val ) {
                        if ( !templateSwitch ) {
                            $layoutTemplate.val(
                                (
                                    pip.sanitize_title( val ) ? pip.sanitize_title( val ) : 'template'
                                ) + '.php',
                            );
                        }

                        if ( !cssSwitch ) {
                            $renderCSS.val(
                                (
                                    pip.sanitize_title( val ) ? pip.sanitize_title( val ) : 'style'
                                ) + '.css',
                            );
                        }

                        if ( !scriptSwitch ) {
                            $renderScript.val(
                                (
                                    pip.sanitize_title( val ) ? pip.sanitize_title( val ) : 'script'
                                ) + '.js',
                            );
                        }

                        if ( !configSwitch ) {
                            $configFile.val(
                                (
                                    pip.sanitize_title( val ) ? 'configuration-' + pip.sanitize_title( val ) : 'configuration'
                                ) + '.php',
                            );
                        }
                    }

                },
            );
        }

        $( document ).ready(
            function () {

                /**
                 * Remove search for layouts admin page
                 */
                var searchParams = new URLSearchParams( window.location.search );
                if ( $( 'body' ).hasClass( 'wp-admin', 'post-type-acf-field-group' ) && searchParams.get( 'layouts' ) === '1' ) {
                    $( '.subsubsub li:last-child:not([class])' ).remove();
                }
            },
        );

        /**
         * Sanitize value like WP function "sanitize_title"
         *
         * @param $val
         *
         * @returns {string}
         */
        pip.sanitize_title = function ( $val ) {
            return $val
                .toLowerCase()
                .replace( /\s+/g, '-' )               // Replace spaces with -
                .replace( /\-\-+/g, '-' )             // Replace multiple - with single -
                .replace( /\_\_+/g, '_' )             // Replace multiple _ with single _
                .replace( /^-+/, '' )                 // Trim - from start of text
                .normalize( 'NFD' )                                  // Change accent to unicode value
                .replace( /[\u0300-\u036f]/g, '' )    // From unicode value to letter
                .replace( /[^a-zA-Z0-9_\-\s]+/g, '' ); // Remove all non-word chars
        };

    }
)( jQuery );
