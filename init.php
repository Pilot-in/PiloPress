<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Get Pilo'Press path
 *
 * @return mixed
 */
function pip_path() {

    return PIP_PATH;
}

/**
 * Include if file exists
 *
 * @param string $filename
 */
function pip_include( $filename = '' ) {

    $file_path = pip_path() . ltrim( $filename, '/' );
    if ( file_exists( $file_path ) ) {
        include_once $file_path;
    }
}

/**
 * Load translation
 *
 * @param string $domain
 *
 * @return bool
 */
function pip_load_textdomain( $domain = 'pilopress' ) {

    if ( ! function_exists( 'acf_get_locale' ) ) {
        return false;
    }

    $locale  = apply_filters( 'plugin_locale', acf_get_locale(), $domain );
    $mo_file = $domain . '-' . $locale . '.mo';

    // Try to load from the languages directory first.
    if ( load_textdomain( $domain, WP_LANG_DIR . '/plugins/' . $mo_file ) ) {
        return true;
    }

    // Load from plugin lang folder.
    return load_textdomain( $domain, pip_path() . 'lang/' . $mo_file );
}

/**
 * Enqueue Pilo'Press style
 */
function pip_enqueue() {

    // Theme style
    $style_path = PIP_THEME_ASSETS_PATH . PIP_THEME_STYLE_FILENAME . '.min.css';
    $style_url  = PIP_THEME_ASSETS_URL . PIP_THEME_STYLE_FILENAME . '.min.css';

    // Plugin style
    $default_path = PIP_PATH . 'assets/css/' . PIP_THEME_STYLE_FILENAME . '.min.css';
    $default_url  = PIP_URL . 'assets/css/' . PIP_THEME_STYLE_FILENAME . '.min.css';

    $css = false;
    if ( file_exists( $style_path ) ) {
        $css = $style_url;
    } elseif ( file_exists( $default_path ) ) {
        $css = $default_url;
    }

    if ( $css ) {
        wp_enqueue_style( 'style-pilopress', $css, false, pilopress()->version );
    }
}

/**
 * Enqueue Pilo'Press admin style
 */
function pip_enqueue_admin() {

    // Theme style
    $style_path = PIP_THEME_ASSETS_PATH . PIP_THEME_STYLE_ADMIN_FILENAME . '.min.css';
    $style_url  = PIP_THEME_ASSETS_URL . PIP_THEME_STYLE_ADMIN_FILENAME . '.min.css';

    // Plugin style
    $default_path = PIP_PATH . 'assets/css/' . PIP_THEME_STYLE_ADMIN_FILENAME . '.min.css';
    $default_url  = PIP_URL . 'assets/css/' . PIP_THEME_STYLE_ADMIN_FILENAME . '.min.css';

    $css = false;
    if ( file_exists( $style_path ) ) {
        $css = $style_url;
    } elseif ( file_exists( $default_path ) ) {
        $css = $default_url;
    }

    if ( $css ) {
        wp_enqueue_style( 'style-pilopress-admin', $css, false, pilopress()->version );
    }
}

/**
 * Check if ACF Pro and ACFE are activated
 */
add_action( 'after_plugin_row_' . PIP_BASENAME, 'pip_plugin_row', 5, 3 );
function pip_plugin_row( $plugin_file, $plugin_data, $status ) {

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

        <?php if ( isset( $plugin_data['update'] ) && !empty( $plugin_data['update'] ) ) : ?>

        .plugins tr.pilopress-plugin-tr td {
            box-shadow: none !important;
        }

        .plugins tr.pilopress-plugin-tr .update-message {
            margin-bottom: 0;
        }

        <?php endif; ?>
    </style>

    <tr class="plugin-update-tr active pilopress-plugin-tr">
        <td colspan="3" class="plugin-update colspanchange">
            <div class="update-message notice inline notice-error notice-alt">
                <p><?php _e( 'Pilo\'Press requires Advanced Custom Fields PRO (minimum: 5.7.13) and ACF Extended.', 'pilopress' ); ?></p>
            </div>
        </td>
    </tr>

    <?php

}
