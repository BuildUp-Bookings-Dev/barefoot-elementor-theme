<?php
/**
 * Single property template for Barefoot Engine properties.
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
		$elementor_location_rendered = elementor_theme_do_location( 'single' );
	}

	if ( ! $elementor_location_rendered && have_posts() ) :
		while ( have_posts() ) :
			the_post();

			$normalize_text = static function ( $value ): string {
				if ( ! is_scalar( $value ) ) {
					return '';
				}

				return trim( sanitize_text_field( (string) $value ) );
			};

			$normalize_multiline_text = static function ( $value ): string {
				if ( ! is_scalar( $value ) ) {
					return '';
				}

				$normalized = str_replace( [ "\r\n", "\r" ], "\n", (string) $value );

				return trim( sanitize_textarea_field( $normalized ) );
			};

			$resolve_metric = static function ( array $candidates ) use ( $normalize_text ): string {
				foreach ( $candidates as $candidate ) {
					$value = $normalize_text( $candidate );
					if ( '' === $value ) {
						continue;
					}

					if ( preg_match( '/^-?\d+(?:\.\d+)?$/', $value ) === 1 ) {
						$numeric = rtrim( rtrim( $value, '0' ), '.' );
						return '' !== $numeric ? $numeric : '0';
					}

					if ( preg_match( '/(\d+(?:\.\d+)?)/', $value, $matches ) === 1 ) {
						$numeric = rtrim( rtrim( (string) $matches[1], '0' ), '.' );
						return '' !== $numeric ? $numeric : (string) $matches[1];
					}
				}

				return '';
			};

			$post_id          = get_the_ID();
			$fields           = get_post_meta( $post_id, '_be_property_fields', true );
			$fields           = is_array( $fields ) ? $fields : [];
			$stored_images    = get_post_meta( $post_id, '_be_property_images', true );
			$stored_images    = is_array( $stored_images ) ? $stored_images : [];
			$rates            = get_post_meta( $post_id, '_be_property_rates', true );
			$rates            = is_array( $rates ) ? $rates : [];
			$container_width  = function_exists( 'barefoot_elementor_theme_get_container_width' ) ? barefoot_elementor_theme_get_container_width() : 1340;

			$headline_name = $normalize_text( $fields['name'] ?? '' );
			if ( '' === $headline_name ) {
				$headline_name = get_the_title();
			}

			$headline_type = $normalize_text( $fields['a259'] ?? '' );
			if ( '' === $headline_type ) {
				$headline_type = $normalize_text( $fields['UnitType'] ?? '' );
			}

			$headline_parts = array_filter( [ $headline_name, $headline_type ] );
			$headline       = [] !== $headline_parts ? implode( ' · ', $headline_parts ) : get_the_title();
			$subheadline    = $normalize_text( $fields['PropertyTitle'] ?? '' );
			$property_type  = $normalize_text( $fields['PropertyType'] ?? '' );
			$rating         = $normalize_text( $fields['a267'] ?? '' );
			$view           = $normalize_text( $fields['a261'] ?? '' );

			$location_parts = array_filter(
				[
					$normalize_text( $fields['city'] ?? '' ),
					$normalize_text( $fields['state'] ?? '' ),
					$normalize_text( $fields['country'] ?? '' ),
				]
			);
			$location       = [] !== $location_parts ? implode( ', ', $location_parts ) : $normalize_text( $fields['propAddressNew'] ?? '' );

			$gallery_images = [];
			foreach ( $stored_images as $image_url ) {
				$url = esc_url_raw( (string) $image_url );
				if ( '' !== $url ) {
					$gallery_images[] = $url;
				}
			}
			$gallery_images = array_values( array_unique( $gallery_images ) );

			if ( [] === $gallery_images ) {
				$thumbnail_url = get_the_post_thumbnail_url( $post_id, 'full' );
				if ( is_string( $thumbnail_url ) && '' !== $thumbnail_url ) {
					$gallery_images[] = $thumbnail_url;
				}
			}

			$all_gallery_images = $gallery_images;
			$gallery_images     = array_slice( $gallery_images, 0, 4 );
			$has_gallery        = [] !== $gallery_images;
			$gallery_count      = count( $all_gallery_images );

			$sleeps    = $resolve_metric(
				[
					get_post_meta( $post_id, '_be_property_guest_count', true ),
					$fields['SleepsBeds'] ?? '',
					$fields['a53'] ?? '',
				]
			);
			$bedrooms  = $resolve_metric(
				[
					get_post_meta( $post_id, '_be_property_bedroom_count', true ),
					$fields['a56'] ?? '',
					$fields['a259'] ?? '',
					$fields['PropertyTitle'] ?? '',
				]
			);
			$bathrooms = $resolve_metric(
				[
					get_post_meta( $post_id, '_be_property_bathroom_count', true ),
					$fields['a195'] ?? '',
				]
			);
			$floors    = $resolve_metric( [ $fields['NumberFloors'] ?? '' ] );

			$description_blocks = array_filter(
				[
					$normalize_multiline_text( $fields['extdescription'] ?? '' ),
					$normalize_multiline_text( $fields['description'] ?? '' ),
				]
			);
			$description_preview = [] !== $description_blocks ? $description_blocks[0] : '';

			$amenities = [];
			if ( isset( $fields['amenities'] ) && is_array( $fields['amenities'] ) ) {
				foreach ( $fields['amenities'] as $amenity ) {
					if ( ! is_array( $amenity ) ) {
						continue;
					}

					$display = $normalize_text( $amenity['display'] ?? '' );
					if ( '' === $display ) {
						$display = $normalize_text( $amenity['label'] ?? '' );
					}
					if ( '' !== $display ) {
						$amenities[] = $display;
					}
				}
			}

			if ( [] === $amenities ) {
				$amenity_terms = get_the_terms( $post_id, 'be_property_amenity' );
				if ( is_array( $amenity_terms ) ) {
					foreach ( $amenity_terms as $term ) {
						if ( $term instanceof WP_Term ) {
							$amenities[] = $term->name;
						}
					}
				}
			}
			$amenities = array_values( array_unique( array_filter( $amenities ) ) );
			$amenity_count      = count( $amenities );
			$amenity_details    = [];
			$amenity_groups_map = [
				'Kitchen & Dining' => [],
				'Popular Amenities' => [],
				'Bath & Laundry' => [],
				'Sleeping Arrangements' => [],
				'Outdoor & Views' => [],
				'Extra Features' => [],
			];

			$resolve_amenity_meta = static function ( string $label ): array {
				$normalized = strtolower( $label );

				$icon_lookup = [
					'wifi'                   => 'wifi',
					'internet'               => 'wifi',
					'air conditioning'       => 'air',
					'heating'                => 'mode_heat',
					'washer'                 => 'local_laundry_service',
					'dryer'                  => 'local_laundry_service',
					'linens'                 => 'bed',
					'towels'                 => 'dry_cleaning',
					'bed'                    => 'king_bed',
					'bedroom'                => 'king_bed',
					'bath'                   => 'bathtub',
					'bathroom'               => 'bathtub',
					'shower'                 => 'bathtub',
					'tub'                    => 'bathtub',
					'kitchen'                => 'kitchen',
					'kitchenette'            => 'kitchen',
					'oven'                   => 'countertops',
					'toaster'                => 'breakfast_dining',
					'dishwasher'             => 'dishwasher',
					'microwave'              => 'microwave',
					'refrigerator'           => 'kitchen',
					'fridge'                 => 'kitchen',
					'coffee'                 => 'coffee_maker',
					'stove'                  => 'skillet',
					'cooktop'                => 'skillet',
					'pantry'                 => 'inventory_2',
					'pool'                   => 'pool',
					'parking'                => 'local_parking',
					'tv'                     => 'tv',
					'cable'                  => 'tv',
					'balcony'                => 'deck',
					'patio'                  => 'deck',
					'deck'                   => 'deck',
					'porch'                  => 'deck',
					'view'                   => 'landscape',
					'golf'                   => 'landscape',
					'ocean'                  => 'water',
					'beach'                  => 'beach_access',
					'lake'                   => 'water',
					'water'                  => 'water',
					'pet'                    => 'pets',
					'elevator'               => 'elevator',
					'accessible'             => 'accessible',
					'lock'                   => 'lock',
					'fireplace'              => 'fireplace',
				];

				$rules = [
					[
						'group'    => 'Kitchen & Dining',
						'keywords' => [ 'kitchen', 'kitchenette', 'oven', 'toaster', 'dishwasher', 'microwave', 'refrigerator', 'fridge', 'coffee', 'stove', 'cooktop', 'pantry', 'ice maker' ],
					],
					[
						'group'    => 'Popular Amenities',
						'keywords' => [ 'wifi', 'internet', 'air conditioning', 'heating', 'tv', 'cable', 'pool', 'parking', 'pet', 'accessible', 'elevator', 'lock', 'fireplace' ],
					],
					[
						'group'    => 'Bath & Laundry',
						'keywords' => [ 'washer', 'dryer', 'linens', 'towels', 'bath', 'bathroom', 'shower', 'tub', 'laundry' ],
					],
					[
						'group'    => 'Sleeping Arrangements',
						'keywords' => [ 'bed', 'bedroom', 'sleep' ],
					],
					[
						'group'    => 'Outdoor & Views',
						'keywords' => [ 'balcony', 'view', 'golf', 'porch', 'deck', 'patio', 'beach', 'ocean', 'water', 'lake' ],
					],
				];

				foreach ( $rules as $rule ) {
					foreach ( $rule['keywords'] as $keyword ) {
						if ( false !== strpos( $normalized, $keyword ) ) {
							$icon = 'check_circle';

							foreach ( $icon_lookup as $icon_keyword => $candidate_icon ) {
								if ( false !== strpos( $normalized, $icon_keyword ) ) {
									$icon = $candidate_icon;
									break;
								}
							}

							return [
								'group' => $rule['group'],
								'icon'  => $icon,
							];
						}
					}
				}

				return [
					'group' => 'Extra Features',
					'icon'  => 'check_circle',
				];
			};

			foreach ( $amenities as $amenity_label ) {
				$meta = $resolve_amenity_meta( $amenity_label );
				$item = [
					'label' => $amenity_label,
					'icon'  => $meta['icon'],
					'group' => $meta['group'],
				];

				$amenity_details[] = $item;
				$amenity_groups_map[ $meta['group'] ][] = $item;
			}

			$amenities_preview = array_slice( $amenity_details, 0, 10 );

			$daily_rate = null;
			$rate_items = isset( $rates['items'] ) && is_array( $rates['items'] ) ? $rates['items'] : [];
			foreach ( $rate_items as $rate_item ) {
				if ( ! is_array( $rate_item ) ) {
					continue;
				}

				$price_type = strtolower( $normalize_text( $rate_item['pricetype'] ?? '' ) );
				if ( 'daily' !== $price_type && 'weekendany' !== $price_type ) {
					continue;
				}

				$amount = $rate_item['amount'] ?? $rate_item['rent'] ?? null;
				if ( is_numeric( $amount ) ) {
					$daily_rate = (float) $amount;
					break;
				}
			}

			$latitude   = $normalize_text( $fields['Latitude'] ?? '' );
			$longitude  = $normalize_text( $fields['Longitude'] ?? '' );
			$map_query  = '';
			if ( '' !== $latitude && '' !== $longitude ) {
				$map_query = rawurlencode( $latitude . ',' . $longitude );
			} elseif ( '' !== $location ) {
				$map_query = rawurlencode( $location );
			}

			?>
			<article id="post-<?php the_ID(); ?>" <?php post_class( 'be-single-property' ); ?> style="--be-sp-max-width: <?php echo esc_attr( $container_width ); ?>px;">
				<div class="be-single-property__inner">
					<header class="be-single-property__header">
						<div class="be-single-property__header-row">
							<span class="be-single-property__chip"><?php echo esc_html( '' !== $property_type ? $property_type : __( 'Private Sanctuary', 'barefoot-elementor-theme' ) ); ?></span>
							<div class="be-single-property__rating">
								<span class="material-symbols-outlined">star</span>
								<span>
									<?php
									echo esc_html(
										'' !== $rating
											? sprintf( __( 'Unit Grade %s', 'barefoot-elementor-theme' ), $rating )
											: __( 'Property Highlights', 'barefoot-elementor-theme' )
									);
									?>
								</span>
							</div>
						</div>
						<h1 class="be-single-property__title"><?php echo esc_html( $headline ); ?></h1>
						<?php if ( '' !== $location ) : ?>
							<p class="be-single-property__location">
								<span class="material-symbols-outlined">location_on</span>
								<span><?php echo esc_html( $location ); ?></span>
							</p>
						<?php endif; ?>
					</header>

					<?php if ( $has_gallery ) : ?>
						<section class="be-single-property__gallery">
							<div class="be-single-property__gallery-grid">
								<?php if ( isset( $gallery_images[0] ) ) : ?>
									<figure class="be-single-property__gallery-item be-single-property__gallery-item--hero">
										<img src="<?php echo esc_url( $gallery_images[0] ); ?>" alt="<?php echo esc_attr( $headline ); ?>" loading="eager" />
									</figure>
								<?php endif; ?>
								<?php if ( isset( $gallery_images[1] ) ) : ?>
									<figure class="be-single-property__gallery-item">
										<img src="<?php echo esc_url( $gallery_images[1] ); ?>" alt="<?php echo esc_attr( $headline ); ?>" loading="lazy" />
									</figure>
								<?php endif; ?>
								<?php if ( isset( $gallery_images[2] ) ) : ?>
									<figure class="be-single-property__gallery-item">
										<img src="<?php echo esc_url( $gallery_images[2] ); ?>" alt="<?php echo esc_attr( $headline ); ?>" loading="lazy" />
									</figure>
								<?php endif; ?>
								<?php if ( isset( $gallery_images[3] ) ) : ?>
									<figure class="be-single-property__gallery-item be-single-property__gallery-item--wide">
										<img src="<?php echo esc_url( $gallery_images[3] ); ?>" alt="<?php echo esc_attr( $headline ); ?>" loading="lazy" />
									</figure>
								<?php endif; ?>
								<?php if ( $gallery_count > 0 ) : ?>
									<script type="application/json" class="be-single-property__gallery-data" data-be-gallery-id="property-gallery-<?php echo esc_attr( $post_id ); ?>">
										<?php
										echo wp_json_encode(
											array_map(
												static function ( string $image_url ) use ( $headline ): array {
													return [
														'src'   => $image_url,
														'type'  => 'image',
														'thumb' => $image_url,
														'caption' => $headline,
													];
												},
												$all_gallery_images
											)
										);
										?>
									</script>
									<div class="be-single-property__gallery-overlay">
										<button
											type="button"
											class="be-single-property__gallery-button"
											data-be-gallery-trigger="property-gallery-<?php echo esc_attr( $post_id ); ?>"
										>
											<span class="material-symbols-outlined">grid_view</span>
											<span><?php esc_html_e( 'Show all photos', 'barefoot-elementor-theme' ); ?></span>
										</button>
									</div>
								<?php endif; ?>
							</div>
						</section>
					<?php endif; ?>

					<div class="be-single-property__content-grid">
						<div class="be-single-property__main">
							<section class="be-single-property__facts">
								<div class="be-single-property__fact">
									<span class="material-symbols-outlined">group</span>
									<div>
										<p><?php esc_html_e( 'Guests', 'barefoot-elementor-theme' ); ?></p>
										<strong><?php echo esc_html( '' !== $sleeps ? $sleeps . ' People' : '—' ); ?></strong>
									</div>
								</div>
								<div class="be-single-property__fact">
									<span class="material-symbols-outlined">king_bed</span>
									<div>
										<p><?php esc_html_e( 'Beds', 'barefoot-elementor-theme' ); ?></p>
										<strong><?php echo esc_html( '' !== $bedrooms ? $bedrooms : '—' ); ?></strong>
									</div>
								</div>
								<div class="be-single-property__fact">
									<span class="material-symbols-outlined">bathtub</span>
									<div>
										<p><?php esc_html_e( 'Baths', 'barefoot-elementor-theme' ); ?></p>
										<strong><?php echo esc_html( '' !== $bathrooms ? $bathrooms : '—' ); ?></strong>
									</div>
								</div>
								<div class="be-single-property__fact">
									<span class="material-symbols-outlined">home_work</span>
									<div>
										<p><?php esc_html_e( 'Floors', 'barefoot-elementor-theme' ); ?></p>
										<strong><?php echo esc_html( '' !== $floors ? $floors : '—' ); ?></strong>
									</div>
								</div>
							</section>

							<?php if ( [] !== $description_blocks ) : ?>
								<section class="be-single-property__section">
									<h2><?php esc_html_e( 'Description', 'barefoot-elementor-theme' ); ?></h2>
									<?php foreach ( $description_blocks as $description_block ) : ?>
										<div class="be-single-property__description-block">
											<?php echo wp_kses_post( wpautop( esc_html( $description_block ) ) ); ?>
										</div>
									<?php endforeach; ?>
								</section>
							<?php endif; ?>

							<?php if ( [] !== $amenities ) : ?>
								<section class="be-single-property__section">
									<h2><?php esc_html_e( 'Amenities', 'barefoot-elementor-theme' ); ?></h2>
									<div class="be-single-property__amenities" role="list">
										<?php foreach ( $amenities_preview as $amenity ) : ?>
											<div class="be-single-property__amenity" role="listitem">
												<span class="material-symbols-outlined"><?php echo esc_html( $amenity['icon'] ); ?></span>
												<span><?php echo esc_html( $amenity['label'] ); ?></span>
											</div>
										<?php endforeach; ?>
									</div>
									<?php if ( $amenity_count > 0 ) : ?>
										<div class="be-single-property__section-actions">
											<button type="button" class="elementor-button elementor-size-sm be-single-property__elementor-button" data-be-modal-open="amenities">
												<span class="elementor-button-content-wrapper">
													<span class="elementor-button-text"><?php echo esc_html( sprintf( __( 'Show all %d amenities', 'barefoot-elementor-theme' ), $amenity_count ) ); ?></span>
												</span>
											</button>
										</div>
									<?php endif; ?>
								</section>
							<?php endif; ?>
						</div>

						<aside class="be-single-property__aside">
							<div class="be-single-property__sticky">
								<div class="be-single-property__booking-card">
									<div class="be-single-property__booking-price">
										<span><?php echo esc_html( null !== $daily_rate ? '$' . number_format( $daily_rate, 0 ) : '—' ); ?></span>
										<small><?php esc_html_e( 'per night', 'barefoot-elementor-theme' ); ?></small>
									</div>
									<?php if ( shortcode_exists( 'barefoot_booking_widget' ) ) : ?>
										<?php echo do_shortcode( '[barefoot_booking_widget]' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
									<?php endif; ?>
								</div>
								<?php if ( shortcode_exists( 'barefoot_pricing_table' ) ) : ?>
									<div class="be-single-property__booking-actions">
										<button type="button" class="be-single-property__text-button be-single-property__view-rates" data-be-modal-open="rates">
											<?php esc_html_e( 'View Rates', 'barefoot-elementor-theme' ); ?>
										</button>
									</div>
								<?php endif; ?>
							</div>
						</aside>
					</div>

					<?php if ( '' !== $map_query ) : ?>
						<section class="be-single-property__map-section">
							<h2><?php esc_html_e( "Where you'll be", 'barefoot-elementor-theme' ); ?></h2>
							<div class="be-single-property__map-wrap">
								<iframe
									title="<?php esc_attr_e( 'Property location map', 'barefoot-elementor-theme' ); ?>"
									src="<?php echo esc_url( 'https://maps.google.com/maps?q=' . $map_query . '&z=14&output=embed' ); ?>"
									loading="lazy"
									referrerpolicy="no-referrer-when-downgrade"
								></iframe>
								<div class="be-single-property__map-card">
									<p><?php echo esc_html( '' !== $location ? $location : __( 'Property location', 'barefoot-elementor-theme' ) ); ?></p>
									<?php if ( '' !== $subheadline ) : ?>
										<span><?php echo esc_html( wp_trim_words( $subheadline, 18, '…' ) ); ?></span>
									<?php endif; ?>
								</div>
							</div>
						</section>
					<?php endif; ?>
				</div>

				<?php if ( $amenity_count > 0 ) : ?>
					<div class="be-single-property__modal" hidden data-be-modal="amenities" aria-hidden="true">
						<div class="be-single-property__modal-backdrop" data-be-modal-close></div>
						<div class="be-single-property__modal-dialog" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'What this place offers', 'barefoot-elementor-theme' ); ?>">
							<button type="button" class="be-single-property__modal-close" data-be-modal-close aria-label="<?php esc_attr_e( 'Close', 'barefoot-elementor-theme' ); ?>">
								<span class="material-symbols-outlined">close</span>
							</button>
							<div class="be-single-property__modal-head">
								<h2><?php esc_html_e( 'What this place offers', 'barefoot-elementor-theme' ); ?></h2>
							</div>
							<?php foreach ( $amenity_groups_map as $group_label => $group_items ) : ?>
								<?php if ( [] === $group_items ) : ?>
									<?php continue; ?>
								<?php endif; ?>
								<section class="be-single-property__modal-amenity-group">
									<h3><?php echo esc_html( $group_label ); ?></h3>
									<div class="be-single-property__modal-amenities">
										<?php foreach ( $group_items as $amenity ) : ?>
											<div class="be-single-property__modal-amenity-card">
												<span class="material-symbols-outlined"><?php echo esc_html( $amenity['icon'] ); ?></span>
												<span><?php echo esc_html( $amenity['label'] ); ?></span>
											</div>
										<?php endforeach; ?>
									</div>
								</section>
							<?php endforeach; ?>
						</div>
					</div>
				<?php endif; ?>

				<?php if ( shortcode_exists( 'barefoot_pricing_table' ) ) : ?>
					<div class="be-single-property__modal be-single-property__modal--rates" hidden data-be-modal="rates" aria-hidden="true">
						<div class="be-single-property__modal-backdrop" data-be-modal-close></div>
						<div class="be-single-property__modal-dialog be-single-property__modal-dialog--rates" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Rates', 'barefoot-elementor-theme' ); ?>">
							<button type="button" class="be-single-property__modal-close" data-be-modal-close aria-label="<?php esc_attr_e( 'Close', 'barefoot-elementor-theme' ); ?>">
								<span class="material-symbols-outlined">close</span>
							</button>
							<div class="be-single-property__modal-head">
								<h2><?php esc_html_e( 'Rates', 'barefoot-elementor-theme' ); ?></h2>
							</div>
							<div class="be-single-property__rates-modal-body">
								<?php echo do_shortcode( '[barefoot_pricing_table title="Rates" show_search="true"]' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</div>
						</div>
					</div>
				<?php endif; ?>
			</article>
			<?php
		endwhile;
	endif;
	?>
</main>
<?php
get_footer();
