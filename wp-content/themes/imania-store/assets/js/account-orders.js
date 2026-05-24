(function () {
	function closeOrderItem(item) {
		if (!item) {
			return;
		}

		var trigger = item.querySelector('[data-imania-order-toggle]');
		var panel = item.querySelector('[data-imania-order-panel]');
		if (!trigger || !panel) {
			return;
		}

		trigger.setAttribute('aria-expanded', 'false');
		panel.hidden = true;
		item.classList.remove('is-open');
	}

	function openOrderItem(item) {
		if (!item) {
			return;
		}

		var trigger = item.querySelector('[data-imania-order-toggle]');
		var panel = item.querySelector('[data-imania-order-panel]');
		if (!trigger || !panel) {
			return;
		}

		trigger.setAttribute('aria-expanded', 'true');
		panel.hidden = false;
		item.classList.add('is-open');
	}

	document.addEventListener('click', function (event) {
		var trigger = event.target.closest('[data-imania-order-toggle]');
		if (!trigger) {
			return;
		}

		event.preventDefault();
		var item = trigger.closest('[data-imania-order-item]');
		if (!item) {
			return;
		}

		var root = item.closest('[data-imania-orders-root]');
		var isExpanded = trigger.getAttribute('aria-expanded') === 'true';

		if (root) {
			root.querySelectorAll('[data-imania-order-item]').forEach(function (node) {
				if (node !== item) {
					closeOrderItem(node);
				}
			});
		}

		if (isExpanded) {
			closeOrderItem(item);
		} else {
			openOrderItem(item);
		}
	});
})();
