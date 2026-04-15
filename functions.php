<?php
/**
 * Barefoot Elementor Theme functions.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'BAREFOOT_ELEMENTOR_THEME_VERSION', '1.0.2' );

if ( ! function_exists( 'barefoot_elementor_theme_setup' ) ) {
	/**
	 * Register theme supports and menus.
	 */
	function barefoot_elementor_theme_setup() {
		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support(
			'html5',
			[
				'search-form',
				'comment-form',
				'comment-list',
				'gallery',
				'caption',
				'script',
				'style',
				'navigation-widgets',
			]
		);
		add_theme_support(
			'custom-logo',
			[
				'height'      => 100,
				'width'       => 300,
				'flex-height' => true,
				'flex-width'  => true,
			]
		);
		add_theme_support( 'align-wide' );
		add_theme_support( 'responsive-embeds' );

		register_nav_menus(
			[
				'header-menu' => __( 'Header Menu', 'barefoot-elementor-theme' ),
				'footer-menu' => __( 'Footer Menu', 'barefoot-elementor-theme' ),
			]
		);
	}
}
add_action( 'after_setup_theme', 'barefoot_elementor_theme_setup' );

if ( ! function_exists( 'barefoot_elementor_theme_enqueue_styles' ) ) {
	/**
	 * Enqueue the single theme stylesheet.
	 */
	function barefoot_elementor_theme_enqueue_styles() {
		$style_path = get_stylesheet_directory() . '/style.css';

		wp_enqueue_style(
			'barefoot-elementor-theme',
			get_stylesheet_uri(),
			[],
			file_exists( $style_path ) ? (string) filemtime( $style_path ) : BAREFOOT_ELEMENTOR_THEME_VERSION
		);
	}
}
add_action( 'wp_enqueue_scripts', 'barefoot_elementor_theme_enqueue_styles' );

if ( ! function_exists( 'barefoot_elementor_theme_get_elementor_setting' ) ) {
	/**
	 * Read a setting from the active Elementor kit when available.
	 *
	 * @param string $setting_key Setting key.
	 * @param mixed  $default     Default value.
	 *
	 * @return mixed
	 */
	function barefoot_elementor_theme_get_elementor_setting( string $setting_key, $default = null ) {
		if ( ! class_exists( '\Elementor\Plugin' ) ) {
			return $default;
		}

		$plugin = \Elementor\Plugin::$instance ?? null;
		if ( ! $plugin || ! isset( $plugin->kits_manager ) || ! method_exists( $plugin->kits_manager, 'get_active_kit_for_frontend' ) ) {
			return $default;
		}

		$kit = $plugin->kits_manager->get_active_kit_for_frontend();
		if ( ! $kit || ! method_exists( $kit, 'get_settings_for_display' ) ) {
			return $default;
		}

		$value = $kit->get_settings_for_display( $setting_key );

		return null !== $value && '' !== $value ? $value : $default;
	}
}

if ( ! function_exists( 'barefoot_elementor_theme_get_container_width' ) ) {
	/**
	 * Return the active Elementor container width in pixels.
	 */
	function barefoot_elementor_theme_get_container_width(): int {
		$container_width = barefoot_elementor_theme_get_elementor_setting( 'container_width', null );

		if ( is_array( $container_width ) ) {
			$size = $container_width['size'] ?? null;
			if ( is_numeric( $size ) ) {
				return max( 960, (int) $size );
			}
		}

		if ( is_numeric( $container_width ) ) {
			return max( 960, (int) $container_width );
		}

		return 1340;
	}
}

if ( ! function_exists( 'barefoot_elementor_theme_enqueue_single_property_assets' ) ) {
	/**
	 * Enqueue single property template assets.
	 */
	function barefoot_elementor_theme_enqueue_single_property_assets() {
		if ( ! is_singular( 'be_property' ) ) {
			return;
		}

		$single_property_path = get_template_directory() . '/single-property.css';
		$single_property_js   = get_template_directory() . '/single-property.js';

		wp_enqueue_style(
			'barefoot-elementor-theme-fonts',
			'https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=Manrope:wght@400;500;600;700;800&display=swap',
			[],
			null
		);

		wp_enqueue_style(
			'barefoot-elementor-theme-material-symbols',
			'https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL,GRAD,opsz@100..700,0..1,-50..200,20..48',
			[],
			null
		);

		wp_enqueue_style(
			'barefoot-elementor-theme-single-property',
			get_template_directory_uri() . '/single-property.css',
			[ 'barefoot-elementor-theme', 'barefoot-elementor-theme-fonts', 'barefoot-elementor-theme-material-symbols' ],
			file_exists( $single_property_path ) ? (string) filemtime( $single_property_path ) : BAREFOOT_ELEMENTOR_THEME_VERSION
		);

		wp_enqueue_style(
			'barefoot-elementor-theme-fancybox',
			'https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.css',
			[],
			null
		);

		wp_enqueue_script(
			'barefoot-elementor-theme-fancybox',
			'https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.umd.js',
			[],
			null,
			true
		);

		wp_enqueue_script(
			'barefoot-elementor-theme-single-property',
			get_template_directory_uri() . '/single-property.js',
			[ 'barefoot-elementor-theme-fancybox' ],
			file_exists( $single_property_js ) ? (string) filemtime( $single_property_js ) : BAREFOOT_ELEMENTOR_THEME_VERSION,
			true
		);
	}
}
add_action( 'wp_enqueue_scripts', 'barefoot_elementor_theme_enqueue_single_property_assets', 20 );

