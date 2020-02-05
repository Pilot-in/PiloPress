<?php

if ( !class_exists( 'PIP_Admin' ) ) {
    class PIP_Admin {
        public function __construct() {
            // WP hooks
            add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        }

        /**
         * Enqueue admin style
         */
        public function enqueue_scripts() {
            wp_enqueue_style( 'admin-style', _PIP_URL . 'assets/pilopress-admin.css', array(), null );
        }

    }

    new PIP_Admin();
}
