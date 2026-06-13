<?php
defined( 'ABSPATH' ) || exit;

$contact = kztravel_get_contact();
$phone_href = 'tel:' . preg_replace( '/\s+/', '', $contact['phone'] );
?>
<div class="site-header__contact">
	<a class="site-header__contact-item" href="<?php echo esc_url( $phone_href ); ?>">
		<svg class="site-header__contact-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
			<rect x="7" y="3" width="10" height="18" rx="2" stroke="currentColor" stroke-width="1.75" />
			<path d="M10 6h4" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" />
		</svg>
		<span><?php echo esc_html( $contact['phone'] ); ?></span>
	</a>
	<a class="site-header__contact-item" href="<?php echo esc_url( 'mailto:' . $contact['email'] ); ?>">
		<svg class="site-header__contact-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
			<rect x="3" y="5" width="18" height="14" rx="2" stroke="currentColor" stroke-width="1.75" />
			<path d="m4 7 8 6 8-6" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" />
		</svg>
		<span><?php echo esc_html( $contact['email'] ); ?></span>
	</a>
	<div class="site-header__contact-item site-header__contact-item--static">
		<svg class="site-header__contact-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
			<path d="M12 21s6-5.2 6-10a6 6 0 1 0-12 0c0 4.8 6 10 6 10Z" stroke="currentColor" stroke-width="1.75" stroke-linejoin="round" />
			<circle cx="12" cy="11" r="2.25" stroke="currentColor" stroke-width="1.75" />
		</svg>
		<span><?php echo esc_html( $contact['address'] ); ?></span>
	</div>
</div>