if ( ! function_exists( 'barefoot_elementor_theme_register_locations' ) ) {
	/**
	 * Register Elementor theme locations when Elementor is available.
	 *
	 * @param object $elementor_theme_manager Elementor theme locations manager.
	 */
	function barefoot_elementor_theme_register_locations( $elementor_theme_manager ) {
		if ( method_exists( $elementor_theme_manager, 'register_all_core_location' ) ) {
			$elementor_theme_manager->register_all_core_location();
		}
	}
}
add_action( 'elementor/theme/register_locations', 'barefoot_elementor_theme_register_locations' );

if ( ! function_exists( 'barefoot_elementor_theme_is_assoc_array' ) ) {
	/**
	 * Check whether an array uses associative keys.
	 *
	 * @param array<mixed> $array Array to inspect.
	 *
	 * @return bool
	 */
	function barefoot_elementor_theme_is_assoc_array( array $array ) {
		if ( [] === $array ) {
			return false;
		}

		return array_keys( $array ) !== range( 0, count( $array ) - 1 );
	}
}

if ( ! function_exists( 'barefoot_elementor_theme_merge_search_widget_presets' ) ) {
	/**
	 * Merge plugin presets with theme overrides while replacing list arrays wholesale.
	 *
	 * @param array<string, mixed> $base      Plugin preset map.
	 * @param array<string, mixed> $overrides Theme override map.
	 *
	 * @return array<string, mixed>
	 */
	function barefoot_elementor_theme_merge_search_widget_presets( array $base, array $overrides ) {
		foreach ( $overrides as $key => $value ) {
			if (
				is_array( $value )
				&& isset( $base[ $key ] )
				&& is_array( $base[ $key ] )
				&& barefoot_elementor_theme_is_assoc_array( $base[ $key ] )
				&& barefoot_elementor_theme_is_assoc_array( $value )
			) {
				$base[ $key ] = barefoot_elementor_theme_merge_search_widget_presets( $base[ $key ], $value );
				continue;
			}

			$base[ $key ] = $value;
		}

		return $base;
	}
}

$barefoot_elementor_theme_search_widget_preset_file = get_template_directory() . '/config/search-widget/presets.php';
$barefoot_elementor_theme_search_widget_overrides   = file_exists( $barefoot_elementor_theme_search_widget_preset_file )
	? require $barefoot_elementor_theme_search_widget_preset_file
	: [];

if ( is_array( $barefoot_elementor_theme_search_widget_overrides ) && ! empty( $barefoot_elementor_theme_search_widget_overrides ) ) {
	add_filter(
		'barefoot_engine_search_widget_presets',
		static function ( array $presets ) use ( $barefoot_elementor_theme_search_widget_overrides ) {
			return barefoot_elementor_theme_merge_search_widget_presets(
				$presets,
				$barefoot_elementor_theme_search_widget_overrides
			);
		}
	);
}

$barefoot_elementor_theme_listings_preset_file = get_template_directory() . '/config/listings/presets.php';
$barefoot_elementor_theme_listings_overrides   = file_exists( $barefoot_elementor_theme_listings_preset_file )
	? require $barefoot_elementor_theme_listings_preset_file
	: [];

if ( is_array( $barefoot_elementor_theme_listings_overrides ) && ! empty( $barefoot_elementor_theme_listings_overrides ) ) {
	add_filter(
		'barefoot_engine_listings_presets',
		static function ( array $presets ) use ( $barefoot_elementor_theme_listings_overrides ) {
			return barefoot_elementor_theme_merge_search_widget_presets(
				$presets,
				$barefoot_elementor_theme_listings_overrides
			);
		}
	);
}

// ── Google Reviews Shortcode ────────────────────────────────────────────────

if ( ! defined( 'BAREFOOT_GOOGLE_PLACES_API_KEY' ) ) {
	define( 'BAREFOOT_GOOGLE_PLACES_API_KEY', 'AIzaSyCy7bXEo2X3nG8jU8JnbRBfmMG0yVVkVCY' );
}

if ( ! defined( 'BAREFOOT_GOOGLE_PLACES_PLACE_ID' ) ) {
	define( 'BAREFOOT_GOOGLE_PLACES_PLACE_ID', 'ChIJp7oE7I6BAIkReIOjsTPZoxQ' );
}

require_once get_template_directory() . '/shortcodes/google-reviews/google-reviews.php';

// ── Google Reviews Elementor Widget ────────────────────────────────────────

add_action(
	'elementor/widgets/register',
	static function ( $widgets_manager ) {
		if ( ! class_exists( '\Elementor\Widget_Base' ) ) {
			return;
		}
		require_once get_template_directory() . '/widgets/google-reviews/google-reviews-widget.php';
		$widgets_manager->register( new Barefoot_Google_Reviews_Widget() );
	}
);

add_action(
	'elementor/frontend/after_enqueue_styles',
	static function () {
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
	}
);

// ── Hero Slider Widget ──────────────────────────────────────────────────────

add_action(
	'elementor/widgets/register',
	static function ( $widgets_manager ) {
		if ( ! class_exists( '\Elementor\Widget_Base' ) ) {
			return;
		}
		require_once get_template_directory() . '/widgets/hero-slider/hero-slider-widget.php';
		$widgets_manager->register( new Barefoot_Hero_Slider_Widget() );
	}
);

add_action(
	'elementor/frontend/after_enqueue_styles',
	static function () {
		wp_enqueue_style(
			'barefoot-hero-slider',
			get_template_directory_uri() . '/widgets/hero-slider/hero-slider.css',
			[],
			BAREFOOT_ELEMENTOR_THEME_VERSION
		);
	}
);

add_action(
	'wp_enqueue_scripts',
	static function () {
		wp_enqueue_script(
			'barefoot-hero-slider',
			get_template_directory_uri() . '/widgets/hero-slider/hero-slider.js',
			[],
			BAREFOOT_ELEMENTOR_THEME_VERSION,
			true
		);
	}
);
