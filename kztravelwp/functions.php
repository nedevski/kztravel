<?php
defined( 'ABSPATH' ) || exit;

define( 'KZTRAVEL_VERSION', '1.0.0' );

add_action(
	'after_setup_theme',
	function () {
		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
	}
);

add_action(
	'wp_enqueue_scripts',
	function () {
		wp_enqueue_style( 'kztravel', get_stylesheet_uri(), array(), KZTRAVEL_VERSION );
	}
);
