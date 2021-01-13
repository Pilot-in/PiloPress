<?php

if ( !class_exists( 'PIP_Field_Groups' ) ) {

    /**
     * Class PIP_Field_Groups
     */
    class PIP_Field_Groups {

        public function __construct() {

            // WP hooks
            add_action( 'current_screen', array( $this, 'current_screen' ) );

        }

        /**
         * Field groups screen
         */
        public function current_screen() {

            global $typenow;

            // If not field groups or layout(s) screen, return
            if ( $typenow !== 'acf-field-group' || pip_is_layout_screen() ) {
                return;
            }

            // List
            add_action( 'load-edit.php', array( $this, 'load_list' ) );

            // Single
            add_action( 'load-post.php', array( $this, 'load_single' ) );
            add_action( 'load-post-new.php', array( $this, 'load_single' ) );

        }

        /**
         * Layouts list
         */
        public function load_list() {

            // Get admin field groups class
            $acf_field_groups = acf_get_instance( 'ACF_Admin_Field_Groups' );

            // Browse all field groups
            foreach ( $acf_field_groups->sync as $key => $field_group ) {

                // If not a layout, skip
                if ( !pip_is_layout( $field_group ) ) {
                    continue;
                }

                // Remove from synced field groups
                unset( $acf_field_groups->sync[ $key ] );
            }

            add_action( 'pre_get_posts', array( $this, 'pre_get_posts' ) );
            add_filter( 'views_edit-acf-field-group', array( $this, 'views' ), 999 );

            // After sync
            if ( acf_maybe_get_GET( 'acfsynccomplete' ) ) {
                $this->maybe_redirect_to_layouts();
            }

            // After sync and redirection
            if ( acf_maybe_get_GET( 'sync_ok' ) ) {
                $this->show_notice_message();
            }

        }

        /**
         * Field group screen
         */
        public function load_single() {
            add_action( 'acf/field_group/admin_head', array( $this, 'metaboxes' ) );
        }

        /**
         * Pre get posts
         *
         * @param WP_Query $query
         */
        public function pre_get_posts( $query ) {

            // acf_maybe_get_GET( 'post_status' ) !== 'trash'

            // Remove layouts
            $query->set(
                'pip_post_content',
                array(
                    'compare' => 'NOT LIKE',
                    'value'   => 's:14:"_pip_is_layout";i:1',
                )
            );

            // Remove flexible
            $query->set( 'post__not_in', array( pip_get_flexible_mirror_group_id() ) );
        }

        /**
         * Views
         *
         * @param $views
         *
         * @return bool
         */
        public function views( $views ) {

            // Statuses
            $post_statuses = array(
                'all',
                'publish',
                'trash',
                'acf-disabled',
            );

            // Browse all statuses
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
                $url = add_query_arg(
                    array(
                        'post_type' => 'acf-field-group',
                    ),
                    admin_url( 'edit.php' )
                );

                // Set parameters
                switch ( $post_status ) {
                    case 'all':
                        $class = ( !acf_maybe_get_GET( 'post_status' ) ) ? 'current' : '';
                        $title = __( 'All', 'acf' );
                        $count = $query->found_posts;
                        break;

                    case 'publish':
                        $url   = add_query_arg( array( 'post_status' => 'publish' ), $url );
                        $class = ( acf_maybe_get_GET( 'post_status' ) === 'publish' ) ? 'current' : '';
                        $title = __( 'Active', 'acf' );
                        $count = $query->found_posts;
                        break;

                    case 'trash':
                        $url   = add_query_arg( array( 'post_status' => 'trash' ), $url );
                        $class = ( acf_maybe_get_GET( 'post_status' ) === 'trash' ) ? 'current' : '';
                        $title = __( 'Trash', 'acf' );
                        $count = $query->found_posts;
                        break;

                    case 'acf-disabled':
                        $url   = add_query_arg( array( 'post_status' => 'acf-disabled' ), $url );
                        $class = ( acf_maybe_get_GET( 'post_status' ) === 'acf-disabled' ) ? 'current' : '';
                        $title = __( 'Inactive', 'acf' );
                        $count = $query->found_posts;
                        break;
                }

                if ( $count > 0 || $post_status === 'all' ) {
                    // Update counter
                    $views[ $post_status ] = '<a href="' . $url . '" class="' . $class . '">' . $title . ' <span class="count">(' . $count . ')</span></a>';
                } else {
                    // Remove counter
                    unset( $views[ $post_status ] );
                }
            }

            return $views;
        }

        /**
         * Redirect to layouts admin page
         */
        public function maybe_redirect_to_layouts() {

            // Bail early if no sync completed
            $field_groups = acf_maybe_get_GET( 'acfsynccomplete' );
            if ( !$field_groups ) {
                return;
            }

            // Get field groups
            $field_groups = explode( ',', $field_groups );

            // If no field group, return
            if ( !$field_groups ) {
                return;
            }

            // Browse all field groups
            foreach ( $field_groups as $field_group ) {

                // If not layout, skip
                if ( !pip_is_layout( $field_group ) ) {
                    continue;
                }

                // Build URL
                $url = add_query_arg(
                    array(
                        'post_type' => 'acf-field-group',
                        'layouts'   => 1,
                        'sync_ok'   => acf_maybe_get_GET( 'acfsynccomplete' ),
                    ),
                    admin_url( 'edit.php' )
                );

                // Redirect
                wp_safe_redirect( $url );
                exit();

            }

        }

        /**
         * Show notice message after sync
         */
        public function show_notice_message() {

            $sync_field_groups = acf_maybe_get_GET( 'sync_ok' );
            if ( !$sync_field_groups ) {
                return;
            }

            // Explode
            $sync_field_groups = explode( ',', $sync_field_groups );
            $total             = count( $sync_field_groups );

            // Generate text
            // translators: Number of layouts synchronised
            $text = sprintf( _n( '%s layout synchronised.', '%s layouts synchronised.', $total, 'acf' ), $total );

            // Add links to text
            $links = array();
            if ( $sync_field_groups ) {
                foreach ( $sync_field_groups as $id ) {
                    $links[] = '<a href="' . get_edit_post_link( $id ) . '">' . get_the_title( $id ) . '</a>';
                }
            }
            $text .= ' ' . implode( ', ', $links );

            // Add notice
            acf_add_admin_notice( $text, 'success' );

        }

        /**
         * Remove Pilo'Press meta boxes
         */
        public function metaboxes() {

            // Remove Pilo'Press Layouts Categories / Collections Metaboxes
            remove_meta_box( 'acf-layouts-categorydiv', 'acf-field-group', 'side' );
            remove_meta_box( 'acf-layouts-collectiondiv', 'acf-field-group', 'side' );

        }

    }

    acf_new_instance( 'PIP_Field_Groups' );

}
