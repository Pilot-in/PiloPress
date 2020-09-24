<?php

if( !class_exists( 'PIP_Layouts_List' ) ) {

    /**
     * Class PIP_Layouts_List
     */
    class PIP_Layouts_List {

        /*
         * Construct
         */
        public function __construct() {

            // Current Screen
            add_action( 'current_screen', array( $this, 'current_screen' ) );

        }

        /**
         * Current Screen
         */
        public function current_screen() {

            if( !pip_is_layout_screen() ) {
                return;
            }

            // List
            add_action( 'load-edit.php', array( $this, 'load_list' ) );

        }

        /*
         * List
         */
        function load_list() {

            // Remove ACF Disabled Visual State
            add_filter( 'display_post_states', array( $this, 'post_states' ), 20 );
            add_filter( 'views_edit-acf-field-group', array( $this, 'edit_views' ), 999 );

            // Remove ACF Extended: Field Group Category Column
            remove_filter( 'manage_edit-acf-field-group_columns', 'acfe_field_group_category_column', 11 );
            remove_action( 'manage_acf-field-group_posts_custom_column', 'acfe_field_group_category_column_html', 10 );

        }

        /**
         * Hide "Disabled" state
         *
         * @param $states
         *
         * @return mixed
         */
        public function post_states( $states ) {

            // Unset disabled state
            if( isset( $states['acf-disabled'] ) ) {

                unset( $states['acf-disabled'] );

            }

            return $states;

        }

        /**
         * Remove links on layouts page
         *
         * @param $views
         *
         * @return bool
         */
        public function edit_views( $views ) {

            // If layouts page, remove links and update counters
            //$this->update_layouts_counters( $views );
            //$this->update_sync_counters( $views );

            // Remove category terms counters
            $terms = get_terms( array(
                'taxonomy'   => 'acf-field-group-category',
                'hide_empty' => false,
                'fields'     => 'id=>slug',
            ) );

            if ( $terms ) {
                foreach ( $terms as $term ) {
                    unset( $views[ 'category-' . $term ] );
                }
            }

            // Remove useless counters
            unset( $views['publish'] );
            unset( $views['acfe-third-party'] );
            unset( $views['acf-disabled'] );
            unset( $views['acfe-local'] );

            return $views;

        }

        /**
         * Update counters for layouts page
         *
         * @param $views
         */
        public function update_layouts_counters( &$views ) {

            $post_statuses = array(
                'all',
                'trash',
            );

            foreach ( $post_statuses as $post_status ) {
                $class = null;
                $count = null;
                $title = null;

                // Get all field groups ids
                $args = array(
                    'post_type'        => 'acf-field-group',
                    'posts_per_page'   => - 1,
                    'fields'           => 'ids',
                    'suppress_filters' => 0,
                    'pip_post_content' => array(
                        'compare' => 'LIKE',
                        'value'   => 's:14:"_pip_is_layout";i:1',
                    ),
                );

                // If post_status not "all", add query arg
                if ( $post_status !== 'all' ) {
                    $args['post_status'] = $post_status;
                }
                $query = new WP_Query( $args );

                // Admin URL
                $url = add_query_arg( array(
                    'layouts'   => 1,
                    'post_type' => 'acf-field-group',
                ), admin_url( 'edit.php' ) );

                // Set parameters
                switch ( $post_status ) {
                    case 'all':
                        $class = ( !acf_maybe_get_GET( 'post_status' ) ) ? 'current' : '';
                        $title = 'All';
                        $count = $query->found_posts;
                        break;
                    case 'trash':
                        $url   = add_query_arg( array( 'post_status' => 'trash' ), $url );
                        $class = ( acf_maybe_get_GET( 'post_status' ) === 'trash' ) ? 'current' : '';
                        $title = 'Trash';
                        $count = $query->found_posts;
                        break;
                }

                if ( $count > 0 ) {
                    // Update counter
                    $views[ $post_status ] = '<a href="' . $url . '" class="' . $class . '">' . $title . ' <span class="count">(' . $count . ')</span></a>';
                } else {
                    // Remove counter
                    unset( $views[ $post_status ] );
                }

            }

        }

        /**
         * Update counters for sync available
         *
         * @param      $views
         * @param bool $is_layout
         */
        public function update_sync_counters( &$views, $is_layout = true ) {

            // Get field groups
            $field_groups = acf_get_field_groups();

            // If no field group, return
            if ( empty( $field_groups ) ) {
                return;
            }

            // Hide ACF counter
            unset( $views['sync'] );

            $pip_layouts = acf_get_instance( 'PIP_Layouts' );

            // Get field group
            $sync = array();
            foreach ( $field_groups as $field_group ) {

                // Get type
                $local    = acf_maybe_get( $field_group, 'local', false );
                $modified = acf_maybe_get( $field_group, 'modified', 0 );
                $private  = acf_maybe_get( $field_group, 'private', false );

                if ( $private || $local !== 'json' ) {

                    // Continue if private or not JSON
                    continue;

                } elseif ( !$field_group['ID'] || ( $modified && $modified > get_post_modified_time( 'U', true, $field_group['ID'], true ) ) ) {
                    // If not in DB or JSON newer than post

                    if ( $is_layout && $pip_layouts->is_layout( $field_group ) ) {

                        // Store layout
                        $sync[ $field_group['key'] ] = $field_group['title'];

                    } elseif ( !$is_layout && !$pip_layouts->is_layout( $field_group ) ) {

                        // Store non layout
                        $sync[ $field_group['key'] ] = $field_group['title'];

                    }
                }
            }

            // If there's field group to sync, add custom counter
            if ( count( $sync ) > 0 ) {

                // Admin URL
                $url = add_query_arg( array(
                    'post_type'   => 'acf-field-group',
                    'post_status' => 'sync',
                ), admin_url( 'edit.php' ) );
                if ( $is_layout ) {
                    $url = add_query_arg( array( 'layouts' => 1 ), $url );
                }

                // Maybe add current class
                $class = ( acf_maybe_get_GET( 'post_status' ) === 'sync' ) ? 'current' : '';

                // Update counter
                $views['sync'] = '<a href="' . $url . '" class="' . $class . '">' . __( 'Sync available', 'acf' ) . ' <span class="count">(' . count( $sync ) . ')</span></a>';
            }

        }

    }

    acf_new_instance( 'PIP_Layouts_List' );

}

function pip_layouts_update_sync_counters(&$views, $is_layout = true){

    return acf_get_instance('PIP_Layouts_List')->update_sync_counters($views, $is_layout);

}
