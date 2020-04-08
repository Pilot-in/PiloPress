<?php

/**
 * Check if a string begins with sub string
 *
 * @param $haystack
 * @param $needle
 *
 * @return bool
 */
function str_starts( $haystack, $needle ) {
    $length = strlen( $needle );

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
function str_ends( $haystack, $needle ) {
    $length = strlen( $needle );
    if ( $length == 0 ) {
        return true;
    }

    return ( substr( $haystack, - $length ) === $needle );
}
