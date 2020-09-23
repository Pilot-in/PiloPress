<?php

if ( !class_exists( 'PIP_Flexible_Mirror' ) ) {

    /**
     * Class PIP_Flexible_Mirror
     */
    class PIP_Flexible_Mirror {

        /*
         * Vars
         */
        var $flexible_mirror_group = false;
        var $flexible_mirror_group_id = false;
        var $flexible_mirror_group_key = 'group_pip_flexible_mirror';

        /**
         * Constructor
         */
        public function __construct() {

            // WP hooks
            add_action( 'current_screen', array( $this, 'current_screen' ) );

        }

        /**
         * Fire actions on acf field groups page
         */
        public function current_screen() {

            // Bail early if not "Builder" view
            if ( !acf_is_screen( 'acf-field-group' ) || (int) acf_maybe_get_GET( 'post' ) !== $this->get_group_id() ) {
                return;
            }

            add_action( 'load-post.php', array( $this, 'load' ) );
            add_action( 'load-post.php', array( $this, 'labels' ) );

        }

        function load() {

            add_filter( 'admin_body_class',     array( $this, 'admin_body_class' ) );
            add_action( 'acf/input/admin_head', array( $this, 'meta_boxes' ) );
            add_action( 'acf/form_data',        array( $this, 'hidden_fields' ) );

        }

        /**
         * Change title on flexible edition page
         */
        public function labels() {

            $post_type = get_post_type_object( 'acf-field-group' );

            // Change title on flexible edition page
            $post_type->labels->edit_item = __( 'Edit Builder', 'pilopress' );

            // Hide "Add new" button
            $post_type->cap->create_posts = false;

            // Hide "Trash" button
            $post_type->cap->delete_post = false;

        }

        function admin_body_class( $classes ) {

            $classes .= ' pip-builder ';

            return $classes;

        }

        /**
         * Customize meta boxes on mirror flexible content page
         */
        public function meta_boxes() {

            // Remove meta boxes
            remove_meta_box( 'acf-field-group-options', 'acf-field-group', 'normal' );
            remove_meta_box( 'acf-field-group-fields', 'acf-field-group', 'normal' );
            remove_meta_box( 'slugdiv', 'acf-field-group', 'normal' );
            remove_meta_box( 'acf-field-group-acfe-side', 'acf-field-group', 'side' );
            remove_meta_box( 'acf-field-group-acfe', 'acf-field-group', 'normal' );
            remove_meta_box( 'acfe-wp-custom-fields', 'acf-field-group', 'normal' );
            remove_meta_box( 'acf-layouts-collectiondiv', 'acf-field-group', 'side' );

        }

        /**
         * Add hidden data on flexible mirror admin
         */
        public function hidden_fields() {

            // Add hidden fields
            acf_hidden_input( array(
                'name'  => 'acf_field_group[key]',
                'value' => $this->flexible_mirror_group_key,
            ) );

            acf_hidden_input( array(
                'name'  => 'acf_field_group[style]',
                'value' => 'seamless',
            ) );

            acf_hidden_input( array(
                'name'  => 'acf_field_group[active]',
                'value' => 0,
            ) );

            acf_hidden_input( array(
                'name'  => 'acf_field_group[position]',
                'value' => 'normal',
            ) );

            acf_hidden_input( array(
                'name'  => 'acf_field_group[label_placement]',
                'value' => 'left',
            ) );

            acf_hidden_input( array(
                'name'  => 'acf_field_group[menu_order]',
                'value' => 0,
            ) );

        }

        /**
         * Generate flexible mirror
         */
        public function import_group() {

            // If mirror flexible already exists, return
            if ( $this->get_group() ) {
                return;
            }

            // Mirror flexible field group
            $flexible_mirror = array(
                'key'                   => $this->flexible_mirror_group_key,
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
         * Getter: $flexible_mirror_group
         *
         * @return mixed
         */
        public function get_group() {

            if ( !$this->flexible_mirror_group ) {

                $this->flexible_mirror_group = acf_get_field_group( $this->flexible_mirror_group_key );

            }

            return $this->flexible_mirror_group;

        }

        /**
         * Getter: $flexible_mirror_group
         *
         * @return mixed
         */
        public function get_group_id() {

            if ( !$this->flexible_mirror_group_id ) {

                $group = $this->get_group();

                $this->flexible_mirror_group_id = acf_maybe_get( $group, 'ID', false );

            }

            return $this->flexible_mirror_group_id;

        }

    }

    // Instantiate class
    acf_new_instance( 'PIP_Flexible_Mirror' );

}

function pip_get_flexible_mirror_group() {

    return acf_get_instance( 'PIP_Flexible_Mirror' )->get_group();

}

function pip_get_flexible_mirror_group_key() {

    return acf_get_instance( 'PIP_Flexible_Mirror' )->flexible_mirror_group_key;

}

function pip_get_flexible_mirror_group_id() {

    return acf_get_instance( 'PIP_Flexible_Mirror' )->get_group_id();

}

function pip_import_flexible_mirror_group() {

    return acf_get_instance( 'PIP_Flexible_Mirror' )->import_group();

}
