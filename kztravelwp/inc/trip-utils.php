<?php
defined( 'ABSPATH' ) || exit;

const KZTRAVEL_VALID_STATUSES = array( 'available', 'lastSpots', 'soldout' );

function kztravel_uses_acf_trip_fields(): bool {
	return function_exists( 'get_field' ) && function_exists( 'acf_add_local_field_group' );
}

function kztravel_get_trip_field( int $post_id, string $field_name, $default = null ) {
	if ( kztravel_uses_acf_trip_fields() ) {
		$value = get_field( $field_name, $post_id );
		if ( null !== $value && false !== $value && '' !== $value ) {
			return $value;
		}
		if ( is_array( $value ) && array() !== $value ) {
			return $value;
		}
	}

	$meta = get_post_meta( $post_id, $field_name, true );
	if ( '' === $meta || false === $meta ) {
		return $default;
	}

	return $meta;
}

function kztravel_update_trip_field( int $post_id, string $field_name, $value ): void {
	if ( kztravel_uses_acf_trip_fields() ) {
		update_field( $field_name, $value, $post_id );
		return;
	}

	if ( '' === $value || ( is_array( $value ) && array() === $value ) ) {
		delete_post_meta( $post_id, $field_name );
		return;
	}

	update_post_meta( $post_id, $field_name, $value );
}

function kztravel_normalize_date( array $entry ): array {
	if ( ! empty( $entry['status'] ) && in_array( $entry['status'], KZTRAVEL_VALID_STATUSES, true ) ) {
		return $entry;
	}

	$status = ( isset( $entry['available'] ) && false === $entry['available'] ) ? 'soldout' : 'available';
	$entry['status'] = $status;
	return $entry;
}

function kztravel_get_today_iso(): string {
	return wp_date( 'Y-m-d' );
}

function kztravel_is_date_past( string $date ): bool {
	return $date < kztravel_get_today_iso();
}

function kztravel_has_upcoming_dates( array $dates ): bool {
	$today = kztravel_get_today_iso();
	foreach ( $dates as $entry ) {
		if ( ( $entry['date'] ?? '' ) >= $today ) {
			return true;
		}
	}
	return false;
}

function kztravel_is_trip_ended( array $dates ): bool {
	return count( $dates ) > 0 && ! kztravel_has_upcoming_dates( $dates );
}

function kztravel_get_most_recent_date( array $dates ): ?array {
	if ( empty( $dates ) ) {
		return null;
	}
	usort(
		$dates,
		function ( $a, $b ) {
			return strcmp( $b['date'] ?? '', $a['date'] ?? '' );
		}
	);
	return $dates[0];
}

function kztravel_is_date_bookable( array $date ): bool {
	$status = $date['status'] ?? 'available';
	switch ( $status ) {
		case 'available':
		case 'lastSpots':
			return true;
		case 'soldout':
			return false;
		default:
			return ! ( isset( $date['available'] ) && false === $date['available'] );
	}
}

function kztravel_get_bookable_dates( array $dates ): array {
	return array_values( array_filter( $dates, 'kztravel_is_date_bookable' ) );
}

function kztravel_get_upcoming_bookable_dates( array $dates ): array {
	$today = kztravel_get_today_iso();
	$bookable = kztravel_get_bookable_dates( $dates );
	$upcoming = array_filter(
		$bookable,
		function ( $date ) use ( $today ) {
			return ( $date['date'] ?? '' ) >= $today;
		}
	);
	usort(
		$upcoming,
		function ( $a, $b ) {
			return strcmp( $a['date'] ?? '', $b['date'] ?? '' );
		}
	);
	return array_values( $upcoming );
}

function kztravel_get_additional_bookable_date_count( array $dates ): int {
	$upcoming = kztravel_get_upcoming_bookable_dates( $dates );
	return max( 0, count( $upcoming ) - 1 );
}

function kztravel_get_next_available_date( array $dates ): ?array {
	$upcoming = kztravel_get_upcoming_bookable_dates( $dates );
	return $upcoming[0] ?? null;
}

function kztravel_get_effective_date_price( array $date ): float {
	if ( isset( $date['discountedPrice'] ) && null !== $date['discountedPrice'] ) {
		return (float) $date['discountedPrice'];
	}
	if ( isset( $date['discounted_price'] ) && null !== $date['discounted_price'] && '' !== $date['discounted_price'] ) {
		return (float) $date['discounted_price'];
	}
	return (float) ( $date['price'] ?? 0 );
}

