<?php

if ( !class_exists( 'TailwindAPI' ) ) {
    class TailwindAPI {

        /**
         * Make API call to build CSS
         *
         * @param array $args
         *
         * @return bool|string
         */
        public function build( $args = array() ) {
            $args = $this->parse_args( $args, array(
                'css'          => '',
                'config'       => '',
                'autoprefixer' => true,
                'minify'       => true,
                'output'       => false,
            ) );

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

            $data = json_encode( $args );

            $curl = curl_init( 'https://www.tailwindapi.com/api/v1/build' );

            curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
            curl_setopt( $curl, CURLINFO_HEADER_OUT, true );
            curl_setopt( $curl, CURLOPT_POST, true );
            curl_setopt( $curl, CURLOPT_POSTFIELDS, $data );

            curl_setopt( $curl, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen( $data ),
                )
            );

            $return = curl_exec( $curl );

            curl_close( $curl );

            // Output
            if ( !empty( $args['output'] ) ) {
                file_put_contents( $args['output'], $return );

                return true;
            }

            return $return;
        }

        /**
         * @param $content
         * @param string $extension
         *
         * @return false|string
         */
        private function file_or_content( $content, $extension = 'css' ) {

            $return           = '';
            $extension_length = strlen( $extension ) + 1;

            $found_extension = substr( $content, - $extension_length );

            if ( $found_extension === '.' . $extension ) {

                if ( file_exists( $content ) ) {
                    $return = file_get_contents( $content );
                }

            } else {
                $return = $content;
            }

            return $return;

        }

        /**
         * Parse arguments
         *
         * @param $args
         * @param string $defaults
         *
         * @return array
         */
        private function parse_args( $args, $defaults = '' ) {

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
