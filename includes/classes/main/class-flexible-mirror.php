<?php

if ( !class_exists( 'PIP_Flexible_Mirror' ) ) {

    /**
     * Class PIP_Flexible_Mirror
     */
    class PIP_Flexible_Mirror {

        /**
         * Flexible mirror
         *
         * @var object
         */
        private static $flexible_mirror_group;

        /**
         * Flexible mirror group key
         *
         * @var string
         */
        private static $flexible_mirror_group_key = 'group_pip_flexible_mirror';

        public function __construct() {
            // WP hooks
            add_action( 'init', array( $this, 'modify_acf_post_type' ) );
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
                add_action( 'acf/form_data', array( $this, 'add_flexible_mirror_hidden_data' ) );
            }
        }

        /**
         * Change title on flexible edition page
         */
        public function modify_acf_post_type() {
            // If AJAX or not admin, return
            if ( wp_doing_ajax() || !is_admin() ) {
                return;
            }

            // Get current post
            $post = get_post( acf_maybe_get_GET( 'post' ) );

            // Mirror flexible
            if ( $post && $post->post_name === self::get_flexible_mirror_group_key() ) {
                $acf_field_group = get_post_type_object( 'acf-field-group' );

                // Change title on flexible edition page
                $acf_field_group->labels->edit_item = __( 'Edit Builder', 'pilopress' );

                // Hide "Add new" button
                $acf_field_group->cap->create_posts = false;
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
                'title'                 => __( 'Builder', 'pilopress' ),
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

        }

        /**
         * Add hidden data on flexible mirror admin
         */
        public function add_flexible_mirror_hidden_data() {
            global $field_group;

            // If not mirror flexible group field, return
            if ( $field_group['key'] !== self::get_flexible_mirror_group_key() ) {
                return;
            }

            // Add hidden fields
            acf_hidden_input(
                array(
                    'name'  => 'acf_field_group[key]',
                    'value' => self::get_flexible_mirror_group_key(),
                )
            );
            acf_hidden_input(
                array(
                    'name'  => 'acf_field_group[style]',
                    'value' => 'seamless',
                )
            );
            acf_hidden_input(
                array(
                    'name'  => 'acf_field_group[active]',
                    'value' => 0,
                )
            );
            acf_hidden_input(
                array(
                    'name'  => 'acf_field_group[position]',
                    'value' => 'normal',
                )
            );
            acf_hidden_input(
                array(
                    'name'  => 'acf_field_group[label_placement]',
                    'value' => 'left',
                )
            );
            acf_hidden_input(
                array(
                    'name'  => 'acf_field_group[menu_order]',
                    'value' => 0,
                )
            );
        }

        /**
         * Get terms html
         *
         * @param $field_group_id
         *
         * @return string
         */
        private static function get_terms_html( $field_group_id ) {
            $terms_html = '';
            $terms      = get_the_terms( $field_group_id, 'acf-layouts-category' );
            if ( $terms ) {
                foreach ( $terms as $term ) {
                    $url = add_query_arg(
                        array(
                            'layouts'              => 1,
                            'acf-layouts-category' => $term->slug,
                            'post_type'            => 'acf-field-group',
                        ),
                        admin_url( 'edit.php' )
                    );

                    $terms_html .= '<a href="' . $url . '">' . $term->name . '</a>';
                }
            }

            return $terms_html;
        }

        /**
         * Getter: $flexible_mirror_group_key
         *
         * @return string
         */
        public static function get_flexible_mirror_group_key() {
            return self::$flexible_mirror_group_key;
        }

        /**
         * Getter: $flexible_mirror_group
         *
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
    new PIP_Flexible_Mirror();
}