function kztravel_get_lowest_bookable_date( array $dates ): ?array {
	$upcoming = kztravel_get_upcoming_bookable_dates( $dates );
	if ( empty( $upcoming ) ) {
		return null;
	}
	$lowest = $upcoming[0];
	foreach ( $upcoming as $current ) {
		if ( kztravel_get_effective_date_price( $current ) < kztravel_get_effective_date_price( $lowest ) ) {
			$lowest = $current;
		}
	}
	return $lowest;
}

function kztravel_trip_has_active_discount( array $trip ): bool {
	$dates = $trip['dates'] ?? array();
	foreach ( kztravel_get_upcoming_bookable_dates( $dates ) as $date ) {
		$discounted = $date['discountedPrice'] ?? $date['discounted_price'] ?? null;
		$price      = (float) ( $date['price'] ?? 0 );
		if ( null !== $discounted && $discounted > 0 && $discounted < $price ) {
			return true;
		}
	}
	return false;
}

function kztravel_pick_random_trips( array $trips, int $count ): array {
	if ( empty( $trips ) ) {
		return array();
	}
	$shuffled = $trips;
	shuffle( $shuffled );
	return array_slice( $shuffled, 0, $count );
}

function kztravel_is_fully_booked( array $dates ): bool {
	return null === kztravel_get_next_available_date( $dates );
}

function kztravel_map_acf_date( array $row ): array {
	return kztravel_normalize_date(
		array(
			'date'                 => $row['date'] ?? '',
			'price'                => isset( $row['price'] ) ? (float) $row['price'] : 0,
			'priceBgn'             => isset( $row['price_bgn'] ) ? (float) $row['price_bgn'] : 0,
			'discountedPrice'      => isset( $row['discounted_price'] ) && '' !== $row['discounted_price'] ? (float) $row['discounted_price'] : null,
			'discountedPriceBgn'   => isset( $row['discounted_price_bgn'] ) && '' !== $row['discounted_price_bgn'] ? (float) $row['discounted_price_bgn'] : null,
			'status'               => $row['status'] ?? 'available',
		)
	);
}

function kztravel_map_acf_included( array $row ): array {
	return array(
		'title' => $row['title'] ?? $row['name'] ?? '',
	);
}

function kztravel_map_acf_excluded( array $row ): array {
	return array(
		'title'    => $row['title'] ?? $row['name'] ?? '',
		'price'    => isset( $row['price'] ) && '' !== $row['price'] ? (float) $row['price'] : null,
		'priceBgn' => isset( $row['price_bgn'] ) && '' !== $row['price_bgn'] ? (float) $row['price_bgn'] : null,
	);
}

