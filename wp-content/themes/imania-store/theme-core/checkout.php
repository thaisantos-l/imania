<?php
/**
 * Checkout presentation helpers.
 *
 * @package Imania_Store
 */

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Whether current request is running on a local development host.
 *
 * @return bool
 */
function imania_store_is_local_checkout_host()
{
	$host = isset($_SERVER['HTTP_HOST']) ? strtolower((string) wp_unslash($_SERVER['HTTP_HOST'])) : '';
	$host = trim($host, '[]');
	if (in_array($host, array('localhost', '127.0.0.1', '::1'), true)) {
		return true;
	}

	$host = preg_replace('/:\d+$/', '', $host);
	$host = is_string($host) ? $host : '';

	return in_array($host, array('localhost', '127.0.0.1', '::1'), true);
}

/**
 * Do not force HTTPS checkout on local WAMP hosts without a TLS listener.
 *
 * @param mixed $pre_option Short-circuit option value.
 *
 * @return mixed
 */
function imania_store_disable_local_checkout_ssl($pre_option)
{
	if (imania_store_is_local_checkout_host()) {
		return 'no';
	}

	return $pre_option;
}
add_filter('pre_option_woocommerce_force_ssl_checkout', 'imania_store_disable_local_checkout_ssl');

/**
 * Redirect guests away from checkout into the custom auth page.
 */
function imania_store_redirect_guest_checkout_to_auth()
{
	if (!function_exists('is_checkout') || !is_checkout() || is_user_logged_in()) {
		return;
	}

	if (function_exists('is_wc_endpoint_url') && (is_wc_endpoint_url('order-pay') || is_wc_endpoint_url('order-received'))) {
		return;
	}

	$checkout_url = set_url_scheme(wc_get_checkout_url(), is_ssl() ? 'https' : 'http');
	$redirect_token = base64_encode($checkout_url);
	$login_url = add_query_arg('imania_redirect_to', $redirect_token, imania_store_get_conta_url());

	wp_safe_redirect($login_url);
	exit;
}
add_action('template_redirect', 'imania_store_redirect_guest_checkout_to_auth', 2);

/**
 * Return the user's checkout document meta.
 *
 * @param int $user_id User id.
 *
 * @return array{type:string,persontype:string,document:string,field:string}
 */
function imania_store_get_checkout_document_meta($user_id)
{
	$user_id = absint($user_id);
	$type = function_exists('imania_store_resolve_customer_type') ? imania_store_resolve_customer_type($user_id) : null;
	$type = 'pj' === $type ? 'pj' : 'pf';
	$field = 'pj' === $type ? 'billing_cnpj' : 'billing_cpf';
	$document = (string) get_user_meta($user_id, $field, true);

	if ('' === $document) {
		$document = (string) get_user_meta($user_id, 'imania_document', true);
	}

	return array(
		'type' => $type,
		'persontype' => 'pj' === $type ? '2' : '1',
		'document' => $document,
		'field' => $field,
	);
}

/**
 * Normalize document digits.
 *
 * @param string $document Raw document.
 *
 * @return string
 */
function imania_store_normalize_checkout_document($document)
{
	$normalized = preg_replace('/\D+/', '', (string) $document);
	return is_string($normalized) ? $normalized : '';
}

/**
 * Format a checkout document for display.
 *
 * @param string $document Raw document.
 * @param string $type     pf|pj.
 *
 * @return string
 */
function imania_store_format_checkout_document($document, $type)
{
	$document = imania_store_normalize_checkout_document($document);

	if ('pf' === $type && 11 === strlen($document)) {
		return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $document);
	}

	if ('pj' === $type && 14 === strlen($document)) {
		return preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $document);
	}

	return $document;
}

/**
 * Return whether the account document is valid for checkout.
 *
 * @param array $document_meta Checkout document meta.
 *
 * @return bool
 */
function imania_store_is_checkout_document_valid(array $document_meta)
{
	$document = imania_store_normalize_checkout_document($document_meta['document']);
	if ('' === $document || !function_exists('imania_store_validate_customer_document')) {
		return false;
	}

	$validation = imania_store_validate_customer_document($document_meta['type'], $document);
	return !empty($validation['valid']);
}

