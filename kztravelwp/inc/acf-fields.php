<?php
defined( 'ABSPATH' ) || exit;

add_filter(
	'acf/settings/save_json',
	function () {
		return KZTRAVEL_DIR . '/acf-json';
	}
);

add_filter(
	'acf/settings/load_json',
	function ( $paths ) {
		$paths[] = KZTRAVEL_DIR . '/acf-json';
		return $paths;
	}
);

add_action(
	'acf/init',
	function () {
		if ( ! function_exists( 'acf_add_options_page' ) || ! function_exists( 'acf_add_local_field_group' ) ) {
			return;
		}

		acf_add_options_page(
			array(
				'page_title' => __( 'Site Settings', 'kztravel' ),
				'menu_title' => __( 'Site Settings', 'kztravel' ),
				'menu_slug'  => 'kztravel-settings',
				'capability' => 'edit_theme_options',
				'icon_url'   => 'dashicons-admin-site-alt3',
				'position'   => 59,
			)
		);

		acf_add_local_field_group(
			array(
				'key'    => 'group_kztravel_trip_details',
				'title'  => 'Детайли за екскурзията',
				'fields' => array(
					array(
						'key'   => 'field_trip_duration',
						'label' => 'Продължителност',
						'name'  => 'trip_duration',
						'type'  => 'text',
						'placeholder' => 'напр. 3 дни',
					),
					array(
						'key'          => 'field_trip_dates',
						'label'        => 'Дати',
						'name'         => 'trip_dates',
						'type'         => 'repeater',
						'layout'       => 'block',
						'button_label' => 'Добави дата',
						'sub_fields'   => array(
							array(
								'key'            => 'field_trip_date',
								'label'          => 'Дата',
								'name'           => 'date',
								'type'           => 'date_picker',
								'display_format' => 'd/m/Y',
								'return_format'  => 'Y-m-d',
							),
							array(
								'key'   => 'field_trip_price',
								'label' => 'Цена (EUR)',
								'name'  => 'price',
								'type'  => 'number',
							),
							array(
								'key'   => 'field_trip_price_bgn',
								'label' => 'Цена (BGN)',
								'name'  => 'price_bgn',
								'type'  => 'number',
							),
							array(
								'key'   => 'field_trip_discounted_price',
								'label' => 'Намалена цена (EUR)',
								'name'  => 'discounted_price',
								'type'  => 'number',
							),
							array(
								'key'   => 'field_trip_discounted_price_bgn',
								'label' => 'Намалена цена (BGN)',
								'name'  => 'discounted_price_bgn',
								'type'  => 'number',
							),
							array(
								'key'     => 'field_trip_status',
								'label'   => 'Статус',
								'name'    => 'status',
								'type'    => 'select',
								'choices' => array(
									'available'  => 'Налична',
									'lastSpots'  => 'Последни места',
									'soldout'    => 'Изчерпано',
								),
								'default_value' => 'available',
							),
						),
					),
					array(
						'key'           => 'field_trip_gallery',
						'label'         => 'Галерия',
						'name'          => 'trip_gallery',
						'type'          => 'gallery',
						'return_format' => 'array',
					),
					array(
						'key'          => 'field_trip_itinerary',
						'label'        => 'Маршрут',
						'name'         => 'trip_itinerary',
						'type'         => 'repeater',
						'layout'       => 'block',
						'button_label' => 'Добави ден',
						'sub_fields'   => array(
							array(
								'key'   => 'field_itinerary_day',
								'label' => 'Ден',
								'name'  => 'day',
								'type'  => 'number',
							),
							array(
								'key'   => 'field_itinerary_title',
								'label' => 'Заглавие',
								'name'  => 'title',
								'type'  => 'text',
							),
							array(
								'key'          => 'field_itinerary_body',
								'label'        => 'Описание',
								'name'         => 'body',
								'type'         => 'textarea',
								'rows'         => 4,
								'new_lines'    => 'br',
							),
						),
					),
					array(
						'key'          => 'field_trip_included',
						'label'        => 'Включено',
						'name'         => 'trip_included',
						'type'         => 'repeater',
						'layout'       => 'table',
						'button_label' => 'Добави услуга',
						'sub_fields'   => kztravel_acf_included_subfields(),
					),
					array(
						'key'          => 'field_trip_excluded',
						'label'        => 'Не е включено',
						'name'         => 'trip_excluded',
						'type'         => 'repeater',
						'layout'       => 'table',
						'button_label' => 'Добави услуга',
						'sub_fields'   => kztravel_acf_excluded_subfields(),
					),
				),
				'location' => array(
					array(
						array(
							'param'    => 'post_type',
							'operator' => '==',
							'value'    => 'trip',
						),
					),
				),
			)
		);

		acf_add_local_field_group(
			array(
				'key'    => 'group_kztravel_site_settings',
				'title'  => 'Site Settings',
				'fields' => array(
					array(
						'key'   => 'field_site_title',
						'label' => 'Site Title',
						'name'  => 'site_title',
						'type'  => 'text',
					),
					array(
						'key'           => 'field_site_favicon',
						'label'         => 'Favicon',
						'name'          => 'site_favicon',
						'type'          => 'image',
						'return_format' => 'array',
					),
					array(
						'key'           => 'field_site_background',
						'label'         => 'Background Image',
						'name'          => 'site_background',
						'type'          => 'image',
						'return_format' => 'array',
					),
					array(
						'key'   => 'field_contact_phone',
						'label' => 'Phone',
						'name'  => 'contact_phone',
						'type'  => 'text',
					),
					array(
						'key'   => 'field_contact_email',
						'label' => 'Email',
						'name'  => 'contact_email',
						'type'  => 'email',
					),
					array(
						'key'   => 'field_contact_address',
						'label' => 'Address',
						'name'  => 'contact_address',
						'type'  => 'textarea',
						'rows'  => 2,
					),
					array(
						'key'   => 'field_contact_map_embed_url',
						'label' => 'Map Embed URL',
						'name'  => 'contact_map_embed_url',
						'type'  => 'url',
					),
					array(
						'key'          => 'field_contact_working_hours',
						'label'        => 'Working Hours',
						'name'         => 'contact_working_hours',
						'type'         => 'repeater',
						'layout'       => 'table',
						'button_label' => 'Add Row',
						'min'          => 7,
						'max'          => 7,
						'sub_fields'   => array(
							array(
								'key'   => 'field_working_hours',
								'label' => 'Hours',
								'name'  => 'hours',
								'type'  => 'text',
							),
						),
					),
					array(
						'key'   => 'field_bank_name',
						'label' => 'Bank Name',
						'name'  => 'bank_name',
						'type'  => 'text',
					),
					array(
						'key'   => 'field_bank_iban',
						'label' => 'IBAN',
						'name'  => 'bank_iban',
						'type'  => 'text',
					),
					array(
						'key'   => 'field_bank_holder',
						'label' => 'Account Holder',
						'name'  => 'bank_holder',
						'type'  => 'text',
					),
					array(
						'key'   => 'field_footer_registration',
						'label' => 'Footer Registration',
						'name'  => 'footer_registration',
						'type'  => 'textarea',
						'rows'  => 2,
					),
					array(
						'key'   => 'field_footer_company',
						'label' => 'Footer Company',
						'name'  => 'footer_company',
						'type'  => 'text',
					),
				),
				'location' => array(
					array(
						array(
							'param'    => 'options_page',
							'operator' => '==',
							'value'    => 'kztravel-settings',
						),
					),
				),
			)
		);

		acf_add_local_field_group(
			array(
				'key'    => 'group_kztravel_booking',
				'title'  => 'Booking Page Content',
				'fields' => array(
					array(
						'key'   => 'field_booking_intro',
						'label' => 'Intro',
						'name'  => 'booking_intro',
						'type'  => 'textarea',
						'rows'  => 4,
					),
					array(
						'key'          => 'field_booking_sections',
						'label'        => 'Sections',
						'name'         => 'booking_sections',
						'type'         => 'repeater',
						'layout'       => 'block',
						'button_label' => 'Add Section',
						'sub_fields'   => array(
							array(
								'key'   => 'field_booking_section_title',
								'label' => 'Title',
								'name'  => 'title',
								'type'  => 'text',
							),
							array(
								'key'          => 'field_booking_section_items',
								'label'        => 'Items',
								'name'         => 'items',
								'type'         => 'repeater',
								'layout'       => 'table',
								'button_label' => 'Add Item',
								'sub_fields'   => array(
									array(
										'key'   => 'field_booking_item_text',
										'label' => 'Item',
										'name'  => 'text',
										'type'  => 'text',
									),
								),
							),
						),
					),
				),
				'location' => array(
					array(
						array(
							'param'    => 'page_template',
							'operator' => '==',
							'value'    => 'page-booking.php',
						),
					),
					array(
						array(
							'param'    => 'page',
							'operator' => '==',
							'value'    => 'booking',
						),
					),
				),
			)
		);
	}
);

