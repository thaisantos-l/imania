(function () {
	'use strict';

	var root = document.querySelector('[data-imania-catalog]');
	var config = window.imaniaCatalog || {};
	if (!root) {
		return;
	}

	var filter = root.querySelector('[data-imania-catalog-filter]');
	var filterToggle = root.querySelector('[data-imania-catalog-filter-toggle]');
	var filterCloseButtons = root.querySelectorAll('[data-imania-catalog-filter-close]');
	var filterForm = filter ? filter.querySelector('form') : null;
	var grid = root.querySelector('[data-imania-catalog-grid]');
	var summary = root.querySelector('.imania-catalog__summary p');
	var loadContainer = root.querySelector('.imania-catalog__load');
	var currentPage = parseInt(root.getAttribute('data-current-page'), 10) || 1;
	var totalProducts = parseInt(root.getAttribute('data-total-products'), 10) || 0;
	var isLoading = false;

	function setFilterOpen(isOpen) {
		if (!filter || !filterToggle) {
			return;
		}

		root.classList.toggle('is-filter-open', isOpen);
		filterToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
		document.body.classList.toggle('imania-catalog-filter-open', isOpen);
	}

	if (filterToggle) {
		filterToggle.addEventListener('click', function () {
			setFilterOpen(!root.classList.contains('is-filter-open'));
		});
	}

	filterCloseButtons.forEach(function (button) {
		button.addEventListener('click', function () {
			setFilterOpen(false);
		});
	});

	document.addEventListener('keydown', function (event) {
		if (event.key === 'Escape' && root.classList.contains('is-filter-open')) {
			setFilterOpen(false);
			filterToggle.focus();
		}
	});

	if (filterForm) {
		filterForm.addEventListener('submit', function () {
			filterForm.querySelectorAll('input[name="min_price"], input[name="max_price"]').forEach(function (input) {
				if (!input.value.trim()) {
					input.disabled = true;
				}
			});
		});
	}

	function formatSummary(shown, total) {
		var template = config.messages && config.messages.summary
			? config.messages.summary
			: 'Exibindo %1$d de %2$d produtos';

		return template.replace('%1$d', String(shown)).replace('%2$d', String(total));
	}

	function getLoadButton() {
		return loadContainer ? loadContainer.querySelector('[data-imania-catalog-load-more]') : null;
	}

	function setEndState() {
		if (!loadContainer) {
			return;
		}

		loadContainer.innerHTML = '<p class="imania-catalog__end">'
			+ (config.messages && config.messages.end ? config.messages.end : 'Voce chegou ao final dos produtos.')
			+ '</p>';
	}

	function setLoadingState(button, loading) {
		button.classList.toggle('is-loading', loading);
		button.setAttribute('aria-busy', loading ? 'true' : 'false');
		var label = button.querySelector('span');
		if (label) {
			label.textContent = loading
				? (config.messages && config.messages.loading ? config.messages.loading : 'Carregando...')
				: (config.messages && config.messages.loadMore ? config.messages.loadMore : 'Carregar mais');
		}
	}

	function loadNextPage(button) {
		if (isLoading || !config.ajaxUrl || !grid) {
			return;
		}

		isLoading = true;
		setLoadingState(button, true);

		var filters = config.filters || {};
		var body = new URLSearchParams({
			page: String(currentPage + 1),
			context: config.context || root.getAttribute('data-context') || 'shop',
			category: config.category || root.getAttribute('data-category') || '',
			min_price: filters.min_price || '',
			max_price: filters.max_price || ''
		});

		(filters.categories || []).forEach(function (category) {
			body.append('categories[]', category);
		});

		fetch(config.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
			},
			body: body.toString()
		})
			.then(function (response) {
				if (!response.ok) {
					throw new Error('HTTP ' + response.status);
				}
				return response.json();
			})
			.then(function (result) {
				if (!result || !result.success || !result.data) {
					throw new Error('Invalid catalog response');
				}

				if (result.data.html) {
					grid.insertAdjacentHTML('beforeend', result.data.html);
					if (typeof window.imaniaInitProductCards === 'function') {
						window.imaniaInitProductCards(grid);
					}
				}

				currentPage = parseInt(result.data.page, 10) || currentPage + 1;
				root.setAttribute('data-current-page', String(currentPage));

				var shown = Math.min(totalProducts, grid.querySelectorAll('.imania-catalog-grid__item').length);
				if (summary) {
					summary.textContent = formatSummary(shown, totalProducts);
				}

				if (!result.data.hasMore) {
					setEndState();
					return;
				}

				if (result.data.nextUrl) {
					button.setAttribute('href', result.data.nextUrl);
				}
			})
			.catch(function (error) {
				if (window.console && typeof window.console.error === 'function') {
					window.console.error('[imania-catalog]', error);
				}
				var label = button.querySelector('span');
				if (label) {
					label.textContent = config.messages && config.messages.genericError
						? config.messages.genericError
						: 'Nao foi possivel carregar mais produtos agora.';
				}
			})
			.finally(function () {
				isLoading = false;
				if (document.body.contains(button)) {
					setLoadingState(button, false);
				}
			});
	}

	if (loadContainer) {
		loadContainer.addEventListener('click', function (event) {
			var button = event.target.closest('[data-imania-catalog-load-more]');
			if (!button || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) {
				return;
			}

			event.preventDefault();
			loadNextPage(button);
		});
	}
})();
