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

        $post_type = get_post_type() ? get_post_type() : get_queried_object()->name;

        // Custom post types
        if ( acf_get_options_page( $post_type . '-archive' ) ) {
            $post_id = $post_type . '_archive';
        }
    }

    $post_id = apply_filters( 'pip/formatted_post_id', $post_id );

    return $post_id;
}

/**
 * Pilo'Press version of acf_maybe_get
 * (that also handles object type)
 *
 * @see acf_maybe_get()
 *
 * @param int  $key
 * @param null $default
 * @param      $data
 *
 * @return mixed|null
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
 * @see array_count_values()
 *
 * @param $index
 * @param $array
 *
 * @return array
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

/**
 * TailwindCSS native colors
 *
 * @param bool $name_labels
 * @param bool $only_names
 * @param bool $name_values
 *
 * @return array|string[]
 */
function pip_get_tailwind_native_colors( $name_labels = false, $only_names = false, $name_values = false ) {
    $colors = array(
        // Gray
        'gray'   => array(
            array(
                'name'  => '50',
                'label' => __( 'Gray 50', 'pilopress' ),
                'value' => '#f9fafb',
            ),
            array(
                'name'  => '100',
                'label' => __( 'Gray 100', 'pilopress' ),
                'value' => '#f3f4f6',
            ),
            array(
                'name'  => '200',
                'label' => __( 'Gray 200', 'pilopress' ),
                'value' => '#e5e7eb',
            ),
            array(
                'name'  => '300',
                'label' => __( 'Gray 300', 'pilopress' ),
                'value' => '#d1d5db',
            ),
            array(
                'name'  => '400',
                'label' => __( 'Gray 400', 'pilopress' ),
                'value' => '#9ca3af',
            ),
            array(
                'name'  => '500',
                'label' => __( 'Gray 500', 'pilopress' ),
                'value' => '#6b7280',
            ),
            array(
                'name'  => '600',
                'label' => __( 'Gray 600', 'pilopress' ),
                'value' => '#4b5563',
            ),
            array(
                'name'  => '700',
                'label' => __( 'Gray 700', 'pilopress' ),
                'value' => '#374151',
            ),
            array(
                'name'  => '800',
                'label' => __( 'Gray 800', 'pilopress' ),
                'value' => '#1f2937',
            ),
            array(
                'name'  => '900',
                'label' => __( 'Gray 900', 'pilopress' ),
                'value' => '#111827',
            ),
        ),

        // Red
        'red'    => array(
            array(
                'name'  => '50',
                'label' => __( 'Red 50', 'pilopress' ),
                'value' => '#fef2f2',
            ),
            array(
                'name'  => '100',
                'label' => __( 'Red 100', 'pilopress' ),
                'value' => '#fee2e2',
            ),
            array(
                'name'  => '200',
                'label' => __( 'Red 200', 'pilopress' ),
                'value' => '#fecaca',
            ),
            array(
                'name'  => '300',
                'label' => __( 'Red 300', 'pilopress' ),
                'value' => '#fca5a5',
            ),
            array(
                'name'  => '400',
                'label' => __( 'Red 400', 'pilopress' ),
                'value' => '#f87171',
            ),
            array(
                'name'  => '500',
                'label' => __( 'Red 500', 'pilopress' ),
                'value' => '#ef4444',
            ),
            array(
                'name'  => '600',
                'label' => __( 'Red 600', 'pilopress' ),
                'value' => '#dc2626',
            ),
            array(
                'name'  => '700',
                'label' => __( 'Red 700', 'pilopress' ),
                'value' => '#b91c1c',
            ),
            array(
                'name'  => '800',
                'label' => __( 'Red 800', 'pilopress' ),
                'value' => '#991b1b',
            ),
            array(
                'name'  => '900',
                'label' => __( 'Red 900', 'pilopress' ),
                'value' => '#7f1d1d',
            ),
        ),

        // Yellow
        'yellow' => array(
            array(
                'name'  => '50',
                'label' => __( 'Yellow 50', 'pilopress' ),
                'value' => '#fffbeb',
            ),
            array(
                'name'  => '100',
                'label' => __( 'Yellow 100', 'pilopress' ),
                'value' => '#fef3c7',
            ),
            array(
                'name'  => '200',
                'label' => __( 'Yellow 200', 'pilopress' ),
                'value' => '#fde68a',
            ),
            array(
                'name'  => '300',
                'label' => __( 'Yellow 300', 'pilopress' ),
                'value' => '#fcd34d',
            ),
            array(
                'name'  => '400',
                'label' => __( 'Yellow 400', 'pilopress' ),
                'value' => '#fbbf24',
            ),
            array(
                'name'  => '500',
                'label' => __( 'Yellow 500', 'pilopress' ),
                'value' => '#f59e0b',
            ),
            array(
                'name'  => '600',
                'label' => __( 'Yellow 600', 'pilopress' ),
                'value' => '#d97706',
            ),
            array(
                'name'  => '700',
                'label' => __( 'Yellow 700', 'pilopress' ),
                'value' => '#b45309',
            ),
            array(
                'name'  => '800',
                'label' => __( 'Yellow 800', 'pilopress' ),
                'value' => '#92400e',
            ),
            array(
                'name'  => '900',
                'label' => __( 'Yellow 900', 'pilopress' ),
                'value' => '#78350f',
            ),
        ),

        // Green
        'green'  => array(
            array(
                'name'  => '50',
                'label' => __( 'Green 50', 'pilopress' ),
                'value' => '#ecfdf5',
            ),
            array(
                'name'  => '100',
                'label' => __( 'Green 100', 'pilopress' ),
                'value' => '#d1fae5',
            ),
            array(
                'name'  => '200',
                'label' => __( 'Green 200', 'pilopress' ),
                'value' => '#a7f3d0',
            ),
            array(
                'name'  => '300',
                'label' => __( 'Green 300', 'pilopress' ),
                'value' => '#ee7b7',
            ),
            array(
                'name'  => '400',
                'label' => __( 'Green 400', 'pilopress' ),
                'value' => '#34d399',
            ),
            array(
                'name'  => '500',
                'label' => __( 'Green 500', 'pilopress' ),
                'value' => '#10b981',
            ),
            array(
                'name'  => '600',
                'label' => __( 'Green 600', 'pilopress' ),
                'value' => '#059669',
            ),
            array(
                'name'  => '700',
                'label' => __( 'Green 700', 'pilopress' ),
                'value' => '#047857',
            ),
            array(
                'name'  => '800',
                'label' => __( 'Green 800', 'pilopress' ),
                'value' => '#065f46',
            ),
            array(
                'name'  => '900',
                'label' => __( 'Green 900', 'pilopress' ),
                'value' => '#064e3b',
            ),
        ),

        // Blue
        'blue'   => array(
            array(
                'name'  => '50',
                'label' => __( 'Blue 50', 'pilopress' ),
                'value' => '#eff6ff',
            ),
            array(
                'name'  => '100',
                'label' => __( 'Blue 100', 'pilopress' ),
                'value' => '#dbeafe',
            ),
            array(
                'name'  => '200',
                'label' => __( 'Blue 200', 'pilopress' ),
                'value' => '#bfdbfe',
            ),
            array(
                'name'  => '300',
                'label' => __( 'Blue 300', 'pilopress' ),
                'value' => '#93c5fd',
            ),
            array(
                'name'  => '400',
                'label' => __( 'Blue 400', 'pilopress' ),
                'value' => '#60a5fa',
            ),
            array(
                'name'  => '500',
                'label' => __( 'Blue 500', 'pilopress' ),
                'value' => '#3b82f6',
            ),
            array(
                'name'  => '600',
                'label' => __( 'Blue 600', 'pilopress' ),
                'value' => '#2563eb',
            ),
            array(
                'name'  => '700',
                'label' => __( 'Blue 700', 'pilopress' ),
                'value' => '#1d4ed8',
            ),
            array(
                'name'  => '800',
                'label' => __( 'Blue 800', 'pilopress' ),
                'value' => '#1e40af',
            ),
            array(
                'name'  => '900',
                'label' => __( 'Blue 900', 'pilopress' ),
                'value' => '#1e3a8a',
            ),
        ),

        // Indigo
        'indigo' => array(
            array(
                'name'  => '50',
                'label' => __( 'Indigo 50', 'pilopress' ),
                'value' => '#eef2ff',
            ),
            array(
                'name'  => '100',
                'label' => __( 'Indigo 100', 'pilopress' ),
                'value' => '#e0e7ff',
            ),
            array(
                'name'  => '200',
                'label' => __( 'Indigo 200', 'pilopress' ),
                'value' => '#c7d2fe',
            ),
            array(
                'name'  => '300',
                'label' => __( 'Indigo 300', 'pilopress' ),
                'value' => '#a5b4fc',
            ),
            array(
                'name'  => '400',
                'label' => __( 'Indigo 400', 'pilopress' ),
                'value' => '#818cf8',
            ),
            array(
                'name'  => '500',
                'label' => __( 'Indigo 500', 'pilopress' ),
                'value' => '#6366f1',
            ),
            array(
                'name'  => '600',
                'label' => __( 'Indigo 600', 'pilopress' ),
                'value' => '#4f46e5',
            ),
            array(
                'name'  => '700',
                'label' => __( 'Indigo 700', 'pilopress' ),
                'value' => '#4338ca',
            ),
            array(
                'name'  => '800',
                'label' => __( 'Indigo 800', 'pilopress' ),
                'value' => '#3730a3',
            ),
            array(
                'name'  => '900',
                'label' => __( 'Indigo 900', 'pilopress' ),
                'value' => '#312e81',
            ),
        ),

        // Purple
        'purple' => array(
            array(
                'name'  => '50',
                'label' => __( 'Purple 50', 'pilopress' ),
                'value' => '#f5f3ff',
            ),
            array(
                'name'  => '100',
                'label' => __( 'Purple 100', 'pilopress' ),
                'value' => '#ede9fe',
            ),
            array(
                'name'  => '200',
                'label' => __( 'Purple 200', 'pilopress' ),
                'value' => '#ddd6fe',
            ),
            array(
                'name'  => '300',
                'label' => __( 'Purple 300', 'pilopress' ),
                'value' => '#c4b5fd',
            ),
            array(
                'name'  => '400',
                'label' => __( 'Purple 400', 'pilopress' ),
                'value' => '#a78bfa',
            ),
            array(
                'name'  => '500',
                'label' => __( 'Purple 500', 'pilopress' ),
                'value' => '#8b5cf6',
            ),
            array(
                'name'  => '600',
                'label' => __( 'Purple 600', 'pilopress' ),
                'value' => '#7c3aed',
            ),
            array(
                'name'  => '700',
                'label' => __( 'Purple 700', 'pilopress' ),
                'value' => '#6d28d9',
            ),
            array(
                'name'  => '800',
                'label' => __( 'Purple 800', 'pilopress' ),
                'value' => '#5b21b6',
            ),
            array(
                'name'  => '900',
                'label' => __( 'Purple 900', 'pilopress' ),
                'value' => '#4c1d95',
            ),
        ),

        // Pink
        'pink'   => array(
            array(
                'name'  => '50',
                'label' => __( 'Pink 50', 'pilopress' ),
                'value' => '#fdf2f8',
            ),
            array(
                'name'  => '100',
                'label' => __( 'Pink 100', 'pilopress' ),
                'value' => '#fce7f3',
            ),
            array(
                'name'  => '200',
                'label' => __( 'Pink 200', 'pilopress' ),
                'value' => '#fbcfe8',
            ),
            array(
                'name'  => '300',
                'label' => __( 'Pink 300', 'pilopress' ),
                'value' => '#f9a8d4',
            ),
            array(
                'name'  => '400',
                'label' => __( 'Pink 400', 'pilopress' ),
                'value' => '#f472b6',
            ),
            array(
                'name'  => '500',
                'label' => __( 'Pink 500', 'pilopress' ),
                'value' => '#ec4899',
            ),
            array(
                'name'  => '600',
                'label' => __( 'Pink 600', 'pilopress' ),
                'value' => '#db2777',
            ),
            array(
                'name'  => '700',
                'label' => __( 'Pink 700', 'pilopress' ),
                'value' => '#be185d',
            ),
            array(
                'name'  => '800',
                'label' => __( 'Pink 800', 'pilopress' ),
                'value' => '#9d174d',
            ),
            array(
                'name'  => '900',
                'label' => __( 'Pink 900', 'pilopress' ),
                'value' => '#831843',
            ),
        ),
    );

    $colors = apply_filters( 'pip/tailwind/native_colors', $colors );

    // Return names only
    if ( $only_names ) {
        foreach ( $colors as $key => $shades ) {
            foreach ( $shades as $shade ) {
                $final_array[] = $key . '-' . $shade['name'];
            }
        }

        return $final_array;
    }

    // Return associative array with name => label
    if ( $name_labels ) {
        foreach ( $colors as $key => $shades ) {
            foreach ( $shades as $shade ) {
                $final_array[ $key . '-' . $shade['name'] ] = $shade['label'];
            }
        }

        return $final_array;
    }

    // Return associative array with name => value
    if ( $name_values ) {
        foreach ( $colors as $key => $shades ) {
            foreach ( $shades as $shade ) {
                $final_array[ $key . '-' . $shade['name'] ] = $shade['value'];
            }
        }

        return $final_array;
    }

    // Un-formatted array
    return $colors;
}
