<?php

if ( !class_exists( 'PIP_Admin_Layouts' ) ) {
    class PIP_Admin_Layouts {
        public function __construct() {
            // WP hooks
            add_action( 'current_screen', array( $this, 'admin_layouts_page' ), 1 );
        }

        /**
         * Fire actions only on layouts page
         */
        public function admin_layouts_page() {
            // If not in admin acf field group listing, return
            if ( !is_admin() || !acf_is_screen( 'edit-acf-field-group' ) ) {
                return;
            }

            // Edit quick links
            add_filter( 'views_edit-acf-field-group', array( $this, 'edit_views' ), 999 );

            // Sync page
            if ( acf_maybe_get_GET( 'post_status' ) == 'sync' ) {
                add_filter( 'acf/load_field_groups', array( $this, 'filter_sync_field_groups' ) );
            }
        }

        /**
         * Remove links on layouts page
         *
         * @param $views
         *
         * @return bool
         */
        public function edit_views( $views ) {
            if ( acf_maybe_get_GET( 'layouts' ) == 1 ) {
                // If layouts page, remove links and update counters
                unset( $views['publish'] );
                unset( $views['acfe-third-party'] );
                unset( $views['acf-disabled'] );

                self::update_layouts_counters( $views );
                self::update_sync_counters( $views );
            } else {
                // Update counters for field groups page
                self::update_field_groups_counters( $views );
                self::update_sync_counters( $views, false );
            }

            return $views;
        }

        /**
         * Filter field groups to sync
         *
         * @param $field_groups
         *
         * @return mixed
         */
        public function filter_sync_field_groups( $field_groups ) {
            if ( acf_maybe_get_GET( 'layouts' ) == 1 ) {

                // Layouts page
                foreach ( $field_groups as $key => $field_group ) {
                    if ( !acf_maybe_get( $field_group, '_pip_is_layout' ) ) {
                        unset( $field_groups[ $key ] );
                    }
                }

            } elseif ( !acf_maybe_get_GET( 'layouts' ) ) {

                // ACF Field groups
                foreach ( $field_groups as $key => $field_group ) {
                    if ( acf_maybe_get( $field_group, '_pip_is_layout' ) === 1 ) {
                        unset( $field_groups[ $key ] );
                    }
                }
            }

            return $field_groups;
        }

        /**
         * Update counters for layouts page
         *
         * @param $views
         */
        private static function update_layouts_counters( &$views ) {
            // Get all field groups ids
            $args  = array(
                'post_type'        => 'acf-field-group',
                'posts_per_page'   => - 1,
                'fields'           => 'ids',
                'suppress_filters' => 0,
                'post_status'      => array( 'acf-disabled' ),
                'pip_post_content' => array(
                    'compare' => 'LIKE',
                    'value'   => 's:14:"_pip_is_layout";i:1',
                ),
            );
            $query = new WP_Query( $args );

            // Admin URL
            $url = add_query_arg( array(
                'layouts'   => 1,
                'post_type' => 'acf-field-group',
            ), admin_url( 'edit.php' ) );

            // Maybe add current class
            $class = ( !acf_maybe_get_GET( 'post_status' ) ) ? 'current' : '';

            // Update counter
            $views['all'] = '<a href="' . $url . '" class="' . $class . '">' . __( 'All', 'acf' ) . ' <span class="count">(' . $query->found_posts . ')</span></a>';
        }

        /**
         * Update counters for ACF Field Groups page
         *
         * @param $views
         */
        private static function update_field_groups_counters( &$views ) {
            $post_statuses = array(
                'all',
                'publish',
                'acf-disabled',
            );

            foreach ( $post_statuses as $post_status ) {
                $class = $count = $title = null;

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

                // Update counter
                $views[ $post_status ] = '<a href="' . $url . '" class="' . $class . '">' . __( $title, 'acf' ) . ' <span class="count">(' . $count . ')</span></a>';
            }
        }

        /**
         * Update counters for sync available
         *
         * @param $views
         * @param bool $is_layout
         */
        private static function update_sync_counters( &$views, $is_layout = true ) {
            // Get field groups
            $field_groups = acf_get_field_groups();

            // If no field group, return
            if ( empty( $field_groups ) ) {
                return;
            }

            // Get field group
            $sync = array();
            foreach ( $field_groups as $group ) {

                // Get type
                $local    = acf_maybe_get( $group, 'local', false );
                $modified = acf_maybe_get( $group, 'modified', 0 );
                $private  = acf_maybe_get( $group, 'private', false );

                if ( $private || $local !== 'json' ) {

                    // Continue if private or not JSON
                    continue;

                } elseif ( !$group['ID'] || ( $modified && $modified > get_post_modified_time( 'U', true, $group['ID'], true ) ) ) {
                    // If not in DB or JSON newer than post

                    if ( $is_layout && acf_maybe_get( $group, '_pip_is_layout' ) && $group['_pip_is_layout'] === 1 ) {

                        // Store layout
                        $sync[ $group['key'] ] = $group['title'];

                    } elseif ( !$is_layout && ( !acf_maybe_get( $group, '_pip_is_layout' ) || $group['_pip_is_layout'] !== 1 ) ) {

                        // Store non layout
                        $sync[ $group['key'] ] = $group['title'];

                    }
                }
            }

            if ( count( $sync ) > 0 ) {
                // If there's field group to sync

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
                $views['json'] = '<a href="' . $url . '" class="' . $class . '">' . __( 'Sync available', 'acf' ) . ' <span class="count">(' . count( $sync ) . ')</span></a>';
            } else {
                // If there isn't field group to sync

                // Hide JSON
                unset( $views['json'] );
            }
        }
    }

    // Instantiate class
    new PIP_Admin_Layouts();
}
