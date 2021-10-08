<?php

if ( !class_exists( 'PIP_Layouts_Categories' ) ) {

    /**
     * Class PIP_Layouts_Categories
     */
    class PIP_Layouts_Categories {

        /**
         * Taxonomy slug
         *
         * @var string
         */
        public $taxonomy_name = 'acf-layouts-category';

        public function __construct() {

            // WP hooks
            add_action( 'init', array( $this, 'init' ) );
            add_filter( 'parent_file', array( $this, 'menu_parent_file' ) );
            add_action( 'current_screen', array( $this, 'current_screen' ) );

            // ACF hooks
            add_filter( 'acf/get_taxonomies', array( $this, 'remove_layout_category' ), 10, 2 );
            add_filter( 'acf/prepare_field_group_for_export', array( $this, 'export_layouts_categories' ) );
            add_action( 'acf/import_field_group', array( $this, 'import_layout_categories' ) );
        }

        /**
         * Current screen
         */
        public function current_screen() {

            // If not in admin acf field group listing, in layouts, return
            if (
                !is_admin()
                || !acf_is_screen( 'edit-acf-field-group' )
                || acf_maybe_get_GET( 'layouts' ) !== '1'
                || acf_maybe_get_GET( 'post_status' ) === 'sync'
            ) {
                return;
            }

            // Add custom column
            add_filter( 'manage_edit-acf-field-group_columns', array( $this, 'layouts_category_column' ), 11 );
            add_action( 'manage_acf-field-group_posts_custom_column', array( $this, 'layouts_category_column_html' ), 10, 2 );
            add_filter( 'views_edit-acf-field-group', array( $this, 'layouts_category_counters' ), 9 );
        }

        /**
         * Register taxonomy, remove useless admin columns
         */
        public function init() {

            // Register layouts category
            register_taxonomy(
                $this->taxonomy_name,
                array( 'acf-field-group' ),
                array(
                    'hierarchical'          => true,
                    'public'                => false,
                    'show_ui'               => true,
                    'show_admin_column'     => true,
                    'show_in_menu'          => true,
                    'show_in_nav_menus'     => true,
                    'show_tagcloud'         => false,
                    'rewrite'               => false,
                    'labels'                => array(
                        'name'              => _x( 'Categories', 'Category', 'pilopress' ),
                        'singular_name'     => _x( 'Categories', 'Category', 'pilopress' ),
                        'search_items'      => __( 'Search categories', 'pilopress' ),
                        'all_items'         => __( 'All categories', 'pilopress' ),
                        'parent_item'       => __( 'Parent category', 'pilopress' ),
                        'parent_item_colon' => __( 'Parent category:', 'pilopress' ),
                        'edit_item'         => __( 'Edit category', 'pilopress' ),
                        'update_item'       => __( 'Update category', 'pilopress' ),
                        'add_new_item'      => __( 'Add New category', 'pilopress' ),
                        'new_item_name'     => __( 'New category name', 'pilopress' ),
                        'menu_name'         => __( 'Category', 'pilopress' ),
                    ),
                    'update_count_callback' => array( $this, 'update_layouts_category_count' ),
                )
            );

        }

        /**
         * Count only acf-disabled posts
         *
         * @param $terms
         * @param $taxonomy
         */
        public function update_layouts_category_count( $terms, $taxonomy ) {

            global $wpdb;

            // Get post types
            $object_types = (array) $taxonomy->object_type;

            // Format
            foreach ( $object_types as &$object_type ) {
                list( $object_type ) = explode( ':', $object_type );
            }

            // Remove duplicates
            $object_types = array_unique( $object_types );

            // Check if post types exists
            if ( $object_types ) {
                $object_types = esc_sql( array_filter( $object_types, 'post_type_exists' ) );
            }

            // Browse all terms
            foreach ( (array) $terms as $term ) {
                $count = 0;

                // Add count
                if ( $object_types ) {
                    $count += (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->term_relationships, $wpdb->posts WHERE $wpdb->posts.ID = $wpdb->term_relationships.object_id AND post_status = 'acf-disabled' AND post_type IN ('" . implode( "', '", $object_types ) . "') AND term_taxonomy_id = %d", $term ) );
                }

                // Update DB value
                $wpdb->update( $wpdb->term_taxonomy, compact( 'count' ), array( 'term_taxonomy_id' => $term ) );
            }
        }

        /**
         * Parent menu for layouts categories
         *
         * @param $parent_file
         *
         * @return string
         */
        public function menu_parent_file( $parent_file ) {

            global $current_screen, $pagenow;

            // If not acf-layouts-category page, return
            if ( $current_screen->taxonomy !== $this->taxonomy_name && ( $pagenow !== 'edit-tags.php' || $pagenow !== 'term.php' ) ) {
                return $parent_file;
            }

            // Define parent menu
            $parent_file = 'pilopress';

            return $parent_file;
        }

        /**
         * Return layouts categories
         *
         * @param $taxonomies
         *
         * @return mixed
         */
        public function remove_layout_category( $taxonomies ) {

            // If no taxonomies, return
            if ( empty( $taxonomies ) ) {
                return $taxonomies;
            }

            // Browse all taxonomies
            foreach ( $taxonomies as $k => $taxonomy ) {

                // If not acf-layouts-category, continue
                if ( $taxonomy !== $this->taxonomy_name ) {
                    continue;
                }

                // Remove layouts category
                unset( $taxonomies[ $k ] );
            }

            return $taxonomies;
        }

        /**
         * Add column
         *
         * @param $columns
         *
         * @return array
         */
        public function layouts_category_column( $columns ) {

            $new_columns = array();

            // Get terms
            $terms = get_terms(
                array(
                    'taxonomy'   => $this->taxonomy_name,
                    'hide_empty' => false,
                )
            );

            // If no terms, return
            if ( !$terms ) {
                return $columns;
            }

            // Add new column
            foreach ( $columns as $key => $value ) {
                if ( $key === 'title' ) {
                    $new_columns[ $this->taxonomy_name ] = __( 'Categories' );
                }
                $new_columns[ $key ] = $value;
            }

            // Return columns with new one
            return $new_columns;
        }

        /**
         * Set HTML for custom column
         *
         * @param $column
         * @param $post_id
         */
        public function layouts_category_column_html( $column, $post_id ) {

            // If not layouts category, return
            if ( $column !== $this->taxonomy_name ) {
                return;
            }

            $terms = get_the_terms( $post_id, $this->taxonomy_name );
            // If no terms, return
            if ( !$terms ) {
                echo '—';

                return;
            }

            // Get categories
            $categories = array();
            foreach ( $terms as $term ) {
                $url          = add_query_arg(
                    array(
                        'layouts'            => 1,
                        $this->taxonomy_name => $term->slug,
                        'post_type'          => 'acf-field-group',
                    ),
                    admin_url( 'edit.php' )
                );
                $categories[] = '<a href="' . $url . '">' . $term->name . '</a>';
            }

            // Display categories
            echo implode( ' ', $categories );
        }

        /**
         * Add layouts categories counters
         *
         * @param $views
         *
         * @return mixed
         */
        public function layouts_category_counters( $views ) {

            // Get all layouts categories
            $terms = get_terms( $this->taxonomy_name, array( 'hide_empty' => false ) );

            // If no terms, return
            if ( !$terms ) {
                return $views;
            }

            // Browse all terms
            foreach ( $terms as $term ) {
                // Get all posts with term
                $groups = get_posts(
                    array(
                        'post_type'        => 'acf-field-group',
                        'posts_per_page'   => - 1,
                        'suppress_filters' => false,
                        'post_status'      => array( 'publish', 'acf-disabled' ),
                        'taxonomy'         => $this->taxonomy_name,
                        'term'             => $term->slug,
                        'fields'           => 'ids',
                    )
                );

                // Count
                $count = count( $groups );

                // If count > 0, set HTML
                $html = '';
                if ( $count > 0 ) {
                    $html = ' <span class="count">(' . $count . ')</span>';
                }

                // If on current layout category, add current class
                $class = '';
                if ( get_query_var( $this->taxonomy_name ) === $term->slug ) {
                    $class = ' class="current"';
                }

                // Build URL
                $url = add_query_arg(
                    array(
                        'layouts'            => 1,
                        $this->taxonomy_name => $term->slug,
                        'post_type'          => 'acf-field-group',
                    ),
                    admin_url( 'edit.php' )
                );

                // Add counter
                $views[ 'category-layout-' . $term->slug ] = '<a href="' . $url . '"' . $class . '>' . $term->name . $html . '</a>';
            }

            // Return views
            return $views;
        }

        /**
         * Add layout categories to JSON file
         *
         * @param $field_group
         *
         * @return mixed
         */
        public function export_layouts_categories( $field_group ) {

            // Get field group
            $_field_group = acf_get_field_group( $field_group['key'] );

            // If no field group, return
            if ( empty( $_field_group ) ) {
                return $field_group;
            }

            // If no ID, return
            if ( !acf_maybe_get( $_field_group, 'ID' ) ) {
                return $field_group;
            }

            // Get layout categories
            $categories = get_the_terms( $_field_group['ID'], $this->taxonomy_name );

            // If no categories, return
            if ( empty( $categories ) || is_wp_error( $categories ) ) {
                return $field_group;
            }

            // Initiate array
            $field_group['layout_categories'] = array();

            // Add categories
            foreach ( $categories as $term ) {
                $field_group['layout_categories'][ $term->slug ] = $term->name;
            }

            return $field_group;

        }

        /**
         * Add layout categories when import
         *
         * @param $field_group
         */
        public function import_layout_categories( $field_group ) {

            // If no categories, return
            $categories = acf_maybe_get( $field_group, 'layout_categories' );
            if ( !$categories ) {
                return;
            }

            // Browse categories
            foreach ( $categories as $term_slug => $term_name ) {

                // Get term
                $new_term_id = false;
                $get_term    = get_term_by( 'slug', $term_slug, $this->taxonomy_name );

                if ( empty( $get_term ) ) {
                    // Term doesn't exists

                    // Add new term
                    $new_term = wp_insert_term(
                        $term_name,
                        $this->taxonomy_name,
                        array(
                            'slug' => $term_slug,
                        )
                    );

                    // If well inserted, store ID
                    if ( !is_wp_error( $new_term ) ) {
                        $new_term_id = $new_term['term_id'];
                    }
                } else {
                    // Term already exists

                    // Get term ID
                    $new_term_id = $get_term->term_id;

                }

                // Assign term
                if ( $new_term_id ) {
                    wp_set_post_terms( $field_group['ID'], array( $new_term_id ), $this->taxonomy_name, true );
                }
            }

        }

    }

    acf_new_instance( 'PIP_Layouts_Categories' );
}
