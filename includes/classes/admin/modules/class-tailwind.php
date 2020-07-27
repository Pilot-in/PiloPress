<?php

if ( !class_exists( 'PIP_Tailwind' ) ) {

    /**
     * Class PIP_Tailwind
     */
    class PIP_Tailwind {
        public function __construct() {
            // Check if module is enable
            $modules = get_field( 'pip_modules', 'pip_styles_modules' );
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
            if ( !pip_str_starts( $page['post_id'], 'pip_styles_' ) ) {
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
            if ( $compile ) {

                // Save CSS
                $tailwind_style = '';
                $tailwind_css   = get_field( 'pip_tailwind_style', 'pip_styles_tailwind' );
                if ( $tailwind_css ) {

                    // Get style
                    $tailwind_style = $tailwind_css['tailwind_style'];

                    // Get include position and get custom fonts
                    $base_include_pos = strpos( $tailwind_style, '@tailwind components;' );
                    $custom_fonts     = self::css_custom_fonts() . "\n";

                    // If include position is positive and there is custom fonts
                    if ( $base_include_pos !== false && $custom_fonts ) {

                        // Insert @font-face lines
                        $tailwind_style = substr_replace( $tailwind_style, $custom_fonts, $base_include_pos, 0 );
                    }

                }

                $tailwind_config = get_field( 'pip_tailwind_config', 'pip_styles_tailwind' );

                self::compile_tailwind( $tailwind_style, $tailwind_config['tailwind_config'] );

            }
        }

        /**
         * Compile Tailwind styles
         *
         * @param $tailwind_style
         * @param $tailwind_config
         */
        public static function compile_tailwind( $tailwind_style, $tailwind_config ) {
            // Get Tailwind API
            require_once PIP_PATH . '/assets/libs/tailwindapi.php';
            $tailwind = new TailwindAPI();

            // Get style css content
            $css_content = $tailwind_style;

            // Get layouts CSS
            $css_content .= PIP_Layouts::get_layouts_css();

            // Build front style
            $tailwind->build(
                array(
                    'css'          => $css_content,
                    'config'       => $tailwind_config,
                    'autoprefixer' => true,
                    'minify'       => true,
                    'output'       => PIP_THEME_ASSETS_PATH . PIP_THEME_STYLE_FILENAME . '.min.css',
                )
            );

            // Reset WP styles
            $admin_css = "#poststuff .-preview h2{ all:unset; }\n";

            // Build admin style
            $tailwind->build(
                array(
                    'css'          => $admin_css . $css_content,
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
        private static function css_custom_fonts() {
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

    // Instantiate class
    new PIP_Tailwind();
}