function kztravel_enrich_trip( int $post_id ): array {
	$post = get_post( $post_id );
	if ( ! $post || 'trip' !== $post->post_type ) {
		return array();
	}

	$raw_dates = kztravel_get_trip_field( $post_id, 'trip_dates', array() );
	$dates     = array_map( 'kztravel_map_acf_date', is_array( $raw_dates ) ? $raw_dates : array() );

	$country_terms = wp_get_post_terms( $post_id, 'trip_country', array( 'fields' => 'slugs' ) );
	$country       = ( ! is_wp_error( $country_terms ) && ! empty( $country_terms ) ) ? $country_terms[0] : '';

	$category_terms = wp_get_post_terms( $post_id, 'trip_category', array( 'fields' => 'slugs' ) );
	$categories     = is_wp_error( $category_terms ) ? array() : array_map( 'kztravel_decode_term_slug', $category_terms );

	$gallery_raw = kztravel_get_trip_field( $post_id, 'trip_gallery', array() );
	$gallery     = array();
	$trip_name   = get_the_title( $post_id );
	if ( is_array( $gallery_raw ) ) {
		foreach ( $gallery_raw as $image ) {
			if ( is_numeric( $image ) ) {
				$attachment_id = (int) $image;
				$url           = wp_get_attachment_image_url( $attachment_id, 'large' );
				if ( $url ) {
					$gallery[] = array(
						'id'  => $attachment_id,
						'url' => $url,
						'alt' => kztravel_image_alt_from_attachment( $attachment_id, $trip_name ),
					);
				}
				continue;
			}
			if ( is_array( $image ) ) {
				$gallery[] = array(
					'id'  => $image['ID'] ?? 0,
					'url' => $image['url'] ?? '',
					'alt' => kztravel_image_alt_from_attachment( $image['ID'] ?? 0, $trip_name ),
				);
			}
		}
	}

	$itinerary_raw = kztravel_get_trip_field( $post_id, 'trip_itinerary', array() );
	$itinerary     = array();
	if ( is_array( $itinerary_raw ) ) {
		foreach ( $itinerary_raw as $day ) {
			$body = (string) ( $day['body'] ?? $day['description'] ?? '' );
			$itinerary[] = array(
				'day'   => (int) ( $day['day'] ?? 0 ),
				'title' => $day['title'] ?? '',
				'body'  => preg_replace( '/<br\s*\/?>\r?\n?/i', "\n", $body ),
			);
		}
	}

	$included_raw = kztravel_get_trip_field( $post_id, 'trip_included', array() );
	$included     = array_map( 'kztravel_map_acf_included', is_array( $included_raw ) ? $included_raw : array() );

	$excluded_raw = kztravel_get_trip_field( $post_id, 'trip_excluded', array() );
	$excluded     = array_map( 'kztravel_map_acf_excluded', is_array( $excluded_raw ) ? $excluded_raw : array() );

	$upcoming_bookable = kztravel_get_upcoming_bookable_dates( $dates );
	$next_date         = $upcoming_bookable[0] ?? null;
	$lowest_date       = kztravel_get_lowest_bookable_date( $dates );
	$ended             = kztravel_is_trip_ended( $dates );
	$last_date         = $ended ? kztravel_get_most_recent_date( $dates ) : null;
	$price_date        = $lowest_date ?? $last_date;

	$hero_url = get_the_post_thumbnail_url( $post_id, 'large' ) ?: '';

	return array(
		'id'                         => $post_id,
		'slug'                       => $post->post_name,
		'name'                       => $trip_name,
		'description'                => apply_filters( 'the_content', $post->post_content ),
		'description_plain'          => wp_strip_all_tags( $post->post_content ),
		'country'                    => $country,
		'duration'                   => (string) ( kztravel_get_trip_field( $post_id, 'trip_duration', '' ) ?: '' ),
		'category'                   => $categories,
		'dates'                      => $dates,
		'gallery'                    => $gallery,
		'itinerary'                  => $itinerary,
		'included'                   => $included,
		'excluded'                   => $excluded,
		'hero_url'                   => $hero_url,
		'permalink'                  => get_permalink( $post_id ),
		'nextDate'                   => $next_date,
		'lastDate'                   => $last_date,
		'displayPrice'               => isset( $price_date['price'] ) ? (float) $price_date['price'] : null,
		'displayPriceBgn'            => isset( $price_date['priceBgn'] ) ? (float) $price_date['priceBgn'] : ( isset( $price_date['price_bgn'] ) ? (float) $price_date['price_bgn'] : null ),
		'displayDiscountedPrice'     => isset( $price_date['discountedPrice'] ) ? $price_date['discountedPrice'] : ( $price_date['discounted_price'] ?? null ),
		'displayDiscountedPriceBgn'  => isset( $price_date['discountedPriceBgn'] ) ? $price_date['discountedPriceBgn'] : ( $price_date['discounted_price_bgn'] ?? null ),
		'ended'                      => $ended,
		'fullyBooked'                => ! $ended && null === $next_date,
		'moreAvailableDates'         => kztravel_get_additional_bookable_date_count( $dates ),
	);
}

function kztravel_get_trip_by_slug( string $slug ): ?array {
	$post = get_page_by_path( $slug, OBJECT, 'trip' );
	if ( ! $post ) {
		return null;
	}
	return kztravel_enrich_trip( $post->ID );
}

function kztravel_get_trip_filter_price( array $trip ): ?float {
	if ( null === ( $trip['displayPrice'] ?? null ) ) {
		return null;
	}
	$discounted = $trip['displayDiscountedPrice'] ?? null;
	return null !== $discounted ? (float) $discounted : (float) $trip['displayPrice'];
}

function kztravel_parse_duration_days( ?string $duration ): ?int {
	if ( ! $duration ) {
		return null;
	}
	if ( preg_match( '/(\d+)/', $duration, $matches ) ) {
		return (int) $matches[1];
	}
	return null;
}
