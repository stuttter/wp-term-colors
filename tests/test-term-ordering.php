<?php

/**
 * Focused checks for taxonomy term ordering by color metadata.
 */

define( 'ABSPATH', dirname( __DIR__ ) . '/' );

class WP_Meta_Query {
	public function get_cast_for_type( $type ) {
		return ( 'NUMERIC' === $type )
			? 'SIGNED'
			: 'CHAR';
	}
}

class WP_Term_Colors_Test_DB {
	public $termmeta = 'wp_termmeta';

	public function prepare( $query, $value ) {
		return str_replace( '%s', "'" . addslashes( $value ) . "'", $query );
	}
}

$wpdb = new WP_Term_Colors_Test_DB();

require_once dirname( __DIR__ ) . '/includes/class-wp-term-meta-ui.php';

class WP_Term_Colors_Test_UI extends JJJ\WP\Term\Meta\UI {
	public $key_type = '';

	public function __construct() {
		$this->meta_key   = 'color';
		$this->taxonomies = array( 'product_cat' );
	}
}

$ui = new WP_Term_Colors_Test_UI();

$clauses = array(
	'fields'  => 't.term_id',
	'join'    => '',
	'where'   => 'WHERE 1=1',
	'orderby' => 'ORDER BY t.name ASC',
	'order'   => 'DESC',
);

$filtered = $ui->terms_clauses(
	$clauses,
	array( 'product_cat' ),
	array(
		'orderby' => 'color',
		'order'   => 'DESC',
	)
);

if ( false === strpos( $filtered['join'], 'LEFT JOIN wp_termmeta' ) ) {
	throw new RuntimeException( 'Color ordering must retain terms without color metadata.' );
}

if ( false === strpos( $filtered['join'], "meta_key = 'color'" ) ) {
	throw new RuntimeException( 'The color meta key must be constrained in the join.' );
}

if ( $clauses['where'] !== $filtered['where'] ) {
	throw new RuntimeException( 'Color ordering must not exclude terms in the WHERE clause.' );
}

if ( $clauses['fields'] !== $filtered['fields'] ) {
	throw new RuntimeException( 'Color ordering must not change the selected term fields.' );
}

if ( 'ORDER BY wp_term_meta_color.meta_value DESC' !== trim( $filtered['orderby'] . ' ' . $filtered['order'] ) ) {
	throw new RuntimeException( 'Color ordering must cooperate with the direction appended by WordPress.' );
}

$ui->key_type = 'NUMERIC';
$ascending = $clauses;
$ascending['order'] = 'ASC';
$cast = $ui->terms_clauses(
	$ascending,
	array( 'product_cat' ),
	array(
		'orderby' => 'meta_value',
		'order'   => 'ASC',
	)
);

if ( 'ORDER BY CAST(wp_term_meta_color.meta_value AS SIGNED) ASC' !== trim( $cast['orderby'] . ' ' . $cast['order'] ) ) {
	throw new RuntimeException( 'Typed metadata must use a valid SQL cast.' );
}

$numeric = $ui->terms_clauses(
	$clauses,
	array( 'product_cat' ),
	array(
		'orderby' => 'meta_value_num',
		'order'   => 'DESC',
	)
);

if ( 'ORDER BY wp_term_meta_color.meta_value+0 DESC' !== trim( $numeric['orderby'] . ' ' . $numeric['order'] ) ) {
	throw new RuntimeException( 'Numeric metadata ordering must preserve the requested direction.' );
}

$untouched = $ui->terms_clauses(
	$clauses,
	array( 'category' ),
	array( 'orderby' => 'color' )
);

if ( $clauses !== $untouched ) {
	throw new RuntimeException( 'Queries for unrelated taxonomies must remain unchanged.' );
}

echo "Term ordering checks passed.\n";
