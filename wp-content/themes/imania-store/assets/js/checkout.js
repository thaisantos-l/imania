(function ($) {
	'use strict';

	var selectors = {
		root: '[data-imania-checkout]',
		stepButton: '[data-imania-checkout-step-target]',
		panel: '[data-imania-checkout-step-panel]',
		next: '[data-imania-checkout-next]',
		prev: '[data-imania-checkout-prev]',
		placeOrderTarget: '[data-imania-place-order-target]',
		summaryClone: '[data-imania-checkout-summary-clone]'
	};

	var cepRequest = null;
	var cepAbort = null;
	var stepOrder = {
		details: 1,
		payment: 2,
		review: 3
	};

	function getRoot() {
		return document.querySelector(selectors.root);
	}

	function normalizeCep(value) {
		return String(value || '').replace(/\D+/g, '').slice(0, 8);
	}

	function setFieldValue(fieldId, value) {
		var field = document.getElementById(fieldId);
		if (!field || !value) {
			return;
		}

		field.value = value;
		field.dispatchEvent(new Event('change', { bubbles: true }));
		if (window.jQuery) {
			window.jQuery(field).trigger('change');
		}
	}

	function fillAddressFromCep(address) {
		if (!address || address.erro) {
			return;
		}

		setFieldValue('billing_address_1', address.logradouro || '');
		setFieldValue('billing_neighborhood', address.bairro || '');
		setFieldValue('billing_city', address.localidade || '');
		setFieldValue('billing_state', address.uf || '');
		setFieldValue('billing_country', 'BR');

		if (window.jQuery) {
			window.jQuery(document.body).trigger('update_checkout');
		}
	}

	function lookupCep(cep) {
		if (cep.length !== 8 || cep === cepRequest) {
			return;
		}

		cepRequest = cep;
		if (cepAbort) {
			cepAbort.abort();
		}

		cepAbort = window.AbortController ? new AbortController() : null;

		fetch('https://viacep.com.br/ws/' + encodeURIComponent(cep) + '/json/', {
			credentials: 'omit',
			signal: cepAbort ? cepAbort.signal : undefined
		})
			.then(function (response) {
				if (!response.ok) {
					throw new Error('CEP request failed');
				}

				return response.json();
			})
			.then(fillAddressFromCep)
			.catch(function (error) {
				if (!error || error.name !== 'AbortError') {
					cepRequest = null;
				}
			});
	}

	function setStep(step) {
		var root = getRoot();
		if (!root) {
			return;
		}

		root.querySelectorAll(selectors.panel).forEach(function (panel) {
			var isActive = panel.getAttribute('data-imania-checkout-step-panel') === step;
			panel.hidden = !isActive;
			panel.classList.toggle('is-active', isActive);
		});

		root.querySelectorAll(selectors.stepButton).forEach(function (button) {
			var isActive = button.getAttribute('data-imania-checkout-step-target') === step;
			button.classList.toggle('is-active', isActive);
			if (isActive) {
				button.setAttribute('aria-current', 'step');
			} else {
				button.removeAttribute('aria-current');
			}
		});

		root.setAttribute('data-current-step', step);
	}

	function getPanel(step) {
		var root = getRoot();
		return root ? root.querySelector('[data-imania-checkout-step-panel="' + step + '"]') : null;
	}

	function getFieldControl(row) {
		return row ? row.querySelector('input:not([type="hidden"]), select, textarea') : null;
	}

	function isRequiredControlValid(row, control) {
		if (!row || !control || control.disabled || row.classList.contains('imania-checkout-field--hidden')) {
			return true;
		}

		if (control.getAttribute('data-imania-document-valid') === '0') {
			return false;
		}

		if (control.type === 'checkbox' || control.type === 'radio') {
			return control.checked;
		}

		if (!String(control.value || '').trim()) {
			return false;
		}

		return !control.validity || control.validity.valid;
	}

	function setFieldValidationState(row, control, isValid) {
		if (!row || !control) {
			return;
		}

		row.classList.toggle('woocommerce-invalid', !isValid);
		row.classList.toggle('woocommerce-invalid-required-field', !isValid);
		row.classList.toggle('woocommerce-validated', isValid);

		if (isValid) {
			control.removeAttribute('aria-invalid');
		} else {
			control.setAttribute('aria-invalid', 'true');
		}
	}

	function showStepNotice(panel, message) {
		if (!panel) {
			return;
		}

		var notice = panel.querySelector('[data-imania-step-notice]');
		if (!notice) {
			notice = document.createElement('div');
			notice.className = 'imania-checkout__step-notice';
			notice.setAttribute('data-imania-step-notice', '');
			notice.setAttribute('role', 'alert');
			var actions = panel.querySelector('.imania-checkout__actions');
			panel.insertBefore(notice, actions || null);
		}

		notice.textContent = message;
	}

	function clearStepNotice(panel) {
		var notice = panel ? panel.querySelector('[data-imania-step-notice]') : null;
		if (notice) {
			notice.remove();
		}
	}

	function focusInvalidControl(control) {
		if (!control) {
			return;
		}

		if (typeof control.focus === 'function') {
			control.focus({ preventScroll: true });
		}
		if (typeof control.scrollIntoView === 'function') {
			control.scrollIntoView({ behavior: 'smooth', block: 'center' });
		}
	}

	function validateRequiredRows(scope, noticePanel) {
		if (!scope) {
			return true;
		}

		noticePanel = noticePanel || scope;
		var firstInvalid = null;
		scope.querySelectorAll('.validate-required').forEach(function (row) {
			if (!row.getClientRects().length) {
				return;
			}

			var control = getFieldControl(row);
			if (control && window.jQuery) {
				window.jQuery(control).trigger('validate');
			}
			var isValid = isRequiredControlValid(row, control);
			if (isValid && row.classList.contains('woocommerce-invalid')) {
				isValid = false;
			}
			setFieldValidationState(row, control, isValid);
			if (!isValid && !firstInvalid) {
				firstInvalid = control;
			}
		});

		if (firstInvalid) {
			showStepNotice(noticePanel, 'Preencha corretamente todos os campos obrigatorios antes de continuar.');
			focusInvalidControl(firstInvalid);
			return false;
		}

		clearStepNotice(noticePanel);
		return true;
	}

	function validatePaymentStep() {
		var panel = getPanel('payment');
		if (!panel) {
			return true;
		}

		var selectedMethod = panel.querySelector('input[name="payment_method"]:checked');
		if (!selectedMethod) {
			showStepNotice(panel, 'Selecione uma forma de pagamento antes de continuar.');
			return false;
		}

		var selectedContainer = selectedMethod.closest('.wc_payment_method') || panel;
		return validateRequiredRows(selectedContainer, panel);
	}

	function canOpenStep(step) {
		var root = getRoot();
		var currentStep = root ? root.getAttribute('data-current-step') || 'details' : 'details';
		if (!stepOrder[step] || !stepOrder[currentStep]) {
			return false;
		}

		if (stepOrder[step] <= stepOrder[currentStep]) {
			return true;
		}

		if ('details' === currentStep) {
			if (!validateRequiredRows(getPanel('details'))) {
				return false;
			}

			if ('review' === step) {
				setStep('payment');
				return false;
			}

			return true;
		}

		return 'payment' !== currentStep || validatePaymentStep();
	}

	function relocatePlaceOrder() {
		var root = getRoot();
		if (!root) {
			return;
		}

		var target = root.querySelector(selectors.placeOrderTarget);
		var paymentBox = root.querySelector('.woocommerce-checkout-payment');
		var placeOrder = paymentBox ? paymentBox.querySelector('.place-order') : null;
		if (!target) {
			return;
		}

		target.querySelectorAll('.place-order').forEach(function (existingPlaceOrder) {
			if (existingPlaceOrder !== placeOrder) {
				existingPlaceOrder.remove();
			}
		});

		if (!placeOrder || target.contains(placeOrder)) {
			return;
		}

		target.appendChild(placeOrder);
	}

	function cloneSummaryTotals() {
		var root = getRoot();
		if (!root) {
			return;
		}

		var target = root.querySelector(selectors.summaryClone);
		var review = root.querySelector('.woocommerce-checkout-review-order-table');
		if (!target || !review) {
			return;
		}

		var clone = review.cloneNode(true);
		clone.removeAttribute('id');
		target.textContent = '';
		target.appendChild(clone);
	}

	function bindEvents() {
		var root = getRoot();
		if (!root || root.getAttribute('data-imania-checkout-bound') === '1') {
			return;
		}

		root.setAttribute('data-imania-checkout-bound', '1');

		root.addEventListener('click', function (event) {
			var eventTarget = event.target;
			var targetButton = eventTarget && eventTarget.closest ?
				eventTarget.closest(selectors.stepButton + ', ' + selectors.next + ', ' + selectors.prev) :
				null;
			if (!targetButton || !root.contains(targetButton)) {
				return;
			}

			var isPrevious = targetButton.matches(selectors.prev);
			var step = targetButton.getAttribute('data-imania-checkout-step-target') ||
				targetButton.getAttribute('data-imania-checkout-next') ||
				targetButton.getAttribute('data-imania-checkout-prev');

			if (step && (isPrevious || canOpenStep(step))) {
				setStep(step);
			}
		});

		root.addEventListener('input', function (event) {
			if (!event.target || event.target.id !== 'billing_postcode') {
				return;
			}

			var cep = normalizeCep(event.target.value);
			if (cep.length === 8) {
				lookupCep(cep);
			}
		});

		root.addEventListener('blur', function (event) {
			if (!event.target || event.target.id !== 'billing_postcode') {
				return;
			}

			lookupCep(normalizeCep(event.target.value));
		}, true);
	}

	function refreshCheckoutLayout() {
		var root = getRoot();
		if (!root) {
			return;
		}

		bindEvents();
		relocatePlaceOrder();
		cloneSummaryTotals();
		if (!root.getAttribute('data-current-step')) {
			setStep('details');
		}
	}

	document.addEventListener('DOMContentLoaded', refreshCheckoutLayout);

	$(document.body).on('updated_checkout payment_method_selected checkout_error', function (event) {
		refreshCheckoutLayout();
		if (event.type === 'checkout_error') {
			setStep('details');
		}
	});
})(jQuery);
