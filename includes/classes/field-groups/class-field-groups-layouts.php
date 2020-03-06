<?php

if ( !class_exists( 'PIP_Field_Groups_Layouts' ) ) {
    class PIP_Field_Groups_Layouts {
        private static $layout_group_keys = array();

        public function __construct() {
            // WP hooks
            add_action( 'current_screen', array( $this, 'current_screen' ) );
            add_action( 'register_post_type_args', array( $this, 'modify_acf_post_type' ), 10, 2 );

            // ACF hooks
            add_action( 'acf/import_field_group', array( $this, 'compile_on_sync' ) );

            // Create files and directory or rename directory
//            add_action( 'wp_insert_post', array( $this, 'save_field_group' ), 10, 3 );
        }

        /**
         * Fire actions on layouts pages
         */
        public function current_screen() {
            // If not ACF field group single, return
            if ( !acf_is_screen( 'acf-field-group' ) ) {
                return;
            }

            add_action( 'acf/field_group/admin_head', array( $this, 'layout_meta_boxes' ) );
            add_action( 'acf/input/admin_head', array( $this, 'layout_settings' ), 20 );
            add_action( 'acf/update_field_group', array( $this, 'set_field_group_to_inactive' ) );
        }

        /**
         * Force layout to be inactive
         *
         * @param $field_group
         */
        public function set_field_group_to_inactive( $field_group ) {
            // Is current post a layout ?
            $is_layout = self::is_layout( $field_group );
            if ( !$is_layout ) {
                return;
            }

            // Set active to false
            if ( $field_group['active'] ) {
                $field_group['active'] = 0;
                acf_update_field_group( $field_group );
            }

            // Compile SCSS
            PIP_Styles_Settings::compile_layouts_styles( $field_group['ID'] );
        }

        /**
         * Force layout to be inactive
         */
        public function layout_settings() {
            // Get current field group
            global $field_group;

            // Is current field group a layout ?
            $is_layout = self::is_layout( $field_group );
            if ( !$is_layout ) {
                return;
            }

            // Set active to false
            $field_group['active'] = 0;
        }

        /**
         * Change title on layouts pages
         *
         * @param $args
         * @param $post_type
         *
         * @return mixed
         */
        public function modify_acf_post_type( $args, $post_type ) {
            // If AJAX, not admin or not acf-field-group, return
            if ( wp_doing_ajax() || !is_admin() || $post_type !== 'acf-field-group' ) {
                return $args;
            }

            // Get current post
            $post = get_post( acf_maybe_get_GET( 'post' ) );

            // Layouts
            $is_layout = PIP_Field_Groups_Layouts::is_layout( $post );
            if ( acf_maybe_get_GET( 'layouts' ) == 1 || $is_layout || acf_maybe_get_GET( 'layout' ) == 1 ) {

                // Change title on layouts pages
                $args['labels']['name']         = __( 'Layouts', 'pilopress' );
                $args['labels']['edit_item']    = __( 'Edit Layout', 'pilopress' );
                $args['labels']['add_new_item'] = __( 'Add New Layout', 'pilopress' );
            }

            return $args;
        }

        /**
         * Pilo'Press meta boxes
         */
        public function layout_meta_boxes() {
            // Get current field group
            global $field_group;

            // If mirror flexible page, don't register meta boxes
            if ( $field_group['key'] === PIP_Field_Groups_Flexible_Mirror::get_flexible_mirror_group_key() ) {
                return;
            }

            // Is current field group a layout ?
            $is_layout = self::is_layout( $field_group );

            // Meta box: Layout settings
            if ( $is_layout ) {
                add_meta_box( 'pip_layout_settings', __( "Pilo'Press: Flexible Layout settings", 'pilopress' ), array(
                    $this,
                    'render_meta_box_main',
                ), 'acf-field-group', 'normal', 'high', array( 'field_group' => $field_group ) );
            }
        }

        /**
         * Manage layout directory and files on save
         *
         * @param int $post_id
         * @param WP_Post $post
         * @param bool $update
         *
         */
        public function save_field_group( $post_id, $post, $update ) {
            // If is a revision, not a field group or not a layout, return
            if ( wp_is_post_revision( $post_id )
                 || $post->post_status == 'draft'
                 || $post->post_status == 'auto-draft'
                 || $post->post_type !== 'acf-field-group'
                 || !PIP_Field_Groups_Layouts::is_layout( $post_id ) ) {
                return;
            }

            // Get old and new title
            $field_group = acf_get_field_group( $post_id );
            $old_title   = sanitize_title( $field_group['_pip_layout_slug'] );
            $data        = unserialize( $post->post_content );
            $new_title   = $data['_pip_layout_slug'];

            // Do layout directory already exists ?
            $directory_exists = file_exists( PIP_THEME_LAYOUTS_PATH . $old_title );

            if ( $old_title === $new_title && !$directory_exists ) {

                // If old and new title are the same, create new layout directory
                $this->create_layout_dir( $old_title, $field_group );
            } elseif ( $old_title !== $new_title && $directory_exists ) {

                // If old and new title aren't the same, change layout directory name
                $this->modify_layout_dir( $old_title, $new_title );
            }
        }

        /**
         *  Meta box: Main
         *
         * @param $post
         * @param $meta_box
         */
        public function render_meta_box_main( $post, $meta_box ) {
            $field_group = $meta_box['args']['field_group'];

            // Layout settings
            acf_render_field_wrap( array(
                'label'        => '',
                'name'         => '_pip_is_layout',
                'prefix'       => 'acf_field_group',
                'type'         => 'acfe_hidden',
                'instructions' => '',
                'value'        => 1,
                'required'     => false,
            ) );

            // Layout
            $layout_name        = sanitize_title( $field_group['title'] );
            $layout_slug        = acf_maybe_get( $field_group, '_pip_layout_slug' ) ? sanitize_title( $field_group['_pip_layout_slug'] ) : 'layout';
            $layout_path_prefix = str_replace( home_url() . '/wp-content/themes/', '', PIP_THEME_LAYOUTS_URL ) . '<span>' . $layout_slug . '</span>' . '/';

            // Layout slug
            acf_render_field_wrap( array(
                'label'        => __( 'Layout slug', 'pilopress' ),
                'instructions' => __( 'Layout name and layout directory name', 'pilopress' ),
                'type'         => 'acfe_slug',
                'name'         => '_pip_layout_slug',
                'prefix'       => 'acf_field_group',
                'placeholder'  => 'layout',
                'required'     => 1,
                'value'        => isset( $field_group['_pip_layout_slug'] ) ? $field_group['_pip_layout_slug'] : $layout_slug,
            ) );

            // Layout template
            acf_render_field_wrap( array(
                'label'         => __( 'Layout', 'pilopress' ),
                'instructions'  => __( 'Layout file name', 'pilopress' ),
                'type'          => 'text',
                'name'          => '_pip_render_layout',
                'prefix'        => 'acf_field_group',
                'placeholder'   => 'template.php',
                'default_value' => $layout_name . '.php',
                'prepend'       => $layout_path_prefix,
                'value'         => isset( $field_group['_pip_render_layout'] ) ? $field_group['_pip_render_layout'] : '',
            ) );

            // Style - CSS
            acf_render_field_wrap( array(
                'label'         => __( 'Style CSS', 'pilopress' ),
                'instructions'  => __( 'CSS file name', 'pilopress' ),
                'type'          => 'text',
                'name'          => '_pip_render_style',
                'prefix'        => 'acf_field_group',
                'placeholder'   => 'style.css',
                'default_value' => $layout_name . '.css',
                'prepend'       => $layout_path_prefix,
                'value'         => isset( $field_group['_pip_render_style'] ) ? $field_group['_pip_render_style'] : '',
            ) );

            // Style - SCSS
            acf_render_field_wrap( array(
                'label'         => __( 'Style SCSS', 'pilopress' ),
                'instructions'  => __( 'SCSS file name', 'pilopress' ),
                'type'          => 'text',
                'name'          => '_pip_render_style_scss',
                'prefix'        => 'acf_field_group',
                'placeholder'   => 'style.scss',
                'default_value' => $layout_name . '.scss',
                'prepend'       => $layout_path_prefix,
                'value'         => isset( $field_group['_pip_render_style_scss'] ) ? $field_group['_pip_render_style_scss'] : '',
            ) );

            // Script
            acf_render_field_wrap( array(
                'label'         => __( 'Script', 'pilopress' ),
                'instructions'  => __( 'JS file name', 'pilopress' ),
                'type'          => 'text',
                'name'          => '_pip_render_script',
                'prefix'        => 'acf_field_group',
                'placeholder'   => 'script.js',
                'default_value' => $layout_name . '.js',
                'prepend'       => $layout_path_prefix,
                'value'         => isset( $field_group['_pip_render_script'] ) ? $field_group['_pip_render_script'] : '',
            ) );

            // Get layouts for configuration field
            $choices = array();
            foreach ( PIP_Field_Groups_Layouts::get_layout_group_keys() as $layout_group_key ) {
                // Get current field group
                $group = acf_get_field_group( $layout_group_key );

                // Save title
                $choices[ $group['key'] ] = $group['title'];
            }

            // Configuration
            acf_render_field_wrap( array(
                'label'         => __( 'Configuration', 'pilopress' ),
                'instructions'  => __( 'Configuration clone', 'pilopress' ),
                'type'          => 'select',
                'name'          => '_pip_configuration',
                'prefix'        => 'acf_field_group',
                'value'         => ( isset( $field_group['_pip_configuration'] ) ? $field_group['_pip_configuration'] : '' ),
                'choices'       => $choices,
                'allow_null'    => 1,
                'multiple'      => 1,
                'ui'            => 1,
                'ajax'          => 0,
                'return_format' => 0,
            ) );

            // Modal size
            acf_render_field_wrap( array(
                'label'         => __( 'Modal size', 'pilopress' ),
                'instructions'  => __( 'Configuration modal size', 'pilopress' ),
                'name'          => '_pip_modal_size',
                'type'          => 'select',
                'class'         => '',
                'prefix'        => 'acf_field_group',
                'value'         => ( isset( $field_group['_pip_modal_size'] ) ? $field_group['_pip_modal_size'] : 'medium' ),
                'choices'       => array(
                    'small'  => 'Small',
                    'medium' => 'Medium',
                    'large'  => 'Large',
                    'xlarge' => 'Extra Large',
                    'full'   => 'Full',
                ),
                'allow_null'    => 0,
                'multiple'      => 0,
                'ui'            => 1,
                'ajax'          => 0,
                'return_format' => 0,
            ) );

            // Category
            acf_render_field_wrap( array(
                'label'         => __( 'Catégorie', 'pilopress' ),
                'instructions'  => __( 'Layout category', 'pilopress' ),
                'type'          => 'text',
                'name'          => '_pip_category',
                'prefix'        => 'acf_field_group',
                'default_value' => 'classic',
                'value'         => isset( $field_group['_pip_category'] ) ? $field_group['_pip_category'] : 'Classic',
            ) );

            // Miniature
            acf_render_field_wrap( array(
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
            ) );

            // Script for admin style
            ?>
            <script type="text/javascript">
              if (typeof acf !== 'undefined') {
                acf.postbox.render({
                  'id': 'pip_layout_settings',
                  'label': 'left'
                });
              }
            </script>
            <?php
        }

        /**
         * Check if post/field group is a layout
         *
         * @param array|int $post
         *
         * @return bool|mixed|null
         */
        public static function is_layout( $post ) {
            $is_layout   = false;
            $field_group = null;

            // If no post ID, return false
            if ( !$post ) {
                return $is_layout;
            }

            if ( is_array( $post ) ) {

                // If is array, it's a field group
                $field_group = $post;
            } else {

                // If is ID, get field group
                $field_group = acf_get_field_group( $post );
            }

            // If no field group, return false
            if ( !$field_group ) {
                return $is_layout;
            }

            // Is layout option set to true ?
            $is_layout = acf_maybe_get( $field_group, '_pip_is_layout' );

            // If layout in URL, set to true
            if ( acf_maybe_get_GET( 'layout' ) ) {
                $is_layout = true;
            }

            return $is_layout;
        }

        /**
         * Create layout directory with corresponding files
         *
         * @param $layout_title
         * @param $field_group
         */
        private function create_layout_dir( $layout_title, $field_group ) {
            // Create directory
            wp_mkdir_p( PIP_THEME_LAYOUTS_PATH . $layout_title );

            // Options to check/modify
            $render = array(
                array(
                    'render'    => '_pip_render_layout',
                    'extension' => '.php',
                    'default'   => $layout_title,
                ),
                array(
                    'render'    => '_pip_render_style',
                    'extension' => '.css',
                    'default'   => $layout_title,
                ),
                array(
                    'render'    => '_pip_render_style_scss',
                    'extension' => '.scss',
                    'default'   => $layout_title,
                ),
                array(
                    'render'    => '_pip_render_script',
                    'extension' => '.js',
                    'default'   => $layout_title,
                ),
            );

            // Create files
            foreach ( $render as $item ) {
                if ( !acf_maybe_get( $field_group, $item['render'] ) ) {

                    // Get default file name
                    $field_group[ $item['render'] ] = $item['default'] . $item['extension'];
                }
                touch( PIP_THEME_LAYOUTS_PATH . $layout_title . '/' . $field_group[ $item['render'] ] );
            }

            // Update field group
            acf_update_field_group( $field_group );
        }

        /**
         * Modify layout directory title
         *
         * @param $old_title
         * @param $new_title
         */
        private function modify_layout_dir( $old_title, $new_title ) {
            rename( PIP_THEME_LAYOUTS_PATH . $old_title, PIP_THEME_LAYOUTS_PATH . $new_title );
        }

        /**
         * Compile layout SCSS on sync
         *
         * @param $field_group
         */
        public function compile_on_sync( $field_group ) {
            // If not a layout, return
            if ( !self::is_layout( $field_group ) ) {
                return;
            }

            // Compile
            PIP_Styles_Settings::compile_layouts_styles( $field_group['ID'] );
        }

        /**
         * Setter: $layout_group_keys
         *
         * @param $layout_group_keys
         *
         * @return void
         */
        public static function set_layout_group_keys( $layout_group_keys ) {
            self::$layout_group_keys = $layout_group_keys;
        }

        /**
         * Getter: $layout_group_keys
         * @return array
         */
        public static function get_layout_group_keys() {
            return self::$layout_group_keys;
        }
    }

    // Instantiate class
    new PIP_Field_Groups_Layouts();
}