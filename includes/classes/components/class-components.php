<?php

if ( !class_exists( 'PIP_Components' ) ) {

    /**
     * Class PIP_Components
     */
    class PIP_Components {

        /**
         * Post type slug
         *
         * @var string
         */
        public $post_type = 'pip-components';

        public function __construct() {

            // WP hooks
            add_action( 'init', array( $this, 'register_components' ) );

            // ACF hooks
            add_filter( 'acf/location/rule_values/post_type', array( $this, 'remove_component_from_post_types' ) );
            add_filter( 'acf/location/rule_values/post', array( $this, 'remove_component_from_posts' ) );
            add_filter( 'acf/get_post_types', array( $this, 'remove_component_from_acf_post_types' ), 10, 2 );

            // ACF hooks - Component location rule
            add_filter( 'acf/location/rule_types', array( $this, 'location_types' ) );
            add_filter( 'acf/location/rule_match/' . $this->post_type, array( $this, 'location_match' ), 10, 3 );
            add_filter( 'acf/location/rule_values/' . $this->post_type, array( $this, 'location_values' ), 10, 2 );

            // ACF hooks - Component like location rule
            add_filter( 'acf/location/rule_operators/' . $this->post_type . '-like', array( $this, 'like_location_operators' ), 10, 2 );
            add_filter( 'acf/location/rule_match/' . $this->post_type . '-like', array( $this, 'like_location_match' ), 10, 3 );
            add_filter( 'acf/location/rule_values/' . $this->post_type . '-like', array( $this, 'like_location_values' ), 10, 2 );
        }

        /**
         * Register components post type
         */
        public function register_components() {

            register_post_type(
                $this->post_type,
                array(
                    'label'               => __( 'Components', 'pilopress' ),
                    'labels'              => array(
                        'name'                     => __( 'Components', 'pilopress' ),
                        'singular_name'            => __( 'Component', 'pilopress' ),
                        'add_new'                  => __( 'Add new', 'pilopress' ),
                        'add_new_item'             => __( 'Add new component', 'pilopress' ),
                        'edit_item'                => __( 'Edit component', 'pilopress' ),
                        'new_item'                 => __( 'New component', 'pilopress' ),
                        'view_item'                => __( 'View component', 'pilopress' ),
                        'view_items'               => __( 'View components', 'pilopress' ),
                        'search_items'             => __( 'Search components', 'pilopress' ),
                        'not_found'                => __( 'No components found.', 'pilopress' ),
                        'not_found_in_trash'       => __( 'No components found in Trash.', 'pilopress' ),
                        'parent_item_colon'        => __( 'Parent page:', 'pilopress' ),
                        'all_items'                => __( 'All components', 'pilopress' ),
                        'archives'                 => __( 'Component archives', 'pilopress' ),
                        'attributes'               => __( 'Component attributes', 'pilopress' ),
                        'insert_into_item'         => __( 'Insert into component', 'pilopress' ),
                        'uploaded_to_this_item'    => __( 'Uploaded to this component', 'pilopress' ),
                        'featured_image'           => __( 'Featured image', 'pilopress' ),
                        'set_featured_image'       => __( 'Set featured image', 'pilopress' ),
                        'remove_featured_image'    => __( 'Remove featured image', 'pilopress' ),
                        'use_featured_image'       => __( 'Use as featured image', 'pilopress' ),
                        'filter_items_list'        => __( 'Filter components list', 'pilopress' ),
                        'items_list_navigation'    => __( 'Components list navigation', 'pilopress' ),
                        'items_list'               => __( 'Components list', 'pilopress' ),
                        'item_published'           => __( 'Component published.', 'pilopress' ),
                        'item_published_privately' => __( 'Component published privately.', 'pilopress' ),
                        'item_reverted_to_draft'   => __( 'Component reverted to draft.', 'pilopress' ),
                        'item_scheduled'           => __( 'Component scheduled.', 'pilopress' ),
                        'item_updated'             => __( 'Component updated.', 'pilopress' ),
                    ),
                    'description'         => __( "Your Pilo'Press components", 'pilopress' ),
                    'public'              => false,
                    'has_archive'         => false,
                    'show_ui'             => true,
                    'show_in_menu'        => false,
                    'exclude_from_search' => true,
                    'publicly_queryable'  => false,
                    'show_in_nav_menus'   => false,
                    'show_in_rest'        => false,
                    'rewrite'             => false,
                    'menu_position'       => 83,
                    'menu_icon'           => 'dashicons-layout',
                    'supports'            => array( 'title', 'revisions' ),
                )
            );
        }

        /**
         * Remove Component from post types list
         *
         * @param $choices
         *
         * @return mixed
         */
        public function remove_component_from_post_types( $choices ) {

            // Remove component
            unset( $choices[ $this->post_type ] );

            return $choices;
        }

        /**
         * Remove Components from posts list
         *
         * @param $choices
         *
         * @return mixed
         */
        public function remove_component_from_posts( $choices ) {

            // Get post type labels
            $post_type = get_post_type_labels( get_post_type_object( $this->post_type ) );

            // Remove components
            unset( $choices[ $post_type->singular_name ] );

            return $choices;
        }

        /**
         * Remove Components from acf_get_post_types()
         *
         * @param $post_types
         * @param $args
         *
         * @return mixed
         */
        public function remove_component_from_acf_post_types( $post_types, $args ) {

            $key = array_search( $this->post_type, $post_types, true );

            // If component key found, unset it
            if ( $key ) {
                unset( $post_types[ $key ] );
            }

            return $post_types;
        }

        /**
         * Add component rule
         *
         * @param $choices
         *
         * @return mixed
         */
        public function location_types( $choices ) {

            // Get post type labels
            $post_type = get_post_type_labels( get_post_type_object( $this->post_type ) );

            // Add component option
            $choices["Pilo'Press"][ $this->post_type ] = $post_type->singular_name;

            // Add component like option
            $choices["Pilo'Press"][ $this->post_type . '-like' ] = $post_type->singular_name . ' Like';

            return $choices;
        }

        /**
         * Component rule values
         *
         * @param $values
         * @param $rule
         *
         * @return array
         */
        public function location_values( $values, $rule ) {

            // Get posts grouped by
            $posts = get_posts(
                array(
                    'post_type'      => $this->post_type,
                    'posts_per_page' => - 1,
                )
            );

            // Add "all" option
            $values = array(
                'all' => __( 'All', 'acf' ),
            );

            // Build choices array
            if ( !empty( $posts ) ) {
                // Add posts
                foreach ( $posts as $post ) {
                    $values[ $post->ID ] = $post->post_title;
                }
            }

            return $values;
        }

        /**
         * Component rule matches
         *
         * @param $result
         * @param $rule
         * @param $screen
         *
         * @return bool
         */
        public function location_match( $result, $rule, $screen ) {
            global $current_screen;

            // Get post ID
            $post_id = acf_maybe_get( $screen, 'post_id' );

            // If no post, return
            if ( !$post_id ) {
                return false;
            }

            if ( $rule['value'] === 'all' ) {
                // Allow "all" to match any value.
                $match = $current_screen->post_type === 'pip-components';

            } else {
                // Compare all other values.
                $match = (int) $post_id === (int) $rule['value'];
            }

            // Allow for "!=" operator.
            if ( $rule['operator'] === '!=' ) {
                $match = !$match;
            }

            // Contains operator
            if ( $rule['operator'] === 'contains' ) {

                $post_name = get_post_field( 'post_title', $post_id );
                if ( !$post_name ) {
                    return false;
                }

                // Compare
                return ( stripos( $post_name, $rule['value'] ) !== false );
            }

            return $match;
        }

        /**
         * Component rule values
         *
         * @param $values
         * @param $rule
         *
         * @return array
         */
        public function like_location_values( $values, $rule ) {

            // If not ACF screen and not ACF Ajax for location rules, return
            if ( !acf_is_screen( 'acf-field-group' ) && !acf_is_ajax( 'acf/field_group/render_location_rule' ) ) {
                return array(
                    $rule['value'] => $rule['value'],
                );
            }

            ob_start();

            acf_render_field(
                array(
                    'type'   => 'text',
                    'name'   => 'value',
                    'prefix' => 'acf_field_group[location][' . $rule['group'] . '][' . $rule['id'] . ']',
                    'value'  => isset( $rule['value'] ) ? $rule['value'] : '',
                )
            );

            // Return text field and abort to hide select field
            return ob_get_clean();
        }

        /**
         * Component rule matches
         *
         * @param $result
         * @param $rule
         * @param $screen
         *
         * @return bool
         */
        public function like_location_match( $result, $rule, $screen ) {

            // Get post ID
            $post_id = acf_maybe_get( $screen, 'post_id' );

            // If no post, return
            if ( !$post_id ) {
                return false;
            }

            // If no post title, return
            $post_title = get_post_field( 'post_title', $post_id );
            if ( !$post_title ) {
                return false;
            }

            // Check if match
            switch ( $rule['operator'] ) {
                case 'contains':
                    $result = ( stripos( $post_title, $rule['value'] ) !== false );
                    break;
                case '!contains':
                    $result = ( stripos( $post_title, $rule['value'] ) === false );
                    break;
                case 'starts':
                    $result = ( stripos( $post_title, $rule['value'] ) === 0 );
                    break;
                case '!starts':
                    $result = ( stripos( $post_title, $rule['value'] ) !== 0 );
                    break;
                case 'ends':
                    $result = ( acfe_ends_with( $post_title, $rule['value'] ) );
                    break;
                case '!ends':
                    $result = ( !acfe_ends_with( $post_title, $rule['value'] ) );
                    break;
                case 'regex':
                    $result = ( preg_match( '/' . $rule['value'] . '/', $post_title ) );
                    break;
                case '!regex':
                    $result = ( !preg_match( '/' . $rule['value'] . '/', $post_title ) );
                    break;
            }

            // Compare
            return $result;

        }

        /**
         * Component rule operators
         *
         * @param $operators
         * @param $rule
         *
         * @return mixed
         */
        public function like_location_operators( $operators, $rule ) {

            // Reset operators
            $operators = array();

            // Add operator
            $operators['contains']  = __( 'contains', 'acf' );
            $operators['!contains'] = __( "doesn't contains", 'acf' );
            $operators['starts']    = __( 'starts with', 'acf' );
            $operators['!starts']   = __( "doesn't starts with", 'acf' );
            $operators['ends']      = __( 'ends with', 'acf' );
            $operators['!ends']     = __( "doesn't ends with", 'acf' );
            $operators['regex']     = __( 'matches regex', 'acf' );
            $operators['!regex']    = __( "doesn't matches regex", 'acf' );


            return $operators;
        }

        /**
         * Check if post is a component
         *
         * @param $post
         *
         * @return bool
         */
        public function is_component( $post ) {

            $is_component = false;

            // Get post
            $post = get_post( $post );
            if ( !$post ) {
                return $is_component;
            }

            // Get post type
            $post_type = get_post_type( $post );
            if ( $post_type && $post_type === $this->post_type ) {
                $is_component = true;
            }

            return $is_component;
        }

    }

    // Instantiate class
    acf_new_instance( 'PIP_Components' );
}

