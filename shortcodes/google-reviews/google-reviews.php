<?php
/**
 * Barefoot Theme – Google Reviews shortcode.
 *
 * Usage: [google_reviews]
 * Optional attrs: min_rating="4" max="5" bg_color="#3aabb8" autoplay="yes"
 *
 * Credentials are read from constants defined in functions.php.
 * Override per-instance via shortcode attributes if needed.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Fetch and cache Place Details reviews using the Places API (New).
 *
 * Endpoint : GET https://places.googleapis.com/v1/places/{PLACE_ID}
 * Auth     : X-Goog-Api-Key header (not a query param)
 * Fields   : X-Goog-FieldMask header
 *
 * The raw response is normalised to a flat array of:
 *   [ 'rating' => int, 'text' => string, 'author_name' => string ]
 * so the rendering code is API-version agnostic.
 *
 * @param  string $place_id  Google Place ID.
 * @param  string $api_key   Google Places API key.
 * @param  int    $cache_ttl Cache lifetime in seconds.
 * @return array<int, array<string, mixed>>|WP_Error
 */
function bft_google_reviews_fetch( string $place_id, string $api_key, int $cache_ttl ) {
	$cache_key = 'bft_google_reviews_' . md5( $place_id );
	$cached    = get_transient( $cache_key );

	if ( is_array( $cached ) ) {
		return $cached;
	}

	$url = 'https://places.googleapis.com/v1/places/' . rawurlencode( $place_id );

	$response = wp_remote_get(
		$url,
		[
			'timeout' => 10,
			'headers' => [
				'X-Goog-Api-Key'   => $api_key,
				'X-Goog-FieldMask' => 'reviews.rating,reviews.text,reviews.authorAttribution',
			],
		]
	);

	if ( is_wp_error( $response ) ) {
		return $response;
	}

	$http_code = (int) wp_remote_retrieve_response_code( $response );
	$body      = wp_remote_retrieve_body( $response );
	$data      = json_decode( $body, true );

	if ( $http_code !== 200 || ! is_array( $data ) ) {
		$message = isset( $data['error']['message'] )
			? (string) $data['error']['message']
			: sprintf( 'HTTP %d', $http_code );

		return new WP_Error( 'bft_google_reviews_api', 'Google Places API error: ' . $message );
	}

	// Normalise the new API structure into the flat shape the renderer expects.
	$raw     = isset( $data['reviews'] ) && is_array( $data['reviews'] ) ? $data['reviews'] : [];
	$reviews = [];

	foreach ( $raw as $item ) {
		if ( ! is_array( $item ) ) {
			continue;
		}

		$reviews[] = [
			'rating'      => isset( $item['rating'] ) ? (int) $item['rating'] : 0,
			'text'        => isset( $item['text']['text'] ) ? (string) $item['text']['text'] : '',
			'author_name' => isset( $item['authorAttribution']['displayName'] )
				? (string) $item['authorAttribution']['displayName']
				: '',
		];
	}

	set_transient( $cache_key, $reviews, $cache_ttl );

	return $reviews;
}

/**
 * Render SVG star icons for a rating.
 */
function bft_google_reviews_stars( int $rating ): string {
	$star_svg = '<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>';
	$out      = '';
	for ( $i = 1; $i <= 5; $i++ ) {
		$class = $i <= $rating ? 'bft-reviews__star is-filled' : 'bft-reviews__star is-empty';
		$out  .= '<span class="' . $class . '">' . $star_svg . '</span>';
	}
	return $out;
}

/**
 * Return the decorative SVG quote mark used in each slide.
 */
function bft_google_reviews_quote(): string {
	return '<svg fill="#ffffff" height="40" width="40" viewBox="0 0 64 64" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" style="fill-rule:evenodd;clip-rule:evenodd;"><path d="M27.194,12l0,8.025c-2.537,0.14 -4.458,0.603 -5.761,1.39c-1.304,0.787 -2.22,2.063 -2.749,3.829c-0.528,1.766 -0.793,4.292 -0.793,7.579l9.303,0l0,19.145l-19.081,0l0,-18.201c0,-7.518 1.612,-13.025 4.836,-16.522c3.225,-3.497 7.973,-5.245 14.245,-5.245Zm28.806,0l0,8.025c-2.537,0.14 -4.457,0.586 -5.761,1.338c-1.304,0.751 -2.247,2.028 -2.828,3.829c-0.581,1.8 -0.872,4.344 -0.872,7.631l9.461,0l0,19.145l-19.186,0l0,-18.201c0,-7.518 1.603,-13.025 4.809,-16.522c3.207,-3.497 7.999,-5.245 14.377,-5.245Z" style="fill-rule:nonzero;"/></svg>';
}

