(function () {
	var config = window.imaniaSingleProduct || {};
	var root = document.querySelector('[data-imania-single-product]');
	if (!root) {
		return;
	}

	function setLoadedState() {
		root.setAttribute('data-loading-state', 'loaded');
		var recommended = root.querySelector('[data-imania-single-recommended]');
		if (recommended) {
			recommended.setAttribute('data-loading-state', 'loaded');
		}
	}

	function renderNotice(type, message) {
		var notice = root.querySelector('[data-imania-single-notice]');
		if (!notice) {
			return;
		}

		if (!message) {
			notice.innerHTML = '';
			return;
		}

		var klass = 'woocommerce-info';
		if (type === 'error') {
			klass = 'woocommerce-error';
		} else if (type === 'success') {
			klass = 'woocommerce-message';
		}

		notice.innerHTML = '<div class="' + klass + '">' + message + '</div>';
	}

	function setMainImageFromThumb(slide) {
		if (!slide) {
			return;
		}

		var mainImage = root.querySelector('[data-imania-main-image]');
		if (!mainImage) {
			return;
		}

		var nextSrc = slide.getAttribute('data-imania-main-src') || '';
		if (!nextSrc) {
			return;
		}

		var nextSrcset = slide.getAttribute('data-imania-main-srcset') || '';
		var nextSizes = slide.getAttribute('data-imania-main-sizes') || '';
		var nextAlt = slide.getAttribute('data-imania-main-alt') || '';

		mainImage.setAttribute('src', nextSrc);
		if (nextSrcset) {
			mainImage.setAttribute('srcset', nextSrcset);
		} else {
			mainImage.removeAttribute('srcset');
		}

		if (nextSizes) {
			mainImage.setAttribute('sizes', nextSizes);
		} else {
			mainImage.removeAttribute('sizes');
		}

		mainImage.setAttribute('alt', nextAlt);
	}

	function initGallery() {
		if (typeof window.Swiper !== 'function') {
			return;
		}

		var thumbsEl = root.querySelector('[data-imania-single-thumbs]');
		if (!thumbsEl) {
			return;
		}
		var thumbsRail = root.querySelector('[data-imania-thumbs-rail]');
		var prevArrow = thumbsRail ? thumbsRail.querySelector('[data-imania-thumbs-prev]') : null;
		var nextArrow = thumbsRail ? thumbsRail.querySelector('[data-imania-thumbs-next]') : null;

		function setActiveThumbByIndex(index) {
			var slides = thumbsEl.querySelectorAll('.swiper-slide');
			slides.forEach(function (slideEl) {
				slideEl.classList.remove('swiper-slide-thumb-active');
			});
			if (slides[index]) {
				slides[index].classList.add('swiper-slide-thumb-active');
				setMainImageFromThumb(slides[index]);
			}
		}

		var thumbsSwiper = new window.Swiper(thumbsEl, {
			direction: 'vertical',
			slidesPerView: 4,
			spaceBetween: 10,
			slideToClickedSlide: true,
			watchSlidesProgress: true,
			watchSlidesVisibility: true,
			centerInsufficientSlides: true,
			freeMode: {
				enabled: true,
				sticky: true
			},
			mousewheel: {
				enabled: true,
				forceToAxis: true,
				releaseOnEdges: false,
				sensitivity: 0.9
			},
			navigation: (prevArrow && nextArrow) ? {
				prevEl: prevArrow,
				nextEl: nextArrow
			} : undefined,
			breakpoints: {
				0: {
					direction: 'vertical'
				}
			}
		});

		thumbsEl.querySelectorAll('.swiper-slide').forEach(function (slide) {
			slide.addEventListener('click', function () {
				var slideIndex = Array.prototype.indexOf.call(thumbsSwiper.slides, slide);
				if (slideIndex >= 0) {
					thumbsSwiper.slideTo(slideIndex);
					setActiveThumbByIndex(slideIndex);
				}
			});
		});

		thumbsSwiper.on('slideChange', function () {
			setActiveThumbByIndex(thumbsSwiper.activeIndex || 0);
		});

		if (thumbsSwiper.slides && thumbsSwiper.slides.length > 0) {
			setActiveThumbByIndex(thumbsSwiper.activeIndex || 0);
		}
	}

	function initQuantityControls() {
		root.querySelectorAll('.quantity').forEach(function (quantityWrap) {
			if (quantityWrap.querySelector('[data-imania-qty-btn]')) {
				return;
			}

			var input = quantityWrap.querySelector('input.qty');
			if (!input) {
				return;
			}

			var decBtn = document.createElement('button');
			decBtn.type = 'button';
			decBtn.className = 'imania-single-product__qty-btn';
			decBtn.setAttribute('data-imania-qty-btn', 'dec');
			decBtn.textContent = '-';

			var incBtn = document.createElement('button');
			incBtn.type = 'button';
			incBtn.className = 'imania-single-product__qty-btn';
			incBtn.setAttribute('data-imania-qty-btn', 'inc');
			incBtn.textContent = '+';

			quantityWrap.insertBefore(decBtn, input);
			quantityWrap.appendChild(incBtn);
		});
	}

	function updateCartBadge(count) {
		var cartLink = document.querySelector('.imania-header-actions a[aria-label="Carrinho"]');
		if (!cartLink) {
			return;
		}

		var badge = cartLink.querySelector('.imania-cart-count');
		var value = parseInt(count, 10) || 0;
		if (value <= 0) {
			if (badge) {
				badge.remove();
			}
			return;
		}

		if (!badge) {
			badge = document.createElement('span');
			badge.className = 'imania-cart-count';
			cartLink.appendChild(badge);
		}
		badge.textContent = String(value);
	}

	function getVariationPayload(form) {
		var variationData = {};
		form.querySelectorAll('select[name^="attribute_"], input[name^="attribute_"]').forEach(function (field) {
			variationData[field.name] = field.value;
		});
		return variationData;
	}

	function submitAddToCart(form, triggerButton) {
		if (!config.ajaxUrl || !config.nonce) {
			return;
		}

		var cartWrap = form.closest('.imania-single-product__cart-wrap');
		var submitButtons = form.querySelectorAll('[data-imania-add-trigger], .single_add_to_cart_button');
		var originalTexts = [];
		submitButtons.forEach(function (button) {
			originalTexts.push(button.textContent);
			button.disabled = true;
		});

		if (cartWrap) {
			cartWrap.setAttribute('data-state', 'loading');
		}

		if (triggerButton) {
			triggerButton.textContent = (config.messages && config.messages.loading) ? config.messages.loading : 'Processando...';
		}

		renderNotice('info', (config.messages && config.messages.loading) ? config.messages.loading : 'Processando...');

		var quantityInput = form.querySelector('input.qty');
		var productIdInput = form.querySelector('input[name="product_id"], input[name="add-to-cart"]');
		var variationIdInput = form.querySelector('input[name="variation_id"]');

		var payload = new URLSearchParams();
		payload.set('action', 'imania_single_add_to_cart');
		payload.set('nonce', config.nonce);
		payload.set('product_id', productIdInput ? productIdInput.value : '0');
		payload.set('quantity', quantityInput ? quantityInput.value : '1');
		payload.set('variation_id', variationIdInput ? variationIdInput.value : '0');

		var variationData = getVariationPayload(form);
		Object.keys(variationData).forEach(function (key) {
			payload.set(key, variationData[key]);
		});

		fetch(config.ajaxUrl, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
			},
			body: payload.toString()
		})
			.then(function (response) {
				return response.json().catch(function () {
					return {
						success: false,
						data: {
							message: (config.messages && config.messages.genericError) ? config.messages.genericError : 'Erro inesperado.'
						}
					};
				}).then(function (result) {
					if (!response.ok || !result || !result.success) {
						var message = (result && result.data && result.data.message)
							? result.data.message
							: ((config.messages && config.messages.genericError) ? config.messages.genericError : 'Erro inesperado.');
						throw new Error(message);
					}
					return result;
				});
			})
			.then(function (result) {
				updateCartBadge(result.data && result.data.count ? result.data.count : 0);
				renderNotice('success', (result.data && result.data.message) ? result.data.message : ((config.messages && config.messages.added) ? config.messages.added : 'Produto adicionado ao carrinho.'));
			})
			.catch(function (error) {
				renderNotice('error', (error && error.message) ? error.message : ((config.messages && config.messages.genericError) ? config.messages.genericError : 'Erro inesperado.'));
			})
			.finally(function () {
				submitButtons.forEach(function (button, index) {
					button.disabled = false;
					if (typeof originalTexts[index] === 'string') {
						button.textContent = originalTexts[index];
					}
				});

				if (cartWrap) {
					cartWrap.setAttribute('data-state', 'idle');
				}
			});
	}

	document.addEventListener('click', function (event) {
		var qtyButton = event.target.closest('[data-imania-qty-btn]');
		if (qtyButton) {
			event.preventDefault();
			var quantity = qtyButton.closest('.quantity');
			if (!quantity) {
				return;
			}

			var input = quantity.querySelector('input.qty');
			if (!input) {
				return;
			}

			var current = parseFloat(input.value || '0');
			var step = parseFloat(input.getAttribute('step') || '1') || 1;
			var min = parseFloat(input.getAttribute('min') || '1') || 1;
			var maxAttr = input.getAttribute('max');
			var max = maxAttr ? parseFloat(maxAttr) : NaN;
			if (isNaN(current)) {
				current = min;
			}

			var next = current;
			if (qtyButton.getAttribute('data-imania-qty-btn') === 'inc') {
				next = current + step;
			} else {
				next = current - step;
			}

			if (!isNaN(max)) {
				next = Math.min(next, max);
			}
			next = Math.max(next, min);
			input.value = String(next);
			input.dispatchEvent(new Event('change', { bubbles: true }));
		}
	});

	document.addEventListener('submit', function (event) {
		var form = event.target.closest('.imania-single-product form.cart');
		if (!form) {
			return;
		}

		event.preventDefault();
		var submitter = event.submitter || form.querySelector('[data-imania-add-trigger], .single_add_to_cart_button');
		submitAddToCart(form, submitter);
	});

	document.addEventListener('click', function (event) {
		var button = event.target.closest('.imania-single-product [data-imania-add-trigger]');
		if (!button) {
			return;
		}

		var form = button.closest('form.cart');
		if (!form) {
			return;
		}

		event.preventDefault();
		submitAddToCart(form, button);
	});

	initGallery();
	initQuantityControls();
	window.setTimeout(setLoadedState, 280);
})();
