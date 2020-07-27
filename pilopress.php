<?php
/**
 * Plugin Name:         Pilo'Press
 * Plugin URI:          https://www.pilot-in.com
 * Description:         The most advanced WordPress Page Builder using Advanced Custom Field & TailwindCSS
 * Version:             0.3.2.8
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
        public $version = '0.3.2.8';

        /**
         * ACF
         *
         * @var bool
         */
        public $acf = false;

        /**
         * ACFE
         *
         * @var bool
         */
        public $acfe = false;

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

            // Activation actions
            register_activation_hook( __FILE__, array( $this, 'activation' ) );

            // Enqueue layouts configuration files
            add_action( 'init', array( 'PIP_Layouts', 'enqueue_configuration_files' ), 5 );

            // Init hook
            add_action( 'init', array( $this, 'load_translations' ) );

            // Meta boxes order
            add_filter( 'get_user_option_meta-box-order_acf-field-group', array( $this, 'metabox_order' ) );

            // Load
            add_action( 'acf/include_field_types', array( $this, 'load' ) );
        }

        /**
         * Init hook
         * Load translations
         */
        public function load_translations() {
            // Load text domain file
            pip_load_textdomain();
        }

        /**
         * Re-order meta-boxes
         *
         * @param $order
         *
         * @return array
         */
        public function metabox_order( $order ) {
            if ( !$order ) {
                $order = array(
                    'normal' => implode(
                        ',',
                        array(

                            // Layouts
                            'acf-field-group-fields',
                            'pip_layout_settings',
                            'acf-field-group-options',

                            // Flexible Mirror
                            'pip-flexible-layouts',
                            'acf-field-group-locations',

                        )
                    ),
                );
            }

            return $order;
        }

        /**
         * Load classes
         */
        public function load() {
            // Check if ACF Pro and ACFE are activated
            if ( !$this->has_acf() || !$this->has_acfe() ) {
                return;
            }

            // Includes
            add_action( 'acf/init', array( $this, 'includes' ) );

            // Sync JSON/PHP
            pip_include( 'includes/classes/admin/class-layouts-sync.php' );

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

            // Main
            pip_include( 'includes/classes/main/class-main.php' );
            pip_include( 'includes/classes/main/class-layouts-collections.php' );
            pip_include( 'includes/classes/main/class-layouts-categories.php' );
            pip_include( 'includes/classes/main/class-flexible.php' );
            pip_include( 'includes/classes/main/class-flexible-mirror.php' );
            pip_include( 'includes/classes/main/class-layouts.php' );

            // Pattern
            pip_include( 'includes/classes/pattern/class-pattern.php' );
            pip_include( 'includes/classes/pattern/class-flexible-header.php' );
            pip_include( 'includes/classes/pattern/class-flexible-footer.php' );
            pip_include( 'includes/classes/pattern/class-pattern-message.php' );

            // Admin
            pip_include( 'includes/classes/admin/class-admin.php' );
            pip_include( 'includes/classes/admin/class-admin-layouts.php' );
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

            // Modules
            pip_include( 'includes/classes/admin/modules/class-tinymce.php' );
            pip_include( 'includes/classes/admin/modules/class-tailwind.php' );

            // Helpers
            pip_include( 'includes/helpers.php' );
        }

        /**
         * Include tools
         */
        public function tools() {
            pip_include( 'includes/classes/admin/tools/class-styles-export-tool.php' );
            pip_include( 'includes/classes/admin/tools/class-styles-import-tool.php' );
        }

        /**
         * Activation actions
         */
        public static function activation() {
            // Create Pilo'Press folders
            self::create_pip_folder();

            // If class does not exist, return
            if ( !class_exists( 'PIP_Flexible_Mirror' ) ) {
                return;
            }

            // Generate flexible mirror field group
            $class = new PIP_Flexible_Mirror();
            $class->generate_flexible_mirror();
        }

        /**
         * Create Pilo'Press folders on activation
         */
        private static function create_pip_folder() {
            // Create "layouts" folder in theme
            wp_mkdir_p( get_template_directory() . '/pilopress/layouts' );

            // Create "assets" folder in theme
            wp_mkdir_p( get_template_directory() . '/pilopress/assets' );
        }

        /**
         * Define constants
         *
         * @param      $name
         * @param bool $value
         */
        private function define( $name, $value = true ) {
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
            $this->acf = class_exists( 'ACF' ) && defined( 'ACF_PRO' ) && defined( 'ACF_VERSION' ) && version_compare( ACF_VERSION, '5.7.13', '>=' );

            return $this->acf;
        }

        /**
         * Check if ACFE is activated
         *
         * @return bool
         */
        public function has_acfe() {
            // If ACFE already available, return
            if ( $this->acfe ) {
                return true;
            }

            // Check if ACFE activated
            $this->acfe = class_exists( 'ACFE' );

            return $this->acfe;
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
