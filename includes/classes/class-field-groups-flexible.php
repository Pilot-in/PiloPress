<?php

if ( !class_exists( 'PIP_Field_Groups_Flexible' ) ) {
    class PIP_Field_Groups_Flexible {

        private $flexible_field_name = '_pip_flexible';
        private $flexible_group_key  = 'group_pip_flexible_main';
        private $user_view           = 'edit';

        public function __construct() {
            // WP hooks
            add_action( 'init', array( $this, 'init' ) );

            // ACF hooks
            add_action( "acf/prepare_field/name={$this->flexible_field_name}", array( $this, 'prepare_field_flexible' ), 20 );
            add_action( 'acf/validate_field/type=flexible_content', array( $this, 'validate_field' ), 20 );

            // Pilo'Press hooks
            add_filter( 'pip/flexible/locations', array( $this, 'flexible_locations' ) );
        }

        /**
         * Register main flexible field group
         * Add layouts to main flexible
         */
        public function init() {
            $layouts      = array();
            $group_keys   = array();
            $field_groups = acf_get_field_groups();

            // Get layouts
            if ( $field_groups ) {
                foreach ( $field_groups as $field_group ) {
                    // If not layout, skip
                    if ( !acf_maybe_get( $field_group, '_pip_is_layout' ) ) {
                        continue;
                    }

                    $title          = str_replace( 'Layout: ', '', $field_group['title'] );
                    $name           = sanitize_title( $title );
                    $layout_uniq_id = 'layout_' . $name;

                    // Store layout
                    $layouts[ $layout_uniq_id ] = [
                        'key'        => $layout_uniq_id,
                        'name'       => $name,
                        'label'      => $title,
                        'display'    => 'row',
                        'sub_fields' => [
                            [
                                'key'               => 'field_clone_' . $name,
                                'label'             => $title,
                                'name'              => $name,
                                'type'              => 'clone',
                                'instructions'      => '',
                                'required'          => 0,
                                'conditional_logic' => 0,
                                'wrapper'           => [
                                    'width' => '',
                                    'class' => '',
                                    'id'    => '',
                                ],
                                'acfe_permissions'  => '',
                                'clone'             => [
                                    $field_group['key'],
                                ],
                                'display'           => 'seamless',
                                'layout'            => 'block',
                                'prefix_label'      => 0,
                                'prefix_name'       => 1,
                                'acfe_clone_modal'  => 0,
                            ],
                        ],
                        'min'        => '',
                        'max'        => '',
                    ];

                    // Store group keys for meta box on mirror flexible
                    $group_keys[] = $field_group['key'];
                }
            }

            PIP_Field_Groups_Flexible_Mirror::set_layout_group_keys( $group_keys );

            $locations = apply_filters( 'pip/flexible/locations', array() );

            // Main flexible content field group
            $args = array(
                'key'                   => $this->flexible_group_key,
                'title'                 => 'Flexible Content',
                'fields'                => array(
                    array(
                        'key'                               => 'field_pip' . $this->flexible_field_name,
                        'label'                             => 'Flexible Content',
                        'name'                              => $this->flexible_field_name,
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
                        'acfe_flexible_layouts_thumbnails'  => 0,
                        'acfe_flexible_layouts_settings'    => 0,
                        'acfe_flexible_layouts_ajax'        => 0,
                        'acfe_flexible_layouts_templates'   => 0,
                        'acfe_flexible_layouts_placeholder' => 0,
                        'acfe_flexible_disable_ajax_title'  => 0,
                        'acfe_flexible_close_button'        => 0,
                        'acfe_flexible_title_edition'       => 0,
                        'acfe_flexible_copy_paste'          => 0,
                        'acfe_flexible_modal_edition'       => 0,
                        'acfe_flexible_modal'               => array(
                            'acfe_flexible_modal_enabled' => '0', // PILO_TODO: Switch to 1
                        ),
                        'acfe_flexible_layouts_state'       => '',
                        'layouts'                           => $layouts,
                        'button_label'                      => 'Ajouter une ligne',
                        'min'                               => '',
                        'max'                               => '',
                    ),
                ),
                'location'              => $locations,
                'menu_order'            => 0,
                'position'              => 'normal',
                'style'                 => 'seamless',
                'label_placement'       => 'top',
                'instruction_placement' => 'label',
                'hide_on_screen'        => array(
                    'the_content',
                ),
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
        public function validate_field( $field ) {
            // If not main flexible, return
            if ( $field['name'] !== $this->flexible_field_name ) {
                return $field;
            }

            // If no layouts, return
            if ( empty( $field['layouts'] ) ) {
                return $field;
            }

            // Initiate layouts to empty array for returns
            $layouts          = $field['layouts'];
            $field['layouts'] = array();

            // Get post_id and screen
            $screen  = acf_get_form_data( 'screen' );
            $post_id = acf_get_form_data( 'post_id' );

            if ( !$screen ) {
                $current_screen = get_current_screen();
                if ( !$current_screen ) {
                    return $field;
                }

                $screen = $current_screen->id;
            }

            /**
             * Extract ACF id from URL id
             * @var $id
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

                    // If Dynamic Template: Stop! // PILO_TODO: uncomment
//				if ( $post_type === 'acfe-template' ) {
//					return $field;
//				}

                    $args = array(
                        'post_id'   => $post_id,
                        'post_type' => $post_type,
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
                if ( !$this->get_field_group_visibility( $field_group, $args ) ) {
                    continue;
                }

                // Sanitize label and name
                $field_group_label = str_ireplace( 'Layout: ', '', $field_group['title'] );
                $field_group_name  = sanitize_title( $field_group_label );

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

            // If no layouts, return
            if ( empty( $keep ) ) {
                return $field;
            }

            // Replace layouts
            $field['layouts'] = $keep;

            // Return field with layouts for current screen
            return $field;
        }

        /**
         * Returns true if the given field group's location rules match the given $args
         *
         * @see ACF's acf_get_field_group_visibility()
         *
         * @param $field_group
         * @param array $args
         *
         * @return bool
         */
        public function get_field_group_visibility( $field_group, $args = array() ) {
            // Check if location rules exist
            if ( $field_group['location'] ) {

                // Get the current screen.
                $screen = acf_get_location_screen( $args );

                // Loop through location groups.
                foreach ( $field_group['location'] as $group ) {

                    // ignore group if no rules.
                    if ( empty( $group ) ) {
                        continue;
                    }

                    // Loop over rules and determine if all rules match.
                    $match_group = true;
                    foreach ( $group as $rule ) {
                        if ( !acf_match_location_rule( $rule, $screen, $field_group ) ) {
                            $match_group = false;
                            break;
                        }
                    }

                    // If this group matches, show the field group.
                    if ( $match_group ) {
                        return true;
                    }
                }
            }

            return false;
        }

        /**
         * Hide flexible if no layouts
         *
         * @param $field
         *
         * @return bool
         */
        public function prepare_field_flexible( $field ) {
            // If no layout, return false to hide field group
            if ( empty( $field['layouts'] ) ) {
                return false;
            }

            return $field;
        }

        /**
         * Get locations of mirror flexible
         *
         * @param $locations
         *
         * @return mixed
         */
        public function flexible_locations( $locations ) {
            // Get field group
            $mirror = acf_get_field_group( PIP_Field_Groups_Flexible_Mirror::get_flexible_mirror_group_key() );

            // If field group doesn't exist, return
            if ( !$mirror ) {
                return $locations;
            }

            // Replace main flexible's locations with mirror flexible's locations
            $locations = $mirror['location'];

            return $locations;
        }

    }

    new PIP_Field_Groups_Flexible();
}
