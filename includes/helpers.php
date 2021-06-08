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
                'name'  => '100',
                'label' => 'Gray 100',
                'value' => '#F7FAFC',
            ),
            array(
                'name'  => '200',
                'label' => 'Gray 200',
                'value' => '#EDF2F7',
            ),
            array(
                'name'  => '300',
                'label' => 'Gray 300',
                'value' => '#E2E8F0',
            ),
            array(
                'name'  => '400',
                'label' => 'Gray 400',
                'value' => '#CBD5E0',
            ),
            array(
                'name'  => '500',
                'label' => 'Gray 500',
                'value' => '#A0AEC0',
            ),
            array(
                'name'  => '600',
                'label' => 'Gray 600',
                'value' => '#718096',
            ),
            array(
                'name'  => '700',
                'label' => 'Gray 700',
                'value' => '#4A5568',
            ),
            array(
                'name'  => '800',
                'label' => 'Gray 800',
                'value' => '#2D3748',
            ),
            array(
                'name'  => '900',
                'label' => 'Gray 900',
                'value' => '#1A202C',
            ),
        ),

        // Red
        'red'    => array(
            array(
                'name'  => '100',
                'label' => 'Red 100',
                'value' => '#FFF5F5',
            ),
            array(
                'name'  => '200',
                'label' => 'Red 200',
                'value' => '#FED7D7',
            ),
            array(
                'name'  => '300',
                'label' => 'Red 300',
                'value' => '#FEB2B2',
            ),
            array(
                'name'  => '400',
                'label' => 'Red 400',
                'value' => '#FC8181',
            ),
            array(
                'name'  => '500',
                'label' => 'Red 500',
                'value' => '#F56565',
            ),
            array(
                'name'  => '600',
                'label' => 'Red 600',
                'value' => '#E53E3E',
            ),
            array(
                'name'  => '700',
                'label' => 'Red 700',
                'value' => '#C53030',
            ),
            array(
                'name'  => '800',
                'label' => 'Red 800',
                'value' => '#9B2C2C',
            ),
            array(
                'name'  => '900',
                'label' => 'Red 900',
                'value' => '#742A2A',
            ),
        ),

        // Orange
        'orange' => array(
            array(
                'name'  => '100',
                'label' => 'Orange 100',
                'value' => '#FFFAF0',
            ),
            array(
                'name'  => '200',
                'label' => 'Orange 200',
                'value' => '#FEEBC8',
            ),
            array(
                'name'  => '300',
                'label' => 'Orange 300',
                'value' => '#FBD38D',
            ),
            array(
                'name'  => '400',
                'label' => 'Orange 400',
                'value' => '#F6AD55',
            ),
            array(
                'name'  => '500',
                'label' => 'Orange 500',
                'value' => '#ED8936',
            ),
            array(
                'name'  => '600',
                'label' => 'Orange 600',
                'value' => '#DD6B20',
            ),
            array(
                'name'  => '700',
                'label' => 'Orange 700',
                'value' => '#C05621',
            ),
            array(
                'name'  => '800',
                'label' => 'Orange 800',
                'value' => '#9C4221',
            ),
            array(
                'name'  => '900',
                'label' => 'Orange 900',
                'value' => '#7B341E',
            ),
        ),

        // Yellow
        'yellow' => array(
            array(
                'name'  => '100',
                'label' => 'Yellow 100',
                'value' => '#FFFFF0',
            ),
            array(
                'name'  => '200',
                'label' => 'Yellow 200',
                'value' => '#FEFCBF',
            ),
            array(
                'name'  => '300',
                'label' => 'Yellow 300',
                'value' => '#FAF089',
            ),
            array(
                'name'  => '400',
                'label' => 'Yellow 400',
                'value' => '#F6E05E',
            ),
            array(
                'name'  => '500',
                'label' => 'Yellow 500',
                'value' => '#ECC94B',
            ),
            array(
                'name'  => '600',
                'label' => 'Yellow 600',
                'value' => '#D69E2E',
            ),
            array(
                'name'  => '700',
                'label' => 'Yellow 700',
                'value' => '#B7791F',
            ),
            array(
                'name'  => '800',
                'label' => 'Yellow 800',
                'value' => '#975A16',
            ),
            array(
                'name'  => '900',
                'label' => 'Yellow 900',
                'value' => '#744210',
            ),
        ),

        // Green
        'green'  => array(
            array(
                'name'  => '100',
                'label' => 'Green 100',
                'value' => '#F0FFF4',
            ),
            array(
                'name'  => '200',
                'label' => 'Green 200',
                'value' => '#C6F6D5',
            ),
            array(
                'name'  => '300',
                'label' => 'Green 300',
                'value' => '#9AE6B4',
            ),
            array(
                'name'  => '400',
                'label' => 'Green 400',
                'value' => '#68D391',
            ),
            array(
                'name'  => '500',
                'label' => 'Green 500',
                'value' => '#48BB78',
            ),
            array(
                'name'  => '600',
                'label' => 'Green 600',
                'value' => '#38A169',
            ),
            array(
                'name'  => '700',
                'label' => 'Green 700',
                'value' => '#2F855A',
            ),
            array(
                'name'  => '800',
                'label' => 'Green 800',
                'value' => '#276749',
            ),
            array(
                'name'  => '900',
                'label' => 'Green 900',
                'value' => '#22543D',
            ),
        ),

        // Teal
        'teal'   => array(
            array(
                'name'  => '100',
                'label' => 'Teal 100',
                'value' => '#E6FFFA',
            ),
            array(
                'name'  => '200',
                'label' => 'Teal 200',
                'value' => '#B2F5EA',
            ),
            array(
                'name'  => '300',
                'label' => 'Teal 300',
                'value' => '#81E6D9',
            ),
            array(
                'name'  => '400',
                'label' => 'Teal 400',
                'value' => '#4FD1C5',
            ),
            array(
                'name'  => '500',
                'label' => 'Teal 500',
                'value' => '#38B2AC',
            ),
            array(
                'name'  => '600',
                'label' => 'Teal 600',
                'value' => '#319795',
            ),
            array(
                'name'  => '700',
                'label' => 'Teal 700',
                'value' => '#2C7A7B',
            ),
            array(
                'name'  => '800',
                'label' => 'Teal 800',
                'value' => '#285E61',
            ),
            array(
                'name'  => '900',
                'label' => 'Teal 900',
                'value' => '#234E52',
            ),
        ),

        // Blue
        'blue'   => array(
            array(
                'name'  => '100',
                'label' => 'Blue 100',
                'value' => '#EBF8FF',
            ),
            array(
                'name'  => '200',
                'label' => 'Blue 200',
                'value' => '#BEE3F8',
            ),
            array(
                'name'  => '300',
                'label' => 'Blue 300',
                'value' => '#90CDF4',
            ),
            array(
                'name'  => '400',
                'label' => 'Blue 400',
                'value' => '#63B3ED',
            ),
            array(
                'name'  => '500',
                'label' => 'Blue 500',
                'value' => '#4299E1',
            ),
            array(
                'name'  => '600',
                'label' => 'Blue 600',
                'value' => '#3182CE',
            ),
            array(
                'name'  => '700',
                'label' => 'Blue 700',
                'value' => '#2B6CB0',
            ),
            array(
                'name'  => '800',
                'label' => 'Blue 800',
                'value' => '#2C5282',
            ),
            array(
                'name'  => '900',
                'label' => 'Blue 900',
                'value' => '#2A4365',
            ),
        ),

        // Indigo
        'indigo' => array(
            array(
                'name'  => '100',
                'label' => 'Indigo 100',
                'value' => '#EBF4FF',
            ),
            array(
                'name'  => '200',
                'label' => 'Indigo 200',
                'value' => '#C3DAFE',
            ),
            array(
                'name'  => '300',
                'label' => 'Indigo 300',
                'value' => '#A3BFFA',
            ),
            array(
                'name'  => '400',
                'label' => 'Indigo 400',
                'value' => '#7F9CF5',
            ),
            array(
                'name'  => '500',
                'label' => 'Indigo 500',
                'value' => '#667EEA',
            ),
            array(
                'name'  => '600',
                'label' => 'Indigo 600',
                'value' => '#5A67D8',
            ),
            array(
                'name'  => '700',
                'label' => 'Indigo 700',
                'value' => '#4C51BF',
            ),
            array(
                'name'  => '800',
                'label' => 'Indigo 800',
                'value' => '#434190',
            ),
            array(
                'name'  => '900',
                'label' => 'Indigo 900',
                'value' => '#3C366B',
            ),
        ),

        // Purple
        'purple' => array(
            array(
                'name'  => '100',
                'label' => 'Purple 100',
                'value' => '#FAF5FF',
            ),
            array(
                'name'  => '200',
                'label' => 'Purple 200',
                'value' => '#E9D8FD',
            ),
            array(
                'name'  => '300',
                'label' => 'Purple 300',
                'value' => '#D6BCFA',
            ),
            array(
                'name'  => '400',
                'label' => 'Purple 400',
                'value' => '#B794F4',
            ),
            array(
                'name'  => '500',
                'label' => 'Purple 500',
                'value' => '#9F7AEA',
            ),
            array(
                'name'  => '600',
                'label' => 'Purple 600',
                'value' => '#805AD5',
            ),
            array(
                'name'  => '700',
                'label' => 'Purple 700',
                'value' => '#6B46C1',
            ),
            array(
                'name'  => '800',
                'label' => 'Purple 800',
                'value' => '#553C9A',
            ),
            array(
                'name'  => '900',
                'label' => 'Purple 900',
                'value' => '#44337A',
            ),
        ),

        // Pink
        'pink'   => array(
            array(
                'name'  => '100',
                'label' => 'Pink 100',
                'value' => '#FFF5F7',
            ),
            array(
                'name'  => '200',
                'label' => 'Pink 200',
                'value' => '#FED7E2',
            ),
            array(
                'name'  => '300',
                'label' => 'Pink 300',
                'value' => '#FBB6CE',
            ),
            array(
                'name'  => '400',
                'label' => 'Pink 400',
                'value' => '#F687B3',
            ),
            array(
                'name'  => '500',
                'label' => 'Pink 500',
                'value' => '#ED64A6',
            ),
            array(
                'name'  => '600',
                'label' => 'Pink 600',
                'value' => '#D53F8C',
            ),
            array(
                'name'  => '700',
                'label' => 'Pink 700',
                'value' => '#B83280',
            ),
            array(
                'name'  => '800',
                'label' => 'Pink 800',
                'value' => '#97266D',
            ),
            array(
                'name'  => '900',
                'label' => 'Pink 900',
                'value' => '#702459',
            ),
        ),
    );

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
