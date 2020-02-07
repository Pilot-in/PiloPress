<?php
/*
Plugin Name: Pilo'Press
Plugin URI: www.pilot-in.com
Description: Pilo'Press: Awesome WordPress Framework
Version: 0.1
Author: Pilot'In
Author URI: www.pilot-in.com
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

defined( 'ABSPATH' ) || exit;

/**
 *  Constants
 */
if ( !defined( '_PIP_FILE' ) ) {
    define( '_PIP_FILE', __FILE__ );
}
if ( !defined( '_PIP_PATH' ) ) {
    define( '_PIP_PATH', plugin_dir_path( __FILE__ ) );
}
if ( !defined( '_PIP_URL' ) ) {
    define( '_PIP_URL', plugin_dir_url( __FILE__ ) );
}
if ( !defined( '_PIP_BASENAME' ) ) {
    define( '_PIP_BASENAME', plugin_basename( __FILE__ ) );
}
if ( !defined( '_PIP_THEME_STYLE_PATH' ) ) {
    define( '_PIP_THEME_STYLE_PATH', get_stylesheet_directory() );
}
if ( !defined( '_PIP_THEME_STYLE_URL' ) ) {
    define( '_PIP_THEME_STYLE_URL', get_stylesheet_directory_uri() );
}
if ( !defined( '_PIP_THEME_RENDER_PATH' ) ) {
    define( '_PIP_THEME_RENDER_PATH', get_stylesheet_directory() . '/pilopress/layouts/' );
}
if ( !defined( '_PIP_THEME_RENDER_URL' ) ) {
    define( '_PIP_THEME_RENDER_URL', get_stylesheet_directory_uri() . '/pilopress/layouts/' );
}

// PILO_TODO: Remove
add_action( 'init', '_pip_post_type_page_remove_supports' );
function _pip_post_type_page_remove_supports() {
    remove_post_type_support( 'page', 'editor' );
    remove_post_type_support( 'post', 'editor' );
}

// END : remove

/**
 *  Init
 */
register_activation_hook( _PIP_FILE, '_pip_activation_hook' );
function _pip_activation_hook() {
    if ( !class_exists( 'PIP_Field_Groups_Flexible_Mirror' ) ) {
        return;
    }

    $class = new PIP_Field_Groups_Flexible_Mirror();
    $class->generate_flexible_mirror();
}

/**
 * Field groups
 */
require_once( _PIP_PATH . 'includes/classes/class-field-groups-flexible.php' );
require_once( _PIP_PATH . 'includes/classes/class-field-groups-flexible-mirror.php' );
require_once( _PIP_PATH . 'includes/classes/class-field-groups-layouts.php' );

/**
 * Admin
 */
require_once( _PIP_PATH . 'includes/classes/admin/class-admin.php' );
