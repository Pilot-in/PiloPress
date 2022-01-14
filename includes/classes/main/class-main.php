<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

if ( !class_exists( 'PIP_Main' ) ) {

    /**
     * Class PIP_Main
     */
    class PIP_Main {

        public function __construct() {

            // WP hooks
            add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_pip_style' ) );
            add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_pip_style' ) );
        }

        /**
         * Enqueue Pilo'Press style
         */
        public function enqueue_pip_style() {
            // AlpineJS
            $modules = pip_get_modules();
            if ( acf_maybe_get( $modules, 'alpinejs' ) || apply_filters( 'pip/alpinejs', false ) ) {
                $alpinejs_version = apply_filters( 'pip/alpinejs/version', acf_maybe_get( $modules, 'alpinejs_version' ) );
                wp_enqueue_script( 'alpine', 'https://unpkg.com/alpinejs@' . $alpinejs_version . '/dist/cdn.min.js', array(), $alpinejs_version, true );
            }

            // Styles
            if ( !apply_filters( 'pip/enqueue/remove', false ) ) {
                pip_enqueue();
            }
        }

        /**
         * Enqueue Pilo'Press admin style
         */
        public function admin_enqueue_pip_style() {

            // Allow disabling feature
            if ( apply_filters( 'pip/enqueue/admin/remove', false ) ) {
                return;
            }

            // Enqueue admin style
            pip_enqueue_admin();
        }

        /**
         * Instantiate WP_Filesystem_Base class
         *
         * @return WP_Filesystem_Base
         */
        public static function get_wp_filesystem() {
            global $wp_filesystem;

            // If wp_filesystem instantiated, return
            if ( !empty( $filesystem ) ) {
                return $wp_filesystem;
            }

            // Instantiate WP_Filesystem
            require_once ABSPATH . '/wp-admin/includes/file.php';
            WP_Filesystem();

            return $wp_filesystem;
        }

    }

    acf_new_instance( 'PIP_Main' );

}
