<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

if ( !class_exists( 'PIP_Upgrades' ) ) {

    class PIP_Upgrades {

        public function __construct() {
            $option   = get_option( 'pilopress', array() );
            $upgrades = acf_maybe_get( $option, 'upgrades' );

            if ( empty( $upgrades ) ) {
                return;
            }

            add_action( 'acf/init', array( $this, 'upgrade_0_4' ), 999 );

        }

        public function upgrade_0_4() {
            $option   = get_option( 'pilopress', array() );
            $upgrades = acf_maybe_get( $option, 'upgrades' );

            if ( !array_key_exists( '0_4', $upgrades ) ) {
                return;
            }

            // Do something
        }

    }

    // Instantiate
    new PIP_Upgrades();
}