if ( !function_exists( 'have_component' ) ) {

    // Component globals
    global $pip_component_i, $component_loop_setup, $component_values;

    // Initiate component globals
    $pip_component_i      = 0;
    $component_loop_setup = false;
    $component_values     = array();

    /**
     * Initiate/end component loop
     *
     * @param      $selector
     * @param bool $post_id
     *
     * @return bool
     */
    function have_component( $selector, $post_id = false ) {

        // Component globals
        global $pip_component_i, $component_loop_setup, $component_values;

        // Store preview post ID
        $instance    = acf_get_instance( 'ACF_Local_Meta' );
        $previous_id = $instance->post_id;

        // Initiate loop
        if ( $pip_component_i === 0 ) {

            // Setup loop
            $values = get_sub_field( $selector, false );

            // Get values
            $component_values = $values ? $values : $component_values;

            // Fake wrapper field
            $field_key = 'field_component_wrapper_' . $selector;

            // Get sub fields
            $sub_fields = array();
            foreach ( $component_values as $key => $value ) {
                $sub_fields[] = array(
                    'key'  => $key,
                    'type' => 'text',
                );
            }

            // Create fake field
            acf_add_local_field(
                array(
                    'key'        => $field_key,
                    'type'       => 'group',
                    'sub_fields' => $sub_fields,
                )
            );

            // Wrap values
            $values = array(
                $field_key => $values,
            );

            // If not already setup, setup meta
            if ( !$component_loop_setup ) {
                acf_setup_meta( $values, 'pip_component', true );
                $component_loop_setup = true;
            }

            // Continue loop
            return have_rows( $field_key );
        }

        // Reset meta and post ID
        acf_reset_meta( 'pip_component' );
        $instance->post_id = $previous_id;

        // Reset globals
        $pip_component_i      = 0;
        $component_loop_setup = false;

        // Stop loop
        return false;
    }
}

if ( !function_exists( 'the_component' ) ) {

    /**
     * Increment component loop
     */
    function the_component() {

        global $pip_component_i;
        $pip_component_i ++;

        return the_row();
    }
}
