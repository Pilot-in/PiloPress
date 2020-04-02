<?php

if ( !class_exists( 'PIP_TinyMCE' ) ) {
    class PIP_TinyMCE {
        public function __construct() {
            // WP hooks
//            add_action( 'wp_head', array( $this, 'custom_fonts_stylesheets' ) );
//            add_action( 'admin_head', array( $this, 'custom_fonts_stylesheets' ) );
//            add_action( 'admin_init', array( $this, 'add_custom_fonts_to_editor' ) );
            add_action( 'admin_enqueue_scripts', array( $this, 'localize_data' ) );
            add_filter( 'mce_external_plugins', array( $this, 'editor_button_script' ) );
            add_filter( 'mce_css', array( $this, 'editor_style' ) );

            // ACF hooks
            add_filter( 'acf/fields/wysiwyg/toolbars', array( $this, 'customize_toolbar' ) );
        }

        /**
         * Enqueue custom TinyMCE script and add variables to it
         */
        public function localize_data() {
            acf_localize_data( array(
                'custom_fonts'        => self::get_custom_fonts(),
                'image_sizes'         => self::get_all_image_sizes(),
                'custom_colors'       => self:: get_custom_colors( true ),
                'custom_colors_assoc' => self:: get_custom_colors(),
                'custom_styles'       => self:: get_custom_styles(),
                'custom_spacers'      => PIP_Styles_Settings::get_spacers( 'array' ),
            ) );
        }

        /**
         * Get custom typography
         * @return array
         */
        public static function get_custom_styles() {
            $custom_styles = array();
            $styles        = get_field( 'pip_custom_typography', 'pip_styles_typography' );

            // If no custom style, return
            if ( !is_array( $styles ) ) {
                return $custom_styles;
            }

            // Format array for TinyMCE
            foreach ( $styles as $style ) {
                $custom_styles[ $style['custom_typography_class_name'] ] = $style['custom_typography_name'];
            }

            return $custom_styles;
        }

        /**
         * Get all image sizes
         * @return array
         */
        public static function get_all_image_sizes() {
            $image_sizes = array();

            // Get image sizes
            $default_image_sizes = get_intermediate_image_sizes();

            foreach ( $default_image_sizes as $size ) {
                $image_sizes[ $size ] = array(
                    'width'  => intval( get_option( "{$size}_size_w" ) ),
                    'height' => intval( get_option( "{$size}_size_h" ) ),
                    'crop'   => intval( get_option( "{$size}_crop" ) ),
                );
            }

            if ( isset( $_wp_additional_image_sizes ) && count( $_wp_additional_image_sizes ) ) {
                $image_sizes = array_merge( $image_sizes, $_wp_additional_image_sizes );
            }

            return $image_sizes;
        }

        /**
         * Get colors
         *
         * @param bool $split
         *
         * @return array|null
         */
        public function get_custom_colors( $split = false ) {
            $color_range = array();

            // Get colors
            $colors = PIP_Styles_Settings::get_colors();
            if ( !$colors ) {
                return $color_range;
            }

            // Associative array or split values
            if ( $split ) {
                foreach ( $colors as $name => $color ) {
                    $color_range[] = str_replace( '#', '', $color );
                    $color_range[] = $name;
                }
            } else {
                $color_range = $colors;
            }

            return $color_range;
        }

        /**
         * Get custom fonts for TinyMCE
         * @return array
         */
        private static function get_custom_fonts() {
            $fonts = array();

            // Get custom fonts
            if ( have_rows( 'pip_fonts', 'pip_styles_fonts' ) ) {
                while ( have_rows( 'pip_fonts', 'pip_styles_fonts' ) ) {
                    the_row();

                    // Get font name
                    $font     = get_sub_field( 'name' );
                    $tiny_mce = get_sub_field( 'tinymce' );

                    // Not displayed in TinyMCE
                    if ( !$tiny_mce ) {
                        continue;
                    }

                    // Add custom font
                    $fonts[ $tiny_mce['value'] ] = [
                        'name' => $tiny_mce['label'],
                        'font' => $font,
                    ];
                }
            }

            return $fonts;
        }

        /**
         * Enqueue custom fonts
         */
        public function custom_fonts_stylesheets() {
            // Get custom fonts
            if ( have_rows( 'pip_fonts', 'pip_styles_fonts' ) ) {
                while ( have_rows( 'pip_fonts', 'pip_styles_fonts' ) ) {
                    the_row();

                    // If not google font, skip
                    if ( get_row_layout() !== 'google_font' ) {
                        continue;
                    }

                    // Get sub fields
                    $enqueue = get_sub_field( 'enqueue' );
                    $url     = get_sub_field( 'url' );

                    // Auto enqueue to false
                    if ( !$enqueue ) {
                        continue;
                    }

                    // Add google font
                    echo '<link href="' . $url . '" rel="stylesheet">';
                }
            }
        }

        /**
         * Add custom fonts to editor
         */
        public function add_custom_fonts_to_editor() {
            // Get custom fonts
            if ( have_rows( 'pip_fonts', 'pip_styles_fonts' ) ) {
                while ( have_rows( 'pip_fonts', 'pip_styles_fonts' ) ) {
                    the_row();

                    // If not google font, skip
                    if ( get_row_layout() !== 'google_font' ) {
                        continue;
                    }

                    // Get sub fields
                    $enqueue = get_sub_field( 'enqueue' );
                    $url     = get_sub_field( 'url' );

                    // Auto enqueue to false
                    if ( !$enqueue ) {
                        continue;
                    }

                    // Enqueue google font
                    add_editor_style( str_replace( ',', '%2C', $url ) );
                }
            }
        }

        /**
         * Customize toolbars
         *
         * @param $toolbars
         *
         * @return mixed
         */
        public function customize_toolbar( $toolbars ) {
            // Remove basic toolbar
            unset( $toolbars['Basic'] );

            return $toolbars;
        }

        /**
         * Add editor options
         *
         * @param $scripts
         *
         * @return mixed
         */
        public function editor_button_script( $scripts ) {
            $scripts['pip_colors']     = PIP_URL . 'assets/js/tinymce-custom-styles.js';
            $scripts['pip_fonts']      = PIP_URL . 'assets/js/tinymce-custom-styles.js';
            $scripts['pip_styles']     = PIP_URL . 'assets/js/tinymce-custom-styles.js';
            $scripts['pip_shortcodes'] = PIP_URL . 'assets/js/tinymce-shortcodes.js';

            return $scripts;
        }

        /**
         * Add custom editor style and remove WP's one
         *
         * @param $stylesheets
         *
         * @return string
         */
        public function editor_style( $stylesheets ) {
            $stylesheets = explode( ',', $stylesheets );

            // Parse stylesheets to remove WP CSS
            foreach ( $stylesheets as $key => $stylesheet ) {
                if ( strstr( $stylesheet, 'wp-content.css' ) ) {
                    unset( $stylesheets[ $key ] );
                }
            }

            // Add custom admin stylesheet
            $stylesheets[] = PIP_THEME_STYLE_URL . 'style-pilopress-admin.css';

            return implode( ',', $stylesheets );
        }
    }

    // Instantiate class
    new PIP_TinyMCE();
}
