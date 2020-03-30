<?php

if ( !class_exists( 'PIP_Admin' ) ) {
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

            // AJAX hooks
            add_action( 'wp_ajax_compile_styles', array( $this, 'compile_styles' ) );
            add_action( 'wp_ajax_nopriv_compile_styles', array( $this, 'compile_styles' ) );
        }

        /**
         * Enqueue admin style & scripts
         */
        public function enqueue_scripts() {
            // Style
            wp_enqueue_style( 'pilopress-admin-style', PIP_URL . 'assets/css/pilopress-admin.css', array(), null );

            // Scripts
            wp_enqueue_script( 'pilopress-admin-script', PIP_URL . 'assets/js/pilopress-admin.js', array( 'jquery' ), null );
            wp_localize_script( 'pilopress-admin-script', 'ajaxurl', admin_url( 'admin-ajax.php' ) );

            self::maybe_enqueue_default_style();

            self::enqueue_layout_admin_style();
        }

        private static function enqueue_layout_admin_style() {
            // If not acf field group page, return
            if ( get_current_screen()->id !== 'acf-field-group' ) {
                return;
            }

            // If not layout page, return
            $post_id = acf_maybe_get_GET( 'post' );
            if ( !PIP_Layouts::is_layout( $post_id ) && acf_maybe_get_GET( 'layout' ) !== '1' ) {
                return;
            }

            wp_enqueue_style( 'pilopress-layout-admin-style', PIP_URL . 'assets/css/pilopress-layout-admin.css', array(), null );
        }

        /**
         * Maybe enqueue default bootstrap file
         */
        private static function maybe_enqueue_default_style() {
            $demo_page         = 'pilopress_page_pip-styles-demo';
            $style_file_exists = file_exists( PIP_THEME_STYLE_PATH . 'style-pilopress-admin.css' );
            $styles_pages      = strpos( get_current_screen()->id, 'pilopress_page_pip-styles' ) === 0;

            // Check if "style-pilopress-admin.css" enqueued
            global $wp_styles;
            $admin_style_enqueued = false;
            foreach ( $wp_styles->queue as $style ) {
                if ( $wp_styles->registered[ $style ]->src === get_stylesheet_directory_uri() . '/pilopress/style-pilopress-admin.css' ) {
                    $admin_style_enqueued = true;
                }
            }

            // If Pilo'Press admin style not enqueued
            if ( ( !$style_file_exists || $admin_style_enqueued === false ) && $styles_pages ) {

                // Add admin notice if no 'pilopress' folder
                if ( !$style_file_exists ) {
                    acf_add_admin_notice( '<p>Compilation impossible. Please create a <code>pilopress</code> folder in your theme.</p>', 'error' );
                }

                // Demo page
                if ( get_current_screen()->id === $demo_page ) {

                    // Enqueue default style
                    wp_enqueue_style( 'default-style-demo-admin', PIP_URL . 'assets/css/default-style-demo-admin.css', false );

                    if ( $admin_style_enqueued === false && !$style_file_exists ) {

                        // Add admin notice if no styles files
                        acf_add_admin_notice(
                            '<p>Pilo\'Press style not detected, default Bootstrap style is loaded.</p>',
                            'warning'
                        );

                    } elseif ( $admin_style_enqueued === false ) {

                        // Add admin notice if styles files not enqueued
                        acf_add_admin_notice(
                            '<p>Pilo\'Press style detected but not enqueued, default Bootstrap style is loaded.</p>',
                            'warning'
                        );

                    }
                }
            }
        }

        /**
         * AJAX action: compile_styles
         */
        public function compile_styles() {
            // Get action
            $action = filter_input( INPUT_POST, 'action', FILTER_SANITIZE_FULL_SPECIAL_CHARS );

            // If not compile_styles action, return
            if ( $action !== 'compile_styles' ) {
                return;
            }

            // Compile
            $compiled = PIP_Styles_Settings::compile_styles_settings();

            // Return result
            if ( is_array( $compiled ) ) {
                echo print_r( $compiled );
            } else {
                echo $compiled;
            }

            // End AJAX action
            die();
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
                $query->set( 'pip_post_content', array(
                    'compare' => 'LIKE',
                    'value'   => 's:14:"_pip_is_layout";i:1',
                ) );

            } elseif ( acf_maybe_get_GET( 'layouts' ) === null && acf_maybe_get_GET( 'post_status' ) != 'trash' ) {
                // Classic view

                // Remove layouts
                $query->set( 'pip_post_content', array(
                    'compare' => 'NOT LIKE',
                    'value'   => 's:14:"_pip_is_layout";i:1',
                ) );

                // Remove flexible
                $flexible_mirror = PIP_Flexible_Mirror::get_flexible_mirror_group();
                $query->set( 'post__not_in', array( $flexible_mirror['ID'] ) );
            }
        }

        /**
         * Add custom param for WP_Query
         *
         * @param string $where
         * @param WP_Query $wp_query
         *
         * @return mixed
         */
        public function query_pip_post_content( $where, $wp_query ) {
            global $wpdb;

            // If no custom var, return
            if ( !$pip_post_content = $wp_query->get( 'pip_post_content' ) ) {
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
                array( $this, 'pilopress_admin_page' ),
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
         * Pilo'Press admin page
         */
        public function pilopress_admin_page() {
            // Icons HTML
            $success_icon = '<span class="dashicons dashicons-yes"></span>';
            $error_icon   = '<span class="dashicons dashicons-no-alt"></span>';

            // Check if "style-pilopress-admin.css" enqueued
            global $wp_styles;
            $admin_style_enqueued = false;
            foreach ( $wp_styles->queue as $style ) {
                if ( $wp_styles->registered[ $style ]->src === get_stylesheet_directory_uri() . '/pilopress/style-pilopress-admin.css' ) {
                    $admin_style_enqueued = true;
                }
            }

            // Configurations
            $configurations = array(
                array(
                    'label'        => __( '<code>' . str_replace( get_home_url(), '', get_stylesheet_directory_uri() ) . '/pilopress/</code>', 'pilopress' ),
                    'status'       => file_exists( PIP_THEME_STYLE_PATH ),
                    'status_label' => file_exists( PIP_THEME_STYLE_PATH ) ? ' folder found' : ' folder not found',
                ),
                array(
                    'label'        => __( '<code>' . str_replace( get_home_url(), '', get_stylesheet_directory_uri() ) . '/pilopress/layouts/</code>', 'pilopress' ),
                    'status'       => file_exists( PIP_THEME_LAYOUTS_PATH ),
                    'status_label' => file_exists( PIP_THEME_LAYOUTS_PATH ) ? ' folder found' : ' folder not found',
                ),
                array(
                    'label'        => __( 'Admin style', 'pilopress' ),
                    'status'       => $admin_style_enqueued,
                    'status_label' => $admin_style_enqueued ? ' enqueued' : ' not enqueued',
                ),
            );

            // Layouts list
            $layouts      = array();
            $layouts_keys = PIP_Layouts::get_layout_group_keys();
            if ( is_array( $layouts_keys ) ) {
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
            }

            // Template file
            include_once( PIP_PATH . 'includes/views/pilopress-admin-page.php' );
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
            $wp_admin_bar->add_node( array(
                'id'    => 'pilopress',
                'title' => "<span class='pip-icon'></span> Pilo'Press",
                'href'  => add_query_arg( array( 'page' => 'pilopress' ), admin_url( 'admin.php' ) ),
            ) );

            // Styles
            $wp_admin_bar->add_node( array(
                'parent' => 'pilopress',
                'id'     => 'styles',
                'title'  => __( 'Styles', 'pilopress' ),
                'href'   => add_query_arg( array( 'page' => 'pip-styles-demo' ), admin_url( 'admin.php' ) ),
            ) );

            // Compile styles
            $wp_admin_bar->add_node( array(
                'parent' => 'pilopress',
                'id'     => 'compile_scss',
                'title'  => __( 'Compile styles', 'pilopress' ),
                'href'   => acf_get_current_url(),
            ) );
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

            // If layouts categories, return
            if ( $current_screen->taxonomy === 'acf-layouts-category' ) {
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
            if ( acf_maybe_get_GET( 'layouts' ) == 1 || $is_layout || acf_maybe_get_GET( 'layout' ) == 1 ) {
                $submenu_file = 'edit.php?layouts=1&post_type=acf-field-group';
            }

            // Define submenu for Styles menu
            if ( acf_maybe_get_GET( 'page' ) == 'pip-styles' || strpos( acf_maybe_get_GET( 'page' ), 'pip-styles' ) === 0 ) {
                $submenu_file = 'pip-styles-demo';
            }

            // Define submenu for Pattern menu
            if ( acf_maybe_get_GET( 'page' ) === PIP_Pattern::get_pattern_option_page()['menu_slug'] ) {
                $submenu_file = PIP_Pattern::get_pattern_option_page()['menu_slug'];
            }

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
            if ( ( $current_screen->id === 'edit-acf-field-group' && acf_maybe_get_GET( 'layouts' ) == 1 )
                 || $is_layout
                 || acf_maybe_get_GET( 'layout' ) == 1
                 || strpos( acf_maybe_get_GET( 'page' ), 'pip-styles' ) === 0 ) :
                ?>
                <script type="text/javascript">
                    (function ($) {
                        $('#toplevel_page_edit-post_type-acf-field-group').removeClass('wp-has-current-submenu').addClass('wp-not-current-submenu');
                        $('#toplevel_page_edit-post_type-acf-field-group > .wp-has-current-submenu').removeClass('wp-has-current-submenu').addClass('wp-not-current-submenu');

                        $('#toplevel_page_pilopress').addClass('wp-has-current-submenu').removeClass('wp-not-current-submenu');
                        $('#toplevel_page_pilopress > .wp-not-current-submenu').addClass('wp-has-current-submenu').removeClass('wp-not-current-submenu');
                    })(jQuery);
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
            if ( $path === 'post-new.php?post_type=acf-field-group' && acf_maybe_get_GET( 'layouts' ) == 1 ) {
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
