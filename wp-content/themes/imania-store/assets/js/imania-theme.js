(function () {
	var toggle = document.querySelector('[data-imania-menu-toggle]');
	var menu = document.querySelector('[data-imania-menu]');

	if (!toggle || !menu) {
		return;
	}

	toggle.addEventListener('click', function () {
		var expanded = toggle.getAttribute('aria-expanded') === 'true';
		toggle.setAttribute('aria-expanded', expanded ? 'false' : 'true');
		menu.classList.toggle('is-open');
	});
})();

(function () {
	var sliders = document.querySelectorAll('[data-imania-banner-swiper]');
	if (!sliders.length || typeof window.Swiper !== 'function') {
		return;
	}

	sliders.forEach(function (slider) {
		var slidesLength = slider.querySelectorAll('.swiper-slide').length;
		var pagination = slider.querySelector('[data-imania-banner-pagination]');

		new window.Swiper(slider, {
			loop: slidesLength > 1,
			speed: 700,
			slidesPerView: 1,
			spaceBetween: 0,
			allowTouchMove: slidesLength > 1,
			autoplay: slidesLength > 1 ? {
				delay: 4200,
				disableOnInteraction: false,
				pauseOnMouseEnter: true
			} : false,
			pagination: pagination ? {
				el: pagination,
				clickable: true
			} : false
		});
	});
})();

(function () {
	var modal = document.querySelector('[data-imania-login-modal]');
	if (!modal) {
		return;
	}

	var closeElements = modal.querySelectorAll('[data-imania-modal-close]');

	function openModal() {
		modal.hidden = false;
		modal.setAttribute('aria-hidden', 'false');
		document.body.classList.add('imania-modal-open');
	}

	function closeModal() {
		modal.hidden = true;
		modal.setAttribute('aria-hidden', 'true');
		document.body.classList.remove('imania-modal-open');
	}

	window.imaniaOpenLoginModal = openModal;

	document.querySelectorAll('[data-imania-login-required]').forEach(function (trigger) {
		trigger.addEventListener('click', function (event) {
			event.preventDefault();
			openModal();
		});
	});

	closeElements.forEach(function (element) {
		element.addEventListener('click', function () {
			closeModal();
		});
	});

	document.addEventListener('keydown', function (event) {
		if (event.key === 'Escape' && !modal.hidden) {
			closeModal();
		}
	});
})();

(function () {
	var config = window.imaniaWishlist || {};
	if (!document.querySelector('[data-imania-wishlist-toggle]')) {
		return;
	}

	function setButtonState(productId, isFavorited) {
		document.querySelectorAll('[data-imania-wishlist-toggle][data-product-id="' + productId + '"]').forEach(function (button) {
			button.classList.toggle('is-active', isFavorited);
			button.setAttribute('aria-pressed', isFavorited ? 'true' : 'false');
			var path = button.querySelector('path');
			if (path) {
				path.setAttribute('fill', isFavorited ? 'currentColor' : 'none');
			}
		});
	}

	function updateHeaderCount(count) {
		var container = document.querySelector('.imania-header-actions');
		if (!container) {
			return;
		}

		var favoritesLink = container.querySelector('a[aria-label="Favoritos"]');
		if (!favoritesLink) {
			return;
		}

		var badge = favoritesLink.querySelector('.imania-cart-count');
		if (count > 0) {
			if (!badge) {
				badge = document.createElement('span');
				badge.className = 'imania-cart-count';
				favoritesLink.appendChild(badge);
			}
			badge.textContent = String(count);
		} else if (badge) {
			badge.remove();
		}
	}

	function requestToggle(button, productId, mode, removeRow) {
		button.classList.add('is-loading');

		fetch(config.ajaxUrl, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
			},
			body: new URLSearchParams({
				action: 'imania_toggle_wishlist',
				nonce: config.nonce || '',
				product_id: String(productId),
				mode: mode || 'toggle'
			})
		})
			.then(function (response) {
				return response.json().catch(function () {
					return {
						success: false,
						data: {
							message: config.messages && config.messages.genericError ? config.messages.genericError : 'Erro inesperado.'
						}
					};
				});
			})
			.then(function (result) {
				if (!result || !result.success || !result.data) {
					throw new Error((result && result.data && result.data.message) ? result.data.message : (config.messages && config.messages.genericError ? config.messages.genericError : 'Erro'));
				}

				setButtonState(result.data.productId, !!result.data.isFavorited);
				updateHeaderCount(parseInt(result.data.count, 10) || 0);

				if (removeRow) {
					var item = button.closest('[data-wishlist-account-item]');
					if (item && !result.data.isFavorited) {
						item.remove();
					}
				}
			})
			.catch(function (error) {
				if (window.console && typeof window.console.error === 'function') {
					window.console.error('[imania-wishlist]', error);
				}
			})
			.finally(function () {
				button.classList.remove('is-loading');
			});
	}

	document.addEventListener('click', function (event) {
		var button = event.target.closest('[data-imania-wishlist-toggle]');
		if (!button) {
			return;
		}

		event.preventDefault();
		var productId = parseInt(button.getAttribute('data-product-id'), 10);
		if (!productId) {
			return;
		}

		if (!config.isLoggedIn) {
			if (typeof window.imaniaOpenLoginModal === 'function') {
				window.imaniaOpenLoginModal();
			} else if (config.loginUrl) {
				window.location.href = config.loginUrl;
			}
			return;
		}

		var mode = button.getAttribute('data-imania-wishlist-mode') || 'toggle';
		var removeRow = button.hasAttribute('data-imania-remove-row');
		requestToggle(button, productId, mode, removeRow);
	});
})();
