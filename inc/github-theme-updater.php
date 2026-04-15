<?php
/**
 * GitHub release updater for Barefoot Elementor Theme.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const BAREFOOT_ELEMENTOR_THEME_GITHUB_OWNER = 'BuildUp-Bookings-Dev';
const BAREFOOT_ELEMENTOR_THEME_GITHUB_REPO  = 'barefoot-elementor-theme';
const BAREFOOT_ELEMENTOR_THEME_SLUG         = 'barefoot-elementor-theme';
const BAREFOOT_ELEMENTOR_THEME_RELEASE_TTL  = 6 * HOUR_IN_SECONDS;

/**
 * Fetch and cache the latest public GitHub release metadata.
 *
 * @param bool $force_refresh Whether to bypass the release cache.
 * @return array<string, string>|WP_Error
 */
function barefoot_elementor_theme_get_latest_release( bool $force_refresh = false ) {
	$cache_key = 'barefoot_elementor_theme_latest_release';

	if ( ! $force_refresh ) {
		$cached = get_transient( $cache_key );
		if ( is_array( $cached ) ) {
			return $cached;
		}
	}

	$request_url = sprintf(
		'https://api.github.com/repos/%s/%s/releases/latest',
		rawurlencode( BAREFOOT_ELEMENTOR_THEME_GITHUB_OWNER ),
		rawurlencode( BAREFOOT_ELEMENTOR_THEME_GITHUB_REPO )
	);

	$headers = [
		'Accept'     => 'application/vnd.github+json',
		'User-Agent' => BAREFOOT_ELEMENTOR_THEME_SLUG . '/' . BAREFOOT_ELEMENTOR_THEME_VERSION,
	];

	if ( defined( 'BAREFOOT_ELEMENTOR_THEME_GITHUB_TOKEN' ) && BAREFOOT_ELEMENTOR_THEME_GITHUB_TOKEN !== '' ) {
		$headers['Authorization'] = 'Bearer ' . BAREFOOT_ELEMENTOR_THEME_GITHUB_TOKEN;
	}

	$response = wp_remote_get(
		$request_url,
		[
			'timeout' => 10,
			'headers' => $headers,
		]
	);

	if ( is_wp_error( $response ) ) {
		return $response;
	}

	$status = (int) wp_remote_retrieve_response_code( $response );
	$body   = wp_remote_retrieve_body( $response );
	$data   = json_decode( $body, true );

	if ( $status !== 200 || ! is_array( $data ) ) {
		return new WP_Error(
			'barefoot_theme_github_release_error',
			sprintf(
				/* translators: %d is the HTTP status code returned by GitHub. */
				__( 'Unable to fetch Barefoot theme release from GitHub. HTTP %d.', 'barefoot-elementor-theme' ),
				$status
			)
		);
	}

	$tag     = isset( $data['tag_name'] ) ? (string) $data['tag_name'] : '';
	$version = ltrim( $tag, 'vV' );
	$url     = isset( $data['html_url'] ) ? (string) $data['html_url'] : '';
	$package = '';

	foreach ( (array) ( $data['assets'] ?? [] ) as $asset ) {
		if ( ! is_array( $asset ) ) {
			continue;
		}

		$name = isset( $asset['name'] ) ? (string) $asset['name'] : '';
		if ( substr( strtolower( $name ), -4 ) !== '.zip' ) {
			continue;
		}

		$package = isset( $asset['browser_download_url'] ) ? (string) $asset['browser_download_url'] : '';
		if ( $package !== '' ) {
			break;
		}
	}

	if ( $version === '' || $package === '' ) {
		return new WP_Error(
			'barefoot_theme_github_release_invalid',
			__( 'Barefoot theme release is missing a version tag or zip asset.', 'barefoot-elementor-theme' )
		);
	}

	$release = [
		'version'      => $version,
		'tag'          => $tag,
		'url'          => $url,
		'package'      => $package,
		'requires'     => '6.0',
		'requires_php' => '7.4',
	];

	set_transient( $cache_key, $release, BAREFOOT_ELEMENTOR_THEME_RELEASE_TTL );

	return $release;
}

/**
 * Inject the GitHub release into WordPress' normal theme update checks.
 *
 * @param stdClass|false $transient Theme update transient.
 * @return stdClass|false
 */
function barefoot_elementor_theme_check_for_updates( $transient ) {
	if ( ! is_object( $transient ) ) {
		return $transient;
	}

	if ( ! isset( $transient->checked ) || ! is_array( $transient->checked ) ) {
		return $transient;
	}

	$theme = wp_get_theme( BAREFOOT_ELEMENTOR_THEME_SLUG );
	if ( ! $theme->exists() ) {
		return $transient;
	}

	$current_version = isset( $transient->checked[ BAREFOOT_ELEMENTOR_THEME_SLUG ] )
		? (string) $transient->checked[ BAREFOOT_ELEMENTOR_THEME_SLUG ]
		: ( $theme->get( 'Version' ) ?: BAREFOOT_ELEMENTOR_THEME_VERSION );
	$release         = barefoot_elementor_theme_get_latest_release();

	if ( is_wp_error( $release ) ) {
		return $transient;
	}

	if ( version_compare( $release['version'], $current_version, '<=' ) ) {
		unset( $transient->response[ BAREFOOT_ELEMENTOR_THEME_SLUG ] );
		return $transient;
	}

	$transient->response[ BAREFOOT_ELEMENTOR_THEME_SLUG ] = [
		'theme'        => BAREFOOT_ELEMENTOR_THEME_SLUG,
		'new_version'  => $release['version'],
		'url'          => $release['url'],
		'package'      => $release['package'],
		'requires'     => $release['requires'],
		'requires_php' => $release['requires_php'],
	];

	return $transient;
}
add_filter( 'pre_set_site_transient_update_themes', 'barefoot_elementor_theme_check_for_updates' );

/**
 * Clear cached release data after theme updates complete.
 *
 * @param WP_Upgrader $upgrader Upgrader instance.
 * @param array       $options  Update context.
 */
function barefoot_elementor_theme_clear_update_cache( $upgrader, array $options ): void {
	if ( ( $options['type'] ?? '' ) !== 'theme' ) {
		return;
	}

	delete_transient( 'barefoot_elementor_theme_latest_release' );
}
add_action( 'upgrader_process_complete', 'barefoot_elementor_theme_clear_update_cache', 10, 2 );
