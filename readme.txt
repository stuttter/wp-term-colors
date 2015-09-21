=== WP Term Colors ===
Contributors: johnjamesjacoby, stuttter
Tags: taxonomy, term, meta, metadata, color, colors
Requires at least: 4.2
Tested up to: 4.3
Stable tag: 0.1.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

== Description ==

Pretty colors for categories, tags, and other taxonomy terms

WP Term Colors allows users to assign colors to any visible category, tag, or taxonomy term using a fancy color picker, providing a customized look for their taxonomies.

= Dependencies =

This plugin requires [WP Term Meta](https://wordpress.org/plugins/wp-term-meta/ "Metadata, for taxonomy terms.")

= Also checkout =

* [WP Term Meta](https://wordpress.org/plugins/wp-term-meta/ "Sort taxonomy terms, your way.")
* [WP Term Authors](https://wordpress.org/plugins/wp-term-authors/ "Authors for categories, tags, and other taxonomy terms.")
* [WP Term Order](https://wordpress.org/plugins/wp-term-order/ "Sort taxonomy terms, your way.")
* [WP Term Icons](https://wordpress.org/plugins/wp-term-icons/ "Pretty icons for categories, tags, and other taxonomy terms.")
* [WP Term Visibility](https://wordpress.org/plugins/wp-term-visibility/ "Visibilities for categories, tags, and other taxonomy terms.")
* [WP User Groups](https://wordpress.org/plugins/wp-user-groups/ "Group users together with taxonomies & terms.")
* [WP Event Calendar](https://wordpress.org/plugins/wp-event-calendar/ "Flexible events, with a calendar view.")

== Screenshots ==

1. Category Colors

== Installation ==

* Download and install using the built in WordPress plugin installer.
* Activate in the "Plugins" area of your admin by clicking the "Activate" link.
* No further setup or configuration is necessary.

== Frequently Asked Questions ==

= Does this plugin depend on any others? =

Yes. Please install the [WP Term Meta](https://wordpress.org/plugins/wp-term-meta/ "Metadata, for taxonomy terms.") plugin.

= Does this create new database tables? =

No. There are no new database tables with this plugin.

= Does this modify existing database tables? =

No. All of WordPress's core database tables remain untouched.

= How do I query for terms via their colors? =

With WordPress's `get_terms()` function, the same as usual, but with an additional `meta_query` argument according the `WP_Meta_Query` specification:
http://codex.wordpress.org/Class_Reference/WP_Meta_Query

`
$terms = get_terms( 'category', array(
	'depth'      => 1,
	'number'     => 100,
	'parent'     => 0,
	'hide_empty' => false,

	// Query by color using the "wp-term-meta" plugin!
	'meta_query' => array( array(
		'key'   => 'color',
		'value' => '#c0ffee'
	) )
) );
`

= Where can I get support? =

The WordPress support forums: https://wordpress.org/support/plugin/wp-term-colors/

= Where can I find documentation? =

http://github.com/stuttter/wp-term-colors/

== Changelog ==

= 0.1.1 =
* Updated form field classes

= 0.1.0 =
* Initial release
