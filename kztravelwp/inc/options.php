<?php
defined( 'ABSPATH' ) || exit;

function kztravel_get_option( string $key, $default = '' ) {
	if ( function_exists( 'get_field' ) ) {
		$value = get_field( $key, 'option' );
		return ( null !== $value && '' !== $value ) ? $value : $default;
	}
	return $default;
}

function kztravel_get_site_title(): string {
	return (string) kztravel_get_option( 'site_title', get_bloginfo( 'name' ) );
}

function kztravel_get_favicon_url(): string {
	$favicon = kztravel_get_option( 'site_favicon' );
	if ( is_array( $favicon ) && ! empty( $favicon['url'] ) ) {
		return $favicon['url'];
	}
	return KZTRAVEL_URI . '/assets/images/favicon.svg';
}

function kztravel_get_background_url(): string {
	$bg = kztravel_get_option( 'site_background' );
	if ( is_array( $bg ) && ! empty( $bg['url'] ) ) {
		return $bg['url'];
	}
	return KZTRAVEL_URI . '/assets/images/bg.svg';
}

function kztravel_get_contact(): array {
	$hours = kztravel_get_option( 'contact_working_hours' );
	if ( ! is_array( $hours ) ) {
		$hours = array();
	}
	$hour_values = array();
	foreach ( $hours as $row ) {
		$hour_values[] = is_array( $row ) ? ( $row['hours'] ?? '' ) : (string) $row;
	}

	return array(
		'phone'        => (string) kztravel_get_option( 'contact_phone' ),
		'email'        => (string) kztravel_get_option( 'contact_email' ),
		'address'      => (string) kztravel_get_option( 'contact_address' ),
		'mapEmbedUrl'  => (string) kztravel_get_option( 'contact_map_embed_url' ),
		'workingHours' => $hour_values,
		'bankDetails'  => array(
			'bankName' => (string) kztravel_get_option( 'bank_name' ),
			'iban'     => (string) kztravel_get_option( 'bank_iban' ),
			'holder'   => (string) kztravel_get_option( 'bank_holder' ),
		),
	);
}

function kztravel_get_footer(): array {
	return array(
		'registration' => (string) kztravel_get_option( 'footer_registration' ),
		'company'      => (string) kztravel_get_option( 'footer_company' ),
	);
}

function kztravel_is_trips_route(): bool {
	return is_front_page() || is_singular( 'trip' );
}

function kztravel_nav_link_class( string $route ): string {
	$active = false;
	switch ( $route ) {
		case 'trips':
			$active = kztravel_is_trips_route();
			break;
		case 'contact':
			$active = is_page( 'contact' );
			break;
		case 'booking':
			$active = is_page( 'booking' );
			break;
	}
	return 'site-header__nav-link' . ( $active ? ' site-header__nav-link--active' : '' );
}