function kztravel_acf_included_subfields(): array {
	return array(
		array(
			'key'   => 'field_included_title',
			'label' => 'Услуга',
			'name'  => 'title',
			'type'  => 'text',
		),
	);
}

function kztravel_acf_excluded_subfields(): array {
	return array(
		array(
			'key'   => 'field_excluded_title',
			'label' => 'Услуга',
			'name'  => 'title',
			'type'  => 'text',
		),
		array(
			'key'   => 'field_excluded_price',
			'label' => 'Цена (EUR)',
			'name'  => 'price',
			'type'  => 'number',
		),
		array(
			'key'   => 'field_excluded_price_bgn',
			'label' => 'Цена (BGN)',
			'name'  => 'price_bgn',
			'type'  => 'number',
		),
	);
}

function kztravel_get_booking_info( int $page_id = 0 ): array {
	if ( ! $page_id ) {
		$page = get_page_by_path( 'booking' );
		$page_id = $page ? $page->ID : 0;
	}

	$title = $page_id ? get_the_title( $page_id ) : kztravel_ui( 'navBooking' );
	$intro = function_exists( 'get_field' ) ? (string) ( get_field( 'booking_intro', $page_id ) ?: '' ) : '';
	$sections_raw = function_exists( 'get_field' ) ? ( get_field( 'booking_sections', $page_id ) ?: array() ) : array();
	$sections = array();

	if ( is_array( $sections_raw ) ) {
		foreach ( $sections_raw as $section ) {
			$items = array();
			if ( ! empty( $section['items'] ) && is_array( $section['items'] ) ) {
				foreach ( $section['items'] as $item ) {
					$items[] = is_array( $item ) ? ( $item['text'] ?? '' ) : (string) $item;
				}
			}
			$sections[] = array(
				'title' => $section['title'] ?? '',
				'items' => $items,
			);
		}
	}

	return array(
		'title'    => $title,
		'intro'    => $intro,
		'sections' => $sections,
	);
}
