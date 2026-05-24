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
		var favoritesLink = document.querySelector('[data-imania-wishlist-link]') || document.querySelector('.imania-header-actions a[aria-label="Favoritos"]');
		if (!favoritesLink) {
			return;
		}

		var badge = favoritesLink.querySelector('[data-imania-wishlist-count]') || favoritesLink.querySelector('.imania-cart-count');
		if (count > 0) {
			if (!badge) {
				badge = document.createElement('span');
				badge.className = 'imania-cart-count';
				badge.setAttribute('data-imania-wishlist-count', '');
				favoritesLink.appendChild(badge);
			}
			badge.textContent = String(count);
		} else if (badge) {
			badge.remove();
		}
	}

	function applyWishlistFeedback(productId, isFavorited) {
		var selector = '[data-imania-wishlist-toggle][data-product-id="' + productId + '"]';
		document.querySelectorAll(selector).forEach(function (button) {
			var card = button.closest('.imania-product-card, .imania-wishlist-account__item');
			if (!card) {
				return;
			}

			card.classList.remove('imania-wishlist-feedback--added', 'imania-wishlist-feedback--removed');
			card.classList.add(isFavorited ? 'imania-wishlist-feedback--added' : 'imania-wishlist-feedback--removed');
			window.setTimeout(function () {
				card.classList.remove('imania-wishlist-feedback--added', 'imania-wishlist-feedback--removed');
			}, 280);
		});
	}

	function maybeRenderWishlistEmptyState(grid) {
		if (!grid) {
			return;
		}

		if (grid.querySelector('[data-wishlist-account-col]')) {
			return;
		}

		var endpointRoot = grid.closest('[data-imania-wishlist-endpoint]');
		var emptyText = endpointRoot && endpointRoot.getAttribute('data-empty-text') ? endpointRoot.getAttribute('data-empty-text') : 'Sua wishlist está vazia.';
		grid.innerHTML = '<div class="col-12"><div class="imania-empty-state"><p>' + emptyText + '</p></div></div>';
	}

	function removeWishlistCardWithAnimation(button) {
		var card = button.closest('[data-wishlist-account-item]');
		if (!card) {
			return;
		}

		var column = card.closest('[data-wishlist-account-col]') || card.parentElement;
		if (!column) {
			card.remove();
			return;
		}

		var grid = column.parentElement;
		var animatedColumns = grid ? Array.prototype.slice.call(grid.querySelectorAll('[data-wishlist-account-col]')) : [];
		var firstRects = new Map();
		animatedColumns.forEach(function (node) {
			firstRects.set(node, node.getBoundingClientRect());
		});

		column.classList.add('is-removing');
		column.style.height = column.offsetHeight + 'px';
		var isDone = false;

		window.requestAnimationFrame(function () {
			column.style.height = '0px';
		});

		var finalize = function () {
			if (isDone) {
				return;
			}
			isDone = true;
			column.removeEventListener('transitionend', onEnd);
			column.remove();

			if (grid) {
				var remaining = Array.prototype.slice.call(grid.querySelectorAll('[data-wishlist-account-col]'));
				remaining.forEach(function (node) {
					var first = firstRects.get(node);
					if (!first) {
						return;
					}

					var last = node.getBoundingClientRect();
					var dx = first.left - last.left;
					var dy = first.top - last.top;
					if (!dx && !dy) {
						return;
					}

					node.style.transition = 'none';
					node.style.transform = 'translate(' + dx + 'px, ' + dy + 'px)';
					window.requestAnimationFrame(function () {
						node.style.transition = 'transform 280ms ease';
						node.style.transform = '';
						window.setTimeout(function () {
							node.style.transition = '';
						}, 320);
					});
				});

				maybeRenderWishlistEmptyState(grid);
			}
		};

		var onEnd = function (event) {
			if (event.target !== column || event.propertyName !== 'height') {
				return;
			}
			finalize();
		};

		column.addEventListener('transitionend', onEnd);
		window.setTimeout(finalize, 420);
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
				applyWishlistFeedback(result.data.productId, !!result.data.isFavorited);

				if (removeRow) {
					if (!result.data.isFavorited) {
						removeWishlistCardWithAnimation(button);
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

(function () {
	var config = window.imaniaAccount || {};
	var root = document.querySelector('[data-imania-account]');
	if (!root || !config.ajaxUrl || !config.nonce) {
		return;
	}

	var content = root.querySelector('[data-imania-account-content-inner]');
	var skeleton = root.querySelector('[data-imania-account-skeleton]');
	if (!content || !skeleton) {
		return;
	}

	var allowedEndpoints = ['profile', 'orders', 'wishlist', 'payment-methods'];
	var activeEndpoint = root.getAttribute('data-current-endpoint') || 'profile';

	function setLoadingState(isLoading) {
		skeleton.hidden = !isLoading;
		content.hidden = isLoading;
	}

	function setActiveLink(endpoint) {
		document.querySelectorAll('[data-imania-account-nav]').forEach(function (link) {
			var isActive = link.getAttribute('data-endpoint') === endpoint;
			link.parentElement.classList.toggle('is-active', isActive);
			if (isActive) {
				link.setAttribute('aria-current', 'page');
			} else {
				link.removeAttribute('aria-current');
			}
		});
	}

	function getUrlForEndpoint(endpoint) {
		if (config.endpoints && config.endpoints[endpoint]) {
			return config.endpoints[endpoint];
		}

		return null;
	}

	function parseEndpointFromUrl(url) {
		var endpoint = 'profile';
		allowedEndpoints.forEach(function (key) {
			if (url.indexOf('/' + key + '/') !== -1 || url.endsWith('/' + key)) {
				endpoint = key;
			}
		});

		return endpoint;
	}

	function loadEndpoint(endpoint, updateHistory) {
		if (!endpoint || allowedEndpoints.indexOf(endpoint) === -1) {
			endpoint = 'profile';
		}

		if (endpoint === activeEndpoint && !updateHistory) {
			return;
		}

		setLoadingState(true);

		fetch(config.ajaxUrl, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
			},
			body: new URLSearchParams({
				action: 'imania_account_endpoint',
				nonce: config.nonce,
				endpoint: endpoint
			})
		})
			.then(function (response) {
				return response.json();
			})
			.then(function (result) {
				if (!result || !result.success || !result.data || !result.data.html) {
					throw new Error((config.messages && config.messages.genericError) ? config.messages.genericError : 'Erro ao carregar.');
				}

				content.innerHTML = result.data.html;
				activeEndpoint = result.data.endpoint || endpoint;
				setActiveLink(activeEndpoint);

				if (updateHistory) {
					var nextUrl = getUrlForEndpoint(activeEndpoint);
					if (nextUrl) {
						window.history.pushState({ endpoint: activeEndpoint }, '', nextUrl);
					}
				}
			})
			.catch(function (error) {
				if (window.console && typeof window.console.error === 'function') {
					window.console.error('[imania-account]', error);
				}
			})
			.finally(function () {
				setLoadingState(false);
			});
	}

	document.addEventListener('click', function (event) {
		var link = event.target.closest('[data-imania-account-nav], .imania-account__nav a');
		if (!link) {
			return;
		}

		// Let modified clicks behave natively (new tab/window).
		if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || event.button !== 0) {
			return;
		}

		var endpoint = link.getAttribute('data-endpoint');
		if (!endpoint) {
			var href = link.getAttribute('href') || '';
			endpoint = parseEndpointFromUrl(href);
		}
		if (!endpoint || allowedEndpoints.indexOf(endpoint) === -1) {
			return;
		}

		event.preventDefault();
		loadEndpoint(endpoint, true);
	});

	window.addEventListener('popstate', function () {
		var endpoint = parseEndpointFromUrl(window.location.pathname);
		loadEndpoint(endpoint, false);
	});
})();
