<?php
/**
 * Barefoot Hero Slider Elementor Widget.
 *
 * @package Barefoot_Elementor_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Full-width / full-height hero slider widget for Elementor.
 */
class Barefoot_Hero_Slider_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'barefoot-hero-slider';
	}

	public function get_title() {
		return __( 'Hero Slider', 'barefoot-elementor-theme' );
	}

	public function get_icon() {
		return 'eicon-slides';
	}

	public function get_categories() {
		return [ 'barefoot-engine' ];
	}

	public function get_keywords() {
		return [ 'hero', 'slider', 'fullscreen', 'banner' ];
	}

	/**
	 * Register widget controls.
	 */
	protected function register_controls() {

		// ── Slides ───────────────────────────────────────────────────────────

		$this->start_controls_section(
			'section_slides',
			[
				'label' => __( 'Slides', 'barefoot-elementor-theme' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$repeater = new \Elementor\Repeater();

		$repeater->add_control(
			'slide_title',
			[
				'label'       => __( 'Title', 'barefoot-elementor-theme' ),
				'type'        => \Elementor\Controls_Manager::TEXTAREA,
				'default'     => __( 'Slide Title', 'barefoot-elementor-theme' ),
				'label_block' => true,
				'rows'        => 3,
				'dynamic'     => [
					'active' => true,
				],
			]
		);

		$repeater->add_control(
			'slide_content',
			[
				'label'   => __( 'Content', 'barefoot-elementor-theme' ),
				'type'    => \Elementor\Controls_Manager::TEXTAREA,
				'default' => __( 'Slide description goes here.', 'barefoot-elementor-theme' ),
				'rows'    => 4,
				'dynamic' => [
					'active' => true,
				],
			]
		);

		$repeater->add_control(
			'slide_button_text',
			[
				'label'   => __( 'Button Text', 'barefoot-elementor-theme' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => __( 'Learn More', 'barefoot-elementor-theme' ),
				'dynamic' => [
					'active' => true,
				],
			]
		);

		$repeater->add_control(
			'slide_button_url',
			[
				'label'         => __( 'Button URL', 'barefoot-elementor-theme' ),
				'type'          => \Elementor\Controls_Manager::URL,
				'placeholder'   => __( 'https://example.com', 'barefoot-elementor-theme' ),
				'default'       => [ 'url' => '#' ],
				'show_external' => true,
				'dynamic'       => [
					'active' => true,
				],
			]
		);

		$repeater->add_control(
			'slide_bg_image',
			[
				'label'   => __( 'Background Image', 'barefoot-elementor-theme' ),
				'type'    => \Elementor\Controls_Manager::MEDIA,
				'default' => [ 'url' => '' ],
				'dynamic' => [
					'active' => true,
				],
			]
		);

		$repeater->add_control(
			'slide_bg_color',
			[
				'label'   => __( 'Background Color (fallback)', 'barefoot-elementor-theme' ),
				'type'    => \Elementor\Controls_Manager::COLOR,
				'default' => '#1a1a2e',
				'dynamic' => [
					'active' => true,
				],
			]
		);

		$this->add_control(
			'slides',
			[
				'label'       => __( 'Slides', 'barefoot-elementor-theme' ),
				'type'        => \Elementor\Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'default'     => [
					[
						'slide_title'       => __( 'First Slide', 'barefoot-elementor-theme' ),
						'slide_content'     => __( 'Add your description here.', 'barefoot-elementor-theme' ),
						'slide_button_text' => __( 'Learn More', 'barefoot-elementor-theme' ),
						'slide_button_url'  => [ 'url' => '#' ],
						'slide_bg_color'    => '#1a1a2e',
					],
					[
						'slide_title'       => __( 'Second Slide', 'barefoot-elementor-theme' ),
						'slide_content'     => __( 'Add your description here.', 'barefoot-elementor-theme' ),
						'slide_button_text' => __( 'Learn More', 'barefoot-elementor-theme' ),
						'slide_button_url'  => [ 'url' => '#' ],
						'slide_bg_color'    => '#16213e',
					],
				],
				'title_field' => '{{{ slide_title }}}',
			]
		);

		$this->end_controls_section();

		// ── Persistent Content ───────────────────────────────────────────────

		$this->start_controls_section(
			'section_persistent_content',
			[
				'label' => __( 'Persistent Content', 'barefoot-elementor-theme' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'persistent_content_info',
			[
				'type'            => \Elementor\Controls_Manager::RAW_HTML,
				'raw'             => __( 'This content appears on every slide between the slide text and the button. It does not animate during slide transitions.', 'barefoot-elementor-theme' ),
				'content_classes' => 'elementor-descriptor',
			]
		);

		$this->add_control(
			'persistent_content',
			[
				'label'   => __( 'Content', 'barefoot-elementor-theme' ),
				'type'    => \Elementor\Controls_Manager::WYSIWYG,
				'default' => '',
			]
		);

		$this->end_controls_section();

		// ── Slider Settings ──────────────────────────────────────────────────

		$this->start_controls_section(
			'section_slider_settings',
			[
				'label' => __( 'Slider Settings', 'barefoot-elementor-theme' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_responsive_control(
			'slider_height',
			[
				'label'      => __( 'Height', 'barefoot-elementor-theme' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'vh' ],
				'range'      => [
					'px' => [ 'min' => 200, 'max' => 1200, 'step' => 10 ],
					'vh' => [ 'min' => 10,  'max' => 100,  'step' => 1  ],
				],
				'default'    => [ 'unit' => 'vh', 'size' => 100 ],
				'selectors'  => [
					'{{WRAPPER}} .bft-hero-slider-wrapper' => 'height: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .bft-slide'               => 'height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'content_max_width',
			[
				'label'      => __( 'Content Max Width', 'barefoot-elementor-theme' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'vw' ],
				'range'      => [
					'px'  => [ 'min' => 200, 'max' => 1600, 'step' => 10 ],
					'%'   => [ 'min' => 10,  'max' => 100,  'step' => 1  ],
					'vw'  => [ 'min' => 10,  'max' => 100,  'step' => 1  ],
				],
				'default'    => [ 'unit' => 'px', 'size' => 800 ],
				'selectors'  => [
					'{{WRAPPER}} .bft-slide-content'     => 'max-width: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .bft-persistent-content' => 'max-width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'transition_effect',
			[
				'label'   => __( 'Transition Effect', 'barefoot-elementor-theme' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'slide',
				'options' => [
					'slide'     => __( 'Slide', 'barefoot-elementor-theme' ),
					'fade'      => __( 'Fade', 'barefoot-elementor-theme' ),
					'zoom-fade' => __( 'Zoom Fade', 'barefoot-elementor-theme' ),
					'slide-up'  => __( 'Slide Up', 'barefoot-elementor-theme' ),
				],
			]
		);

		$this->add_control(
			'nav_style',
			[
				'label'   => __( 'Navigation Style', 'barefoot-elementor-theme' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'style-1',
				'options' => [
					'style-1' => __( 'Style 1 – Arrows: sides / Dots: bottom', 'barefoot-elementor-theme' ),
					'style-2' => __( 'Style 2 – Arrows: bottom-center / Dots: left', 'barefoot-elementor-theme' ),
				],
			]
		);

		$this->add_control(
			'autoplay',
			[
				'label'        => __( 'Autoplay', 'barefoot-elementor-theme' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => __( 'On', 'barefoot-elementor-theme' ),
				'label_off'    => __( 'Off', 'barefoot-elementor-theme' ),
				'return_value' => 'yes',
				'default'      => 'yes',
				'separator'    => 'before',
			]
		);

		$this->add_control(
			'autoplay_delay',
			[
				'label'     => __( 'Autoplay Delay (ms)', 'barefoot-elementor-theme' ),
				'type'      => \Elementor\Controls_Manager::NUMBER,
				'default'   => 5000,
				'min'       => 1000,
				'step'      => 500,
				'condition' => [ 'autoplay' => 'yes' ],
			]
		);

		$this->add_control(
			'show_arrows',
			[
				'label'        => __( 'Show Arrows', 'barefoot-elementor-theme' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => __( 'Show', 'barefoot-elementor-theme' ),
				'label_off'    => __( 'Hide', 'barefoot-elementor-theme' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->add_control(
			'show_dots',
			[
				'label'        => __( 'Show Dots', 'barefoot-elementor-theme' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => __( 'Show', 'barefoot-elementor-theme' ),
				'label_off'    => __( 'Hide', 'barefoot-elementor-theme' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->add_control(
			'transition_speed',
			[
				'label'   => __( 'Transition Speed (ms)', 'barefoot-elementor-theme' ),
				'type'    => \Elementor\Controls_Manager::NUMBER,
				'default' => 600,
				'min'     => 100,
				'step'    => 100,
			]
		);

		$this->end_controls_section();

		// ── Style: Text ──────────────────────────────────────────────────────

		$this->start_controls_section(
			'section_style_text',
			[
				'label' => __( 'Text', 'barefoot-elementor-theme' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'title_color',
			[
				'label'   => __( 'Title Color', 'barefoot-elementor-theme' ),
				'type'    => \Elementor\Controls_Manager::COLOR,
				'default' => '#ffffff',
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name'     => 'title_typography',
				'label'    => __( 'Title Typography', 'barefoot-elementor-theme' ),
				'selector' => '{{WRAPPER}} .bft-slide-title',
			]
		);

		$this->add_control(
			'content_color',
			[
				'label'   => __( 'Content Color', 'barefoot-elementor-theme' ),
				'type'    => \Elementor\Controls_Manager::COLOR,
				'default' => 'rgba(255,255,255,0.85)',
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name'     => 'content_typography',
				'label'    => __( 'Content Typography', 'barefoot-elementor-theme' ),
				'selector' => '{{WRAPPER}} .bft-slide-text',
			]
		);

		$this->end_controls_section();

		// ── Style: Button ────────────────────────────────────────────────────

		$this->start_controls_section(
			'section_style_button',
			[
				'label' => __( 'Button', 'barefoot-elementor-theme' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name'     => 'button_typography',
				'selector' => '{{WRAPPER}} .bft-slide-btn',
			]
		);

		$this->add_responsive_control(
			'button_padding',
			[
				'label'      => __( 'Padding', 'barefoot-elementor-theme' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'default'    => [
					'top'    => 12,
					'right'  => 32,
					'bottom' => 12,
					'left'   => 32,
					'unit'   => 'px',
				],
				'selectors'  => [
					'{{WRAPPER}} .bft-slide-btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'button_border_radius',
			[
				'label'      => __( 'Border Radius', 'barefoot-elementor-theme' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'default'    => [
					'top'    => 4,
					'right'  => 4,
					'bottom' => 4,
					'left'   => 4,
					'unit'   => 'px',
				],
				'selectors'  => [
					'{{WRAPPER}} .bft-slide-btn' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->start_controls_tabs( 'tabs_button_style' );

		// Normal tab ───────────────────────────────────────────────────────

		$this->start_controls_tab(
			'tab_button_normal',
			[ 'label' => __( 'Normal', 'barefoot-elementor-theme' ) ]
		);

		$this->add_control(
			'button_text_color',
			[
				'label'     => __( 'Text Color', 'barefoot-elementor-theme' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#1a1a2e',
				'selectors' => [
					'{{WRAPPER}} .bft-slide-btn' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'button_bg_color',
			[
				'label'     => __( 'Background Color', 'barefoot-elementor-theme' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .bft-slide-btn' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name'     => 'button_border',
				'selector' => '{{WRAPPER}} .bft-slide-btn',
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'button_box_shadow',
				'selector' => '{{WRAPPER}} .bft-slide-btn',
			]
		);

		$this->end_controls_tab();

		// Hover tab ────────────────────────────────────────────────────────

		$this->start_controls_tab(
			'tab_button_hover',
			[ 'label' => __( 'Hover', 'barefoot-elementor-theme' ) ]
		);

		$this->add_control(
			'button_text_color_hover',
			[
				'label'     => __( 'Text Color', 'barefoot-elementor-theme' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .bft-slide-btn:hover, {{WRAPPER}} .bft-slide-btn:focus-visible' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'button_bg_color_hover',
			[
				'label'     => __( 'Background Color', 'barefoot-elementor-theme' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .bft-slide-btn:hover, {{WRAPPER}} .bft-slide-btn:focus-visible' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'button_border_color_hover',
			[
				'label'     => __( 'Border Color', 'barefoot-elementor-theme' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .bft-slide-btn:hover, {{WRAPPER}} .bft-slide-btn:focus-visible' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'button_box_shadow_hover',
				'selector' => '{{WRAPPER}} .bft-slide-btn:hover, {{WRAPPER}} .bft-slide-btn:focus-visible',
			]
		);

		$this->add_control(
			'button_hover_transition',
			[
				'label'     => __( 'Transition Duration (ms)', 'barefoot-elementor-theme' ),
				'type'      => \Elementor\Controls_Manager::NUMBER,
				'default'   => 250,
				'min'       => 0,
				'step'      => 50,
				'selectors' => [
					'{{WRAPPER}} .bft-slide-btn' => 'transition-duration: {{VALUE}}ms;',
				],
			]
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->end_controls_section();

		// ── Style: Overlay ───────────────────────────────────────────────────

		$this->start_controls_section(
			'section_style_overlay',
			[
				'label' => __( 'Overlay', 'barefoot-elementor-theme' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'overlay_color',
			[
				'label'   => __( 'Overlay Color', 'barefoot-elementor-theme' ),
				'type'    => \Elementor\Controls_Manager::COLOR,
				'default' => 'rgba(0,0,0,0.4)',
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Render widget HTML.
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();
		$slides   = $settings['slides'] ?? [];

		if ( empty( $slides ) ) {
			return;
		}

		$transition         = $settings['transition_effect'] ?? 'slide';
		$nav_style          = $settings['nav_style'] ?? 'style-1';
		$persistent_content = $settings['persistent_content'] ?? '';
		$has_persistent     = ! empty( trim( wp_strip_all_tags( $persistent_content ) ) );

		$slider_config = [
			'autoplay'        => 'yes' === ( $settings['autoplay'] ?? 'yes' ),
			'autoplayDelay'   => (int) ( $settings['autoplay_delay'] ?? 5000 ),
			'showArrows'      => 'yes' === ( $settings['show_arrows'] ?? 'yes' ),
			'showDots'        => 'yes' === ( $settings['show_dots'] ?? 'yes' ),
			'transitionSpeed' => (int) ( $settings['transition_speed'] ?? 600 ),
			'transition'      => $transition,
			'navStyle'        => $nav_style,
			'slideCount'      => count( $slides ),
			'hasPersistent'   => $has_persistent,
		];

		$config_json = wp_json_encode( $slider_config );
		$wrapper_id  = 'bft-slider-' . $this->get_id();

		$wrapper_classes = implode(
			' ',
			[
				'bft-hero-slider-wrapper',
				'bft-transition-' . esc_attr( $transition ),
				'bft-nav-' . esc_attr( $nav_style ),
			]
		);
		?>
		<style>
			#<?php echo esc_attr( $wrapper_id ); ?> .bft-slide-title {
				color: <?php echo esc_attr( $settings['title_color'] ?: '#ffffff' ); ?>;
			}
			#<?php echo esc_attr( $wrapper_id ); ?> .bft-slide-text {
				color: <?php echo esc_attr( $settings['content_color'] ?: 'rgba(255,255,255,0.85)' ); ?>;
			}
			#<?php echo esc_attr( $wrapper_id ); ?> .bft-slide-overlay {
				background-color: <?php echo esc_attr( $settings['overlay_color'] ?: 'rgba(0,0,0,0.4)' ); ?>;
			}
		</style>

		<div
			id="<?php echo esc_attr( $wrapper_id ); ?>"
			class="<?php echo esc_attr( $wrapper_classes ); ?>"
			data-slider-config="<?php echo esc_attr( $config_json ); ?>"
			aria-label="<?php esc_attr_e( 'Hero Slider', 'barefoot-elementor-theme' ); ?>"
			role="region"
		>
			<div class="bft-slides-track" aria-live="off">
				<?php foreach ( $slides as $index => $slide ) : ?>
					<?php
					$bg_image  = $slide['slide_bg_image']['url'] ?? '';
					$bg_color  = $slide['slide_bg_color'] ?? '#1a1a2e';
					$btn_url   = $slide['slide_button_url']['url'] ?? '#';
					$btn_ext   = ! empty( $slide['slide_button_url']['is_external'] );
					$btn_norel = ! empty( $slide['slide_button_url']['nofollow'] );

					$slide_style = 'background-color:' . esc_attr( $bg_color ) . ';';
					if ( $bg_image ) {
						$slide_style .= 'background-image:url(' . esc_url( $bg_image ) . ');';
					}

					$btn_target = $btn_ext ? ' target="_blank"' : '';
					$btn_rel    = $btn_norel ? ' rel="nofollow"' : ( $btn_ext ? ' rel="noopener noreferrer"' : '' );
					?>
					<div
						class="bft-slide<?php echo 0 === $index ? ' bft-slide-active' : ''; ?>"
						style="<?php echo esc_attr( $slide_style ); ?>"
						aria-label="<?php echo esc_attr( sprintf( __( 'Slide %d of %d', 'barefoot-elementor-theme' ), $index + 1, count( $slides ) ) ); ?>"
						role="group"
						<?php echo 0 !== $index ? 'aria-hidden="true"' : ''; ?>
					>
						<div class="bft-slide-overlay" aria-hidden="true"></div>
						<div class="bft-slide-content">

							<?php if ( ! empty( $slide['slide_title'] ) ) : ?>
								<h2 class="bft-slide-title"><?php echo wp_kses_post( $slide['slide_title'] ); ?></h2>
							<?php endif; ?>

							<?php if ( ! empty( $slide['slide_content'] ) ) : ?>
								<p class="bft-slide-text"><?php echo esc_html( $slide['slide_content'] ); ?></p>
							<?php endif; ?>

							<?php if ( $has_persistent ) : ?>
								<div class="bft-slide-persistent-spacer" aria-hidden="true"></div>
							<?php endif; ?>

							<?php if ( ! empty( $slide['slide_button_text'] ) && ! empty( $btn_url ) ) : ?>
								<a
									href="<?php echo esc_url( $btn_url ); ?>"
									class="bft-slide-btn"
									<?php echo $btn_target; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
									<?php echo $btn_rel; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								><?php echo esc_html( $slide['slide_button_text'] ); ?></a>
							<?php endif; ?>

						</div>
					</div>
				<?php endforeach; ?>
			</div>

			<?php if ( $has_persistent ) : ?>
				<div class="bft-persistent-content" aria-live="polite">
					<?php echo wp_kses_post( $persistent_content ); ?>
				</div>
			<?php endif; ?>

			<?php if ( 'yes' === ( $settings['show_arrows'] ?? 'yes' ) && count( $slides ) > 1 ) : ?>
				<button
					class="bft-slider-arrow bft-slider-arrow-prev"
					aria-label="<?php esc_attr_e( 'Previous slide', 'barefoot-elementor-theme' ); ?>"
					type="button"
				>
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false">
						<polyline points="15 18 9 12 15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>
				</button>
				<button
					class="bft-slider-arrow bft-slider-arrow-next"
					aria-label="<?php esc_attr_e( 'Next slide', 'barefoot-elementor-theme' ); ?>"
					type="button"
				>
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false">
						<polyline points="9 18 15 12 9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>
				</button>
			<?php endif; ?>

			<?php if ( 'yes' === ( $settings['show_dots'] ?? 'yes' ) && count( $slides ) > 1 ) : ?>
				<div
					class="bft-slider-dots"
					role="tablist"
					aria-label="<?php esc_attr_e( 'Slider navigation', 'barefoot-elementor-theme' ); ?>"
				>
					<?php foreach ( $slides as $index => $slide ) : ?>
						<button
							class="bft-slider-dot<?php echo 0 === $index ? ' active' : ''; ?>"
							role="tab"
							aria-selected="<?php echo 0 === $index ? 'true' : 'false'; ?>"
							aria-label="<?php echo esc_attr( sprintf( __( 'Go to slide %d', 'barefoot-elementor-theme' ), $index + 1 ) ); ?>"
							data-index="<?php echo esc_attr( (string) $index ); ?>"
							type="button"
						></button>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}
}
