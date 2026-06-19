<?php
defined( 'ABSPATH' ) || exit;

$bg_url = kztravel_get_background_url();
$site_style = $bg_url ? sprintf( '--site-bg-image: url(%s)', esc_url( $bg_url ) ) : '';
$favicon = kztravel_get_favicon_url();
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> data-theme="light">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="icon" href="<?php echo esc_url( $favicon ); ?>">
	<script>
	(function () {
		var key = 'kz-theme';
		var stored = localStorage.getItem(key);
		var theme = (stored === 'light' || stored === 'dark')
			? stored
			: (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
		document.documentElement.dataset.theme = theme;
	})();
	</script>
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<div class="site"<?php echo $site_style ? ' style="' . esc_attr( $site_style ) . '"' : ''; ?>>
	<header class="site-header">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="site-header__brand">
			<?php echo esc_html( kztravel_get_site_title() ); ?>
		</a>
		<div class="site-header__actions">
			<?php get_template_part( 'template-parts/theme', 'toggle' ); ?>
			<button
				type="button"
				class="site-header__menu-toggle"
				aria-expanded="false"
				aria-controls="site-header-menu"
				aria-label="<?php echo esc_attr( kztravel_ui( 'openMenu' ) ); ?>"
				data-open-label="<?php echo esc_attr( kztravel_ui( 'openMenu' ) ); ?>"
				data-close-label="<?php echo esc_attr( kztravel_ui( 'closeMenu' ) ); ?>"
			>
				<svg class="site-header__menu-icon site-header__menu-icon--open" viewBox="0 0 24 24" fill="none" aria-hidden="true">
					<path d="M4 7h16" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
					<path d="M4 12h16" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
					<path d="M4 17h16" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
				</svg>
				<svg class="site-header__menu-icon site-header__menu-icon--close" viewBox="0 0 24 24" fill="none" aria-hidden="true">
					<path d="M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
					<path d="M6 18L18 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
				</svg>
			</button>
		</div>
		<div id="site-header-menu" class="site-header__menu">
			<?php get_template_part( 'template-parts/header', 'nav' ); ?>
			<div class="site-header__end">
				<?php get_template_part( 'template-parts/header', 'contact' ); ?>
				<?php get_template_part( 'template-parts/theme', 'toggle', array( 'class' => 'site-header__theme--desktop' ) ); ?>
			</div>
		</div>
	</header>
	<main class="site-main">
