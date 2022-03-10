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

            $pip_flexible = acf_get_instance( 'PIP_Flexible' );

            // WP hooks
            add_action( 'load-post.php', array( $this, 'add_admin_notice' ) );
            add_action( 'post.php', array( $this, 'add_admin_notice' ) );
            add_action( 'load-term.php', array( $this, 'add_admin_notice' ) );
            add_action( 'term.php', array( $this, 'add_admin_notice' ) );

            // ACF hooks
            add_filter( 'acf/load_value/name=' . $pip_flexible->flexible_field_name, array( $this, 'add_target_content_layout_by_default' ), 10, 3 );

            // Pilo'Press hooks
            add_filter( 'pip/layouts/file_path', array( $this, 'custom_layout_template_path' ), 10, 2 );
            add_filter( 'pip/layouts/thumbnail/file_path', array( $this, 'custom_layout_thumbnail_path' ), 10, 2 );
            add_filter( 'pip/layouts/thumbnail/file_url', array( $this, 'custom_layout_thumbnail_url' ), 10, 2 );

            // Field groups
            acf_add_local_field_group(
                array(
                    'key'                   => 'group_pip_locked_content',
                    'title'                 => __( 'Locked content', 'pilopress' ),
                    'fields'                => array(
                        array(
                            'key'               => 'field_62222e6281fa6',
                            'label'             => 'Instructions',
                            'name'              => '',
                            'type'              => 'message',
                            'instructions'      => '',
                            'required'          => 0,
                            'conditional_logic' => 0,
                            'wrapper'           => array(
                                'width' => '',
                                'class' => '',
                                'id'    => '',
                            ),
                            'acfe_permissions'  => '',
                            'message'           => '<h2>You must add <strong><code>Target content</code> layout</strong> if you don\'t want to override layouts used in your target content.</h2>',
                            'new_lines'         => 'wpautop',
                            'esc_html'          => 0,
                            'acfe_settings'     => '',
                        ),
                    ),
                    'location'              => array(
                        array(
                            array(
                                'param'    => 'post_type',
                                'operator' => '==',
                                'value'    => 'pip-locked-content',
                            ),
                        ),
                    ),
                    'menu_order'            => 0,
                    'position'              => 'acf_after_title',
                    'style'                 => 'seamless',
                    'label_placement'       => 'top',
                    'instruction_placement' => 'label',
                    'hide_on_screen'        => '',
                    'active'                => true,
                    'description'           => '',
                    'show_in_rest'          => 0,
                    'acfe_display_title'    => '',
                    'acfe_autosync'         => array(
                        'json',
                        'php',
                    ),
                    'acfe_permissions'      => '',
                    'acfe_form'             => 1,
                    'acfe_meta'             => '',
                    'acfe_note'             => '',
                )
            );

        }

        /**
         * Automatically add "Target content" layout when no layout is set
         *
         * @param $value
         * @param $post_id
         * @param $field
         *
         * @return mixed
         */
        public function add_target_content_layout_by_default( $value, $post_id, $field ) {

            // Fires only on admin side
            if ( !is_admin() ) {
                return $value;
            }

            // If post already has content, return content
            if ( $value ) {
                return $value;
            }

            $current_post_type = get_post_type( $post_id );
            if ( $current_post_type !== PIP_Patterns::get_locked_content_slug() ) {
                return $value;
            }

            $target_content_layout_values = array(
                array(
                    'acf_fc_layout'              => 'locked-content-target-content',
                    'acfe_flexible_toggle'       => '',
                    'acfe_flexible_layout_title' => 'Locked content: Target content',
                ),
            );

            return $target_content_layout_values;
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
            $locked_content_id = self::get_locked_content( $content_id );
            $pip_flexible      = acf_get_instance( 'PIP_Flexible' );
            if ( !get_flexible( $pip_flexible->flexible_field_name, $locked_content_id ) ) {
                return;
            }

            // If locked content is not custom, return
            $has_custom_locked_content = self::has_custom_locked_content( $content_id );
            if ( !$has_custom_locked_content ) {
                return;
            }

            // Get content name
            $pattern_title = get_post_meta( $locked_content_id, 'linked_post_type', true ) ?
                get_post_meta( $locked_content_id, 'linked_post_type', true ) :
                get_post_meta( $locked_content_id, 'linked_taxonomy', true );
            $pattern_title = str_replace( '_', ' ', $pattern_title );
            $pattern_title = str_replace( '-', ' ', $pattern_title );

            // Get Locked content edit link
            $edit_link = get_edit_post_link( $locked_content_id );

            // Add notice
            acf_add_admin_notice(
                sprintf(
                    // translators: Link to post edition
                    __( 'This content is using <strong>Locked content</strong> <em>(Patterns)</em>.', 'pilopress' ) .
                    ' <a href="%s" target="_blank">' . __( 'Edit locked content', 'pilopress' ) . " ($pattern_title)</a>",
                    $edit_link
                ),
                'warning',
                false
            );
        }

        /**
         * Undocumented function
         *
         * @param [type] $post_id
         * @return boolean
         */
        public static function has_custom_locked_content( $post_id ) {

            $has_custom_locked_content = false;
            $locked_content_id         = self::get_locked_content( $post_id );
            if ( !$locked_content_id ) {
                return $has_custom_locked_content;
            }

            // Check if pattern post has no layout
            $pattern_post_layouts = get_field( 'pip_flexible', $locked_content_id );
            if ( !is_array( $pattern_post_layouts ) || empty( $pattern_post_layouts ) ) {
                return $has_custom_locked_content;
            }

            // At this point, we assume there are custom layouts
            $has_custom_locked_content = true;

            // Invalidate if there is exactly 1 layout which isn't "Target content" layout
            if ( count( $pattern_post_layouts ) === 1 ) {
                $pattern_post_layout = acf_unarray( $pattern_post_layouts );
                $layout_key          = acf_maybe_get( $pattern_post_layout, 'acf_fc_layout' );
                if ( $layout_key === 'locked-content-target-content' ) {
                    $has_custom_locked_content = false;
                }
            }

            return $has_custom_locked_content;
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
                    $current_taxonomy = $queried_object->taxonomy ?: get_current_screen()->taxonomy;
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
