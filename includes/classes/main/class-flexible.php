<?php

if ( !class_exists( 'PIP_Flexible' ) ) {

    /**
     * Class PIP_Flexible
     */
    class PIP_Flexible {

        /**
         * Flexible field name
         *
         * @var string
         */
        public $flexible_field_name = 'pip_flexible';

        /**
         * Flexible group key
         *
         * @var string
         */
        public $flexible_group_key = 'group_pip_flexible_main';

        /**
         * User view
         *
         * @var string
         */
        public $user_view = 'edit';

        /**
         * Layout Group Keys
         *
         * @var array
         */
        public $layout_group_keys = array();

        /**
         * Layout Group Keys
         *
         * @var array
         */
        private $layouts = array();

        /**
         * Group Keys
         *
         * @var array
         */
        private $group_keys = array();

        /**
         * PIP_Flexible constructor.
         */
        public function __construct() {

            // WP hooks
            add_action( 'init', array( $this, 'init' ) );

            // ACF hooks
            add_filter( "acfe/flexible/thumbnail/name={$this->flexible_field_name}", array( $this, 'add_custom_thumbnail' ), 10, 3 );
            add_filter( "acf/prepare_field/name={$this->flexible_field_name}", array( $this, 'prepare_flexible_field' ), 20 );

            // ACFE hooks
            add_filter( 'acfe/flexible/layouts/icons', array( $this, 'custom_layout_actions' ), 10, 3 );
            add_filter( 'acfe/flexible/layouts/icons', array( $this, 'hide_some_actions' ), 25, 3 );

        }

        /**
         * Register main flexible field group
         * Add layouts to main flexible
         */
        public function init() {

            // Get layouts and group keys
            $this->set_layouts_and_group_keys();

            // Mirror
            $mirror = pip_get_flexible_mirror_group();

            // Locations
            $locations = apply_filters( 'pip/builder/locations', acf_maybe_get( $mirror, 'location', array() ) );

            // Field
            $field = array(
                'key'               => 'field_' . $this->flexible_field_name,
                'label'             => '',
                'name'              => $this->flexible_field_name,
                'type'              => 'flexible_content',
                'instructions'      => '',
                'required'          => 0,
                'conditional_logic' => 0,
                'wrapper'           => array(
                    'width' => '',
                    'class' => '',
                    'id'    => '',
                ),
                'layouts'           => $this->layouts,
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
                        'acfe_flexible_modal_title'      => acf_maybe_get( $mirror, 'title' ),
                        'acfe_flexible_modal_enabled'    => '1',
                        'acfe_flexible_modal_col'        => '6',
                        'acfe_flexible_modal_categories' => '1',
                    ),
                )
            );

            // Final Field
            $field = array_merge( $field, $field_args );

            // Hide on screen option
            $hide_on_screen = apply_filters( 'pip/builder/hide_on_screen', array( 'the_content' ) );

            // Register main flexible
            acf_add_local_field_group(
                array(
                    'key'                   => $this->flexible_group_key,
                    'title'                 => __( 'Builder', 'pilopress' ),
                    'fields'                => array( $field ),
                    'location'              => $locations,
                    'menu_order'            => 0,
                    'position'              => 'normal',
                    'style'                 => 'seamless',
                    'label_placement'       => 'top',
                    'instruction_placement' => 'label',
                    'hide_on_screen'        => $hide_on_screen,
                    'active'                => true,
                    'description'           => '',
                    'acfe_display_title'    => '',
                    'acfe_autosync'         => '',
                    'acfe_permissions'      => '',
                    'acfe_form'             => 0,
                    'acfe_meta'             => '',
                    'acfe_note'             => '',
                )
            );

        }

        /**
         * Set layouts and group keys
         *
         * @return void
         */
        public function set_layouts_and_group_keys() {

            $layouts      = array();
            $group_keys   = array();
            $field_groups = acf_get_field_groups();
            $counter      = pip_array_count_values_assoc( $field_groups, 'title' );

            // If no field groups, return
            if ( !$field_groups ) {
                return;
            }

            // Browse all field groups
            foreach ( $field_groups as $field_group ) {

                // If not a layout, skip
                if ( !pip_is_layout( $field_group ) ) {
                    continue;
                }

                // Layout data
                $title          = $field_group['title'];
                $name           = sanitize_title( $field_group['title'] );
                $layout_slug    = sanitize_title( acf_maybe_get( $field_group, '_pip_layout_slug', '' ) );
                $layout_uniq_id = 'layout_' . $layout_slug;

                // Path
                $file_path = PIP_THEME_LAYOUTS_PATH . $layout_slug . '/';
                $file_path = apply_filters( 'pip/layouts/file_path', $file_path, $field_group );

                // Get layout categories from field group
                $layout_categories = acf_maybe_get( $field_group, 'layout_categories' );
                $layout_categories = $layout_categories ? array_values( $layout_categories ) : array();

                // Get layout collections from field group
                $layout_collections = acf_maybe_get( $field_group, 'layout_collections' );
                $layout_collections = $layout_collections ? array_values( $layout_collections ) : array();

                // Allow user to by-pass condition
                $always_show_collection = apply_filters( 'pip/layouts/always_show_collection', false );

                // Add collection badge if two layouts have the same name
                if (
                    !wp_doing_ajax()
                    && !empty( $layout_collections )
                    && ( $counter[ $title ] > 1 || $always_show_collection )
                ) {
                    $title = '<div class="pip_collection">' . reset( $layout_collections ) . '</div>' . $title;
                }

                // Settings
                $render_layout    = $file_path . acf_maybe_get( $field_group, '_pip_render_layout', $layout_slug . '.php' );
                $render_script    = $file_path . acf_maybe_get( $field_group, '_pip_render_script', $layout_slug . '.js' );
                $layout_thumbnail = acf_maybe_get( $field_group, '_pip_thumbnail' );
                $configuration    = acf_maybe_get( $field_group, '_pip_configuration', array() );
                $modal_size       = acf_maybe_get( $field_group, '_pip_modal_size', array() );
                $layout_min       = acf_maybe_get( $field_group, '_pip_layout_min' );
                $layout_max       = acf_maybe_get( $field_group, '_pip_layout_max' );

                // Check if JS file exists before enqueue
                if ( !file_exists( $render_script ) ) {
                    $render_script = null;
                }

                // Get layout alignment
                switch ( $field_group['label_placement'] ) {
                    case 'top':
                        $display = 'block';
                        break;
                    case 'left':
                    default:
                        $display = 'row';
                        break;
                }

                // Store layout
                $layouts[ $layout_uniq_id ] = array(
                    'key'                           => $layout_uniq_id,
                    'name'                          => $name,
                    'label'                         => $title,
                    'display'                       => $display,
                    'sub_fields'                    => array(
                        array(
                            'key'               => 'field_clone_' . $layout_slug,
                            'label'             => $title,
                            'name'              => $name,
                            'type'              => 'clone',
                            'instructions'      => '',
                            'required'          => 0,
                            'conditional_logic' => 0,
                            'wrapper'           => array(
                                'width' => '',
                                'class' => '',
                                'id'    => '',
                            ),
                            'clone'             => array(
                                $field_group['key'],
                            ),
                            'display'           => 'seamless',
                            'layout'            => 'block',
                            'prefix_label'      => 0,
                            'prefix_name'       => 0,
                            'acfe_clone_modal'  => 0,
                        ),
                    ),
                    'acfe_flexible_category'        => $layout_categories,
                    'acfe_flexible_render_template' => $render_layout,
                    'acfe_flexible_render_style'    => '', // Empty for no enqueue
                    'acfe_flexible_render_script'   => $render_script,
                    'acfe_flexible_thumbnail'       => $layout_thumbnail,
                    'acfe_flexible_settings'        => $configuration,
                    'acfe_flexible_settings_size'   => $modal_size,
                    'min'                           => $layout_min,
                    'max'                           => $layout_max,
                    '_pip_field_group_id'           => acf_maybe_get( $field_group, 'ID' ),
                );

                // Store group keys for meta box on mirror flexible
                $group_keys[ $layout_uniq_id ] = $field_group['key'];

            }

            // Layouts
            $this->layouts           = $layouts;
            $this->group_keys        = $group_keys;
            $this->layout_group_keys = array_merge( $layouts, $group_keys );

        }

        /**
         * Get layouts and group keys
         *
         * @return array
         */
        public function get_layouts_and_group_keys() {

            return array(
                'layouts'    => $this->layouts,
                'group_keys' => $this->group_keys,
            );

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
            if ( !acf_maybe_get( $field, 'layouts' ) ) {
                return false;
            }

            // If AJAX, filters not needed
            if ( wp_doing_ajax() ) {

                // PILO_TODO: Fix min and max validation
                // Exception for attachments view in grid mode
                if (
                    acf_maybe_get_POST( 'action' ) !== 'query-attachments'
                    //  && acf_maybe_get_POST( 'action' ) !== 'acfe/flexible/models'
                    //  && acf_maybe_get_POST( 'action' ) !== 'acf/validate_save_post'
                ) {
                    return $field;
                }
            }

            // Initiate layouts to empty array for returns
            $layouts          = $field['layouts'];
            $field['layouts'] = array();

            // Get post_id and screen
            $screen  = acf_get_form_data( 'screen' );
            $post_id = acf_get_form_data( 'post_id' );

            // Second attempt to get screen
            if ( !$screen ) {

                // Menu item support
                $nav_menu_id = acf_get_data( 'nav_menu_id' );
                if ( $nav_menu_id ) {
                    $screen  = 'nav_menu';
                    $post_id = 'term_' . $nav_menu_id;
                }
            }

            /**
             * Extract ACF id from URL id
             *
             * @var $type string post type
             * @var $id   int|string post ID
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
                        'options_page' => acf_maybe_get_GET( 'page' ),
                    );
                    break;
                case 'nav_menu':
                    $args = array(
                        'screen'  => $screen,
                        'post_id' => $post_id,
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

            $pip_layouts = acf_get_instance( 'PIP_Layouts' );

            foreach ( $field_groups as $field_group ) {

                // If not layout, skip
                if ( !$pip_layouts->is_layout( $field_group ) ) {
                    continue;
                }

                // If current screen not included in field group location, skip
                if ( !$this->get_field_group_visibility( $field_group, $args ) ) {
                    continue;
                }

                // Sanitize name
                $field_group_name  = sanitize_title( acf_maybe_get( $field_group, '_pip_layout_slug' ) );
                $field_group_title = sanitize_title( acf_maybe_get( $field_group, 'title' ) );

                // Browse all layouts
                foreach ( $layouts as $key => $layout ) {

                    // If field group not in layouts, skip
                    if ( $layout['name'] !== $field_group_name && $layout['name'] !== $field_group_title ) {
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
         * Add custom thumbnail
         *
         * @param $thumbnail
         * @param $field
         *
         * @param $layout
         *
         * @return bool
         */
        public function add_custom_thumbnail( $thumbnail, $field, $layout ) {

            $layouts = acf_maybe_get( $field, 'layouts' );

            // If no layouts, return
            if ( !$layouts ) {
                return $thumbnail;
            }

            $layouts_groups_keys = $this->get_layouts_and_group_keys();
            $field_group_key     = $layouts_groups_keys['group_keys'][ $layout['key'] ];
            $field_group         = acf_get_field_group( $field_group_key );

            // Get file path thanks to layout slug
            $layout_slug = acf_maybe_get( $field_group, '_pip_layout_slug' );
            if ( !$layout_slug ) {
                return $thumbnail;
            }

            // Get layout thumbnail
            $layout_thumbnail = PIP_Layouts_Single::get_layout_thumbnail( $field_group );

            return acf_maybe_get( $layout_thumbnail, 'url' );
        }

        /**
         * Returns true if the given field group's location rules match the given $args
         *
         * @see ACF's acf_get_field_group_visibility()
         *
         * @param       $field_group
         * @param array $args
         *
         * @return bool
         */
        public function get_field_group_visibility( $field_group, $args = array() ) {

            // Check if location rules exist
            if ( isset( $field_group['location'] ) ) {

                // Get the current screen
                $screen = acf_get_location_screen( $args );

                // Loop through location groups.
                foreach ( $field_group['location'] as $group ) {

                    // If no rules, skip
                    if ( empty( $group ) ) {
                        continue;
                    }

                    // Loop through rules and determine if all rules match
                    $match_group = true;
                    foreach ( $group as $rule ) {
                        if ( !acf_match_location_rule( $rule, $screen, $field_group ) ) {
                            $match_group = false;
                            break;
                        }
                    }

                    // If this group matches, show the field group
                    if ( $match_group ) {
                        return true;
                    }
                }
            }

            return false;
        }

        /**
         * Add custom actions to layouts
         *
         * @param $icons
         * @param $layout
         * @param $field
         *
         * @return mixed
         */
        public function custom_layout_actions( $icons, $layout, $field ) {

            // Add actions only for Pilo'Press flexibles fields
            $field_name = acf_maybe_get( $field, '_name' );

            // Getting name from each Pilo'Press flexibles fields
            $pip_flexibles_names = array(
                $this->flexible_field_name,
                acf_get_instance( 'PIP_Flexible_Header' )->flexible_header_field_name,
                acf_get_instance( 'PIP_Flexible_Footer' )->flexible_footer_field_name,
            );

            $pip_flexibles_names = apply_filters( 'pip/flexible/flexibles_names', $pip_flexibles_names );

            if ( !in_array( $field_name, $pip_flexibles_names, true ) ) {
                return $icons;
            }

            // Capability
            $capability = apply_filters( 'pip/options/capability', acf_get_setting( 'capability' ) );

            // Check if user has rights to edit layouts
            if ( !current_user_can( $capability ) ) {
                return $icons;
            }

            // Edit layout link
            $field_group_id           = acf_maybe_get( $layout, '_pip_field_group_id' );
            $edit_link                = get_edit_post_link( $field_group_id );
            $icons['edit-pip-layout'] = '<a class="acf-icon dashicons dashicons-edit small light acf-js-tooltip" target="_blank" href="' . $edit_link . '" data-name="edit-pip-layout" title="' . __( 'Edit layout', 'pilopress' ) . '"></a>';

            // Add Move up and Move down buttons
            $icons['move-up']   = '<a class="acf-icon dashicons dashicons-arrow-up-alt small light acf-js-tooltip up" target="_blank" href="#" data-name="move-pip-layout" title="' . __( 'Move layout up', 'pilopress' ) . '"></a>';
            $icons['move-down'] = '<a class="acf-icon dashicons dashicons-arrow-down-alt small light acf-js-tooltip down" target="_blank" href="#" data-name="move-pip-layout" title="' . __( 'Move layout down', 'pilopress' ) . '"></a>';

            return apply_filters( 'pip/flexible/layouts/icons', $icons, $layout, $field );
        }

        /**
         * Hide buttons to avoid too many actions above layouts
         *
         * @param $icons
         * @param $layout
         * @param $field
         *
         * @return array|mixed
         */
        public function hide_some_actions( $icons, $layout, $field ) {

            // Hide actions only for Pilo'Press flexibles fields
            $field_name = acf_maybe_get( $field, '_name' );

            // Getting name from each Pilo'Press flexibles fields
            $pip_flexibles_names = array(
                $this->flexible_field_name,
                acf_get_instance( 'PIP_Flexible_Header' )->flexible_header_field_name,
                acf_get_instance( 'PIP_Flexible_Footer' )->flexible_footer_field_name,
            );

            $pip_flexibles_names = apply_filters( 'pip/flexible/flexibles_names', $pip_flexibles_names );

            if ( !in_array( $field_name, $pip_flexibles_names, true ) ) {
                return $icons;
            }

            // Capability
            $capability = apply_filters( 'pip/options/capability', acf_get_setting( 'capability' ) );

            // Check if user has rights to edit layouts
            if ( !current_user_can( $capability ) ) {
                return $icons;
            }

            // Get icons to hide
            $icons_to_hide = apply_filters( 'pip/flexible/layouts/icons/hide', array( 'add', 'copy', 'edit-pip-layout' ), $icons, $layout, $field );
            if ( !$icons_to_hide ) {
                return $icons;
            }

            // Separate buttons with a "more actions" button
            $visible_icons['more-actions'] = '<a class="acf-icon dashicons dashicons-ellipsis small light acf-js-tooltip" target="_blank" href="#" data-name="more-actions" title="' . __( 'More actions', 'pilopress' ) . '"></a>';

            // Add all icons
            $visible_icons += $icons;

            // Add class to hide icons
            $hidden_actions = array();
            foreach ( $icons_to_hide as $icon_to_hide ) {
                // Add class
                $icons[ $icon_to_hide ] = str_replace( 'class="', 'class="hide-icon ', $icons[ $icon_to_hide ] );

                // Remove hidden icon from visible ones
                unset( $visible_icons[ $icon_to_hide ] );

                // Move hidden icon to hidden ones
                $hidden_actions[ $icon_to_hide ] = $icons[ $icon_to_hide ];
            }

            // Return filtered and reorder icons array
            return $hidden_actions + $visible_icons;
        }

    }

    // Instantiate class
    acf_new_instance( 'PIP_Flexible' );
}

