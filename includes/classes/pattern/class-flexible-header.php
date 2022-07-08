<?php

if ( !class_exists( 'PIP_Flexible_Header' ) ) {
    /**
     * Class PIP_Flexible_Header
     */
    class PIP_Flexible_Header {

        /**
         * Header field name
         *
         * @var string
         */
        public $flexible_header_field_name = 'pip_flexible_header';

        /**
         * Header group key
         *
         * @var string
         */
        public $flexible_header_group_key = 'group_pip_flexible_header';

        public function __construct() {

            // WP hooks
            add_action( 'init', array( $this, 'init' ) );

            $pip_flexible               = acf_get_instance( 'PIP_Flexible' );
            $flexible_header_field_name = $this->get_flexible_header_field_name();

            // ACF hooks
            add_filter( "acf/prepare_field/name={$flexible_header_field_name}", array( $this, 'prepare_flexible_field' ), 20 );

            // ACFE hook
            add_filter( "acfe/flexible/thumbnail/name={$flexible_header_field_name}", array( $pip_flexible, 'add_custom_thumbnail' ), 10, 3 );
            add_filter( 'acfe/flexible/layouts/icons', array( $pip_flexible, 'custom_layout_actions' ), 10, 3 );
            add_filter( 'acfe/flexible/layouts/icons', array( $pip_flexible, 'hide_some_actions' ), 25, 3 );
        }

        /**
         * Register header flexible field group
         * Add layouts to header flexible
         */
        public function init() {

            $pip_flexible = acf_get_instance( 'PIP_Flexible' );
            $pip_pattern  = acf_get_instance( 'PIP_Pattern' );

            // Get layouts
            $data    = $pip_flexible->get_layouts_and_group_keys();
            $layouts = $data['layouts'];

            // Field
            $field = array(
                'key'               => 'field_' . $this->get_flexible_header_field_name(),
                'label'             => __( 'Header', 'pilopress' ),
                'name'              => $this->get_flexible_header_field_name(),
                'type'              => 'flexible_content',
                'instructions'      => '',
                'required'          => 0,
                'conditional_logic' => 0,
                'wrapper'           => array(
                    'width' => '',
                    'class' => '',
                    'id'    => '',
                ),
                'layouts'           => $layouts,
                'button_label'      => __( 'Add Row', 'pilopress' ),
                'min'               => '',
                'max'               => '',
            );

            // Field Additional Args
            $field_args = apply_filters(
                'pip/builder/parameters',
                array(
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
                    'acfe_flexible_clone'               => 1,
                    'acfe_flexible_copy_paste'          => 1,
                    'acfe_flexible_modal_edition'       => 1,
                    'acfe_flexible_layouts_state'       => '',
                    'acfe_flexible_hide_empty_message'  => 1,
                    'acfe_flexible_empty_message'       => '',
                    'acfe_flexible_layouts_previews'    => 1,
                    'acfe_flexible_modal'               => array(
                        'acfe_flexible_modal_title'      => __( 'Header', 'pilopress' ),
                        'acfe_flexible_modal_enabled'    => '1',
                        'acfe_flexible_modal_col'        => '6',
                        'acfe_flexible_modal_categories' => '1',
                    ),
                )
            );

            // Final Field
            $field = array_merge( $field, $field_args );

            // Header flexible content field group
            $args = array(
                'key'                   => $this->get_flexible_header_group_key(),
                'title'                 => 'Flexible Content Header',
                'fields'                => array( $field ),
                'location'              => array(
                    array(
                        array(
                            'param'    => 'options_page',
                            'operator' => '==',
                            'value'    => $pip_pattern->menu_slug,
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
         * @return bool
         */
        public function prepare_flexible_field( $field ) {

            // If no layouts, return
            if ( empty( $field['layouts'] ) && strpos( $field['_name'], $this->get_flexible_header_field_name() ) === 0 ) {
                return false;
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
            $screen = acf_get_form_data( 'screen' );

            // Get args depending on screen
            switch ( $screen ) {
                case 'options':
                    $args = array(
                        'pip-pattern' => $this->get_flexible_header_field_name(),
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

            $pip_flexible = acf_get_instance( 'PIP_Flexible' );

            // Array for valid layouts
            $keep = array();

            foreach ( $field_groups as $field_group ) {
                // If current screen not included in field group location, skip
                if ( !$pip_flexible->get_field_group_visibility( $field_group, $args ) ) {
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
         *
         * @return string
         */
        public function get_flexible_header_field_name() {

            return $this->flexible_header_field_name;
        }

        /**
         * Getter: $flexible_header_group_key
         *
         * @return string
         */
        public function get_flexible_header_group_key() {

            return $this->flexible_header_group_key;
        }

    }

    acf_new_instance( 'PIP_Flexible_Header' );

}

/**
 * Get flexible header content
 *
 * @param bool $echo
 *
 * @return false|string|void
 */
function get_pip_header( $echo = true ) {

    $pip_flexible_header = acf_get_instance( 'PIP_Flexible_Header' );
    $pip_pattern         = acf_get_instance( 'PIP_Pattern' );
    $pip_pattern_id      = pip_maybe_get( $pip_pattern, 'pattern_post_id' );

    if ( $echo ) {
        echo get_flexible( $pip_flexible_header->get_flexible_header_field_name(), $pip_pattern_id );
    } else {
        return get_flexible( $pip_flexible_header->get_flexible_header_field_name(), $pip_pattern_id );
    }
}
