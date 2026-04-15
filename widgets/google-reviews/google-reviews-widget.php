<?php
/**
 * Barefoot Theme – Google Reviews Elementor Widget.
 *
 * Reuses bft_google_reviews_fetch() and the slider assets from the
 * [barefoot_google_reviews] shortcode. Register via functions.php.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Barefoot_Google_Reviews_Widget extends \Elementor\Widget_Base {

	public function get_name(): string {
		return 'barefoot-google-reviews';
	}

	public function get_title(): string {
		return __( 'Google Reviews', 'barefoot-elementor-theme' );
	}

	public function get_icon(): string {
		return 'eicon-google';
	}

	/**
	 * @return array<int, string>
	 */
	public function get_categories(): array {
		return [ 'barefoot-engine' ];
	}

	/**
	 * @return array<int, string>
	 */
	public function get_keywords(): array {
		return [ 'google', 'reviews', 'testimonials', 'rating' ];
	}

	protected function register_controls(): void {

		// ── Content ───────────────────────────────────────────────

		$this->start_controls_section(
			'section_content',
			[
				'label' => __( 'Reviews', 'barefoot-elementor-theme' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'review_source',
			[
				'label'   => __( 'Review Source', 'barefoot-elementor-theme' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'api',
				'options' => [
					'api'    => __( 'Google Places API', 'barefoot-elementor-theme' ),
					'manual' => __( 'Manual Reviews', 'barefoot-elementor-theme' ),
				],
			]
		);

		$this->add_control(
			'place_id',
			[
				'label'       => __( 'Place ID', 'barefoot-elementor-theme' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => defined( 'BAREFOOT_GOOGLE_PLACES_PLACE_ID' ) ? BAREFOOT_GOOGLE_PLACES_PLACE_ID : '',
				'description' => __( 'Your Google Place ID. Defaults to the value set in functions.php.', 'barefoot-elementor-theme' ),
				'condition'   => [
					'review_source' => 'api',
				],
			]
		);

		$this->add_control(
			'min_rating',
			[
				'label'   => __( 'Minimum Star Rating', 'barefoot-elementor-theme' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => '4',
				'options' => [
					'1' => '1 ★',
					'2' => '2 ★★',
					'3' => '3 ★★★',
					'4' => '4 ★★★★',
					'5' => '5 ★★★★★',
				],
			]
		);

		$this->add_control(
			'max_reviews',
			[
				'label'   => __( 'Max Reviews', 'barefoot-elementor-theme' ),
				'type'    => \Elementor\Controls_Manager::NUMBER,
				'default' => 5,
				'min'     => 1,
				'max'     => 10,
				'step'    => 1,
				'description' => __( 'Limits the number of reviews rendered by the widget.', 'barefoot-elementor-theme' ),
			]
		);

		$manual_reviews = new \Elementor\Repeater();

		$manual_reviews->add_control(
			'author_name',
			[
				'label'       => __( 'Author Name', 'barefoot-elementor-theme' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => __( 'Guest Name', 'barefoot-elementor-theme' ),
				'label_block' => true,
			]
		);

		$manual_reviews->add_control(
			'rating',
			[
				'label'   => __( 'Rating', 'barefoot-elementor-theme' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => '5',
				'options' => [
					'1' => '1 ★',
					'2' => '2 ★★',
					'3' => '3 ★★★',
					'4' => '4 ★★★★',
					'5' => '5 ★★★★★',
				],
			]
		);

		$manual_reviews->add_control(
			'review_text',
			[
				'label'       => __( 'Review Text', 'barefoot-elementor-theme' ),
				'type'        => \Elementor\Controls_Manager::TEXTAREA,
				'rows'        => 5,
				'default'     => __( 'Add the guest review text here.', 'barefoot-elementor-theme' ),
				'label_block' => true,
			]
		);

		$this->add_control(
			'manual_reviews',
			[
				'label'       => __( 'Manual Reviews', 'barefoot-elementor-theme' ),
				'type'        => \Elementor\Controls_Manager::REPEATER,
				'fields'      => $manual_reviews->get_controls(),
				'title_field' => '{{{ author_name || "Manual review" }}}',
				'condition'   => [
					'review_source' => 'manual',
				],
			]
		);

		$this->add_control(
			'autoplay',
			[
				'label'        => __( 'Autoplay', 'barefoot-elementor-theme' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => __( 'Yes', 'barefoot-elementor-theme' ),
				'label_off'    => __( 'No', 'barefoot-elementor-theme' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->add_control(
			'empty_text',
			[
				'label'     => __( 'No Reviews Text', 'barefoot-elementor-theme' ),
				'type'      => \Elementor\Controls_Manager::TEXT,
				'default'   => __( 'No reviews available.', 'barefoot-elementor-theme' ),
				'separator' => 'before',
			]
		);

		$this->end_controls_section();

		// ── Style: Card ───────────────────────────────────────────

		$this->start_controls_section(
			'section_style_card',
			[
				'label' => __( 'Card', 'barefoot-elementor-theme' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'bg_color',
			[
				'label'     => __( 'Background Color', 'barefoot-elementor-theme' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#2ea3ad',
				'selectors' => [
					'{{WRAPPER}} .bft-reviews' => '--bft-reviews-bg: {{VALUE}}; background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'border_radius',
			[
				'label'      => __( 'Border Radius', 'barefoot-elementor-theme' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .bft-reviews' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'card_padding',
			[
				'label'      => __( 'Padding', 'barefoot-elementor-theme' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', 'rem' ],
				'selectors'  => [
					'{{WRAPPER}} .bft-reviews' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		// ── Style: Stars ──────────────────────────────────────────

		$this->start_controls_section(
			'section_style_stars',
			[
				'label' => __( 'Stars', 'barefoot-elementor-theme' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'stars_color',
			[
				'label'     => __( 'Filled Star Color', 'barefoot-elementor-theme' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .bft-reviews__star.is-filled' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'stars_empty_color',
			[
				'label'     => __( 'Empty Star Color', 'barefoot-elementor-theme' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => 'rgba(255,255,255,0.3)',
				'selectors' => [
					'{{WRAPPER}} .bft-reviews__star.is-empty' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'stars_size',
			[
				'label'      => __( 'Size', 'barefoot-elementor-theme' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'rem' ],
				'range'      => [
					'px'  => [ 'min' => 10, 'max' => 60 ],
					'rem' => [ 'min' => 1,  'max' => 5 ],
				],
				'selectors'  => [
					'{{WRAPPER}} .bft-reviews__stars' => 'font-size: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		// ── Style: Review Text ────────────────────────────────────

		$this->start_controls_section(
			'section_style_text',
			[
				'label' => __( 'Review Text', 'barefoot-elementor-theme' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'text_color',
			[
				'label'     => __( 'Color', 'barefoot-elementor-theme' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .bft-reviews__text' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name'     => 'text_typography',
				'selector' => '{{WRAPPER}} .bft-reviews__text',
			]
		);

		$this->add_responsive_control(
			'text_max_width',
			[
				'label'      => __( 'Max Width', 'barefoot-elementor-theme' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'range'      => [
					'px' => [ 'min' => 200, 'max' => 1200 ],
					'%'  => [ 'min' => 20,  'max' => 100 ],
				],
				'selectors'  => [
					'{{WRAPPER}} .bft-reviews__text' => 'max-width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		// ── Style: Author ─────────────────────────────────────────

		$this->start_controls_section(
			'section_style_author',
			[
				'label' => __( 'Author Name', 'barefoot-elementor-theme' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'author_color',
			[
				'label'     => __( 'Color', 'barefoot-elementor-theme' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .bft-reviews__author' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name'     => 'author_typography',
				'selector' => '{{WRAPPER}} .bft-reviews__author',
			]
		);

		$this->end_controls_section();

		// ── Style: Divider ────────────────────────────────────────

		$this->start_controls_section(
			'section_style_divider',
			[
				'label' => __( 'Divider', 'barefoot-elementor-theme' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'divider_color',
			[
				'label'     => __( 'Color', 'barefoot-elementor-theme' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => 'rgba(255,255,255,0.45)',
				'selectors' => [
					'{{WRAPPER}} .bft-reviews__divider' => 'border-top-color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'divider_width',
			[
				'label'      => __( 'Width', 'barefoot-elementor-theme' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'range'      => [
					'px' => [ 'min' => 50,  'max' => 800 ],
					'%'  => [ 'min' => 10,  'max' => 100 ],
				],
				'selectors'  => [
					'{{WRAPPER}} .bft-reviews__divider' => 'max-width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		// ── Style: Navigation Buttons ─────────────────────────────

		$this->start_controls_section(
			'section_style_nav',
			[
				'label' => __( 'Navigation Buttons', 'barefoot-elementor-theme' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'nav_color',
			[
				'label'     => __( 'Icon Color', 'barefoot-elementor-theme' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#334155',
				'selectors' => [
					'{{WRAPPER}} .bft-reviews__btn' => 'color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_control(
			'nav_bg_color',
			[
				'label'     => __( 'Background', 'barefoot-elementor-theme' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => 'rgba(255,255,255,0.82)',
				'selectors' => [
					'{{WRAPPER}} .bft-reviews__btn' => 'background: {{VALUE}} !important;',
				],
			]
		);

		$this->add_responsive_control(
			'nav_size',
			[
				'label'      => __( 'Button Size', 'barefoot-elementor-theme' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [ 'min' => 24, 'max' => 80 ],
				],
				'selectors'  => [
					'{{WRAPPER}} .bft-reviews__btn' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function render(): void {
		$settings = $this->get_settings_for_display();

		$source     = ( $settings['review_source'] ?? 'api' ) === 'manual' ? 'manual' : 'api';
		$min_rating = max( 1, min( 5, (int) ( $settings['min_rating'] ?? 4 ) ) );
		$max        = max( 1, min( 10, (int) ( $settings['max_reviews'] ?? 5 ) ) );
		$autoplay   = ( $settings['autoplay'] ?? 'yes' ) === 'yes';
		$empty_text = sanitize_text_field( (string) ( $settings['empty_text'] ?? __( 'No reviews available.', 'barefoot-elementor-theme' ) ) );

		if ( $source === 'manual' ) {
			$reviews = $this->get_manual_reviews( $settings['manual_reviews'] ?? [] );
		} else {
			$place_id = sanitize_text_field( (string) ( $settings['place_id'] ?? '' ) );
			$api_key  = defined( 'BAREFOOT_GOOGLE_PLACES_API_KEY' ) ? BAREFOOT_GOOGLE_PLACES_API_KEY : '';

			if ( ! $place_id || ! $api_key ) {
				if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
					echo '<p style="padding:1rem;background:#f0f0f0;">' . esc_html__( '[Google Reviews] Place ID or API key is missing.', 'barefoot-elementor-theme' ) . '</p>';
				}
				return;
			}

			$reviews = bft_google_reviews_fetch( $place_id, $api_key, 43200 );

			if ( is_wp_error( $reviews ) ) {
				if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
					echo '<p style="padding:1rem;background:#f0f0f0;">' . esc_html( $reviews->get_error_message() ) . '</p>';
				}
				return;
			}
		}

		$reviews = array_values(
			array_filter(
				$reviews,
				static fn( array $r ) => isset( $r['rating'] ) && (int) $r['rating'] >= $min_rating
			)
		);
		$reviews = array_slice( $reviews, 0, $max );

		if ( empty( $reviews ) ) {
			echo '<p class="bft-reviews__empty">' . esc_html( $empty_text ) . '</p>';
			return;
		}

		$this->render_reviews_slider( $reviews, $autoplay );
	}

	/**
	 * Normalize manually entered Elementor repeater rows into review records.
	 *
	 * @param array<int, array<string, mixed>> $items
	 * @return array<int, array<string, mixed>>
	 */
	private function get_manual_reviews( array $items ): array {
		$reviews = [];

		foreach ( $items as $item ) {
			if ( ! is_array( $item ) ) {
				continue;
			}

			$text   = trim( wp_strip_all_tags( (string) ( $item['review_text'] ?? '' ) ) );
			$author = trim( wp_strip_all_tags( (string) ( $item['author_name'] ?? '' ) ) );
			$rating = max( 1, min( 5, (int) ( $item['rating'] ?? 5 ) ) );

			if ( $text === '' && $author === '' ) {
				continue;
			}

			$reviews[] = [
				'rating'      => $rating,
				'text'        => $text,
				'author_name' => $author,
			];
		}

		return $reviews;
	}

	/**
	 * Render the shared reviews carousel markup.
	 *
	 * @param array<int, array<string, mixed>> $reviews
	 * @param bool                            $autoplay
	 */
	private function render_reviews_slider( array $reviews, bool $autoplay ): void {
		$count        = count( $reviews );
		$first_rating = (int) ( $reviews[0]['rating'] ?? 5 );
		?>
		<div
			class="bft-reviews barefoot-engine-public"
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
	}
}
