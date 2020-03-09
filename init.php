<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Get Pilo'Press path
 * @return mixed
 */
function pilopress_path() {
    return PIP_PATH;
}

/**
 * Include if file exists
 *
 * @param string $filename
 */
function pilopress_include( $filename = '' ) {
    $file_path = pilopress_path() . ltrim( $filename, '/' );
    if ( file_exists( $file_path ) ) {
        include_once( $file_path );
    }
}

/**
 * Get flexible content
 *
 * @param bool|int $post_id
 *
 * @return false|string|void
 */
function get_pip_content( $post_id = false ) {
    return get_flexible( PIP_Flexible::get_flexible_field_name(), $post_id );
}

/**
 * Return flexible content
 *
 * @param bool|int $post_id
 *
 * @return false|string|void
 */
function the_pip_content( $post_id = false ) {
    echo get_pip_content( $post_id );
}

/**
 * Check if ACF Pro and ACFE are activated
 */
add_action( 'after_plugin_row_' . PIP_BASENAME, 'pilopress_plugin_row', 5, 3 );
function pilopress_plugin_row( $plugin_file, $plugin_data, $status ) {

    // If ACF Pro and ACFE activated, return
    if ( pilopress()->has_acf() && pilopress()->has_acfe() ) {
        return;
    }

    ?>

    <style>
        .plugins tr[data-plugin='<?php echo PIP_BASENAME; ?>'] th,
        .plugins tr[data-plugin='<?php echo PIP_BASENAME; ?>'] td {
            box-shadow: none;
        }

        <?php if(isset($plugin_data['update']) && !empty($plugin_data['update'])){ ?>

        .plugins tr.pilopress-plugin-tr td {
            box-shadow: none !important;
        }

        .plugins tr.pilopress-plugin-tr .update-message {
            margin-bottom: 0;
        }

        <?php } ?>
    </style>

    <tr class="plugin-update-tr active pilopress-plugin-tr">
        <td colspan="3" class="plugin-update colspanchange">
            <div class="update-message notice inline notice-error notice-alt">
                <p><?php _e( 'Pilo\'Press requires Advanced Custom Fields PRO (minimum: 5.7.10) and ACF Extended.', 'pilopress' ); ?></p>
            </div>
        </td>
    </tr>

    <?php

}