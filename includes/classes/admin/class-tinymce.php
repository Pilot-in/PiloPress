<?php

if ( !class_exists( 'PIP_TinyMCE' ) ) {
    class PIP_TinyMCE {
        public function __construct() {
            // WP hooks
            add_action( 'wp_head', array( $this, 'custom_fonts_stylesheets' ) );
            add_action( 'admin_head', array( $this, 'custom_fonts_stylesheets' ) );
            add_action( 'admin_init', array( $this, 'add_custom_fonts_to_editor' ) );
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
                'custom_fonts'   => self::get_custom_fonts(),
                'custom_styles'  => self:: get_custom_styles(),
                'custom_colors'  => self:: get_custom_colors(),
                'custom_buttons' => self:: get_custom_buttons(),
                'image_sizes'    => self::get_all_image_sizes(),
            ) );
        }

        /**
         * Get custom fonts families
         * @return array
         */
        private static function get_custom_fonts() {
            $fonts = array();

            // Get custom fonts
            if ( have_rows( 'pip_font_family', 'pip_styles_tinymce' ) ) {
                while ( have_rows( 'pip_font_family', 'pip_styles_tinymce' ) ) {
                    the_row();

                    // Get font name
                    $label   = get_sub_field( 'label' );
                    $classes = get_sub_field( 'classes_to_apply' );

                    // Add custom font
                    $fonts[ sanitize_title( $label ) ] = [
                        'name'    => $label,
                        'classes' => $classes,
                    ];
                }
            }

            return $fonts;
        }

        /**
         * Get custom font styles
         * @return array
         */
        public static function get_custom_styles() {
            $custom_styles = array();

            // Get custom styles
            if ( have_rows( 'pip_font_style', 'pip_styles_tinymce' ) ) {
                while ( have_rows( 'pip_font_style', 'pip_styles_tinymce' ) ) {
                    the_row();

                    $label   = get_sub_field( 'label' );
                    $classes = get_sub_field( 'classes_to_apply' );

                    // Add custom style
                    $custom_styles[ sanitize_title( $label ) ] = [
                        'name'    => $label,
                        'classes' => $classes,
                    ];
                }
            }

            return $custom_styles;
        }

        /**
         * Get custom font colors
         * @return array
         */
        public static function get_custom_colors() {
            $colors = array();

            // Get custom styles
            if ( have_rows( 'pip_font_color', 'pip_styles_tinymce' ) ) {
                while ( have_rows( 'pip_font_color', 'pip_styles_tinymce' ) ) {
                    the_row();

                    $label   = get_sub_field( 'label' );
                    $classes = get_sub_field( 'classes_to_apply' );

                    // Add custom style
                    $colors[ sanitize_title( $label ) ] = [
                        'name'    => $label,
                        'font'    => $label,
                        'classes' => $classes,
                    ];
                }
            }

            return $colors;
        }

        /**
         * Get custom font buttons
         * @return array
         */
        public static function get_custom_buttons() {
            $buttons = array();

            // Get custom buttons
            if ( have_rows( 'pip_button', 'pip_styles_tinymce' ) ) {
                while ( have_rows( 'pip_button', 'pip_styles_tinymce' ) ) {
                    the_row();

                    $label   = get_sub_field( 'label' );
                    $classes = get_sub_field( 'classes_to_apply' );

                    // Add custom button
                    $buttons[ sanitize_title( $label ) ] = [
                        'name'    => $label,
                        'classes' => $classes,
                    ];
                }
            }

            return $buttons;
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
                    $url = get_sub_field( 'url' );

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
            if ( file_exists( PIP_THEME_STYLE_PATH . 'tailwind/tailwind.min.css' ) ) {
                $stylesheets[] = PIP_THEME_STYLE_URL . 'tailwind/tailwind.min.css';
            }

            return implode( ',', $stylesheets );
        }
    }

    // Instantiate class
    new PIP_TinyMCE();
}
