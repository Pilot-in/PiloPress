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

                // TailwindCSS v3 output a lot more error data, filter only the "reason:" message.
                preg_match_all( '/(?<=reason:\s).*?(?=\')/m', $return['body'], $api_reason_match );

                // Clean matched message
                $api_reason_match = acf_maybe_get( acf_unarray( $api_reason_match ), 1 );
                $api_reason_match = str_replace( "'", '', $api_reason_match );

                if ( !$api_reason_match ) {

                    // TailwindCSS v3 output a lot more error data, filter only the "reason:" message.
                    preg_match_all( '/(?<=reason:\s).*?(?=\")/m', $return['body'], $api_reason_match );

                    // Clean matched message
                    $api_reason_match = acf_maybe_get( acf_unarray( $api_reason_match ), 1 );
                    $api_reason_match = str_replace( '"', '', $api_reason_match );

                }

                // Filter only the "line:" message to display css to fix
                preg_match_all( '/(?<=line:\s).*?(?=,)/m', $return['body'], $api_line_match );

                // Clean css lines matched
                $api_line_match = acf_unarray( $api_line_match );
                array_pop( $api_line_match );
                $api_line_match  = end( $api_line_match );
                $css_lines       = preg_split( '/\r\n|\r|\n/', $args['css'] );
                $css_line_before = trim( acf_maybe_get( $css_lines, ( $api_line_match - 2 ) ) );
                $css_line        = trim( acf_maybe_get( $css_lines, ( $api_line_match - 1 ) ) );
                $css_line_after  = trim( acf_maybe_get( $css_lines, $api_line_match ) );

                // Output specific message or default message (if TailwindCSS v2)
                $api_response_message = $api_reason_match ?
                    wp_json_encode(
                        array(
                            '<strong>' . __( 'Error:', 'pilot-in' ) . '</strong> ' . __( 'TailwindCSS Compilation failed.', 'pilot-in' ) . PHP_EOL .
                            '<strong>' . __( 'Reason:', 'pilot-in' ) . "</strong> $api_reason_match" . PHP_EOL .
                            '<strong>' . __( 'Code', 'pilot-in' ) . '</strong> ' . '<em>(line ' . ( $api_line_match - 1 ) . ')</em>:' . PHP_EOL .
                            $css_line_before . PHP_EOL .
                            "🔴 $css_line" . PHP_EOL .
                            $css_line_after,
                        )
                    ) :
                    $return['body'];

                // Set transient to display the compile error
                set_transient( 'pip_tailwind_api_compile_error', $api_response_message, 45 );
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

                        $all_classes_match = array();

                        // TailwindCSS purge regex to find potential classes
                        // @link https://github.com/tailwindlabs/tailwindcss/blob/c0a4980555ed44f070655b8f32d7d9d100c280f2/src/lib/defaultExtractor.js#L29
                        $tw_classes_regex = array(
                            '/[^<>"\'`\s.(){}[\]#=%$]*[^<>"\'`\s.(){}[\]#=%:$]/', // INNER GLOBAL
                            // '/(?:\[\'([^\'\s]+[^<>"\'`\s:\\])\')/', // ['text-lg' -> text-lg
                            // '/(?:\["([^"\s]+[^<>"\'`\s:\\])")/', // ["text-lg" -> text-lg
                            // '/(?:\[`([^`\s]+[^<>"\'`\s:\\])`)/', // [`text-lg` -> text-lg
                            // '/([^${(<>"\'`\s]*\[\w*\'[^"`\s]*\'?\])/', // font-['some_font',sans-serif]
                            // '/([^${(<>"\'`\s]*\[\w*"[^\'`\s]*"?\])/', // font-["some_font",sans-serif]
                            // '/([^<>"\'`\s]*\[\w*\(\'[^"\'`\s]*\'\)\])/', // bg-[url('...')]
                            // '/([^<>"\'`\s]*\[\w*\("[^"\'`\s]*"\)\])/', // bg-[url("...")]
                            // '/([^<>"\'`\s]*\[\w*\(\'[^"`\s]*\'\)\])/', // bg-[url('...'),url('...')]
                            // '/([^<>"\'`\s]*\[\w*\("[^\'`\s]*"\)\])/', // bg-[url("..."),url("...")]
                            // '/([^<>"\'`\s]*\[[^<>"\'`\s]*\(\'[^"`\s]*\'\)+\])/', // h-[calc(100%-theme('spacing.1'))]
                            // '/([^<>"\'`\s]*\[[^<>"\'`\s]*\("[^\'`\s]*"\)+\])/', // h-[calc(100%-theme("spacing.1"))]
                            // '/([^${(<>"\'`\s]*\[\'[^"\'`\s]*\'\])/', // `content-['hello']` but not `content-['hello']']`
                            // '/([^${(<>"\'`\s]*\["[^"\'`\s]*"\])/', // `content-["hello"]` but not `content-["hello"]"]`
                            // '/([^<>"\'`\s]*\[[^<>"\'`\s]*:[^\]\s]*\])/', // `[attr:value]`
                            // '/([^<>"\'`\s]*\[[^<>"\'`\s]*:\'[^"\'`\s]*\'\])/', // `[content:'hello']` but not `[content:"hello"]`
                            // '/([^<>"\'`\s]*\[[^<>"\'`\s]*:"[^"\'`\s]*"\])/', // `[content:"hello"]` but not `[content:'hello']`
                            // '/([^<>"\'`\s]*\[[^"\'`\s]+\][^<>"\'`\s]*)/', // `fill-[#bada55]`, `fill-[#bada55]/50`
                            '/([^"\'`\s]*[^<>"\'`\s:])/', //  `<sm:underline`, `md>:font-bold`
                            // '/(\[^<>"\'`\s\]*\[^"\'`\s:\\])/', //  `px-1.5`, `uppercase` but not `uppercase:`
                        );

                        foreach ( $tw_classes_regex as $class_regex ) {
                            preg_match_all( $class_regex, $file_content, $classes_match );

                            // Grab first match of array values
                            $classes_match = (array) array_unique( acf_unarray( $classes_match ) );

                            // Clean matched values
                            foreach ( $classes_match as $class_index => &$class ) {

                                // Remove bad characters matched by the regex
                                $class = str_replace( '?', '', $class );
                                $class = str_replace( ';', '', $class );
                                $class = str_replace( '//', '', $class );
                                $class = str_replace( '+', '', $class );
                                $class = str_replace( '!', '', $class );
                                $class = str_replace( '\/', '/', $class );

                                // Remove line if it's not useful
                                if (
                                    !$class ||
                                    stripos( $class, '_' ) !== false || // is field key or non-useful line
                                    stripos( $class, 'row-' ) !== false // is layout var field key
                                ) {
                                    unset( $classes_match[ $class_index ] );
                                }

                            }

                            $all_classes_match = array_unique( array_merge( $all_classes_match, $classes_match ) );

                        }

                        // Remove duplicate classes values & merge
                        $potential_classes = array_unique( array_merge( $potential_classes, $all_classes_match ) );

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
