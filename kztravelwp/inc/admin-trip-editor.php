<?php
defined( 'ABSPATH' ) || exit;

add_filter(
	'use_block_editor_for_post_type',
	function ( $use, $post_type ) {
		return 'trip' === $post_type ? false : $use;
	},
	10,
	2
);

add_action(
	'add_meta_boxes',
	function () {
		remove_meta_box( 'slugdiv', 'trip', 'normal' );
		remove_meta_box( 'postcustom', 'trip', 'normal' );
		remove_meta_box( 'postimagediv', 'trip', 'side' );
		remove_meta_box( 'trip_countrydiv', 'trip', 'side' );
		remove_meta_box( 'trip_categorydiv', 'trip', 'side' );
	},
	99
);

add_filter(
	'acf/update_value/name=trip_hero',
	function ( $value, $post_id ) {
		if ( 'trip' !== get_post_type( $post_id ) ) {
			return $value;
		}
		$attachment_id = is_array( $value ) ? (int) ( $value['ID'] ?? 0 ) : (int) $value;
		if ( $attachment_id ) {
			set_post_thumbnail( $post_id, $attachment_id );
		} else {
			delete_post_thumbnail( $post_id );
		}
		return $value;
	},
	10,
	2
);

add_action(
	'admin_enqueue_scripts',
	function ( $hook ) {
		if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
			return;
		}
		$screen = get_current_screen();
		if ( ! $screen || 'trip' !== $screen->post_type ) {
			return;
		}
		wp_enqueue_style(
			'kztravel-trip-edit-admin',
			KZTRAVEL_URI . '/assets/css/trip-edit-admin.css',
			array(),
			KZTRAVEL_VERSION
		);
	}
);