/**
 * Return flexible content
 *
 * @param false $post_id
 */
function the_pip_content( $post_id = false ) {

    // Display content
    echo get_pip_content( $post_id );
}

/**
 * Get flexible content
 *
 * @param bool|int $post_id
 *
 * @return string
 */
function get_pip_content( $post_id = false ) {

    // Fix post_id based on conditional tags
    $post_id = pip_get_formatted_post_id( $post_id );

    $header = '';
    $footer = '';
    $html   = '';

    // Maybe get pip header
    if ( !apply_filters( 'pip/header/remove', false ) ) {
        $header = get_pip_header( false );
    }

    $pip_flexible = acf_get_instance( 'PIP_Flexible' );

    // Get content
    $content = get_flexible( $pip_flexible->flexible_field_name, $post_id );

    // Maybe wrap content with locked content layouts
    $locked_content_post = PIP_Locked_Content::get_locked_content( $post_id );
    if ( $locked_content_post ) {
        $locked_content = get_flexible( $pip_flexible->flexible_field_name, $locked_content_post );
        $content        = $locked_content ? str_replace( '%%PIP_LOCKED_CONTENT%%', $content, $locked_content ) : $content;
    }

    // Maybe get pip footer
    if ( !apply_filters( 'pip/footer/remove', false ) ) {
        $footer = get_pip_footer( false );
    }

    // Concat
    $html .= $header ? $header : '';
    $html .= $content ? $content : '';
    $html .= $footer ? $footer : '';

    return $html;
}
