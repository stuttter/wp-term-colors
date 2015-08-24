<?php

/**
 * Plugin Name: WP Term Colors
 * Plugin URI:  https://wordpress.org/plugins/wp-term-colors/
 * Description: Pretty colors for categories, tags, and other taxonomy terms
 * Author:      John James Jacoby
 * Version:     0.1.0
 * Author URI:  https://profiles.wordpress.org/johnjamesjacoby/
 * License:     GPL v2 or later
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WP_Term_Colors' ) ) :
/**
 * Main WP Term Colors class
 *
 * @link https://make.wordpress.org/core/2013/07/28/potential-roadmap-for-taxonomy-meta-and-post-relationships/ Taxonomy Roadmap
 *
 * @since 0.1.0
 */
final class WP_Term_Colors {

	/**
	 * @var string Plugin version
	 */
	public $version = '0.1.0';

	/**
	 * @var string Database version
	 */
	public $db_version = 201508240001;

	/**
	 * @var string Database version
	 */
	public $db_version_key = 'wpdb_term_color_version';

	/**
	 * @var string File for plugin
	 */
	public $file = '';

	/**
	 * @var string URL to plugin
	 */
	public $url = '';

	/**
	 * @var string Path to plugin
	 */
	public $path = '';

	/**
	 * @var string Basename for plugin
	 */
	public $basename = '';

	/**
	 * @var boo Whether to use fancy coloring
	 */
	public $fancy = false;

	/**
	 * Hook into queries, admin screens, and more!
	 *
	 * @since 0.1.0
	 */
	public function __construct() {

		// Setup plugin
		$this->file     = __FILE__;
		$this->url      = plugin_dir_url( $this->file );
		$this->path     = plugin_dir_path( $this->file );
		$this->basename = plugin_basename( $this->file );
		$this->fancy    = apply_filters( 'wp_fancy_term_color', true );

		// Queries
		add_action( 'create_term', array( $this, 'add_term_color' ), 10, 2 );
		add_action( 'edit_term',   array( $this, 'add_term_color' ), 10, 2 );

		// Get visible taxonomies
		$taxonomies = $this->get_taxonomies();

		// Always hook these in, for ajax actions
		foreach ( $taxonomies as $value ) {

			// Unfancy gets the column
			add_filter( "manage_edit-{$value}_columns",          array( $this, 'add_column_header' ) );
			add_filter( "manage_{$value}_custom_column",         array( $this, 'add_column_value'  ), 10, 3 );
			add_filter( "manage_edit-{$value}_sortable_columns", array( $this, 'sortable_columns'  ) );

			add_action( "{$value}_add_form_fields",  array( $this, 'term_color_add_form_field'  ) );
			add_action( "{$value}_edit_form_fields", array( $this, 'term_color_edit_form_field' ) );
		}

		// @todo ajax actions
		//add_action( 'wp_ajax_recoloring_terms', array( $this, 'ajax_recoloring_terms' ) );

		// Only blog admin screens
		if ( is_blog_admin() || doing_action( 'wp_ajax_inline_save_tax' ) ) {
			add_action( 'admin_init',         array( $this, 'admin_init' ) );
			add_action( 'load-edit-tags.php', array( $this, 'edit_tags'  ) );
		}
	}

	/**
	 * Administration area hooks
	 *
	 * @since 0.1.0
	 */
	public function admin_init() {

		// Check for DB update
		$this->maybe_upgrade_database();
	}

	/**
	 * Administration area hooks
	 *
	 * @since 0.1.0
	 */
	public function edit_tags() {

		// Enqueue javascript
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'admin_head',            array( $this, 'admin_head'      ) );

