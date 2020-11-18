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
            add_filter( 'disable_months_dropdown', array( $this, 'disable_month_dropdown' ), 10, 2 );
            add_action( 'restrict_manage_posts', array( $this, 'custom_dropdown' ), 10, 2 );

            // Remove ACF Extended: Field Group Category Column
            remove_filter( 'manage_edit-acf-field-group_columns', 'acfe_field_group_category_column', 11 );
            remove_action( 'manage_acf-field-group_posts_custom_column', 'acfe_field_group_category_column_html', 10 );

        }

        /**
         * Pre get posts
         *
         * @param WP_Query $query
         */
        public function pre_get_posts( WP_Query $query ) {

            // If not admin, not main query and not layouts screen, return
            if ( !is_admin() || !$query->is_main_query() || is_post_type_archive( 'acf-field-group' ) ) {
                return;
            }

            // Get current filters
            $cat = filter_input( INPUT_GET, 'ct', FILTER_VALIDATE_INT );
            $col = filter_input( INPUT_GET, 'cl', FILTER_VALIDATE_INT );

            $pip_layouts_categories  = acf_get_instance( 'PIP_Layouts_Categories' );
            $pip_layouts_collections = acf_get_instance( 'PIP_Layouts_Collections' );

            $tax_query = array();
            if ( $cat > 0 && $col > 0 ) {
                $tax_query['relation'] = 'AND';
            }

            // Category filter
            if ( $cat > 0 ) {
                $tax_query[] = array(
                    'taxonomy' => $pip_layouts_categories->taxonomy_name,
                    'terms'    => $cat,
                );
            }

            // Collection filter
            if ( $col > 0 ) {
                $tax_query[] = array(
                    'taxonomy' => $pip_layouts_collections->taxonomy_name,
                    'terms'    => $col,
                );
            }

            // Add tax query
            if ( ( $cat > 0 || $col > 0 ) && $tax_query ) {
                $query->set( 'tax_query', $tax_query );
            }

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
         * @return array
         */
        public function views( $views ) {

            $views = array();

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
                }
            }

            return $views;
        }

        /**
         * Disable month dropdown
         *
         * @param $show
         * @param $post_type
         *
         * @return bool
         */
        public function disable_month_dropdown( $show, $post_type ) {
            return true;
        }

        /**
         * Add custom dropdown for filters
         *
         * @param $post_type
         * @param $which
         */
        public function custom_dropdown( $post_type, $which ) {

            // Get classes
            $pip_layouts_categories  = acf_get_instance( 'PIP_Layouts_Categories' );
            $pip_layouts_collections = acf_get_instance( 'PIP_Layouts_Collections' );

            // Layouts taxonomies slug
            $layouts_cat = $pip_layouts_categories->taxonomy_name;
            $layouts_col = $pip_layouts_collections->taxonomy_name;

            // Get categories
            $layouts_categories = get_terms(
                array(
                    'taxonomy' => $layouts_cat,
                )
            );

            // Get collections
            $layouts_collections = get_terms(
                array(
                    'taxonomy' => $layouts_col,
                )
            );

            // Get current filters
            $cat = filter_input( INPUT_GET, 'ct', FILTER_VALIDATE_INT );
            $col = filter_input( INPUT_GET, 'cl', FILTER_VALIDATE_INT );
            ?>
            <?php // Stay on layouts page ?>
            <input type="hidden" name="layouts" value="1">

            <label for="filter-by-category" class="screen-reader-text"><?php _e( 'Filter by category' ); ?></label>
            <select name="ct" id="filter-by-category">
                <option<?php selected( $cat, 0 ); ?> value="0"><?php _e( 'All categories' ); ?></option>
                <?php
                foreach ( $layouts_categories as $layouts_category ) {
                    printf(
                        "<option %s value='%s'>%s</option>\n",
                        selected( $cat, $layouts_category->term_id, false ),
                        esc_attr( $layouts_category->term_id ),
                        $layouts_category->name
                    );
                }
                ?>
            </select>

            <label for="filter-by-collection" class="screen-reader-text"><?php _e( 'Filter by collection' ); ?></label>
            <select name="cl" id="filter-by-collection">
                <option<?php selected( $col, 0 ); ?> value="0"><?php _e( 'All collections' ); ?></option>
                <?php
                foreach ( $layouts_collections as $layouts_collection ) {
                    printf(
                        "<option %s value='%s'>%s</option>\n",
                        selected( $col, $layouts_collection->term_id, false ),
                        esc_attr( $layouts_collection->term_id ),
                        $layouts_collection->name
                    );
                }
                ?>
            </select>
            <?php
        }

    }

    acf_new_instance( 'PIP_Layouts_List' );

}
