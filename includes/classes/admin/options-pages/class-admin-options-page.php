<?php

/**
 * Duplicate class
 *
 * @see acf_admin_options_page
 */

if ( !class_exists( 'PIP_Admin_Options_Page' ) ) {

    /**
     * Class PIP_Admin_Options_Page
     */
    class PIP_Admin_Options_Page {

        /**
         * Page
         *
         * @var array
         */
        public $page;

        /**
         * Pages
         *
         * @var array
         */
        public $pages;

        /**
         * PIP_Admin_Options_Page constructor.
         */
        public function __construct() {

            // Capability for Pilo'Press pages
            $capability = apply_filters( 'pip/options/capability', acf_get_setting( 'capability' ) );

            // Pages
            $this->pages = array(
                'configuration'   => array(
                    'page_title'     => __( 'Configuration', 'pilopress' ),
                    'menu_title'     => __( 'Styles', 'pilopress' ),
                    'menu_slug'      => 'pip_styles_configuration',
                    'post_id'        => 'pip_styles_configuration',
                    'capability'     => $capability,
                    'parent_slug'    => 'pilopress',
                    'update_button'  => __( 'Update', 'acf' ),
                    'update_message' => __( 'Options Updated', 'acf' ),
                    'autoload'       => 1,
                    'redirect'       => 0,
                    'icon_url'       => '',
                    'position'       => 82,
                ),
                'fonts'           => array(
                    'page_title'     => __( 'Fonts', 'pilopress' ),
                    'menu_title'     => __( 'Fonts', 'pilopress' ),
                    'menu_slug'      => 'pip_styles_fonts',
                    'post_id'        => 'pip_styles_fonts',
                    'capability'     => $capability,
                    'parent_slug'    => 'pip_styles_configuration',
                    'update_button'  => __( 'Update', 'acf' ),
                    'update_message' => __( 'Options Updated', 'acf' ),
                    'autoload'       => 1,
                    'redirect'       => 0,
                    'icon_url'       => '',
                    'position'       => 82,
                ),
                'image_sizes'     => array(
                    'page_title'     => __( 'Images', 'pilopress' ),
                    'menu_title'     => __( 'Images', 'pilopress' ),
                    'menu_slug'      => 'pip_styles_image_sizes',
                    'post_id'        => 'pip_styles_image_sizes',
                    'capability'     => $capability,
                    'parent_slug'    => 'pip_styles_configuration',
                    'update_button'  => __( 'Update', 'acf' ),
                    'update_message' => __( 'Options Updated', 'acf' ),
                    'autoload'       => 1,
                    'redirect'       => 0,
                    'icon_url'       => '',
                    'position'       => 82,
                ),
                'modules'         => array(
                    'page_title'     => __( 'Modules', 'pilopress' ),
                    'menu_title'     => __( 'Modules', 'pilopress' ),
                    'menu_slug'      => 'pip_styles_modules',
                    'post_id'        => 'pip_styles_modules',
                    'capability'     => $capability,
                    'parent_slug'    => 'pip_styles_configuration',
                    'update_button'  => __( 'Update', 'acf' ),
                    'update_message' => __( 'Options Updated', 'acf' ),
                    'autoload'       => 1,
                    'redirect'       => 0,
                    'icon_url'       => '',
                    'position'       => 82,
                ),
                'tailwind_module' => array(
                    'page_title'     => __( 'TailwindCSS', 'pilopress' ),
                    'menu_title'     => __( 'TailwindCSS', 'pilopress' ),
                    'menu_slug'      => 'pip_styles_tailwind_module',
                    'post_id'        => 'pip_styles_tailwind_module',
                    'capability'     => $capability,
                    'parent_slug'    => 'pip_styles_configuration',
                    'update_button'  => __( 'Update', 'acf' ),
                    'update_message' => __( 'Options Updated', 'acf' ),
                    'autoload'       => 1,
                    'redirect'       => 0,
                    'icon_url'       => '',
                    'position'       => 82,
                ),
            );

            add_action( 'admin_menu', array( $this, 'admin_menu' ), 99, 0 );
            add_filter( 'acf/location/rule_values', array( $this, 'rule_values' ), 10, 2 );
            add_filter( 'acfe/field_groups_third_party/source', array( $this, 'styles_options_page_source' ), 10, 3 );
        }

        /**
         * Check if page ID is a style setting page
         *
         * @param $page_id
         *
         * @return bool
         */
        public function is_style_page( $page_id ) {

            $is_style_page = false;

            if ( !$this->pages ) {
                return $is_style_page;
            }

            foreach ( $this->pages as $page ) {
                if ( $page['menu_slug'] === $page_id ) {
                    $is_style_page = true;
                }
            }

            return $is_style_page;
        }

        /**
         * Add submenus
         */
        public function admin_menu() {

            if ( !$this->pages ) {
                return;
            }

            foreach ( $this->pages as $key => $page ) {
                // Maybe hide modules pages
                $modules = pip_get_modules();
                if ( !acf_maybe_get( $modules, 'tailwind' ) && $key === 'tailwind-module' ) {
                    continue;
                }

                // Register submenu page
                $slug = add_submenu_page(
                    $page['parent_slug'],
                    $page['page_title'],
                    $page['menu_title'],
                    $page['capability'],
                    $page['menu_slug'],
                    array(
                        $this,
                        'html',
                    )
                );

                add_action( "load-{$slug}", array( $this, 'admin_load' ) );
            }
        }

        /**
         * Update options
         */
        public function admin_load() {

            global $plugin_page;

            // Get current page
            $this->page            = $this->pages[ str_replace( 'pip_styles_', '', $plugin_page ) ];
            $this->page['post_id'] = acf_get_valid_post_id( $this->page['post_id'] );

            // Validate
            if ( acf_verify_nonce( 'options' ) ) {
                if ( acf_validate_save_post( true ) ) {

                    // Updates
                    acf_update_setting( 'autoload', $this->page['autoload'] );
                    acf_save_post( $this->page['post_id'] );

                    // Redirect
                    wp_safe_redirect( add_query_arg( array( 'message' => '1' ) ) );
                    exit;
                }
            }

            // Enqueue scripts
            acf_enqueue_scripts();

            add_action( 'acf/input/admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
            add_action( 'acf/input/admin_head', array( $this, 'admin_head' ) );

            add_screen_option(
                'layout_columns',
                array(
                    'max'     => 2,
                    'default' => 2,
                )
            );
        }

        /**
         * Enqueue script
         */
        public function admin_enqueue_scripts() {

            wp_enqueue_script( 'post' );
        }

        /**
         * Output meat boxes
         */
        public function admin_head() {

            // Get current page
            $menu_slug  = acf_maybe_get_GET( 'page' );
            $this->page = $this->pages[ str_replace( 'pip_styles_', '', $menu_slug ) ];

            // Get associated field groups
            $field_groups = acf_get_field_groups(
                array(
                    'options_page' => $menu_slug,
                )
            );

            if ( acf_maybe_get_GET( 'message' ) === '1' ) {
                // Add notice
                acf_add_admin_notice( __( 'Options Updated', 'acf' ), 'success' );
            }

            // Add "Publish" meta box
            add_meta_box(
                'submitdiv',
                __( 'Publish', 'acf' ),
                array(
                    $this,
                    'postbox_submitdiv',
                ),
                'acf_options_page',
                'side',
                'high'
            );

            if ( empty( $field_groups ) ) {

                // No field group, display warning message
                // translators: Fields group URL
                acf_add_admin_notice( sprintf( __( 'No Custom Field Groups found for this options page. <a href="%s">Create a Custom Field Group</a>', 'acf' ), admin_url( 'post-new.php?post_type=acf-field-group' ) ), 'warning' );

            } else {
                foreach ( $field_groups as $i => $field_group ) {
                    $id       = "acf-{$field_group['key']}";
                    $title    = $field_group['title'];
                    $context  = $field_group['position'];
                    $priority = 'high';
                    $args     = array( 'field_group' => $field_group );

                    if ( $context === 'acf_after_title' ) {
                        $context = 'normal';
                    } elseif ( $context === 'side' ) {
                        $priority = 'core';
                    }

                    $priority = apply_filters( 'acf/input/meta_box_priority', $priority, $field_group );

                    // Add field group meta box
                    add_meta_box(
                        $id,
                        $title,
                        array(
                            $this,
                            'postbox_acf',
                        ),
                        'acf_options_page',
                        $context,
                        $priority,
                        $args
                    );
                }
            }
        }

        /**
         * Post box Submit
         *
         * @param $post
         * @param $args
         */
        public function postbox_submitdiv( $post, $args ) {

            do_action( 'acf/options_page/submitbox_before_major_actions', $this->page );
            ?>

            <div id="major-publishing-actions">

                <div id="publishing-action">
                    <span class="spinner"></span>
                    <input
                        type="submit" accesskey="p" value="<?php echo $this->page['update_button']; ?>"
                        class="button button-primary button-large" id="publish" name="publish">
                </div>

                <?php do_action( 'acf/options_page/submitbox_major_actions', $this->page ); ?>
                <div class="clear"></div>

            </div>

            <?php
        }

        /**
         * Post box ACF
         *
         * @param $post
         * @param $args
         */
        public function postbox_acf( $post, $args ) {

            $id          = $args['id'];
            $field_group = $args['args']['field_group'];

            // Field group object
            $field_group_object = array(
                'id'         => $id,
                'key'        => $field_group['key'],
                'style'      => $field_group['style'],
                'label'      => $field_group['label_placement'],
                'editLink'   => '',
                'editTitle'  => __( 'Edit field group', 'acf' ),
                'visibility' => true,
            );

            // If current user can edit field group, add edit link
            if ( $field_group['ID'] && acf_current_user_can_admin() ) {
                $field_group_object['editLink'] = admin_url( 'post.php?post=' . $field_group['ID'] . '&action=edit' );
            }

            // Get fields
            $fields = acf_get_fields( $field_group );

            // Render fields
            acf_render_fields( $fields, $this->page['post_id'], 'div', $field_group['instruction_placement'] );

            ?>
            <script type="text/javascript">
                if ( typeof acf !== 'undefined' ) {
                    acf.newPostbox(<?php echo wp_json_encode( $field_group_object ); ?>);
                }
            </script>
            <?php
        }

        /**
         * Output template
         */
        public function html() {

            // Get current page
            $menu_slug  = acf_maybe_get_GET( 'page' );
            $this->page = $this->pages[ str_replace( 'pip_styles_', '', $menu_slug ) ];

            // Define variables for template
            $page_title   = $this->page['page_title'];
            $post_id      = $this->page['post_id'];
            $pages        = $this->pages;
            $current_page = $menu_slug;
            $admin_url    = admin_url( 'admin.php' );

            // Display custom option page
            include_once PIP_PATH . 'includes/views/styles-admin-page.php';
        }

        /**
         * Set source to Pilo'Press for third party field groups
         *
         * @param $source
         * @param $post_id
         * @param $field_group
         *
         * @return string
         */
        public function styles_options_page_source( $source, $post_id, $field_group ) {

            if ( pip_str_starts( $post_id, 'group_styles_' ) || pip_str_starts( $post_id, 'group_pip_' ) ) {
                $source = "Pilo'Press";
            }

            return $source;
        }

        /**
         * Add custom pages to ACF locations
         *
         * @param $values
         * @param $rule
         *
         * @return mixed
         */
        public function rule_values( $values, $rule ) {

            // If not admin or not AJAX, return
            if ( !is_admin() && !wp_doing_ajax() ) {
                return $values;
            }

            // If not options pages, return
            if ( $rule['param'] !== 'options_page' ) {
                return $values;
            }

            // If pages not defined, return
            if ( !$this->pages ) {
                return $values;
            }

            // Add custom pages
            foreach ( $this->pages as $page ) {
                $values[ $page['menu_slug'] ] = $page['page_title'];
            }

            // Unset "No options pages exist"
            unset( $values[''] );

            return $values;
        }

    }

    acf_new_instance( 'PIP_Admin_Options_Page' );

}
