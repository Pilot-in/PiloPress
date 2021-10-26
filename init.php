<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

if ( !class_exists( 'PIP_Init' ) ) {

    /**
     * Class PIP_Init
     */
    class PIP_Init {

        public function __construct() {

            // Activation
            register_activation_hook( PIP_FILE, array( $this, 'pip_activation' ) );

            // Deactivation
            register_deactivation_hook( PIP_FILE, array( $this, 'pip_deactivation' ) );

            // Hooks
            add_action( 'init', array( $this, 'load_translations' ) );
            add_action( 'after_plugin_row_' . PIP_BASENAME, array( $this, 'plugin_row' ), 5, 3 );

        }


        /**
         * Pilo'Press Activation
         */
        public function pip_activation() {
            $schedule  = 'hourly';
            $cron_name = 'pip_delete_layouts_zip';
            if ( !wp_next_scheduled( $cron_name ) ) :
                wp_schedule_event( time(), $schedule, $cron_name );
            endif;
        }

        /**
         * Pilo'Press Deactivation
         */
        public function pip_deactivation() {

            // Remove cron event
            $cron_name = 'pip_delete_layouts_zip';
            $timestamp = wp_next_scheduled( $cron_name );
            wp_unschedule_event( $timestamp, $cron_name );

        }

        /**
         * Init hook
         * Load translations
         */
        public function load_translations() {

            $domain = 'pilopress';

            $locale  = apply_filters( 'plugin_locale', get_locale(), $domain );
            $mo_file = $domain . '-' . $locale . '.mo';

            // Try to load from the languages directory first.
            if ( load_textdomain( $domain, WP_LANG_DIR . '/plugins/' . $mo_file ) ) {
                return true;
            }

            // Load from plugin lang folder.
            return load_textdomain( $domain, PIP_PATH . 'lang/' . $mo_file );

        }

        /**
         * Check if ACF Pro and ACFE are activated
         *
         * @param $plugin_file
         * @param $plugin_data
         * @param $status
         */
        public function plugin_row( $plugin_file, $plugin_data, $status ) {

            // If ACF Pro and ACFE activated, return
            if ( pilopress()->has_acf() ) {
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
                        <p><?php _e( 'Pilo\'Press requires Advanced Custom Fields PRO (minimum: 5.8) and ACF Extended.', 'pilopress' ); ?></p>
                    </div>
                </td>
            </tr>

            <?php

        }

    }

    new PIP_Init();

}

/**
 * Include if file exists
 *
 * @param string $filename
 */
function pip_include( $filename = '' ) {

    $file_path = PIP_PATH . ltrim( $filename, '/' );

    if ( file_exists( $file_path ) ) {
        include_once $file_path;
    }

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

    $css      = false;
    $css_path = pilopress()->version;
    if ( file_exists( $style_path ) ) {
        $css      = $style_url;
        $css_path = $style_path;
    } elseif ( file_exists( $default_path ) ) {
        $css      = $default_url;
        $css_path = $default_path;
    }

    if ( $css ) {
        wp_enqueue_style( 'style-pilopress', $css, false, filemtime( $css_path ) );
    }

}

/**
 * Enqueue Pilo'Press admin style
 */
function pip_enqueue_admin() {

    // Exception for ACF: Font Awesome plugin compatibility
    $current_screen = get_current_screen();
    if ( pip_maybe_get( $current_screen, 'base' ) === 'acf_page_fontawesome-settings' ) {
        return;
    }

    // Theme style
    $style_path = PIP_THEME_ASSETS_PATH . PIP_THEME_STYLE_ADMIN_FILENAME . '.min.css';
    $style_url  = PIP_THEME_ASSETS_URL . PIP_THEME_STYLE_ADMIN_FILENAME . '.min.css';

    // Plugin style
    $default_path = PIP_PATH . 'assets/css/' . PIP_THEME_STYLE_ADMIN_FILENAME . '.min.css';
    $default_url  = PIP_URL . 'assets/css/' . PIP_THEME_STYLE_ADMIN_FILENAME . '.min.css';

    $css      = false;
    $css_path = pilopress()->version;
    if ( file_exists( $style_path ) ) {
        $css      = $style_url;
        $css_path = $style_path;
    } elseif ( file_exists( $default_path ) ) {
        $css      = $default_url;
        $css_path = $default_path;
    }

    if ( $css ) {
        wp_enqueue_style( 'style-pilopress-admin', $css, false, filemtime( $css_path ) );
    }

}
