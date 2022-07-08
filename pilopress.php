<?php
/**
 * Plugin Name:         Pilo'Press
 * Plugin URI:          https://www.pilot-in.com
 * Description:         The most advanced WordPress Page Builder using Advanced Custom Field & TailwindCSS
 * Version:             0.4.3
 * Author:              Pilot'in
 * Author URI:          https://www.pilot-in.com
 * License:             GPLv2 or later
 * License URI:         http://www.gnu.org/licenses/gpl-2.0.html
 * Requires PHP:        5.6 or higher
 * Requires at least:   4.9 or higher
 * Text Domain:         pilopress
 * Domain Path:         /lang
 */

defined( 'ABSPATH' ) || exit;

if ( !class_exists( 'PiloPress' ) ) {

    /**
     * Class PiloPress
     */
    class PiloPress {

        /**
         * Plugin version
         *
         * @var string
         */
        public $version = '0.4.3';

        /**
         * ACF
         *
         * @var bool
         */
        public $acf = false;

        /**
         * Pilo'Press constructor.
         */
        public function __construct() {
            // Do nothing.
        }

        /**
         * Initialize plugin
         */
        public function initialize() {

            // Constants
            $this->define( 'PIP_VERSION', $this->version );
            $this->define( 'PIP_FILE', __FILE__ );
            $this->define( 'PIP_PATH', plugin_dir_path( __FILE__ ) );
            $this->define( 'PIP_URL', plugin_dir_url( __FILE__ ) );
            $this->define( 'PIP_BASENAME', plugin_basename( __FILE__ ) );
            $this->define( 'PIP_THEME_PILOPRESS_PATH', get_stylesheet_directory() . '/pilopress/' );
            $this->define( 'PIP_THEME_PILOPRESS_URL', get_stylesheet_directory_uri() . '/pilopress/' );
            $this->define( 'PIP_THEME_LAYOUTS_PATH', get_stylesheet_directory() . '/pilopress/layouts/' );
            $this->define( 'PIP_THEME_LAYOUTS_URL', get_stylesheet_directory_uri() . '/pilopress/layouts/' );
            $this->define( 'PIP_THEME_ASSETS_PATH', get_stylesheet_directory() . '/pilopress/assets/' );
            $this->define( 'PIP_THEME_ASSETS_URL', get_stylesheet_directory_uri() . '/pilopress/assets/' );
            $this->define( 'PIP_THEME_STYLE_FILENAME', 'styles' );
            $this->define( 'PIP_THEME_STYLE_ADMIN_FILENAME', 'styles-admin' );

            // Init
            include_once PIP_PATH . 'init.php';

            // Load
            add_action( 'acf/include_field_types', array( $this, 'load' ) );
        }

        /**
         * Load classes
         */
        public function load() {

            // Check if ACF Pro and ACFE are activated
            if ( !$this->has_acf() ) {
                return;
            }

            // Sync JSON/PHP
            pip_include( 'includes/classes/main/class-layouts-sync.php' );

            // Includes
            add_action( 'acf/init', array( $this, 'includes' ) );

            // Tools
            add_action( 'acf/include_admin_tools', array( $this, 'tools' ), 9 );
        }

        /**
         * Include files
         */
        public function includes() {

            // Components
            pip_include( 'includes/classes/components/class-components.php' );
            pip_include( 'includes/classes/components/class-component-field-type.php' );

            // Layouts taxonomies
            pip_include( 'includes/classes/main/class-layouts-tax-categories.php' );
            pip_include( 'includes/classes/main/class-layouts-tax-collections.php' );

            // Flexible
            pip_include( 'includes/classes/main/class-main.php' );
            pip_include( 'includes/classes/main/class-flexible.php' );
            pip_include( 'includes/classes/main/class-flexible-mirror.php' );

            // Field Groups
            pip_include( 'includes/classes/main/class-field-groups.php' );

            // Layouts
            pip_include( 'includes/classes/main/class-layouts.php' );
            pip_include( 'includes/classes/main/class-layouts-list.php' );
            pip_include( 'includes/classes/main/class-layouts-single.php' );

            // Pattern
            pip_include( 'includes/classes/pattern/class-pattern.php' );
            pip_include( 'includes/classes/pattern/class-flexible-header.php' );
            pip_include( 'includes/classes/pattern/class-flexible-footer.php' );
            pip_include( 'includes/classes/pattern/class-pattern-message.php' );

            // Admin
            pip_include( 'includes/classes/admin/class-admin.php' );
            pip_include( 'includes/classes/admin/class-options-single-meta.php' );

            // Admin - Editor
            pip_include( 'includes/classes/admin/editor/class-shortcodes.php' );
            pip_include( 'includes/classes/admin/editor/class-font-style-field.php' );
            pip_include( 'includes/classes/admin/editor/class-font-family-field.php' );
            pip_include( 'includes/classes/admin/editor/class-font-color-field.php' );
            pip_include( 'includes/classes/admin/editor/class-button-field.php' );

            // Admin - Options pages
            pip_include( 'includes/classes/admin/options-pages/class-options-pages.php' );
            pip_include( 'includes/classes/admin/options-pages/class-admin-options-page.php' );
            pip_include( 'includes/classes/admin/options-pages/styles-option-tailwind.php' );
            pip_include( 'includes/classes/admin/options-pages/styles-option-fonts.php' );
            pip_include( 'includes/classes/admin/options-pages/styles-option-image-sizes.php' );
            pip_include( 'includes/classes/admin/options-pages/styles-option-configuration.php' );
            pip_include( 'includes/classes/admin/options-pages/styles-option-modules.php' );

            // Admin - Patterns
            pip_include( 'includes/classes/admin/patterns/class-patterns.php' );
            pip_include( 'includes/classes/admin/patterns/class-default-content.php' );
            pip_include( 'includes/classes/admin/patterns/class-locked-content.php' );
            pip_include( 'includes/layouts/template-target-content/group_pip_target_content.php' );

            // Modules
            pip_include( 'includes/classes/admin/modules/class-tinymce.php' );
            pip_include( 'includes/classes/admin/modules/class-tailwind.php' );

            // Core
            pip_include( 'includes/classes/core/class-upgrades.php' );
            pip_include( 'includes/classes/core/class-settings.php' );

            // Helpers
            pip_include( 'includes/helpers.php' );

            // Cron
            pip_include( 'includes/classes/main/class-cron.php' );

        }

        /**
         * Include tools
         */
        public function tools() {

            pip_include( 'includes/classes/admin/tools/class-styles-export-tool.php' );
            pip_include( 'includes/classes/admin/tools/class-styles-import-tool.php' );
            pip_include( 'includes/classes/admin/tools/class-layouts-export-tool.php' );
            pip_include( 'includes/classes/admin/tools/class-layouts-import-tool.php' );
        }

        /**
         * Define constants
         *
         * @param      $name
         * @param bool $value
         */
        public function define( $name, $value = true ) {

            if ( !defined( $name ) ) {
                define( $name, $value );
            }

        }

        /**
         * Check if ACF Pro is activated
         *
         * @return bool
         */
        public function has_acf() {

            // If ACF already available, return
            if ( $this->acf ) {
                return true;
            }

            // Check if ACF Pro is activated
            $this->acf = class_exists( 'ACF' ) && defined( 'ACF_PRO' ) && defined( 'ACF_VERSION' ) && version_compare( ACF_VERSION, '5.8', '>=' ) && class_exists( 'ACFE' );

            return $this->acf;

        }

    }
}

/**
 * Instantiate Pilo'Press
 *
 * @return PiloPress
 */
function pilopress() {

    global $pilopress;

    if ( !isset( $pilopress ) ) {
        $pilopress = new PiloPress();
        $pilopress->initialize();
    }

    return $pilopress;

}

// Instantiate
pilopress();
