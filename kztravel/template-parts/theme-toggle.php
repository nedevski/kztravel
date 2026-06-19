<?php
defined( 'ABSPATH' ) || exit;

$class = ! empty( $args['class'] ) ? 'theme-toggle ' . esc_attr( $args['class'] ) : 'theme-toggle';
?>
<button
	type="button"
	class="<?php echo esc_attr( $class ); ?>"
	aria-label="<?php echo esc_attr( kztravel_ui( 'switchToDark' ) ); ?>"
	title="<?php echo esc_attr( kztravel_ui( 'darkMode' ) ); ?>"
	data-label-dark="<?php echo esc_attr( kztravel_ui( 'switchToDark' ) ); ?>"
	data-label-light="<?php echo esc_attr( kztravel_ui( 'switchToLight' ) ); ?>"
	data-title-dark="<?php echo esc_attr( kztravel_ui( 'darkMode' ) ); ?>"
	data-title-light="<?php echo esc_attr( kztravel_ui( 'lightMode' ) ); ?>"
>
	<svg class="theme-toggle__icon theme-toggle__icon--moon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
		<path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" />
	</svg>
	<svg class="theme-toggle__icon theme-toggle__icon--sun" viewBox="0 0 24 24" fill="none" aria-hidden="true" hidden>
		<circle cx="12" cy="12" r="4" stroke="currentColor" stroke-width="1.75" />
		<path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" />
	</svg>
</button>
