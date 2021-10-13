<?php

defined( 'ABSPATH' ) || exit;

if ( !class_exists( 'PIP_Patterns' ) ) {

    /**
     * Class PIP_Patterns
     */
    class PIP_Patterns {

        /**
         * Default content post type
         *
         * @var string
         */
        private static $default_content_post_type = 'pip-default-content';

        /**
         * Locked content post type
         *
         * @var string
         */
        private static $locked_content_post_type = 'pip-locked-content';

        /**
         * PIP_Patterns constructor.
         */
        public function __construct() {

            // WP hooks
            add_action( 'init', array( $this, 'register_post_types' ), 20 );
            add_action( 'admin_init', array( $this, 'auto_populate_post_types' ) );

        }

        /**
         * Register post types
         */
        public function register_post_types() {

            // Capability
            $capability = apply_filters( 'pip/options/capability', acf_get_setting( 'capability' ) );

            // Do not register post types if user doesn't have rights
            if ( !current_user_can( $capability ) ) {
                return;
            }

            // Default content post type
            register_post_type(
                self::$default_content_post_type,
                array(
                    'label'               => __( 'Default content', 'pilopress' ),
                    'description'         => __( 'Default content', 'pilopress' ),
                    'labels'              => array(
                        'name'               => __( 'Default content', 'pilopress' ),
                        'singular_name'      => __( 'Default content', 'pilopress' ),
                        'menu_name'          => __( 'Patterns', 'pilopress' ),
                        'all_items'          => __( 'Default content', 'pilopress' ),
                        'add_new_item'       => __( 'Add', 'pilopress' ),
                        'add_new'            => __( 'Add', 'pilopress' ),
                        'not_found_in_trash' => __( 'Not found', 'pilopress' ),
                    ),
                    'supports'            => array( 'custom-fields' ),
                    'hierarchical'        => false,
                    'public'              => false,
                    'show_ui'             => true,
                    'show_in_menu'        => true,
                    'menu_position'       => 83,
                    'menu_icon'           => 'dashicons-layout',
                    'show_in_admin_bar'   => false,
                    'show_in_nav_menus'   => false,
                    'can_export'          => true,
                    'has_archive'         => false,
                    'exclude_from_search' => true,
                    'publicly_queryable'  => false,
                    'capability_type'     => 'page',
                    'map_meta_cap'        => true,
                    'capabilities'        => array(
                        'create_posts' => false,
                    ),
                )
            );

            // Locked content post type
            register_post_type(
                self::$locked_content_post_type,
                array(
                    'label'               => __( 'Locked content', 'pilopress' ),
                    'description'         => __( 'Locked content', 'pilopress' ),
                    'labels'              => array(
                        'name'          => __( 'Locked content', 'pilopress' ),
                        'singular_name' => __( 'Locked content', 'pilopress' ),
                        'menu_name'     => __( 'Locked content', 'pilopress' ),
                        'all_items'     => __( 'Locked content', 'pilopress' ),
                    ),
                    'supports'            => array( 'custom-fields' ),
                    'hierarchical'        => false,
                    'public'              => false,
                    'show_ui'             => true,
                    'show_in_menu'        => 'edit.php?post_type=pip-default-content',
                    'show_in_admin_bar'   => false,
                    'show_in_nav_menus'   => false,
                    'can_export'          => true,
                    'has_archive'         => false,
                    'exclude_from_search' => true,
                    'publicly_queryable'  => false,
                    'capability_type'     => 'page',
                    'map_meta_cap'        => true,
                    'capabilities'        => array(
                        'create_posts' => false,
                    ),
                )
            );
        }

        public function auto_populate_post_types() {

            // Get post types
            $post_types = self::get_post_types();
            if ( !$post_types ) {
                return;
            }

            // Browse all post types
            foreach ( $post_types as $post_type ) {

                // Get patterns post types
                $templates_post_types = array( self::$default_content_post_type, self::$locked_content_post_type );
                foreach ( $templates_post_types as $template_post_type ) {

                    // Get pattern object
                    $template_post_object = get_page_by_path( $post_type, 'OBJECT', $template_post_type );
                    $template_post_id     = $template_post_object ? $template_post_object->ID : '';

                    // Insert post
                    if ( !$template_post_object ) {

                        $post_type_object = get_post_type_object( $post_type );
                        $template_post_id = wp_insert_post(
                            array(
                                'post_type'   => $template_post_type,
                                'post_title'  => $post_type_object->labels->name,
                                'post_name'   => $post_type,
                                'post_status' => 'publish',
                            )
                        );

                    }

                    // WPML Compatibility - Create translations duplicates
                    if ( defined( 'ICL_LANGUAGE_CODE' ) ) {

                        // WPML - Vars
                        $wpml_element_type     = apply_filters( 'wpml_element_type', $template_post_type );
                        $default_language_code = wpml_get_default_language();
                        $active_languages      = apply_filters( 'wpml_active_languages', null, '' );

                        /**
                         *  Get the language info of the original post
                         *
                         * @link https://wpml.org/wpml-hook/wpml_element_language_details/
                         */
                        $get_language_args       = array(
                            'element_id'   => $template_post_id,
                            'element_type' => $wpml_element_type,
                        );
                        $original_post_lang_data = apply_filters( 'wpml_element_language_details', null, $get_language_args );


                        // Set default translation data if it's not yet translated
                        $is_translated = apply_filters( 'wpml_element_has_translations', '', $template_post_id, $wpml_element_type );
                        if ( !$is_translated ) {

                            /**
                             *  Set / Overwrite the language data of the original post
                             *
                             * @link https://wpml.org/wpml-hook/wpml_set_element_language_details/
                             */
                            $set_language_args = array(
                                'element_id'           => $template_post_id,
                                'element_type'         => $wpml_element_type,
                                'trid'                 => pip_maybe_get( $original_post_lang_data, 'trid' ),
                                'language_code'        => $default_language_code,                                     // Set to the default language code
                                'source_language_code' => pip_maybe_get( $original_post_lang_data, 'language_code' ), // "null" makes it the original source / delete link to others translations.
                            );
                            do_action( 'wpml_set_element_language_details', $set_language_args );

                            // Generate translations for each languages
                            do_action( 'wpml_admin_make_post_duplicates', $template_post_id );

                        } else {
                            // Has translations, generate missing translations

                            $translations = apply_filters( 'wpml_get_element_translations', null, $original_post_lang_data->trid, $wpml_element_type );
                            if ( empty( $translations ) ) {
                                continue;
                            }

                            // If all translations are already there, abort
                            $missing_translations = array_diff_key( $active_languages, $translations );
                            if ( !$missing_translations ) {
                                continue;
                            }

                            // Generate missing ones
                            global $sitepress;
                            foreach ( $missing_translations as $missing_lang_code => $lang_data ) {
                                $sitepress->make_duplicate( $template_post_id, $missing_lang_code );
                            }
                        }
                    }
                }
            }
        }

        /**
         * Get filtered post types
         *
         * @return array|false
         */
        private static function get_post_types() {
            $filtered_post_types = array();

            $public_post_types = get_post_types(
                array(
                    'public' => true,
                )
            );

            // Get post types
            $allowed_post_types = apply_filters( 'pip/patterns/post_types', $public_post_types );
            if ( !$allowed_post_types ) {
                return $filtered_post_types;
            }

            // Browse all post types
            foreach ( $public_post_types as $post_type ) {
                // If attachment, skip
                if ( $post_type === 'attachment' ) {
                    continue;
                }

                // If not in templates, skip
                if ( !in_array( $post_type, $allowed_post_types, true ) ) {
                    continue;
                }

                // Store post type
                $filtered_post_types[] = $post_type;
            }

            return $filtered_post_types;
        }

    }

    // Instantiate
    new PIP_Patterns();
}
