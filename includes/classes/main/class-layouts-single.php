<?php

if ( !class_exists( 'PIP_Layouts_Single' ) ) {

    /**
     * Class PIP_Layouts_Single
     */
    class PIP_Layouts_Single {

        /**
         * PIP_Layouts_Single constructor.
         */
        public function __construct() {

            // WP hooks
            add_action( 'init', array( $this, 'include_layout_configuration' ), 6 );
            add_action( 'current_screen', array( $this, 'current_screen' ) );

            // ACF hooks
            add_action( 'acf/prepare_field/name=pip_layout_var', array( $this, 'fix_multiple_row_json_files' ) );

        }

        /**
         * Current Screen
         */
        public function current_screen() {

            // If not layout(s) screen, return
            if ( !pip_is_layout_screen() ) {
                return;
            }

            // Single
            add_action( 'load-post.php', array( $this, 'load_single' ) );
            add_action( 'load-post-new.php', array( $this, 'load_single' ) );

            // New
            add_action( 'load-post-new.php', array( $this, 'load_new' ) );

        }

        /**
         * Single
         */
        public function load_single() {

            // WP hooks
            add_filter( 'get_user_option_meta-box-order_acf-field-group', array( $this, 'metabox_order' ) );
            add_action( 'save_post', array( $this, 'save_post' ), 10, 3 );
            add_action( 'untrashed_post', array( $this, 'untrash' ), 1 );

            // ACF hooks
            add_filter( 'acf/validate_field_group', array( $this, 'validate_single' ), 20 );
            add_action( 'acf/update_field_group', array( $this, 'update_field_group' ) );
            add_action( 'acf/field_group/admin_head', array( $this, 'metaboxes' ) );

        }

        /**
         * New
         */
        public function load_new() {
            add_filter( 'acf/validate_field_group', array( $this, 'validate_new' ), 20 );
        }

        /**
         * Validate New Layout
         *
         * @param $field_group
         *
         * @return mixed
         */
        public function validate_new( $field_group ) {

            // Get Flexible Mirror
            $flexible_mirror = pip_get_flexible_mirror_group();

            // Force Location
            $field_group['location'] = $flexible_mirror['location'];

            return $field_group;
        }

        /**
         * Validate Single Layout
         *
         * @param $field_group
         *
         * @return mixed
         */
        public function validate_single( $field_group ) {

            // Force Disable
            $field_group['active'] = false;

            return $field_group;
        }

        /**
         * Make layout slug unique
         *
         * @param $field_group
         */
        public function update_field_group( $field_group ) {

            // Get slug
            $slug = $field_group['_pip_layout_slug'];

            // Get layouts with same slug
            $other_layouts = $this->get_layouts_with_same_slug( $slug, $field_group );

            // If no duplicated slug, return
            if ( empty( $other_layouts ) ) {
                return;
            }

            // Initialize suffix
            $suffix = 2;

            // Make unique layout slug
            do {
                // Build new slug
                $new_slug = _truncate_post_slug( $slug, 200 - ( strlen( $suffix ) + 1 ) ) . "-$suffix";

                // Check if layout exists with new slug
                $other_layouts = $this->get_layouts_with_same_slug( $new_slug, $field_group );

                // Increment suffix
                $suffix ++;

                // Do it again until no layout is find
            } while ( $other_layouts );

            // Replace file names with new slug
            $field_group['_pip_layout_slug']   = $new_slug;
            $field_group['_pip_render_layout'] = str_replace( $slug, $new_slug, $field_group['_pip_render_layout'] );
            $field_group['_pip_render_style']  = str_replace( $slug, $new_slug, $field_group['_pip_render_style'] );
            $field_group['_pip_render_script'] = str_replace( $slug, $new_slug, $field_group['_pip_render_script'] );

            // Update field group with new slug
            acf_update_field_group( $field_group );
        }

        /**
         * Layouts Meta Boxes
         */
        public function metaboxes() {

            // Get current field group
            global $field_group;

            // Meta box: Layout settings
            add_meta_box(
                'pip_layout_settings',
                __( 'Layout settings', 'pilopress' ),
                array( $this, 'render_meta_box_main' ),
                'acf-field-group',
                'normal',
                'high',
                array( 'field_group' => $field_group )
            );

            // Meta box: Thumbnail
            add_meta_box(
                'pip_layout_thumbnail',
                __( 'Layout thumbnail', 'pilopress' ),
                array( $this, 'render_meta_box_thumbnail' ),
                'acf-field-group',
                'side',
                'default',
                array( 'field_group' => $field_group )
            );

            // Remove ACF Extended: Field Group Category Metabox
            remove_meta_box( 'acf-field-group-categorydiv', 'acf-field-group', 'side' );

        }

        /**
         * Re-order meta-boxes
         *
         * @param $order
         *
         * @return array
         */
        public function metabox_order( $order ) {

            // Bail early if order already set by user
            if ( $order ) {
                return $order;
            }

            $order = array(

                // Normal
                'normal' => implode(
                    ',',
                    array(

                        // Layouts
                        'acf-field-group-fields',
                        'pip_layout_settings',
                        'acf-field-group-options',

                        // Flexible Mirror
                        'acf-field-group-locations',

                    )
                ),

                // Side
                'side'   => implode(
                    ',',
                    array(

                        // Layouts
                        'submitdiv',
                        'pip_layout_thumbnail',

                    )
                ),

            );

            return $order;
        }

        /**
         *  Meta box: Main
         *
         * @param $post
         * @param $meta_box
         */
        public function render_meta_box_main( $post, $meta_box ) {

            // Get field group
            $field_group = $meta_box['args']['field_group'];

            // Layout settings
            acf_render_field_wrap(
                array(
                    'label' => 'Layout',
                    'type'  => 'tab',
                )
            );

            // Layout settings
            acf_render_field_wrap(
                array(
                    'label'        => '',
                    'name'         => '_pip_is_layout',
                    'prefix'       => 'acf_field_group',
                    'type'         => 'acfe_hidden',
                    'instructions' => '',
                    'value'        => 1,
                    'required'     => false,
                )
            );

            // Layout
            $current_theme      = wp_get_theme();
            $layout_slug        = acf_maybe_get( $field_group, '_pip_layout_slug' ) ? sanitize_title( $field_group['_pip_layout_slug'] ) : 'layout';
            $layout_path_prefix = $current_theme->get_template() . '/pilopress/layouts/<span>' . $layout_slug . '</span>/';

            // Layout slug
            acf_render_field_wrap(
                array(
                    'label'        => __( 'Layout slug', 'pilopress' ),
                    'instructions' => __( 'Layout slug and layout folder name', 'pilopress' ),
                    'type'         => 'acfe_slug',
                    'name'         => '_pip_layout_slug',
                    'prefix'       => 'acf_field_group',
                    'placeholder'  => 'layout',
                    'required'     => 1,
                    'value'        => isset( $field_group['_pip_layout_slug'] ) ? $field_group['_pip_layout_slug'] : $layout_slug,
                )
            );

            // Layout template
            acf_render_field_wrap(
                array(
                    'label'         => __( 'Layout', 'pilopress' ),
                    'instructions'  => __( 'Layout file name', 'pilopress' ),
                    'type'          => 'text',
                    'name'          => '_pip_render_layout',
                    'prefix'        => 'acf_field_group',
                    'placeholder'   => 'template.php',
                    'default_value' => $layout_slug . '.php',
                    'prepend'       => $layout_path_prefix,
                    'required'      => 1,
                    'value'         => isset( $field_group['_pip_render_layout'] ) ? $field_group['_pip_render_layout'] : '',
                )
            );

            // Style - CSS
            acf_render_field_wrap(
                array(
                    'label'         => __( 'Style CSS', 'pilopress' ),
                    'instructions'  => __( 'CSS file name', 'pilopress' ),
                    'type'          => 'text',
                    'name'          => '_pip_render_style',
                    'prefix'        => 'acf_field_group',
                    'placeholder'   => 'style.css',
                    'default_value' => $layout_slug . '.css',
                    'prepend'       => $layout_path_prefix,
                    'value'         => isset( $field_group['_pip_render_style'] ) ? $field_group['_pip_render_style'] : '',
                )
            );

            // Script
            acf_render_field_wrap(
                array(
                    'label'         => __( 'Script', 'pilopress' ),
                    'instructions'  => __( 'JS file name', 'pilopress' ),
                    'type'          => 'text',
                    'name'          => '_pip_render_script',
                    'prefix'        => 'acf_field_group',
                    'placeholder'   => 'script.js',
                    'default_value' => $layout_slug . '.js',
                    'prepend'       => $layout_path_prefix,
                    'value'         => isset( $field_group['_pip_render_script'] ) ? $field_group['_pip_render_script'] : '',
                )
            );

            // Layout settings
            acf_render_field_wrap(
                array(
                    'label' => 'Configuration',
                    'type'  => 'tab',
                )
            );

            // Add configuration file ?
            acf_render_field_wrap(
                array(
                    'label'         => __( 'Configuration file', 'pilopress' ),
                    'instructions'  => '',
                    'key'           => 'field_add_config_file',
                    'type'          => 'true_false',
                    'name'          => '_pip_add_config_file',
                    'prefix'        => 'acf_field_group',
                    'value'         => ( isset( $field_group['field_add_config_file'] ) ? $field_group['field_add_config_file'] : '' ),
                    'default_value' => '',
                    'ui'            => 1,
                )
            );

            // Configuration file
            acf_render_field_wrap(
                array(
                    'label'             => __( 'Configuration file', 'pilopress' ),
                    'instructions'      => __( 'PHP file name', 'pilopress' ),
                    'type'              => 'text',
                    'name'              => '_pip_config_file',
                    'prefix'            => 'acf_field_group',
                    'placeholder'       => 'configuration.php',
                    'prepend'           => $layout_path_prefix,
                    'value'             => isset( $field_group['_pip_config_file'] ) ? $field_group['_pip_config_file'] : 'configuration-' . $layout_slug . '.php',
                    'conditional_logic' => array(
                        array(
                            array(
                                'field'    => 'field_add_config_file',
                                'operator' => '==',
                                'value'    => '1',
                            ),
                        ),
                    ),
                )
            );

            // Get layouts for configuration field
            $choices = array();

            acf_enable_local();

            $field_groups = acf_get_field_groups();

            acf_disable_local();

            if ( $field_groups ) {
                foreach ( $field_groups as $field_grp ) {
                    $choices[ $field_grp['key'] ] = $field_grp['title'];
                }
            }

            // Add configuration ?
            acf_render_field_wrap(
                array(
                    'label'         => __( 'Configuration modal', 'pilopress' ),
                    'instructions'  => '',
                    'key'           => 'field_add_configuration',
                    'type'          => 'true_false',
                    'name'          => '_pip_add_configuration',
                    'prefix'        => 'acf_field_group',
                    'value'         => ( isset( $field_group['field_add_configuration'] ) ? $field_group['field_add_configuration'] : '' ),
                    'default_value' => '',
                    'ui'            => 1,
                )
            );

            // Configuration
            acf_render_field_wrap(
                array(
                    'label'             => __( 'Configuration field group', 'pilopress' ),
                    'instructions'      => '',
                    'type'              => 'select',
                    'name'              => '_pip_configuration',
                    'prefix'            => 'acf_field_group',
                    'value'             => ( isset( $field_group['_pip_configuration'] ) ? $field_group['_pip_configuration'] : '' ),
                    'choices'           => $choices,
                    'allow_null'        => 1,
                    'multiple'          => 1,
                    'ui'                => 1,
                    'ajax'              => 0,
                    'return_format'     => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field'    => 'field_add_configuration',
                                'operator' => '==',
                                'value'    => '1',
                            ),
                        ),
                    ),
                )
            );

            // Modal size
            acf_render_field_wrap(
                array(
                    'label'             => __( 'Configuration modal size', 'pilopress' ),
                    'instructions'      => '',
                    'name'              => '_pip_modal_size',
                    'type'              => 'select',
                    'class'             => '',
                    'prefix'            => 'acf_field_group',
                    'value'             => ( isset( $field_group['_pip_modal_size'] ) ? $field_group['_pip_modal_size'] : 'medium' ),
                    'choices'           => array(
                        'small'  => __( 'Small', 'pilopress' ),
                        'medium' => __( 'Medium', 'pilopress' ),
                        'large'  => __( 'Large', 'pilopress' ),
                        'xlarge' => __( 'Extra Large', 'pilopress' ),
                        'full'   => __( 'Full', 'pilopress' ),
                    ),
                    'allow_null'        => 0,
                    'multiple'          => 0,
                    'ui'                => 1,
                    'ajax'              => 0,
                    'return_format'     => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field'    => 'field_add_configuration',
                                'operator' => '==',
                                'value'    => '1',
                            ),
                        ),
                    ),
                )
            );

            // Layout variables
            acf_render_field_wrap(
                array(
                    'label' => __( 'Variables', 'pilopress' ),
                    'type'  => 'tab',
                )
            );

            // Variables status
            $pip_layout_vars_lock = acf_maybe_get( $field_group, 'pip_layout_vars_lock', '' );
            if ( $pip_layout_vars_lock ) {

                // Lock repeater
                add_filter( 'acfe/repeater/remove_actions/name=pip_layout_var', '__return_true', 50 );

                // Lock variable names
                add_filter( 'acf/prepare_field/name=pip_layout_var_key', 'pip_layout_var_key_lock', 50, 2 );
                function pip_layout_var_key_lock( $field ) {
                    $field['readonly'] = 1;

                    return $field;
                }
            }

            // Layout style variables
            $pip_layout_var = acf_maybe_get( $field_group, 'pip_layout_var', array() );
            acf_render_field_wrap(
                array(
                    'label'                         => __( 'Style variables', 'pilopress' ),
                    'name'                          => 'pip_layout_var',
                    'key'                           => 'pip_layout_var',
                    'instructions'                  => __( 'Dynamic variables to use inside the layout.', 'pilopress' ),
                    'prefix'                        => 'acf_field_group',
                    'type'                          => 'repeater',
                    'button_label'                  => __( '+ Add variable', 'pilopress' ),
                    'acfe_repeater_stylised_button' => true,
                    'required'                      => false,
                    'layout'                        => 'table',
                    'value'                         => $pip_layout_var,
                    'wrapper'                       => array(),
                    'sub_fields'                    => array(
                        array(
                            'ID'           => false,
                            'label'        => __( 'Key', 'pilopress' ),
                            'name'         => 'pip_layout_var_key',
                            'key'          => 'pip_layout_var_key',
                            'prefix'       => '',
                            '_name'        => '',
                            '_prepare'     => '',
                            'type'         => 'text',
                            'placeholder'  => __( 'variable-key', 'pilopress' ),
                            'instructions' => false,
                            'required'     => false,
                            'wrapper'      => array(
                                'width' => '',
                                'class' => '',
                                'id'    => '',
                            ),
                        ),
                        array(
                            'ID'           => false,
                            'label'        => __( 'Value', 'pilopress' ),
                            'name'         => 'pip_layout_var_value',
                            'key'          => 'pip_layout_var_value',
                            'prefix'       => '',
                            '_name'        => '',
                            '_prepare'     => '',
                            'type'         => 'text',
                            'placeholder'  => __( 'example-class example-class-2', 'pilopress' ),
                            'instructions' => false,
                            'required'     => false,
                            'wrapper'      => array(
                                'width' => '',
                                'class' => '',
                                'id'    => '',
                            ),
                        ),
                    ),
                )
            );

            // Lock variables
            acf_render_field_wrap(
                array(
                    'label'         => __( 'Variables status', 'pilopress' ),
                    'instructions'  => __( "Lock variables when you're done adding it.", 'pilopress' ),
                    'type'          => 'true_false',
                    'key'           => 'pip_layout_vars_lock',
                    'name'          => 'pip_layout_vars_lock',
                    'prefix'        => 'acf_field_group',
                    'value'         => $pip_layout_vars_lock,
                    'default_value' => '',
                    'ui'            => 1,
                    'ui_on_text'    => __( 'Locked', 'pilopress' ),
                    'ui_off_text'   => __( 'Unlocked', 'pilopress' ),
                )
            );

            // Layout settings
            acf_render_field_wrap(
                array(
                    'label' => __( 'Settings', 'pilopress' ),
                    'type'  => 'tab',
                    'name'  => 'tab_more',
                )
            );

            // PILO_TODO: Uncomment when min and max validation will be fixed
            // Layout min
            //            acf_render_field_wrap(
            //                array(
            //                    'label'       => __( 'Min', 'pilopress' ),
            //                    'type'        => 'number',
            //                    'name'        => '_pip_layout_min',
            //                    'prefix'      => 'acf_field_group',
            //                    'placeholder' => '',
            //                    'required'    => 0,
            //                    'step'        => 1,
            //                    'min'         => '0',
            //                    'value'       => isset( $field_group['_pip_layout_min'] ) ? $field_group['_pip_layout_min'] : '',
            //                )
            //            );

            // Layout max
            //            acf_render_field_wrap(
            //                array(
            //                    'label'       => __( 'Max', 'pilopress' ),
            //                    'type'        => 'number',
            //                    'name'        => '_pip_layout_max',
            //                    'prefix'      => 'acf_field_group',
            //                    'placeholder' => '',
            //                    'required'    => 0,
            //                    'step'        => 1,
            //                    'min'         => '0',
            //                    'value'       => isset( $field_group['_pip_layout_max'] ) ? $field_group['_pip_layout_max'] : '',
            //                )
            //            );

            // Script for admin style
            ?>
            <script type="text/javascript">
                if ( typeof acf !== 'undefined' ) {
                    acf.postbox.render(
                        {
                            'id': 'pip_layout_settings',
                            'label': 'left',
                        },
                    );
                }
            </script>
            <?php
        }

        /**
         * Meta box: Thumbnail
         *
         * @param $post
         * @param $meta_box
         */
        public function render_meta_box_thumbnail( $post, $meta_box ) {

            // Get field group
            $field_group = $meta_box['args']['field_group'];

            // Get layout slug
            $layout_slug = acf_maybe_get( $field_group, '_pip_layout_slug' );

            // Check if layout has thumbnail added via UI
            $has_thumbnail = acf_maybe_get( $field_group, '_pip_thumbnail' );

            // Get layout thumbnail
            $layout_thumbnail = self::get_layout_thumbnail( $field_group );

            if ( $layout_thumbnail && !$has_thumbnail ) {
                // Add notice to show user that a thumbnail is already in layout folder
                $instructions_attrs['class'] = 'acf-js-tooltip';
                $instructions_attrs['title'] = __( 'Screenshot location:', 'pilopress' ) . '<br/>' . $layout_slug . '/' . $layout_slug . acf_maybe_get( $layout_thumbnail, 'extension' );
                $instructions                = '<span ' . acf_esc_atts( $instructions_attrs ) . '><span class="dashicons dashicons-yes" style="color:#46B450;"></span>' . __( 'Screenshot defined in layout folder', 'pilopress' ) . '</span>';

                // Add image preview with tooltip to see it in bigger size
                $instructions_attrs['class'] = 'acf-js-tooltip';
                $instructions_attrs['title'] = '<img alt="' . $layout_slug . '" src="' . acf_maybe_get( $layout_thumbnail, 'url' ) . '" width="auto" style="max-height:350px;">';

                $instructions .= '<span ' . acf_esc_atts( $instructions_attrs ) . '><img alt="' . $layout_slug . '" src="' . acf_maybe_get( $layout_thumbnail, 'url' ) . '" style="margin-top: 10px; object-fit: cover; object-position: center; width: 100%; height: auto;" width="100" height="100"></span>';
            } else {
                $instructions = __( 'Layout preview', 'pilopress' );
            }

            // Thumbnail
            acf_render_field_wrap(
                array(
                    'label'         => __( 'Thumbnail', 'pilopress' ),
                    'instructions'  => $instructions,
                    'name'          => '_pip_thumbnail',
                    'type'          => 'image',
                    'class'         => '',
                    'prefix'        => 'acf_field_group',
                    'value'         => ( isset( $field_group['_pip_thumbnail'] ) ? $field_group['_pip_thumbnail'] : '' ),
                    'return_format' => 'array',
                    'preview_size'  => 'thumbnail',
                    'library'       => 'all',
                )
            );

            // Script for admin style
            ?>
            <script type="text/javascript">
                if ( typeof acf !== 'undefined' ) {
                    acf.postbox.render(
                        {
                            'id': 'pip_layout_thumbnail',
                            'label': 'top',
                        },
                    );
                }
            </script>
            <?php
        }

        /**
         * Get layout thumbnail data
         *
         * @param $layout
         *
         * @return string[]|null
         */
        public static function get_layout_thumbnail( $layout ) {
            $pip_thumbnail = acf_maybe_get( $layout, '_pip_thumbnail' );
            $layout_slug   = acf_maybe_get( $layout, '_pip_layout_slug' );

            if ( $pip_thumbnail ) {
                $img_url = wp_get_attachment_image_url( $pip_thumbnail, 'large' );

                return array(
                    'url' => $img_url,
                );
            }

            // Get file path and URL
            $file_path = apply_filters( 'pip/layouts/thumbnail/file_path', PIP_THEME_LAYOUTS_PATH . $layout_slug . '/' . $layout_slug, $layout );
            $file_url  = apply_filters( 'pip/layouts/thumbnail/file_url', PIP_THEME_LAYOUTS_URL . $layout_slug . '/' . $layout_slug, $layout );

            // Get file extension
            $extension = null;
            switch ( $file_path ) {
                case file_exists( $file_path . '.png' ):
                    $extension = '.png';
                    break;
                case file_exists( $file_path . '.jpeg' ):
                    $extension = '.jpeg';
                    break;
                case file_exists( $file_path . '.jpg' ):
                    $extension = '.jpg';
                    break;
            }

            // No file or file in other format
            if ( !$file_url || !$extension ) {
                return null;
            }

            // Return URL and extension for tooltip
            return array(
                'url'       => $file_url . $extension,
                'extension' => $extension,
            );
        }

        /**
         * Save layout
         *
         * @param $post_id
         * @param $post
         * @param $update
         */
        public function save_post( $post_id, $post, $update ) {

            // Fire only once
            if ( !$post->post_content ) {
                return;
            }

            $field_group = acf_get_field_group( $post_id );

            // Bail early if field group not found
            if ( !$field_group ) {
                return;
            }

            // Create layout files
            $this->generate_directory_files( $field_group );
        }

        /**
         * Create layout folder with corresponding files
         *
         * @param $field_group
         */
        public function generate_directory_files( $field_group ) {

            $layout_title = sanitize_title( $field_group['_pip_layout_slug'] );

            // Create layout folder if doesn't exists
            if ( !file_exists( PIP_THEME_LAYOUTS_PATH . $layout_title ) ) {
                wp_mkdir_p( PIP_THEME_LAYOUTS_PATH . $layout_title );
            }

            // Add configuration file ?
            if ( acf_maybe_get( $field_group, 'field_add_config_file' ) ) {

                // Get file name
                $config_file_name = acf_maybe_get( $field_group, '_pip_config_file' );

                // If file doesn't already exists, create it
                if ( !file_exists( PIP_THEME_LAYOUTS_PATH . $layout_title . '/' . $config_file_name ) ) {
                    touch( PIP_THEME_LAYOUTS_PATH . $layout_title . '/' . $config_file_name );
                }
            }

            // Get template file name
            $file_name = acf_maybe_get( $field_group, '_pip_render_layout', $layout_title . '.php' );

            // Create template file if doesn't exists
            if ( !file_exists( PIP_THEME_LAYOUTS_PATH . $layout_title . '/' . $file_name ) ) {
                touch( PIP_THEME_LAYOUTS_PATH . $layout_title . '/' . $file_name );
            }
        }

        /**
         * Remove ACF action when un-trash layout field group
         *
         * @param $post_id
         */
        public function untrash( $post_id ) {
            //remove_action( 'acf/untrash_field_group', array( acf()->json, 'update_field_group' ) );
        }

        /**
         * Enqueue layouts configuration files
         */
        public function include_layout_configuration() {

            foreach ( pip_get_layouts() as $layout ) {

                // If no configuration file added, skip
                if ( !acf_maybe_get( $layout, 'field_add_config_file' ) ) {
                    continue;
                }

                // Get layout slug and file name
                $layout_slug      = acf_maybe_get( $layout, '_pip_layout_slug' );
                $config_file_name = acf_maybe_get( $layout, '_pip_config_file' );

                $file_path = PIP_THEME_LAYOUTS_PATH . $layout_slug . '/' . $config_file_name;

                // If no file name or file doesn't exist, skip
                if ( !$config_file_name || !file_exists( $file_path ) ) {
                    continue;
                }

                // Include PHP file
                include_once $file_path;

            }

        }

        /**
         * Get layouts with the same slug
         *
         * @param $slug
         * @param $field_group
         *
         * @return array
         */
        public function get_layouts_with_same_slug( $slug, $field_group ) {

            $other_layouts = array();

            foreach ( pip_get_layouts() as $layout ) {

                if ( $layout['ID'] === $field_group['ID'] || $layout['_pip_layout_slug'] !== $slug ) {
                    continue;
                }

                $other_layouts[] = $layout;
                break;
            }

            return $other_layouts;
        }

        /**
         * Get layout field group by slug
         *
         * @param $layout_slug
         *
         * @return false|mixed
         */
        public static function get_layout_field_group_by_slug( $layout_slug ) {
            $layout = false;

            // If no slug, return
            if ( !$layout_slug ) {
                return $layout;
            }

            // Get field groups
            $store        = acf_get_store( 'field-groups' );
            $field_groups = pip_maybe_get( $store, 'data' );
            if ( !$field_groups ) {
                return $layout;
            }

            // Browse field groups
            foreach ( $field_groups as $field_group ) {
                // Get layout slug
                $field_group_slug = acf_maybe_get( $field_group, '_pip_layout_slug' );

                // If requested field group, store and break loop
                if ( $field_group_slug === $layout_slug ) {
                    $layout = $field_group;
                    break;
                }
            }

            // Return field group or false
            return $layout;
        }

        /**
         * Leave only one "row-" in key for layout vars
         *
         * @param $field
         *
         * @return mixed
         */
        public function fix_multiple_row_json_files( $field ) {

            $values = $field['value'];

            // If no value, return
            if ( !$values ) {
                return $field;
            }

            // Browse all values to remove duplicated "row-" in keys
            $sanitized_values = array();
            foreach ( $values as $key => $value ) {
                $sanitized_values[ str_replace( 'row-', '', $key ) ] = $value;
            }

            // Replace values
            $field['value'] = $sanitized_values;

            return $field;
        }

    }

    acf_new_instance( 'PIP_Layouts_Single' );

}
