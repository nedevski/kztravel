<?php
defined( 'ABSPATH' ) || exit;

function kztravel_decode_term_slug( string $slug ): string {
	if ( str_contains( $slug, '%' ) ) {
		$decoded = rawurldecode( $slug );
		if ( '' !== $decoded ) {
			return $decoded;
		}
	}
	return $slug;
}

function kztravel_format_label( string $value ): string {
	$value = kztravel_decode_term_slug( $value );
	$words = explode( '-', $value );
	$words = array_map(
		function ( $word ) {
			if ( '' === $word ) {
				return '';
			}
			return mb_strtoupper( mb_substr( $word, 0, 1 ) ) . mb_substr( $word, 1 );
		},
		$words
	);
	return implode( ' ', $words );
}

function kztravel_format_country_label( string $country ): string {
	$labels = kztravel_country_labels();
	return $labels[ $country ] ?? kztravel_format_label( $country );
}

function kztravel_format_price_amount( float $price ): string {
	return number_format( $price, 0, ',', ' ' );
}

function kztravel_format_price( float $price ): string {
	return '€' . kztravel_format_price_amount( $price );
}

function kztravel_format_dual_price( float $eur, float $bgn ): string {
	return '€' . kztravel_format_price_amount( $eur ) . ' / ' . kztravel_format_price_amount( $bgn ) . 'лв';
}

function kztravel_format_price_from( float $price, float $price_bgn ): string {
	return kztravel_format_dual_price( $price, $price_bgn );
}

function kztravel_format_date( string $iso_date ): string {
	$timestamp = strtotime( $iso_date . 'T00:00:00' );
	if ( false === $timestamp ) {
		return $iso_date;
	}
	$months = array(
		1  => 'яну',
		2  => 'фев',
		3  => 'мар',
		4  => 'апр',
		5  => 'май',
		6  => 'юни',
		7  => 'юли',
		8  => 'авг',
		9  => 'сеп',
		10 => 'окт',
		11 => 'ное',
		12 => 'дек',
	);
	$day   = (int) date( 'j', $timestamp );
	$month = (int) date( 'n', $timestamp );
	$year  = date( 'Y', $timestamp );
	return sprintf( '%d %s %s г.', $day, $months[ $month ], $year );
}

function kztravel_image_alt_from_path( string $path, string $fallback ): string {
	if ( preg_match( '/^https?:\/\//', $path ) ) {
		return $fallback;
	}
	$filename = basename( $path );
	$filename = preg_replace( '/\.[^.]+$/', '', $filename );
	return str_replace( array( '-', '_' ), ' ', $filename );
}

function kztravel_image_alt_from_attachment( $attachment_id, string $trip_name ): string {
	$alt = get_post_meta( (int) $attachment_id, '_wp_attachment_image_alt', true );
	if ( is_string( $alt ) && '' !== $alt ) {
		return $alt;
	}
	return $trip_name;
}
