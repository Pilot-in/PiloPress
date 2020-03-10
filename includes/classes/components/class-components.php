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
                    'show_ui'             => true,
                    'show_in_menu'        => true,
                    'exclude_from_search' => true,
                    'publicly_queryable'  => false,
                    'show_in_nav_menus'   => false,
                    'show_in_rest'        => false,
                    'menu_position'       => 62,
                    'menu_icon'           => 'dashicons-layout',
                    'supports'            => array( 'title', 'revisions' ),
                )
            );
        }
    }

    // Instantiate class
    new PIP_Components();
}
