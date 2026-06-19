<?php
defined( 'ABSPATH' ) || exit;

/**
 * Trip slug => category slugs. Used when YAML data is not on the server.
 *
 * @return array<string, array<int, string>>
 */
function kztravel_trip_category_assignments_seed(): array {
	return array(
		'bulgaria-sunny-beach-nessebar' => array( 'плаж', 'лято', 'семейно', 'с-водач' ),
		'bulgaria-varna'                => array( 'плаж', 'градска-ваканция', 'лято', 'уикенд' ),
		'bulgaria-veliko-tarnovo'       => array( 'култура', 'екскурзия', 'уикенд', 'с-водач' ),
		'greece-alexandroupolis'        => array( 'плаж', 'семейно', 'лято', 'с-водач' ),
		'turkey-edirne-market'          => array( 'екскурзия', 'пазар', 'уикенд', 'с-водач' ),
		'turkey-istanbul'               => array( 'градска-ваканция', 'култура', 'екскурзия', 'с-водач' ),
	);
}

/**
 * Locate trip YAML data: theme bundle first, then sibling kztravelreact checkout.
 */
function kztravel_resolve_data_dir(): string {
	$candidates = array(
		KZTRAVEL_DIR . '/data',
		dirname( KZTRAVEL_DIR ) . '/kztravelreact/data',
	);

	foreach ( $candidates as $path ) {
		$resolved = realpath( $path );
		if ( false !== $resolved && is_dir( $resolved ) ) {
			return $resolved;
		}
	}

	return '';
}

/**
 * @return array<string, array<int, string>>
 */
function kztravel_load_trip_category_assignments_from_yaml( string $data_dir ): array {
	require_once KZTRAVEL_DIR . '/tools/yaml-parser.php';

	$trip_files  = glob( trailingslashit( $data_dir ) . 'trips/*.yaml' );
	$assignments = array();

	if ( ! is_array( $trip_files ) ) {
		return $assignments;
	}

	foreach ( $trip_files as $file ) {
		$slug = basename( $file, '.yaml' );
		$trip = kztravel_yaml_parse_file( $file );

		$categories = array();
		foreach ( $trip['category'] ?? array() as $category ) {
			if ( is_string( $category ) && '' !== trim( $category ) ) {
				$categories[] = trim( $category );
			}
		}

		if ( ! empty( $categories ) ) {
			$assignments[ $slug ] = $categories;
		}
	}

	return $assignments;
}

/**
 * @return array<string, array<int, string>>
 */
function kztravel_trip_category_assignments(): array {
	$data_dir = kztravel_resolve_data_dir();
	if ( '' !== $data_dir ) {
		$from_yaml = kztravel_load_trip_category_assignments_from_yaml( $data_dir );
		if ( ! empty( $from_yaml ) ) {
			return $from_yaml;
		}
	}

	return kztravel_trip_category_assignments_seed();
}

function kztravel_find_trip_post_by_slug( string $slug ): ?WP_Post {
	$posts = get_posts(
		array(
			'name'           => $slug,
			'post_type'      => 'trip',
			'post_status'    => 'any',
			'posts_per_page' => 1,
		)
	);

	return $posts[0] ?? null;
}

function kztravel_set_term_slug_raw( int $term_id, string $slug, string $taxonomy ): void {
	global $wpdb;

	$wpdb->update(
		$wpdb->terms,
		array( 'slug' => $slug ),
		array( 'term_id' => $term_id ),
		array( '%s' ),
		array( '%d' )
	);
	clean_term_cache( $term_id, $taxonomy );
}

function kztravel_find_trip_category_term( string $category ): ?WP_Term {
	$existing = get_term_by( 'slug', $category, 'trip_category' );
	if ( $existing && ! is_wp_error( $existing ) ) {
		return $existing;
	}

	$sanitized = sanitize_title( $category );
	if ( $sanitized !== $category ) {
		$existing = get_term_by( 'slug', $sanitized, 'trip_category' );
		if ( $existing && ! is_wp_error( $existing ) ) {
			return $existing;
		}
	}

	return null;
}

function kztravel_normalize_trip_category_term( int $term_id, string $category ): void {
	wp_update_term(
		$term_id,
		'trip_category',
		array( 'name' => $category )
	);
	kztravel_set_term_slug_raw( $term_id, $category, 'trip_category' );
}

/**
 * Ensure a trip_category term exists with the correct Cyrillic name and slug.
 */
