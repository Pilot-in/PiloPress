<?php

if ( !class_exists( 'PIP_Helpers' ) ) {
    class PIP_Helpers {

        /**
         * Check if a string begins with sub string
         *
         * @param $haystack
         * @param $needle
         *
         * @return bool
         */
        public static function starts_with( $haystack, $needle ) {
            $length = strlen( $haystack );

            return ( substr( $haystack, 0, $length ) === $needle );
        }

        /**
         * Check if a string ends with sub string
         *
         * @param $haystack
         * @param $needle
         *
         * @return bool
         */
        public static function ends_with( $haystack, $needle ) {
            $length = strlen( $needle );
            if ( $length == 0 ) {
                return true;
            }

            return ( substr( $haystack, - $length ) === $needle );
        }
    }
}
