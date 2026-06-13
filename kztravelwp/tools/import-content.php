<?php
/**
 * One-time content import from kztravelreact YAML data.
 *
 * Usage (from Local site shell):
 *   wp eval-file wp-content/themes/kztravel/tools/import-content.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	echo "Run via: wp eval-file wp-content/themes/kztravel/tools/import-content.php\n";
	exit( 1 );
}

require_once dirname( __DIR__ ) . '/tools/yaml-parser.php';

$react_data = kztravel_resolve_data_dir();
if ( '' === $react_data ) {
	WP_CLI::error( 'Could not locate trip data. Expected kztravelwp/data or kztravelreact/data.' );
}

WP_CLI::log( 'Importing from: ' . $react_data );

function kztravel_import_sideload_image( string $url, int $post_id, string $desc = '' ) {
	if ( str_starts_with( $url, '/' ) ) {
		$theme_dir = realpath( KZTRAVEL_DIR ) ?: KZTRAVEL_DIR;
		$local     = dirname( $theme_dir ) . '/kztravelreact/public' . $url;
		if ( ! file_exists( $local ) ) {
			WP_CLI::warning( 'Local image not found: ' . $url );
			return 0;
		}

		$filename   = basename( $local );
		$upload_dir = wp_upload_dir();
		$tmp        = trailingslashit( $upload_dir['path'] ) . wp_unique_filename( $upload_dir['path'], $filename );
		copy( $local, $tmp );

		$file_array = array(
			'name'     => $filename,
			'tmp_name' => $tmp,
		);

		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		$attachment_id = media_handle_sideload( $file_array, $post_id, $desc );
		if ( is_wp_error( $attachment_id ) ) {
			@unlink( $tmp );
			WP_CLI::warning( $attachment_id->get_error_message() );
			return 0;
		}
		return (int) $attachment_id;
	}

	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$tmp = download_url( $url );
	if ( is_wp_error( $tmp ) ) {
		WP_CLI::warning( $tmp->get_error_message() );
		return 0;
	}

	$filename = basename( parse_url( $url, PHP_URL_PATH ) ?: 'image.jpg' );
	if ( ! str_contains( $filename, '.' ) ) {
		$filename .= '.jpg';
	}

	$file_array = array(
		'name'     => sanitize_file_name( $filename ),
		'tmp_name' => $tmp,
	);

	$attachment_id = media_handle_sideload( $file_array, $post_id, $desc );
	if ( is_wp_error( $attachment_id ) ) {
		@unlink( $tmp );
		WP_CLI::warning( $attachment_id->get_error_message() );
		return 0;
	}
	return (int) $attachment_id;
}

// Site settings.
$site_yaml = $react_data . '/site.yaml';
if ( file_exists( $site_yaml ) ) {
	$site = kztravel_yaml_parse_file( $site_yaml );
	WP_CLI::log( 'Importing site settings...' );

	update_field( 'site_title', $site['title'] ?? '', 'option' );

	// Favicon and background use theme assets (SVG not uploaded to media library).

	$contact = $site['contact'] ?? array();
	update_field( 'contact_phone', $contact['phone'] ?? '', 'option' );
	update_field( 'contact_email', $contact['email'] ?? '', 'option' );
	update_field( 'contact_address', $contact['address'] ?? '', 'option' );
	update_field( 'contact_map_embed_url', $contact['mapEmbedUrl'] ?? '', 'option' );

	$hours_rows = array();
	foreach ( $contact['workingHours'] ?? array() as $hours ) {
		$hours_rows[] = array( 'hours' => $hours );
	}
	update_field( 'contact_working_hours', $hours_rows, 'option' );

	$bank = $contact['bankDetails'] ?? array();
	update_field( 'bank_name', $bank['bankName'] ?? '', 'option' );
	update_field( 'bank_iban', $bank['iban'] ?? '', 'option' );
	update_field( 'bank_holder', $bank['holder'] ?? '', 'option' );

	$footer = $site['footer'] ?? array();
	update_field( 'footer_registration', $footer['registration'] ?? '', 'option' );
	update_field( 'footer_company', $footer['company'] ?? '', 'option' );
}

// Booking page.
$booking_yaml = $react_data . '/booking.yaml';
if ( file_exists( $booking_yaml ) ) {
	$booking = kztravel_yaml_parse_file( $booking_yaml );
	WP_CLI::log( 'Importing booking page...' );

	$booking_page = get_page_by_path( 'booking' );
	if ( ! $booking_page ) {
		$page_id = wp_insert_post(
			array(
				'post_type'    => 'page',
				'post_title'   => $booking['title'] ?? 'Как да резервирам',
				'post_name'    => 'booking',
				'post_status'  => 'publish',
				'post_content' => '',
			)
		);
	} else {
		$page_id = $booking_page->ID;
	}

	update_post_meta( $page_id, '_wp_page_template', 'page-booking.php' );
	update_field( 'booking_intro', trim( $booking['intro'] ?? '' ), $page_id );

	$sections = array();
	foreach ( $booking['sections'] ?? array() as $section ) {
		$items = array();
		foreach ( $section['items'] ?? array() as $item ) {
			$items[] = array( 'text' => $item );
		}
		$sections[] = array(
			'title' => $section['title'] ?? '',
			'items' => $items,
		);
	}
	update_field( 'booking_sections', $sections, $page_id );
}

// Contact page.
$contact_page = get_page_by_path( 'contact' );
if ( ! $contact_page ) {
	$contact_id = wp_insert_post(
		array(
			'post_type'    => 'page',
			'post_title'   => 'Контакти',
			'post_name'    => 'contact',
			'post_status'  => 'publish',
			'post_content' => '',
		)
	);
	if ( $contact_id && ! is_wp_error( $contact_id ) ) {
		update_post_meta( $contact_id, '_wp_page_template', 'page-contact.php' );
	}
} else {
	update_post_meta( $contact_page->ID, '_wp_page_template', 'page-contact.php' );
}

// Trips.
$trip_files = glob( $react_data . '/trips/*.yaml' );
WP_CLI::log( 'Importing ' . count( $trip_files ) . ' trips...' );

foreach ( $trip_files as $file ) {
	$slug = basename( $file, '.yaml' );
	$trip = kztravel_yaml_parse_file( $file );

	WP_CLI::log( '  → ' . $slug );

	$existing = kztravel_find_trip_post_by_slug( $slug );
	$post_id  = $existing ? $existing->ID : 0;

	$post_id = wp_insert_post(
		array(
			'ID'           => $post_id,
			'post_type'    => 'trip',
			'post_title'   => $trip['name'] ?? $slug,
			'post_name'    => $slug,
			'post_status'  => 'publish',
			'post_content' => trim( $trip['description'] ?? '' ),
		),
		true
	);

	if ( is_wp_error( $post_id ) ) {
		WP_CLI::warning( $post_id->get_error_message() );
		continue;
	}

	if ( ! empty( $trip['country'] ) ) {
		wp_set_object_terms( $post_id, array( $trip['country'] ), 'trip_country', false );
	}

	if ( ! empty( $trip['category'] ) ) {
		$category_term_ids = array();
		foreach ( $trip['category'] as $category ) {
			if ( ! is_string( $category ) || '' === trim( $category ) ) {
				continue;
			}
			$term_id = kztravel_ensure_trip_category_term( trim( $category ) );
			if ( $term_id > 0 ) {
				$category_term_ids[] = $term_id;
			}
		}
		wp_set_object_terms( $post_id, $category_term_ids, 'trip_category', false );
	}

	kztravel_update_trip_field( $post_id, 'trip_duration', $trip['duration'] ?? '' );

	$dates_rows = array();
	foreach ( $trip['dates'] ?? array() as $date ) {
		$dates_rows[] = array(
			'date'                 => $date['date'] ?? '',
			'price'                => $date['price'] ?? 0,
			'price_bgn'            => $date['priceBgn'] ?? 0,
			'discounted_price'     => $date['discountedPrice'] ?? '',
			'discounted_price_bgn' => $date['discountedPriceBgn'] ?? '',
			'status'               => $date['status'] ?? 'available',
		);
	}
	kztravel_update_trip_field( $post_id, 'trip_dates', $dates_rows );

	$itinerary_rows = array();
	foreach ( $trip['itinerary'] ?? array() as $day ) {
		$itinerary_rows[] = array(
			'day'   => $day['day'] ?? 0,
			'title' => trim( $day['title'] ?? '' ),
			'body'  => trim( $day['description'] ?? $day['body'] ?? '' ),
		);
	}
	kztravel_update_trip_field( $post_id, 'trip_itinerary', $itinerary_rows );

	$included_rows = array();
	foreach ( $trip['included'] ?? array() as $item ) {
		$included_rows[] = array(
			'title' => $item['title'] ?? $item['name'] ?? '',
		);
	}
	kztravel_update_trip_field( $post_id, 'trip_included', $included_rows );

	$excluded_rows = array();
	foreach ( $trip['excluded'] ?? array() as $item ) {
		$excluded_rows[] = array(
			'title'     => $item['title'] ?? $item['name'] ?? '',
			'price'     => $item['price'] ?? 0,
			'price_bgn' => $item['priceBgn'] ?? '',
		);
	}
	kztravel_update_trip_field( $post_id, 'trip_excluded', $excluded_rows );

	$thumbnails = $trip['thumbnails'] ?? array();
	if ( ! empty( $thumbnails[0] ) ) {
		$hero_id = kztravel_import_sideload_image( $thumbnails[0], $post_id, $trip['name'] ?? $slug );
		if ( $hero_id ) {
			set_post_thumbnail( $post_id, $hero_id );
		}
	}

	$gallery_ids = array();
	foreach ( $trip['gallery'] ?? array() as $image_url ) {
		$image_id = kztravel_import_sideload_image( $image_url, $post_id, $trip['name'] ?? $slug );
		if ( $image_id ) {
			$gallery_ids[] = $image_id;
		}
	}
	if ( ! empty( $gallery_ids ) ) {
		kztravel_update_trip_field( $post_id, 'trip_gallery', $gallery_ids );
	}
}

flush_rewrite_rules();
WP_CLI::success( 'Import complete.' );
