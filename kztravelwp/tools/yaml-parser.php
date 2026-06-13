<?php
defined( 'ABSPATH' ) || exit;

function kztravel_yaml_parse_file( string $path ): array {
	require_once dirname( __FILE__ ) . '/Spyc.php';

	$parsed = Spyc::YAMLLoad( $path );
	return is_array( $parsed ) ? $parsed : array();
}
