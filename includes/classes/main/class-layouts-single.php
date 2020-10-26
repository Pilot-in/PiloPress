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

            // Hooks
            add_action( 'init', array( $this, 'include_layout_configuration' ) );
            add_action( 'current_screen', array( $this, 'current_screen' ) );

        }

        /**
         * Current Screen
         */
        public function current_screen() {

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

            add_filter( 'acf/validate_field_group', array( $this, 'validate_single' ), 20 );
            add_action( 'acf/update_field_group', array( $this, 'update_field_group' ) );
            add_action( 'acf/field_group/admin_head', array( $this, 'metaboxes' ) );
            add_filter( 'get_user_option_meta-box-order_acf-field-group', array( $this, 'metabox_order' ) );
            add_action( 'auto-draft_to_publish', array( $this, 'draft_to_publish' ) );
            add_action( 'untrashed_post', array( $this, 'untrash' ), 1 );

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

            $slug = $field_group['_pip_layout_slug'];

            $other_layouts = $this->get_layouts_with_same_slug( $slug, $field_group );

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
                        'small'  => 'Small',
                        'medium' => 'Medium',
                        'large'  => 'Large',
                        'xlarge' => 'Extra Large',
                        'full'   => 'Full',
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

            // Layout settings
            acf_render_field_wrap(
                array(
                    'label' => 'Settings',
                    'type'  => 'tab',
                    'name'  => 'tab_more',
                )
            );

            // Script for admin style
            ?>
            <script type="text/javascript">
                if ( typeof acf !== 'undefined' ) {
                    acf.postbox.render( {
                        'id': 'pip_layout_settings',
                        'label': 'left'
                    } )
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

            // Thumbnail
            acf_render_field_wrap(
                array(
                    'label'         => __( 'Thumbnail', 'pilopress' ),
                    'instructions'  => __( 'Layout preview', 'pilopress' ),
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
                    acf.postbox.render( {
                        'id': 'pip_layout_thumbnail',
                        'label': 'top'
                    } )
                }
            </script>
            <?php
        }

        /**
         * Draft to Publish
         *
         * @param $post
         */
        public function draft_to_publish( $post ) {

            add_action( 'wp_insert_post', array( $this, 'insert_post' ), 20, 3 );

        }

        /**
         * Insert Post
         *
         * @param $post_id
         * @param $post
         * @param $update
         */
        public function insert_post( $post_id, $post, $update ) {

            // Fire only once
            if ( !$post->post_content ) {
                return;
            }

            $field_group = acf_get_field_group( $post_id );

            // Bail early if field group not found
            if ( !$field_group ) {
                return;
            }

            // Create layout dans files
            $this->generate_directory_files( $field_group );

        }

        /**
         * Create layout folder with corresponding files
         *
         * @param $field_group
         */
        public function generate_directory_files( $field_group ) {

            $layout_title = sanitize_title( $field_group['_pip_layout_slug'] );

            // Bail early if folder already exists
            if ( file_exists( PIP_THEME_LAYOUTS_PATH . $layout_title ) ) {
                return;
            }

            // Create folder
            wp_mkdir_p( PIP_THEME_LAYOUTS_PATH . $layout_title );

            // Create file
            $file_name = acf_maybe_get( $field_group, '_pip_render_layout', $layout_title . '.php' );

            touch( PIP_THEME_LAYOUTS_PATH . $layout_title . '/' . $file_name );

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

                // If no file name or file doesn't exists, skip
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

    }

    acf_new_instance( 'PIP_Layouts_Single' );

}
