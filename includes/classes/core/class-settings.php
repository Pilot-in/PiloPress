<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

if ( !class_exists( 'PIP_Settings' ) ) {

    /**
     * Class PIP_Settings
     */
    class PIP_Settings {

        /**
         * Pilo'Press settings
         *
         * @var array
         */
        public $settings = array();

        /**
         * Upgrades scripts
         *
         * @var string[]
         */
        public $upgrades = array(
            '0_4_0' => '0.4.0',
        );

        /**
         * Pilo'Press settings model
         *
         * @var array
         */
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

        public function reset() {

            // Add upgrades to do
            $this->model['upgrades'] = $this->upgrades;

            // Update option
            update_option( 'pilopress', $this->model, true );

            acf_new_instance( 'PIP_Upgrades' );

        }

        public function version() {

            $current_version = $this->model['version'];

            // No upgrades needed
            if ( acf_version_compare( $current_version, '>=', pilopress()->version ) ) {
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
            $new_model['version'] = pilopress()->version;

            // Update option
            update_option( 'pilopress', $new_model, true );

            if ( $do_upgrades ) {
                acf_new_instance( 'PIP_upgrades' );
            }

        }

    }

    acf_new_instance( 'PIP_Settings' );

}
