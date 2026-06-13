(function () {
	var root = document.querySelector('.imania-cart-page');
	if (!root) {
		return;
	}

	var config = window.imaniaCartPage || {};
	var updateButton = root.querySelector('button[name="update_cart"]');
	var selectAll = root.querySelector('[data-imania-cart-select-all]');
	var noticeArea = root.querySelector('[data-imania-cart-notices]');
	var summary = root.querySelector('[data-imania-cart-summary]');

	function getItemChecks() {
		return Array.prototype.slice.call(root.querySelectorAll('[data-imania-cart-item-check]'));
	}

	function enableUpdate() {
		if (updateButton) {
			updateButton.disabled = false;
		}
	}

	function normalizeNumber(value, fallback) {
		var parsed = parseFloat(value);
		return Number.isNaN(parsed) ? fallback : parsed;
	}

	function initQuantityControls() {
		root.querySelectorAll('.imania-cart-page__buy-more .quantity').forEach(function (quantityWrap) {
			if (quantityWrap.querySelector('[data-imania-cart-qty-btn]')) {
				return;
			}

			var input = quantityWrap.querySelector('input.qty');
			if (!input || input.readOnly) {
				return;
			}

			var decBtn = document.createElement('button');
			decBtn.type = 'button';
			decBtn.className = 'imania-cart-page__qty-btn';
			decBtn.setAttribute('data-imania-cart-qty-btn', 'dec');
			decBtn.setAttribute('aria-label', 'Diminuir quantidade');
			decBtn.textContent = '-';

			var incBtn = document.createElement('button');
			incBtn.type = 'button';
			incBtn.className = 'imania-cart-page__qty-btn';
			incBtn.setAttribute('data-imania-cart-qty-btn', 'inc');
			incBtn.setAttribute('aria-label', 'Aumentar quantidade');
			incBtn.textContent = '+';

			quantityWrap.insertBefore(decBtn, input);
			quantityWrap.appendChild(incBtn);
		});
	}

	function syncSelectAll() {
		var itemChecks = getItemChecks();
		if (!selectAll || !itemChecks.length) {
			return;
		}

		var checkedCount = itemChecks.filter(function (input) {
			return input.checked;
		}).length;

		selectAll.checked = checkedCount === itemChecks.length;
		selectAll.indeterminate = checkedCount > 0 && checkedCount < itemChecks.length;
	}

	function renderNotice(html) {
		if (!noticeArea || !html) {
			return;
		}

		noticeArea.innerHTML = html;
		noticeArea.hidden = false;
	}

	function escapeHtml(value) {
		var div = document.createElement('div');
		div.textContent = value || '';
		return div.innerHTML;
	}

	function renderError(message) {
		if (!noticeArea) {
			return;
		}

		noticeArea.innerHTML = '<ul class="woocommerce-error" role="alert"><li>' + escapeHtml(message) + '</li></ul>';
		noticeArea.hidden = false;
	}

	function updateHeaderCartCount(count) {
		if (typeof window.imaniaUpdateCartBadge === 'function') {
			window.imaniaUpdateCartBadge(count);
			return;
		}

		document.querySelectorAll('[data-imania-cart-drawer-trigger]').forEach(function (trigger) {
			var value = parseInt(count, 10) || 0;
			var badge = trigger.querySelector('[data-imania-cart-count]') || trigger.querySelector('.imania-cart-count');
			if (value <= 0) {
				if (badge) {
					badge.remove();
				}
				return;
			}

			if (!badge) {
				badge = document.createElement('span');
				badge.className = 'imania-cart-count';
				badge.setAttribute('data-imania-cart-count', '');
				trigger.appendChild(badge);
			}
			badge.textContent = String(value);
		});
	}

	function replaceWithEmptyState(html) {
		if (!html) {
			return;
		}

		var currentNotice = noticeArea ? noticeArea.innerHTML : '';
		root.innerHTML = '<div class="imania-cart-page__notice-area" data-imania-cart-notices aria-live="polite">' + currentNotice + '</div>' + html;
		noticeArea = root.querySelector('[data-imania-cart-notices]');
	}

	function removeCartRowWithAnimation(row, afterRemove) {
		if (!row) {
			if (typeof afterRemove === 'function') {
				afterRemove();
			}
			return;
		}

		var list = row.parentElement;
		var animatedRows = list ? Array.prototype.slice.call(list.querySelectorAll('[data-imania-cart-row]')) : [];
		var firstRects = new Map();
		animatedRows.forEach(function (node) {
			firstRects.set(node, node.getBoundingClientRect());
		});

		row.classList.add('is-removing');
		row.style.height = row.offsetHeight + 'px';
		var isDone = false;

		window.requestAnimationFrame(function () {
			row.style.height = '0px';
		});

		var finalize = function () {
			if (isDone) {
				return;
			}
			isDone = true;
			row.removeEventListener('transitionend', onEnd);
			row.remove();

			if (list) {
				var remaining = Array.prototype.slice.call(list.querySelectorAll('[data-imania-cart-row]'));
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
			}

			syncSelectAll();
			if (typeof afterRemove === 'function') {
				afterRemove();
			}
		};

		var onEnd = function (event) {
			if (event.target !== row || event.propertyName !== 'height') {
				return;
			}
			finalize();
		};

		row.addEventListener('transitionend', onEnd);
		window.setTimeout(finalize, 460);
	}

	function requestRemoveItem(link) {
		if (!config.ajaxUrl || !config.nonce) {
			window.location.href = link.href;
			return;
		}

		var row = link.closest('[data-imania-cart-row]');
		var cartItemKey = link.getAttribute('data-cart-item-key') || (row ? row.getAttribute('data-cart-item-key') : '');
		if (!cartItemKey) {
			window.location.href = link.href;
			return;
		}

		link.classList.add('is-loading');
		link.setAttribute('aria-disabled', 'true');

		fetch(config.ajaxUrl, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
			},
			body: new URLSearchParams({
				action: 'imania_cart_page_remove_item',
				nonce: config.nonce,
				cart_item_key: cartItemKey
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
				}).then(function (result) {
					if (!response.ok || !result || !result.success || !result.data) {
						var message = result && result.data && result.data.message ? result.data.message : ((config.messages && config.messages.genericError) ? config.messages.genericError : 'Erro inesperado.');
						throw new Error(message);
					}
					return result;
				});
			})
			.then(function (result) {
				var data = result.data;
				renderNotice(data.noticeHtml || '');
				if (summary && data.summaryHtml) {
					summary.innerHTML = data.summaryHtml;
				}
				updateHeaderCartCount(data.count || 0);
				if (typeof window.imaniaRefreshCartDrawer === 'function') {
					window.imaniaRefreshCartDrawer();
				}

				removeCartRowWithAnimation(row, function () {
					if (data.isEmpty && data.emptyHtml) {
						replaceWithEmptyState(data.emptyHtml);
					}
				});
			})
			.catch(function (error) {
				renderError((error && error.message) ? error.message : ((config.messages && config.messages.genericError) ? config.messages.genericError : 'Erro inesperado.'));
				link.classList.remove('is-loading');
				link.removeAttribute('aria-disabled');
			});
	}

	root.addEventListener('click', function (event) {
		var removeLink = event.target.closest('.imania-cart-page__remove');
		if (removeLink) {
			event.preventDefault();
			if (removeLink.classList.contains('is-loading')) {
				return;
			}
			requestRemoveItem(removeLink);
			return;
		}

		var qtyButton = event.target.closest('[data-imania-cart-qty-btn]');
		if (!qtyButton) {
			return;
		}

		event.preventDefault();
		var quantity = qtyButton.closest('.quantity');
		var input = quantity ? quantity.querySelector('input.qty') : null;
		if (!input) {
			return;
		}

		var current = normalizeNumber(input.value, 0);
		var step = normalizeNumber(input.getAttribute('step'), 1) || 1;
		var min = normalizeNumber(input.getAttribute('min'), 0);
		var maxAttr = input.getAttribute('max');
		var max = maxAttr ? parseFloat(maxAttr) : NaN;
		var next = qtyButton.getAttribute('data-imania-cart-qty-btn') === 'inc' ? current + step : current - step;

		if (!Number.isNaN(max)) {
			next = Math.min(next, max);
		}
		next = Math.max(next, min);

		input.value = String(next);
		input.dispatchEvent(new Event('change', { bubbles: true }));
	});

	root.addEventListener('change', function (event) {
		if (event.target.matches('input.qty')) {
			enableUpdate();
			return;
		}

		if (event.target.matches('[data-imania-cart-select-all]')) {
			getItemChecks().forEach(function (input) {
				input.checked = event.target.checked;
			});
			syncSelectAll();
			return;
		}

		if (event.target.matches('[data-imania-cart-item-check]')) {
			syncSelectAll();
		}
	});

	initQuantityControls();
	syncSelectAll();
})();
