<?php
defined( 'ABSPATH' ) || exit;

function kztravel_format_price_range_label( float $min, ?float $max ): string {
	if ( null === $max ) {
		return 'над ' . kztravel_format_price( $min );
	}
	if ( 0.0 === $min ) {
		return 'до ' . kztravel_format_price( $max );
	}
	return kztravel_format_price( $min ) . ' – ' . kztravel_format_price( $max );
}

function kztravel_build_price_ranges( array $trips ): array {
	$prices = array();
	foreach ( $trips as $trip ) {
		$price = kztravel_get_trip_filter_price( $trip );
		if ( null !== $price ) {
			$prices[] = $price;
		}
	}

	if ( empty( $prices ) ) {
		return array();
	}

	$candidates = array(
		array( 'id' => 'under-400', 'min' => 0, 'max' => 400 ),
		array( 'id' => '400-600', 'min' => 400, 'max' => 600 ),
		array( 'id' => '600-900', 'min' => 600, 'max' => 900 ),
		array( 'id' => 'over-900', 'min' => 900, 'max' => null ),
	);

	$ranges = array();
	foreach ( $candidates as $range ) {
		foreach ( $prices as $price ) {
			if ( kztravel_price_in_range( $price, $range ) ) {
				$ranges[] = array(
					'id'    => $range['id'],
					'label' => kztravel_format_price_range_label( $range['min'], $range['max'] ),
					'min'   => $range['min'],
					'max'   => $range['max'],
				);
				break;
			}
		}
	}

	return $ranges;
}

function kztravel_price_in_range( float $price, array $range ): bool {
	switch ( $range['id'] ) {
		case 'under-400':
			return $price < 400;
		case '400-600':
			return $price >= 400 && $price <= 600;
		case '600-900':
			return $price > 600 && $price <= 900;
		case 'over-900':
			return $price > 900;
		default:
			if ( null === $range['max'] ) {
				return $price >= $range['min'];
			}
			return $price >= $range['min'] && $price <= $range['max'];
	}
}

function kztravel_build_filter_index( array $trips ): array {
	$countries = array_unique( array_column( $trips, 'country' ) );
	$countries = array_values( array_filter( $countries ) );
	sort( $countries );

	$categories = array();
	foreach ( $trips as $trip ) {
		foreach ( $trip['category'] ?? array() as $cat ) {
			$categories[ $cat ] = true;
		}
	}
	$category_list = array_keys( $categories );
	usort(
		$category_list,
		function ( $a, $b ) {
			return strcmp( kztravel_format_label( $a ), kztravel_format_label( $b ) );
		}
	);

	$duration_map = array();
	foreach ( $trips as $trip ) {
		$days = kztravel_parse_duration_days( $trip['duration'] ?? '' );
		if ( null !== $days && ! empty( $trip['duration'] ) ) {
			$duration_map[ $days ] = $trip['duration'];
		}
	}
	ksort( $duration_map );
	$durations = array();
	foreach ( $duration_map as $days => $label ) {
		$durations[] = array(
			'days'  => (string) $days,
			'label' => $label,
		);
	}

	$price_ranges = kztravel_build_price_ranges( $trips );

	$show_discount = false;
	foreach ( $trips as $trip ) {
		if ( kztravel_trip_has_active_discount( $trip ) ) {
			$show_discount = true;
			break;
		}
	}

	return array(
		'countries'           => $countries,
		'categories'          => $category_list,
		'durations'           => $durations,
		'priceRanges'         => $price_ranges,
		'showCountryFilter'   => count( $countries ) > 1,
		'showCategoryFilter'  => count( $category_list ) > 0,
		'showDurationFilter'  => count( $durations ) > 1,
		'showPriceFilter'     => count( $price_ranges ) > 1,
		'showDiscountFilter'  => $show_discount,
	);
}

