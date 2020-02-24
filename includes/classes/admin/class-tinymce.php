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
            add_filter( 'acf/fields/wysiwyg/toolbars', array( $this, 'customize_toolbar' ) );
        }

        /**
         * Enqueue custom TinyMCE script and add variables to it
         */
        public function localize_data() {
            acf_localize_data( array(
                'custom_fonts' => self::get_custom_fonts(),
            ) );
        }

        /**
         * Get custom fonts for TinyMCE
         * @return array
         */
        private static function get_custom_fonts() {
            $fonts = array();
            if ( have_rows( 'pip_fonts', 'option' ) ) {
                while ( have_rows( 'pip_fonts', 'option' ) ) {
                    the_row();

                    // Get font name
                    $font     = get_sub_field( 'name' );
                    $tiny_mce = get_sub_field( 'tinymce' );

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
            if ( have_rows( 'pip_fonts', 'option' ) ) {
                while ( have_rows( 'pip_fonts', 'option' ) ) {
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
            if ( have_rows( 'pip_fonts', 'option' ) ) {
                while ( have_rows( 'pip_fonts', 'option' ) ) {
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
            $scripts['_pip_shortcodes'] = _PIP_URL . 'assets/js/editor.js';
            $scripts['pip_colors']      = _PIP_URL . 'assets/js/tinymce-custom-styles.js';
            $scripts['pip_fonts']       = _PIP_URL . 'assets/js/tinymce-custom-styles.js';
            $scripts['pip_styles']      = _PIP_URL . 'assets/js/tinymce-custom-styles.js';

            return $scripts;
        }
    }

    // Instantiate class
    new PIP_TinyMCE();
}