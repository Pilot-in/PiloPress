<?php

if ( !class_exists( 'PIP_Flexible_Mirror' ) ) {
    class PIP_Flexible_Mirror {

        private static $flexible_mirror_group;
        private static $flexible_mirror_group_key = 'group_pip_flexible_mirror';

        public function __construct() {
            // WP hooks
            add_action( 'init', array( $this, 'modify_acf_post_type' ) );
            add_action( 'current_screen', array( $this, 'current_screen' ) );
            add_filter( 'pre_delete_post', array( $this, 'delete_post' ), 10, 2 );
            add_filter( 'pre_trash_post', array( $this, 'delete_post' ), 10, 2 );
        }

        /**
         * Fire actions on acf field groups page
         */
        public function current_screen() {
            // ACF field groups archive
            if ( acf_is_screen( 'edit-acf-field-group' ) ) {
                add_action( 'load-edit.php', array( $this, 'generate_flexible_mirror' ) );
                add_filter( 'page_row_actions', array( $this, 'row_actions' ), 10, 2 );
            }

            // ACF field group single
            if ( acf_is_screen( 'acf-field-group' ) ) {
                add_action( 'acf/input/admin_head', array( $this, 'meta_boxes' ) );
                add_filter( 'get_user_option_meta-box-order_acf-field-group', array( $this, 'metabox_order' ) );
            }
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
                            'pip-flexible-layouts',
                            'acf-field-group-locations',
                        )
                    ),
                );
            }

            return $order;
        }

        /**
         * Change title on flexible edition page
         */
        public function modify_acf_post_type() {
            // If AJAX or not admin, return
            if ( wp_doing_ajax() || !is_admin() ) {
                return;
            }

            // Get current post
            $post = get_post( acf_maybe_get_GET( 'post' ) );

            // Mirror flexible
            if ( $post && $post->post_name === self::get_flexible_mirror_group_key() ) {
                $acf_field_group = get_post_type_object( 'acf-field-group' );

                // Change title on flexible edition page
                $acf_field_group->labels->edit_item = __( 'Edit Flexible Content', 'pilopress' );

                // Hide "Add new" button
                $acf_field_group->cap->create_posts = false;
            }
        }

        /**
         * Generate flexible mirror
         */
        public function generate_flexible_mirror() {
            // If mirror flexible already exists, return
            if ( self::get_flexible_mirror_group() ) {
                return;
            }

            // Mirror flexible field group
            $flexible_mirror = array(
                'key'                   => self::get_flexible_mirror_group_key(),
                'title'                 => __( 'Flexible Content', 'pilopress' ),
                'fields'                => array(),
                'location'              => array(
                    array(
                        array(
                            'param'    => 'post_type',
                            'operator' => '==',
                            'value'    => 'all',
                        ),
                        array(
                            'param'    => 'post_type',
                            'operator' => '!=',
                            'value'    => PIP_Components::$post_type,
                        ),
                    ),
                    array(
                        array(
                            'param'    => 'taxonomy',
                            'operator' => '==',
                            'value'    => 'all',
                        ),
                    ),
                ),
                'menu_order'            => 0,
                'position'              => 'normal',
                'style'                 => 'seamless',
                'label_placement'       => 'top',
                'instruction_placement' => 'label',
                'hide_on_screen'        => '',
                'active'                => false,
                'description'           => '',
                'acfe_display_title'    => '',
                'acfe_autosync'         => '',
                'acfe_permissions'      => '',
                'acfe_form'             => 0,
                'acfe_meta'             => '',
                'acfe_note'             => '',
            );

            // Import flexible in local
            acf_import_field_group( $flexible_mirror );
        }

        /**
         * Remove trash action for mirror flexible field group
         *
         * @param $actions
         * @param $post
         *
         * @return mixed
         */
        public function row_actions( $actions, $post ) {
            // If not mirror flexible, return
            if ( $post->post_name !== self::get_flexible_mirror_group_key() ) {
                return $actions;
            }

            // Remove trash action
            unset( $actions['trash'] );

            return $actions;
        }

        /**
         * Prevent removal of mirror flexible field group
         *
         * @param $trash
         * @param $post
         *
         * @return bool
         */
        public function delete_post( $trash, $post ) {
            // If not mirror flexible group field, return
            $flexible_mirror_group_key = self::get_flexible_mirror_group_key();
            if ( $post->post_name !== $flexible_mirror_group_key && $post->post_name !== $flexible_mirror_group_key . '__trashed' ) {
                return $trash;
            }

            // Prevent delete/trash field group
            return false;
        }

        /**
         * Customize meta boxes on mirror flexible content page
         */
        public function meta_boxes() {
            global $field_group;

            // If not mirror flexible group field, return
            if ( $field_group['key'] !== self::get_flexible_mirror_group_key() ) {
                return;
            }

            // Remove meta boxes
            remove_meta_box( 'acf-field-group-options', 'acf-field-group', 'normal' );
            remove_meta_box( 'acf-field-group-fields', 'acf-field-group', 'normal' );
            remove_meta_box( 'slugdiv', 'acf-field-group', 'normal' );
            remove_meta_box( 'acf-field-group-acfe-side', 'acf-field-group', 'side' );
            remove_meta_box( 'acf-field-group-acfe', 'acf-field-group', 'normal' );
            remove_meta_box( 'acfe-wp-custom-fields', 'acf-field-group', 'normal' );

            // Add meta box
            add_meta_box( 'pip-flexible-layouts', __( 'Layouts', 'pilopress' ), array( $this, 'layouts_meta_box' ), 'acf-field-group', 'normal', 'high' );
        }

        /**
         * Add custom meta box for mirror flexible
         */
        public function layouts_meta_box() {
            $layouts = array();
            foreach ( PIP_Layouts::get_layout_group_keys() as $layout_group_key ) {
                // Get current field group
                $layout_field_group = acf_get_field_group( $layout_group_key );

                // Get locations html
                $locations = ''; // PILO_TODO: get ACFE helper (next version)

                // Structured array for template file
                $layouts[] = array(
                    'title'     => $layout_field_group['title'],
                    'locations' => $locations,
                    'edit_link' => get_edit_post_link( $layout_field_group['ID'] ),
                    'terms'     => self::get_terms_html( $layout_field_group['ID'] ),
                    'fields'    => acf_get_field_count( $layout_field_group ),
                    'load'      => self::layout_load_from( $layout_field_group ),
                    'php'       => self::layout_php_sync( $layout_field_group ),
                    'json'      => self::layout_json_sync( $layout_field_group ),
                );
            }

            // New field group link
            $add_new_link = add_query_arg(
                array(
                    'post_type' => 'acf-field-group',
                    'layout'    => 1,
                ),
                admin_url( 'post-new.php' )
            );

            // Template file
            include_once( PIP_PATH . 'includes/views/flexible-layouts-meta-box.php' );
        }

        /**
         * Get layout "load from" data
         *
         * @param $field_group
         *
         * @return string
         */
        private static function layout_load_from( $field_group ) {
            // Get local field group
            $local_field_group      = acf_get_local_field_group( $field_group['key'] );
            $local_field_group_type = acf_maybe_get( $local_field_group, 'local', false );

            // Get HTML
            if ( $local_field_group_type === 'php' ) {
                $html = '<span class="acf-js-tooltip" title="' . $field_group['key'] . ' is registered locally">PHP</span>';
            } elseif ( $local_field_group_type === 'json' ) {
                $html = '<span class="acf-js-tooltip" title="' . $field_group['key'] . ' is registered locally">JSON</span>';
            } else {
                $html = '<span class="acf-js-tooltip" title="' . $field_group['key'] . ' is not registered locally">DB</span>';
            }

            return $html;
        }

        /**
         * Get layout "PHP sync" data
         *
         * @param $field_group
         *
         * @return string
         */
        private static function layout_php_sync( $field_group ) {
            // Not sync
            if ( !acfe_has_field_group_autosync( $field_group, 'php' ) ) {
                $html = '<span style="color:#ccc" class="dashicons dashicons-no-alt"></span>';

                // Third party sync
                if ( acfe_has_field_group_autosync_file( $field_group, 'php' ) ) {
                    $html .= '<span style="color:#ccc;font-size:16px;vertical-align:text-top;" class="acf-js-tooltip dashicons dashicons-warning" title="Field group: ' . $field_group['key'] . ' is registered via a third-party PHP code"></span>';
                }

                return $html;
            }

            if ( !acf_get_setting( 'acfe/php_found' ) ) {

                // No "acfe-php" directory
                $html = '<span style="color:#ccc" class="dashicons dashicons-yes"></span>';
                $html .= '<span style="color:#ccc;font-size:16px;vertical-align:text-top;" class="acf-js-tooltip dashicons dashicons-warning" title="Folder \'/acfe-php\' was not found in your theme.<br />You must create it to activate this setting"></span>';

            } elseif ( !acfe_has_field_group_autosync_file( $field_group, 'php' ) ) {

                // File will be created
                $html = '<span style="color:#ccc" class="dashicons dashicons-yes"></span>';
                $html .= '<span style="color:#ccc;font-size:16px;vertical-align:text-top;" class="acf-js-tooltip dashicons dashicons-warning" title="Local file ' . $field_group['key'] . '.php will be created upon update"></span>';
            } else {

                // Sync
                $html = '<span class="dashicons dashicons-yes"></span>';
            }

            return $html;
        }

        /**
         * Get layout "JSON sync" data
         *
         * @param $field_group
         *
         * @return string
         */
        private static function layout_json_sync( $field_group ) {
            if ( acfe_has_field_group_autosync_file( $field_group, 'json' ) ) {

                // Sync
                $html = '<span class="dashicons dashicons-yes"></span>';

            } else {

                if ( !acfe_has_field_group_autosync( $field_group, 'json' ) ) {

                    // Not sync
                    $html = '<span style="color:#ccc" class="dashicons dashicons-no-alt"></span>';
                } else {

                    // Sync
                    $html = '<span style="color:#ccc" class="dashicons dashicons-yes"></span>';

                    if ( !acf_get_setting( 'acfe/json_found' ) ) {

                        // No "acf-json" directory
                        $html .= '<span style="color:#ccc;font-size:16px;vertical-align:text-top;" class="acf-js-tooltip dashicons dashicons-warning" title="Folder \'/acf-json\' was not found in your theme.<br />You must create it to activate this setting"></span>';

                    } else {

                        // File will be created
                        $html .= '<span style="color:#ccc;font-size:16px;vertical-align:text-top;" class="acf-js-tooltip dashicons dashicons-warning" title="Local file ' . $field_group['key'] . '.json will be created upon update."></span>';

                    }
                }
            }

            return $html;
        }

        /**
         * Get terms html
         *
         * @param $field_group_id
         *
         * @return string
         */
        private static function get_terms_html( $field_group_id ) {
            $terms_html = '';
            $terms      = get_the_terms( $field_group_id, 'acf-layouts-category' );
            if ( $terms ) {
                foreach ( $terms as $term ) {
                    $url        = add_query_arg(
                        array(
                            'layouts'              => 1,
                            'acf-layouts-category' => $term->slug,
                            'post_type'            => 'acf-field-group',
                        ),
                        admin_url( 'edit.php' )
                    );
                    $terms_html .= '<a href="' . $url . '">' . $term->name . '</a>';
                }
            }

            return $terms_html;
        }

        /**
         * Getter: $flexible_mirror_group_key
         * @return string
         */
        public static function get_flexible_mirror_group_key() {
            return self::$flexible_mirror_group_key;
        }

        /**
         * Getter: $flexible_mirror_group
         * @return mixed
         */
        public static function get_flexible_mirror_group() {
            return self::$flexible_mirror_group;
        }

        /**
         * Setter: $flexible_mirror_group
         *
         * @param mixed $flexible_mirror_group
         */
        public static function set_flexible_mirror_group( $flexible_mirror_group ) {
            self::$flexible_mirror_group = $flexible_mirror_group;
        }

    }

    // Instantiate class
    new PIP_Flexible_Mirror();
}
