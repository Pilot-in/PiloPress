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

            $return = wp_remote_post( 'https://api.pilopress.com/api/v1/build', $post_args );

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

    }

}