function kztravel_ensure_trip_category_term( string $category ): int {
	$category = trim( $category );
	if ( '' === $category ) {
		return 0;
	}

	$existing = kztravel_find_trip_category_term( $category );
	if ( $existing ) {
		if ( $existing->name !== $category || $existing->slug !== $category ) {
			kztravel_normalize_trip_category_term( (int) $existing->term_id, $category );
		}
		return (int) $existing->term_id;
	}

	$created = wp_insert_term(
		$category,
		'trip_category',
		array( 'slug' => $category )
	);

	if ( is_wp_error( $created ) ) {
		if ( 'term_exists' === $created->get_error_code() ) {
			$term_id = (int) $created->get_error_data();
			kztravel_normalize_trip_category_term( $term_id, $category );
			return $term_id;
		}
		return 0;
	}

	$term_id = (int) $created['term_id'];
	kztravel_normalize_trip_category_term( $term_id, $category );

	return $term_id;
}

/**
 * Create category terms and assign them to trips.
 *
 * @return array{terms: int, trips: int, skipped_trips: array<int, string>}
 */
function kztravel_sync_trip_categories(): array {
	$assignments    = kztravel_trip_category_assignments();
	$all_categories = array();

	foreach ( $assignments as $categories ) {
		foreach ( $categories as $category ) {
			$all_categories[ $category ] = true;
		}
	}

	foreach ( array_keys( $all_categories ) as $category ) {
		kztravel_ensure_trip_category_term( $category );
	}

	$existing_terms = get_terms(
		array(
			'taxonomy'   => 'trip_category',
			'hide_empty' => false,
		)
	);
	if ( is_array( $existing_terms ) ) {
		foreach ( $existing_terms as $term ) {
			$decoded = kztravel_decode_term_slug( $term->slug );
			if ( $term->slug !== $decoded || $term->name !== $decoded ) {
				kztravel_normalize_trip_category_term( (int) $term->term_id, $decoded );
			}
		}
	}

	$updated_trips  = 0;
	$skipped_trips  = array();

	foreach ( $assignments as $slug => $categories ) {
		$post = kztravel_find_trip_post_by_slug( $slug );
		if ( ! $post ) {
			$skipped_trips[] = $slug;
			continue;
		}

		$term_ids = array();
		foreach ( $categories as $category ) {
			$term_id = kztravel_ensure_trip_category_term( $category );
			if ( $term_id > 0 ) {
				$term_ids[] = $term_id;
			}
		}

		wp_set_object_terms( $post->ID, $term_ids, 'trip_category', false );
		++$updated_trips;
	}

	return array(
		'terms'          => count( $all_categories ),
		'trips'          => $updated_trips,
		'skipped_trips'  => $skipped_trips,
	);
}

/**
 * @return array<int, string>
 */
function kztravel_collect_trip_categories(): array {
	$categories = array();
	foreach ( kztravel_trip_category_assignments() as $trip_categories ) {
		foreach ( $trip_categories as $category ) {
			$categories[ $category ] = true;
		}
	}

	$category_list = array_keys( $categories );
	sort( $category_list, SORT_STRING );

	return $category_list;
}

/**
 * Repair trip_category terms and trip assignments.
 *
 * @return array{updated_terms: int, updated_trips: int, deleted_orphans: int, skipped_trips: array<int, string>}
 */
function kztravel_repair_trip_categories(): array {
	$sync           = kztravel_sync_trip_categories();
	$valid_slugs    = array_flip( kztravel_collect_trip_categories() );
	$deleted_orphans = 0;

	if ( $sync['trips'] > 0 && ! empty( $valid_slugs ) ) {
		$existing_terms = get_terms(
			array(
				'taxonomy'   => 'trip_category',
				'hide_empty' => false,
			)
		);

		if ( is_array( $existing_terms ) ) {
			foreach ( $existing_terms as $term ) {
				if ( isset( $valid_slugs[ $term->slug ] ) ) {
					continue;
				}
				$deleted = wp_delete_term( (int) $term->term_id, 'trip_category' );
				if ( ! is_wp_error( $deleted ) && $deleted ) {
					++$deleted_orphans;
				}
			}
		}
	}

	return array(
		'updated_terms'   => $sync['terms'],
		'updated_trips'   => $sync['trips'],
		'deleted_orphans' => $deleted_orphans,
		'skipped_trips'   => $sync['skipped_trips'],
	);
}
