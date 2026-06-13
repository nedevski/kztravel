<?php
/**
 * Restore trip_category terms and trip assignments.
 *
 * Usage (from Local site shell):
 *   wp eval-file wp-content/themes/kztravel/tools/fix-category-terms.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	echo "Run via: wp eval-file wp-content/themes/kztravel/tools/fix-category-terms.php\n";
	exit( 1 );
}

$data_dir = kztravel_resolve_data_dir();
if ( '' !== $data_dir ) {
	WP_CLI::log( 'Using YAML data from: ' . $data_dir );
} else {
	WP_CLI::log( 'YAML data not found; using built-in category seed.' );
}

$result = kztravel_sync_trip_categories();

WP_CLI::log( 'Category terms ensured: ' . $result['terms'] );
WP_CLI::log( 'Trips updated: ' . $result['trips'] );

if ( ! empty( $result['skipped_trips'] ) ) {
	WP_CLI::warning( 'Trips not found in WordPress: ' . implode( ', ', $result['skipped_trips'] ) );
}

WP_CLI::log( 'Category terms now:' );
foreach ( kztravel_collect_trip_categories() as $category ) {
	$term = get_term_by( 'slug', $category, 'trip_category' );
	if ( $term && ! is_wp_error( $term ) ) {
		WP_CLI::log( '  ' . $term->slug . ' => ' . $term->name );
	}
}

WP_CLI::success( 'Category sync complete.' );
