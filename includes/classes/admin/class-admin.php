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
            $PIP_Admin_Options_Page  = acf_get_instance( 'PIP_Admin_Options_Page' );
            $PIP_Layouts_Categories  = acf_get_instance( 'PIP_Layouts_Categories' );
            $PIP_Layouts_Collections = acf_get_instance( 'PIP_Layouts_Collections' );

            $flexible_mirror_id = pip_get_flexible_mirror_group_id();

            // If no flexible mirror, return
            if ( !$flexible_mirror_id ) {
                return false;
            }

            // If Pilo'Press admin page, set variable to true
            if (
                acf_maybe_get_GET( 'layouts' ) == '1' ||
                $pip_layouts->is_layout( get_post( acf_maybe_get_GET( 'post' ) ) ) ||
                (int) acf_maybe_get_GET( 'post' ) === $flexible_mirror_id ||
                acf_maybe_get_GET( 'taxonomy' ) === $PIP_Layouts_Categories->taxonomy_name ||
                acf_maybe_get_GET( 'taxonomy' ) === $PIP_Layouts_Collections->taxonomy_name ||
                acf_maybe_get_GET( 'post_type' ) === $pip_components->post_type ||
                $pip_components->is_component( acf_maybe_get_GET( 'post' ) ) ||
                acf_maybe_get_GET( 'page' ) == $pip_pattern->menu_slug ||
                strstr( acf_maybe_get_GET( 'page' ), 'pip_addon' ) ||
                $PIP_Admin_Options_Page->is_style_page( acf_maybe_get_GET( 'page' ) )
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
            wp_localize_script( 'pilopress-admin-script', 'ajaxurl', admin_url( 'admin-ajax.php' ) );
            wp_enqueue_script( 'pilopress-fields', PIP_URL . 'assets/js/pilopress-fields.js', array( 'jquery' ), pilopress()->version, true );
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
         * @param string $where
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

            $pip_components      = acf_get_instance( 'PIP_Components' );

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
                'cb'    => array($this, 'pilopress_dashboard'),
                'icon'  => 'data:image/svg+xml;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=', // (1x1 pixel https://png-pixel.com)
                'pos'   => 82,
            );

            add_menu_page( $menu['title'], $menu['title'], $menu['cap'], $menu['slug'], $menu['cb'], $menu['icon'], $menu['pos']);

            // Submenu
            $submenus = array(

                // Dashboard
                array(
                    'parent'    => 'pilopress',
                    'title'     => __( 'Dashboard', 'pilopress' ),
                    'slug'      => 'pilopress',
                    'cap'       => $capability,
                ),

                // Builder
                array(
                    'parent'    => 'pilopress',
                    'title'     => __( 'Builder', 'pilopress' ),
                    'slug'      => 'post.php?post=' . pip_get_flexible_mirror_group_id() . '&action=edit',
                    'cap'       => $capability,
                ),

                // Layouts
                array(
                    'parent'    => 'pilopress',
                    'title'     => __( 'Layouts', 'pilopress' ),
                    'slug'      => 'edit.php?post_type=acf-field-group&layouts=1',
                    'cap'       => $capability,
                ),

                // Categories
                array(
                    'parent'    => 'pilopress',
                    'title'     => __( 'Categories', 'pilopress' ),
                    'slug'      => 'edit-tags.php?taxonomy=acf-layouts-category',
                    'cap'       => $capability,
                ),

                // Collections
                array(
                    'parent'    => 'pilopress',
                    'title'     => __( 'Collections', 'pilopress' ),
                    'slug'      => 'edit-tags.php?taxonomy=acf-layouts-collection',
                    'cap'       => $capability,
                ),

                // Components
                array(
                    'parent'    => 'pilopress',
                    'title'     => __( 'Components', 'pilopress' ),
                    'slug'      => 'edit.php?post_type=' . $pip_components->post_type,
                    'cap'       => $capability,
                ),
            );

            foreach($submenus as $submenu){

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

            // Check if "style-admin.min.css" enqueued
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
            $add_new_layout = add_query_arg( array(
                'post_type' => 'acf-field-group',
                'layout'    => 1,
            ), admin_url( 'post-new.php' ) );

            // All layouts link
            $all_layouts = add_query_arg( array(
                'post_type' => 'acf-field-group',
                'layouts'   => 1,
            ), admin_url( 'edit.php' ) );

            // Components
            $components = get_posts( array(
                'post_type'      => $pip_components->post_type,
                'posts_per_page' => - 1,
            ) );

            // New field group link
            $add_new_component = add_query_arg( array(
                'post_type' => $pip_components->post_type,
            ), admin_url( 'post-new.php' ) );

            // All components link
            $all_components = add_query_arg( array(
                'post_type' => $pip_components->post_type,
            ), admin_url( 'edit.php' ) );

            // Template file
            include_once( PIP_PATH . 'includes/views/pip-dashboard.php' );
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
            $wp_admin_bar->add_node( array(
                'id'    => 'pilopress',
                'title' => "<span class='pip-icon'></span> Pilo'Press",
                'href'  => add_query_arg( array( 'page' => 'pilopress' ), admin_url( 'admin.php' ) ),
            ) );

            // Layouts
            $wp_admin_bar->add_node( array(
                'parent' => 'pilopress',
                'id'     => 'layouts',
                'title'  => __( 'Layouts', 'pilopress' ),
                'href'   => add_query_arg( array(
                    'layouts'   => 1,
                    'post_type' => 'acf-field-group',
                ), admin_url( 'edit.php' ) ),
            ) );

            // Styles
            $wp_admin_bar->add_node( array(
                'parent' => 'pilopress',
                'id'     => 'styles',
                'title'  => __( 'Styles', 'pilopress' ),
                'href'   => add_query_arg( array(
                    'page' => 'pip-styles-configuration',
                ), admin_url( 'admin.php' ) ),
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

            // Highlight Pilo'Press in Layouts + Styles
            if(pip_is_layout_screen() || pip_str_starts( acf_maybe_get_GET( 'page' ), 'pip-styles' )){

                global $pagenow, $plugin_page;

                $pagenow = 'pilopress';
                $plugin_page = 'pilopress';

            }

            $pip_components      = acf_get_instance( 'PIP_Components' );

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
            if ( $current_screen->taxonomy === $pip_layouts_categories->taxonomy_name || $current_screen->taxonomy === $pip_layouts_collections->taxonomy_name ) {
                return $submenu_file;
            }

            // Define submenu for Flexible menu
            if ( (int) acf_maybe_get_GET( 'post' ) === pip_get_flexible_mirror_group_id() && !acf_maybe_get_GET( 'page' ) ) {
                $submenu_file = 'post.php?post=' . pip_get_flexible_mirror_group_id() . '&action=edit';
            }

            // Define submenu for Layouts menu
            $is_layout = $pip_layouts->is_layout( acf_maybe_get_GET( 'post' ) );
            if ( acf_maybe_get_GET( 'layouts' ) === '1' || $is_layout || acf_maybe_get_GET( 'layout' ) === '1' ) {
                $submenu_file = 'edit.php?post_type=acf-field-group&layouts=1';
            }

            // Define submenu for Styles menu
            if ( acf_maybe_get_GET( 'page' ) === 'pip-styles' || pip_str_starts( acf_maybe_get_GET( 'page' ), 'pip-styles' ) ) {
                $submenu_file = 'pip-styles-configuration';
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

            $mimes['svg']   = 'image/svg+xml';
            $mimes['ttf']   = 'application/x-font-ttf';
            $mimes['woff']  = 'application/font-woff';
            $mimes['woff2'] = 'application/font-woff2';

            return $mimes;
        }

    }

    acf_new_instance( 'PIP_Admin' );

}
