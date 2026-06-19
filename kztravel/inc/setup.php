<?php
defined( 'ABSPATH' ) || exit;

add_action(
	'after_setup_theme',
	function () {
		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		register_nav_menus(
			array(
				'primary' => __( 'Primary Menu', 'kztravel' ),
			)
		);
	}
);

add_filter(
	'document_title_parts',
	function ( $parts ) {
		$site_title = kztravel_get_option( 'site_title' );
		if ( $site_title && ! is_front_page() ) {
			$parts['site'] = $site_title;
		} elseif ( $site_title && is_front_page() ) {
			$parts['title'] = $site_title;
			unset( $parts['site'] );
		}
		return $parts;
	}
);

add_action(
	'wp_enqueue_scripts',
	function () {
		wp_enqueue_style( 'kztravel', get_stylesheet_uri(), array(), KZTRAVEL_VERSION );

		wp_enqueue_script(
			'kztravel-theme-toggle',
			KZTRAVEL_URI . '/assets/js/theme-toggle.js',
			array(),
			KZTRAVEL_VERSION,
			array(
				'strategy'  => 'defer',
				'in_footer' => false,
			)
		);

		wp_enqueue_script(
			'kztravel-filters',
			KZTRAVEL_URI . '/assets/js/filters.js',
			array(),
			KZTRAVEL_VERSION,
			array(
				'strategy'  => 'defer',
				'in_footer' => true,
			)
		);

		wp_enqueue_script(
			'kztravel-mobile-menu',
			KZTRAVEL_URI . '/assets/js/mobile-menu.js',
			array(),
			KZTRAVEL_VERSION,
			array(
				'strategy'  => 'defer',
				'in_footer' => true,
			)
		);

		if ( is_front_page() ) {
			$trip_query = new WP_Query(
				array(
					'post_type'      => 'trip',
					'posts_per_page' => -1,
					'orderby'        => 'title',
					'order'          => 'ASC',
					'fields'         => 'ids',
				)
			);
			$trips = array();
			foreach ( $trip_query->posts as $post_id ) {
				$trips[] = kztravel_enrich_trip( $post_id );
			}
			wp_reset_postdata();

			$filter_index = kztravel_build_filter_index( $trips );

			wp_localize_script(
				'kztravel-filters',
				'kztravelFilterData',
				array(
					'filterIndex' => $filter_index,
					'priceRanges' => $filter_index['priceRanges'],
					'strings'     => array(
						'filterCountry'  => kztravel_ui( 'filterCountry' ),
						'filterPrice'    => kztravel_ui( 'filterPrice' ),
						'filterDuration' => kztravel_ui( 'filterDuration' ),
						'filterCategory' => kztravel_ui( 'filterCategory' ),
						'filterDiscount' => kztravel_ui( 'filterDiscount' ),
						'filtersHeading' => kztravel_ui( 'filtersHeading' ),
						'all'            => kztravel_ui( 'all' ),
						'clear'          => kztravel_ui( 'clear' ),
						'filterByCountry'  => kztravel_ui( 'filterByCountry' ),
						'filterByPrice'    => kztravel_ui( 'filterByPrice' ),
						'filterByDuration' => kztravel_ui( 'filterByDuration' ),
						'filterByCategory' => kztravel_ui( 'filterByCategory' ),
					),
					'countryLabels' => $GLOBALS['kztravel_country_labels'],
				)
			);
		}
	}
);
