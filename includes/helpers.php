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

/**
 * Get formatted post ID
 *
 * @param bool $post_id
 *
 * @return bool|int|mixed|string|void
 * @example return term_6 for term ID
 *
 */
function get_formatted_post_id( $post_id = false ) {
    // Get current post ID
    $post_id = $post_id ? $post_id : get_queried_object_id();

    if ( is_home() ) {

        // Blog
        $post_id = get_option( 'page_for_posts' );

    } elseif ( is_category() ) {

        // Category
        global $cat;
        $post_id = 'term_' . $cat;

    } elseif ( is_tag() ) {

        // Tags
        $post_id = 'post_tag_' . $post_id;

    } elseif ( is_tax() ) {

        // Custom taxonomies
        $post_id = 'term_' . $post_id;

    }

    return $post_id;
}
