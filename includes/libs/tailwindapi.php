<?php

if ( !class_exists( 'TailwindAPI' ) ) {

    /**
     * Class TailwindAPI
     */
    class TailwindAPI {

        /**
         * Make API call to build CSS
         *
         * @param array $args
         *
         * @return array|bool|void|WP_Error
         */
        public function build( $args = array() ) {

            $args = $this->parse_args(
                $args,
                array(
                    'css'          => '',
                    'config'       => '',
                    'safelist'     => '',
                    'autoprefixer' => true,
                    'minify'       => true,
                    'output'       => false,
                )
            );

            // CSS
            if ( !empty( $args['css'] ) ) {
                $args['css'] = $this->file_or_content( $args['css'], 'css' );
            }

            // Config
            if ( !empty( $args['config'] ) ) {
                $args['config'] = $this->file_or_content( $args['config'], 'js' );
            }

            // Safelist
            if ( !empty( $args['safelist'] ) ) {
                $args['safelist'] = $this->extract_tailwindcss_classes( $args['safelist'] );
            }

            // Autoprefixer
            $args['autoprefixer'] = boolval( $args['autoprefixer'] );

            // Minify
            $args['minify'] = boolval( $args['minify'] );

            $data = wp_json_encode( $args );

            $post_args = array(
                'body'    => $data,
                'timeout' => 60,
                'headers' => array(
                    'Content-Type'   => 'application/json',
                    'Content-Length' => strlen( $data ),
                ),
            );

            // Get PiloPress API version
            $modules          = pip_get_modules();
            $tailwind_version = acf_maybe_get( $modules, 'tailwindcss_version' );
            $tailwind_version = $tailwind_version ? intval( $tailwind_version ) : 1;

            // v1 already use TailwindCSS v2.x.x
            $api_version = $tailwind_version === 2 ? 1 : $tailwind_version;

            // Send request to the API
            $return = wp_remote_post( "https://api.pilopress.com/api/v$api_version/build", $post_args );

            // Error
            if ( is_wp_error( $return ) ) {
                set_transient( 'pip_tailwind_api_compile_error', __( 'An error occurred. Please try again later', 'pilopress' ), 45 );
                wp_safe_redirect( add_query_arg( 'error_compile', 1, acf_get_current_url() ) );
                exit();
            }

            // Error
            if ( $return['response']['code'] !== 200 ) {
                set_transient( 'pip_tailwind_api_compile_error', $return['body'], 45 );
                wp_safe_redirect( add_query_arg( 'error_compile', 1, acf_get_current_url() ) );
                exit();
            }

            // Success
            if ( (int) $return['response']['code'] === 200 ) {
                set_transient( 'pip_tailwind_api_compile_success', __( 'Styles compiled successfully.', 'pilopress' ), 45 );
            }

            // Output
            if ( !empty( $args['output'] ) && (int) $return['response']['code'] === 200 ) {
                $wp_filesystem = PIP_Main::get_wp_filesystem();
                $wp_filesystem->put_contents( $args['output'], $return['body'] );

                return true;
            }

            return $return;
        }

        /**
         * Return content or put it in file
         *
         * @param        $content
         * @param string $extension
         *
         * @return false|string
         */
        public function file_or_content( $content, $extension = 'css' ) {

            $return           = '';
            $extension_length = strlen( $extension ) + 1;

            $found_extension = substr( $content, - $extension_length );

            if ( $found_extension === '.' . $extension ) {

                if ( file_exists( $content ) ) {
                    $filesystem = PIP_Main::get_wp_filesystem();
                    $return     = $filesystem->get_contents( $content );
                }
            } else {
                $return = $content;
            }

            return $return;

        }

        /**
         * Parse arguments
         *
         * @param        $args
         * @param string $defaults
         *
         * @return array
         */
        public function parse_args( $args, $defaults = '' ) {

            if ( is_array( $args ) ) {
                $parsed_args =& $args;
            } else {
                parse_str( $args, $parsed_args );
            }

            if ( is_array( $defaults ) ) {
                return array_merge( $defaults, $parsed_args );
            }

            return $parsed_args;
        }

        /**
         * Extract potential TailwindCSS classes from array of files
         *
         * @param array $content_to_parse
         * @return string
         */
        public function extract_tailwindcss_classes( $content_to_parse ) {

            //? DEBUG PERF
            // $time_start = microtime( true );

            $potential_classes = array();

            if ( is_array( $content_to_parse ) ) {

                foreach ( $content_to_parse as $file_glob ) {

                    // TODO: If glob is too slow, might be interesting to dig "readdir" instead later: https://tutorialspage.com/benchmarking-on-the-glob-and-readdir-php-functions/
                    foreach ( glob( $file_glob, GLOB_NOSORT ) as $file_path ) {

                        $file_content = file_get_contents( $file_path, FILE_USE_INCLUDE_PATH ); // phpcs:ignore

                        // TailwindCSS purge regex to find potential classes
                        // @link https://github.com/tailwindlabs/tailwindcss/blob/c0a4980555ed44f070655b8f32d7d9d100c280f2/src/lib/defaultExtractor.js#L29
                        preg_match_all( '/[^<>"\'`\s.(){}[\]#=%$]*[^<>"\'`\s.(){}[\]#=%:$]/', $file_content, $classes_match );

                        // Grab first match of array values
                        $classes_match = (array) acf_unarray( $classes_match );

                        // Remove duplicate classes values & merge
                        $potential_classes = array_unique( array_merge( $potential_classes, $classes_match ) );

                    }
                }
            }

            // Allow 3rd party to add classes to safelist
            $potential_classes = apply_filters( 'pip/tailwind_api/safelist_classes', $potential_classes );

            // Clean data & Remove empty values
            $potential_classes = map_deep( $potential_classes, 'sanitize_text_field' );
            $potential_classes = array_filter( $potential_classes );

            if ( !$potential_classes || !is_array( $potential_classes ) ) {
                return '';
            }

            //? Count potential classes after cleaning
            // acf_log( 'DEBUG: $potential_classes', count( $potential_classes ) );

            // Make a big string of all classes
            $potential_classes_string = implode( ',', $potential_classes );

            //? DEBUG PERF
            //? Dividing with 60 will give the execution time in minutes otherwise seconds
            // $time_end = microtime( true );
            // $execution_time = round( $time_end - $time_start, 2 );
            // acf_log( 'DEBUG: $execution_time', "$execution_time seconds." );

            return $potential_classes_string;
        }

    }

}
