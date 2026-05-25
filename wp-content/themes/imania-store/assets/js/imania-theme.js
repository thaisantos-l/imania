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
	var cards = document.querySelectorAll('[data-imania-product-card-gallery]');
	if (!cards.length) {
		return;
	}

	function normalizeIndex(index, total) {
		if (total <= 0) {
			return 0;
		}
		if (index < 0) {
			return total - 1;
		}
		if (index >= total) {
			return 0;
		}
		return index;
	}

	cards.forEach(function (card) {
		var image = card.querySelector('[data-imania-product-card-image]');
		var dots = Array.prototype.slice.call(card.querySelectorAll('[data-imania-product-card-dot]'));
		if (!image || !dots.length) {
			return;
		}

		var rawGallery = card.getAttribute('data-imania-product-card-gallery') || '[]';
		var gallery = [];
		try {
			gallery = JSON.parse(rawGallery);
		} catch (error) {
			gallery = [];
		}

		if (!Array.isArray(gallery) || gallery.length <= 1) {
			return;
		}

		var activeIndex = 0;
		var transitionTimer = null;

		function setActiveSlide(nextIndex) {
			activeIndex = normalizeIndex(nextIndex, gallery.length);
			var nextImage = gallery[activeIndex] || {};
			if (transitionTimer) {
				window.clearTimeout(transitionTimer);
			}

			image.classList.add('is-switching');
			transitionTimer = window.setTimeout(function () {
				if (nextImage.src) {
					image.setAttribute('src', nextImage.src);
				}
				if (nextImage.srcset) {
					image.setAttribute('srcset', nextImage.srcset);
				} else {
					image.removeAttribute('srcset');
				}
				if (nextImage.sizes) {
					image.setAttribute('sizes', nextImage.sizes);
				} else {
					image.removeAttribute('sizes');
				}
				if (nextImage.alt) {
					image.setAttribute('alt', nextImage.alt);
				}

				window.requestAnimationFrame(function () {
					image.classList.remove('is-switching');
				});
			}, 110);

			dots.forEach(function (dot, index) {
				var isActive = index === activeIndex;
				dot.classList.toggle('is-active', isActive);
				dot.setAttribute('aria-current', isActive ? 'true' : 'false');
			});
		}

		dots.forEach(function (dot) {
			dot.addEventListener('click', function () {
				var index = parseInt(dot.getAttribute('data-slide-index'), 10);
				if (Number.isNaN(index)) {
					return;
				}
				setActiveSlide(index);
			});
		});

		var startX = null;
		card.addEventListener('touchstart', function (event) {
			if (!event.touches || !event.touches.length) {
				return;
			}
			startX = event.touches[0].clientX;
		}, { passive: true });

		card.addEventListener('touchend', function (event) {
			if (startX === null || !event.changedTouches || !event.changedTouches.length) {
				startX = null;
				return;
			}

			var deltaX = event.changedTouches[0].clientX - startX;
			startX = null;
			if (Math.abs(deltaX) < 30) {
				return;
			}

			setActiveSlide(activeIndex + (deltaX < 0 ? 1 : -1));
		}, { passive: true });
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

	function renderNotice(type, message) {
		var oldNotices = content.querySelectorAll('[data-imania-account-notice]');
		oldNotices.forEach(function (node) {
			node.remove();
		});

		if (!message) {
			return;
		}

		var notice = document.createElement('div');
		notice.setAttribute('data-imania-account-notice', '');
		notice.className = 'woocommerce-message';
		if (type === 'error') {
			notice.className = 'woocommerce-error';
		} else if (type === 'info') {
			notice.className = 'woocommerce-info';
		}
		notice.textContent = message;
		content.insertBefore(notice, content.firstChild);
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

	function saveProfileForm(form) {
		if (!form || !config.profileNonce) {
			return;
		}

		var profileSection = form.closest('.imania-account-profile');
		var formData = new FormData(form);
		formData.set('action', 'imania_account_profile_save');
		formData.set('nonce', config.profileNonce);

		var submitButton = form.querySelector('.imania-account-profile__actions .imania-btn--primary');
		var originalButtonText = submitButton ? submitButton.textContent : '';
		form.setAttribute('aria-busy', 'true');
		if (profileSection) {
			profileSection.setAttribute('aria-busy', 'true');
			profileSection.style.opacity = '0.5';
			profileSection.style.pointerEvents = 'none';
		}

		form.querySelectorAll('input, select, textarea, button').forEach(function (field) {
			field.disabled = true;
		});

		if (submitButton) {
			submitButton.textContent = 'Salvando...';
		}

		renderNotice('info', 'Processando atualizacao do perfil...');

		fetch(config.ajaxUrl, {
			method: 'POST',
			body: new URLSearchParams(formData)
		})
			.then(function (response) {
				return response.json();
			})
			.then(function (result) {
				if (!result || !result.success) {
					var message = (result && result.data && result.data.message)
						? result.data.message
						: ((config.messages && config.messages.profileSaveError) ? config.messages.profileSaveError : 'Erro ao salvar.');
					throw new Error(message);
				}

				renderNotice('success', (result.data && result.data.message) ? result.data.message : ((config.messages && config.messages.profileSaved) ? config.messages.profileSaved : 'Perfil atualizado com sucesso.'));
			})
			.catch(function (error) {
				renderNotice('error', (error && error.message) ? error.message : ((config.messages && config.messages.profileSaveError) ? config.messages.profileSaveError : 'Erro ao salvar.'));
				if (window.console && typeof window.console.error === 'function') {
					window.console.error('[imania-account-profile]', error);
				}
			})
			.finally(function () {
				form.removeAttribute('aria-busy');
				if (profileSection) {
					profileSection.removeAttribute('aria-busy');
					profileSection.style.opacity = '';
					profileSection.style.pointerEvents = '';
				}
				form.querySelectorAll('input, select, textarea, button').forEach(function (field) {
					field.disabled = false;
				});

				if (submitButton) {
					submitButton.textContent = originalButtonText || 'Salvar';
				}
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

	document.addEventListener('click', function (event) {
		var saveButton = event.target.closest('.imania-account-profile__actions .imania-btn--primary');
		if (!saveButton) {
			return;
		}

		var form = saveButton.closest('.imania-account-profile__form');
		if (!form) {
			return;
		}

		event.preventDefault();
		saveProfileForm(form);
	});

	document.addEventListener('submit', function (event) {
		var form = event.target.closest('.imania-account-profile__form');
		if (!form) {
			return;
		}

		event.preventDefault();
		saveProfileForm(form);
	});

	window.addEventListener('popstate', function () {
		var endpoint = parseEndpointFromUrl(window.location.pathname);
		loadEndpoint(endpoint, false);
	});
})();
