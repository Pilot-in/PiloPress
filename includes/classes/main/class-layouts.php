<?php

if ( !class_exists( 'PIP_Layouts' ) ) {

    /**
     * Class PIP_Layouts
     */
    class PIP_Layouts {

        /**
         * Vars
         */
        var $layout_group_keys = array();

        /*
         * Construct
         */
        public function __construct() {

            // WP hooks
            add_action( 'init', array( $this, 'enqueue_configuration_files' ), 5 );

            // Current Screen
            add_action( 'current_screen', array( $this, 'current_screen' ) );

            // Insert
            //add_action( 'wp_insert_post', array( $this, 'insert_post' ), 20, 3 );

            //add_filter( 'acf/validate_field_group', array( $this, 'save_validate_field_group' ), 20 );

        }

        /**
         * Current Screen
         */
        public function current_screen() {

            if ( !$this->is_layout_screen() ) {
                return;
            }

            // Post Type
            $this->labels();

            // Single
            add_action( 'load-post.php', array( $this, 'load_single' ) );
            add_action( 'load-post-new.php', array( $this, 'load_single' ) );

            // List
            add_action( 'load-edit.php', array( $this, 'load_list' ) );

        }

        /*
         * Single
         */
        function load_single() {

            add_filter( 'acf/validate_field_group', array( $this, 'validate_field_group' ), 20 );
            add_action( 'acf/field_group/admin_head', array( $this, 'metaboxes' ) );
            add_filter( 'get_user_option_meta-box-order_acf-field-group', array( $this, 'metabox_order' ) );

            add_action( 'auto-draft_to_publish', array( $this, 'save_post' ));

        }

        /*
         * List
         */
        function load_list() {

            // Remove ACF Disabled Visual State
            add_filter( 'display_post_states', array( $this, 'post_states' ), 20 );

            // Remove ACF Extended: Field Group Category Column
            remove_filter( 'manage_edit-acf-field-group_columns', 'acfe_field_group_category_column', 11 );
            remove_action( 'manage_acf-field-group_posts_custom_column', 'acfe_field_group_category_column_html', 10 );

        }

        /**
         * Layouts Labels
         */
        public function labels() {

            $post_type = get_post_type_object( 'acf-field-group' );

            // Change title on flexible edition page
            $post_type->labels->name         = __( 'Layouts', 'pilopress' );
            $post_type->labels->edit_item    = __( 'Edit Layout', 'pilopress' );
            $post_type->labels->add_new_item = __( 'Add New Layout', 'pilopress' );

        }

        /**
         * Layouts Locations
         */
        function validate_field_group( $field_group ) {

            // New Layout
            if ( !acf_maybe_get( $field_group, 'location' ) ) {

                // Get Flexible Mirror
                $flexible_mirror = pip_get_flexible_mirror_group();

                $field_group['location'] = $flexible_mirror['location'];

            }

            // Force disable
            $field_group['active'] = false;

            return $field_group;

        }

        /**
         * Layouts Locations
         */
        function save_validate_field_group( $field_group ) {

            $field_group['title'] = $field_group['title'] . ' - TEST';

            return $field_group;

        }

        /**
         * Layouts Meta Boxes
         */
        public function metaboxes() {

            // Get current field group
            global $field_group;

            // Meta box: Layout settings
            add_meta_box( 'pip_layout_settings', __( "Layout settings", 'pilopress' ), array(
                $this,
                'render_meta_box_main',
            ), 'acf-field-group', 'normal', 'high', array(
                'field_group' => $field_group,
            ) );

            // Meta box: Thumbnail
            add_meta_box( 'pip_layout_thumbnail', __( "Layout thumbnail", 'pilopress' ), array(
                $this,
                'render_meta_box_thumbnail',
            ), 'acf-field-group', 'side', 'default', array(
                'field_group' => $field_group,
            ) );

            // Remove ACF Extended: Field Group Category Metabox
            remove_meta_box( 'acf-field-group-categorydiv', 'acf-field-group', 'side' );

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

        /**
         * Hide "Disabled" state
         *
         * @param $states
         *
         * @return mixed
         */
        public function post_states( $states ) {

            // Unset disabled state
            if(isset($states['acf-disabled'])){

                unset( $states['acf-disabled'] );

            }

            return $states;
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

        function save_post($post){

            $post_id = $post->ID;
            $field_group = acf_get_field_group( $post_id );
            $layout_slug = sanitize_title( $field_group['_pip_layout_slug'] );

            // Do layout folder already exists ?
            $folder_exists = file_exists( PIP_THEME_LAYOUTS_PATH . $layout_slug );
            if ( $folder_exists ) {
                return;
            }

            // Create layout dans files
            $this->create_layout_dir( $layout_slug, $field_group );

        }

        /**
         * Manage layout folder and files on save
         *
         * @param $post_id
         * @param $post
         * @param $update
         */
        public function insert_post( $post_id, $post, $update ) {

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

        public function is_layout_screen() {

            global $typenow;

            if ( $typenow !== 'acf-field-group' ) {

                return false;

            }

            if ( acf_is_screen( 'edit-acf-field-group' ) && acf_maybe_get_GET( 'layouts' ) === '1' ) {

                return true;

            } elseif ( acf_is_screen( 'acf-field-group' ) ) {

                if ( acf_maybe_get_GET( 'layout' ) === '1' || $this->is_layout( acf_maybe_get_GET( 'post' ) ) || isset($_REQUEST['acf_field_group']['_pip_is_layout']) ) {

                    return true;

                }

            }

            return false;

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
            //acf_update_field_group( $field_group );
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



    }

    acf_new_instance( 'PIP_Layouts' );

}

function pip_is_layout_screen(){

    return acf_get_instance('PIP_Layouts')->is_layout_screen();

}

function pip_is_layout($post){

    return acf_get_instance('PIP_Layouts')->is_layout($post);

}
