<?php

/**
 * Plugin Name: WP Term Colors
 * Plugin URI:  https://wordpress.org/plugins/wp-term-colors/
 * Author:      John James Jacoby
 * Author URI:  https://profiles.wordpress.org/johnjamesjacoby/
 * Version:     0.2.0
 * Description: Pretty colors for categories, tags, and other taxonomy terms
 * License:     GPL v2 or later
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

	// Include the main class
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

	// Allow term colors to be registered
	do_action( 'wp_register_term_colors' );

	// Instantiate the main class
	new WP_Term_Colors( __FILE__ );
}
add_action( 'init', '_wp_term_colors_init', 99 );
