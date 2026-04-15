<?php
/**
 * Theme footer.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$footer_menu = wp_nav_menu(
	[
		'theme_location' => 'footer-menu',
		'container'      => false,
		'fallback_cb'    => false,
		'echo'           => false,
	]
);
?>
<?php if ( ! function_exists( 'elementor_theme_do_location' ) || ! elementor_theme_do_location( 'footer' ) ) : ?>
	<footer id="site-footer" class="site-footer">
		<div class="site-shell site-footer__inner">
			<?php if ( $footer_menu ) : ?>
				<nav class="site-navigation" aria-label="<?php esc_attr_e( 'Footer menu', 'barefoot-elementor-theme' ); ?>">
					<?php echo $footer_menu; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</nav>
			<?php endif; ?>

			<p class="site-footer__meta">
				<?php
				echo esc_html(
					sprintf(
						/* translators: %s: site name */
						__( '%s', 'barefoot-elementor-theme' ),
						get_bloginfo( 'name' )
					)
				);
				?>
			</p>
		</div>
	</footer>
<?php endif; ?>

<?php wp_footer(); ?>
</body>
</html>