/**
 * [google_reviews] shortcode handler.
 *
 * @param  array<string, string> $atts
 * @return string
 */
function bft_google_reviews_shortcode( $atts ): string {
	$atts = shortcode_atts(
		[
			'place_id'   => defined( 'BAREFOOT_GOOGLE_PLACES_PLACE_ID' ) ? BAREFOOT_GOOGLE_PLACES_PLACE_ID : '',
			'api_key'    => defined( 'BAREFOOT_GOOGLE_PLACES_API_KEY' )  ? BAREFOOT_GOOGLE_PLACES_API_KEY  : '',
			'min_rating' => 4,
			'max'        => 10,
			'bg_color'   => '#2ea3ad',
			'autoplay'   => 'yes',
			'cache_ttl'  => 43200, // 12 hours
		],
		$atts,
		'barefoot_google_reviews'
	);

	$place_id   = sanitize_text_field( (string) $atts['place_id'] );
	$api_key    = sanitize_text_field( (string) $atts['api_key'] );
	$min_rating = max( 1, min( 5, (int) $atts['min_rating'] ) );
	$max        = max( 1, min( 20, (int) $atts['max'] ) );
	$bg_color   = sanitize_hex_color( (string) $atts['bg_color'] ) ?: '#3aabb8';
	$autoplay   = in_array( strtolower( (string) $atts['autoplay'] ), [ 'yes', '1', 'true' ], true );
	$cache_ttl  = max( 60, (int) $atts['cache_ttl'] );

	if ( ! $place_id || ! $api_key ) {
		return '<!-- [google_reviews] missing place_id or api_key -->';
	}

	$reviews = bft_google_reviews_fetch( $place_id, $api_key, $cache_ttl );

	if ( is_wp_error( $reviews ) ) {
		return '<!-- [google_reviews] ' . esc_html( $reviews->get_error_message() ) . ' -->';
	}

	// Filter by minimum star rating.
	$reviews = array_values(
		array_filter(
			$reviews,
			static fn( array $r ) => isset( $r['rating'] ) && (int) $r['rating'] >= $min_rating
		)
	);

	// Honour the max cap.
	$reviews = array_slice( $reviews, 0, $max );

	if ( empty( $reviews ) ) {
		return '<!-- [google_reviews] no reviews matched the criteria -->';
	}

	// Enqueue assets (safe to call after wp_head; Elementor pages use footer scripts).
	wp_enqueue_style(
		'bft-google-reviews',
		get_template_directory_uri() . '/shortcodes/google-reviews/google-reviews.css',
		[],
		BAREFOOT_ELEMENTOR_THEME_VERSION
	);
	wp_enqueue_script(
		'bft-google-reviews',
		get_template_directory_uri() . '/shortcodes/google-reviews/google-reviews.js',
		[],
		BAREFOOT_ELEMENTOR_THEME_VERSION,
		true
	);

	$count = count( $reviews );

	$first_rating = (int) ( $reviews[0]['rating'] ?? 5 );

	ob_start();
	?>
	<div
		class="bft-reviews barefoot-engine-public"
		style="--bft-reviews-bg: <?php echo esc_attr( $bg_color ); ?>;"
		data-bft-reviews
		data-autoplay="<?php echo $autoplay ? 'true' : 'false'; ?>"
		role="region"
		aria-label="<?php esc_attr_e( 'Customer Reviews', 'barefoot-elementor-theme' ); ?>"
		aria-roledescription="carousel"
	>
		<!-- Stars: static outside the track, updated by JS on slide change -->
		<div class="bft-reviews__stars" aria-label="<?php echo esc_attr( $first_rating . ' out of 5 stars' ); ?>" data-bft-stars>
			<?php echo bft_google_reviews_stars( $first_rating ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</div>

		<!-- Content: position:relative anchor for the quote mark (NOT overflow:hidden) -->
		<div class="bft-reviews__content">

			<!-- Quote: absolute top-right, floats into the gap between stars and text -->
			<div class="bft-reviews__quote"><?php echo bft_google_reviews_quote(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>

			<!-- Viewport: the only overflow:hidden element, clips the sliding track -->
			<div class="bft-reviews__viewport">

				<div class="bft-reviews__track">
					<?php foreach ( $reviews as $index => $review ) :
					$rating = (int) ( $review['rating'] ?? 0 );
					$text   = trim( (string) ( $review['text'] ?? '' ) );
					$author = trim( (string) ( $review['author_name'] ?? '' ) );
				?>
				<div
					class="bft-reviews__slide"
					role="group"
					aria-roledescription="slide"
					aria-label="<?php echo esc_attr( sprintf( '%d of %d', $index + 1, $count ) ); ?>"
					data-rating="<?php echo esc_attr( (string) $rating ); ?>"
					<?php if ( $index !== 0 ) : ?>aria-hidden="true"<?php endif; ?>
				>
					<?php if ( $text !== '' ) : ?>
						<p class="bft-reviews__text"><?php echo esc_html( $text ); ?></p>
					<?php endif; ?>

					<hr class="bft-reviews__divider" />

					<?php if ( $author !== '' ) : ?>
						<p class="bft-reviews__author"><?php echo esc_html( $author ); ?></p>
					<?php endif; ?>
				</div>
				<?php endforeach; ?>
				</div><!-- /.bft-reviews__track -->

			</div><!-- /.bft-reviews__viewport -->

		</div><!-- /.bft-reviews__content -->

		<?php if ( $count > 1 ) : ?>
		<nav class="bft-reviews__nav" aria-label="<?php esc_attr_e( 'Review navigation', 'barefoot-elementor-theme' ); ?>">
			<button
				type="button"
				class="bft-reviews__btn bft-reviews__btn--prev"
				aria-label="<?php esc_attr_e( 'Previous review', 'barefoot-elementor-theme' ); ?>"
			>
				<i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
			</button>
			<button
				type="button"
				class="bft-reviews__btn bft-reviews__btn--next"
				aria-label="<?php esc_attr_e( 'Next review', 'barefoot-elementor-theme' ); ?>"
			>
				<i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
			</button>
		</nav>
		<?php endif; ?>
	</div>
	<?php
	return (string) ob_get_clean();
}
add_shortcode( 'barefoot_google_reviews', 'bft_google_reviews_shortcode' );

// ── Admin-bar "Sync Reviews" button ────────────────────────────────────────

/**
 * Add a "Sync Reviews" node to the WordPress admin bar.
 * Only shown to users who can manage options (admins).
 *
 * @param WP_Admin_Bar $wp_admin_bar
 */
function bft_google_reviews_admin_bar_node( WP_Admin_Bar $wp_admin_bar ): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$url = wp_nonce_url(
		add_query_arg( 'action', 'bft_sync_reviews', admin_url( 'admin-post.php' ) ),
		'bft_sync_reviews'
	);

	$wp_admin_bar->add_node(
		[
			'id'    => 'bft-sync-reviews',
			'title' => '&#8635; Sync Reviews',
			'href'  => esc_url( $url ),
			'meta'  => [
				'title' => 'Clear the Google Reviews cache and re-fetch from the API',
			],
		]
	);
}
add_action( 'admin_bar_menu', 'bft_google_reviews_admin_bar_node', 999 );

/**
 * Handle the sync action: delete the transient, re-fetch, then redirect back.
 * Hooked to admin-post.php so it runs before any output.
 */
function bft_google_reviews_handle_sync(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Insufficient permissions.', 'barefoot-elementor-theme' ), 403 );
	}

	check_admin_referer( 'bft_sync_reviews' );

	$place_id = defined( 'BAREFOOT_GOOGLE_PLACES_PLACE_ID' ) ? BAREFOOT_GOOGLE_PLACES_PLACE_ID : '';
	$api_key  = defined( 'BAREFOOT_GOOGLE_PLACES_API_KEY' )  ? BAREFOOT_GOOGLE_PLACES_API_KEY  : '';

	// Delete the cached transient so the next fetch is fresh.
	if ( $place_id !== '' ) {
		delete_transient( 'bft_google_reviews_' . md5( $place_id ) );

		// Pre-warm the cache immediately so the next visitor doesn't wait.
		if ( $api_key !== '' ) {
			bft_google_reviews_fetch( $place_id, $api_key, 43200 );
		}
	}

	// Redirect back to wherever the admin was (front-end page or dashboard).
	$redirect = wp_get_referer() ?: admin_url();
	wp_safe_redirect(
		add_query_arg( 'bft_reviews_synced', '1', $redirect )
	);
	exit;
}
add_action( 'admin_post_bft_sync_reviews', 'bft_google_reviews_handle_sync' );

/**
 * Show a brief admin-bar notice after a successful sync.
 */
function bft_google_reviews_sync_notice(): void {
	if (
		! is_admin_bar_showing()
		|| ! current_user_can( 'manage_options' )
		|| empty( $_GET['bft_reviews_synced'] ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	) {
		return;
	}
	?>
	<style>
		#wp-admin-bar-bft-sync-reviews > .ab-item { color: #7aff7a !important; }
	</style>
	<?php
}
add_action( 'wp_head',    'bft_google_reviews_sync_notice' );
add_action( 'admin_head', 'bft_google_reviews_sync_notice' );
