<?php
defined( 'ABSPATH' ) || exit;

add_action(
	'add_meta_boxes',
	function () {
		if ( kztravel_uses_acf_trip_fields() ) {
			return;
		}

		add_meta_box(
			'kztravel_trip_details',
			'Детайли за екскурзията',
			'kztravel_render_trip_details_meta_box',
			'trip',
			'normal',
			'high'
		);
	}
);

add_action(
	'admin_enqueue_scripts',
	function ( $hook ) {
		if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( ! $screen || 'trip' !== $screen->post_type || kztravel_uses_acf_trip_fields() ) {
			return;
		}

		wp_enqueue_style(
			'kztravel-trip-meta',
			KZTRAVEL_URI . '/assets/css/trip-meta-admin.css',
			array(),
			KZTRAVEL_VERSION
		);

		wp_enqueue_script(
			'kztravel-trip-meta',
			KZTRAVEL_URI . '/assets/js/trip-meta-boxes.js',
			array(),
			KZTRAVEL_VERSION,
			true
		);
	}
);

add_action(
	'save_post_trip',
	function ( int $post_id ) {
		if ( kztravel_uses_acf_trip_fields() ) {
			return;
		}

		if ( ! isset( $_POST['kztravel_trip_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['kztravel_trip_meta_nonce'] ) ), 'kztravel_save_trip_meta' ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$duration = isset( $_POST['kztravel_trip_duration'] )
			? sanitize_text_field( wp_unslash( $_POST['kztravel_trip_duration'] ) )
			: '';
		kztravel_update_trip_field( $post_id, 'trip_duration', $duration );

		$dates = array();
		if ( isset( $_POST['kztravel_trip_dates'] ) && is_array( $_POST['kztravel_trip_dates'] ) ) {
			foreach ( wp_unslash( $_POST['kztravel_trip_dates'] ) as $row ) {
				if ( ! is_array( $row ) ) {
					continue;
				}
				$date = sanitize_text_field( $row['date'] ?? '' );
				if ( '' === $date ) {
					continue;
				}
				$status = sanitize_text_field( $row['status'] ?? 'available' );
				if ( ! in_array( $status, KZTRAVEL_VALID_STATUSES, true ) ) {
					$status = 'available';
				}
				$dates[] = array(
					'date'                 => $date,
					'price'                => isset( $row['price'] ) && '' !== $row['price'] ? (float) $row['price'] : 0,
					'price_bgn'            => isset( $row['price_bgn'] ) && '' !== $row['price_bgn'] ? (float) $row['price_bgn'] : 0,
					'discounted_price'     => isset( $row['discounted_price'] ) && '' !== $row['discounted_price'] ? (float) $row['discounted_price'] : '',
					'discounted_price_bgn' => isset( $row['discounted_price_bgn'] ) && '' !== $row['discounted_price_bgn'] ? (float) $row['discounted_price_bgn'] : '',
					'status'               => $status,
				);
			}
		}
		kztravel_update_trip_field( $post_id, 'trip_dates', $dates );

		$itinerary = array();
		if ( isset( $_POST['kztravel_trip_itinerary'] ) && is_array( $_POST['kztravel_trip_itinerary'] ) ) {
			foreach ( wp_unslash( $_POST['kztravel_trip_itinerary'] ) as $row ) {
				if ( ! is_array( $row ) ) {
					continue;
				}
				$title = sanitize_text_field( $row['title'] ?? '' );
				$body  = sanitize_textarea_field( $row['body'] ?? '' );
				if ( '' === $title && '' === $body ) {
					continue;
				}
				$itinerary[] = array(
					'title' => $title,
					'body'  => $body,
				);
			}
		}
		kztravel_update_trip_field( $post_id, 'trip_itinerary', $itinerary );

		$included = array();
		if ( isset( $_POST['kztravel_trip_included'] ) && is_array( $_POST['kztravel_trip_included'] ) ) {
			foreach ( wp_unslash( $_POST['kztravel_trip_included'] ) as $row ) {
				if ( ! is_array( $row ) ) {
					continue;
				}
				$title = sanitize_text_field( $row['title'] ?? '' );
				if ( '' === $title ) {
					continue;
				}
				$included[] = array( 'title' => $title );
			}
		}
		kztravel_update_trip_field( $post_id, 'trip_included', $included );

		$excluded = array();
		if ( isset( $_POST['kztravel_trip_excluded'] ) && is_array( $_POST['kztravel_trip_excluded'] ) ) {
			foreach ( wp_unslash( $_POST['kztravel_trip_excluded'] ) as $row ) {
				if ( ! is_array( $row ) ) {
					continue;
				}
				$title = sanitize_text_field( $row['title'] ?? '' );
				if ( '' === $title ) {
					continue;
				}
				$excluded[] = array(
					'title'     => $title,
					'price'     => isset( $row['price'] ) && '' !== $row['price'] ? (float) $row['price'] : 0,
					'price_bgn' => isset( $row['price_bgn'] ) && '' !== $row['price_bgn'] ? (float) $row['price_bgn'] : '',
				);
			}
		}
		kztravel_update_trip_field( $post_id, 'trip_excluded', $excluded );
	}
);

function kztravel_render_trip_details_meta_box( WP_Post $post ): void {
	wp_nonce_field( 'kztravel_save_trip_meta', 'kztravel_trip_meta_nonce' );

	$duration  = (string) kztravel_get_trip_field( $post->ID, 'trip_duration', '' );
	$dates     = kztravel_get_trip_field( $post->ID, 'trip_dates', array() );
	$itinerary = kztravel_get_trip_field( $post->ID, 'trip_itinerary', array() );
	$included  = kztravel_get_trip_field( $post->ID, 'trip_included', array() );
	$excluded  = kztravel_get_trip_field( $post->ID, 'trip_excluded', array() );

	if ( ! is_array( $dates ) ) {
		$dates = array();
	}
	if ( ! is_array( $itinerary ) ) {
		$itinerary = array();
	}
	if ( ! is_array( $included ) ) {
		$included = array();
	}
	if ( ! is_array( $excluded ) ) {
		$excluded = array();
	}
	?>
	<div class="kztravel-trip-meta">
		<p class="kztravel-trip-meta__field">
			<label for="kztravel_trip_duration"><strong>Продължителност</strong></label>
			<input
				type="text"
				id="kztravel_trip_duration"
				name="kztravel_trip_duration"
				value="<?php echo esc_attr( $duration ); ?>"
				class="widefat"
				placeholder="напр. 3 дни"
			/>
		</p>

		<?php kztravel_render_trip_dates_meta_section( $dates ); ?>
		<?php kztravel_render_trip_itinerary_meta_section( $itinerary ); ?>
		<?php kztravel_render_trip_included_meta_section( $included ); ?>
		<?php kztravel_render_trip_excluded_meta_section( $excluded ); ?>
	</div>
	<?php
}

function kztravel_render_trip_dates_meta_section( array $dates ): void {
	?>
	<div class="kztravel-trip-meta__section" data-repeater="dates">
		<div class="kztravel-trip-meta__section-header">
			<h3>Дати и цени</h3>
			<button type="button" class="button button-secondary kztravel-trip-meta__add">Добави дата</button>
		</div>
		<table class="widefat kztravel-trip-meta__table">
			<thead>
				<tr>
					<th>Дата</th>
					<th>Цена (EUR)</th>
					<th>Цена (BGN)</th>
					<th>Намалена (EUR)</th>
					<th>Намалена (BGN)</th>
					<th>Статус</th>
					<th></th>
				</tr>
			</thead>
			<tbody>
				<?php
				if ( empty( $dates ) ) {
					kztravel_render_trip_date_row( 0, array() );
				} else {
					foreach ( $dates as $index => $row ) {
						kztravel_render_trip_date_row( (int) $index, is_array( $row ) ? $row : array() );
					}
				}
				?>
			</tbody>
		</table>
		<template class="kztravel-trip-meta__template">
			<?php kztravel_render_trip_date_row( '__INDEX__', array() ); ?>
		</template>
	</div>
	<?php
}

function kztravel_render_trip_date_row( $index, array $row ): void {
	$statuses = array(
		'available' => 'Налична',
		'lastSpots' => 'Последни места',
		'soldout'   => 'Изчерпано',
	);
	$status   = $row['status'] ?? 'available';
	?>
	<tr class="kztravel-trip-meta__row">
		<td>
			<input
				type="date"
				name="kztravel_trip_dates[<?php echo esc_attr( (string) $index ); ?>][date]"
				value="<?php echo esc_attr( $row['date'] ?? '' ); ?>"
			/>
		</td>
		<td>
			<input
				type="number"
				step="0.01"
				min="0"
				name="kztravel_trip_dates[<?php echo esc_attr( (string) $index ); ?>][price]"
				value="<?php echo esc_attr( isset( $row['price'] ) ? (string) $row['price'] : '' ); ?>"
			/>
		</td>
		<td>
			<input
				type="number"
				step="0.01"
				min="0"
				name="kztravel_trip_dates[<?php echo esc_attr( (string) $index ); ?>][price_bgn]"
				value="<?php echo esc_attr( isset( $row['price_bgn'] ) ? (string) $row['price_bgn'] : '' ); ?>"
			/>
		</td>
		<td>
			<input
				type="number"
				step="0.01"
				min="0"
				name="kztravel_trip_dates[<?php echo esc_attr( (string) $index ); ?>][discounted_price]"
				value="<?php echo esc_attr( isset( $row['discounted_price'] ) && '' !== $row['discounted_price'] ? (string) $row['discounted_price'] : '' ); ?>"
			/>
		</td>
		<td>
			<input
				type="number"
				step="0.01"
				min="0"
				name="kztravel_trip_dates[<?php echo esc_attr( (string) $index ); ?>][discounted_price_bgn]"
				value="<?php echo esc_attr( isset( $row['discounted_price_bgn'] ) && '' !== $row['discounted_price_bgn'] ? (string) $row['discounted_price_bgn'] : '' ); ?>"
			/>
		</td>
		<td>
			<select name="kztravel_trip_dates[<?php echo esc_attr( (string) $index ); ?>][status]">
				<?php foreach ( $statuses as $value => $label ) : ?>
					<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $status, $value ); ?>>
						<?php echo esc_html( $label ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</td>
		<td>
			<button type="button" class="button-link-delete kztravel-trip-meta__remove" aria-label="Премахни ред">&times;</button>
		</td>
	</tr>
	<?php
}

function kztravel_render_trip_itinerary_meta_section( array $itinerary ): void {
	?>
	<div class="kztravel-trip-meta__section" data-repeater="itinerary">
		<div class="kztravel-trip-meta__section-header">
			<h3>Дневен маршрут</h3>
			<button type="button" class="button button-secondary kztravel-trip-meta__add">Добави ден</button>
		</div>
		<div class="kztravel-trip-meta__cards">
			<?php
			if ( empty( $itinerary ) ) {
				kztravel_render_trip_itinerary_row( 0, array() );
			} else {
				foreach ( $itinerary as $index => $row ) {
					kztravel_render_trip_itinerary_row( (int) $index, is_array( $row ) ? $row : array() );
				}
			}
			?>
		</div>
		<template class="kztravel-trip-meta__template">
			<?php kztravel_render_trip_itinerary_row( '__INDEX__', array() ); ?>
		</template>
	</div>
	<?php
}

function kztravel_render_trip_itinerary_row( $index, array $row ): void {
	?>
	<div class="kztravel-trip-meta__card kztravel-trip-meta__row">
		<p>
			<label>Заглавие</label>
			<input
				type="text"
				class="widefat"
				name="kztravel_trip_itinerary[<?php echo esc_attr( (string) $index ); ?>][title]"
				value="<?php echo esc_attr( $row['title'] ?? '' ); ?>"
			/>
		</p>
		<p>
			<label>Описание</label>
			<textarea
				class="widefat"
				rows="3"
				name="kztravel_trip_itinerary[<?php echo esc_attr( (string) $index ); ?>][body]"
			><?php echo esc_textarea( $row['body'] ?? $row['description'] ?? '' ); ?></textarea>
		</p>
		<button type="button" class="button-link-delete kztravel-trip-meta__remove">Премахни ден</button>
	</div>
	<?php
}

function kztravel_render_trip_included_meta_section( array $included ): void {
	kztravel_render_trip_simple_list_meta_section(
		'included',
		'Включени услуги',
		'Добави услуга',
		$included,
		false
	);
}

function kztravel_render_trip_excluded_meta_section( array $excluded ): void {
	kztravel_render_trip_simple_list_meta_section(
		'excluded',
		'Невключени услуги',
		'Добави услуга',
		$excluded,
		true
	);
}

function kztravel_render_trip_simple_list_meta_section( string $type, string $title, string $add_label, array $rows, bool $with_prices ): void {
	?>
	<div class="kztravel-trip-meta__section" data-repeater="<?php echo esc_attr( $type ); ?>">
		<div class="kztravel-trip-meta__section-header">
			<h3><?php echo esc_html( $title ); ?></h3>
			<button type="button" class="button button-secondary kztravel-trip-meta__add"><?php echo esc_html( $add_label ); ?></button>
		</div>
		<table class="widefat kztravel-trip-meta__table">
			<thead>
				<tr>
					<th>Услуга</th>
					<?php if ( $with_prices ) : ?>
						<th>Цена (EUR)</th>
						<th>Цена (BGN)</th>
					<?php endif; ?>
					<th></th>
				</tr>
			</thead>
			<tbody>
				<?php
				if ( empty( $rows ) ) {
					kztravel_render_trip_simple_list_row( $type, 0, array(), $with_prices );
				} else {
					foreach ( $rows as $index => $row ) {
						kztravel_render_trip_simple_list_row( $type, (int) $index, is_array( $row ) ? $row : array(), $with_prices );
					}
				}
				?>
			</tbody>
		</table>
		<template class="kztravel-trip-meta__template">
			<?php kztravel_render_trip_simple_list_row( $type, '__INDEX__', array(), $with_prices ); ?>
		</template>
	</div>
	<?php
}

function kztravel_render_trip_simple_list_row( string $type, $index, array $row, bool $with_prices ): void {
	$field_prefix = 'kztravel_trip_' . $type;
	$title        = $row['title'] ?? $row['name'] ?? '';
	?>
	<tr class="kztravel-trip-meta__row">
		<td>
			<input
				type="text"
				class="widefat"
				name="<?php echo esc_attr( $field_prefix ); ?>[<?php echo esc_attr( (string) $index ); ?>][title]"
				value="<?php echo esc_attr( $title ); ?>"
			/>
		</td>
		<?php if ( $with_prices ) : ?>
			<td>
				<input
					type="number"
					step="0.01"
					min="0"
					name="<?php echo esc_attr( $field_prefix ); ?>[<?php echo esc_attr( (string) $index ); ?>][price]"
					value="<?php echo esc_attr( isset( $row['price'] ) ? (string) $row['price'] : '' ); ?>"
				/>
			</td>
			<td>
				<input
					type="number"
					step="0.01"
					min="0"
					name="<?php echo esc_attr( $field_prefix ); ?>[<?php echo esc_attr( (string) $index ); ?>][price_bgn]"
					value="<?php echo esc_attr( isset( $row['price_bgn'] ) ? (string) $row['price_bgn'] : '' ); ?>"
				/>
			</td>
		<?php endif; ?>
		<td>
			<button type="button" class="button-link-delete kztravel-trip-meta__remove" aria-label="Премахни ред">&times;</button>
		</td>
	</tr>
	<?php
}
