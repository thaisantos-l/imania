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
