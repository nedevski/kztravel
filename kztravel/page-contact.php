<?php
/**
 * Template Name: Contact
 */

get_header();

$trip_slug = isset( $_GET['trip'] ) ? sanitize_title( wp_unslash( $_GET['trip'] ) ) : '';
$trip      = $trip_slug ? kztravel_get_trip_by_slug( $trip_slug ) : null;
$contact   = kztravel_get_contact();
$weekdays  = kztravel_weekday_labels();

$mailto_subject = $trip
	? rawurlencode( kztravel_ui( 'contactTripInquiry', $trip['name'] ) )
	: rawurlencode( kztravel_ui( 'contactHeading' ) );
?>
<article class="contact">
	<a href="<?php echo esc_url( $trip ? $trip['permalink'] : home_url( '/' ) ); ?>" class="contact__back">
		<?php echo esc_html( $trip ? '← ' . $trip['name'] : kztravel_ui( 'allTrips' ) ); ?>
	</a>

	<header class="contact__header">
		<h1 class="contact__title"><?php echo esc_html( kztravel_ui( 'contactHeading' ) ); ?></h1>
		<p class="contact__intro"><?php echo esc_html( kztravel_ui( 'contactIntro' ) ); ?></p>
		<?php if ( $trip ) : ?>
			<p class="contact__trip"><?php echo esc_html( kztravel_ui( 'contactTripInquiry', $trip['name'] ) ); ?></p>
		<?php endif; ?>
		<p class="contact__booking-link">
			<a href="<?php echo esc_url( home_url( '/booking' ) ); ?>"><?php echo esc_html( kztravel_ui( 'contactBookingLink' ) ); ?> →</a>
		</p>
	</header>

	<section class="contact__details">
		<div class="contact__row">
			<div class="contact__item contact__card">
				<span class="contact__label"><?php echo esc_html( kztravel_ui( 'contactPhone' ) ); ?></span>
				<a class="contact__value" href="<?php echo esc_url( 'tel:' . preg_replace( '/\s+/', '', $contact['phone'] ) ); ?>">
					<?php echo esc_html( $contact['phone'] ); ?>
				</a>
			</div>
			<div class="contact__item contact__card">
				<span class="contact__label"><?php echo esc_html( kztravel_ui( 'contactEmail' ) ); ?></span>
				<a class="contact__value" href="<?php echo esc_url( 'mailto:' . $contact['email'] . '?subject=' . $mailto_subject ); ?>">
					<?php echo esc_html( $contact['email'] ); ?>
				</a>
			</div>
		</div>
		<div class="contact__row">
			<div class="contact__item contact__card">
				<span class="contact__label"><?php echo esc_html( kztravel_ui( 'contactWorkingHours' ) ); ?></span>
				<dl class="contact__hours">
					<?php foreach ( $weekdays as $index => $day ) : ?>
						<div class="contact__hours-row">
							<dt><?php echo esc_html( $day ); ?></dt>
							<dd><?php echo esc_html( $contact['workingHours'][ $index ] ?? '' ); ?></dd>
						</div>
					<?php endforeach; ?>
				</dl>
			</div>
			<div class="contact__item contact__card">
				<dl class="contact__bank">
					<div class="contact__bank-row">
						<dt><?php echo esc_html( kztravel_ui( 'contactBankName' ) ); ?></dt>
						<dd><?php echo esc_html( $contact['bankDetails']['bankName'] ); ?></dd>
					</div>
					<div class="contact__bank-row">
						<dt><?php echo esc_html( kztravel_ui( 'contactIban' ) ); ?></dt>
						<dd><?php echo esc_html( $contact['bankDetails']['iban'] ); ?></dd>
					</div>
					<div class="contact__bank-row">
						<dt><?php echo esc_html( kztravel_ui( 'contactAccountHolder' ) ); ?></dt>
						<dd><?php echo esc_html( $contact['bankDetails']['holder'] ); ?></dd>
					</div>
				</dl>
			</div>
		</div>
	</section>

	<section class="contact__map-section contact__card">
		<div class="contact__item">
			<span class="contact__label"><?php echo esc_html( kztravel_ui( 'contactOffice' ) ); ?></span>
			<p class="contact__map-label"><?php echo esc_html( $contact['address'] ); ?></p>
		</div>
		<div class="contact__map">
			<iframe
				title="<?php echo esc_attr( $contact['address'] ); ?>"
				src="<?php echo esc_url( $contact['mapEmbedUrl'] ); ?>"
				loading="lazy"
				referrerpolicy="no-referrer-when-downgrade"
			></iframe>
		</div>
	</section>
</article>
<?php
get_footer();
