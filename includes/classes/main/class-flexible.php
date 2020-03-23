<?php

if ( !class_exists( 'PIP_Flexible' ) ) {
    class PIP_Flexible {

        private static $flexible_field_name = 'pip_flexible';
        /**
         * @var bool
         */
        public static  $show_pattern_notice = false;
        private        $flexible_group_key  = 'group_pip_flexible_main';
        private static $user_view           = 'edit';

        public function __construct() {
            // WP hooks
            add_action( 'init', array( $this, 'init' ) );

            // ACF hooks
            $flexible_field_name = self::get_flexible_field_name();
            add_filter( "acf/prepare_field/name={$flexible_field_name}", array( $this, 'prepare_flexible_field' ), 20 );

            // Pilo'Press hooks
            add_filter( 'pip/flexible/locations', array( $this, 'flexible_locations' ) );
        }

        /**
         * Register main flexible field group
         * Add layouts to main flexible
         */
        public function init() {
            // Get layouts and group keys
            $data       = self::get_layouts_and_group_keys();
            $layouts    = $data['layouts'];
            $group_keys = $data['group_keys'];

            PIP_Layouts::set_layout_group_keys( $group_keys );
            PIP_Flexible_Mirror::set_flexible_mirror_group( acf_get_field_group( PIP_Flexible_Mirror::get_flexible_mirror_group_key() ) );

            $locations = apply_filters( 'pip/flexible/locations', array() );

            // Main flexible content field group
            $args = array(
                'key'                   => $this->flexible_group_key,
                'title'                 => __( 'Flexible Content', 'pilopress' ),
                'fields'                => array(
                    array(
                        'key'                           => 'field_' . self::get_flexible_field_name(),
                        'label'                         => __( 'Flexible Content', 'pilopress' ),
                        'name'                          => self::get_flexible_field_name(),
                        'type'                          => 'flexible_content',
                        'instructions'                  => '',
                        'required'                      => 0,
                        'conditional_logic'             => 0,
                        'wrapper'                       => array(
                            'width' => '',
                            'class' => '',
                            'id'    => '',
                        ),
                        'acfe_permissions'              => '',
                        'acfe_flexible_stylised_button' => 1,

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
                        'button_label'                      => __( 'Add Row', 'pilopress' ),
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
         * Get layouts and group keys
         * @return array
         */
        public static function get_layouts_and_group_keys() {
            $layouts      = array();
            $group_keys   = array();
            $field_groups = acf_get_field_groups();

            if ( $field_groups ) {
                foreach ( $field_groups as $field_group ) {
                    // If not layout, skip
                    if ( !PIP_Layouts::is_layout( $field_group ) ) {
                        continue;
                    }

                    $title          = $field_group['title'];
                    $name           = sanitize_title( $field_group['title'] );
                    $layout_slug    = sanitize_title( acf_maybe_get( $field_group, '_pip_layout_slug', '' ) );
                    $layout_uniq_id = 'layout_' . $layout_slug;

                    // Paths
                    $file_path = PIP_THEME_LAYOUTS_PATH . $layout_slug . '/';
                    $file_url  = PIP_THEME_LAYOUTS_URL . $layout_slug . '/';

                    // Terms
                    $terms = get_terms(
                        array(
                            'taxonomy'   => 'acf-layouts-category',
                            'object_ids' => $field_group['ID'],
                            'fields'     => 'names',
                        )
                    );

                    // Settings
                    $modal_category   = $terms ? $terms : array();
                    $render_layout    = $file_path . acf_maybe_get( $field_group, '_pip_render_layout', $name . '.php' );
                    $render_style     = $file_url . acf_maybe_get( $field_group, '_pip_render_style', $name . '.css' );
                    $render_script    = $file_url . acf_maybe_get( $field_group, '_pip_render_script', $name . '.js' );
                    $layout_thumbnail = acf_maybe_get( $field_group, '_pip_thumbnail', '870' );
                    $configuration    = acf_maybe_get( $field_group, '_pip_configuration', array() );
                    $modal_size       = acf_maybe_get( $field_group, '_pip_modal_size', array() );

                    // Store layout
                    $layouts[ $layout_uniq_id ] = [
                        'key'                           => $layout_uniq_id,
                        'name'                          => $name,
                        'label'                         => $title,
                        'display'                       => 'row',
                        'sub_fields'                    => [
                            [
                                'key'               => 'field_clone_' . $layout_slug,
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
                                'clone'             => array(
                                    $field_group['key'],
                                ),
                                'display'           => 'seamless',
                                'layout'            => 'block',
                                'prefix_label'      => 0,
                                'prefix_name'       => 0,
                                'acfe_clone_modal'  => 0,
                            ],
                        ],
                        'acfe_flexible_category'        => $modal_category,
                        'acfe_flexible_render_template' => $render_layout,
                        'acfe_flexible_render_style'    => $render_style,
                        'acfe_flexible_render_script'   => $render_script,
                        'acfe_flexible_thumbnail'       => $layout_thumbnail,
                        'acfe_flexible_settings'        => $configuration,
                        'acfe_flexible_settings_size'   => $modal_size,
                        'min'                           => '',
                        'max'                           => '',
                    ];

                    // Store group keys for meta box on mirror flexible
                    $group_keys[] = $field_group['key'];
                }
            }

            return array(
                'layouts'    => $layouts,
                'group_keys' => $group_keys,
            );
        }

        /**
         * Parse all field groups and show only those for current screen
         *
         * @param $field
         *
         * @return mixed
         */
        public static function prepare_flexible_field( $field ) {
            // If no layouts, return
            if ( empty( $field['layouts'] ) && strpos( $field['_name'], self::get_flexible_field_name() ) === 0 ) {
                self::$show_pattern_notice = true;

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
                        'user_form' => self::$user_view,
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
                if ( !self::get_field_group_visibility( $field_group, $args ) ) {
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
         * Returns true if the given field group's location rules match the given $args
         *
         * @see ACF's acf_get_field_group_visibility()
         *
         * @param $field_group
         * @param array $args
         *
         * @return bool
         */
        public static function get_field_group_visibility( $field_group, $args = array() ) {
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
         * Get locations of mirror flexible
         *
         * @param $locations
         *
         * @return mixed
         */
        public function flexible_locations( $locations ) {
            // Get field group
            $mirror = acf_get_field_group( PIP_Flexible_Mirror::get_flexible_mirror_group_key() );

            // If field group doesn't exist, return
            if ( !$mirror ) {
                return $locations;
            }

            // Replace main flexible's locations with mirror flexible's locations
            $locations = $mirror['location'];

            return $locations;
        }

        /**
         * Getter: $flexible_field_name
         * @return string
         */
        public static function get_flexible_field_name() {
            return self::$flexible_field_name;
        }

    }

    // Instantiate class
    new PIP_Flexible();
}

/**
 * Return flexible content
 *
 * @param bool|int $post_id
 *
 * @return false|string|void
 */
function the_pip_content( $post_id = false ) {
    echo get_pip_content( $post_id );
}

/**
 * Get flexible content
 *
 * @param bool|int $post_id
 *
 * @return false|string|void
 */
function get_pip_content( $post_id = false ) {
    return get_flexible( PIP_Flexible::get_flexible_field_name(), $post_id );
}
