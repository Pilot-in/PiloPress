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
            add_action( 'init', array( $this, 'register_post_types' ), 10 );
            add_action( 'admin_init', array( $this, 'auto_populate_post_types' ) );
            add_action( 'admin_init', array( $this, 'auto_populate_taxonomies' ) );

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
                        'name'                     => __( 'Default content', 'pilopress' ),
                        'singular_name'            => __( 'Default content', 'pilopress' ),
                        'menu_name'                => __( 'Patterns', 'pilopress' ),
                        'all_items'                => __( 'Default content', 'pilopress' ),
                        'add_new_item'             => __( 'Add default content', 'pilopress' ),
                        'add_new'                  => __( 'Add default content', 'pilopress' ),
                        'edit_item'                => __( 'Edit default content', 'pilopress' ),
                        'new_item'                 => __( 'New default content', 'pilopress' ),
                        'view_item'                => __( 'View default content', 'pilopress' ),
                        'view_items'               => __( 'View default contents', 'pilopress' ),
                        'search_items'             => __( 'Search default content', 'pilopress' ),
                        'uploaded_to_this_item'    => __( 'Uploaded to this default content', 'pilopress' ),
                        'filter_items_list'        => __( 'Filter default content list', 'pilopress' ),
                        'items_list_navigation'    => __( 'Default content list navigation', 'pilopress' ),
                        'items_list'               => __( 'Default content list', 'pilopress' ),
                        'name_admin_bar'           => __( 'Default content', 'pilopress' ),
                        'item_published'           => __( 'Default content published', 'pilopress' ),
                        'item_published_privately' => __( 'Default content published privately', 'pilopress' ),
                        'item_reverted_to_draft'   => __( 'Default content reverted to draft', 'pilopress' ),
                        'item_scheduled'           => __( 'Default content scheduled', 'pilopress' ),
                        'item_updated'             => __( 'Default content updated', 'pilopress' ),
                    ),
                    'supports'            => array( 'title', 'custom-fields' ),
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
                        'name'                     => __( 'Locked content', 'pilopress' ),
                        'singular_name'            => __( 'Locked content', 'pilopress' ),
                        'menu_name'                => __( 'Locked content', 'pilopress' ),
                        'all_items'                => __( 'Locked content', 'pilopress' ),
                        'add_new_item'             => __( 'Add locked content', 'pilopress' ),
                        'add_new'                  => __( 'Add locked content', 'pilopress' ),
                        'edit_item'                => __( 'Edit locked content', 'pilopress' ),
                        'new_item'                 => __( 'New locked content', 'pilopress' ),
                        'view_item'                => __( 'View locked content', 'pilopress' ),
                        'view_items'               => __( 'View locked contents', 'pilopress' ),
                        'search_items'             => __( 'Search locked content', 'pilopress' ),
                        'uploaded_to_this_item'    => __( 'Uploaded to this locked content', 'pilopress' ),
                        'filter_items_list'        => __( 'Filter locked content list', 'pilopress' ),
                        'items_list_navigation'    => __( 'Locked content list navigation', 'pilopress' ),
                        'items_list'               => __( 'Locked content list', 'pilopress' ),
                        'name_admin_bar'           => __( 'Locked content', 'pilopress' ),
                        'item_published'           => __( 'Locked content published', 'pilopress' ),
                        'item_published_privately' => __( 'Locked content published privately', 'pilopress' ),
                        'item_reverted_to_draft'   => __( 'Locked content reverted to draft', 'pilopress' ),
                        'item_scheduled'           => __( 'Locked content scheduled', 'pilopress' ),
                        'item_updated'             => __( 'Locked content updated', 'pilopress' ),
                    ),
                    'supports'            => array( 'title', 'custom-fields' ),
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

            // WPML Compatibility - Set post types to be translatable by default
            if ( defined( 'ICL_LANGUAGE_CODE' ) ) {
                do_action( 'wpml_set_translation_mode_for_post_type', self::get_default_content_slug(), 'translate' );
                do_action( 'wpml_set_translation_mode_for_post_type', self::get_locked_content_slug(), 'translate' );
            }

            // Polylang Compatibility - Add post types to translated post types
            $polylang_option = get_option( 'polylang' );
            if ( $polylang_option ) {

                // Add post types
                $polylang_option['post_types'][ self::get_default_content_slug() ] = self::get_default_content_slug();
                $polylang_option['post_types'][ self::get_locked_content_slug() ]  = self::get_locked_content_slug();

                // Update option
                update_option( 'polylang', $polylang_option );
            }
        }

        /**
         * Auto-populate post types
         */
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
                                'post_title'  => 'Post Type: ' . $post_type_object->labels->name,
                                'post_name'   => $post_type,
                                'post_status' => 'publish',
                                'meta_input'  => array(
                                    'linked_post_type' => $post_type_object->name,
                                ),
                            )
                        );
                    }

                    // WPML compatibility
                    $this->auto_populate_wpml( $template_post_type, $template_post_id );

                    // Polylang compatibility
                    $this->auto_populate_post_types_polylang( $post_type, $template_post_id, $template_post_type );
                }
            }
        }

        /**
         * Auto-populate taxonomies
         */
        public function auto_populate_taxonomies() {

            // Get taxonomies
            $taxonomies = self::get_taxonomies();
            if ( !$taxonomies ) {
                return;
            }

            // Browse all taxonomies
            foreach ( $taxonomies as $taxonomy ) {

                // Get patterns taxonomies
                $templates_post_types = array( self::$default_content_post_type, self::$locked_content_post_type );
                foreach ( $templates_post_types as $template_post_type ) {

                    // Get pattern object
                    $template_post_object = get_page_by_path( $taxonomy, 'OBJECT', $template_post_type );
                    $template_post_id     = $template_post_object ? $template_post_object->ID : '';

                    // Insert post
                    if ( !$template_post_object ) {

                        $taxonomy_object  = get_taxonomy( $taxonomy );
                        $template_post_id = wp_insert_post(
                            array(
                                'post_type'   => $template_post_type,
                                'post_title'  => 'Taxonomy: ' . $taxonomy_object->labels->name,
                                'post_name'   => $taxonomy,
                                'post_status' => 'publish',
                                'meta_input'  => array(
                                    'linked_taxonomy' => $taxonomy_object->name,
                                ),
                            )
                        );
                    }

                    // WPML compatibility
                    $this->auto_populate_wpml( $template_post_type, $template_post_id );

                    // Polylang compatibility
                    $this->auto_populate_taxonomies_polylang( $taxonomy, $template_post_id, $template_post_type );
                }
            }
        }

        /**
         * Generate posts translations in WPML context
         *
         * @param $template_post_type
         * @param $template_post_id
         */
        private function auto_populate_wpml( $template_post_type, $template_post_id ) {

            // WPML Compatibility - Create translations duplicates
            if ( is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' ) ) {

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

                    // Generate translations for each language
                    do_action( 'wpml_admin_make_post_duplicates', $template_post_id );

                } else {
                    // Has translations, generate missing translations

                    $translations = apply_filters( 'wpml_get_element_translations', null, pip_maybe_get( $original_post_lang_data, 'trid' ), $wpml_element_type );
                    if ( empty( $translations ) ) {
                        return;
                    }

                    // If all translations are already there, abort
                    $missing_translations = array_diff_key( $active_languages, $translations );
                    if ( !$missing_translations ) {
                        return;
                    }

                    // Generate missing ones
                    global $sitepress;
                    foreach ( $missing_translations as $missing_lang_code => $lang_data ) {
                        $sitepress->make_duplicate( $template_post_id, $missing_lang_code );
                    }
                }
            }
        }

        /**
         * Generate posts translations in Polylang context
         *
         * @param $post_type
         * @param $template_post_id
         * @param $template_post_type
         */
        private function auto_populate_post_types_polylang( $post_type, $template_post_id, $template_post_type ) {

            // Polylang Compatibility - Create translations
            if ( is_plugin_active( 'polylang/polylang.php' ) && function_exists( 'pll_languages_list' ) ) {

                $languages              = pll_languages_list();
                $post_type_object       = get_post_type_object( $post_type );
                $available_translations = array();

                // Create translations if not already exists
                foreach ( $languages as $language ) {
                    $translated_id = pll_get_post( $template_post_id, $language );
                    if ( $translated_id ) {
                        $available_translations[ $language ] = $translated_id;
                    } else {
                        $available_translations[ $language ] = wp_insert_post(
                            array(
                                'post_type'   => $template_post_type,
                                'post_title'  => 'Post Type: ' . $post_type_object->labels->name,
                                'post_name'   => $post_type,
                                'post_status' => 'publish',
                                'meta_input'  => array(
                                    'linked_post_type' => $post_type_object->name,
                                ),
                            ),
                            true
                        );
                    }
                }

                if ( function_exists( 'pll_set_post_language' ) && function_exists( 'pll_save_post_translations' ) ) {

                    // Set language of main post to default value
                    pll_set_post_language( $template_post_id, pll_default_language() );

                    // Browse translations to set languages
                    foreach ( $available_translations as $language => $value ) {
                        pll_set_post_language( $value, $language );
                    }

                    // Link translations to main post
                    pll_save_post_translations( $available_translations );
                }
            }
        }

        /**
         * Generate posts translations in Polylang context
         *
         * @param $taxonomy
         * @param $template_post_id
         * @param $template_post_type
         */
        private function auto_populate_taxonomies_polylang( $taxonomy, $template_post_id, $template_post_type ) {

            // Polylang Compatibility - Create translations
            if ( is_plugin_active( 'polylang/polylang.php' ) && function_exists( 'pll_languages_list' ) ) {

                $languages              = pll_languages_list();
                $taxonomy_object        = get_taxonomy( $taxonomy );
                $available_translations = array();

                // Create translations if not already exists
                foreach ( $languages as $language ) {
                    $translated_id = pll_get_post( $template_post_id, $language );
                    if ( $translated_id ) {
                        $available_translations[ $language ] = $translated_id;
                    } else {
                        $available_translations[ $language ] = wp_insert_post(
                            array(
                                'post_type'   => $template_post_type,
                                'post_title'  => 'Taxonomy: ' . $taxonomy_object->labels->name,
                                'post_name'   => $taxonomy,
                                'post_status' => 'publish',
                                'meta_input'  => array(
                                    'linked_taxonomy' => $taxonomy_object->name,
                                ),
                            ),
                            true
                        );
                    }
                }

                if ( function_exists( 'pll_set_post_language' ) && function_exists( 'pll_save_post_translations' ) ) {

                    // Set language of main post to default value
                    pll_set_post_language( $template_post_id, pll_default_language() );

                    // Browse translations to set languages
                    foreach ( $available_translations as $language => $value ) {
                        pll_set_post_language( $value, $language );
                    }

                    // Link translations to main post
                    pll_save_post_translations( $available_translations );
                }
            }
        }

        /**
         * Get filtered post types
         *
         * @return array
         */
        public static function get_post_types() {
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

        /**
         * Get filtered taxonomies
         *
         * @return array
         */
        public static function get_taxonomies() {
            $filtered_taxonomies = array();

            $public_taxonomies = get_taxonomies(
                array(
                    'public' => true,
                )
            );

            // Get taxonomies
            $allowed_taxonomies = apply_filters( 'pip/patterns/taxonomies', $public_taxonomies );
            if ( !$allowed_taxonomies ) {
                return $filtered_taxonomies;
            }

            // Browse all taxonomies
            foreach ( $public_taxonomies as $taxonomy ) {
                // If not in templates, skip
                if ( !in_array( $taxonomy, $allowed_taxonomies, true ) ) {
                    continue;
                }

                // Store taxonomy
                $filtered_taxonomies[] = $taxonomy;
            }

            return $filtered_taxonomies;
        }

        /**
         * Getter: $default_content_post_type
         *
         * @return string
         */
        public static function get_default_content_slug() {
            return self::$default_content_post_type;
        }

        /**
         * Getter: $locked_content_post_type
         *
         * @return string
         */
        public static function get_locked_content_slug() {
            return self::$locked_content_post_type;
        }

    }

    // Instantiate
    new PIP_Patterns();
}
