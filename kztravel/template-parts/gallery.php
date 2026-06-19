<?php
defined( 'ABSPATH' ) || exit;

$images    = $args['images'] ?? array();
$trip_name = $args['trip_name'] ?? '';
if ( empty( $images ) ) {
	return;
}
?>
<div class="gallery">
	<?php foreach ( $images as $image ) : ?>
		<div class="gallery__thumb" tabindex="0">
			<img
				src="<?php echo esc_url( is_array( $image ) ? ( $image['url'] ?? '' ) : $image ); ?>"
				alt="<?php echo esc_attr( is_array( $image ) ? ( $image['alt'] ?? $trip_name ) : $trip_name ); ?>"
				class="gallery__img"
				loading="lazy"
			/>
		</div>
	<?php endforeach; ?>
</div>