function kztravel_filter_trips( array $trips, array $filters ): array {
	return array_values(
		array_filter(
			$trips,
			function ( $trip ) use ( $filters ) {
				if ( ! empty( $filters['country'] ) && ( $trip['country'] ?? '' ) !== $filters['country'] ) {
					return false;
				}

				if ( ! empty( $filters['categories'] ) ) {
					$has_category = false;
					foreach ( $filters['categories'] as $category ) {
						if ( in_array( $category, $trip['category'] ?? array(), true ) ) {
							$has_category = true;
							break;
						}
					}
					if ( ! $has_category ) {
						return false;
					}
				}

				if ( ! empty( $filters['durations'] ) ) {
					$days = kztravel_parse_duration_days( $trip['duration'] ?? '' );
					if ( null === $days || ! in_array( (string) $days, $filters['durations'], true ) ) {
						return false;
					}
				}

				if ( ! empty( $filters['priceRange'] ) ) {
					$price = kztravel_get_trip_filter_price( $trip );
					if ( null === $price || ! kztravel_price_in_range( $price, $filters['priceRange'] ) ) {
						return false;
					}
				}

				if ( ! empty( $filters['discountedOnly'] ) && ! kztravel_trip_has_active_discount( $trip ) ) {
					return false;
				}

				return true;
			}
		)
	);
}

function kztravel_get_filter_pool( array $trips, array $filters, string $dimension ): array {
	$partial = $filters;
	switch ( $dimension ) {
		case 'country':
			$partial['country'] = null;
			break;
		case 'price':
			$partial['priceRange'] = null;
			break;
		case 'duration':
			$partial['durations'] = array();
			break;
		case 'category':
			$partial['categories'] = array();
			break;
		case 'discount':
			$partial['discountedOnly'] = false;
			break;
	}
	return kztravel_filter_trips( $trips, $partial );
}

function kztravel_parse_filters_from_search( string $search, array $price_ranges = array() ): array {
	$params = array();
	parse_str( ltrim( $search, '?' ), $params );

	$country    = ! empty( $params['country'] ) ? sanitize_text_field( $params['country'] ) : null;
	$categories = ! empty( $params['category'] ) ? array_filter( explode( ',', $params['category'] ) ) : array();
	$categories = array_map( 'kztravel_decode_term_slug', $categories );
	$durations  = ! empty( $params['duration'] ) ? array_filter( explode( ',', $params['duration'] ) ) : array();
	$price_id   = ! empty( $params['price'] ) ? sanitize_text_field( $params['price'] ) : null;
	$price_range = null;
	if ( $price_id ) {
		foreach ( $price_ranges as $range ) {
			if ( $range['id'] === $price_id ) {
				$price_range = $range;
				break;
			}
		}
	}
	$discounted_only = isset( $params['discount'] ) && '1' === $params['discount'];

	return array(
		'country'        => $country,
		'categories'     => array_values( $categories ),
		'durations'      => array_values( $durations ),
		'priceRange'     => $price_range,
		'discountedOnly' => $discounted_only,
	);
}

function kztravel_has_active_filters( array $filters ): bool {
	return ! empty( $filters['country'] )
		|| ! empty( $filters['categories'] )
		|| ! empty( $filters['durations'] )
		|| ! empty( $filters['priceRange'] )
		|| ! empty( $filters['discountedOnly'] );
}

function kztravel_build_filter_params( array $filters ): string {
	$params = array();
	if ( ! empty( $filters['country'] ) ) {
		$params['country'] = $filters['country'];
	}
	if ( ! empty( $filters['categories'] ) ) {
		$params['category'] = implode( ',', $filters['categories'] );
	}
	if ( ! empty( $filters['durations'] ) ) {
		$params['duration'] = implode( ',', $filters['durations'] );
	}
	if ( ! empty( $filters['priceRange']['id'] ) ) {
		$params['price'] = $filters['priceRange']['id'];
	}
	if ( ! empty( $filters['discountedOnly'] ) ) {
		$params['discount'] = '1';
	}
	$query = http_build_query( $params );
	return $query ? '?' . $query : '';
}