/**
 * Always display the authenticated account document in the checkout field.
 *
 * @param mixed  $value Current checkout value.
 * @param string $input Checkout field key.
 *
 * @return mixed
 */
function imania_store_checkout_document_field_value($value, $input)
{
	if (!is_user_logged_in() || !in_array($input, array('billing_cpf', 'billing_cnpj'), true)) {
		return $value;
	}

	$document_meta = imania_store_get_checkout_document_meta(get_current_user_id());
	if ($input !== $document_meta['field']) {
		return '';
	}

	return imania_store_format_checkout_document($document_meta['document'], $document_meta['type']);
}
add_filter('woocommerce_checkout_get_value', 'imania_store_checkout_document_field_value', 20, 2);

/**
 * Apply checkout layout classes without keeping WooCommerce row width classes.
 *
 * @param array  $field Field config.
 * @param string $size  full|half.
 *
 * @return array
 */
function imania_store_set_checkout_field_layout(array $field, $size)
{
	$classes = isset($field['class']) && is_array($field['class']) ? $field['class'] : array();
	$classes = array_values(
		array_filter(
			$classes,
			static function ($class) {
				return !in_array($class, array('form-row-first', 'form-row-last', 'form-row-wide'), true);
			}
		)
	);

	$classes[] = 'imania-checkout-field--' . ('full' === $size ? 'full' : 'half');
	$field['class'] = array_values(array_unique($classes));

	return $field;
}

/**
 * Pre-fill and classify checkout fields using logged customer data.
 *
 * @param array $fields Checkout fields.
 *
 * @return array
 */
