<?php

if ( !class_exists( 'PIP_Tailwind' ) ) {

    /**
     * Class PIP_Tailwind
     */
    class PIP_Tailwind {

        public function __construct() {

            // Check if module is enable
            $modules = pip_get_modules();
            if ( !acf_maybe_get( $modules, 'tailwind' ) ) {
                return;
            }

            // ACF hooks
            add_action( 'acf/save_post', array( $this, 'save_styles_settings' ), 20, 1 );
            add_action( 'acf/options_page/submitbox_major_actions', array( $this, 'add_compile_styles_button' ) );
        }

        /**
         * Add Update & Compile button
         *
         * @param $page
         */
        public function add_compile_styles_button( $page ) {

            // If not on Styles admin page, return
            if ( !pip_str_starts( acf_maybe_get( $page, 'post_id' ), 'pip_styles_' ) ) {
                return;
            }

            echo '
            <div id="publishing-action">
                <input type="submit" accesskey="p" value="' . __( 'Update & Build', 'pilopress' ) . '"
                class="button button-secondary button-large" id="update_compile" name="update_compile">
			</div>
            ';
        }

        /**
         * Save styles settings
         *
         * @param $post_id
         */
        public function save_styles_settings( $post_id ) {

            // If not on Styles admin page, return
            if ( !pip_str_starts( $post_id, 'pip_styles_' ) ) {
                return;
            }

            // If assets folder doesn't exists, return
            if ( !file_exists( PIP_THEME_ASSETS_PATH ) ) {
                return;
            }

            // Update & Compile button
            $compile = acf_maybe_get_POST( 'update_compile' );
            if ( !$compile ) {
                return;
            }

            // Compile styles
            $this->compile_tailwind();
        }

        /**
         * Get CSS for TailwindCSS build
         *
         * @return string
         */
        public function get_tailwind_css() {

            $tailwind_css        = '';
            $tailwind_base       = get_field( 'pip_tailwind_style_base', 'pip_styles_tailwind_module' );
            $tailwind_components = get_field( 'pip_tailwind_style_components', 'pip_styles_tailwind_module' );
            $tailwind_utilities  = get_field( 'pip_tailwind_style_utilities', 'pip_styles_tailwind_module' );

            // Maybe add base import
            if ( $tailwind_base ) {
                $add_base_import = acf_maybe_get( $tailwind_base, 'add_base_import' );
                if ( $add_base_import ) {
                    // Base import
                    $tailwind_css .= '@import "tailwindcss/base";' . "\n";

                    // After base CSS
                    $tailwind_css .= acf_maybe_get( $tailwind_base, 'tailwind_style_after_base' ) . "\n";

                    // Custom CSS
                    $tailwind_css .= apply_filters( 'pip/tailwind/css/after_base', $tailwind_css );
                }
            }

            // Add custom fonts import
            $tailwind_css .= $this->css_custom_fonts() . "\n";

            // Custom CSS
            $tailwind_css .= apply_filters( 'pip/tailwind/css/after_fonts', $tailwind_css );

            // Maybe add components import
            if ( $tailwind_components ) {
                $add_components_import = acf_maybe_get( $tailwind_components, 'add_components_import' );
                if ( $add_components_import ) {
                    // Components import
                    $tailwind_css .= '@import "tailwindcss/components";' . "\n";

                    // After components CSS
                    $tailwind_css .= acf_maybe_get( $tailwind_components, 'tailwind_style_after_components' ) . "\n";

                    // Body classes
                    $tailwind_css .= $this->get_body_css() . "\n";

                    // Typography
                    $tailwind_css .= $this->get_typography_css() . "\n";

                    // Buttons
                    $tailwind_css .= $this->get_buttons_css() . "\n";

                    // Custom CSS
                    $tailwind_css .= apply_filters( 'pip/tailwind/css/after_components', $tailwind_css );
                }
            }

            // Maybe add utilities import
            if ( $tailwind_utilities ) {
                $add_utilities_import = acf_maybe_get( $tailwind_utilities, 'add_utilities_import' );
                if ( $add_utilities_import ) {
                    // Utilities import
                    $tailwind_css .= '@import "tailwindcss/utilities";' . "\n";

                    // After utilities CSS
                    $tailwind_css .= acf_maybe_get( $tailwind_utilities, 'tailwind_style_after_utilities' ) . "\n";

                    // Custom CSS
                    $tailwind_css .= apply_filters( 'pip/tailwind/css/after_utilities', $tailwind_css );
                }
            }

            return $tailwind_css;
        }

        /**
         * Get CSS for body
         *
         * @return string
         */
        public function get_body_css() {

            $body_css     = '';
            $body_classes = get_field( 'pip_body_classes', 'pip_styles_configuration' );

            // If not body classes, return
            if ( !$body_classes ) {
                return $body_css;
            }

            // Build body css
            $classes_to_apply = acf_maybe_get( $body_classes, 'body_classes' );
            if ( $classes_to_apply ) {
                $body_css .= "body {\n";
                $body_css .= '@apply ' . $classes_to_apply . ";\n";
                $body_css .= "}\n";
            }

            return $body_css;
        }

        /**
         * Get CSS for typography
         *
         * @return string
         */
        public function get_typography_css() {

            $typo_css = '';

            // Browse typography
            if ( have_rows( 'pip_typography', 'pip_styles_configuration' ) ) {
                while ( have_rows( 'pip_typography', 'pip_styles_configuration' ) ) {
                    the_row();

                    $class_name       = get_sub_field( 'class_name' );
                    $classes_to_apply = get_sub_field( 'classes_to_apply' );

                    // Add class
                    if ( $classes_to_apply ) {
                        $typo_css .= '.' . $class_name . " {\n";
                        $typo_css .= '@apply ' . $classes_to_apply . ";\n";
                        $typo_css .= "}\n";
                    }
                }
            }

            return $typo_css;
        }

        /**
         * Get CSS for buttons
         *
         * @return string
         */
        public function get_buttons_css() {

            $buttons_css = '';

            // Browse buttons
            if ( have_rows( 'pip_button', 'pip_styles_configuration' ) ) {
                while ( have_rows( 'pip_button', 'pip_styles_configuration' ) ) {
                    the_row();

                    $class_name       = get_sub_field( 'class_name' );
                    $classes_to_apply = get_sub_field( 'classes_to_apply' );
                    $states           = get_sub_field( 'states' );

                    // Add class
                    if ( $classes_to_apply ) {
                        $buttons_css .= '.' . $class_name . " {\n";
                        $buttons_css .= '@apply ' . $classes_to_apply . ";\n";
                        $buttons_css .= "}\n";
                    }

                    // Add states
                    if ( $states ) {
                        foreach ( $states as $state ) {
                            $type    = acf_maybe_get( $state, 'type' );
                            $classes = acf_maybe_get( $state, 'classes_to_apply' );

                            if ( $classes ) {
                                $buttons_css .= '.' . $class_name . ':' . $type . " {\n";
                                $buttons_css .= '@apply ' . $classes . ";\n";
                                $buttons_css .= "}\n";
                            }
                        }
                    }
                }
            }

            return $buttons_css;
        }

        /**
         * Get Configuration for TailwindCSS build
         *
         * @return false|mixed|string|null
         */
        public function get_tailwind_config() {

            $config          = array();
            $tailwind_config = get_field( 'pip_tailwind_config', 'pip_styles_tailwind_module' );
            $override_config = acf_maybe_get( $tailwind_config, 'override_config' );

            if ( $override_config ) {

                // If override configuration, return field content
                return acf_maybe_get( $tailwind_config, 'tailwind_config' );

            } else {

                // Screens
                $this->set_screens( $config );

                // Container
                $this->set_container_options( $config );

                // Colors
                $this->set_colors( $config );

                // Fonts
                $this->set_fonts( $config );

                // Format configuration
                $config = 'module.exports = ' . wp_json_encode( $config, JSON_PRETTY_PRINT ) . ';';
                $config = str_replace( '"', "'", $config );

                // Update configuration field
                update_field( 'pip_tailwind_config', array( 'tailwind_config' => $config ), 'pip_styles_tailwind_module' );

                return $config;
            }
        }

        /**
         * Set screens
         *
         * @param $config
         */
        public function set_screens( &$config ) {

            $screens = array();

            if ( have_rows( 'pip_screens', 'pip_styles_configuration' ) ) {
                while ( have_rows( 'pip_screens', 'pip_styles_configuration' ) ) {
                    the_row();

                    $name  = get_sub_field( 'name' );
                    $value = get_sub_field( 'value' );

                    // Add screen value
                    $screens[ $name ] = $value;
                }
            }

            // If screens, add to config
            if ( $screens ) {
                $config['theme']['screens'] = $screens;
            }
        }

        /**
         * Set container options
         *
         * @param $config
         */
        public function set_container_options( &$config ) {

            $options           = array();
            $container_options = get_field( 'pip_container', 'pip_styles_configuration' );
            if ( !$container_options ) {
                return;
            }

            // Center container
            $center_container = acf_maybe_get( $container_options, 'center_container' );
            if ( $center_container ) {
                $options['center'] = true;
            }

            // Add horizontal padding to container
            $add_padding    = acf_maybe_get( $container_options, 'add_horizontal_padding' );
            $padding_values = acf_maybe_get( $container_options, 'padding_values' );
            if ( $add_padding && $padding_values ) {
                foreach ( $padding_values as $padding_value ) {
                    $options['padding'][ $padding_value['breakpoint'] ] = $padding_value['value'];
                }
            }

            // If options, add to config
            if ( $options ) {
                $config['theme']['container'] = $options;
            }
        }

        /**
         * Set colors
         *
         * @param $config
         */
        public function set_colors( &$config ) {

            $colors = array();

            // Get simple colors
            if ( have_rows( 'pip_simple_colors', 'pip_styles_configuration' ) ) {
                while ( have_rows( 'pip_simple_colors', 'pip_styles_configuration' ) ) {
                    the_row();

                    $name  = get_sub_field( 'name' );
                    $value = get_sub_field( 'value' );

                    // Add custom style
                    $colors[ $name ] = $value;
                }
            }

            // Get colors with shades
            if ( have_rows( 'pip_colors_shades', 'pip_styles_configuration' ) ) {
                while ( have_rows( 'pip_colors_shades', 'pip_styles_configuration' ) ) {
                    the_row();

                    $color_name = get_sub_field( 'color_name' );

                    // Get shades
                    if ( have_rows( 'shades' ) ) {
                        while ( have_rows( 'shades' ) ) {
                            the_row();

                            $name  = get_sub_field( 'shade_name' );
                            $value = get_sub_field( 'value' );

                            // Add custom style
                            $colors[ $color_name ][ $name ] = $value;

                        }
                    }
                }
            }

            // Get override colors option
            $override       = false;
            $override_group = get_field( 'pip_override_colors', 'pip_styles_configuration' );
            if ( $override_group ) {
                $override = acf_maybe_get( $override_group, 'override_colors' );
            }

            // If colors, add to config
            if ( $colors ) {
                if ( $override ) {
                    $config['theme']['colors'] = $colors;
                } else {
                    $config['theme']['extend']['colors'] = $colors;
                }
            }
        }

        /**
         * Set fonts
         *
         * @param $config
         */
        public function set_fonts( &$config ) {

            $options = array();
            $fonts   = pip_get_fonts();
            if ( !$fonts ) {
                return;
            }

            // Browse fonts
            foreach ( $fonts as $font ) {

                $name       = acf_maybe_get( $font, 'name' );
                $class_name = acf_maybe_get( $font, 'class_name' );

                // Add font
                $options[ $class_name ] = $name;
            }

            // If options, add to config
            if ( $options ) {
                $config['theme']['fontFamily'] = $options;
            }
        }

        /**
         * Compile Tailwind styles
         */
        public function compile_tailwind() {

            // Get CSS
            $tailwind_style = $this->get_tailwind_css();

            // Get layouts CSS
            $tailwind_style .= pip_get_layouts_css();

            // Get config
            $tailwind_config = $this->get_tailwind_config();

            // Get Tailwind API
            require_once PIP_PATH . '/assets/libs/tailwindapi.php';
            $tailwind = new TailwindAPI();

            // Build front style
            $tailwind->build(
                array(
                    'css'          => $tailwind_style,
                    'config'       => $tailwind_config,
                    'autoprefixer' => true,
                    'minify'       => true,
                    'output'       => PIP_THEME_ASSETS_PATH . PIP_THEME_STYLE_FILENAME . '.min.css',
                )
            );

            // Build admin style
            $tailwind->build(
                array(
                    'css'          => $tailwind_style,
                    'config'       => $tailwind_config,
                    'autoprefixer' => true,
                    'minify'       => true,
                    'prefixer'     => '.-preview',
                    'output'       => PIP_THEME_ASSETS_PATH . PIP_THEME_STYLE_ADMIN_FILENAME . '.min.css',
                )
            );
        }

        /**
         * Get CSS to enqueue custom fonts
         *
         * @return string
         */
        public function css_custom_fonts() {

            $css_custom    = '';
            $tinymce_fonts = '';

            // Get fonts
            if ( have_rows( 'pip_fonts', 'pip_styles_fonts' ) ) {
                while ( have_rows( 'pip_fonts', 'pip_styles_fonts' ) ) {
                    the_row();

                    // If not custom font, skip
                    if ( get_row_layout() !== 'custom_font' ) {
                        continue;
                    }

                    // Get sub fields
                    $name   = get_sub_field( 'name' );
                    $files  = get_sub_field( 'files' );
                    $weight = get_sub_field( 'weight' );
                    $style  = get_sub_field( 'style' );

                    // Build @font-face
                    $css_custom .= "@font-face {\n";
                    $css_custom .= 'font-family: "' . $name . '";' . "\n";

                    // Get URLs
                    $url = array();
                    if ( $files ) {
                        foreach ( $files as $file ) {
                            // Get format
                            $format = strtolower( pathinfo( $file['file']['filename'], PATHINFO_EXTENSION ) );

                            // Get post
                            $posts   = new WP_Query(
                                array(
                                    'name'           => $file['file']['name'],
                                    'post_type'      => 'attachment',
                                    'posts_per_page' => 1,
                                    'fields'         => 'ids',
                                )
                            );
                            $posts   = $posts->get_posts();
                            $post_id = reset( $posts );

                            // Store URL
                            $url[] = 'url(' . wp_get_attachment_url( $post_id ) . ') format("' . $format . '")';
                        }
                    }
                    // Implode URLs for src
                    $css_custom .= 'src: ' . implode( ",\n", $url ) . ";\n";

                    // Font parameters
                    $css_custom .= 'font-weight: ' . $weight . ";\n";
                    $css_custom .= 'font-style: ' . $style . ";\n";

                    // End @font-face
                    $css_custom .= "}\n";

                }
            }

            return $css_custom . $tinymce_fonts;
        }

    }

    acf_new_instance( 'PIP_Tailwind' );

}

/**
 * Get formatted CSS
 *
 * @return mixed
 */
function pip_get_tailwind_css() {

    $tailwind = acf_get_instance( 'PIP_Tailwind' );

    return $tailwind->get_tailwind_css();
}

/**
 * Get configuration
 *
 * @return mixed
 */
function pip_get_tailwind_config() {

    $tailwind = acf_get_instance( 'PIP_Tailwind' );

    return $tailwind->get_tailwind_config();
}
