<?php
defined( 'ABSPATH' ) || exit;

$trip = $args['trip'] ?? array();
if ( empty( $trip ) ) {
	return;
}

$visible_categories = array_slice( $trip['category'] ?? array(), 0, 2 );
$display_date       = ! empty( $trip['ended'] ) ? ( $trip['lastDate'] ?? null ) : ( $trip['nextDate'] ?? null );
$filter_price       = kztravel_get_trip_filter_price( $trip );
$duration_days      = kztravel_parse_duration_days( $trip['duration'] ?? '' );
$has_discount       = kztravel_trip_has_active_discount( $trip );

$card_classes = 'trip-card';
if ( ! empty( $trip['ended'] ) ) {
	$card_classes .= ' trip-card--ended';
}
if ( ! empty( $args['hidden'] ) ) {
	$card_classes .= ' trip-card--hidden';
}
?>
<a
	href="<?php echo esc_url( $trip['permalink'] ?? get_permalink( $trip['id'] ) ); ?>"
	class="<?php echo esc_attr( $card_classes ); ?>"
	data-trip-slug="<?php echo esc_attr( $trip['slug'] ?? '' ); ?>"
	data-country="<?php echo esc_attr( $trip['country'] ?? '' ); ?>"
	data-categories="<?php echo esc_attr( implode( ',', $trip['category'] ?? array() ) ); ?>"
	data-duration-days="<?php echo esc_attr( $duration_days ?? '' ); ?>"
	data-price="<?php echo esc_attr( null !== $filter_price ? (string) $filter_price : '' ); ?>"
	data-has-discount="<?php echo $has_discount ? '1' : '0'; ?>"
>
	<div class="trip-card__media">
		<?php if ( ! empty( $trip['hero_url'] ) ) : ?>
			<div class="slideshow trip-card__slideshow">
				<img
					src="<?php echo esc_url( $trip['hero_url'] ); ?>"
					alt="<?php echo esc_attr( $trip['name'] ); ?>"
					class="slideshow__img active"
					loading="lazy"
				/>
			</div>
		<?php endif; ?>
		<?php if ( ! empty( $trip['fullyBooked'] ) ) : ?>
			<span class="trip-card__badge trip-card__badge--soldout"><?php echo esc_html( kztravel_ui( 'fullyBooked' ) ); ?></span>
		<?php endif; ?>
	</div>
	<div class="trip-card__body">
		<div class="trip-card__badges">
			<span class="badge badge--country"><?php echo esc_html( kztravel_format_country_label( $trip['country'] ?? '' ) ); ?></span>
			<?php foreach ( $visible_categories as $cat ) : ?>
				<span class="badge badge--category"><?php echo esc_html( kztravel_format_label( $cat ) ); ?></span>
			<?php endforeach; ?>
		</div>
		<h2 class="trip-card__title"><?php echo esc_html( $trip['name'] ); ?></h2>
		<div class="trip-card__meta">
			<div class="trip-card__pricing">
				<?php if ( ! empty( $trip['duration'] ) ) : ?>
					<span class="trip-card__duration"><?php echo esc_html( $trip['duration'] ); ?></span>
				<?php endif; ?>
				<span class="trip-card__price-chip">
					<?php if ( ! empty( $trip['fullyBooked'] ) ) : ?>
						<?php echo esc_html( kztravel_ui( 'contactUs' ) ); ?>
					<?php elseif ( null !== ( $trip['displayPrice'] ?? null ) && null !== ( $trip['displayPriceBgn'] ?? null ) ) : ?>
						<?php
						get_template_part(
							'template-parts/price',
							'display',
							array(
								'price'               => $trip['displayPrice'],
								'priceBgn'            => $trip['displayPriceBgn'],
								'discountedPrice'     => $trip['displayDiscountedPrice'] ?? null,
								'discountedPriceBgn'  => $trip['displayDiscountedPriceBgn'] ?? null,
								'variant'             => 'chip',
							)
						);
						?>
					<?php else : ?>
						<?php echo esc_html( kztravel_ui( 'contactUs' ) ); ?>
					<?php endif; ?>
				</span>
			</div>
			<?php if ( empty( $trip['fullyBooked'] ) && $display_date ) : ?>
				<div class="trip-card__date-group">
					<?php if ( empty( $trip['ended'] ) && ( $trip['moreAvailableDates'] ?? 0 ) > 0 ) : ?>
						<span class="trip-card__more"><?php echo esc_html( kztravel_ui( 'moreDates', $trip['moreAvailableDates'] ) ); ?></span>
					<?php endif; ?>
					<span class="trip-card__date"><?php echo esc_html( kztravel_format_date( $display_date['date'] ) ); ?></span>
				</div>
			<?php endif; ?>
		</div>
	</div>
</a>
