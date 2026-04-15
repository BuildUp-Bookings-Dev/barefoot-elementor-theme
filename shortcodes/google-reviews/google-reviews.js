/**
 * Barefoot Theme – Google Reviews slider.
 * Vanilla JS, no dependencies.
 */
( function () {
	'use strict';

	var STAR_SVG = '<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>';

	/**
	 * Re-render stars into [data-bft-stars] for a given rating.
	 *
	 * @param {HTMLElement} starsEl
	 * @param {number}      rating
	 */
	function updateStars( starsEl, rating ) {
		if ( ! starsEl ) {
			return;
		}

		var html = '';
		for ( var i = 1; i <= 5; i++ ) {
			var cls = i <= rating ? 'bft-reviews__star is-filled' : 'bft-reviews__star is-empty';
			html += '<span class="' + cls + '">' + STAR_SVG + '</span>';
		}

		starsEl.innerHTML = html;
		starsEl.setAttribute( 'aria-label', rating + ' out of 5 stars' );
	}

	/**
	 * Initialise a single reviews carousel instance.
	 *
	 * @param {HTMLElement} wrapper
	 */
	function initReviews( wrapper ) {
		var viewport = wrapper.querySelector( '.bft-reviews__viewport' );
		var track    = wrapper.querySelector( '.bft-reviews__track' );
		var slides   = wrapper.querySelectorAll( '.bft-reviews__slide' );
		var starsEl  = wrapper.querySelector( '[data-bft-stars]' );
		var prev     = wrapper.querySelector( '.bft-reviews__btn--prev' );
		var next     = wrapper.querySelector( '.bft-reviews__btn--next' );
		var count    = slides.length;
		var autoplay = wrapper.getAttribute( 'data-autoplay' ) !== 'false';

		if ( ! track || ! viewport || count <= 1 ) {
			return;
		}

		var current     = 0;
		var timer       = null;
		var touchStartX = null;

		// ── Core navigation ───────────────────────────────────────

		function goTo( index ) {
			if ( index < 0 ) {
				index = count - 1;
			} else if ( index >= count ) {
				index = 0;
			}

			slides[ current ].setAttribute( 'aria-hidden', 'true' );
			current = index;
			slides[ current ].removeAttribute( 'aria-hidden' );

			track.style.transform = 'translateX(-' + ( current * 100 ) + '%)';

			// Match viewport height to the current slide's natural height.
			viewport.style.height = slides[ current ].offsetHeight + 'px';

			// Sync star display to current slide's rating.
			var rating = parseInt( slides[ current ].getAttribute( 'data-rating' ) || '5', 10 );
			updateStars( starsEl, rating );
		}

		// ── Autoplay ─────────────────────────────────────────────

		function startAuto() {
			if ( ! autoplay ) {
				return;
			}
			stopAuto();
			timer = setInterval( function () {
				goTo( current + 1 );
			}, 6000 );
		}

		function stopAuto() {
			if ( timer ) {
				clearInterval( timer );
				timer = null;
			}
		}

		function resetAuto() {
			stopAuto();
			startAuto();
		}

		// ── Arrow controls ────────────────────────────────────────

		if ( prev ) {
			prev.addEventListener( 'click', function () {
				goTo( current - 1 );
				resetAuto();
			} );
		}

		if ( next ) {
			next.addEventListener( 'click', function () {
				goTo( current + 1 );
				resetAuto();
			} );
		}

		// ── Pause on hover / focus ────────────────────────────────

		wrapper.addEventListener( 'mouseenter', stopAuto );
		wrapper.addEventListener( 'mouseleave', startAuto );
		wrapper.addEventListener( 'focusin',    stopAuto );
		wrapper.addEventListener( 'focusout',   startAuto );

		// ── Touch / swipe ─────────────────────────────────────────

		viewport.addEventListener( 'touchstart', function ( e ) {
			touchStartX = e.changedTouches[ 0 ].clientX;
		}, { passive: true } );

		viewport.addEventListener( 'touchend', function ( e ) {
			if ( touchStartX === null ) {
				return;
			}
			var delta = e.changedTouches[ 0 ].clientX - touchStartX;
			touchStartX = null;
			if ( Math.abs( delta ) < 40 ) {
				return;
			}
			goTo( delta < 0 ? current + 1 : current - 1 );
			resetAuto();
		}, { passive: true } );

		// ── Boot ──────────────────────────────────────────────────

		goTo( 0 );
		startAuto();
	}

	function initAll( root ) {
		( root || document ).querySelectorAll( '[data-bft-reviews]' ).forEach( initReviews );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', function () {
			initAll( document );
		} );
	} else {
		initAll( document );
	}

	// Re-init inside the Elementor editor preview.
	function bindElementorHook() {
		if ( window.elementorFrontend && window.elementorFrontend.hooks ) {
			window.elementorFrontend.hooks.addAction(
				'frontend/element_ready/barefoot-google-reviews.default',
				function ( $scope ) {
					initAll( $scope[ 0 ] );
				}
			);
		}
	}

	if ( window.elementorFrontend ) {
		bindElementorHook();
	} else {
		document.addEventListener( 'elementor/frontend/init', bindElementorHook );
	}

} )();
