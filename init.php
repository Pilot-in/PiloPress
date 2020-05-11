<?php

if ( !defined( 'ABSPATH' ) ) {
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
        include_once( $file_path );
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
    $tailwind_css = PIP_THEME_TAILWIND_PATH . 'tailwind.min.css';

    if ( file_exists( $tailwind_css ) ) {
        wp_enqueue_style(
            'style-pilopress',
            PIP_THEME_TAILWIND_URL . 'tailwind.min.css',
            false
        );
    }
}

/**
 * Enqueue Pilo'Press admin style
 */
function pip_enqueue_admin() {
    $tailwind_admin_css = PIP_THEME_TAILWIND_PATH . 'tailwind-admin.min.css';

    if ( file_exists( $tailwind_admin_css ) ) {
        wp_enqueue_style(
            'style-pilopress-admin',
            PIP_THEME_TAILWIND_URL . 'tailwind-admin.min.css',
            false
        );
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
                <p><?php _e( 'Pilo\'Press requires Advanced Custom Fields PRO (minimum: 5.7.13) and ACF Extended.', 'pilopress' ); ?></p>
            </div>
        </td>
    </tr>

    <?php

}
