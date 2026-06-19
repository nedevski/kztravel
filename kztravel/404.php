<?php
get_header();
?>
<section class="empty-state">
	<p>Страницата не е намерена.</p>
	<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="empty-state__btn">
		<?php echo esc_html( kztravel_ui( 'allTrips' ) ); ?>
	</a>
</section>
<?php
get_footer();
