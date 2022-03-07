(
    function ( $ ) {
        'use strict';

        // The global pip object
        let pip = {};

        // Set as a browser global
        window.pip = pip;

        /**
         * Layout settings: Move default settings
         *
         * @type {acf.Model}
         */
        if ( typeof acf !== 'undefined' ) {
            const move_default_settings = new acf.Model(
                {

                    actions: {
                        'new_field': 'onNewField',
                    },

                    onNewField: function ( field ) {

                        // 'acfe_form', 'label_placement', 'instruction_placement', 'description'
                        const name = field.get( 'name' );

                        if ( name === 'acfe_form' || name === 'label_placement' || name === 'instruction_placement' || name === 'description' ) {

                            const $sibling = $( '[data-name="tab_more"]' ).first();

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
                        const LayoutSettingsPostBox = acf.getPostbox( 'acf-field-group-options' );

                        if ( typeof LayoutSettingsPostBox !== 'undefined' && $( '#pip_layout_settings' ).length ) {
                            LayoutSettingsPostBox.hide();
                        }
                    }

                    // Layout admin page
                    const $title          = $( '#title' );
                    const $prepend        = $( '.acf-input-prepend span' );
                    const $layoutSlug     = $( '#acf_field_group-_pip_layout_slug' );
                    const $layoutTemplate = $( '#acf_field_group-_pip_render_layout' );
                    const $renderCSS      = $( '#acf_field_group-_pip_render_style' );
                    const $renderScript   = $( '#acf_field_group-_pip_render_script' );
                    const $configFile     = $( '#acf_field_group-_pip_config_file' );
                    let templateSwitch    = false;
                    let cssSwitch         = false;
                    let scriptSwitch      = false;
                    let configSwitch      = false;

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
                            const $this = $( this );

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
                            const $this = $( this );

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
                const searchParams = new URLSearchParams( window.location.search );
                if ( $( 'body' ).hasClass( 'wp-admin', 'post-type-acf-field-group' ) && searchParams.get( 'layouts' ) === '1' ) {
                    $( '.subsubsub li:last-child:not([class])' ).remove();
                }

                /**
                 * Search bar for layout modal
                 */
                let $addLayoutBtn = $( '.acfe-flexible-stylised-button .acf-actions > a.acf-button.button[data-name="add-layout"]' );

                /**
                 * Add search input
                 */
                if ( $addLayoutBtn.length === 1 ) {
                    $( document ).on(
                        'click',
                        $addLayoutBtn,
                        function () {
                            let $searchInput    = $( '#search-layout' );
                            let $acfeModalTitle = $( '.acfe-modal-select-pip_flexible .acfe-modal-wrapper .acfe-modal-title' );

                            // Return if element exist or modal don't exist
                            if ( $searchInput.length === 1 && $acfeModalTitle.length === 1 ) {
                                return;
                            }

                            // Append search input
                            $acfeModalTitle.append( '<input id="search-layout" type="text" placeholder="' + acf.__( 'Search for a layout' ) + '" style="margin-left:16px;"/>' );
                        },
                    );
                }

                /**
                 * Make layout search work
                 */
                $( document ).on(
                    'keyup',
                    '#search-layout',
                    function () {
                        let $this = $( this );
                        let value = $this.val().toLowerCase();
                        $( '.acfe-flex-thumbnails ul li' ).filter(
                            function () {
                                $( this ).toggle( $( this ).text().toLowerCase().indexOf( value ) > - 1 );
                            },
                        );
                    },
                );

                // Return if no flexible
                let $hasFlexible = $( '#acf-group_pip_flexible_main' );
                if ( !$hasFlexible ) {
                    return;
                }

                // Show more actions above layouts
                $( document ).on(
                    'mouseenter',
                    'a[data-name=more-actions]',
                    function () {
                        let $parent = $( this ).closest( '.acf-fc-layout-controls' )[0];
                        $( $parent ).addClass( 'show-more-actions' );
                    },
                );

                // Hide actions above layouts
                $( document ).on(
                    'mouseleave',
                    '.acf-fc-layout-controls',
                    function () {
                        $( this ).removeClass( 'show-more-actions' );
                    },
                );

                // Move layout up and/or down
                $( document ).on(
                    'click',
                    'a[data-name=move-pip-layout]',
                    function ( e ) {
                        e.preventDefault();

                        let $this   = $( this );
                        let $layout = $this.closest( '.layout' )[0];

                        if ( $this.hasClass( 'up' ) ) {
                            if ( $layout ) {
                                let prevLayoutId = $( $layout ).prev().data( 'id' );
                                if ( prevLayoutId ) {
                                    $( $layout ).insertBefore( $( '.layout[data-id="' + prevLayoutId + '"]' ) );
                                }
                            }
                        } else if ( $this.hasClass( 'down' ) ) {
                            if ( $layout ) {
                                let nextLayoutId = $( $layout ).next().data( 'id' );
                                if ( nextLayoutId ) {
                                    $( $layout ).insertAfter( $( '.layout[data-id="' + nextLayoutId + '"]' ) );
                                }
                            }
                        }
                    },
                );
            },
        );

        /**
         * Remove collection badge to avoid having it twice
         */
        $( document ).ajaxComplete(
            function () {
                $( '.acfe-layout-title .acfe-layout-title-text .pip_collection' ).remove();
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

        // Lock title input for Default content and Locked content
        $( 'body.post-type-pip-default-content #poststuff input[name="post_title"]' ).attr( 'disabled', 'disabled' );
        $( 'body.post-type-pip-locked-content #poststuff input[name="post_title"]' ).attr( 'disabled', 'disabled' );

    }
)( jQuery );
