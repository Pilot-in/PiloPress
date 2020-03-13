<?php

if ( !class_exists( 'PIP_Flexible_Header' ) ) {
    class PIP_Flexible_Header {

        private static $flexible_header_field_name = 'pip_flexible_header';
        private        $flexible_header_group_key  = 'group_pip_flexible_header';
        private        $user_view                  = 'edit';

        public function __construct() {
            // WP hooks
            add_action( 'init', array( $this, 'init' ) );

            // ACF hooks
            $flexible_header_field_name = self::get_flexible_header_field_name();
            add_filter( "acf/prepare_field/name={$flexible_header_field_name}", array( $this, 'prepare_flexible_field' ), 20 );
        }

        /**
         * Register header flexible field group
         * Add layouts to header flexible
         */
        public function init() {
            // Get layouts
            $data    = PIP_Flexible::get_layouts_and_group_keys();
            $layouts = $data['layouts'];

            // Header flexible content field group
            $args = array(
                'key'                   => $this->flexible_header_group_key,
                'title'                 => 'Flexible Content Header',
                'fields'                => array(
                    array(
                        'key'                               => 'field_' . self::get_flexible_header_field_name(),
                        'label'                             => 'Header',
                        'name'                              => self::get_flexible_header_field_name(),
                        'type'                              => 'flexible_content',
                        'instructions'                      => '',
                        'required'                          => 0,
                        'conditional_logic'                 => 0,
                        'wrapper'                           => array(
                            'width' => '',
                            'class' => '',
                            'id'    => '',
                        ),
                        'acfe_permissions'                  => '',
                        'acfe_flexible_stylised_button'     => 1,
                        'acfe_flexible_layouts_thumbnails'  => 1,
                        'acfe_flexible_layouts_settings'    => 1,
                        'acfe_flexible_layouts_ajax'        => 1,
                        'acfe_flexible_layouts_templates'   => 1,
                        'acfe_flexible_layouts_placeholder' => 0,
                        'acfe_flexible_disable_ajax_title'  => 1,
                        'acfe_flexible_close_button'        => 1,
                        'acfe_flexible_title_edition'       => 1,
                        'acfe_flexible_copy_paste'          => 1,
                        'acfe_flexible_modal_edition'       => 0,
                        'acfe_flexible_modal'               => array(
                            'acfe_flexible_modal_enabled'    => '1',
                            'acfe_flexible_modal_title'      => "Pilo'Press",
                            'acfe_flexible_modal_col'        => '6',
                            'acfe_flexible_modal_categories' => '1',
                        ),
                        'acfe_flexible_layouts_state'       => '',
                        'acfe_flexible_hide_empty_message'  => 1,
                        'acfe_flexible_empty_message'       => '',
                        'acfe_flexible_layouts_previews'    => 1,
                        'layouts'                           => $layouts,
                        'button_label'                      => 'Add Row',
                        'min'                               => '',
                        'max'                               => '',
                    ),
                ),
                'location'              => array(
                    array(
                        array(
                            'param'    => 'options_page',
                            'operator' => '==',
                            'value'    => PIP_Pattern::get_pattern_option_page()['menu_slug'],
                        ),
                    ),
                ),
                'menu_order'            => 0,
                'position'              => 'normal',
                'style'                 => 'seamless',
                'label_placement'       => 'top',
                'instruction_placement' => 'label',
                'active'                => true,
                'description'           => '',
                'acfe_display_title'    => '',
                'acfe_autosync'         => '',
                'acfe_permissions'      => '',
                'acfe_form'             => 0,
                'acfe_meta'             => '',
                'acfe_note'             => '',
            );

            // Register field group
            acf_add_local_field_group( $args );
        }

        /**
         * Parse all field groups and show only those for current screen
         *
         * @param $field
         *
         * @return mixed
         */
        public function prepare_flexible_field( $field ) {
            // If no layouts, return
            if ( empty( $field['layouts'] ) ) {
                return $field;
            }

            // If AJAX, filters not needed
            if ( wp_doing_ajax() ) {

                // Exception for attachments view in grid mode
                if ( acf_maybe_get_POST( 'action' ) !== 'query-attachments' ) {
                    return $field;
                }
            }

            // Initiate layouts to empty array for returns
            $layouts          = $field['layouts'];
            $field['layouts'] = array();

            // Get post_id and screen
            $screen  = acf_get_form_data( 'screen' );
            $post_id = acf_get_form_data( 'post_id' );

            /**
             * Extract ACF id from URL id
             * @var $type string post type
             * @var $id int|string post ID
             */
            extract( acf_get_post_id_info( $post_id ) );

            // Get args depending on screen
            switch ( $screen ) {
                case 'user':
                    $args = array(
                        'user_id'   => $id,
                        'user_form' => $this->user_view,
                    );
                    break;
                case 'attachment':
                    $args = array(
                        'attachment_id' => $id,
                        'attachment'    => $id,
                    );
                    break;
                case 'taxonomy':
                    if ( !empty( $id ) ) {
                        $term     = get_term( $id );
                        $taxonomy = $term->taxonomy;
                    } else {
                        $taxonomy = acf_maybe_get_GET( 'taxonomy' );
                    }

                    $args = array(
                        'taxonomy' => $taxonomy,
                    );
                    break;
                case 'page':
                case 'post':
                    $post_type = get_post_type( $post_id );

                    $args = array(
                        'post_id'   => $post_id,
                        'post_type' => $post_type,
                    );
                    break;
                case 'options':
                    $args = array(
                        'options_page' => str_replace( '_', '-', $post_id ),
                    );
                    break;
            }

            // If no args, return
            if ( empty( $args ) ) {
                return $field;
            }

            // Get all fields groups (hidden included)
            $field_groups = acf_get_field_groups();

            // If no field groups, return
            if ( empty( $field_groups ) ) {
                return $field;
            }

            // Array for valid layouts
            $keep = array();

            foreach ( $field_groups as $field_group ) {

                // If current screen not included in field group location, skip
                if ( !PIP_Flexible::get_field_group_visibility( $field_group, $args ) ) {
                    continue;
                }

                // Sanitize name
                $field_group_name = sanitize_title( $field_group['title'] );

                // Browse all layouts
                foreach ( $layouts as $key => $layout ) {

                    // If field group not in layouts, skip
                    if ( $layout['name'] !== $field_group_name ) {
                        continue;
                    }

                    // If field group in layouts, keep it
                    $keep[ $key ] = $layout;
                    break;
                }

            }

            // If no layouts, return false to hide field group
            if ( empty( $keep ) ) {
                return false;
            }

            // Replace layouts
            $field['layouts'] = $keep;

            // Return field with layouts for current screen
            return $field;
        }

        /**
         * Getter: $flexible_header_field_name
         * @return string
         */
        public static function get_flexible_header_field_name() {
            return self::$flexible_header_field_name;
        }

    }

    // Instantiate class
    new PIP_Flexible_Header();
}

/**
 * Get flexible header content
 *
 * @param bool|int $post_id
 *
 * @return false|string|void
 */
function get_pip_header( $post_id = false ) {
    return get_flexible( PIP_Flexible_Header::get_flexible_header_field_name(), $post_id );
}