function imania_store_prepare_checkout_fields($fields)
{
	if (!is_user_logged_in()) {
		return $fields;
	}

	$user_id = get_current_user_id();
	$user = wp_get_current_user();
	$document_meta = imania_store_get_checkout_document_meta($user_id);
	$document_is_valid = imania_store_is_checkout_document_valid($document_meta);
	$formatted_document = imania_store_format_checkout_document($document_meta['document'], $document_meta['type']);
	$field_order = array(
		'billing_country' => array('priority' => 10, 'layout' => 'full'),
		'billing_first_name' => array('priority' => 20, 'layout' => 'half'),
		'billing_last_name' => array('priority' => 30, 'layout' => 'half'),
		'billing_cpf' => array('priority' => 40, 'layout' => 'half'),
		'billing_cnpj' => array('priority' => 40, 'layout' => 'half'),
		'billing_email' => array('priority' => 50, 'layout' => 'half'),
		'billing_phone' => array('priority' => 60, 'layout' => 'half'),
		'billing_postcode' => array('priority' => 70, 'layout' => 'half'),
		'billing_state' => array('priority' => 80, 'layout' => 'half'),
		'billing_city' => array('priority' => 90, 'layout' => 'half'),
		'billing_address_1' => array('priority' => 100, 'layout' => 'half'),
		'billing_neighborhood' => array('priority' => 110, 'layout' => 'half'),
		'billing_address_2' => array('priority' => 120, 'layout' => 'half'),
		'billing_number' => array('priority' => 130, 'layout' => 'half'),
	);
	$billing_defaults = array(
		'billing_phone',
		'billing_address_1',
		'billing_address_2',
		'billing_number',
		'billing_neighborhood',
		'billing_postcode',
		'billing_city',
		'billing_state',
	);

	foreach ($billing_defaults as $field_key) {
		if (isset($fields['billing'][$field_key])) {
			$fields['billing'][$field_key]['default'] = (string) get_user_meta($user_id, $field_key, true);
		}
	}

	foreach ($field_order as $field_key => $field_config) {
		if (isset($fields['billing'][$field_key])) {
			$fields['billing'][$field_key]['priority'] = (int) $field_config['priority'];
			$fields['billing'][$field_key] = imania_store_set_checkout_field_layout($fields['billing'][$field_key], (string) $field_config['layout']);
		}
	}

	if (isset($fields['billing']['billing_first_name'])) {
		$fields['billing']['billing_first_name']['default'] = (string) get_user_meta($user_id, 'billing_first_name', true) ?: $user->first_name;
		$fields['billing']['billing_first_name']['label'] = __('Nome', 'imania-store');
	}
	if (isset($fields['billing']['billing_last_name'])) {
		$fields['billing']['billing_last_name']['default'] = (string) get_user_meta($user_id, 'billing_last_name', true) ?: $user->last_name;
		$fields['billing']['billing_last_name']['label'] = __('Sobrenome', 'imania-store');
	}
	if (isset($fields['billing']['billing_email'])) {
		$fields['billing']['billing_email']['default'] = (string) get_user_meta($user_id, 'billing_email', true) ?: $user->user_email;
		$fields['billing']['billing_email']['label'] = __('E-mail', 'imania-store');
	}
	if (isset($fields['billing']['billing_phone'])) {
		$fields['billing']['billing_phone']['label'] = __('Telefone', 'imania-store');
	}
	if (isset($fields['billing']['billing_number'])) {
		$fields['billing']['billing_number']['label'] = __('Numero', 'imania-store');
	}
	if (isset($fields['billing']['billing_neighborhood'])) {
		$fields['billing']['billing_neighborhood']['label'] = __('Bairro', 'imania-store');
	}
	if (isset($fields['billing']['billing_address_1'])) {
		$fields['billing']['billing_address_1']['label'] = __('Endereco', 'imania-store');
	}
	if (isset($fields['billing']['billing_address_2'])) {
		$fields['billing']['billing_address_2']['label'] = __('Complemento', 'imania-store');
	}
	if (isset($fields['billing']['billing_postcode'])) {
		$fields['billing']['billing_postcode']['label'] = __('CEP', 'imania-store');
	}
	if (isset($fields['billing']['billing_city'])) {
		$fields['billing']['billing_city']['label'] = __('Cidade', 'imania-store');
	}
	if (isset($fields['billing']['billing_state'])) {
		$fields['billing']['billing_state']['label'] = __('Estado', 'imania-store');
	}
	if (isset($fields['billing']['billing_country'])) {
		$fields['billing']['billing_country']['label'] = __('Pais', 'imania-store');
		$fields['billing']['billing_country']['default'] = (string) get_user_meta($user_id, 'billing_country', true) ?: 'BR';
	}

	if (isset($fields['billing']['billing_persontype'])) {
		$fields['billing']['billing_persontype']['default'] = $document_meta['persontype'];
		$fields['billing']['billing_persontype']['class'][] = 'imania-checkout-field--hidden';
		$fields['billing']['billing_persontype']['priority'] = 5;
	}

	foreach (array('billing_cpf', 'billing_cnpj') as $document_field) {
		if (!isset($fields['billing'][$document_field])) {
			continue;
		}

		$is_active = $document_field === $document_meta['field'];
		$fields['billing'][$document_field]['default'] = $is_active ? $formatted_document : '';
		$fields['billing'][$document_field]['required'] = $is_active;
		if (!isset($fields['billing'][$document_field]['custom_attributes']) || !is_array($fields['billing'][$document_field]['custom_attributes'])) {
			$fields['billing'][$document_field]['custom_attributes'] = array();
		}
		$fields['billing'][$document_field]['custom_attributes']['readonly'] = 'readonly';
		$fields['billing'][$document_field]['custom_attributes']['data-imania-document-valid'] = $is_active && $document_is_valid ? '1' : '0';
		$fields['billing'][$document_field]['class'][] = 'imania-checkout-field--document';
		if (!$is_active) {
			$fields['billing'][$document_field]['class'][] = 'imania-checkout-field--hidden';
		} elseif (!$document_is_valid) {
			$fields['billing'][$document_field]['class'][] = 'imania-checkout-field--document-warning';
		}
	}

	if ('pf' === $document_meta['type'] && isset($fields['billing']['billing_company'])) {
		$fields['billing']['billing_company']['required'] = false;
		$fields['billing']['billing_company']['class'][] = 'imania-checkout-field--hidden';
	}
	if (isset($fields['billing']['billing_company'])) {
		$fields['billing']['billing_company']['required'] = false;
		$fields['billing']['billing_company']['class'][] = 'imania-checkout-field--hidden';
		$fields['billing']['billing_company']['priority'] = 45;
	}

	return $fields;
}
add_filter('woocommerce_checkout_fields', 'imania_store_prepare_checkout_fields', 30);

