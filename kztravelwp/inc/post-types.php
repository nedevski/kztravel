<?php
defined( 'ABSPATH' ) || exit;

add_action(
	'init',
	function () {
		register_post_type(
			'trip',
			array(
				'labels'       => array(
					'name'                  => 'Екскурзии',
					'singular_name'         => 'Екскурзия',
					'menu_name'             => 'Екскурзии',
					'name_admin_bar'        => 'Екскурзия',
					'add_new'               => 'Добави',
					'add_new_item'          => 'Добави нова екскурзия',
					'new_item'              => 'Нова екскурзия',
					'edit_item'             => 'Редактирай екскурзия',
					'view_item'             => 'Виж екскурзия',
					'all_items'             => 'Всички екскурзии',
					'search_items'          => 'Търси екскурзии',
					'not_found'             => 'Няма намерени екскурзии',
					'not_found_in_trash'    => 'Няма намерени екскурзии в кошчето',
					'parent_item_colon'     => 'Родителска екскурзия:',
					'archives'              => 'Архив на екскурзии',
					'insert_into_item'      => 'Вмъкни в екскурзия',
					'uploaded_to_this_item' => 'Качено към тази екскурзия',
					'filter_items_list'     => 'Филтрирай списъка с екскурзии',
					'items_list_navigation' => 'Навигация в списъка с екскурзии',
					'items_list'            => 'Списък с екскурзии',
					'featured_image'        => 'Основно изображение',
					'set_featured_image'    => 'Задай основно изображение',
					'remove_featured_image' => 'Премахни основно изображение',
					'use_featured_image'    => 'Използвай като основно изображение',
				),
				'public'       => true,
				'has_archive'  => false,
				'rewrite'      => array( 'slug' => 'trips' ),
				'menu_icon'    => 'dashicons-palmtree',
				'supports'     => array( 'title' ),
				'show_in_rest' => false,
			)
		);

		register_taxonomy(
			'trip_country',
			'trip',
			array(
				'labels'       => array(
					'name'          => 'Държави',
					'singular_name' => 'Държава',
					'search_items'  => 'Търси държави',
					'all_items'     => 'Всички държави',
					'edit_item'     => 'Редактирай държава',
					'update_item'   => 'Обнови държава',
					'add_new_item'  => 'Добави нова държава',
					'new_item_name' => 'Име на нова държава',
					'menu_name'     => 'Държави',
				),
				'public'       => true,
				'hierarchical' => false,
				'rewrite'      => array( 'slug' => 'country' ),
				'show_in_rest' => true,
			)
		);

		register_taxonomy(
			'trip_category',
			'trip',
			array(
				'labels'       => array(
					'name'          => 'Категории',
					'singular_name' => 'Категория',
					'search_items'  => 'Търси категории',
					'all_items'     => 'Всички категории',
					'edit_item'     => 'Редактирай категория',
					'update_item'   => 'Обнови категория',
					'add_new_item'  => 'Добави нова категория',
					'new_item_name' => 'Име на нова категория',
					'menu_name'     => 'Категории',
				),
				'public'       => true,
				'hierarchical' => false,
				'rewrite'      => array( 'slug' => 'category' ),
				'show_in_rest' => true,
			)
		);
	}
);

add_action(
	'set_object_terms',
	function ( $object_id, $terms, $tt_ids, $taxonomy ) {
		if ( 'trip_country' !== $taxonomy || 'trip' !== get_post_type( $object_id ) ) {
			return;
		}
		if ( count( $terms ) <= 1 ) {
			return;
		}
		$keep = array_slice( $terms, 0, 1 );
		wp_set_object_terms( $object_id, $keep, $taxonomy, false );
	},
	10,
	4
);

add_action(
	'after_switch_theme',
	function () {
		flush_rewrite_rules();
	}
);

add_filter(
	'enter_title_here',
	function ( $title, $post ) {
		if ( $post instanceof WP_Post && 'trip' === $post->post_type ) {
			return 'Име на екскурзията';
		}
		return $title;
	},
	10,
	2
);

add_action(
	'admin_head-post.php',
	'kztravel_trip_admin_labels'
);
add_action(
	'admin_head-post-new.php',
	'kztravel_trip_admin_labels'
);

function kztravel_trip_admin_labels(): void {
	$screen = get_current_screen();
	if ( ! $screen || 'trip' !== $screen->post_type ) {
		return;
	}
	add_filter( 'gettext', 'kztravel_translate_trip_admin_strings', 10, 3 );
}

function kztravel_translate_trip_admin_strings( string $translation, string $text, string $domain ): string {
	if ( 'default' !== $domain ) {
		return $translation;
	}

	$strings = array(
		'Excerpt'               => 'Кратко описание',
		'Featured image'        => 'Основно изображение',
		'Set featured image'    => 'Задай основно изображение',
		'Remove featured image' => 'Премахни основно изображение',
		'Use as featured image' => 'Използвай като основно изображение',
		'Publish'               => 'Публикувай',
		'Update'                => 'Обнови',
		'Save Draft'            => 'Запази чернова',
		'Slug'                  => 'URL адрес',
	);

	return $strings[ $text ] ?? $translation;
}
