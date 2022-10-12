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
            add_filter( 'posts_where', array( $this, 'query_pip_post_content' ), 10, 2 );
            add_filter( 'admin_url', array( $this, 'change_admin_url' ), 10, 2 );
            add_filter( 'upload_mimes', array( $this, 'allow_mimes_types' ) );
            add_action( 'in_admin_header', array( $this, 'add_pip_navbar' ) );
            add_action( 'admin_notices', array( $this, 'no_pilopress_folder_notice' ) );

            // ACF hooks
            add_action( 'acf/save_post', array( $this, 'save_styles_settings' ), 20, 1 );
        }

        /**
         * Save default values for every styles pages
         *
         * @param $post_id
         */
        public function save_styles_settings( $post_id ) {

            // If not on Styles admin page, return
            if ( !pip_str_starts( $post_id, 'pip_styles_' ) ) {
                return;
            }

            // If assets folder doesn't exists, return
            if ( !file_exists( PIP_THEME_ASSETS_PATH ) ) {
                return;
            }

            // Save all styles pages
            $other_style_pages = $this->get_style_admin_pages();

            // Remove current page
            $key = array_search( $post_id, $other_style_pages, true );
            if ( false !== $key ) {
                unset( $other_style_pages[ $key ] );
            }

            // Maybe re-save default values
            foreach ( $other_style_pages as $other_style_page ) {
                $fields = get_fields( $other_style_page );
                if ( $fields ) {
                    continue;
                }

                PIP_Tailwind::save_default_values( $other_style_page );
            }

        }

        /**
         * Get styles pages post IDs
         *
         * @return string[]
         */
        public function get_style_admin_pages() {
            return array(
                'pip_styles_configuration',
                'pip_styles_fonts',
                'pip_styles_image_sizes',
                'pip_styles_modules',
                'pip_styles_tailwind_module',
            );
        }

        /**
         * Add notice if theme doesn't support Pilo'Press (folders doesn't exists)
         */
        public function no_pilopress_folder_notice() {
            // Pilo'Press folder exists, return
            if ( file_exists( PIP_THEME_PILOPRESS_PATH ) ) {
                return;
            }

            // Get current screen data
            $current_screen = get_current_screen();
            $parent_base    = pip_maybe_get( $current_screen, 'parent_base' );

            // If not an edit page, return
            if ( $parent_base !== 'edit' ) {
                return;
            }

            // Display notice
            $pilopress_url = add_query_arg(
                array(
                    'page' => 'pilopress',
                ),
                get_admin_url( get_current_blog_id(), 'admin.php' )
            );
            ?>
            <div class="notice notice-error is-dismissible">
                <p>
                    <?php
                    // translators: Pilo'Press dashboard URL
                    echo sprintf( __( "Your current theme does not support Pilo'Press. See <a href='%s'>configuration status</a> for more details.", 'pilopress' ), $pilopress_url );
                    ?>
                </p>
            </div>
            <?php
        }

        /**
         * Check if it is a Pilo'Press admin page
         *
         * @return bool
         */
        public function is_pip_admin_page() {

            $is_pip_admin = false;

            $pip_layouts             = acf_get_instance( 'PIP_Layouts' );
            $pip_components          = acf_get_instance( 'PIP_Components' );
            $pip_pattern             = acf_get_instance( 'PIP_Pattern' );
            $pip_admin_options_page  = acf_get_instance( 'PIP_Admin_Options_Page' );
            $pip_layouts_categories  = acf_get_instance( 'PIP_Layouts_Categories' );
            $pip_layouts_collections = acf_get_instance( 'PIP_Layouts_Collections' );

            $flexible_mirror_id = pip_get_flexible_mirror_group_id();

            // If no flexible mirror, return
            if ( !$flexible_mirror_id ) {
                return false;
            }

            // If Pilo'Press admin page, set variable to true
            if (
                acf_maybe_get_GET( 'layouts' ) === '1' ||
                acf_maybe_get_GET( 'layout' ) === '1' ||
                $pip_layouts->is_layout( get_post( acf_maybe_get_GET( 'post' ) ) ) ||
                (int) acf_maybe_get_GET( 'post' ) === $flexible_mirror_id ||
                acf_maybe_get_GET( 'taxonomy' ) === $pip_layouts_categories->taxonomy_name ||
                acf_maybe_get_GET( 'taxonomy' ) === $pip_layouts_collections->taxonomy_name ||
                acf_maybe_get_GET( 'post_type' ) === $pip_components->post_type ||
                $pip_components->is_component( acf_maybe_get_GET( 'post' ) ) ||
                acf_maybe_get_GET( 'page' ) === $pip_pattern->menu_slug ||
                strstr( acf_maybe_get_GET( 'page' ), 'pip_addon' ) ||
                $pip_admin_options_page->is_style_page( acf_maybe_get_GET( 'page' ) )
            ) {
                $is_pip_admin = true;
            }

            return $is_pip_admin;
        }

        /**
         * Add Pilo'Press top navbar
         */
        public function add_pip_navbar() {

            if ( $this->is_pip_admin_page() ) {
                $this->display_pip_navbar();
            }
        }

        /**
         * Enqueue admin style & scripts
         */
        public function enqueue_scripts() {

            // Styles
            wp_enqueue_style( 'pilopress-admin-style', PIP_URL . 'assets/css/pilopress-admin.css', array(), pilopress()->version );
            $this->maybe_enqueue_layout_admin_style();

            // Scripts
            wp_enqueue_script( 'pilopress-admin-script', PIP_URL . 'assets/js/pilopress-admin.js', array( 'jquery' ), pilopress()->version, true );
            wp_localize_script( 'pilopress-admin-script', 'ajaxurl', array( admin_url( 'admin-ajax.php' ) ) );
            wp_enqueue_script( 'pilopress-fields', PIP_URL . 'assets/js/pilopress-fields.js', array( 'jquery' ), pilopress()->version, true );
            wp_enqueue_script( 'pilopress-live-preview', PIP_URL . 'assets/js/pilopress-live-preview.js', array( 'jquery' ), pilopress()->version, true );

            // String translation for JS
            acf_localize_text(
                array(
                    'Search for a layout' => __( 'Search for a layout', 'pilopress' ),
                    'No previous layout'  => __( 'No previous layout', 'pilopress' ),
                    'No next layout'      => __( 'No next layout', 'pilopress' ),
                )
            );
        }

        /**
         * Enqueue layouts admin style
         */
        public function maybe_enqueue_layout_admin_style() {

            // If not acf field group page, return
            if ( get_current_screen()->id !== 'acf-field-group' && get_current_screen()->id !== 'edit-acf-field-group' ) {
                return;
            }

            $pip_layouts = acf_get_instance( 'PIP_Layouts' );

            // If layout(s) page, enqueue style
            $post_id = acf_maybe_get_GET( 'post' );
            if ( $pip_layouts->is_layout( $post_id ) || acf_maybe_get_GET( 'layouts' ) === '1' || acf_maybe_get_GET( 'layout' ) === '1' ) {
                wp_enqueue_style( 'pilopress-layout-admin-style', PIP_URL . 'assets/css/pilopress-layout-admin.css', array(), pilopress()->version );
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

            $pip_components = acf_get_instance( 'PIP_Components' );

            // Capability
            $capability = apply_filters( 'pip/options/capability', acf_get_setting( 'capability' ) );

            if ( !current_user_can( $capability ) ) {
                return;
            }

            // Top Menu
            $menu = array(
                'title' => __( "Pilo'Press", 'pilopress' ),
                'slug'  => 'pilopress',
                'cap'   => $capability,
                'cb'    => array( $this, 'pilopress_dashboard' ),
                'icon'  => 'data:image/svg+xml;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=', // (1x1 pixel https://png-pixel.com)
                'pos'   => 82,
            );

            add_menu_page( $menu['title'], $menu['title'], $menu['cap'], $menu['slug'], $menu['cb'], $menu['icon'], $menu['pos'] );

            // Submenu
            $submenus = array(

                // Dashboard
                array(
                    'parent' => 'pilopress',
                    'title'  => __( 'Dashboard', 'pilopress' ),
                    'slug'   => 'pilopress',
                    'cap'    => $capability,
                ),

                // Builder
                array(
                    'parent' => 'pilopress',
                    'title'  => __( 'Builder', 'pilopress' ),
                    'slug'   => 'post.php?post=' . pip_get_flexible_mirror_group_id() . '&action=edit',
                    'cap'    => $capability,
                ),

                // Layouts
                array(
                    'parent' => 'pilopress',
                    'title'  => __( 'Layouts', 'pilopress' ),
                    'slug'   => 'edit.php?post_type=acf-field-group&layouts=1',
                    'cap'    => $capability,
                ),

                // Categories
                array(
                    'parent' => 'pilopress',
                    'title'  => __( 'Categories', 'pilopress' ),
                    'slug'   => 'edit-tags.php?taxonomy=acf-layouts-category',
                    'cap'    => $capability,
                ),

                // Collections
                array(
                    'parent' => 'pilopress',
                    'title'  => __( 'Collections', 'pilopress' ),
                    'slug'   => 'edit-tags.php?taxonomy=acf-layouts-collection',
                    'cap'    => $capability,
                ),

                // Components
                array(
                    'parent' => 'pilopress',
                    'title'  => __( 'Components', 'pilopress' ),
                    'slug'   => 'edit.php?post_type=' . $pip_components->post_type,
                    'cap'    => $capability,
                ),
            );

            // Add submenus
            foreach ( $submenus as $submenu ) {
                add_submenu_page( $submenu['parent'], $submenu['title'], $submenu['title'], $submenu['cap'], $submenu['slug'] );
            }

        }

        /**
         * Pilo'Press dashboard
         */
        public function pilopress_dashboard() {

            // Icons HTML
            $success_icon = '<span class="dashicons dashicons-yes"></span>';
            $error_icon   = '<span class="dashicons dashicons-no-alt"></span>';

            $pip_components = acf_get_instance( 'PIP_Components' );

            // Check if "style-admin.min.css" is enqueued
            global $wp_styles;
            $admin_style_enqueued = false;
            if ( pip_maybe_get( $wp_styles, 'queue' ) ) {
                foreach ( $wp_styles->queue as $style ) {

                    // If not Pilo'Press admin style, skip
                    if ( $wp_styles->registered[ $style ]->handle !== 'style-pilopress-admin' ) {
                        continue;
                    }

                    $admin_style_enqueued = true;
                    break;
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
            $layouts = array();

            foreach ( pip_get_layouts() as $layout ) {

                // Structured array for template file
                $layouts[] = array(
                    'field_group' => $layout,
                );

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
                    'post_type'      => $pip_components->post_type,
                    'posts_per_page' => - 1,
                )
            );

            // New field group link
            $add_new_component = add_query_arg(
                array(
                    'post_type' => $pip_components->post_type,
                ),
                admin_url( 'post-new.php' )
            );

            // All components link
            $all_components = add_query_arg(
                array(
                    'post_type' => $pip_components->post_type,
                ),
                admin_url( 'edit.php' )
            );

            // Template file
            include_once PIP_PATH . 'includes/views/pip-dashboard.php';
        }

        /**
         * Display Pilo'Press navbar
         */
        public function display_pip_navbar() {

            // Hide ACF top navbar
            add_filter( 'acf/admin/toolbar', '__return_false' );

            // Get menu items
            global $submenu;
            $pilopress_menu = acf_maybe_get( $submenu, 'pilopress' );
            if ( $pilopress_menu ) {
                foreach ( $pilopress_menu as $menu_item ) {
                    $menu_items[] = array(
                        'title' => $menu_item[0],
                        'link'  => strstr( $menu_item[2], '.php?' ) ? admin_url() . $menu_item[2] : menu_page_url( $menu_item[2], false ),
                    );
                }
            }

            // Add Pilo'Press navbar
            include_once PIP_PATH . 'includes/views/pip-navbar.php';
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

            // Edit locked content (patterns)
            if ( !is_admin() ) {

                $queried_object_id = get_queried_object_id();

                // Fuzzy title / link while we don't have enough info on current content
                $edit_locked_content_icon = '<i class="dashicons-before dashicons-layout" style="display: inline-flex; vertical-align: text-top; align-items: center; opacity: .6"></i>';
                $edit_locked_content_link = add_query_arg(
                    array(
                        'post_type' => PIP_Patterns::get_locked_content_slug(),
                    ),
                    admin_url( 'edit.php' )
                );

                $pattern_post_id = PIP_Locked_Content::get_locked_content( $queried_object_id );

                $pattern_title = get_post_meta( $pattern_post_id, 'linked_post_type', true ) ?
                    get_post_meta( $pattern_post_id, 'linked_post_type', true ) :
                    get_post_meta( $pattern_post_id, 'linked_taxonomy', true );
                $pattern_title = str_replace( '_', ' ', $pattern_title );
                $pattern_title = str_replace( '-', ' ', $pattern_title );

                $has_custom_locked_content = PIP_Locked_Content::has_custom_locked_content( $queried_object_id );

                $edit_locked_content_title  = "$edit_locked_content_icon ";
                $edit_locked_content_title .= $has_custom_locked_content ?
                    __( 'Edit locked content', 'pilopress' ) . ' (' . ucfirst( $pattern_title ) . ')' :
                    __( 'Set locked content', 'pilopress' ) . ' (' . ucfirst( $pattern_title ) . ')';

                if ( !is_404() ) {
                    $wp_admin_bar->add_node(
                        array(
                            'id'    => 'patterns',
                            'title' => $edit_locked_content_title,
                            'href'  => $edit_locked_content_link,
                        )
                    );
                }
            }

            // Pilo'Press icon
            ob_start(); ?>
            <span
                class='pip-icon'
                style='display: inline-block; width: 26px; height: 20px; vertical-align: text-top; float: none; background: url(data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyMCAyMCIgZmlsbD0iI2EwYTVhYSI+PHBhdGggZD0iTTEwIC4yQzQuNi4yLjMgNC42LjMgMTBzNC40IDkuOCA5LjcgOS44YzIuNiAwIDUuMS0xIDYuOS0yLjggMS44LTEuOCAyLjgtNC4zIDIuOC02LjkgMC01LjUtNC4zLTkuOS05LjctOS45em02LjQgMTYuM2MtMS43IDEuNy00IDIuNi02LjQgMi42LTUgMC05LTQuMS05LTkuMVM1IC45IDEwIC45IDE5IDUgMTkgMTBjMCAyLjUtLjkgNC43LTIuNiA2LjV6Ii8+PHBhdGggZD0iTTEwIDUuM2MtMi41IDAtNC42IDIuMS00LjYgNC43di41Yy4yIDEuOCAxLjQgMy4zIDMgMy45LjUuMiAxIC4zIDEuNS4zLjQgMCAuOS0uMSAxLjMtLjIuMSAwIC4xIDAgLjItLjEuMy0uMS41LS4yLjgtLjMgMCAwIC4xIDAgLjEtLjEgMCAwIC4xIDAgLjEtLjFoLjFzLjEgMCAuMS0uMWMwIDAgLjEgMCAuMS0uMS4yLS4yLjUtLjQuNy0uNmwuMy0uM2MuNi0uOCAxLTEuOSAxLTIuOSAwLTIuNS0yLjEtNC42LTQuNy00LjZ6bTMuMSA3LjNjMC0uMSAwLS4xIDAgMC0uNi0uNC0uNy0uOS0uNy0xLjR2LS40LS4xLS4zYzAtLjctLjItMS41LTEuNS0xLjYtLjUgMC0xLjMuMS0yLjMuNC0uMi0uMS0uNCAwLS42LjEtLjYuMi0xLjIuNC0yIC43IDAtMi4yIDEuOC00IDMuOS00IDEuNSAwIDIuOC44IDMuNSAyLjEuNC42LjYgMS4yLjYgMS45IDAgLjktLjMgMS44LS45IDIuNnoiLz48L3N2Zz4=) center center no-repeat;'>
            </span>
            <?php
            $pilopress_icon = ob_get_clean();

            // Pilo'Press menu
            $wp_admin_bar->add_node(
                array(
                    'id'    => 'pilopress',
                    'title' => "$pilopress_icon Pilo'Press",
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
                            'page' => 'pip_styles_configuration',
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

            // Highlight Pilo'Press in Layouts + Styles
            if ( pip_is_layout_screen() || pip_str_starts( acf_maybe_get_GET( 'page' ), 'pip_styles' ) ) {

                global $pagenow, $plugin_page;

                $pagenow     = 'pilopress';
                $plugin_page = 'pilopress';

            }

            $pip_components = acf_get_instance( 'PIP_Components' );

            // Define parent menu for Flexible menu
            if ( (int) acf_maybe_get_GET( 'post' ) === pip_get_flexible_mirror_group_id() ) {
                $parent_file = 'pilopress';
            }

            // Define parent menu for Components menu
            if ( strstr( $parent_file, 'post_type=' . $pip_components->post_type ) ) {
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

            $pip_components          = acf_get_instance( 'PIP_Components' );
            $pip_layouts             = acf_get_instance( 'PIP_Layouts' );
            $pip_pattern             = acf_get_instance( 'PIP_Pattern' );
            $pip_layouts_categories  = acf_get_instance( 'PIP_Layouts_Categories' );
            $pip_layouts_collections = acf_get_instance( 'PIP_Layouts_Collections' );

            // If layouts categories or collections, return
            if (
                $current_screen->taxonomy === $pip_layouts_categories->taxonomy_name
                || $current_screen->taxonomy === $pip_layouts_collections->taxonomy_name
            ) {
                return $submenu_file;
            }

            // Define submenu for Flexible menu
            if (
                (int) acf_maybe_get_GET( 'post' ) === pip_get_flexible_mirror_group_id()
                && !acf_maybe_get_GET( 'page' )
            ) {
                $submenu_file = 'post.php?post=' . pip_get_flexible_mirror_group_id() . '&action=edit';
            }

            // Define submenu for Layouts menu
            $is_layout = $pip_layouts->is_layout( acf_maybe_get_GET( 'post' ) );
            if ( acf_maybe_get_GET( 'layouts' ) === '1' || $is_layout || acf_maybe_get_GET( 'layout' ) === '1' ) {
                $submenu_file = 'edit.php?post_type=acf-field-group&layouts=1';
            }

            // Define submenu for Styles menu
            if (
                acf_maybe_get_GET( 'page' ) === 'pip_styles'
                || pip_str_starts( acf_maybe_get_GET( 'page' ), 'pip_styles' )
            ) {
                $submenu_file = 'pip_styles_configuration';
            }

            // Define submenu for Pattern menu
            if ( acf_maybe_get_GET( 'page' ) === $pip_pattern->menu_slug ) {
                $submenu_file = $pip_pattern->menu_slug;
            }

            // Define submenu for Component menu
            if ( strstr( $submenu_file, 'post_type=' . $pip_components->post_type ) ) {
                $submenu_file = 'edit.php?post_type=' . $pip_components->post_type;
            }

            return $submenu_file;
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

            $pip_layouts = acf_get_instance( 'PIP_Layouts' );

            // Modify "Add new" link on layout single page
            $is_layout = $pip_layouts->is_layout( acf_maybe_get_GET( 'post' ) );
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

            $mimes['ttf']   = 'application/x-font-ttf';
            $mimes['woff']  = 'application/font-woff';
            $mimes['woff2'] = 'application/font-woff2';
            $mimes['eot']   = 'application/vnd.ms-fontobject';

            return $mimes;
        }

    }

    acf_new_instance( 'PIP_Admin' );

}
