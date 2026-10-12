<?php

/**
 * Focused checks for loading translations before translated strings are used.
 */

define( 'ABSPATH', dirname( __DIR__ ) . '/' );

$wp_term_colors_actions = array();
$wp_term_colors_events  = array();

function add_action( $hook, $callback, $priority = 10 ) {
	global $wp_term_colors_actions;

	$wp_term_colors_actions[] = array( $hook, $callback, $priority );
}

function plugin_basename( $file ) {
	return 'wp-term-colors/' . basename( $file );
}

function load_plugin_textdomain( $domain, $deprecated = false, $plugin_rel_path = false ) {
	global $wp_term_colors_events;

	$wp_term_colors_events[] = array( 'load', $domain, $deprecated, $plugin_rel_path );

	return true;
}

class WP_Term_Colors {
	public function __construct( $file ) {
		global $wp_term_colors_events;

		$wp_term_colors_events[] = array( 'construct', basename( $file ) );
	}
}

require dirname( __DIR__ ) . '/wp-term-colors.php';

if ( ! in_array( array( 'init', '_wp_term_colors_init', 99 ), $wp_term_colors_actions, true ) ) {
	throw new RuntimeException( 'The plugin initializer must remain attached to init.' );
}

_wp_term_colors_init();

$expected = array(
	array( 'load', 'wp-term-colors', false, 'wp-term-colors/assets/lang' ),
	array( 'construct', 'wp-term-colors.php' ),
);

if ( $expected !== $wp_term_colors_events ) {
	throw new RuntimeException( 'Translations must load before the plugin constructs translated labels.' );
}

echo "Text domain loading checks passed.\n";
