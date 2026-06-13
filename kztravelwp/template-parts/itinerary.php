<?php
defined( 'ABSPATH' ) || exit;

$days = $args['days'] ?? array();
if ( empty( $days ) ) {
	return;
}

usort(
	$days,
	function ( $a, $b ) {
		return ( $a['day'] ?? 0 ) <=> ( $b['day'] ?? 0 );
	}
);
?>
<ol class="itinerary">
	<?php foreach ( $days as $day ) : ?>
		<li class="itinerary__day">
			<span class="itinerary__number"><?php echo esc_html( kztravel_ui( 'day', $day['day'] ?? 0 ) ); ?></span>
			<div class="itinerary__content">
				<h3 class="itinerary__title"><?php echo esc_html( $day['title'] ?? '' ); ?></h3>
				<?php if ( ! empty( $day['body'] ) ) : ?>
					<p class="itinerary__description"><?php echo nl2br( esc_html( $day['body'] ) ); ?></p>
				<?php endif; ?>
			</div>
		</li>
	<?php endforeach; ?>
</ol>
