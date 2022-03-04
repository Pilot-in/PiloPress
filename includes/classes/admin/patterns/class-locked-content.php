<?php

defined( 'ABSPATH' ) || exit;

if ( !class_exists( 'PIP_Locked_Content' ) ) {

    /**
     * Class PIP_Locked_Content
     */
    class PIP_Locked_Content {

        /**
         * PIP_Locked_Content constructor.
         */
        public function __construct() {

            // PILO_TODO: Find a way to make layout current mandatory

            // WP hooks
            add_action( 'load-post.php', array( $this, 'add_admin_notice' ) );
            add_action( 'load-term.php', array( $this, 'add_admin_notice' ) );
            pip_include( 'acfe-php/group_pip_locked_content.php' );

            // Pilo'Press hooks
            add_filter( 'pip/layouts/file_path', array( $this, 'custom_layout_template_path' ), 10, 2 );
            add_filter( 'pip/layouts/thumbnail/file_path', array( $this, 'custom_layout_thumbnail_path' ), 10, 2 );
            add_filter( 'pip/layouts/thumbnail/file_url', array( $this, 'custom_layout_thumbnail_url' ), 10, 2 );

        }

        /**
         * Add notice to inform user Locked content is use
         */
        public function add_admin_notice() {
            // Only on page/post and terms
            $current_screen = get_current_screen();
            if ( $current_screen->base !== 'post' && $current_screen->base !== 'term' ) {
                return;
            }

            // If post ID or term ID not found, return
            $content_id = $current_screen->base === 'post' ? acf_maybe_get_GET( 'post' ) : ( $current_screen->base === 'term' ? acf_maybe_get_GET( 'tag_ID' ) : '' );
            if ( !$content_id ) {
                return;
            }

            // If no locked content, return
            $locked_content = self::get_locked_content( $content_id, 'term' );
            $pip_flexible   = acf_get_instance( 'PIP_Flexible' );
            if ( !get_flexible( $pip_flexible->flexible_field_name, $locked_content ) ) {
                return;
            }

            // Get Locked content edit link
            $edit_link = get_edit_post_link( $locked_content );

            // Add notice
            acf_add_admin_notice(
                sprintf(
                // translators: Link to post edition
                    __( 'Template with locked content is used for this content. <a href="%s">See template.</a>', 'pilopress' ),
                    $edit_link
                )
            );
        }

        /**
         * Get locked content associated to current post's post type
         *
         * @param        $post_id
         * @param string $type Accepted values: "post" and "term"
         *
         * @return mixed|null
         */
        public static function get_locked_content( $post_id, $type = 'post' ) {
            // Get object to determine if current content is a post or a term
            $queried_object   = get_queried_object();
            $current_taxonomy = null;
            if ( $queried_object ) {
                $type             = is_a( $queried_object, 'WP_Term' ) ? 'term' : 'post';
                $current_taxonomy = $queried_object->taxonomy;
            }

            // Get post type or taxonomy and build meta query array
            switch ( $type ) {
                default:
                case 'post':
                    // Get post type of current post
                    $current_post_type = get_post_type( $post_id );
                    $meta_query        = array(
                        array(
                            'key'     => 'linked_post_type',
                            'compare' => '=',
                            'value'   => $current_post_type,
                        ),
                    );
                    break;
                case 'term':
                    // Get taxonomy of current term
                    $current_taxonomy = $current_taxonomy ? $current_taxonomy : get_current_screen()->taxonomy;
                    $meta_query       = array(
                        array(
                            'key'     => 'linked_taxonomy',
                            'compare' => '=',
                            'value'   => $current_taxonomy,
                        ),
                    );
                    break;
            }

            // Check if Polylang is active
            $polylang = is_plugin_active( 'polylang/polylang.php' ) && function_exists( 'pll_get_post_language' );

            // Get current language for Polylang
            $current_language = null;
            if ( $polylang ) {
                $current_language = pll_get_post_language( $post_id );
            }

            // Main args
            $args = array(
                'post_type'        => PIP_Patterns::get_locked_content_slug(),
                'fields'           => 'ids',
                'posts_per_page'   => 1,
                'suppress_filters' => 0,
                'meta_query'       => $meta_query, // phpcs:ignore
            );

            // If Polylang add language filter
            if ( $polylang && $current_language ) {
                $args['lang'] = $current_language;
            }

            // Get locked content post
            $locked_content_post = get_posts( $args );

            // Sanitize result
            $locked_content_post = acf_unarray( $locked_content_post );

            // If no result, return
            if ( !$locked_content_post ) {
                return null;
            }

            return $locked_content_post;
        }

        /**
         * Set layout template path to path inside Pilo'Press
         *
         * @param $file_path
         * @param $field_group
         *
         * @return mixed|string
         */
        public function custom_layout_template_path( $file_path, $field_group ) {
            if ( acf_maybe_get( $field_group, 'key' ) !== 'group_pip_target_content' ) {
                return $file_path;
            }

            $layout_slug = acf_maybe_get( $field_group, '_pip_layout_slug' );

            return PIP_PATH . "includes/layouts/$layout_slug/";
        }

        /**
         * Set layout thumbnail path to path inside Pilo'Press
         *
         * @param $file_path
         * @param $field_group
         *
         * @return mixed|string
         */
        public function custom_layout_thumbnail_path( $file_path, $field_group ) {
            if ( acf_maybe_get( $field_group, 'key' ) !== 'group_pip_target_content' ) {
                return $file_path;
            }

            $layout_slug = acf_maybe_get( $field_group, '_pip_layout_slug' );

            return PIP_PATH . "includes/layouts/$layout_slug/$layout_slug";
        }

        /**
         * Set layout thumbnail URL to URL inside Pilo'Press
         *
         * @param $file_path
         * @param $field_group
         *
         * @return mixed|string
         */
        public function custom_layout_thumbnail_url( $file_path, $field_group ) {
            if ( acf_maybe_get( $field_group, 'key' ) !== 'group_pip_target_content' ) {
                return $file_path;
            }

            $layout_slug = acf_maybe_get( $field_group, '_pip_layout_slug' );

            return PIP_URL . "includes/layouts/$layout_slug/$layout_slug";
        }

    }

    // Instantiate
    new PIP_Locked_Content();
}
