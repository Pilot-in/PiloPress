<?php

if ( !class_exists( 'PIP_Components' ) ) {
    class PIP_Components {

        public static $post_type = 'pip-components';

        public function __construct() {
            add_action( 'init', array( $this, 'register_components' ) );
        }

        /**
         * Register components post type
         */
        public function register_components() {
            register_post_type( self::$post_type,
                array(
                    'label'               => __( 'Components', 'pilopress' ),
                    'labels'              => array(
                        'name'                     => _x( 'Components', 'pilopress' ),
                        'singular_name'            => _x( 'Component', 'pilopress' ),
                        'add_new'                  => _x( 'Add new', 'pilopress' ),
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
                        'featured_image'           => _x( 'Featured image', 'pilopress' ),
                        'set_featured_image'       => _x( 'Set featured image', 'pilopress' ),
                        'remove_featured_image'    => _x( 'Remove featured image', 'pilopress' ),
                        'use_featured_image'       => _x( 'Use as featured image', 'pilopress' ),
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
                    'public'              => true,
                    'has_archive'         => true,
                    'show_ui'             => true,
                    'show_in_menu'        => false,
                    'exclude_from_search' => true,
                    'publicly_queryable'  => false,
                    'show_in_nav_menus'   => false,
                    'show_in_rest'        => false,
                    'menu_position'       => 83,
                    'menu_icon'           => 'dashicons-layout',
                    'supports'            => array( 'title', 'revisions' ),
                )
            );
        }
    }

    // Instantiate class
    new PIP_Components();
}


if ( !function_exists( 'have_component' ) ) {

    // Initiate component globals
    $pip_component_i      = 0;
    $component_loop_setup = false;
    $component_values     = array();

    /**
     * Initiate/end component loop
     *
     * @param $selector
     * @param bool $post_id
     *
     * @return bool
     */
    function have_component( $selector, $post_id = false ) {

        global $pip_component_i, $component_loop_setup, $component_values;

        // Store preview post ID
        $instance    = acf_get_instance( 'ACF_Local_Meta' );
        $previous_id = $instance->post_id;

        // Initiate loop
        if ( $pip_component_i == 0 ) {

            // Setup loop
            $values = get_sub_field( $selector, false );

            // Get values
            $component_values = $values ? $values : $component_values;

            // Fake wrapper field
            $field_key = 'field_component_wrapper';

            // Get sub fields
            $sub_fields = array();
            foreach ( $component_values as $k => $v ) {
                $sub_fields[] = array(
                    'key'  => $k,
                    'type' => 'text',
                );
            }

            // Create fake field
            acf_add_local_field( array(
                'key'        => $field_key,
                'type'       => 'group',
                'sub_fields' => $sub_fields,
            ) );

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
