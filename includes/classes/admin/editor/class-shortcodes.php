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
                    'text'          => false,
                    'type'          => false,
                    'alignment'     => false,
                    'target'        => false,
                    'download'      => false,
                    'download_name' => false,
                    'xclass'        => false,
                    'link'          => '',
                    'nodiv'         => false,
                    'icon'          => false,
                    'icon_position' => false,
                ),
                $attrs,
                'pip_button'
            );

            // Build class
            $btn_class = '';
            $btn_class .= ( $attrs['type'] ) ? $attrs['type'] : '';
            $btn_class .= ( $attrs['xclass'] ) ? ' ' . $attrs['xclass'] : '';

            // Get args
            $btn_class     = esc_attr( trim( $btn_class ) );
            $alignment     = esc_attr( $attrs['alignment'] );
            $btn_link      = esc_url( $attrs['link'] );
            $target        = $attrs['target'] ? esc_attr( $attrs['target'] ) : '_blank';
            $download      = $attrs['download'];
            $download_name = $attrs['download_name'];
            $btn_text      = $attrs['text'];
            $no_div        = $attrs['nodiv'];
            $icon_class    = $attrs['icon'];
            $icon_pos      = $attrs['icon_position'];

            $html = '';
            if ( !$no_div ) {
                $html .= '<div class="' . $alignment . '">';
            }

            $download_option = '';
            if ( $download ) {
                $download_name   = $download_name ? $download_name : 'download';
                $download_option = 'download="' . $download_name . '"';
            }

            $html .= '<a href="' . $btn_link . '" class="' . $btn_class . '" target="' . $target . '" ' . $download_option . '>';

            if ( $icon_class ) {

                $is_rtl      = pip_is_rtl();
                $icon_margin = $icon_pos === 'left' ? ( $is_rtl ? 'ml-2' : 'mr-2' ) : ( $is_rtl ? 'mr-2' : 'ml-2' );
                $icon_margin = apply_filters( 'pip/shortcode/button/icon_margin', $icon_margin, $icon_pos, $icon_class, $is_rtl );
                $icon_html   = '<i class="' . $icon_class . ' ' . $icon_margin . '"></i>';

                switch ( $icon_pos ) {
                    case 'left':
                        $html .= $icon_html . $btn_text;
                        break;
                    case 'right':
                        $html .= $btn_text . $icon_html;
                        break;
                }

                $html .= '</a>';
            } else {
                $html .= $btn_text . '</a>';
            }

            if ( !$no_div ) {
                $html .= '</div>';
            }

            // Render shortcode
            return do_shortcode( $html );
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
            $class = apply_filters( 'pip/shortcode/button_group/class', $class );

            // Render shortcode
            return sprintf( '<div class="%s pip_button_group">%s</div>', esc_attr( trim( $class ) ), do_shortcode( $content ) );
        }



        /**
         * Breadcrumb shortcode
         *
         * @return string|null
         */
        public function pip_breadcrumb() {

            $breadcrumb = '';

            // Use Yoast if available
            if ( function_exists( 'yoast_breadcrumb' ) ) {
                $breadcrumb = yoast_breadcrumb( '<p class="pip_breadcrumb">', '</p>', false );
            }

            // Use Rank Math if available
            elseif ( function_exists( 'rank_math_the_breadcrumbs' ) ) {
                $breadcrumb = rank_math_get_breadcrumbs();
            }

            // If AJAX, display default message
            if ( wp_doing_ajax() ) {
                $breadcrumb = __( 'You > Are > Here', 'pilopress' );
            }

            // 3rd party hook if needed
            $breadcrumb = apply_filters( 'pip/shortcode/breadcrumb', $breadcrumb );

            // Render shortcode
            return $breadcrumb;
        }

        /**
         * Title shortcode
         *
         * @return string
         */
        public function pip_title() {

            $title = '';

            // Front
            if ( is_singular() ) {

                // Post / Page...
                $title = get_the_title();

            } elseif ( is_home() ) {

                // Blog archive page
                $home_post_object = get_queried_object();
                $title            = pip_maybe_get( $home_post_object, 'post_title' );

            } elseif ( !is_admin() ) {

                // Taxonomy / Category / Tag / Archive / User...
                $title = get_the_archive_title();

            }

            // Admin
            if ( is_admin() ) {

                // Admin screen data
                $screen_obj  = get_current_screen();
                $screen_data = (array) $screen_obj;

                // Admin - GET data (WordPress)
                $screen_base = acf_maybe_get( $screen_data, 'base' );

                // Compatibility fix for ACFE archive
                if ( stripos( $screen_base, '-archive' ) !== false ) {
                    $screen_base = 'archive';
                }

                switch ( $screen_base ) {

                    // Post type
                    case 'post':
                        // Get ID
                        $found_id = acf_maybe_get_GET( 'post' );

                        // Get title
                        $title = get_the_title( $found_id );
                        break;

                    // Term
                    case 'term':
                        // Get ID
                        $found_id = acf_maybe_get_GET( 'tag_ID' );

                        // Get term
                        $found_term_obj  = get_term( $found_id );
                        $found_term_data = (array) $found_term_obj;

                        // Get title
                        $found_term_title = acf_maybe_get( $found_term_data, 'name' );
                        $title            = $found_term_title ? $found_term_title : $title;
                        break;

                    // ACFE Archive (option)
                    case 'archive':
                        // Get post type
                        $found_post_type = acf_maybe_get_GET( 'post_type' );
                        $post_type_obj   = get_post_type_object( $found_post_type );
                        $post_type_data  = (array) $post_type_obj;

                        // Get labels
                        $post_type_labels = acf_maybe_get( $post_type_data, 'labels' );
                        $post_type_labels = (array) $post_type_labels;

                        // Get title
                        $post_type_label = acf_maybe_get( $post_type_labels, 'archives' ) ? acf_maybe_get( $post_type_labels, 'archives' ) : acf_maybe_get( $post_type_data, 'label' );
                        $title           = $post_type_label ? $post_type_label : $title;
                        break;

                    default:
                        // code...
                        break;
                }

                if ( !$title ) {

                    // Admin - AJAX data (ACFE)
                    $found_acf_id = acf_maybe_get_POST( 'post_id' );
                    if ( $found_acf_id ) {

                        // Get ID
                        $found_id_data = acf_get_post_id_info( $found_acf_id );
                        $found_id      = acf_maybe_get( $found_id_data, 'id' );

                        // Get type
                        $found_id_type = acf_maybe_get( $found_id_data, 'type' );

                        // Compatibility fix for ACFE archive
                        if ( stripos( $found_id, '_archive' ) !== false ) {
                            $found_id_type = 'archive';
                        }

                        switch ( $found_id_type ) {

                            // Post type
                            default:
                            case 'post':
                                $title = get_the_title( $found_id );
                                break;

                            // Term
                            case 'term':
                                // Get term
                                $found_term_obj  = get_term( $found_id );
                                $found_term_data = (array) $found_term_obj;

                                // Get title
                                $found_term_title = acf_maybe_get( $found_term_data, 'name' );
                                $title            = $found_term_title ? $found_term_title : $title;
                                break;

                            // ACFE Archive (option)
                            case 'archive':
                                // Get post type
                                $found_post_type = str_replace( '_archive', '', $found_id );
                                $post_type_obj   = get_post_type_object( $found_post_type );
                                $post_type_data  = (array) $post_type_obj;

                                // Get labels
                                $post_type_labels = acf_maybe_get( $post_type_data, 'labels' );
                                $post_type_labels = (array) $post_type_labels;

                                // Get title
                                $post_type_label = acf_maybe_get( $post_type_labels, 'archives' ) ? acf_maybe_get( $post_type_labels, 'archives' ) : acf_maybe_get( $post_type_data, 'label' );
                                $title           = $post_type_label ? $post_type_label : $title;
                                break;

                        }
                    }
                }
            }

            // Fallback title
            if ( !$title ) {
                $title = __( 'Title here', 'pilopress' );
            }

            // Return title
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

    acf_new_instance( 'PIP_Shortcodes' );

}
