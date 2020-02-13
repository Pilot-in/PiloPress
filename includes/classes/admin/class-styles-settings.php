<?php

if ( !class_exists( 'PIP_Styles_Settings' ) ) {
    class PIP_Styles_Settings {
        public function __construct() {
            // WP hooks
            add_action( 'acf/save_post', array( $this, 'save_styles_settings' ), 20 );
        }

        /**
         * Compile style on Styles page save
         */
        public function save_styles_settings() {
            if ( !acf_is_screen( 'admin_page_styles' ) ) {
                return;
            }

            // Get style options
            $variables = get_field( 'variables', 'options' );

            // Compile base style for admin & front
            self::compile_bootstrap_styles( $variables );

            // Compile layouts styles
            self::compile_layouts_styles( $variables );
        }

        /**
         * Compile bootstrap styles
         *
         * @param $variables
         */
        private function compile_bootstrap_styles( $variables ) {
            $dirs = array();

            // Front-office
            $front = self::get_front_scss_code( $variables );

            array_push( $dirs, array(
                'scss_dir'  => _PIP_PATH . 'assets/libs/bootstrap/scss/',
                'scss_code' => $front,
                'css_dir'   => _PIP_THEME_STYLE_PATH . '/pilopress/',
                'css_file'  => 'style-pilopress.css',
            ) );

            // Back-office
            $admin = self::get_admin_scss_code( $variables );

            array_push( $dirs, array(
                'scss_dir'  => _PIP_PATH . 'assets/scss/', // For @import
                'scss_code' => $admin,
                'css_dir'   => _PIP_THEME_STYLE_PATH . '/pilopress/',
                'css_file'  => 'style-pilopress-admin.css',
            ) );

            // Compile style
            $class = new PIP_Scss_Php( array(
                'dirs' => $dirs,
            ) );
            $class->compile();
        }

        /**
         * Compile layouts styles
         *
         * @param $variables
         */
        private static function compile_layouts_styles( $variables ) {
            $dirs = array();

            // Layouts args
            $args = array(
                'post_type'        => 'acf-field-group',
                'posts_per_page'   => - 1,
                'pip_post_content' => array(
                    'compare' => 'LIKE',
                    'value'   => 's:14:"_pip_is_layout";i:1',
                ),
                'fields'           => 'ids',
            );

            // Get layout dirs
            $query = new WP_Query( $args );
            if ( $query->have_posts() ) {
                foreach ( $query->get_posts() as $post_id ) {
                    // Get field group
                    $field_group = acf_get_field_group( $post_id );
                    if ( !$field_group ) {
                        continue;
                    }

                    // Get sanitized name
                    $name = sanitize_title( $field_group['title'] );

                    // Paths
                    $file_path = _PIP_THEME_LAYOUTS_PATH . $name . '/';

                    // No SCSS file, skip
                    if ( !acf_maybe_get( $field_group, '_pip_render_style_scss' ) ) {
                        continue;
                    }

                    $path_to_scss_bootstrap = apply_filters( 'pip/layouts/bootstrap_path', '../../../../../..' . parse_url( _PIP_URL . 'assets/libs/bootstrap/scss/', PHP_URL_PATH ) );

                    // Store directory and scss code
                    ob_start();

                    echo $variables; ?>

                    // Import Bootstrap utilities
                    @import '<?php echo $path_to_scss_bootstrap; ?>functions';
                    @import '<?php echo $path_to_scss_bootstrap; ?>variables';
                    @import '<?php echo $path_to_scss_bootstrap; ?>mixins';
                    @import '<?php echo $path_to_scss_bootstrap; ?>utilities';

                    <?php
                    echo file_get_contents( $file_path . $field_group['_pip_render_style_scss'] );
                    $layout_code = ob_get_clean();

                    array_push( $dirs, array(
                        'scss_dir'  => _PIP_THEME_LAYOUTS_PATH . $name . '/', // For @import
                        'scss_code' => $layout_code,
                        'css_dir'   => $file_path,
                        'css_file'  => acf_maybe_get( $field_group, '_pip_render_style' ) ? $field_group['_pip_render_style'] : $name . '.css',
                    ) );
                }
            }

            if ( !$dirs ) {
                return;
            }

            $class = new PIP_Scss_Php( array(
                'dirs' => $dirs,
            ) );
            $class->compile();
        }

        /**
         * Get admin SCSS code
         *
         * @param $variables
         *
         * @return false|string
         */
        private static function get_admin_scss_code( $variables ) {
            ob_start(); ?>
            .-preview {

            <?php echo $variables; ?>

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
            <?php return ob_get_clean();
        }

        /**
         * Get front SCSS code
         *
         * @param $variables
         *
         * @return false|string
         */
        private static function get_front_scss_code( $variables ) {
            ob_start();

            echo $variables; ?>

            // Import Bootstrap
            @import 'bootstrap';

            <?php return ob_get_clean();
        }
    }

    // Instantiate class
    new PIP_Styles_Settings();
}