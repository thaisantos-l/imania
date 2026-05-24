(function () {
	var config = window.imaniaAuth || {};
	var root = document.querySelector('[data-imania-auth]');
	if (!root || !config.ajaxUrl || !config.loginNonce || !config.registerNonce) {
		return;
	}

	function normalizeType(type) {
		return type === 'pj' ? 'pj' : 'pf';
	}

	function getTypeMeta(type) {
		if (type === 'pj') {
			return {
				label: 'CNPJ',
				placeholder: 'Digite seu CNPJ'
			};
		}

		return {
			label: 'CPF',
			placeholder: 'Digite seu CPF'
		};
	}

	function renderNotice(formType, kind, message) {
		var notice = root.querySelector('[data-imania-auth-notice="' + formType + '"]');
		if (!notice) {
			return;
		}

		notice.className = 'imania-auth__notice';
		if (!message) {
			notice.textContent = '';
			return;
		}

		if (kind === 'error') {
			notice.classList.add('is-error');
		} else if (kind === 'success') {
			notice.classList.add('is-success');
		} else {
			notice.classList.add('is-info');
		}
		notice.textContent = message;
	}

	function setFormLoading(form, isLoading) {
		if (!form) {
			return;
		}

		var submitButton = form.querySelector('button[type="submit"]');
		if (submitButton) {
			if (!submitButton.getAttribute('data-original-text')) {
				submitButton.setAttribute('data-original-text', submitButton.textContent || '');
			}
			var originalText = submitButton.getAttribute('data-original-text') || '';
			var loadingText = submitButton.getAttribute('data-loading-text') || 'Processando...';
			submitButton.textContent = isLoading ? loadingText : originalText;
		}

		form.setAttribute('aria-busy', isLoading ? 'true' : 'false');
		form.querySelectorAll('input, button').forEach(function (field) {
			field.disabled = !!isLoading;
		});
	}

	var currentType = normalizeType(root.getAttribute('data-customer-type'));

	function setCustomerType(nextType) {
		currentType = normalizeType(nextType);
		root.setAttribute('data-customer-type', currentType);

		var meta = getTypeMeta(currentType);
		root.querySelectorAll('[data-imania-auth-type]').forEach(function (button) {
			var isActive = button.getAttribute('data-imania-auth-type') === currentType;
			button.classList.toggle('is-active', isActive);
			button.setAttribute('aria-pressed', isActive ? 'true' : 'false');
		});

		root.querySelectorAll('[data-imania-doc-label]').forEach(function (label) {
			label.textContent = meta.label;
		});

		root.querySelectorAll('[data-imania-doc-placeholder]').forEach(function (input) {
			input.setAttribute('placeholder', meta.placeholder);
		});

		renderNotice('login', '', '');
		renderNotice('register', '', '');
	}

	function getRedirectUrl(result) {
		if (result && result.data && result.data.redirect) {
			return result.data.redirect;
		}
		if (config.myAccountUrl) {
			return config.myAccountUrl;
		}
		return '/minha-conta/';
	}

	function requestAuth(payload) {
		return fetch(config.ajaxUrl, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
			},
			body: new URLSearchParams(payload)
		}).then(function (response) {
			return response.json().catch(function () {
				return {
					success: false,
					data: {
						message: (config.messages && config.messages.genericError) ? config.messages.genericError : 'Erro inesperado.'
					}
				};
			}).then(function (json) {
				if (!response.ok || !json || !json.success) {
					var message = (json && json.data && json.data.message)
						? json.data.message
						: ((config.messages && config.messages.genericError) ? config.messages.genericError : 'Erro inesperado.');
					throw new Error(message);
				}
				return json;
			});
		});
	}

	root.addEventListener('click', function (event) {
		var typeButton = event.target.closest('[data-imania-auth-type]');
		if (!typeButton) {
			return;
		}

		event.preventDefault();
		setCustomerType(typeButton.getAttribute('data-imania-auth-type'));
	});

	root.addEventListener('submit', function (event) {
		var form = event.target.closest('[data-imania-auth-form]');
		if (!form) {
			return;
		}

		event.preventDefault();
		var formType = form.getAttribute('data-imania-auth-form');
		var payload = {
			customer_type: currentType,
			imania_redirect_to: config.redirectToken || ''
		};

		if (formType === 'login') {
			payload.action = 'imania_account_login';
			payload.nonce = config.loginNonce;
			payload.document = (form.querySelector('input[name="document"]') || {}).value || '';
			payload.password = (form.querySelector('input[name="password"]') || {}).value || '';
		} else if (formType === 'register') {
			payload.action = 'imania_account_register';
			payload.nonce = config.registerNonce;
			payload.email = (form.querySelector('input[name="email"]') || {}).value || '';
			payload.document = (form.querySelector('input[name="document"]') || {}).value || '';
			payload.password = (form.querySelector('input[name="password"]') || {}).value || '';
		}

		if (!payload.action || !payload.nonce) {
			return;
		}

		setFormLoading(form, true);
		renderNotice(formType, 'info', (config.messages && config.messages.loading) ? config.messages.loading : 'Processando...');

		requestAuth(payload)
			.then(function (result) {
				var successMessage = (result.data && result.data.message)
					? result.data.message
					: ((formType === 'login' && config.messages && config.messages.loginSuccess)
						? config.messages.loginSuccess
						: ((config.messages && config.messages.registerSuccess) ? config.messages.registerSuccess : 'Concluido com sucesso.'));
				renderNotice(formType, 'success', successMessage);
				window.location.href = getRedirectUrl(result);
			})
			.catch(function (error) {
				renderNotice(formType, 'error', (error && error.message) ? error.message : ((config.messages && config.messages.genericError) ? config.messages.genericError : 'Erro inesperado.'));
			})
			.finally(function () {
				setFormLoading(form, false);
			});
	});

	setCustomerType(currentType);
})();
