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
if ( ! defined( '_PIP_FILE' ) ) {
	define( '_PIP_FILE', __FILE__ );
}
if ( ! defined( '_PIP_PATH' ) ) {
	define( '_PIP_PATH', plugin_dir_path( __FILE__ ) );
}
if ( ! defined( '_PIP_URL' ) ) {
	define( '_PIP_URL', plugin_dir_url( __FILE__ ) );
}
if ( ! defined( '_PIP_BASENAME' ) ) {
	define( '_PIP_BASENAME', plugin_basename( __FILE__ ) );
}
if ( ! defined( '_PIP_THEME_STYLE_PATH' ) ) {
	define( '_PIP_THEME_STYLE_PATH', get_stylesheet_directory() );
}
if ( ! defined( '_PIP_THEME_STYLE_URL' ) ) {
	define( '_PIP_THEME_STYLE_URL', get_stylesheet_directory_uri() );
}
if ( ! defined( '_PIP_FLEXIBLE' ) ) {
	define( '_PIP_FLEXIBLE', '_pip_flexible' );
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
//require_once( _PIP_PATH . 'init.php' );
register_activation_hook( _PIP_FILE, '_pip_activation_hook' );
function _pip_activation_hook() {
	if ( ! class_exists( 'Flexible_Content' ) ) {
		return;
	}

	$class = new Flexible_Content();
	$class->_pip_load_edit();
}

/**
 * Flexible content
 */
require_once( _PIP_PATH . 'flexible-content/class-flexible-content.php' );


/**
 *  Core
 */
//require_once( _PIP_PATH . 'includes/core/helpers.php' );
//require_once( _PIP_PATH . 'includes/core/admin.php' );
//require_once( _PIP_PATH . 'includes/core/animation.php' );
//require_once( _PIP_PATH . 'includes/core/comments.php' );
//require_once( _PIP_PATH . 'includes/core/dashboard.php' );
//require_once( _PIP_PATH . 'includes/core/editor.php' );
//require_once( _PIP_PATH . 'includes/core/emails.php' );
//require_once( _PIP_PATH . 'includes/core/enqueue.php' );
//require_once( _PIP_PATH . 'includes/core/footer.php' );
//require_once( _PIP_PATH . 'includes/core/helpers.php' );
//require_once( _PIP_PATH . 'includes/core/homepage.php' );
//require_once( _PIP_PATH . 'includes/core/image.php' );
//require_once( _PIP_PATH . 'includes/core/login.php' );
//require_once( _PIP_PATH . 'includes/core/logo.php' );
//require_once( _PIP_PATH . 'includes/core/menu.php' );
//require_once( _PIP_PATH . 'includes/core/multisite.php' );
//require_once( _PIP_PATH . 'includes/core/pagination.php' );
//require_once( _PIP_PATH . 'includes/core/query.php' );
//require_once( _PIP_PATH . 'includes/core/scss.php' );
//require_once( _PIP_PATH . 'includes/core/search.php' );
//require_once( _PIP_PATH . 'includes/core/setup.php' );
//require_once( _PIP_PATH . 'includes/core/section.php' );
//require_once( _PIP_PATH . 'includes/core/shortcodes.php' );
//require_once( _PIP_PATH . 'includes/core/template.php' );
//require_once( _PIP_PATH . 'includes/core/thumbnail.php' );
//require_once( _PIP_PATH . 'includes/core/tracking.php' );
//require_once( _PIP_PATH . 'includes/core/widget.php' );

/**
 *  Post Types
 */
//require_once( _PIP_PATH . 'includes/post-types/page.php' );
//require_once( _PIP_PATH . 'includes/post-types/post.php' );

/**
 *  Taxonomies
 */
//require_once( _PIP_PATH . 'includes/taxonomies/category.php' );
//require_once( _PIP_PATH . 'includes/taxonomies/post_tag.php' );

/**
 *  Plugin: ACF
 */
//require_once( _PIP_PATH . 'includes/acf/init.php' );

/**
 *  Config
 */
//require_once( _PIP_PATH . 'includes/core/_config.php' );