		// Quick edit
		add_action( 'quick_edit_custom_box', array( $this, 'quick_edit_term_color' ), 10, 3 );
	}

	/** Assets ****************************************************************/

	/**
	 * Enqueue quick-edit JS
	 *
	 * @since 0.1.0
	 */
	public function enqueue_scripts() {

		// Enqueue the color picker
		wp_enqueue_script( 'wp-color-picker' );
		wp_enqueue_style( 'wp-color-picker' );

		// Enqueue fancy coloring; includes quick-edit
		wp_enqueue_script( 'term-color', $this->url . 'js/term-color.js', array( 'wp-color-picker' ), $this->db_version, true );
	}

	/**
	 * Align custom `color` column
	 *
	 * @since 0.1.0
	 */
	public function admin_head() {

		// Add the help tab
		get_current_screen()->add_help_tab(array(
			'id'      => 'wp_term_color_help_tab',
			'title'   => __( 'Term Color', 'wp-term-colors' ),
			'content' => '<p>' . __( 'Terms can have unique colors to help separate them from each other.', 'wp-term-colors' ) . '</p>',
		) ); ?>

		<style type="text/css">
			.column-color {
				width: 74px;
			}
			.term-color {
				height: 25px;
				width: 25px;
				display: inline-block;
				border: 2px solid #eee;
				border-radius: 100%;
			}
		</style>

		<?php
	}

	/**
	 * Return the taxonomies used by this plugin
	 *
	 * @since 0.1.0
	 *
	 * @param array $args
	 * @return array
	 */
	private static function get_taxonomies( $args = array() ) {

		// Parse arguments
		$r = wp_parse_args( $args, array(
			'show_ui' => true
		) );

		// Get & return the taxonomies
		return get_taxonomies( $r );
	}

	/** Columns ***************************************************************/

	/**
	 * Add the "Color" column to taxonomy terms list-tables
	 *
	 * @since 0.1.0
	 *
	 * @param array $columns
	 *
	 * @return array
	 */
	public function add_column_header( $columns = array() ) {
		$columns['color'] = __( 'Color', 'term-color' );

		return $columns;
	}

	/**
	 * Output the value for the custom column, in our case: `color`
	 *
	 * @since 0.1.0
	 *
	 * @param string $empty
	 * @param string $custom_column
	 * @param int    $term_id
	 *
	 * @return mixed
	 */
	public function add_column_value( $empty = '', $custom_column = '', $term_id = 0 ) {

		// Bail if no taxonomy passed or not on the `color` column
		if ( empty( $_REQUEST['taxonomy'] ) || ( 'color' !== $custom_column ) || ! empty( $empty ) ) {
			return;
		}

		// Get the hex color
		$color  = $this->get_term_color( $term_id );
		$retval = '&#8212;';

		// Output HTML element if not empty
		if ( ! empty( $color ) ) {
			$retval = '<i class="term-color" data-color="' . $color . '" style="background-color: ' . esc_attr( $color ) . '"></i>';
		}

		echo $retval;
	}

	/**
	 * Allow sorting by `color` color
	 *
	 * @since 0.1.0
	 *
	 * @param array $columns
	 *
	 * @return array
	 */
	public function sortable_columns( $columns = array() ) {
		$columns['color'] = 'color';
		return $columns;
	}

	/**
	 * Add `color` to term when updating
	 *
	 * @since 0.1.0
	 *
	 * @param  int     $term_id
	 * @param  string  $taxonomy
	 */
	public function add_term_color( $term_id = 0, $taxonomy = '' ) {

		// Bail if not updating color
		$color = ! empty( $_POST['term-color'] )
			? $_POST['term-color']
			: '';

		self::set_term_color( $term_id, $taxonomy, $color );
	}

	/**
	 * Set color of a specific term
	 *
	 * @since 0.1.0
	 *
	 * @param  int     $term_id
	 * @param  string  $taxonomy
	 * @param  string  $color
	 * @param  bool    $clean_cache
	 */
	public static function set_term_color( $term_id = 0, $taxonomy = '', $color = '', $clean_cache = false ) {

		// No color, so delete
		if ( empty( $color ) ) {
			delete_term_meta( $term_id, 'color' );

		// Update color value
		} else {
			update_term_meta( $term_id, 'color', $color );
		}

		// Maybe clean the term cache
		if ( true === $clean_cache ) {
			clean_term_cache( $term_id, $taxonomy );
		}
	}

	/**
	 * Return the color of a term
	 *
	 * @since 0.1.0
	 *
	 * @param int $term_id
	 */
	public function get_term_color( $term_id = 0 ) {
		return get_term_meta( $term_id, 'color', true );
	}

	/** Markup ****************************************************************/

	/**
	 * Output the "term-color" form field when adding a new term
	 *
	 * @since 0.1.0
	 */
	public static function term_color_add_form_field() {
		?>

		<div class="form-field form-required">
			<label for="term-color">
				<?php esc_html_e( 'Color', 'wp-term-colors' ); ?>
			</label>
			<input type="text" name="term-color" id="term-color" value="" size="7">
			<p class="description">
				<?php esc_html_e( 'Assign terms a custom color to visually separate them from each-other.', 'wp-term-colors' ); ?>
			</p>
		</div>

		<?php
	}

	/**
	 * Output the "term-color" form field when editing an existing term
	 *
	 * @since 0.1.0
	 *
	 * @param object $term
	 */
	public function term_color_edit_form_field( $term = false ) {
		?>

		<tr class="form-field">
			<th scope="row" valign="top">
				<label for="term-color">
					<?php esc_html_e( 'Color', 'wp-term-colors' ); ?>
				</label>
			</th>
			<td>
				<input name="term-color" id="term-color" type="text" value="<?php echo $this->get_term_color( $term->term_id ); ?>" size="7" />
				<p class="description">
					<?php esc_html_e( 'Assign terms a custom color to visually separate them from each-other.', 'wp-term-colors' ); ?>
				</p>
			</td>
		</tr>

		<?php
	}

	/**
	 * Output the "term-color" quick-edit field
	 *
	 * @since 0.1.0
	 *
	 * @param  $term
	 */
	public function quick_edit_term_color( $column_name = '', $screen = '', $name = '' ) {

		// Bail if not the `color` column on the `edit-tags` screen for a visible taxonomy
		if ( ( 'color' !== $column_name ) || ( 'edit-tags' !== $screen ) || ! in_array( $name, $this->get_taxonomies() ) ) {
			return false;
		} ?>

		<fieldset>
			<div class="inline-edit-col">
				<label>
					<span class="title"><?php esc_html_e( 'Color', 'wp-term-colors' ); ?></span>
					<span class="input-text-wrap">
						<input type="text" class="ptitle" name="term-color" value="" size="7">
					</span>
				</label>
			</div>
		</fieldset>

		<?php
	}

	/** Database Alters *******************************************************/

	/**
	 * Should a database update occur
	 *
	 * Runs on `init`
	 *
	 * @since 0.1.0
	 */
	private function maybe_upgrade_database() {

		// Check DB for version
		$db_version = get_option( $this->db_version_key );

		// Needs
		if ( $db_version < $this->db_version ) {
			$this->upgrade_database( $db_version );
		}
	}

	/**
	 * Modify the `term_taxonomy` table and add an `color` column to it
	 *
	 * @since 0.1.0
	 *
	 * @param  int    $old_version
	 *
	 * @global object $wpdb
	 */
	private function upgrade_database( $old_version = 0 ) {
		global $wpdb;

		$old_version = (int) $old_version;

		// The main column alter
		if ( $old_version < 201508240001 ) {
			// Nothing to do here yet
		}

		// Update the DB version
		update_option( $this->db_version_key, $this->db_version );
	}
}
endif;

/**
 * Instantiate the main WordPress Term Colors class
 *
 * @since 0.1.0
 */
function _wp_term_colors() {

	// Bail if no term meta
	if ( ! function_exists( 'add_term_meta' ) ) {
		return;
	}

	new WP_Term_Colors();
}
add_action( 'init', '_wp_term_colors', 99 );
