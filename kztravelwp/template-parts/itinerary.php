<?php
defined( 'ABSPATH' ) || exit;

$days = $args['days'] ?? array();
if ( empty( $days ) ) {
	return;
}
?>
<ol class="itinerary">
	<?php foreach ( $days as $index => $day ) : ?>
		<li class="itinerary__day">
			<span class="itinerary__number"><?php echo esc_html( kztravel_ui( 'day', $index + 1 ) ); ?></span>
			<div class="itinerary__content">
				<h3 class="itinerary__title"><?php echo esc_html( $day['title'] ?? '' ); ?></h3>
				<?php if ( ! empty( $day['body'] ) ) : ?>
					<p class="itinerary__description"><?php echo esc_html( $day['body'] ); ?></p>
				<?php endif; ?>
			</div>
		</li>
	<?php endforeach; ?>
</ol>
