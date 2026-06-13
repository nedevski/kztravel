<?php
get_header();

$trip = kztravel_enrich_trip( get_the_ID() );
if ( empty( $trip ) ) {
	wp_safe_redirect( home_url( '/' ) );
	exit;
}

$same_country_query = new WP_Query(
	array(
		'post_type'      => 'trip',
		'posts_per_page' => -1,
		'post__not_in'   => array( get_the_ID() ),
		'tax_query'      => array(
			array(
				'taxonomy' => 'trip_country',
				'field'    => 'slug',
				'terms'    => $trip['country'],
			),
		),
	)
);

$same_country = array();
while ( $same_country_query->have_posts() ) {
	$same_country_query->the_post();
	$same_country[] = kztravel_enrich_trip( get_the_ID() );
}
wp_reset_postdata();

$suggested_trips = kztravel_pick_random_trips( $same_country, 3 );
$country_filter  = kztravel_build_filter_params( array( 'country' => $trip['country'] ) );
$contact_url     = add_query_arg( 'trip', $trip['slug'], home_url( '/contact' ) );
?>
<article class="trip-detail">
	<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="trip-detail__back">
		<?php echo esc_html( kztravel_ui( 'allTrips' ) ); ?>
	</a>

	<header class="trip-detail__hero">
		<h1 class="trip-detail__title"><?php echo esc_html( $trip['name'] ); ?></h1>
		<div class="trip-detail__hero-card">
			<?php if ( ! empty( $trip['hero_url'] ) ) : ?>
				<div class="slideshow trip-detail__slideshow">
					<img
						src="<?php echo esc_url( $trip['hero_url'] ); ?>"
						alt="<?php echo esc_attr( $trip['name'] ); ?>"
						class="slideshow__img active"
						loading="eager"
					/>
				</div>
			<?php endif; ?>
			<div class="trip-detail__hero-body">
				<div class="trip-detail__hero-bar">
					<div class="trip-detail__badges">
						<a href="<?php echo esc_url( home_url( '/' ) . $country_filter ); ?>" class="badge badge--country badge--link">
							<?php echo esc_html( kztravel_format_country_label( $trip['country'] ) ); ?>
						</a>
						<?php if ( ! empty( $trip['duration'] ) ) : ?>
							<span class="badge badge--duration"><?php echo esc_html( $trip['duration'] ); ?></span>
						<?php endif; ?>
						<?php foreach ( $trip['category'] as $cat ) : ?>
							<span class="badge badge--category"><?php echo esc_html( kztravel_format_label( $cat ) ); ?></span>
						<?php endforeach; ?>
					</div>
					<a href="<?php echo esc_url( $contact_url ); ?>" class="btn btn--primary trip-detail__cta">
						<?php echo esc_html( kztravel_ui( 'bookNow' ) ); ?>
					</a>
				</div>
				<p class="trip-detail__description"><?php echo esc_html( $trip['description_plain'] ?? '' ); ?></p>
			</div>
		</div>
	</header>

	<section class="trip-detail__section">
		<h2><?php echo esc_html( kztravel_ui( 'datesAndPricing' ) ); ?></h2>
		<?php get_template_part( 'template-parts/dates', 'table', array( 'dates' => $trip['dates'] ) ); ?>
	</section>

	<?php if ( ! empty( $trip['gallery'] ) ) : ?>
		<section class="trip-detail__section">
			<h2><?php echo esc_html( kztravel_ui( 'gallery' ) ); ?></h2>
			<?php
			get_template_part(
				'template-parts/gallery',
				null,
				array(
					'images'    => $trip['gallery'],
					'trip_name' => $trip['name'],
				)
			);
			?>
		</section>
	<?php endif; ?>

	<?php if ( ! empty( $trip['itinerary'] ) ) : ?>
		<section class="trip-detail__section">
			<h2><?php echo esc_html( kztravel_ui( 'itinerary' ) ); ?></h2>
			<?php get_template_part( 'template-parts/itinerary', null, array( 'days' => $trip['itinerary'] ) ); ?>
		</section>
	<?php endif; ?>

	<?php if ( ! empty( $trip['included'] ) ) : ?>
		<section class="trip-detail__section">
			<h2><?php echo esc_html( kztravel_ui( 'included' ) ); ?></h2>
			<?php get_template_part( 'template-parts/inclusion', 'list', array( 'items' => $trip['included'], 'variant' => 'included' ) ); ?>
		</section>
	<?php endif; ?>

	<?php if ( ! empty( $trip['excluded'] ) ) : ?>
		<section class="trip-detail__section">
			<h2><?php echo esc_html( kztravel_ui( 'notIncluded' ) ); ?></h2>
			<?php get_template_part( 'template-parts/inclusion', 'list', array( 'items' => $trip['excluded'], 'variant' => 'excluded' ) ); ?>
		</section>
	<?php endif; ?>

	<?php if ( ! empty( $suggested_trips ) ) : ?>
		<section class="trip-detail__section trip-detail__suggestions">
			<h2><?php echo esc_html( kztravel_ui( 'suggestedTrips', kztravel_format_country_label( $trip['country'] ) ) ); ?></h2>
			<div class="trip-grid">
				<?php foreach ( $suggested_trips as $suggested ) : ?>
					<?php get_template_part( 'template-parts/trip', 'card', array( 'trip' => $suggested ) ); ?>
				<?php endforeach; ?>
			</div>
		</section>
	<?php endif; ?>

	<div class="trip-detail__footer-cta">
		<a href="<?php echo esc_url( $contact_url ); ?>" class="btn btn--primary trip-detail__cta">
			<?php echo esc_html( kztravel_ui( 'bookNow' ) ); ?>
		</a>
	</div>
</article>
<?php
get_footer();
