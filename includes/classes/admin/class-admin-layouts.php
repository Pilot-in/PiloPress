<?php

if ( !class_exists( 'PIP_Admin_Layouts' ) ) {

    /**
     * Class PIP_Admin_Layouts
     */
    class PIP_Admin_Layouts {

        public function __construct() {

            // WP hooks
            add_action( 'current_screen', array( $this, 'admin_layouts_page' ), 1 );
            add_action( 'untrashed_post', array( $this, 'untrash_field_group' ), 1 );

            // ACF hooks
            add_action( 'acf/update_field_group', array( $this, 'update_layout_setting' ), 10, 1 );
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

            // Layouts listing page
            if ( acf_maybe_get_GET( 'layouts' ) === '1' ) {
                add_filter( 'manage_edit-acf-field-group_columns', array( $this, 'field_group_columns' ), 20 );
            }

            // Sync page
            if ( acf_maybe_get_GET( 'post_status' ) === 'sync' ) {
                add_filter( 'acf/load_field_groups', array( $this, 'filter_sync_field_groups' ), 20 );
            }

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
         * Remove status column for layouts
         *
         * @param $columns
         *
         * @return mixed
         */
        public function field_group_columns( $columns ) {

            unset( $columns['acf-fg-status'] );

            return $columns;
        }

        /**
         * Make layout slug unique
         *
         * @param $field_group
         */
        public function update_layout_setting( $field_group ) {

            $pip_layouts = acf_get_instance( 'PIP_Layouts' );

            // If not a layout, return
            if ( !$pip_layouts->is_layout( $field_group ) ) {
                return;
            }

            // If ACF sync, skip
            if ( acf_maybe_get_GET( 'acfsync' ) ) {
                return;
            }

            // Get layout slug
            $slug = $field_group['_pip_layout_slug'];

            // Get layout with current slug
            $original = $pip_layouts->get_layout_by_slug( $slug, $field_group['ID'] );

            // If not a duplicated layout slug, return
            if ( !$original ) {
                return;
            }

            // Initialize suffix
            $suffix = 2;

            // Make unique layout slug
            do {
                // Build new slug
                $alt_post_name = _truncate_post_slug( $slug, 200 - ( strlen( $suffix ) + 1 ) ) . "-$suffix";

                // Check if layout exists with new slug
                $unique_layout_slug = $pip_layouts->get_layout_by_slug( $alt_post_name );

                // Increment suffix
                $suffix ++;

                // Do it again until no layout is find
            } while ( $unique_layout_slug );

            // Replace file names with new slug
            $field_group['_pip_render_layout'] = str_replace( $slug, $alt_post_name, $field_group['_pip_render_layout'] );
            $field_group['_pip_render_style']  = str_replace( $slug, $alt_post_name, $field_group['_pip_render_style'] );
            $field_group['_pip_render_script'] = str_replace( $slug, $alt_post_name, $field_group['_pip_render_script'] );

            // Update field group with new slug
            $field_group['_pip_layout_slug'] = $alt_post_name;
            acf_update_field_group( $field_group );
        }

        /**
         * Remove ACF action when un-trash layout field group
         *
         * @param $post_id
         */
        public function untrash_field_group( $post_id ) {

            $pip_layouts = acf_get_instance( 'PIP_Layouts' );

            if ( !$pip_layouts->is_layout( $post_id ) ) {
                return;
            }

            remove_action( 'acf/untrash_field_group', array( acf()->json, 'update_field_group' ) );
        }

        /**
         * Remove links on layouts page
         *
         * @param $views
         *
         * @return bool
         */
        public function edit_views( $views ) {

            if ( acf_maybe_get_GET( 'layouts' ) === '1' ) {

                // If layouts page, remove links and update counters
                $this->update_layouts_counters( $views );
                $this->update_sync_counters( $views );

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

            } else {

                // Update counters for field groups page
                $this->update_field_groups_counters( $views );
                $this->update_sync_counters( $views, false );

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

            // Sync
            if ( acf_maybe_get_GET( 'acfsync' ) || acf_maybe_get_GET( 'action2' ) === 'acfsync' ) {
                return $field_groups;
            }

            $pip_layouts = acf_get_instance( 'PIP_Layouts' );

            if ( acf_maybe_get_GET( 'layouts' ) === '1' ) {

                // Layouts page
                foreach ( $field_groups as $key => $field_group ) {
                    if ( !$pip_layouts->is_layout( $field_group ) ) {
                        unset( $field_groups[ $key ] );
                    }
                }

            } elseif ( !acf_maybe_get_GET( 'layouts' ) ) {

                // ACF Field groups
                foreach ( $field_groups as $key => $field_group ) {
                    if ( $pip_layouts->is_layout( $field_group ) ) {
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

        /**
         * Redirect to layouts admin page
         */
        public function maybe_redirect_to_layouts() {

            $redirect = false;

            $pip_layouts = acf_get_instance( 'PIP_Layouts' );

            // If layouts, redirect to true
            $field_groups = acf_maybe_get_GET( 'acfsynccomplete' );
            if ( $field_groups ) {
                $field_groups = explode( ',', $field_groups );

                if ( $field_groups ) {
                    foreach ( $field_groups as $field_group ) {
                        if ( $pip_layouts->is_layout( $field_group ) ) {
                            $redirect = true;
                        }
                    }
                }
            }

            // Redirect
            if ( $redirect ) {
                $url = add_query_arg( array(
                    'post_type' => 'acf-field-group',
                    'layouts'   => 1,
                    'sync_ok'   => acf_maybe_get_GET( 'acfsynccomplete' ),
                ), admin_url( 'edit.php' ) );

                wp_safe_redirect( $url );
                exit();
            }
        }

        /**
         * Show notice message after sync
         */
        public function show_notice_message() {

            $sync_field_groups = acf_maybe_get_GET( 'sync_ok' );
            if ( $sync_field_groups ) {

                // explode
                $sync_field_groups = explode( ',', $sync_field_groups );
                $total             = count( $sync_field_groups );

                // Generate text.
                // translators: Number of layouts synchronised
                $text = sprintf( _n( '%s layout synchronised.', '%s layouts synchronised.', $total, 'acf' ), $total );

                // Add links to text.
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
        }

    }

    acf_new_instance( 'PIP_Admin_Layouts' );

}
