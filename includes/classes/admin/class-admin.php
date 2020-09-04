<?php

if ( !class_exists( 'PIP_Admin' ) ) {

    /**
     * Class PIP_Admin
     */
    class PIP_Admin {
        public function __construct() {
            // WP hooks
            add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
            add_action( 'admin_menu', array( $this, 'add_admin_menu' ), 20 );
            add_action( 'admin_bar_menu', array( $this, 'add_admin_bar_menu' ), 9999 );
            add_filter( 'parent_file', array( $this, 'menu_parent_file' ) );
            add_filter( 'submenu_file', array( $this, 'menu_submenu_file' ) );
            add_action( 'pre_get_posts', array( $this, 'admin_pre_get_posts' ) );
            add_filter( 'posts_where', array( $this, 'query_pip_post_content' ), 10, 2 );
            add_action( 'adminmenu', array( $this, 'admin_menu_parent' ) );
            add_filter( 'admin_url', array( $this, 'change_admin_url' ), 10, 2 );
            add_filter( 'upload_mimes', array( $this, 'allow_mimes_types' ) );
            add_action( 'in_admin_header', array( $this, 'add_pip_navbar' ) );
        }

        /**
         * Check if it is a Pilo'Press admin page
         *
         * @return bool
         */
        private function is_pip_admin_page() {
            $is_pip_admin    = false;
            $flexible_mirror = PIP_Flexible_Mirror::get_flexible_mirror_group();

            // If Pilo'Press admin page, set variable to true
            if ( acf_maybe_get_GET( 'layouts' ) == '1'
                 || PIP_Layouts::is_layout( get_post( acf_maybe_get_GET( 'post' ) ) )
                 || acf_maybe_get_GET( 'post' ) == $flexible_mirror['ID']
                 || acf_maybe_get_GET( 'taxonomy' ) === PIP_Layouts_Categories::$taxonomy
                 || acf_maybe_get_GET( 'post_type' ) === PIP_Components::$post_type
                 || PIP_Components::is_component( acf_maybe_get_GET( 'post' ) )
                 || acf_maybe_get_GET( 'page' ) == PIP_Pattern::$menu_slug
                 || PIP_Admin_Options_Page::is_style_page( acf_maybe_get_GET( 'page' ) ) ) {
                $is_pip_admin = true;
            }

            return $is_pip_admin;
        }

        /**
         * Add Pilo'Press top navbar
         */
        public function add_pip_navbar() {
            if ( $this->is_pip_admin_page() ) {
                self::display_pip_navbar();
            }
        }

        /**
         * Enqueue admin style & scripts
         */
        public function enqueue_scripts() {
            // Styles
            wp_enqueue_style( 'pilopress-admin-style', PIP_URL . 'assets/css/pilopress-admin.css', array(), PiloPress::$version );
            self::maybe_enqueue_layout_admin_style();

            // Scripts
            wp_enqueue_script( 'pilopress-admin-script', PIP_URL . 'assets/js/pilopress-admin.js', array( 'jquery' ), PiloPress::$version, true );
            wp_localize_script( 'pilopress-admin-script', 'ajaxurl', admin_url( 'admin-ajax.php' ) );
            wp_enqueue_script( 'pilopress-fields', PIP_URL . 'assets/js/pilopress-fields.js', array( 'jquery' ), PiloPress::$version, true );
        }

        /**
         * Enqueue layouts admin style
         */
        private static function maybe_enqueue_layout_admin_style() {
            // If not acf field group page, return
            if ( get_current_screen()->id !== 'acf-field-group' ) {
                return;
            }

            // If not layout page, return
            $post_id = acf_maybe_get_GET( 'post' );
            if ( !PIP_Layouts::is_layout( $post_id ) && acf_maybe_get_GET( 'layout' ) !== '1' ) {
                return;
            }

            wp_enqueue_style( 'pilopress-layout-admin-style', PIP_URL . 'assets/css/pilopress-layout-admin.css', array(), PiloPress::$version );
        }

        /**
         * Filter ACF archive page in admin
         *
         * @param WP_Query $query
         */
        public function admin_pre_get_posts( $query ) {
            // In admin, on ACF field groups archive
            if ( !is_admin() || !acf_is_screen( 'edit-acf-field-group' ) ) {
                return;
            }

            if ( acf_maybe_get_GET( 'layouts' ) == 1 ) {
                // Layouts view
                $query->set(
                    'pip_post_content',
                    array(
                        'compare' => 'LIKE',
                        'value'   => 's:14:"_pip_is_layout";i:1',
                    )
                );

            } elseif ( acf_maybe_get_GET( 'layouts' ) === null && acf_maybe_get_GET( 'post_status' ) != 'trash' ) {
                // Classic view

                // Remove layouts
                $query->set(
                    'pip_post_content',
                    array(
                        'compare' => 'NOT LIKE',
                        'value'   => 's:14:"_pip_is_layout";i:1',
                    )
                );

                // Remove flexible
                $flexible_mirror = PIP_Flexible_Mirror::get_flexible_mirror_group();
                $query->set( 'post__not_in', array( $flexible_mirror['ID'] ) );
            }
        }

        /**
         * Add custom param for WP_Query
         *
         * @param string   $where
         * @param WP_Query $wp_query
         *
         * @return mixed
         */
        public function query_pip_post_content( $where, $wp_query ) {
            global $wpdb;

            // If no custom var, return
            $pip_post_content = $wp_query->get( 'pip_post_content' );
            if ( !$pip_post_content ) {
                return $where;
            }

            // Add custom condition
            if ( is_array( $pip_post_content ) ) {
                $where .= ' AND ' . $wpdb->posts . '.post_content ' . $pip_post_content['compare'] . ' \'%' . esc_sql( $wpdb->esc_like( $pip_post_content['value'] ) ) . '%\'';
            }

            return $where;
        }

        /**
         * Add Pilo'Press menu pages
         */
        public function add_admin_menu() {
            // Pilot'in logo
            $pip_logo_base64_svg = 'PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyMCAyMCIgZmlsbD0iI2EwYTVhYSI+PHBhdGggZD0iTTEwIC4yQzQuNi4yLjMgNC42LjMgMTBzNC40IDkuOCA5LjcgOS44YzIuNiAwIDUuMS0xIDYuOS0yLjggMS44LTEuOCAyLjgtNC4zIDIuOC02LjkgMC01LjUtNC4zLTkuOS05LjctOS45em02LjQgMTYuM2MtMS43IDEuNy00IDIuNi02LjQgMi42LTUgMC05LTQuMS05LTkuMVM1IC45IDEwIC45IDE5IDUgMTkgMTBjMCAyLjUtLjkgNC43LTIuNiA2LjV6Ii8+PHBhdGggZD0iTTEwIDUuM2MtMi41IDAtNC42IDIuMS00LjYgNC43di41Yy4yIDEuOCAxLjQgMy4zIDMgMy45LjUuMiAxIC4zIDEuNS4zLjQgMCAuOS0uMSAxLjMtLjIuMSAwIC4xIDAgLjItLjEuMy0uMS41LS4yLjgtLjMgMCAwIC4xIDAgLjEtLjEgMCAwIC4xIDAgLjEtLjFoLjFzLjEgMCAuMS0uMWMwIDAgLjEgMCAuMS0uMS4yLS4yLjUtLjQuNy0uNmwuMy0uM2MuNi0uOCAxLTEuOSAxLTIuOSAwLTIuNS0yLjEtNC42LTQuNy00LjZ6bTMuMSA3LjNjMC0uMSAwLS4xIDAgMC0uNi0uNC0uNy0uOS0uNy0xLjR2LS40LS4xLS4zYzAtLjctLjItMS41LTEuNS0xLjYtLjUgMC0xLjMuMS0yLjMuNC0uMi0uMS0uNCAwLS42LjEtLjYuMi0xLjIuNC0yIC43IDAtMi4yIDEuOC00IDMuOS00IDEuNSAwIDIuOC44IDMuNSAyLjEuNC42LjYgMS4yLjYgMS45IDAgLjktLjMgMS44LS45IDIuNnoiLz48L3N2Zz4=';

            // Get flexible mirror
            $flexible_mirror = PIP_Flexible_Mirror::get_flexible_mirror_group();

            // Capability
            $capability = apply_filters( 'pip/options/capability', acf_get_setting( 'capability' ) );
            if ( !current_user_can( $capability ) ) {
                return;
            }

            // Main menu page
            add_menu_page(
                __( "Pilo'Press", 'pilopress' ),
                __( "Pilo'Press", 'pilopress' ),
                $capability,
                'pilopress',
                array( $this, 'pilopress_dashboard' ),
                'data:image/svg+xml;base64,' . $pip_logo_base64_svg,
                82 // After 'ACF' menu
            );

            // Flexible sub menu
            add_submenu_page(
                'pilopress',
                __( 'Builder', 'pilopress' ),
                __( 'Builder', 'pilopress' ),
                $capability,
                'post.php?post=' . $flexible_mirror['ID'] . '&action=edit'
            );

            // Layouts sub menu
            add_submenu_page(
                'pilopress',
                __( 'Layouts', 'pilopress' ),
                __( 'Layouts', 'pilopress' ),
                $capability,
                'edit.php?layouts=1&post_type=acf-field-group'
            );

            // Layouts categories sub menu
            add_submenu_page(
                'pilopress',
                __( 'Categories', 'pilopress' ),
                __( 'Categories', 'pilopress' ),
                $capability,
                'edit-tags.php?taxonomy=acf-layouts-category'
            );

            // Layouts collections sub menu
            add_submenu_page(
                'pilopress',
                __( 'Collections', 'pilopress' ),
                __( 'Collections', 'pilopress' ),
                $capability,
                'edit-tags.php?taxonomy=acf-layouts-collection'
            );

            // Components sub menu
            add_submenu_page(
                'pilopress',
                __( 'Components', 'pilopress' ),
                __( 'Components', 'pilopress' ),
                $capability,
                'edit.php?post_type=' . PIP_Components::$post_type
            );
        }

        /**
         * Pilo'Press dashboard
         */
        public function pilopress_dashboard() {
            // Icons HTML
            $success_icon = '<span class="dashicons dashicons-yes"></span>';
            $error_icon   = '<span class="dashicons dashicons-no-alt"></span>';

            // Check if "tailwind-admin.min.css" enqueued
            global $wp_styles;
            $admin_style_enqueued = false;
            foreach ( $wp_styles->queue as $style ) {
                if ( $wp_styles->registered[ $style ]->src === PIP_THEME_ASSETS_URL . PIP_THEME_STYLE_ADMIN_FILENAME . '.min.css' ) {
                    $admin_style_enqueued = true;
                }
            }

            // Get theme folder
            $theme_folder = str_replace( get_home_url(), '', get_stylesheet_directory_uri() );

            // Configurations
            $configurations = array(
                array(
                    'label'        => '<code>' . $theme_folder . '/pilopress/</code>',
                    'status'       => file_exists( PIP_THEME_PILOPRESS_PATH ),
                    'status_label' => file_exists( PIP_THEME_PILOPRESS_PATH ) ? __( ' folder found', 'pilopress' ) : __( ' folder not found', 'pilopress' ),
                ),
                array(
                    'label'        => '<code>' . $theme_folder . '/pilopress/assets/</code>',
                    'status'       => file_exists( PIP_THEME_ASSETS_PATH ),
                    'status_label' => file_exists( PIP_THEME_ASSETS_PATH ) ? __( ' folder found', 'pilopress' ) : __( ' folder not found', 'pilopress' ),
                ),
                array(
                    'label'        => '<code>' . $theme_folder . '/pilopress/layouts/</code>',
                    'status'       => file_exists( PIP_THEME_LAYOUTS_PATH ),
                    'status_label' => file_exists( PIP_THEME_LAYOUTS_PATH ) ? __( ' folder found', 'pilopress' ) : __( ' folder not found', 'pilopress' ),
                ),
                array(
                    'label'        => __( 'Admin style', 'pilopress' ),
                    'status'       => $admin_style_enqueued,
                    'status_label' => $admin_style_enqueued ? __( ' enqueued', 'pilopress' ) : __( ' not enqueued', 'pilopress' ),
                ),
            );

            // Layouts list
            $layouts      = array();
            $layouts_keys = PIP_Layouts::get_layout_group_keys();
            if ( is_array( $layouts_keys ) ) {
                $total_layouts_count = count( $layouts_keys );

                if ( $total_layouts_count > 15 ) {

                    for ( $i = 0; $i < 15; $i ++ ) {
                        // Get field group
                        $field_group = acf_get_field_group( $layouts_keys[ $i ] );

                        // Get locations html
                        $locations = ''; // PILO_TODO: get ACFE helper (next version)

                        // Structured array for template file
                        $layouts[] = array(
                            'title'     => $field_group['title'],
                            'location'  => $locations,
                            'edit_link' => get_edit_post_link( $field_group['ID'] ),
                        );

                        $see_more_layouts = true;
                    }

                } else {

                    foreach ( $layouts_keys as $layout_key ) {
                        // Get field group
                        $field_group = acf_get_field_group( $layout_key );

                        // Get locations html
                        $locations = ''; // PILO_TODO: get ACFE helper (next version)

                        // Structured array for template file
                        $layouts[] = array(
                            'title'     => $field_group['title'],
                            'location'  => $locations,
                            'edit_link' => get_edit_post_link( $field_group['ID'] ),
                        );
                    }

                    $see_more_layouts = false;

                }

            }

            // New field group link
            $add_new_layout = add_query_arg(
                array(
                    'post_type' => 'acf-field-group',
                    'layout'    => 1,
                ),
                admin_url( 'post-new.php' )
            );

            // All layouts link
            $all_layouts = add_query_arg(
                array(
                    'post_type' => 'acf-field-group',
                    'layouts'   => 1,
                ),
                admin_url( 'edit.php' )
            );

            // Components
            $components = get_posts(
                array(
                    'post_type'      => PIP_Components::$post_type,
                    'posts_per_page' => - 1,
                )
            );

            // New field group link
            $add_new_component = add_query_arg(
                array(
                    'post_type' => PIP_Components::$post_type,
                ),
                admin_url( 'post-new.php' )
            );

            // Template file
            include_once( PIP_PATH . 'includes/views/pip-dashboard.php' );
        }

        /**
         * Display Pilo'Press navbar
         */
        public static function display_pip_navbar() {
            // Hide ACF top navbar
            add_filter( 'acf/admin/navigation', '__return_false' );

            // Get menu items
            global $submenu;
            $pilopress_menu = $submenu['pilopress'];
            foreach ( $pilopress_menu as $menu_item ) {
                $menu_items[] = array(
                    'title' => $menu_item[0],
                    'link'  => strstr( $menu_item[2], '.php?' ) ? admin_url() . $menu_item[2] : menu_page_url( $menu_item[2], false ),
                );
            }

            // Add Pilo'Press navbar
            include_once( PIP_PATH . 'includes/views/pip-navbar.php' );
        }

        /**
         * Add Pilo'Press admin bar menu
         *
         * @param WP_Admin_Bar $wp_admin_bar
         */
        public function add_admin_bar_menu( $wp_admin_bar ) {
            // Capability
            $capability = apply_filters( 'pip/options/capability', acf_get_setting( 'capability' ) );
            if ( !current_user_can( $capability ) ) {
                return;
            }

            // Pilo'Press menu
            $wp_admin_bar->add_node(
                array(
                    'id'    => 'pilopress',
                    'title' => "<span class='pip-icon'></span> Pilo'Press",
                    'href'  => add_query_arg( array( 'page' => 'pilopress' ), admin_url( 'admin.php' ) ),
                )
            );

            // Layouts
            $wp_admin_bar->add_node(
                array(
                    'parent' => 'pilopress',
                    'id'     => 'layouts',
                    'title'  => __( 'Layouts', 'pilopress' ),
                    'href'   => add_query_arg(
                        array(
                            'layouts'   => 1,
                            'post_type' => 'acf-field-group',
                        ),
                        admin_url( 'edit.php' )
                    ),
                )
            );

            // Styles
            $wp_admin_bar->add_node(
                array(
                    'parent' => 'pilopress',
                    'id'     => 'styles',
                    'title'  => __( 'Styles', 'pilopress' ),
                    'href'   => add_query_arg(
                        array(
                            'page' => 'pip-styles-tailwind',
                        ),
                        admin_url( 'admin.php' )
                    ),
                )
            );
        }

        /**
         * Change highlighted parent menu
         *
         * @param $parent_file
         *
         * @return string
         */
        public function menu_parent_file( $parent_file ) {
            // Get flexible mirror
            $flexible_mirror = PIP_Flexible_Mirror::get_flexible_mirror_group();

            // Define parent menu for Flexible menu
            if ( acf_maybe_get_GET( 'post' ) == $flexible_mirror['ID'] ) {
                $parent_file = 'pilopress';
            }

            // Define parent menu for Components menu
            if ( strstr( $parent_file, 'post_type=' . PIP_Components::$post_type ) ) {
                $parent_file = 'pilopress';
            }

            return $parent_file;
        }

        /**
         * Change highlighted subpage menu
         *
         * @param $submenu_file
         *
         * @return string
         */
        public function menu_submenu_file( $submenu_file ) {
            global $current_screen;

            // If layouts categories or collections, return
            if (
                $current_screen->taxonomy === PIP_Layouts_Categories::$taxonomy_name
                || $current_screen->taxonomy === PIP_Layouts_Collections::$taxonomy_name ) {
                return $submenu_file;
            }

            // Get flexible mirror
            $flexible_mirror = PIP_Flexible_Mirror::get_flexible_mirror_group();

            // Define submenu for Flexible menu
            if ( acf_maybe_get_GET( 'post' ) == $flexible_mirror['ID'] && !acf_maybe_get_GET( 'page' ) ) {
                $submenu_file = 'post.php?post=' . $flexible_mirror['ID'] . '&action=edit';
            }

            // Define submenu for Layouts menu
            $is_layout = PIP_Layouts::is_layout( acf_maybe_get_GET( 'post' ) );
            if ( acf_maybe_get_GET( 'layouts' ) === '1' || $is_layout || acf_maybe_get_GET( 'layout' ) === '1' ) {
                $submenu_file = 'edit.php?layouts=1&post_type=acf-field-group';
            }

            // Define submenu for Styles menu
            if ( acf_maybe_get_GET( 'page' ) === 'pip-styles' || pip_str_starts( acf_maybe_get_GET( 'page' ), 'pip-styles' ) ) {
                $submenu_file = 'pip-styles-tailwind';
            }

            // Define submenu for Pattern menu
            if ( acf_maybe_get_GET( 'page' ) === PIP_Pattern::get_pattern_option_page()['menu_slug'] ) {
                $submenu_file = PIP_Pattern::get_pattern_option_page()['menu_slug'];
            }

            // Define submenu for Component menu
            if ( strstr( $submenu_file, 'post_type=' . PIP_Components::$post_type ) ) {
                $submenu_file = 'edit.php?post_type=' . PIP_Components::$post_type;
            }

            return $submenu_file;
        }

        /**
         * Define parent menu for Layout menu
         */
        public function admin_menu_parent() {
            global $current_screen;

            // Define parent menu for Layouts menu
            $is_layout = PIP_Layouts::is_layout( acf_maybe_get_GET( 'post' ) );
            if (
                ( $current_screen->id === 'edit-acf-field-group' && acf_maybe_get_GET( 'layouts' ) === '1' )
                || $is_layout
                || acf_maybe_get_GET( 'layout' ) === '1'
                || pip_str_starts( acf_maybe_get_GET( 'page' ), 'pip-styles' ) ) :
                ?>
                <script type="text/javascript">
                    (
                        function ( $ ) {
                            $( '#toplevel_page_edit-post_type-acf-field-group' ).removeClass( 'wp-has-current-submenu' ).addClass( 'wp-not-current-submenu' )
                            $( '#toplevel_page_edit-post_type-acf-field-group > .wp-has-current-submenu' ).removeClass( 'wp-has-current-submenu' ).addClass( 'wp-not-current-submenu' )

                            $( '#toplevel_page_pilopress' ).addClass( 'wp-has-current-submenu' ).removeClass( 'wp-not-current-submenu' )
                            $( '#toplevel_page_pilopress > .wp-not-current-submenu' ).addClass( 'wp-has-current-submenu' ).removeClass( 'wp-not-current-submenu' )
                        }
                    )( jQuery )
                </script>
            <?php
            endif;
        }

        /**
         * Change "Add new" link on layouts page
         *
         * @param $url
         * @param $path
         *
         * @return string
         */
        public function change_admin_url( $url, $path ) {
            // Modify "Add new" link on layouts page
            if ( $path === 'post-new.php?post_type=acf-field-group' && acf_maybe_get_GET( 'layouts' ) === '1' ) {
                // Add argument
                $url = $url . '&layout=1';
            }

            // Modify "Add new" link on layout single page
            $is_layout = PIP_Layouts::is_layout( acf_maybe_get_GET( 'post' ) );
            if ( $path === 'post-new.php?post_type=acf-field-group' && $is_layout ) {

                // Add argument
                $url = $url . '&layout=1';

            }

            return $url;
        }

        /**
         * Allow mimes types
         *
         * @param $mimes
         *
         * @return mixed
         */
        public function allow_mimes_types( $mimes ) {
            $mimes['svg']   = 'image/svg+xml';
            $mimes['ttf']   = 'application/x-font-ttf';
            $mimes['woff']  = 'application/font-woff';
            $mimes['woff2'] = 'application/font-woff2';

            return $mimes;
        }

    }

    // Instantiate class
    new PIP_Admin();
}