/**
 * Render a profile link beside an absent or invalid checkout document.
 *
 * @param string $field Field HTML.
 * @param string $key   Field key.
 *
 * @return string
 */
function imania_store_render_checkout_document_notice($field, $key)
{
	if (
		!is_user_logged_in()
		|| !function_exists('is_checkout')
		|| !is_checkout()
		|| !in_array($key, array('billing_cpf', 'billing_cnpj'), true)
	) {
		return $field;
	}

	$document_meta = imania_store_get_checkout_document_meta(get_current_user_id());
	if ($key !== $document_meta['field'] || imania_store_is_checkout_document_valid($document_meta)) {
		return $field;
	}

	$label = 'pj' === $document_meta['type'] ? __('CNPJ', 'imania-store') : __('CPF', 'imania-store');
	$profile_url = function_exists('imania_store_get_conta_url') ? imania_store_get_conta_url() : home_url('/conta/');
	$notice = sprintf(
		'<span class="imania-checkout__document-notice" role="alert">%1$s <a href="%2$s">%3$s</a></span>',
		esc_html(sprintf(__('%s ausente ou invalido no seu cadastro.', 'imania-store'), $label)),
		esc_url($profile_url),
		esc_html__('Atualizar em Minha Conta', 'imania-store')
	);

	$position = strrpos($field, '</p>');
	if (false === $position) {
		return $field . $notice;
	}

	return substr_replace($field, $notice, $position, 0);
}
add_filter('woocommerce_form_field', 'imania_store_render_checkout_document_notice', 20, 2);

/**
 * Force identity fields from the authenticated user before WooCommerce validates/saves checkout.
 *
 * @param array $data Posted checkout data.
 *
 * @return array
 */
function imania_store_force_checkout_identity_data($data)
{
	if (!is_user_logged_in()) {
		return $data;
	}

	$document_meta = imania_store_get_checkout_document_meta(get_current_user_id());
	$data['billing_persontype'] = $document_meta['persontype'];
	$document = imania_store_normalize_checkout_document($document_meta['document']);

	if ('pj' === $document_meta['type']) {
		$data['billing_cnpj'] = $document;
		$data['billing_cpf'] = '';
	} else {
		$data['billing_cpf'] = $document;
		$data['billing_cnpj'] = '';
	}

	return $data;
}
add_filter('woocommerce_checkout_posted_data', 'imania_store_force_checkout_identity_data', 20);

/**
 * Backend validation for authenticated checkout identity.
 *
 * @param array    $data   Posted data.
 * @param WP_Error $errors Validation errors.
 */
function imania_store_validate_checkout_identity_data($data, $errors)
{
	if (!is_user_logged_in()) {
		$errors->add('imania_checkout_login_required', __('Faca login para finalizar a compra.', 'imania-store'));
		return;
	}

	$document_meta = imania_store_get_checkout_document_meta(get_current_user_id());
	if (!function_exists('imania_store_validate_customer_document')) {
		return;
	}

	$document = imania_store_normalize_checkout_document($document_meta['document']);
	$posted_type = isset($data['billing_persontype']) ? (string) $data['billing_persontype'] : '';
	$posted_document_key = 'pj' === $document_meta['type'] ? 'billing_cnpj' : 'billing_cpf';
	$posted_document = isset($data[$posted_document_key]) ? imania_store_normalize_checkout_document((string) $data[$posted_document_key]) : '';

	if ($posted_type !== $document_meta['persontype'] || $posted_document !== $document) {
		$errors->add('imania_checkout_document_mismatch', __('Documento invalido para esta conta.', 'imania-store'));
		return;
	}

	$validation = imania_store_validate_customer_document($document_meta['type'], $document);
	if (empty($validation['valid'])) {
		$errors->add(
			'imania_checkout_invalid_document',
			'pj' === $document_meta['type'] ? __('CNPJ invalido para esta conta.', 'imania-store') : __('CPF invalido para esta conta.', 'imania-store')
		);
	}
}
add_action('woocommerce_after_checkout_validation', 'imania_store_validate_checkout_identity_data', 20, 2);
