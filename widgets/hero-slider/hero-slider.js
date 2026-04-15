/**
 * Barefoot Hero Slider – vanilla JS, no dependencies.
 *
 * Transition types: slide | slide-up | fade | zoom-fade
 * Navigation styles: style-1 (arrows sides, dots bottom) |
 *                    style-2 (arrows bottom-center, dots left)
 */
( function () {
	'use strict';

	var reducedMotion =
		window.matchMedia &&
		window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

	/**
	 * Initialise a single slider instance.
	 *
	 * @param {HTMLElement} wrapper
	 */
	function initSlider( wrapper ) {

		// ── Parse config ───────────────────────────────────────────

		var config;
		try {
			config = JSON.parse( wrapper.getAttribute( 'data-slider-config' ) || '{}' );
		} catch ( e ) {
			config = {};
		}

		var autoplay        = ! reducedMotion && config.autoplay !== false;
		var autoplayDelay   = config.autoplayDelay   || 5000;
		var showArrows      = config.showArrows      !== false;
		var showDots        = config.showDots        !== false;
		var transitionSpeed = reducedMotion ? 0 : ( config.transitionSpeed || 600 );
		var transition      = config.transition      || 'slide';
		var slideCount      = config.slideCount      || 0;
		var hasPersistent   = config.hasPersistent   || false;

		if ( slideCount < 2 ) {
			// Single slide — still position persistent content if present.
			if ( hasPersistent ) {
				positionPersistentContent( wrapper );
				window.addEventListener( 'resize', debounce( function () {
					positionPersistentContent( wrapper );
				}, 150 ) );
			}
			return;
		}

		// ── DOM refs ───────────────────────────────────────────────

		var track  = wrapper.querySelector( '.bft-slides-track' );
		var slides = wrapper.querySelectorAll( '.bft-slide' );
		var dots   = wrapper.querySelectorAll( '.bft-slider-dot' );
		var prev   = wrapper.querySelector( '.bft-slider-arrow-prev' );
		var next   = wrapper.querySelector( '.bft-slider-arrow-next' );

		if ( ! track ) {
			return;
		}

		// ── Set transition speed CSS custom property ───────────────

		wrapper.style.setProperty( '--bft-transition-speed', transitionSpeed + 'ms' );

		// ── Slide-up: set pixel heights so translateY works ────────

		var slideHeight = 0;

		function setSlideUpHeights() {
			slideHeight = wrapper.offsetHeight;
			slides.forEach( function ( slide ) {
				slide.style.minHeight = slideHeight + 'px';
			} );
			// Track must be tall enough to hold all slides.
			track.style.height = ( slideCount * slideHeight ) + 'px';
		}

		if ( transition === 'slide-up' ) {
			setSlideUpHeights();
		}

		// ── State ──────────────────────────────────────────────────

		var current    = 0;
		var timer      = null;
		var touchStartX = null;
		var isAnimating = false;

		// ── Helpers ────────────────────────────────────────────────

		function updateSlideStates( index ) {
			slides.forEach( function ( slide, i ) {
				var active = i === index;
				slide.classList.toggle( 'bft-slide-active', active );
				if ( active ) {
					slide.removeAttribute( 'aria-hidden' );
				} else {
					slide.setAttribute( 'aria-hidden', 'true' );
				}
			} );
		}

		function updateDots( index ) {
			if ( ! showDots ) {
				return;
			}
			dots.forEach( function ( dot, i ) {
				var active = i === index;
				dot.classList.toggle( 'active', active );
				dot.setAttribute( 'aria-selected', active ? 'true' : 'false' );
			} );
		}

		// ── Core: go to slide ──────────────────────────────────────

		function goTo( index ) {
			if ( index < 0 ) {
				index = slideCount - 1;
			} else if ( index >= slideCount ) {
				index = 0;
			}

			switch ( transition ) {

				case 'fade':
				case 'zoom-fade':
					( function () {
						var outgoing = slides[ current ];
						var incoming = slides[ index ];

						// Outgoing slide stays fully opaque underneath (no fade-out).
						if ( outgoing && current !== index ) {
							outgoing.classList.remove( 'bft-slide-active' );
							outgoing.classList.add( 'bft-slide-leaving' );
							outgoing.setAttribute( 'aria-hidden', 'true' );
						}

						// Incoming slide fades in on top.
						incoming.classList.remove( 'bft-slide-leaving' );
						incoming.classList.add( 'bft-slide-active' );
						incoming.removeAttribute( 'aria-hidden' );

						// After the transition finishes, drop the leaving class so the
						// outgoing slide goes back to opacity:0 (hidden behind incoming).
						var speed = transitionSpeed;
						setTimeout( function () {
							slides.forEach( function ( slide, i ) {
								if ( i !== index ) {
									slide.classList.remove( 'bft-slide-leaving' );
									slide.classList.remove( 'bft-slide-active' );
								}
							} );
						}, speed + 50 );
					} )();
					break;

				case 'slide-up':
					track.style.transform =
						'translateY(-' + ( index * slideHeight ) + 'px)';
					updateSlideStates( index );
					break;

				default: // 'slide'
					track.style.transform =
						'translateX(-' + ( index * 100 ) + '%)';
					updateSlideStates( index );
					break;
			}

			updateDots( index );
			current = index;
		}

		// ── Autoplay ───────────────────────────────────────────────

		function startAutoplay() {
			if ( ! autoplay ) {
				return;
			}
			// Always clear any existing timer first — prevents double-timers
			// when mouseleave fires after a manual interaction already called resetAutoplay().
			stopAutoplay();
			timer = setInterval( function () {
				goTo( current + 1 );
			}, autoplayDelay );
		}

		function stopAutoplay() {
			if ( timer ) {
				clearInterval( timer );
				timer = null;
			}
		}

		function resetAutoplay() {
			stopAutoplay();
			startAutoplay();
		}

		// ── Arrow controls ─────────────────────────────────────────

		if ( showArrows ) {
			if ( prev ) {
				prev.addEventListener( 'click', function () {
					goTo( current - 1 );
					resetAutoplay();
				} );
			}
			if ( next ) {
				next.addEventListener( 'click', function () {
					goTo( current + 1 );
					resetAutoplay();
				} );
			}
		}

		// ── Dot controls ───────────────────────────────────────────

		if ( showDots ) {
			dots.forEach( function ( dot ) {
				dot.addEventListener( 'click', function () {
					var idx = parseInt( dot.getAttribute( 'data-index' ), 10 );
					if ( ! isNaN( idx ) ) {
						goTo( idx );
						resetAutoplay();
					}
				} );
			} );
		}

		// ── Keyboard navigation ─────────────────────────────────────

		wrapper.setAttribute( 'tabindex', '0' );
		wrapper.addEventListener( 'keydown', function ( e ) {
			if ( e.key === 'ArrowLeft' ) {
				goTo( current - 1 );
				resetAutoplay();
			} else if ( e.key === 'ArrowRight' ) {
				goTo( current + 1 );
				resetAutoplay();
			}
		} );

		// ── Pause on hover / focus ─────────────────────────────────

		wrapper.addEventListener( 'mouseenter', stopAutoplay );
		wrapper.addEventListener( 'mouseleave', startAutoplay );
		wrapper.addEventListener( 'focusin',    stopAutoplay );
		wrapper.addEventListener( 'focusout',   startAutoplay );

		// ── Touch / swipe ──────────────────────────────────────────

		wrapper.addEventListener( 'touchstart', function ( e ) {
			touchStartX = e.changedTouches[ 0 ].clientX;
		}, { passive: true } );

		wrapper.addEventListener( 'touchend', function ( e ) {
			if ( touchStartX === null ) {
				return;
			}
			var delta  = e.changedTouches[ 0 ].clientX - touchStartX;
			touchStartX = null;

			if ( Math.abs( delta ) < 50 ) {
				return;
			}
			goTo( delta < 0 ? current + 1 : current - 1 );
			resetAutoplay();
		}, { passive: true } );

		// ── Resize handling ────────────────────────────────────────

		window.addEventListener( 'resize', debounce( function () {
			if ( transition === 'slide-up' ) {
				setSlideUpHeights();
				goTo( current );
			}
			if ( hasPersistent ) {
				positionPersistentContent( wrapper );
			}
		}, 150 ) );

		// ── Boot ───────────────────────────────────────────────────

		goTo( 0 );
		startAutoplay();

		if ( hasPersistent ) {
			// Defer one frame so layout is painted and getBoundingClientRect is accurate.
			requestAnimationFrame( function () {
				positionPersistentContent( wrapper );
			} );
		}
	}

	// ── Persistent content positioning ──────────────────────────

	/**
	 * Measures the persistent content block and positions it to
	 * overlap the invisible spacer divs inside each slide.
	 *
	 * @param {HTMLElement} wrapper
	 */
	function positionPersistentContent( wrapper ) {
		var pc     = wrapper.querySelector( '.bft-persistent-content' );
		var spacer = wrapper.querySelector( '.bft-slide:first-child .bft-slide-persistent-spacer' );

		if ( ! pc || ! spacer ) {
			return;
		}

		// Temporarily remove absolute positioning so we get natural height.
		pc.style.position = '';
		pc.style.top      = '';

		var pcHeight = pc.offsetHeight;

		// Re-apply absolute positioning.
		pc.style.position = 'absolute';

		if ( ! pcHeight ) {
			return;
		}

		// Size every spacer to match.
		var spacers = wrapper.querySelectorAll( '.bft-slide-persistent-spacer' );
		spacers.forEach( function ( s ) {
			s.style.height = pcHeight + 'px';
		} );

		// Position the overlay to exactly cover the spacer in the first slide.
		// Using getBoundingClientRect diff cancels scroll offset.
		var wrapperRect = wrapper.getBoundingClientRect();
		var spacerRect  = spacer.getBoundingClientRect();
		var topOffset   = spacerRect.top - wrapperRect.top;

		pc.style.top = topOffset + 'px';
	}

	// ── Debounce utility ────────────────────────────────────────

	function debounce( fn, delay ) {
		var t;
		return function () {
			clearTimeout( t );
			t = setTimeout( fn, delay );
		};
	}

	// ── Init all sliders on the page ────────────────────────────

	function initAll() {
		document.querySelectorAll( '.bft-hero-slider-wrapper' ).forEach( initSlider );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', initAll );
	} else {
		initAll();
	}

	// Re-init inside the Elementor editor preview.
	function bindElementorHook() {
		if ( window.elementorFrontend && window.elementorFrontend.hooks ) {
			window.elementorFrontend.hooks.addAction(
				'frontend/element_ready/barefoot-hero-slider.default',
				function ( $scope ) {
					var wrapper = $scope[ 0 ].querySelector( '.bft-hero-slider-wrapper' );
					if ( wrapper ) {
						initSlider( wrapper );
					}
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
