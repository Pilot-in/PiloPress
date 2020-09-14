<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

if ( !class_exists( 'PIP_Upgrades' ) ) {

    /**
     * Class PIP_Upgrades
     */
    class PIP_Upgrades {

        /**
         * PIP_Upgrades constructor.
         */
        public function __construct() {
            $option   = get_option( 'pilopress', array() );
            $upgrades = acf_maybe_get( $option, 'upgrades' );

            if ( empty( $upgrades ) ) {
                return;
            }

            // Do upgrades
            foreach ( $upgrades as $function => $upgrade ) {
                add_action( 'acf/init', array( $this, "upgrade_{$function}" ), 999 );
            }

        }

        /**
         * Styles admin refactor
         */
        public function upgrade_0_4_0() {
            $option   = get_option( 'pilopress', array() );
            $upgrades = acf_maybe_get( $option, 'upgrades' );

            // If not in upgrades to do, return
            if ( !array_key_exists( '0_4_0', $upgrades ) ) {
                return;
            }

            acf_log( "[Pilo'Press] Upgrade 0.4.0" );

            // Enable modules
            update_field( 'pip_modules_tailwind', 1, 'pip_styles_tailwind_module' );
            update_field( 'pip_modules_tinymce', 1, 'pip_styles_tailwind_module' );

            // Enable Tailwind config override
            update_field( 'pip_tailwind_config_override_config', 1, 'pip_styles_tailwind_module' );

            // Get old CSS field
            $tailwind_css = get_field( 'pip_tailwind_style', 'pip_styles_tailwind' );

            if ( $tailwind_css ) {

                // Explode "base" and "components" parts
                $split_css = explode( '@tailwind base;', acf_maybe_get( $tailwind_css, 'tailwind_style' ) );
                $split_css = explode( '@tailwind components;', acf_maybe_get( $split_css, 1 ) );

                // Store after base import CSS
                $new_css['base'] = $split_css[0];

                // Explode "utilities" part
                $split_css = explode( '@tailwind utilities;', acf_maybe_get( $split_css, 1 ) );

                // Store after imports CSS
                $new_css['components'] = acf_maybe_get( $split_css, 0 );
                $new_css['utilities']  = acf_maybe_get( $split_css, 1 );

                // Update "style after base" field
                update_field(
                    'pip_tailwind_style_base',
                    array(
                        'add_base_import'           => 1,
                        'tailwind_style_after_base' => $new_css['base'],
                    ),
                    'pip_styles_tailwind_module'
                );

                // Update "style after components" field
                update_field(
                    'pip_tailwind_style_components',
                    array(
                        'add_components_import'           => 1,
                        'tailwind_style_after_components' => $new_css['components'],
                    ),
                    'pip_styles_tailwind_module'
                );

                // Update "style after utilities" field
                update_field(
                    'pip_tailwind_style_utilities',
                    array(
                        'add_utilities_import'           => 1,
                        'tailwind_style_after_utilities' => $new_css['utilities'],
                    ),
                    'pip_styles_tailwind_module'
                );
            }

            // Typography
            $typo = get_field( 'pip_font_style', 'pip_styles_tinymce' );
            update_field( 'pip_typography', $typo, 'pip_styles_configuration' );

            // Buttons
            $buttons = get_field( 'pip_button', 'pip_styles_tinymce' );
            update_field( 'pip_button', $buttons, 'pip_styles_configuration' );

            // Compile styles
            // PIP_Tailwind::compile_tailwind();

            // Remove upgrade from to do list
            unset( $upgrades['0_4_0'] );
            $option['upgrades'] = $upgrades;
            $option['version']  = PiloPress::$version;
            update_option( 'pilopress', $option );

            acf_log( "[Pilo'Press] Upgrade 0.4.0: Done" );
        }

    }

    // Instantiate
    new PIP_Upgrades();
}
