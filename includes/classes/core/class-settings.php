<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

if ( !class_exists( 'PIP_Settings' ) ) {

    class PIP_Settings {

        public $settings = array();

        public $upgrades = array(
            '0_4' => '0.4',
        );

        public $model = array(

            // Version
            'version'  => PIP_VERSION,

            // Upgrades
            'upgrades' => array(),
        );

        public function __construct() {
            $option = get_option( 'pilopress', array() );

            if ( !empty( $option ) ) {

                $this->version();

            } else {

                $this->reset();

            }
        }

        private function reset() {
            // Add upgrades to do
            $this->model['upgrades'] = $this->upgrades;

            // Update option
            update_option( 'pilopress', $this->model, true );

            // Do upgrades
            new PIP_upgrades();
        }

        private function version() {
            $current_version = $this->model['version'];

            // No upgrades needed
            if ( acf_version_compare( $current_version, '>=', PIP_VERSION ) ) {
                return;
            }

            // No upgrades to do
            if ( empty( $this->upgrades ) ) {
                return;
            }

            $do_upgrades = false;

            // Browse upgrades
            foreach ( $this->upgrades as $function => $version ) {

                // Compare version
                if ( acf_version_compare( $version, '<=', $current_version ) ) {
                    continue;
                }

                $do_upgrades = true;

                // Add function to execute
                $this->model['upgrades'][ $function ] = true;
            }

            // Update version
            $new_model            = $this->model;
            $new_model['version'] = PIP_VERSION;

            // Update option
            update_option( 'pilopress', $new_model, true );

            if ( $do_upgrades ) {
                new PIP_upgrades();
            }

        }

    }

    // Instantiate
    new PIP_Settings();
}
