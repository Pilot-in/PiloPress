<?php

if ( !class_exists( 'PIP_Styles_Settings' ) ) {
    class PIP_Styles_Settings {
        public function __construct() {
            // WP hooks
            add_action( 'acf/save_post', array( $this, 'compile_styles_settings' ), 20 );
        }

        /**
         * Compile style on Styles page save
         *
         * @param bool $force
         *
         * @return bool
         */
        public static function compile_styles_settings( $force = false ) {
            if ( !acf_is_screen( 'admin_page_styles' ) && !$force ) {
                return false;
            }

            // Compile base style for admin & front
            self::compile_bootstrap_styles();

            // Compile layouts styles
            self::compile_layouts_styles();

            return true;
        }

        /**
         * Get custom SCSS
         * @return string
         */
        public static function get_custom_scss() {
            // Get custom fonts SCSS
            $custom_scss = self::scss_custom_fonts();

            // Get custom colors SCSS
            $custom_scss .= self::scss_custom_colors();

            // Get custom CSS/SCSS
            $custom_scss .= get_field( 'pip_custom_style', 'options' );

            return $custom_scss;
        }

        /**
         * Compile bootstrap styles
         */
        private static function compile_bootstrap_styles() {
            $dirs = array();

            // Get custom SCSS
            $custom_scss = self::get_custom_scss();

            // Front-office
            $front = self::get_front_scss_code( $custom_scss );
            array_push( $dirs, array(
                'scss_dir'  => PIP_PATH . 'assets/libs/bootstrap/scss/',
                'scss_code' => $front,
                'css_dir'   => PIP_THEME_STYLE_PATH,
                'css_file'  => 'style-pilopress.css',
            ) );

            // Back-office
            $admin = self::get_admin_scss_code( $custom_scss );
            array_push( $dirs, array(
                'scss_dir'  => PIP_PATH . 'assets/scss/',
                'scss_code' => $admin,
                'css_dir'   => PIP_THEME_STYLE_PATH,
                'css_file'  => 'style-pilopress-admin.css',
            ) );

            // Compile style
            $class = new PIP_Scss_Php( array(
                'dirs'      => $dirs,
                'variables' => $custom_scss,
            ) );
            $class->compile();
        }

        /**
         * Compile layouts styles
         *
         * @param null $layout_id
         */
        public static function compile_layouts_styles( $layout_id = null ) {
            $dirs = array();

            // Get custom SCSS
            $custom_scss = self::get_custom_scss();

            if ( !$layout_id ) {
                // Layouts args
                $args = array(
                    'post_type'        => 'acf-field-group',
                    'posts_per_page'   => - 1,
                    'fields'           => 'ids',
                    'suppress_filters' => 0,
                    'post_status'      => array( 'acf-disabled' ),
                    'pip_post_content' => array(
                        'compare' => 'LIKE',
                        'value'   => 's:14:"_pip_is_layout";i:1',
                    ),
                );

                // Get layout dirs
                $posts = get_posts( $args );
            } else {
                // Use specified layout
                $posts[] = $layout_id;
            }

            if ( $posts ) {
                foreach ( $posts as $post_id ) {
                    // Get field group
                    $field_group = acf_get_field_group( $post_id );

                    // No field group, skip
                    if ( !$field_group ) {
                        continue;
                    }

                    // If no slug, skip
                    if ( !acf_maybe_get( $field_group, '_pip_layout_slug' ) ) {
                        continue;
                    }

                    // Get sanitized slug
                    $name = sanitize_title( $field_group['_pip_layout_slug'] );

                    // Paths
                    $file_path = PIP_THEME_LAYOUTS_PATH . $name . '/';

                    // No SCSS file, skip
                    if ( !acf_maybe_get( $field_group, '_pip_render_style_scss' ) ) {
                        continue;
                    }

                    // Get layout SCSS code
                    $layout_code = self::get_layout_scss_code( $custom_scss, $file_path, $field_group );

                    // Store data
                    array_push( $dirs, array(
                        'scss_dir'  => PIP_THEME_LAYOUTS_PATH . $name . '/', // For @import
                        'scss_code' => $layout_code,
                        'css_dir'   => $file_path,
                        'css_file'  => acf_maybe_get( $field_group, '_pip_render_style' ) ? $field_group['_pip_render_style'] : $name . '.css',
                    ) );
                }
            }

            // If no dirs, return
            if ( !$dirs ) {
                return;
            }

            // Compile style
            $class = new PIP_Scss_Php( array(
                'dirs'      => $dirs,
                'variables' => $custom_scss,
            ) );
            $class->compile();
        }

        /**
         * Get admin SCSS code
         *
         * @param $custom_scss
         *
         * @return false|string
         */
        private static function get_admin_scss_code( $custom_scss ) {
            ob_start(); ?>

            i.mce-i-wp_adv:before {
            content: "\f111" !important;
            }

            .-preview, body#tinymce{

            <?php echo $custom_scss; ?>

            // Import Bootstrap
            @import '../libs/bootstrap/scss/bootstrap';

            color: $body-color;
            font-family: $font-family-base;
            @include font-size($font-size-base);
            font-weight: $font-weight-base;
            line-height: $line-height-base;

            // Reset WP styles
            @import 'reset-wp';

            }

            //.mce-text[style="text-primary"]{
            //color: $primary;
            //}

            <?php return ob_get_clean();
        }

        /**
         * Get front SCSS code
         *
         * @param $custom_scss
         *
         * @return false|string
         */
        private static function get_front_scss_code( $custom_scss ) {
            ob_start();

            echo $custom_scss; ?>

            // Import Bootstrap
            @import 'bootstrap';

            <?php return ob_get_clean();
        }

        /**
         * Get layout SCSS code
         *
         * @param $custom_scss
         * @param $file_path
         * @param $field_group
         *
         * @return false|string
         */
        private static function get_layout_scss_code( $custom_scss, $file_path, $field_group ) {
            $path_to_scss_bootstrap = apply_filters( 'pip/layouts/bootstrap_path', '../../../../../..' . parse_url( PIP_URL . 'assets/libs/bootstrap/scss/', PHP_URL_PATH ) );

            // Store directory and scss code
            ob_start();

            echo $custom_scss; ?>

            // Import Bootstrap utilities
            @import '<?php echo $path_to_scss_bootstrap; ?>functions';
            @import '<?php echo $path_to_scss_bootstrap; ?>variables';
            @import '<?php echo $path_to_scss_bootstrap; ?>mixins';
            @import '<?php echo $path_to_scss_bootstrap; ?>utilities';

            <?php
            echo file_get_contents( $file_path . $field_group['_pip_render_style_scss'] );

            return ob_get_clean();
        }

        /**
         * Get SCSS to enqueue custom fonts
         *
         * @return string
         */
        private static function scss_custom_fonts() {
            $scss_custom_fonts = '';

            if ( have_rows( 'pip_fonts', 'option' ) ) {
                while ( have_rows( 'pip_fonts', 'option' ) ) {
                    the_row();

                    // If not custom font, skip
                    if ( get_row_layout() !== 'custom_font' ) {
                        continue;
                    }

                    // Get sub fields
                    $name    = get_sub_field( 'name' );
                    $files   = get_sub_field( 'files' );
                    $weight  = get_sub_field( 'weight' );
                    $style   = get_sub_field( 'style' );
                    $enqueue = get_sub_field( 'enqueue' );

                    // Auto enqueue to false
                    if ( !$enqueue ) {
                        continue;
                    }

                    // Build @font-face
                    $scss_custom_fonts .= "@font-face {\n";
                    $scss_custom_fonts .= 'font-family: "' . $name . '";' . "\n";

                    // Get URLs
                    $url = array();
                    if ( $files ) {
                        foreach ( $files as $file ) {
                            // Format file name
                            $file_name = $file['file']['url'];
                            $file_name = pathinfo( $file_name, PATHINFO_BASENAME );

                            // Get format
                            $format = strtolower( pathinfo( $file['file']['filename'], PATHINFO_EXTENSION ) );

                            // Upload dir
                            $upload_path = wp_upload_dir();

                            // Store URL
                            $url[] = 'url(' . $upload_path['url'] . '/' . $file_name . ') format("' . $format . '")';
                        }
                    }
                    // Implode URLs for src
                    $scss_custom_fonts .= 'src: ' . implode( ",\n", $url ) . ";\n";

                    // Font parameters
                    $scss_custom_fonts .= 'font-weight: ' . $weight . ";\n";
                    $scss_custom_fonts .= 'font-style: ' . $style . ";\n";

                    // End @font-face
                    $scss_custom_fonts .= "}\n";

                }
            }

            return $scss_custom_fonts;
        }

        /**
         * Get SCSS to enqueue custom colors
         * @return string
         */
        private static function scss_custom_colors() {
            $scss_custom_fonts = '';

            if ( have_rows( 'pip_colors', 'option' ) ) {
                while ( have_rows( 'pip_colors', 'option' ) ) {
                    the_row();

                    $colors = array(
                        'primary',
                        'secondary',
                        'success',
                        'danger',
                        'warning',
                        'info',
                        'light',
                        'dark',
                        'body-color',
                        'text-muted',
                        'white',
                    );

                    foreach ( $colors as $color ) {
                        $scss_custom_fonts .= '$' . $color . ': ' . get_sub_field( $color ) . ';' . "\n";
                    }
                }
            }

            return $scss_custom_fonts;
        }
    }

    // Instantiate class
    new PIP_Styles_Settings();
}