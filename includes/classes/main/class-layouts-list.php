<?php

if ( !class_exists( 'PIP_Layouts_List' ) ) {

    /**
     * Class PIP_Layouts_List
     */
    class PIP_Layouts_List {

        /**
         * PIP_Layouts_List constructor.
         */
        public function __construct() {

            // Current Screen
            add_action( 'current_screen', array( $this, 'current_screen' ) );

        }

        /**
         * Current Screen
         */
        public function current_screen() {

            // If not layout(s) screen, return
            if ( !pip_is_layout_screen() ) {
                return;
            }

            // List
            add_action( 'load-edit.php', array( $this, 'load_list' ) );
        }

        /**
         * List
         */
        public function load_list() {

            // Get admin field groups class
            $acf_field_groups = acf_get_instance( 'ACF_Admin_Field_Groups' );

            // Browse all field groups
            foreach ( $acf_field_groups->sync as $key => $field_group ) {

                // If layout, skip
                if ( pip_is_layout( $field_group ) ) {
                    continue;
                }

                // Remove field group
                unset( $acf_field_groups->sync[ $key ] );
            }

            // Hooks
            add_action( 'pre_get_posts', array( $this, 'pre_get_posts' ) );
            add_filter( 'display_post_states', array( $this, 'post_states' ), 20 );
            add_filter( 'views_edit-acf-field-group', array( $this, 'views' ), 999 );

            // Remove ACF Extended: Field Group Category Column
            remove_filter( 'manage_edit-acf-field-group_columns', 'acfe_field_group_category_column', 11 );
            remove_action( 'manage_acf-field-group_posts_custom_column', 'acfe_field_group_category_column_html', 10 );

        }

        /**
         * Pre Get posts
         *
         * @param WP_Query $query
         */
        public function pre_get_posts( $query ) {

            // Layouts view
            $query->set(
                'pip_post_content',
                array(
                    'compare' => 'LIKE',
                    'value'   => 's:14:"_pip_is_layout";i:1',
                )
            );

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
            if ( isset( $states['acf-disabled'] ) ) {
                unset( $states['acf-disabled'] );
            }

            return $states;
        }

        /**
         * Views
         *
         * @param $views
         *
         * @return bool
         */
        public function views( $views ) {

            // Field Groups Categories
            foreach ( $views as $key => $val ) {

                // If not a category, skip
                if ( strpos( $key, 'category-' ) !== 0 ) {
                    continue;
                }

                // Remove from views
                unset( $views[ $key ] );
            }

            // Others views
            unset( $views['publish'] );
            unset( $views['acfe-third-party'] );
            unset( $views['acf-disabled'] );
            unset( $views['acfe-local'] );

            // Update Sync
            if ( isset( $views['sync'] ) ) {
                preg_match( '/href="([^\"]*)"/', $views['sync'], $url );

                $views['sync'] = str_replace( $url[1], esc_url( $url[1] . '&layouts=1' ), $views['sync'] );
            }

            // Update Post Statuses
            $post_statuses = array(
                'all',
                'trash',
            );

            // Browse all statuses
            foreach ( $post_statuses as $post_status ) {
                $class = null;
                $count = null;
                $title = null;

                // Get all field groups ids
                $args = array(
                    'post_type'        => 'acf-field-group',
                    'posts_per_page'   => 1,
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
                $url = add_query_arg(
                    array(
                        'post_type' => 'acf-field-group',
                        'layouts'   => 1,
                    ),
                    admin_url( 'edit.php' )
                );

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

    }

    acf_new_instance( 'PIP_Layouts_List' );

}
