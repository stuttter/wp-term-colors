<?php

/**
 * Plugin Name: WP Term Colors
 * Plugin URI:  https://wordpress.org/plugins/wp-term-colors/
 * Author:      John James Jacoby
 * Author URI:  https://profiles.wordpress.org/johnjamesjacoby/
 * License:     GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Description: Pretty colors for categories, tags, and other taxonomy terms
 * Version:     0.2.0
 * Text Domain: wp-term-colors
 * Domain Path: /assets/lang/
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Instantiate the main WordPress Term Colors class
 *
 * @since 0.1.0
 */
function _wp_term_colors() {

	// Setup the main file
	$plugin_path = plugin_dir_path( __FILE__ );

	// Classes
	require_once $plugin_path . '/includes/class-wp-term-meta-ui.php';
	require_once $plugin_path . '/includes/class-wp-term-colors.php';
}
add_action( 'plugins_loaded', '_wp_term_colors' );

/**
 * Initialize the main WordPress Term Color class
 *
 * @since 0.2.0
 */
function _wp_term_colors_init() {
	new WP_Term_Colors( __FILE__ );
}
add_action( 'init', '_wp_term_colors_init', 99 );
