<?php

defined( 'ABSPATH' ) || exit;

if ( !class_exists( 'PIP_Default_Content' ) ) {

    /**
     * Class PIP_Default_Content
     */
    class PIP_Default_Content {

        /**
         * PIP_Default_Content constructor.
         */
        public function __construct() {
            add_filter( 'acf/load_value/name=pip_flexible', array( $this, 'populate_default_content' ), 10, 3 );
        }

        /**
         * Pre-populate new post with default content's content
         *
         * @param $value
         * @param $post_id
         * @param $field
         *
         * @return mixed
         */
        public function populate_default_content( $value, $post_id, $field ) {
            $current_post_type = get_post_type( $post_id );
            $post_types        = PIP_Patterns::get_post_types();
            $polylang          = is_plugin_active( 'polylang/polylang.php' ) && function_exists( 'pll_get_post_language' );

            // Get current language for Polylang
            $current_language = null;
            if ( $polylang ) {
                $current_language = pll_get_post_language( $post_id );
            }

            // If current post is Default content or Locked content, return
            if ( $current_post_type === PIP_Patterns::get_default_content_slug() || $current_post_type === PIP_Patterns::get_locked_content_slug() ) {
                return $value;
            }

            // If current post type is not in patterns post types, return
            if ( !in_array( $current_post_type, $post_types, true ) ) {
                return $value;
            }

            // Main args
            $args = array(
                'post_type'        => PIP_Patterns::get_default_content_slug(),
                'suppress_filters' => 0,
            );

            // If Polylang add language filter
            if ( $polylang && $current_language ) {
                $args['lang'] = $current_language;
            }

            // If not Polylang (aka WPML), filter by post name
            if ( !$polylang ) {
                $args['post_name__in'] = array( $current_post_type );
            }

            // Get default content post
            $default_content_post = get_posts( $args );

            // If Polylang, filter posts by post names
            if ( $polylang ) {
                $post_names = wp_list_pluck( $default_content_post, 'post_name' );
                foreach ( $post_names as $index => $post_name ) {
                    if ( !strstr( $post_name, $current_post_type ) ) {
                        unset( $default_content_post[ $index ] );
                    }
                }
            }

            // Sanitize result
            $default_content_post = acf_unarray( $default_content_post );

            // If no result, return content
            if ( !$default_content_post ) {
                return $value;
            }

            // Return default content's content
            return get_field( 'pip_flexible', $default_content_post );
        }

    }

    // Instantiate
    new PIP_Default_Content();
}
