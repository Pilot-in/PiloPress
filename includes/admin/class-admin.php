<?php

if ( ! class_exists( 'PIP_Admin' ) ) {
	class PIP_Admin {
		public function __construct() {
			add_action( 'admin_enqueue_scripts', array( $this, '_pip_enqueue_scripts' ) );
		}

		public function _pip_enqueue_scripts() {
			wp_enqueue_style( 'admin-style', _PIP_URL . 'assets/pilopress-admin.css', array(), null );
		}

	}

	new PIP_Admin();
}
