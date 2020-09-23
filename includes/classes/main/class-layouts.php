<?php

if ( !class_exists( 'PIP_Layouts' ) ) {

    /**
     * Class PIP_Layouts
     */
    class PIP_Layouts {

        /**
         * Layouts group keys
         *
         * @var array
         */
        var $layout_group_keys = array();

        public function __construct() {

            // WP hooks
            add_action( 'init', array( $this, 'enqueue_configuration_files' ), 5 );
            add_action( 'current_screen', array( $this, 'current_screen' ) );
            add_action( 'register_post_type_args', array( $this, 'modify_acf_post_type' ), 10, 2 );
            add_filter( 'display_post_states', array( $this, 'hide_layouts_post_states' ), 20 );

            // Create files and folder or rename folder
            add_action( 'wp_insert_post', array( $this, 'save_field_group' ), 20, 3 );

            add_filter( 'get_user_option_meta-box-order_acf-field-group', array( $this, 'metabox_order' ) );

        }

        /**
         * Fire actions on layouts pages
         */
        public function current_screen() {

            // If not ACF field group single, return
            if ( !acf_is_screen( 'acf-field-group' ) ) {
                return;
            }

            add_filter( 'acf/validate_field_group', array( $this, 'layout_locations' ), 20 );
            add_action( 'acf/field_group/admin_head', array( $this, 'layout_meta_boxes' ) );
            add_action( 'acf/input/admin_head', array( $this, 'layout_settings' ), 20 );
            add_action( 'acf/update_field_group', array( $this, 'set_field_group_to_inactive' ) );

        }

        /**
         * Hide "Disabled" state
         *
         * @param $states
         *
         * @return mixed
         */
        public function hide_layouts_post_states( $states ) {

            // If not Layouts page, return
            if ( acf_maybe_get_GET( 'layouts' ) != '1' ) {
                return $states;
            }

            // Unset disabled state
            unset( $states['acf-disabled'] );

            return $states;
        }

        /**
         * Remove old layout group keys
         *
         * @return array
         */
        public function clean_group_keys() {

            $clean_array = array();

            // Get Layout group keys
            $layout_group_keys = $this->get_layout_group_keys();
            if ( !$layout_group_keys ) {
                return $clean_array;
            }

            // Browse all group keys
            foreach ( $layout_group_keys as $layout_group_key ) {
                // Get field group
                $field_group = acf_get_field_group( $layout_group_key );

                if ( $field_group ) {

                    // If field group, stock it
                    $clean_array[] = $layout_group_key;

                } else {

                    // If no field group, remove from group keys
                    unset( $layout_group_keys[ $layout_group_key ] );
                    $this->set_layout_group_keys( $layout_group_keys );

                }
            }

            return $clean_array;
        }

        /**
         * Force layout to be inactive
         *
         * @param $field_group
         *
         * @return array|bool|void
         */
        public function set_field_group_to_inactive( $field_group ) {

            // Is current post a layout ?
            $is_layout = $this->is_layout( $field_group );
            if ( !$is_layout ) {
                return;
            }

            // Set active to false
            if ( $field_group['active'] ) {
                $field_group['active'] = 0;
                acf_update_field_group( $field_group );
            }
        }

        /**
         * Force layout to be inactive
         */
        public function layout_settings() {

            // Get current field group
            global $field_group;

            // Is current field group a layout ?
            $is_layout = $this->is_layout( $field_group );
            if ( !$is_layout ) {
                return;
            }

            // Set active to false
            $field_group['active'] = 0;
        }

        /**
         * Change title on layouts pages
         *
         * @param $args
         * @param $post_type
         *
         * @return mixed
         */
        public function modify_acf_post_type( $args, $post_type ) {

            // If AJAX, not admin or not acf-field-group, return
            if ( wp_doing_ajax() || !is_admin() || $post_type !== 'acf-field-group' ) {
                return $args;
            }

            // Get current post
            $post = get_post( acf_maybe_get_GET( 'post' ) );

            // Layouts
            $is_layout = $this->is_layout( $post );
            if ( acf_maybe_get_GET( 'layouts' ) === '1' || $is_layout || acf_maybe_get_GET( 'layout' ) === '1' ) {

                // Change title on layouts pages
                $args['labels']['name']         = __( 'Layouts', 'pilopress' );
                $args['labels']['edit_item']    = __( 'Edit Layout', 'pilopress' );
                $args['labels']['add_new_item'] = __( 'Add New Layout', 'pilopress' );
            }

            return $args;
        }

        /**
         * Layouts Default Locations
         */
        function layout_locations( $field_group ) {

            // Only in new layouts
            if ( acf_maybe_get( $field_group, 'location' ) ) {
                return $field_group;
            }

            $pip_flexible_mirror = acf_get_instance( 'PIP_Flexible_Mirror' );

            // Get Flexible Mirror
            $flexible_mirror = $pip_flexible_mirror->get_flexible_mirror_group();

            $field_group['location'] = $flexible_mirror['location'];

            return $field_group;

        }

        /**
         * Pilo'Press meta boxes
         */
        public function layout_meta_boxes() {

            // Get current field group
            global $field_group;

            $pip_flexible_mirror = acf_get_instance( 'PIP_Flexible_Mirror' );

            // If mirror flexible page, remove categories boxes and return
            if ( $field_group['key'] === $pip_flexible_mirror->get_flexible_mirror_group_key() ) {

                // Remove categories meta boxes
                remove_meta_box( 'acf-field-group-categorydiv', 'acf-field-group', 'side' );
                remove_meta_box( 'acf-layouts-categorydiv', 'acf-field-group', 'side' );

                return;
            }

            // Is current field group a layout ?
            $is_layout = $this->is_layout( $field_group );

            if ( $is_layout ) {

                // Meta box: Layout settings
                add_meta_box( 'pip_layout_settings', __( "Pilo'Press: Layout settings", 'pilopress' ), array(
                    $this,
                    'render_meta_box_main',
                ), 'acf-field-group', 'normal', 'high', array(
                    'field_group' => $field_group,
                ) );

                // Meta box: Thumbnail
                add_meta_box( 'pip_layout_thumbnail', __( "Pilo'Press: Layout thumbnail", 'pilopress' ), array(
                    $this,
                    'render_meta_box_thumbnail',
                ), 'acf-field-group', 'side', 'default', array(
                    'field_group' => $field_group,
                ) );

                // Hide field groups categories meta box
                remove_meta_box( 'acf-field-group-categorydiv', 'acf-field-group', 'side' );

            } elseif ( !$is_layout ) {

                // Hide layouts categories and collections meta box
                remove_meta_box( 'acf-layouts-categorydiv', 'acf-field-group', 'side' );
                remove_meta_box( 'acf-layouts-collectiondiv', 'acf-field-group', 'side' );
            }
        }

        /**
         * Manage layout folder and files on save
         *
         * @param $post_id
         * @param $post
         * @param $update
         */
        public function save_field_group( $post_id, $post, $update ) {

            // If is a revision, not a field group or not a layout, return
            if ( wp_is_post_revision( $post_id ) || $post->post_status == 'draft' || $post->post_status == 'auto-draft' || $post->post_type !== 'acf-field-group' || !$this->is_layout( $post_id ) ) {
                return;
            }

            // Get layout slug
            $field_group = acf_get_field_group( $post_id );
            $layout_slug = sanitize_title( $field_group['_pip_layout_slug'] );
            if ( $post->post_content ) {
                return;
            }

            // Do layout folder already exists ?
            $folder_exists = file_exists( PIP_THEME_LAYOUTS_PATH . $layout_slug );
            if ( $folder_exists ) {
                return;
            }

            // Create layout dans files
            $this->create_layout_dir( $layout_slug, $field_group );
        }

        /**
         *  Meta box: Main
         *
         * @param $post
         * @param $meta_box
         */
        public function render_meta_box_main( $post, $meta_box ) {

            // Get field group
            $field_group = $meta_box['args']['field_group'];

            // Layout settings
            acf_render_field_wrap( array(
                'label'        => '',
                'name'         => '_pip_is_layout',
                'prefix'       => 'acf_field_group',
                'type'         => 'acfe_hidden',
                'instructions' => '',
                'value'        => 1,
                'required'     => false,
            ) );

            // Layout
            $current_theme      = wp_get_theme();
            $layout_slug        = acf_maybe_get( $field_group, '_pip_layout_slug' ) ? sanitize_title( $field_group['_pip_layout_slug'] ) : 'layout';
            $layout_path_prefix = $current_theme->get_template() . '/pilopress/layouts/<span>' . $layout_slug . '</span>/';

            // Layout slug
            acf_render_field_wrap( array(
                'label'        => __( 'Layout slug', 'pilopress' ),
                'instructions' => __( 'Layout slug and layout folder name', 'pilopress' ),
                'type'         => 'acfe_slug',
                'name'         => '_pip_layout_slug',
                'prefix'       => 'acf_field_group',
                'placeholder'  => 'layout',
                'required'     => 1,
                'value'        => isset( $field_group['_pip_layout_slug'] ) ? $field_group['_pip_layout_slug'] : $layout_slug,
            ) );

            // Layout template
            acf_render_field_wrap( array(
                'label'         => __( 'Layout', 'pilopress' ),
                'instructions'  => __( 'Layout file name', 'pilopress' ),
                'type'          => 'text',
                'name'          => '_pip_render_layout',
                'prefix'        => 'acf_field_group',
                'placeholder'   => 'template.php',
                'default_value' => $layout_slug . '.php',
                'prepend'       => $layout_path_prefix,
                'required'      => 1,
                'value'         => isset( $field_group['_pip_render_layout'] ) ? $field_group['_pip_render_layout'] : '',
            ) );

            // Style - CSS
            acf_render_field_wrap( array(
                'label'         => __( 'Style CSS', 'pilopress' ),
                'instructions'  => __( 'CSS file name', 'pilopress' ),
                'type'          => 'text',
                'name'          => '_pip_render_style',
                'prefix'        => 'acf_field_group',
                'placeholder'   => 'style.css',
                'default_value' => $layout_slug . '.css',
                'prepend'       => $layout_path_prefix,
                'value'         => isset( $field_group['_pip_render_style'] ) ? $field_group['_pip_render_style'] : '',
            ) );

            // Script
            acf_render_field_wrap( array(
                'label'         => __( 'Script', 'pilopress' ),
                'instructions'  => __( 'JS file name', 'pilopress' ),
                'type'          => 'text',
                'name'          => '_pip_render_script',
                'prefix'        => 'acf_field_group',
                'placeholder'   => 'script.js',
                'default_value' => $layout_slug . '.js',
                'prepend'       => $layout_path_prefix,
                'value'         => isset( $field_group['_pip_render_script'] ) ? $field_group['_pip_render_script'] : '',
            ) );

            // Add configuration file ?
            acf_render_field_wrap( array(
                'label'         => __( 'Configuration file', 'pilopress' ),
                'instructions'  => '',
                'key'           => 'field_add_config_file',
                'type'          => 'true_false',
                'name'          => '_pip_add_config_file',
                'prefix'        => 'acf_field_group',
                'value'         => ( isset( $field_group['field_add_config_file'] ) ? $field_group['field_add_config_file'] : '' ),
                'default_value' => '',
                'ui'            => 1,
            ) );

            // Configuration file
            acf_render_field_wrap( array(
                'label'             => __( 'Configuration file', 'pilopress' ),
                'instructions'      => __( 'PHP file name', 'pilopress' ),
                'type'              => 'text',
                'name'              => '_pip_config_file',
                'prefix'            => 'acf_field_group',
                'placeholder'       => 'configuration.php',
                'prepend'           => $layout_path_prefix,
                'value'             => isset( $field_group['_pip_config_file'] ) ? $field_group['_pip_config_file'] : 'configuration-' . $layout_slug . '.php',
                'conditional_logic' => array(
                    array(
                        array(
                            'field'    => 'field_add_config_file',
                            'operator' => '==',
                            'value'    => '1',
                        ),
                    ),
                ),
            ) );

            // Get layouts for configuration field
            $choices = array();

            acf_enable_local();

            $field_groups = acf_get_field_groups();

            acf_disable_local();

            if ( $field_groups ) {

                foreach ( $field_groups as $field_grp ) {

                    $choices[ $field_grp['key'] ] = $field_grp['title'];

                }

            }

            // Add configuration ?
            acf_render_field_wrap( array(
                'label'         => __( 'Configuration modal', 'pilopress' ),
                'instructions'  => '',
                'key'           => 'field_add_configuration',
                'type'          => 'true_false',
                'name'          => '_pip_add_configuration',
                'prefix'        => 'acf_field_group',
                'value'         => ( isset( $field_group['field_add_configuration'] ) ? $field_group['field_add_configuration'] : '' ),
                'default_value' => '',
                'ui'            => 1,
            ) );

            // Configuration
            acf_render_field_wrap( array(
                'label'             => __( 'Configuration field group', 'pilopress' ),
                'instructions'      => '',
                'type'              => 'select',
                'name'              => '_pip_configuration',
                'prefix'            => 'acf_field_group',
                'value'             => ( isset( $field_group['_pip_configuration'] ) ? $field_group['_pip_configuration'] : '' ),
                'choices'           => $choices,
                'allow_null'        => 1,
                'multiple'          => 1,
                'ui'                => 1,
                'ajax'              => 0,
                'return_format'     => 0,
                'conditional_logic' => array(
                    array(
                        array(
                            'field'    => 'field_add_configuration',
                            'operator' => '==',
                            'value'    => '1',
                        ),
                    ),
                ),
            ) );

            // Modal size
            acf_render_field_wrap( array(
                'label'             => __( 'Configuration modal size', 'pilopress' ),
                'instructions'      => '',
                'name'              => '_pip_modal_size',
                'type'              => 'select',
                'class'             => '',
                'prefix'            => 'acf_field_group',
                'value'             => ( isset( $field_group['_pip_modal_size'] ) ? $field_group['_pip_modal_size'] : 'medium' ),
                'choices'           => array(
                    'small'  => 'Small',
                    'medium' => 'Medium',
                    'large'  => 'Large',
                    'xlarge' => 'Extra Large',
                    'full'   => 'Full',
                ),
                'allow_null'        => 0,
                'multiple'          => 0,
                'ui'                => 1,
                'ajax'              => 0,
                'return_format'     => 0,
                'conditional_logic' => array(
                    array(
                        array(
                            'field'    => 'field_add_configuration',
                            'operator' => '==',
                            'value'    => '1',
                        ),
                    ),
                ),
            ) );

            // Script for admin style
            ?>
            <script type="text/javascript">
                if (typeof acf !== 'undefined') {
                    acf.postbox.render({
                        'id': 'pip_layout_settings',
                        'label': 'left'
                    })
                }
            </script>
            <?php
        }

        /**
         * Meta box: Thumbnail
         *
         * @param $post
         * @param $meta_box
         */
        public function render_meta_box_thumbnail( $post, $meta_box ) {

            // Get field group
            $field_group = $meta_box['args']['field_group'];

            // Thumbnail
            acf_render_field_wrap( array(
                'label'         => __( 'Thumbnail', 'pilopress' ),
                'instructions'  => __( 'Layout preview', 'pilopress' ),
                'name'          => '_pip_thumbnail',
                'type'          => 'image',
                'class'         => '',
                'prefix'        => 'acf_field_group',
                'value'         => ( isset( $field_group['_pip_thumbnail'] ) ? $field_group['_pip_thumbnail'] : '' ),
                'return_format' => 'array',
                'preview_size'  => 'thumbnail',
                'library'       => 'all',
            ) );

            // Script for admin style
            ?>
            <script type="text/javascript">
                if (typeof acf !== 'undefined') {
                    acf.postbox.render({
                        'id': 'pip_layout_thumbnail',
                        'label': 'top'
                    })
                }
            </script>
            <?php
        }

        /**
         * Check if post/field group is a layout
         *
         * @param array|int $post
         *
         * @return bool|mixed|null
         */
        public function is_layout( $post ) {

            $is_layout   = false;
            $field_group = null;

            // If no post ID, return false
            if ( !$post ) {
                return $is_layout;
            }

            if ( is_array( $post ) ) {
                // If is array, it's a field group
                $field_group = $post;
            } else {
                // If is ID, get field group
                $field_group = acf_get_field_group( $post );
            }

            // If no field group, return false
            if ( !$field_group ) {
                return $is_layout;
            }

            // Is layout option set to true ?
            $is_layout = acf_maybe_get( $field_group, '_pip_is_layout' );

            // If layout in URL, set to true
            if ( acf_maybe_get_GET( 'layout' ) ) {
                $is_layout = true;
            }

            return $is_layout;
        }

        /**
         * Get a layout by its slug
         *
         * @param      $slug
         * @param bool $excluded_id
         *
         * @return array|null
         */
        public function get_layout_by_slug( $slug, $excluded_id = false ) {

            $field_group = null;

            // Get slug length
            $count = strlen( $slug );

            // Arguments for query
            $args = array(
                'post_type'        => 'acf-field-group',
                'post_status'      => 'acf-disabled',
                'fields'           => 'ids',
                'posts_per_page'   => - 1,
                'suppress_filters' => 0,
                'pip_post_content' => array(
                    'compare' => 'LIKE',
                    'value'   => 's:16:"_pip_layout_slug";s:' . $count . ':"' . $slug . '";',
                ),
            );

            // Maybe exclude post
            if ( $excluded_id ) {
                $args['post__not_in'] = array( $excluded_id );
            }

            // Get post ids
            $posts_ids = get_posts( $args );

            // If posts found, get field groups
            if ( $posts_ids ) {
                foreach ( $posts_ids as $post_id ) {
                    $field_group[] = acf_get_field_group( $post_id );
                }
            }

            return $field_group;
        }

        /**
         * Create layout folder with corresponding files
         *
         * @param $layout_title
         * @param $field_group
         */
        public function create_layout_dir( $layout_title, $field_group ) {

            // Create folder
            wp_mkdir_p( PIP_THEME_LAYOUTS_PATH . $layout_title );

            // Create file
            $file_name = acf_maybe_get( $field_group, '_pip_render_layout', $layout_title . '.php' );
            touch( PIP_THEME_LAYOUTS_PATH . $layout_title . '/' . $file_name );

            // Update field group
            acf_update_field_group( $field_group );
        }

        /**
         * Enqueue layouts configuration files
         */
        public function enqueue_configuration_files() {

            // Get field groups
            $field_groups = acf_get_field_groups();

            // If no field group, return
            if ( !$field_groups ) {
                return;
            }

            // Browse all field groups
            foreach ( $field_groups as $field_grp ) {

                // If not a layout, skip
                if ( !$this->is_layout( $field_grp ) ) {
                    continue;
                }

                // If no configuration file added, skip
                if ( !acf_maybe_get( $field_grp, 'field_add_config_file' ) ) {
                    continue;
                }

                // Get layout slug and file name
                $layout_slug      = acf_maybe_get( $field_grp, '_pip_layout_slug' );
                $config_file_name = acf_maybe_get( $field_grp, '_pip_config_file' );
                $file_path        = PIP_THEME_LAYOUTS_PATH . $layout_slug . '/' . $config_file_name;

                // If no file name or file doesn't exists, skip
                if ( !$config_file_name || !file_exists( $file_path ) ) {
                    continue;
                }

                // Include PHP file
                include_once $file_path;
            }

        }

        /**
         * Get all layouts CSS files content
         *
         * @return string
         */
        public function get_layouts_css() {

            $css_content = '';

            // Get layouts CSS files
            $layouts_css_files = glob( PIP_THEME_LAYOUTS_PATH . '*/*.css' );

            // If no CSS files, return
            if ( !$layouts_css_files ) {
                return $css_content;
            }

            // Store CSS contents
            foreach ( $layouts_css_files as $layouts_css_file ) {
                $css_file    = file_get_contents( $layouts_css_file );
                $css_content .= $css_file ? $css_file : '';
            }

            return $css_content;
        }

        /**
         * Setter: $layout_group_keys
         *
         * @param $layout_group_keys
         *
         * @return void
         */
        public function set_layout_group_keys( $layout_group_keys ) {

            if ( $this->layout_group_keys ) {
                $this->layout_group_keys = array_merge( $this->layout_group_keys, $layout_group_keys );
            } else {
                $this->layout_group_keys = $layout_group_keys;
            }
        }

        /**
         * Getter: $layout_group_keys
         *
         * @return array
         */
        public function get_layout_group_keys() {

            return $this->layout_group_keys;
        }

        /**
         * Get layouts by location
         *
         * @param array $args
         *
         * @return array
         */
        public function get_layouts_by_location( array $args ) {

            $layouts = array();

            // Get layout keys
            $layout_keys = $this->get_layout_group_keys();
            if ( !$layout_keys ) {
                return $layouts;
            }

            $pip_flexible = acf_get_instance( 'PIP_Flexible' );

            // Browse all layouts
            foreach ( $layout_keys as $layout_key ) {
                $layout = acf_get_field_group( $layout_key );
                if ( !isset( $layout['location'] ) ) {
                    continue;
                }

                // Layout not assign to location
                if ( !$pip_flexible->get_field_group_visibility( $layout, $args ) ) {
                    continue;
                }

                $layouts[] = $layout;
            }

            return $layouts;
        }

        /**
         * Re-order meta-boxes
         *
         * @param $order
         *
         * @return array
         */
        public function metabox_order( $order ) {

            if ( !$order ) {

                $order = array(
                    'normal' => implode( ',', array(

                        // Layouts
                        'acf-field-group-fields',
                        'pip_layout_settings',
                        'acf-field-group-options',

                        // Flexible Mirror
                        'pip-flexible-layouts',
                        'acf-field-group-locations',

                    ) ),
                );

            }

            return $order;

        }

    }

    acf_new_instance( 'PIP_Layouts' );
}
