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
