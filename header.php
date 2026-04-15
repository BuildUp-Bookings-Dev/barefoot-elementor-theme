<?php
/**
 * Theme header.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$site_name   = get_bloginfo( 'name' );
$description = get_bloginfo( 'description', 'display' );
$header_menu = wp_nav_menu(
	[
		'theme_location' => 'header-menu',
		'container'      => false,
		'fallback_cb'    => false,
		'echo'           => false,
	]
);
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="screen-reader-text" href="#content"><?php esc_html_e( 'Skip to content', 'barefoot-elementor-theme' ); ?></a>

<?php if ( ! function_exists( 'elementor_theme_do_location' ) || ! elementor_theme_do_location( 'header' ) ) : ?>
	<header id="site-header" class="site-header">
		<div class="site-shell site-header__inner">
			<div class="site-branding">
				<?php if ( has_custom_logo() ) : ?>
					<?php the_custom_logo(); ?>
				<?php elseif ( $site_name ) : ?>
					<p class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php echo esc_html( $site_name ); ?></a></p>
				<?php endif; ?>
				<?php if ( $description ) : ?>
					<p class="site-description"><?php echo esc_html( $description ); ?></p>
				<?php endif; ?>
			</div>

			<?php if ( $header_menu ) : ?>
				<nav class="site-navigation" aria-label="<?php esc_attr_e( 'Header menu', 'barefoot-elementor-theme' ); ?>">
					<?php echo $header_menu; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</nav>
			<?php endif; ?>
		</div>
	</header>
<?php endif; ?>
