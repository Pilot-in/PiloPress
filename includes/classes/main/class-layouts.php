<?php

if ( !class_exists( 'PIP_Layouts' ) ) {

    /**
     * Class PIP_Layouts
     */
    class PIP_Layouts {

        /**
         * Construct
         */
        public function __construct() {

            // WP hooks
            add_action( 'current_screen', array( $this, 'current_screen' ) );

            // ACF hooks
            add_filter( 'acf/load_field_groups', array( $this, 'remove_layouts_from_field_groups' ) );
        }

        /**
         * Current Screen
         */
        public function current_screen() {

            // If not layout(s) screen, return
            if ( !pip_is_layout_screen() ) {
                return;
            }

            $post_type = get_post_type_object( 'acf-field-group' );

            // Change title on flexible edition page
            $post_type->labels->name         = __( 'Layouts', 'pilopress' );
            $post_type->labels->edit_item    = __( 'Edit Layout', 'pilopress' );
            $post_type->labels->add_new_item = __( 'Add New Layout', 'pilopress' );

        }

        /**
         * Is Layout Screen
         */
        public function is_layout_screen() {

            global $typenow;

            // If not field groups page, return
            if ( $typenow !== 'acf-field-group' ) {
                return false;
            }

            // Get screens
            $is_layout_list   = acf_is_screen( 'edit-acf-field-group' ) && acf_maybe_get_GET( 'layouts' ) === '1';
            $is_layout_single = acf_is_screen( 'acf-field-group' );

            if ( $is_layout_list ) {

                // Layout list
                return true;

            } elseif ( $is_layout_single ) {

                // Check if layout single page
                $is_layout_single_new  = acf_maybe_get_GET( 'layout' ) === '1';
                $is_layout_single_edit = $this->is_layout( acf_maybe_get_GET( 'post' ) );
                $is_layout_single_save = isset( $_REQUEST['acf_field_group']['_pip_is_layout'] );

                // Layout single
                if ( $is_layout_single_new || $is_layout_single_edit || $is_layout_single_save ) {
                    return true;
                }
            }

            return false;
        }

        /**
         * Is field group a layout
         *
         * @param $post
         *
         * @return false|mixed|null
         */
        public function is_layout( $post ) {

            // Store post
            $field_group = $post;

            // If ID/Key then Get Field Group
            if ( !is_array( $post ) ) {
                $field_group = acf_get_field_group( $post );
            }

            // Field Group not found
            if ( !$field_group ) {
                return false;
            }

            // Check Layout setting
            return acf_maybe_get( $field_group, '_pip_is_layout', false );
        }

        /**
         * Get Layouts
         *
         * @param bool $filter
         *
         * @return array
         */
        public function get_layouts( $filter = false ) {

            $layouts = array();

            // Get field groups
            $field_groups = acf_get_field_groups();

            // If no field group, return
            if ( !$field_groups ) {
                return $layouts;
            }

            // Browse all field groups
            foreach ( $field_groups as $field_group ) {

                // If not a layout, skip
                if ( !$this->is_layout( $field_group ) ) {
                    continue;
                }

                // Store field group
                $layouts[] = $field_group;
            }

            // If there's a filter, return layouts with filter applied
            if ( $filter ) {
                return wp_list_pluck( $layouts, $filter );
            }

            return $layouts;
        }

        /**
         * Get Layout
         *
         * @param $post
         *
         * @return array|false|mixed|void
         */
        public function get_layout( $post ) {

            $field_group = $post;

            // If ID/Key then Get Field Group
            if ( !is_array( $post ) ) {
                $field_group = acf_get_field_group( $post );
            }

            // Field Group not found
            if ( !$field_group || !$this->is_layout( $field_group ) ) {
                return false;
            }

            return $field_group;
        }

        /**
         * Get all layouts CSS files content
         *
         * @return string
         */
        public function get_layouts_css() {

            $css = '';

            // Get layouts CSS files
            $layouts_css_files = glob( PIP_THEME_LAYOUTS_PATH . '*/*.css' );

            // If no CSS files, return
            if ( !$layouts_css_files ) {
                return $css;
            }

            // Store CSS contents
            foreach ( $layouts_css_files as $layouts_css_file ) {

                $filesystem = PIP_Main::get_wp_filesystem();
                $css_file   = $filesystem->get_contents( $layouts_css_file );

                // If no CSS, skip
                if ( !$css_file ) {
                    continue;
                }

                $css .= $css_file;
            }

            return $css;
        }

        /**
         * Get layouts by location
         *
         * @param array $args
         *
         * @return array
         */
        public function get_layouts_by_location( array $args ) {

            $layouts      = array();
            $pip_flexible = acf_get_instance( 'PIP_Flexible' );

            // Get layout keys
            $layout_keys = $pip_flexible->layout_group_keys;
            if ( !$layout_keys ) {
                return $layouts;
            }

            // Browse all layouts
            foreach ( $layout_keys as $layout_key ) {
                $layout = acf_get_field_group( $layout_key );
                if ( !isset( $layout['location'] ) ) {
                    continue;
                }

                // Layout not assign to location
                if ( !$pip_flexible->get_field_group_visibility( $layout, $args ) ) {
                    continue;
                }

                $layouts[] = $layout;
            }

            return $layouts;
        }

        /**
         * Remove layouts from ACF field groups on ACF Tools page
         *
         * @param $field_groups
         *
         * @return array|mixed
         */
        public function remove_layouts_from_field_groups( $field_groups ) {
            // If no field group, return
            if ( !$field_groups ) {
                return array();
            }

            // If not ACF Tools page, return
            if ( acf_maybe_get_GET( 'page' ) !== 'acf-tools' ) {
                return $field_groups;
            }

            // Browse all field groups
            foreach ( $field_groups as $key => $field_group ) {

                // If it's a layout, remove it from field groups array
                if ( pip_is_layout( $field_group ) ) {
                    unset( $field_groups[ $key ] );
                }
            }

            return $field_groups;
        }

        /**
         * Get field group by field group key
         *
         * @param      $key
         * @param bool $ids_only
         *
         * @return WP_Post|null
         */
        public function get_field_group_by_key( $key, $ids_only = false ) {

            // Remove "layout_" prefix
            $key = str_replace( 'layout_', '', $key );

            // Main args
            $args = array(
                'post_type'        => 'acf-field-group',
                'pip_post_content' => array(
                    'compare' => 'LIKE',
                    'value'   => 's:16:"_pip_layout_slug";s:' . strlen( $key ) . ':"' . $key . '";',
                ),
            );

            // Maybe get only IDs
            if ( $ids_only ) {
                $args['fields'] = true;
            }

            // Get posts
            $query = new WP_Query( $args );

            // Return post object or null
            return $query->have_posts() ? acf_unarray( $query->get_posts() ) : null;
        }

    }

    acf_new_instance( 'PIP_Layouts' );

}

