<?php
get_header();

$trip_query = new WP_Query(
	array(
		'post_type'      => 'trip',
		'posts_per_page' => -1,
		'orderby'        => 'title',
		'order'          => 'ASC',
	)
);

$trips = array();
while ( $trip_query->have_posts() ) {
	$trip_query->the_post();
	$trips[] = kztravel_enrich_trip( get_the_ID() );
}
wp_reset_postdata();

$filter_index = kztravel_build_filter_index( $trips );
$filters      = kztravel_parse_filters_from_search( isset( $_SERVER['QUERY_STRING'] ) ? $_SERVER['QUERY_STRING'] : '', $filter_index['priceRanges'] );
$filtered     = kztravel_filter_trips( $trips, $filters );
?>
<section class="home">
	<div class="home__intro">
		<h1 class="home__heading"><?php echo esc_html( kztravel_ui( 'homeHeading' ) ); ?></h1>
		<p class="home__subheading"><?php echo esc_html( kztravel_ui( 'homeSubheading' ) ); ?></p>
	</div>

	<?php
	get_template_part(
		'template-parts/filter',
		'bar',
		array(
			'filter_index' => $filter_index,
			'trips'        => $trips,
		)
	);
	?>

	<div class="trip-grid" data-trip-grid>
		<?php foreach ( $trips as $trip ) : ?>
			<?php
			$is_visible = false;
			foreach ( $filtered as $visible_trip ) {
				if ( $visible_trip['slug'] === $trip['slug'] ) {
					$is_visible = true;
					break;
				}
			}
			get_template_part(
				'template-parts/trip',
				'card',
				array(
					'trip'   => $trip,
					'hidden' => ! $is_visible,
				)
			);
			?>
		<?php endforeach; ?>
	</div>

	<div class="empty-state" data-empty-state<?php echo ! empty( $filtered ) ? ' hidden' : ''; ?>>
		<p><?php echo esc_html( kztravel_ui( 'noTripsMatch' ) ); ?></p>
		<button type="button" class="empty-state__btn" data-filter-clear-all<?php echo kztravel_has_active_filters( $filters ) ? '' : ' hidden'; ?>>
			<?php echo esc_html( kztravel_ui( 'clearFilters' ) ); ?>
		</button>
	</div>
</section>
<?php
get_footer();
