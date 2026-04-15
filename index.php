<?php
/**
 * Main theme template.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<main id="content" class="site-main">
	<?php
	$elementor_location_rendered = false;

	if ( function_exists( 'elementor_theme_do_location' ) ) {
		if ( is_singular() ) {
			$elementor_location_rendered = elementor_theme_do_location( 'single' );
		} elseif ( is_home() || is_archive() || is_search() ) {
			$elementor_location_rendered = elementor_theme_do_location( 'archive' );
		}
	}

	if ( ! $elementor_location_rendered ) :
		if ( have_posts() ) :
			if ( ! is_singular() ) :
				?>
				<div class="entry archive-list">
				<?php
			endif;

			while ( have_posts() ) :
				the_post();

				if ( is_singular() ) :
					?>
					<article id="post-<?php the_ID(); ?>" <?php post_class( 'entry' ); ?>>
						<header class="entry__header">
							<?php the_title( '<h1 class="entry__title">', '</h1>' ); ?>
						</header>
						<div class="entry__content">
							<?php the_content(); ?>
						</div>
					</article>
					<?php
				else :
					?>
					<article id="post-<?php the_ID(); ?>" <?php post_class( 'archive-card entry' ); ?>>
						<h2 class="archive-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
						<div class="archive-card__excerpt">
							<?php the_excerpt(); ?>
						</div>
					</article>
					<?php
				endif;
			endwhile;

			if ( ! is_singular() ) :
				?>
				</div>
				<?php
				the_posts_pagination();
			endif;
		else :
			?>
			<section class="empty-state">
				<h1 class="entry__title"><?php esc_html_e( 'Nothing found', 'barefoot-elementor-theme' ); ?></h1>
				<p><?php esc_html_e( 'There is no content to display yet.', 'barefoot-elementor-theme' ); ?></p>
			</section>
			<?php
		endif;
	endif;
	?>
</main>
<?php
get_footer();
