<?php
defined( 'ABSPATH' ) || exit;
?>
<nav class="site-header__nav" aria-label="<?php echo esc_attr( kztravel_ui( 'mainNav' ) ); ?>">
	<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="<?php echo esc_attr( kztravel_nav_link_class( 'trips' ) ); ?>">
		<?php echo esc_html( kztravel_ui( 'navTrips' ) ); ?>
	</a>
	<a href="<?php echo esc_url( home_url( '/contact' ) ); ?>" class="<?php echo esc_attr( kztravel_nav_link_class( 'contact' ) ); ?>">
		<?php echo esc_html( kztravel_ui( 'navContact' ) ); ?>
	</a>
	<a href="<?php echo esc_url( home_url( '/booking' ) ); ?>" class="<?php echo esc_attr( kztravel_nav_link_class( 'booking' ) ); ?>">
		<?php echo esc_html( kztravel_ui( 'navBooking' ) ); ?>
	</a>
</nav>
