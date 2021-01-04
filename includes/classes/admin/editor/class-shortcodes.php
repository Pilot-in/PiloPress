<?php

if ( !class_exists( 'PIP_Shortcodes' ) ) {

    /**
     * Class PIP_Shortcodes
     */
    class PIP_Shortcodes {
        public function __construct() {
            // WP hooks
            add_action( 'init', array( $this, 'register_shortcodes' ) );
        }

        /**
         * Register shortcodes
         */
        public function register_shortcodes() {
            add_shortcode( 'pip_breadcrumb', array( $this, 'pip_breadcrumb' ) );
            add_shortcode( 'pip_button', array( $this, 'pip_button' ) );
            add_shortcode( 'pip_button_group', array( $this, 'pip_button_group' ) );
            add_shortcode( 'pip_spacer', array( $this, 'pip_spacer' ) );
            add_shortcode( 'pip_title', array( $this, 'pip_title' ) );
            add_shortcode( 'pip_thumbnail', array( $this, 'pip_thumbnail' ) );
        }

        /**
         * Button shortcode
         *
         * @param $attrs
         *
         * @return string
         */
        public function pip_button( $attrs ) {
            // Parse attributes
            $attrs = shortcode_atts(
                array(
                    'text'      => false,
                    'type'      => false,
                    'alignment' => false,
                    'target'    => false,
                    'xclass'    => false,
                    'link'      => '',
                    'nodiv'     => false,
                ),
                $attrs,
                'pip_button'
            );

            // Build class
            $class = '';
            $class .= ( $attrs['type'] ) ? $attrs['type'] : '';
            $class .= ( $attrs['xclass'] ) ? ' ' . $attrs['xclass'] : '';

            if ( !$attrs['nodiv'] ) {
                $html = do_shortcode(
                    sprintf(
                        '<div class="%s"><a href="%s" class="%s"%s>%s</a></div>',
                        esc_attr( $attrs['alignment'] ),
                        esc_url( $attrs['link'] ),
                        esc_attr( trim( $class ) ),
                        ( $attrs['target'] ) ? sprintf( ' target="%s"', esc_attr( $attrs['target'] ) ) : '',
                        ( $attrs['text'] ) ? $attrs['text'] : ''
                    )
                );
            } else {
                $html = do_shortcode(
                    sprintf(
                        '<a href="%s" class="%s"%s>%s</a>',
                        esc_url( $attrs['link'] ),
                        esc_attr( trim( $class ) ),
                        ( $attrs['target'] ) ? sprintf( ' target="%s"', esc_attr( $attrs['target'] ) ) : '',
                        ( $attrs['text'] ) ? $attrs['text'] : ''
                    )
                );
            }

            // Render shortcode
            return $html;
        }

        /**
         * Button group shortcode
         *
         * @param      $attrs
         * @param null $content
         *
         * @return string
         */
        public function pip_button_group( $attrs, $content = null ) {
            // Parse attributes
            $attrs = shortcode_atts(
                array(
                    'number'    => false,
                    'alignment' => false,
                ),
                $attrs,
                'pip_button_group'
            );

            $class = acf_maybe_get( $attrs, 'alignment', '' );

            // Render shortcode
            return sprintf(
                '<div class="%s pip_button_group">%s</div>',
                esc_attr( trim( $class ) ),
                do_shortcode( $content )
            );
        }

        /**
         * Breadcrumb shortcode
         *
         * @return string|null
         */
        public function pip_breadcrumb() {
            // If no Yoast, return
            if ( !function_exists( 'yoast_breadcrumb' ) ) {
                return null;
            }

            // If AJAX, display default message
            if ( wp_doing_ajax() ) {
                return __( 'You > Are > Here', 'pilopress' );
            }

            // Render shortcode
            return yoast_breadcrumb( '<p class="pip_breadcrumb">', '</p>', false );
        }

        /**
         * Title shortcode
         *
         * @return string
         */
        public function pip_title() {

            $title = __( 'Title here', 'pilopress' );

            // If AJAX, display default message
            if ( wp_doing_ajax() ) {
                return $title;
            }

            if ( is_tax() || is_category() || is_tag() ) {

                //* Taxonomy / Category / Tag
                $title = single_term_title( '', false );

            } else {

                //* Post / Page / other...
                $title = get_the_title();
            }

            // Render shortcode
            return $title;
        }

        /**
         * Thumbnail shortcode
         *
         * @param $attrs
         *
         * @return string|null
         */
        public function pip_thumbnail( $attrs ) {
            // Parse attributes
            $attrs = shortcode_atts(
                array(
                    'size' => 'full',
                ),
                $attrs,
                'pip_thumbnail'
            );

            // Get post thumbnail URL
            $image_size         = $attrs['size'];
            $post_thumbnail_url = get_the_post_thumbnail_url( get_the_ID(), $image_size );

            // If no URL, display default message
            if ( !$post_thumbnail_url ) {
                return __( 'Post thumbnail here', 'pilopress' );
            }

            // Render shortcode
            return '<img class="post-thumbnail" src="' . $post_thumbnail_url . '"/>';
        }

        /**
         * Spacer shortcode
         *
         * @param $attrs
         *
         * @return string
         */
        public function pip_spacer( $attrs ) {
            // Parse attributes
            $attrs = shortcode_atts(
                array(
                    'spacer' => 1,
                ),
                $attrs,
                'pip_spacer'
            );

            return '<div class="' . $attrs['spacer'] . '"></div>';
        }
    }

    new PIP_Shortcodes();
}