/**
 * Is layout(s) screen
 *
 * @return mixed
 */
function pip_is_layout_screen() {
    return acf_get_instance( 'PIP_Layouts' )->is_layout_screen();
}

/**
 * Is a layout
 *
 * @param $post
 *
 * @return mixed
 */
function pip_is_layout( $post ) {
    return acf_get_instance( 'PIP_Layouts' )->is_layout( $post );
}

/**
 * Get layouts
 *
 * @param false $filter
 *
 * @return mixed
 */
function pip_get_layouts( $filter = false ) {
    return acf_get_instance( 'PIP_Layouts' )->get_layouts( $filter );
}

/**
 * Get layout
 *
 * @param $post
 *
 * @return mixed
 */
function pip_get_layout( $post ) {
    return acf_get_instance( 'PIP_Layouts' )->get_layout( $post );
}

/**
 * Get layout CSS
 *
 * @return mixed
 */
function pip_get_layouts_css() {
    return acf_get_instance( 'PIP_Layouts' )->get_layouts_css();
}

/**
 * Get field group by field group Key
 *
 * @return WP_Post|null
 */
function pip_get_field_group_by_key( $key, $ids_only = false ) {
    return acf_get_instance( 'PIP_Layouts' )->get_field_group_by_key( $key, $ids_only );
}

if ( !function_exists( 'get_layout_config' ) ) {

    /**
     * Get layout configuration data
     *
     * @param string|null $layout_name
     * @return array
     */
    function get_layout_config( $layout_name = null ) {

        // Get layout name
        if ( !$layout_name ) {
            $layout_path = basename( __FILE__ );
            $layout_name = pathinfo( $layout_path, PATHINFO_FILENAME );
        }
        $field_group = PIP_Layouts_Single::get_layout_field_group_by_slug( $layout_name );

        // Get layout vars
        $layout_vars = acf_maybe_get( $field_group, 'pip_layout_var' );
        $css_vars    = array();
        if ( $layout_vars ) {
            foreach ( $layout_vars as $layout_var ) {
                $css_vars[ acf_maybe_get( $layout_var, 'pip_layout_var_key' ) ] = acf_maybe_get( $layout_var, 'pip_layout_var_value' );
            }
        }

        // Store values & merge it with layout vars
        $values = array( 'layout_name' => $layout_name );
        $values = array_merge( $values, $css_vars );
        $values = apply_filters( 'pip/layout/config', $values, $field_group, $layout_name );
        $values = apply_filters( "pip/layout/config/key=$layout_name", $values, $field_group, $layout_name ); // phpcs:ignore

        return $values;
    }
}

if ( !function_exists( 'get_layout_var' ) ) {

    /**
     * Get layout variable
     *
     * @param string $key
     * @return string
     */
    function get_layout_var( $key = '' ) {

        if ( !$key ) {
            return $key;
        }

        // Source of data
        $configuration = get_layout_config();

        // Value
        $value = acf_maybe_get( $configuration, $key );
        $value = apply_filters( 'pip/layout/var', $value );
        $value = apply_filters( "pip/layout/var/key=$key", $value ); // phpcs:ignore

        return $value;
    }
}
