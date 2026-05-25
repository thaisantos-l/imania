<?php

/**
 * Handle auth login via AJAX for /conta/.
 */
function imania_store_handle_auth_login_ajax()
{
	if ('POST' !== strtoupper((string) $_SERVER['REQUEST_METHOD'])) {
		imania_store_send_account_json_error(__('Metodo invalido.', 'imania-store'), 405, 'invalid_method');
	}

	$is_valid_nonce = check_ajax_referer('imania_account_login_nonce', 'nonce', false);
	if (false === $is_valid_nonce) {
		imania_store_send_account_json_error(__('Falha de seguranca. Atualize a pagina e tente novamente.', 'imania-store'), 403, 'invalid_nonce');
	}

	if (is_user_logged_in()) {
		imania_store_send_account_json_success(
			array(
				'message' => __('Sessao ja autenticada.', 'imania-store'),
				'redirect' => imania_store_get_safe_auth_redirect_url(imania_store_get_my_account_url()),
			)
		);
	}

	$customer_type = isset($_POST['customer_type']) ? sanitize_key(wp_unslash($_POST['customer_type'])) : '';
	$document_raw = isset($_POST['document']) ? sanitize_text_field(wp_unslash($_POST['document'])) : '';
	$password = isset($_POST['password']) ? (string) wp_unslash($_POST['password']) : '';

	if (!in_array($customer_type, array('pf', 'pj'), true) || '' === $document_raw || '' === $password) {
		imania_store_send_account_json_error(__('Dados de acesso invalidos.', 'imania-store'), 422, 'invalid_payload');
	}

	$document_validation = imania_store_validate_customer_document($customer_type, $document_raw);
	if (empty($document_validation['valid'])) {
		$message = 'pf' === $customer_type ? __('CPF invalido.', 'imania-store') : __('CNPJ invalido.', 'imania-store');
		imania_store_send_account_json_error($message, 422, (string) $document_validation['error']);
	}

	$normalized_document = (string) $document_validation['normalized'];
	$user = imania_store_get_user_by_document($normalized_document, $customer_type);
	if (!$user instanceof WP_User) {
		imania_store_send_account_json_error(__('Documento ou senha invalidos.', 'imania-store'), 401, 'invalid_credentials');
	}

	$user_type = imania_store_resolve_customer_type_from_user($user);
	if (in_array($user_type, array('pf', 'pj'), true) && $user_type !== $customer_type) {
		imania_store_send_account_json_error(__('Tipo de conta diferente do documento informado.', 'imania-store'), 422, 'customer_type_mismatch');
	}

	$credentials = array(
		'user_login' => $user->user_login,
		'user_password' => $password,
		'remember' => true,
	);
	$signed_user = wp_signon($credentials, is_ssl());
	if (is_wp_error($signed_user)) {
		imania_store_send_account_json_error(__('Documento ou senha invalidos.', 'imania-store'), 401, 'invalid_credentials');
	}

	imania_store_send_account_json_success(
		array(
			'message' => __('Login realizado com sucesso.', 'imania-store'),
			'redirect' => imania_store_get_safe_auth_redirect_url(imania_store_get_my_account_url()),
		)
	);
}
add_action('wp_ajax_nopriv_imania_account_login', 'imania_store_handle_auth_login_ajax');
add_action('wp_ajax_imania_account_login', 'imania_store_handle_auth_login_ajax');

/**
 * Handle auth registration via AJAX for /conta/.
 */
function imania_store_handle_auth_register_ajax()
{
	if ('POST' !== strtoupper((string) $_SERVER['REQUEST_METHOD'])) {
		imania_store_send_account_json_error(__('Metodo invalido.', 'imania-store'), 405, 'invalid_method');
	}

	$is_valid_nonce = check_ajax_referer('imania_account_register_nonce', 'nonce', false);
	if (false === $is_valid_nonce) {
		imania_store_send_account_json_error(__('Falha de seguranca. Atualize a pagina e tente novamente.', 'imania-store'), 403, 'invalid_nonce');
	}

	if (is_user_logged_in()) {
		imania_store_send_account_json_success(
			array(
				'message' => __('Sessao ja autenticada.', 'imania-store'),
				'redirect' => imania_store_get_safe_auth_redirect_url(imania_store_get_my_account_url()),
			)
		);
	}

	$customer_type = isset($_POST['customer_type']) ? sanitize_key(wp_unslash($_POST['customer_type'])) : '';
	$email = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';
	$document_raw = isset($_POST['document']) ? sanitize_text_field(wp_unslash($_POST['document'])) : '';
	$password = isset($_POST['password']) ? (string) wp_unslash($_POST['password']) : '';

	if (!in_array($customer_type, array('pf', 'pj'), true) || '' === $email || '' === $document_raw || '' === $password) {
		imania_store_send_account_json_error(__('Dados de cadastro invalidos.', 'imania-store'), 422, 'invalid_payload');
	}

	if (!is_email($email)) {
		imania_store_send_account_json_error(__('Informe um e-mail valido.', 'imania-store'), 422, 'invalid_email');
	}

	if (email_exists($email)) {
		imania_store_send_account_json_error(__('Este e-mail ja esta cadastrado.', 'imania-store'), 422, 'email_in_use');
	}

	if (strlen($password) < 8) {
		imania_store_send_account_json_error(__('A senha deve ter no minimo 8 caracteres.', 'imania-store'), 422, 'weak_password');
	}

	$document_validation = imania_store_validate_customer_document($customer_type, $document_raw);
	if (empty($document_validation['valid'])) {
		$message = 'pf' === $customer_type ? __('CPF invalido.', 'imania-store') : __('CNPJ invalido.', 'imania-store');
		imania_store_send_account_json_error($message, 422, (string) $document_validation['error']);
	}

	$normalized_document = (string) $document_validation['normalized'];
	if (imania_store_document_exists_for_another_user($normalized_document, 0)) {
		imania_store_send_account_json_error(__('Este documento ja esta vinculado a outra conta.', 'imania-store'), 422, 'duplicated_document');
	}

	$username = imania_store_generate_username_from_email($email);
	$user_id = wp_insert_user(
		array(
			'user_login' => $username,
			'user_pass' => $password,
			'user_email' => $email,
			'role' => 'customer',
		)
	);

	if (is_wp_error($user_id) || $user_id <= 0) {
		imania_store_send_account_json_error(__('Nao foi possivel concluir o cadastro.', 'imania-store'), 500, 'register_failed');
	}

	imania_store_set_customer_identity_meta($user_id, $customer_type, $normalized_document, $email);

	$credentials = array(
		'user_login' => $username,
		'user_password' => $password,
		'remember' => true,
	);
	$signed_user = wp_signon($credentials, is_ssl());
	if (is_wp_error($signed_user)) {
		wp_set_current_user($user_id);
		wp_set_auth_cookie($user_id, true, is_ssl());
	}

	imania_store_send_account_json_success(
		array(
			'message' => __('Cadastro realizado com sucesso.', 'imania-store'),
			'redirect' => imania_store_get_safe_auth_redirect_url(imania_store_get_my_account_url()),
		)
	);
}
add_action('wp_ajax_nopriv_imania_account_register', 'imania_store_handle_auth_register_ajax');
add_action('wp_ajax_imania_account_register', 'imania_store_handle_auth_register_ajax');
