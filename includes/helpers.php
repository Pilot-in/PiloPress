<?php

/**
 * Check if a string begins with sub string
 *
 * @param $haystack
 * @param $needle
 *
 * @return bool
 */
function pip_str_starts( $haystack, $needle ) {

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
function pip_str_ends( $haystack, $needle ) {

    $length = strlen( $needle );
    if ( $length === 0 ) {
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
 */
function pip_get_formatted_post_id( $post_id = false ) {

    // If ID is specified, return
    if ( $post_id ) {
        return $post_id;
    }

    // Get current post ID
    $post_id = get_queried_object_id();

    if ( is_home() ) {

        // Blog
        $post_id = get_option( 'page_for_posts' );

    } elseif ( is_category() || is_tax() ) {

        // Custom taxonomies or category
        $post_id = 'term_' . $post_id;

    } elseif ( is_tag() ) {

        // Tags
        $post_id = 'post_tag_' . $post_id;

    } elseif ( is_post_type_archive() ) {

        // Custom post types
        if ( acf_get_options_page( get_post_type() . '-archive' ) ) {
            $post_id = get_post_type() . '_archive';
        }
    }

    return $post_id;
}

/**
 * Pilo'Press version of acf_maybe_get
 * (that also handles object type)
 *
 * @param      $data
 * @param int  $key
 * @param null $default
 *
 * @return mixed|null
 * @see acf_maybe_get()
 */
function pip_maybe_get( $data, $key = 0, $default = null ) {

    if ( is_object( $data ) ) {
        $data = (object) $data;

        return isset( $data->$key ) ? $data->$key : $default;

    } elseif ( is_array( $data ) ) {
        $data = (array) $data;

        return isset( $data[ $key ] ) ? $data[ $key ] : $default;
    }

    return $default;
}

/**
 * Multi-dimension version of array_count_values
 *
 * @param $array
 * @param $index
 *
 * @return array
 * @see array_count_values()
 */
function pip_array_count_values_assoc( $array, $index ) {

    $result = array();

    foreach ( $array as $key => $value ) {

        // Check if value is already in result array
        if ( array_key_exists( $value[ $index ], $result ) ) {

            // Increment counter
            $result[ $value[ $index ] ] ++;

        } else {

            // New entry
            $result[ $value[ $index ] ] = 1;

        }
    }

    return $result;
}
