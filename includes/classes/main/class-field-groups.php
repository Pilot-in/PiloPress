<?php

if ( !class_exists( 'PIP_Field_Groups' ) ) {

    /**
     * Class PIP_Field_Groups
     */
    class PIP_Field_Groups {

        public function __construct() {

            add_action( 'current_screen', array( $this, 'current_screen' ) );

        }

        function current_screen(){

            global $typenow;

            if( $typenow !== 'acf-field-group' || pip_is_layout_screen()) {
                return;
            }

            // List
            add_action( 'load-edit.php', array( $this, 'load_list' ) );

            // Single
            add_action( 'load-post.php', array( $this, 'load_single' ) );
            add_action( 'load-post-new.php', array( $this, 'load_single' ) );

        }

        function load_list(){

            add_filter( 'views_edit-acf-field-group', array( $this, 'edit_views' ), 999 );

        }

        function load_single(){

            add_action( 'acf/field_group/admin_head', array( $this, 'metaboxes' ) );

        }

        function edit_views(){

            // Update counters for field groups page
            //$this->update_field_groups_counters( $views );
            //pip_layouts_update_sync_counters( $views, false );

        }

        /**
         * Update counters for ACF Field Groups page
         *
         * @param $views
         */
        public function update_field_groups_counters( &$views ) {

            $post_statuses = array(
                'all',
                'publish',
                'acf-disabled',
            );

            foreach ( $post_statuses as $post_status ) {
                $class = null;
                $count = null;
                $title = null;

                // Get all field groups ids
                $args = array(
                    'post_type'        => 'acf-field-group',
                    'fields'           => 'ids',
                    'suppress_filters' => 0,
                    'pip_post_content' => array(
                        'compare' => 'NOT LIKE',
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
                    'post_type' => 'acf-field-group',
                ), admin_url( 'edit.php' ) );

                // Set parameters
                switch ( $post_status ) {
                    case 'all':
                        $class = ( !acf_maybe_get_GET( 'post_status' ) ) ? 'current' : '';
                        $title = 'All';
                        $count = $query->found_posts;
                        break;
                    case 'publish':
                        $url   = add_query_arg( array( 'post_status' => 'publish' ), $url );
                        $class = ( acf_maybe_get_GET( 'post_status' ) === 'publish' ) ? 'current' : '';
                        $title = 'Active';
                        $count = $query->found_posts;
                        break;
                    case 'acf-disabled':
                        $url   = add_query_arg( array( 'post_status' => 'acf-disabled' ), $url );
                        $class = ( acf_maybe_get_GET( 'post_status' ) === 'acf-disabled' ) ? 'current' : '';
                        $title = 'Inactive';
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

        function metaboxes(){

            // Remove Pilo'Press Layotus Categories / Collections Metaboxes
            remove_meta_box( 'acf-layouts-categorydiv', 'acf-field-group', 'side' );
            remove_meta_box( 'acf-layouts-collectiondiv', 'acf-field-group', 'side' );

        }

    }

    acf_new_instance( 'PIP_Field_Groups' );

}
