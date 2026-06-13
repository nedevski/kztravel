<?php
defined( 'ABSPATH' ) || exit;

$footer = kztravel_get_footer();
?>
	</main>
	<footer class="site-footer">
		<p>
			<?php echo esc_html( $footer['registration'] ); ?>
			<br>
			<?php echo esc_html( $footer['company'] ); ?> &copy; <?php echo esc_html( (string) gmdate( 'Y' ) ); ?> Всички права запазени.
		</p>
		<p class="site-footer__links">
			<a href="<?php echo esc_url( home_url( '/booking' ) ); ?>"><?php echo esc_html( kztravel_ui( 'navBooking' ) ); ?></a>
			<span aria-hidden="true"> · </span>
			<a href="<?php echo esc_url( home_url( '/contact' ) ); ?>"><?php echo esc_html( kztravel_ui( 'navContact' ) ); ?></a>
		</p>
	</footer>
</div>
<?php wp_footer(); ?>
</body>
</html>
