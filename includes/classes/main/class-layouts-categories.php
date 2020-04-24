<?php

if ( !class_exists( 'PIP_Layouts_Categories' ) ) {
    class PIP_Layouts_Categories {

        public static $taxonomy = 'acf-layouts-category';

        public function __construct() {
            // WP hooks
            add_action( 'init', array( $this, 'init' ) );
            add_filter( 'parent_file', array( $this, 'menu_parent_file' ) );
            add_action( 'current_screen', array( $this, 'current_screen' ) );

            // ACF hooks
            add_filter( 'acf/get_taxonomies', array( $this, 'remove_layout_category' ), 10, 2 );
        }

        /**
         * Current screen
         */
        public function current_screen() {
            // If not in admin acf field group listing, in layouts, return
            if ( !is_admin() || !acf_is_screen( 'edit-acf-field-group' ) || acf_maybe_get_GET( 'layouts' ) != 1 ) {
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
            register_taxonomy( self::$taxonomy,
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

            // Remove ACF Field groups categories
            if ( acf_maybe_get_GET( 'layouts' ) == 1 ) {
                remove_filter( 'manage_edit-acf-field-group_columns', 'acfe_field_group_category_column', 11 );
                remove_action( 'manage_acf-field-group_posts_custom_column', 'acfe_field_group_category_column_html', 10 );
            }
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
            if ( $current_screen->taxonomy !== self::$taxonomy && ( $pagenow !== 'edit-tags.php' || $pagenow !== 'term.php' ) ) {
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
                if ( $taxonomy !== self::$taxonomy ) {
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

            // Add new column
            foreach ( $columns as $key => $value ) {
                if ( $key === 'title' ) {
                    $new_columns[ self::$taxonomy ] = __( 'Categories' );
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
            if ( $column !== self::$taxonomy ) {
                return;
            }

            $terms = get_the_terms( $post_id, self::$taxonomy );
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
                        'layouts'       => 1,
                        self::$taxonomy => $term->slug,
                        'post_type'     => 'acf-field-group',
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
            $terms = get_terms( self::$taxonomy, array( 'hide_empty' => false ) );

            // If no terms, return
            if ( !$terms ) {
                return $views;
            }

            // Browse all terms
            foreach ( $terms as $term ) {
                global $wp_query;

                // Get all posts with term
                $groups = get_posts( array(
                    'post_type'        => 'acf-field-group',
                    'posts_per_page'   => - 1,
                    'suppress_filters' => false,
                    'post_status'      => array( 'publish', 'acf-disabled' ),
                    'taxonomy'         => self::$taxonomy,
                    'term'             => $term->slug,
                    'fields'           => 'ids',
                ) );

                // Count
                $count = count( $groups );

                // If count > 0, set HTML
                $html = '';
                if ( $count > 0 ) {
                    $html = ' <span class="count">(' . $count . ')</span>';
                }

                // If on current layout category, add current class
                $class = '';
                if ( isset( $wp_query->query_vars[ self::$taxonomy ] ) && $wp_query->query_vars[ self::$taxonomy ] === $term->slug ) {
                    $class = ' class="current"';
                }

                // Build URL
                $url = add_query_arg(
                    array(
                        'layouts'       => 1,
                        self::$taxonomy => $term->slug,
                        'post_type'     => 'acf-field-group',
                    ),
                    admin_url( 'edit.php' )
                );

                // Add counter
                $views[ 'category-layout-' . $term->slug ] = '<a href="' . $url . '"' . $class . '>' . $term->name . $html . '</a>';
            }

            // Return views
            return $views;
        }
    }

    // Instantiate class
    new PIP_Layouts_Categories();
}
