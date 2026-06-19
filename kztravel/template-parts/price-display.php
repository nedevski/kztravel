<?php
defined( 'ABSPATH' ) || exit;

$price             = $args['price'] ?? 0;
$price_bgn         = $args['priceBgn'] ?? 0;
$discounted        = $args['discountedPrice'] ?? null;
$discounted_bgn    = $args['discountedPriceBgn'] ?? null;
$variant           = $args['variant'] ?? 'default';
$class_name        = $args['className'] ?? '';

$has_discount = null !== $discounted
	&& null !== $discounted_bgn
	&& $discounted > 0
	&& $discounted < $price;

$classes = array( 'price-display' );
if ( 'chip' === $variant ) {
	$classes[] = 'price-display--chip';
}
if ( $class_name ) {
	$classes[] = $class_name;
}
?>
<span class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
	<?php if ( $has_discount ) : ?>
		<span class="price-display__original price-display__struck">
			<?php echo esc_html( kztravel_format_dual_price( (float) $price, (float) $price_bgn ) ); ?>
		</span>
		<span class="price-display__current">
			<?php echo esc_html( kztravel_format_dual_price( (float) $discounted, (float) $discounted_bgn ) ); ?>
		</span>
	<?php else : ?>
		<?php echo esc_html( kztravel_format_dual_price( (float) $price, (float) $price_bgn ) ); ?>
	<?php endif; ?>
</span>
