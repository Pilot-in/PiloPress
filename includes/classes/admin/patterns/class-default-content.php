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

            $pip_flexible = acf_get_instance( 'PIP_Flexible' );

            // ACF hooks
            add_filter( 'acf/load_value/name=' . $pip_flexible->flexible_field_name, array( $this, 'populate_default_content' ), 10, 3 );

        }

        /**
         * Pre-populate new object with default content's content
         *
         * @param $value
         * @param $post_id
         * @param $field
         *
         * @return mixed
         */
        public function populate_default_content( $value, $post_id, $field ) {

            // Fires only on admin side
            if ( !is_admin() ) {
                return $value;
            }

            // If post already has content, return content
            if ( $value ) {
                return $value;
            }

            // Get content for terms and post types
            $content = $value;
            $current_screen = get_current_screen();
            if ( $current_screen ) {
                switch ( $current_screen->base ) {
                    case 'edit-tags':
                        $content = $this->terms_content( $current_screen, $post_id );
                        break;
                    default:
                        $content = $this->post_types_content( $post_id );
                        break;
                }
            }

            return $content;
        }

        /**
         * Get default content for post types
         *
         * @param $post_id
         *
         * @return mixed|null
         */
        private function post_types_content( $post_id ) {
            // Get post types and current post type
            $current_post_type = get_post_type( $post_id );
            $post_types        = PIP_Patterns::get_post_types();

            // Check if Polylang is active
            $polylang = is_plugin_active( 'polylang/polylang.php' ) && function_exists( 'pll_get_post_language' );

            // Get current language for Polylang
            $current_language = null;
            if ( $polylang ) {
                $current_language = pll_get_post_language( $post_id );
            }

            // If current post is Default content or Locked content, return
            if ( $current_post_type === PIP_Patterns::get_default_content_slug() || $current_post_type === PIP_Patterns::get_locked_content_slug() ) {
                return null;
            }

            // If current post type is not in patterns post types, return
            if ( !in_array( $current_post_type, $post_types, true ) ) {
                return null;
            }

            // Main args
            $args = array(
                'post_type'        => PIP_Patterns::get_default_content_slug(),
                'fields'           => 'ids',
                'posts_per_page'   => 1,
                'suppress_filters' => 0,
                'meta_query'       => array( // phpcs:ignore
                    array(
                        'key'     => 'linked_post_type',
                        'compare' => '=',
                        'value'   => $current_post_type,
                    ),
                ),
            );

            // If Polylang add language filter
            if ( $polylang && $current_language ) {
                $args['lang'] = $current_language;
            }

            // Get default content post
            $default_content_post = get_posts( $args );

            // Sanitize result
            $default_content_post = acf_unarray( $default_content_post );

            // If no result, return
            if ( !$default_content_post ) {
                return null;
            }

            $pip_flexible = acf_get_instance( 'PIP_Flexible' );

            // Return default content's content
            return get_field( $pip_flexible->flexible_field_name, $default_content_post, false );
        }

        /**
         * Get default content for terms
         *
         * @param WP_Screen $current_screen
         * @param int       $post_id
         *
         * @return mixed|null
         */
        private function terms_content( $current_screen, $post_id ) {
            // Get taxonomies and current term taxonomy
            $current_taxonomy = $current_screen->taxonomy;
            $taxonomies       = PIP_Patterns::get_taxonomies();

            // Check if Polylang is active
            $polylang = is_plugin_active( 'polylang/polylang.php' ) && function_exists( 'pll_get_post_language' );

            // Get current language for Polylang
            $current_language = null;
            if ( $polylang ) {
                $current_language = pll_get_post_language( $post_id );
            }

            // If current taxonomy is not in patterns taxonomies, return
            if ( !in_array( $current_taxonomy, $taxonomies, true ) ) {
                return null;
            }

            // Main args
            $args = array(
                'post_type'        => PIP_Patterns::get_default_content_slug(),
                'fields'           => 'ids',
                'posts_per_page'   => 1,
                'suppress_filters' => 0,
                'meta_query'       => array( // phpcs:ignore
                    array(
                        'key'     => 'linked_taxonomy',
                        'compare' => '=',
                        'value'   => $current_taxonomy,
                    ),
                ),
            );

            // If Polylang add language filter
            if ( $polylang && $current_language ) {
                $args['lang'] = $current_language;
            }

            // Get default content post
            $default_content_post = get_posts( $args );

            // Sanitize result
            $default_content_post = acf_unarray( $default_content_post );

            // If no result, return
            if ( !$default_content_post ) {
                return null;
            }

            $pip_flexible = acf_get_instance( 'PIP_Flexible' );
            $single_meta  = acfe_get_setting( 'modules/single_meta' );

            // Get default content's flexible
            if ( $single_meta ) {
                $acf_meta          = get_post_meta( $default_content_post, 'acf' );
                $acf_meta          = acf_unarray( $acf_meta );
                $pip_flexible_meta = acf_maybe_get( $acf_meta, 'pip_flexible' );
            } else {
                $pip_flexible_meta = get_post_meta( $default_content_post, 'pip_flexible', true );
            }

            // If flexible is not empty, return content
            return $pip_flexible_meta ? get_field( $pip_flexible->flexible_field_name, $default_content_post ) : null;
        }

    }

    // Instantiate
    new PIP_Default_Content();
}
