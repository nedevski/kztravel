<?php
/**
 * Template Name: Booking
 */

get_header();

$booking = kztravel_get_booking_info( get_the_ID() );
?>
<article class="booking">
	<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="booking__back">
		<?php echo esc_html( kztravel_ui( 'allTrips' ) ); ?>
	</a>

	<header class="booking__header">
		<h1 class="booking__title"><?php echo esc_html( $booking['title'] ); ?></h1>
		<?php if ( ! empty( $booking['intro'] ) ) : ?>
			<p class="booking__intro"><?php echo esc_html( $booking['intro'] ); ?></p>
		<?php endif; ?>
	</header>

	<?php foreach ( $booking['sections'] as $section ) : ?>
		<section class="booking__section">
			<h2 class="booking__section-title"><?php echo esc_html( $section['title'] ); ?></h2>
			<ul class="booking__list">
				<?php foreach ( $section['items'] as $item ) : ?>
					<li><?php echo esc_html( $item ); ?></li>
				<?php endforeach; ?>
			</ul>
		</section>
	<?php endforeach; ?>

	<div class="booking__cta">
		<a href="<?php echo esc_url( home_url( '/contact' ) ); ?>" class="btn btn--primary">
			<?php echo esc_html( kztravel_ui( 'contactHeading' ) ); ?>
		</a>
	</div>
</article>
<?php
get_footer();
