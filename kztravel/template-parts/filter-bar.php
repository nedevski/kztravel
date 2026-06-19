<?php
defined( 'ABSPATH' ) || exit;

$filter_index = $args['filter_index'] ?? array();
$trips        = $args['trips'] ?? array();
?>
<div class="filter-box" aria-label="<?php echo esc_attr( kztravel_ui( 'filtersHeading' ) ); ?>" data-filter-bar>
	<div class="filter-box__header">
		<h2 class="filter-box__title"><?php echo esc_html( kztravel_ui( 'filtersHeading' ) ); ?></h2>
	</div>

	<div class="filter-box__toolbar">
		<div class="filter-box__triggers">
			<?php if ( ! empty( $filter_index['showCountryFilter'] ) ) : ?>
				<button
					type="button"
					class="filter-box__trigger"
					data-panel="country"
					data-short-label="<?php echo esc_attr( kztravel_ui( 'filterCountry' ) ); ?>"
					aria-expanded="false"
				>
					<span class="filter-box__trigger-label filter-box__trigger-label--short">
						<?php echo esc_html( kztravel_ui( 'filterCountry' ) ); ?>
						<span class="filter-box__trigger-dot" aria-hidden="true" hidden></span>
					</span>
					<span class="filter-box__trigger-label filter-box__trigger-label--full"><?php echo esc_html( kztravel_ui( 'filterCountry' ) ); ?></span>
					<svg class="filter-box__chevron" viewBox="0 0 16 16" fill="none" aria-hidden="true">
						<path d="M4 6l4 4 4-4" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" />
					</svg>
				</button>
			<?php endif; ?>

			<?php if ( ! empty( $filter_index['showPriceFilter'] ) ) : ?>
				<button
					type="button"
					class="filter-box__trigger"
					data-panel="price"
					data-short-label="<?php echo esc_attr( kztravel_ui( 'filterPrice' ) ); ?>"
					aria-expanded="false"
				>
					<span class="filter-box__trigger-label filter-box__trigger-label--short">
						<?php echo esc_html( kztravel_ui( 'filterPrice' ) ); ?>
						<span class="filter-box__trigger-dot" aria-hidden="true" hidden></span>
					</span>
					<span class="filter-box__trigger-label filter-box__trigger-label--full"><?php echo esc_html( kztravel_ui( 'filterPrice' ) ); ?></span>
					<svg class="filter-box__chevron" viewBox="0 0 16 16" fill="none" aria-hidden="true">
						<path d="M4 6l4 4 4-4" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" />
					</svg>
				</button>
			<?php endif; ?>

			<?php if ( ! empty( $filter_index['showDurationFilter'] ) ) : ?>
				<button
					type="button"
					class="filter-box__trigger"
					data-panel="duration"
					data-short-label="<?php echo esc_attr( kztravel_ui( 'filterDuration' ) ); ?>"
					aria-expanded="false"
				>
					<span class="filter-box__trigger-label filter-box__trigger-label--short">
						<?php echo esc_html( kztravel_ui( 'filterDuration' ) ); ?>
						<span class="filter-box__trigger-dot" aria-hidden="true" hidden></span>
					</span>
					<span class="filter-box__trigger-label filter-box__trigger-label--full"><?php echo esc_html( kztravel_ui( 'filterDuration' ) ); ?></span>
					<svg class="filter-box__chevron" viewBox="0 0 16 16" fill="none" aria-hidden="true">
						<path d="M4 6l4 4 4-4" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" />
					</svg>
				</button>
			<?php endif; ?>

			<?php if ( ! empty( $filter_index['showCategoryFilter'] ) ) : ?>
				<button
					type="button"
					class="filter-box__trigger"
					data-panel="category"
					data-short-label="<?php echo esc_attr( kztravel_ui( 'filterCategory' ) ); ?>"
					aria-expanded="false"
				>
					<span class="filter-box__trigger-label filter-box__trigger-label--short">
						<?php echo esc_html( kztravel_ui( 'filterCategory' ) ); ?>
						<span class="filter-box__trigger-dot" aria-hidden="true" hidden></span>
					</span>
					<span class="filter-box__trigger-label filter-box__trigger-label--full"><?php echo esc_html( kztravel_ui( 'filterCategory' ) ); ?></span>
					<svg class="filter-box__chevron" viewBox="0 0 16 16" fill="none" aria-hidden="true">
						<path d="M4 6l4 4 4-4" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" />
					</svg>
				</button>
			<?php endif; ?>

			<?php if ( ! empty( $filter_index['showDiscountFilter'] ) ) : ?>
				<button
					type="button"
					class="filter-box__trigger filter-box__discount"
					data-panel="discount"
					aria-pressed="false"
				>
					<span class="filter-box__trigger-label"><?php echo esc_html( kztravel_ui( 'filterDiscount' ) ); ?></span>
				</button>
			<?php endif; ?>
		</div>

		<button type="button" class="filter-box__clear" data-filter-clear disabled>
			<?php echo esc_html( kztravel_ui( 'clear' ) ); ?>
		</button>
	</div>

	<div class="filter-box__panel-wrap">
		<div class="filter-box__panel-clip">
		<?php if ( ! empty( $filter_index['showCountryFilter'] ) ) : ?>
			<div class="filter-box__panel" data-panel-content="country" role="group" aria-label="<?php echo esc_attr( kztravel_ui( 'filterByCountry' ) ); ?>" hidden>
				<button type="button" class="chip" data-filter-country=""><?php echo esc_html( kztravel_ui( 'all' ) ); ?> <span class="chip__count" data-count-for="country-all"></span></button>
				<?php foreach ( $filter_index['countries'] as $country ) : ?>
					<button type="button" class="chip" data-filter-country="<?php echo esc_attr( $country ); ?>">
						<?php echo esc_html( kztravel_format_country_label( $country ) ); ?>
						<span class="chip__count" data-count-for="country-<?php echo esc_attr( $country ); ?>"></span>
					</button>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<?php if ( ! empty( $filter_index['showPriceFilter'] ) ) : ?>
			<div class="filter-box__panel" data-panel-content="price" role="group" aria-label="<?php echo esc_attr( kztravel_ui( 'filterByPrice' ) ); ?>" hidden>
				<button type="button" class="chip" data-filter-price=""><?php echo esc_html( kztravel_ui( 'all' ) ); ?> <span class="chip__count" data-count-for="price-all"></span></button>
				<?php foreach ( $filter_index['priceRanges'] as $range ) : ?>
					<button type="button" class="chip" data-filter-price="<?php echo esc_attr( $range['id'] ); ?>" data-price-min="<?php echo esc_attr( (string) $range['min'] ); ?>" data-price-max="<?php echo esc_attr( null === $range['max'] ? '' : (string) $range['max'] ); ?>">
						<?php echo esc_html( $range['label'] ); ?>
						<span class="chip__count" data-count-for="price-<?php echo esc_attr( $range['id'] ); ?>"></span>
					</button>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<?php if ( ! empty( $filter_index['showDurationFilter'] ) ) : ?>
			<div class="filter-box__panel" data-panel-content="duration" role="group" aria-label="<?php echo esc_attr( kztravel_ui( 'filterByDuration' ) ); ?>" hidden>
				<?php foreach ( $filter_index['durations'] as $duration ) : ?>
					<button type="button" class="chip" data-filter-duration="<?php echo esc_attr( $duration['days'] ); ?>">
						<?php echo esc_html( $duration['label'] ); ?>
						<span class="chip__count" data-count-for="duration-<?php echo esc_attr( $duration['days'] ); ?>"></span>
					</button>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<?php if ( ! empty( $filter_index['showCategoryFilter'] ) ) : ?>
			<div class="filter-box__panel" data-panel-content="category" role="group" aria-label="<?php echo esc_attr( kztravel_ui( 'filterByCategory' ) ); ?>" hidden>
				<?php foreach ( $filter_index['categories'] as $category ) : ?>
					<button type="button" class="chip" data-filter-category="<?php echo esc_attr( $category ); ?>">
						<?php echo esc_html( kztravel_format_label( $category ) ); ?>
						<span class="chip__count" data-count-for="category-<?php echo esc_attr( $category ); ?>"></span>
					</button>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
		</div>
	</div>
</div>

<script type="application/json" id="kztravel-trips">
<?php
echo wp_json_encode(
	array_map(
		function ( $trip ) {
			return array(
				'slug'         => $trip['slug'],
				'country'      => $trip['country'],
				'category'     => $trip['category'],
				'durationDays' => kztravel_parse_duration_days( $trip['duration'] ?? '' ),
				'price'        => kztravel_get_trip_filter_price( $trip ),
				'hasDiscount'  => kztravel_trip_has_active_discount( $trip ),
			);
		},
		$trips
	)
);
?>
</script>
