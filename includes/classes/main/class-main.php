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
            // Allow disabling feature
            if ( apply_filters( 'pip/enqueue/remove', false ) ) {
                return;
            }

            // Enqueue style
            pip_enqueue();
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

    }

    // Instantiate
    new PIP_Main();
}
