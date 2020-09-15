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

            pip_include( 'includes/classes/admin/options-pages/old/styles-option-tailwind.php' );
            pip_include( 'includes/classes/admin/options-pages/old/styles-option-tinymce.php' );

            acf_log( "[Pilo'Press] Upgrade 0.4.0" );

            // Enable modules
            update_field(
                'pip_modules',
                array(
                    'tailwind' => true,
                    'tinymce'  => true,
                ),
                'pip_styles_modules'
            );

            // Enable Tailwind config override
            update_field(
                'pip_tailwind_config',
                array(
                    'override_config' => true,
                    'tailwind_config' => get_field( 'pip_tailwind_config_tailwind_config', 'pip_styles_tailwind' ),
                ),
                'pip_styles_tailwind_module'
            );

            // Get old CSS field
            $tailwind_css = get_field( 'pip_tailwind_style_tailwind_style', 'pip_styles_tailwind' );
            if ( $tailwind_css ) {

                // Explode "base" and "components" parts
                $split_css = explode( '@tailwind base;', $tailwind_css );
                $split_css = explode( '@tailwind components;', acf_maybe_get( $split_css, 1 ) );

                // Store "base" part
                $new_css['base'] = acf_maybe_get( $split_css, 0 );

                // Explode "utilities" part
                $split_css = explode( '@tailwind utilities;', acf_maybe_get( $split_css, 1 ) );

                // Store "components" and "utilities" parts
                $new_css['components'] = acf_maybe_get( $split_css, 0 );
                $new_css['utilities']  = acf_maybe_get( $split_css, 1 );

                // Update base fields
                update_field(
                    'pip_tailwind_style_base',
                    array(
                        'add_base_import'           => true,
                        'tailwind_style_after_base' => $new_css['base'],
                    ),
                    'pip_styles_tailwind_module'
                );

                // Update components fields
                update_field(
                    'pip_tailwind_style_components',
                    array(
                        'add_components_import'           => true,
                        'tailwind_style_after_components' => $new_css['components'],
                    ),
                    'pip_styles_tailwind_module'
                );

                // Update utilities fields
                update_field(
                    'pip_tailwind_style_utilities',
                    array(
                        'add_utilities_import'           => true,
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
            if ( is_array( $buttons ) ) {
                foreach ( $buttons as $key => $button ) {
                    $button['class_name']       = $button['classes_to_apply'];
                    $button['classes_to_apply'] = '';

                    $buttons[ $key ] = $button;
                }
            }
            update_field( 'pip_button', $buttons, 'pip_styles_configuration' );

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
