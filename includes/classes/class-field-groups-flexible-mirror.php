<?php

if ( !class_exists( 'PIP_Field_Groups_Flexible_Mirror' ) ) {
    class PIP_Field_Groups_Flexible_Mirror {

        private static $flexible_mirror_group;
        private static $flexible_mirror_group_key = 'group_pip_flexible_mirror';
        private static $layout_group_keys         = array();

        public function __construct() {
            // WP hooks
            add_action( 'init', array( $this, 'modify_acf_post_type_labels' ) );
            add_action( 'current_screen', array( $this, 'current_screen' ) );
            add_filter( 'pre_delete_post', array( $this, 'delete_post' ), 10, 2 );
            add_filter( 'pre_trash_post', array( $this, 'delete_post' ), 10, 2 );
        }

        /**
         * Fire actions on acf field groups page
         */
        public function current_screen() {
            // ACF field groups archive
            if ( acf_is_screen( 'edit-acf-field-group' ) ) {
                add_action( 'load-edit.php', array( $this, 'generate_flexible_mirror' ) );
                add_filter( 'page_row_actions', array( $this, 'row_actions' ), 10, 2 );
            }

            // ACF field group single
            if ( acf_is_screen( 'acf-field-group' ) ) {
                add_action( 'acf/input/admin_head', array( $this, 'meta_boxes' ) );
            }
        }

        /**
         * Change title on flexible edition page
         */
        public function modify_acf_post_type_labels() {
            $post = get_post( acf_maybe_get_GET( 'post' ) );
            // If mirror flexible
            if ( $post && $post->post_name === self::get_flexible_mirror_group_key() ) {
                $acf_field_group = get_post_type_object( 'acf-field-group' );

                // Change title on flexible edition page
                $acf_field_group->labels->edit_item = __( 'Edit Flexible Content', 'pilopress' );
            }
        }

        /**
         * Generate flexible mirror
         */
        public function generate_flexible_mirror() {
            // If mirror flexible already exists, return
            if ( self::get_flexible_mirror_group() ) {
                return;
            }

            // Mirror flexible field group
            $flexible_mirror = array(
                'key'                   => self::get_flexible_mirror_group_key(),
                'title'                 => 'Flexible Content',
                'fields'                => array(),
                'location'              => array(
                    array(
                        array(
                            'param'    => 'post_type',
                            'operator' => '==',
                            'value'    => 'all',
                        ),
                    ),
                    array(
                        array(
                            'param'    => 'taxonomy',
                            'operator' => '==',
                            'value'    => 'all',
                        ),
                    ),
                ),
                'menu_order'            => 0,
                'position'              => 'normal',
                'style'                 => 'seamless',
                'label_placement'       => 'top',
                'instruction_placement' => 'label',
                'hide_on_screen'        => '',
                'active'                => false,
                'description'           => '',
                'acfe_display_title'    => '',
                'acfe_autosync'         => '',
                'acfe_permissions'      => '',
                'acfe_form'             => 0,
                'acfe_meta'             => '',
                'acfe_note'             => '',
            );

            // Import flexible in local
            acf_import_field_group( $flexible_mirror );
        }

        /**
         * Remove trash action for mirror flexible field group
         *
         * @param $actions
         * @param $post
         *
         * @return mixed
         */
        public function row_actions( $actions, $post ) {
            // If not mirror flexible, return
            if ( $post->post_name !== self::get_flexible_mirror_group_key() ) {
                return $actions;
            }

            // Remove trash action
            unset( $actions['trash'] );

            return $actions;
        }

        /**
         * Prevent removal of mirror flexible field group
         *
         * @param $trash
         * @param $post
         *
         * @return bool
         */
        public function delete_post( $trash, $post ) {
            // If not mirror flexible group field, return
            $flexible_mirror_group_key = self::get_flexible_mirror_group_key();
            if ( $post->post_name !== $flexible_mirror_group_key && $post->post_name !== $flexible_mirror_group_key . '__trashed' ) {
                return $trash;
            }

            // Prevent delete/trash field group
            return false;
        }

        /**
         * Customize meta boxes on mirror flexible content page
         */
        public function meta_boxes() {
            global $field_group;

            // If not mirror flexible group field, return
            if ( $field_group['key'] !== self::get_flexible_mirror_group_key() ) {
                return;
            }

            // Remove meta boxes
            remove_meta_box( 'acf-field-group-options', 'acf-field-group', 'normal' );
            remove_meta_box( 'acf-field-group-fields', 'acf-field-group', 'normal' );
            remove_meta_box( 'slugdiv', 'acf-field-group', 'normal' );
            remove_meta_box( 'acf-field-group-acfe-side', 'acf-field-group', 'side' );
            remove_meta_box( 'acf-field-group-acfe', 'acf-field-group', 'normal' );
            remove_meta_box( 'acfe-wp-custom-fields', 'acf-field-group', 'normal' );

            // Add meta box
            add_meta_box( 'pip-flexible-layouts', __( 'Available layouts', 'pilopress' ), array( $this, 'layouts_meta_box' ), 'acf-field-group', 'normal', 'high' );
        }

        /**
         * Add custom meta box for mirror flexible
         */
        public function layouts_meta_box() {
            foreach ( self::get_layout_group_keys() as $layout_group_key ) {
                // Get current field group
                $layout_field_group = acf_get_field_group( $layout_group_key );

                // Get locations html
                $locations = ''; // PILO_TODO: get ACFE helper (next version)

                // Structured array for template file
                $layouts[] = array(
                    'title'     => $layout_field_group['title'],
                    'locations' => $locations,
                    'edit_link' => get_edit_post_link( $layout_field_group['ID'] ),
                );
            }

            // New field group link
            $add_new_link = add_query_arg(
                array(
                    'post_type' => 'acf-field-group',
                    'layout'    => 1,
                ),
                admin_url( 'post-new.php' )
            );

            // Template file
            include_once( _PIP_PATH . 'includes/views/flexible-layouts-meta-box.php' );
        }

        /**
         * Getter: $flexible_mirror_group_key
         * @return string
         */
        public static function get_flexible_mirror_group_key() {
            return self::$flexible_mirror_group_key;
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

        /**
         * Getter: $flexible_mirror_group
         * @return mixed
         */
        public static function get_flexible_mirror_group() {
            return self::$flexible_mirror_group;
        }

        /**
         * Setter: $flexible_mirror_group
         *
         * @param mixed $flexible_mirror_group
         */
        public static function set_flexible_mirror_group( $flexible_mirror_group ) {
            self::$flexible_mirror_group = $flexible_mirror_group;
        }

    }

    // Instantiate class
    new PIP_Field_Groups_Flexible_Mirror();
}
