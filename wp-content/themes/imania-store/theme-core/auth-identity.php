<?php

/**
 * Resolve user by normalized document.
 *
 * @param string $normalized_document Digits only document.
 * @param string $customer_type       Optional pf|pj.
 *
 * @return WP_User|null
 */
function imania_store_get_user_by_document($normalized_document, $customer_type = '')
{
	static $request_cache = array();

	$normalized_document = preg_replace('/\D+/', '', (string) $normalized_document);
	$normalized_document = is_string($normalized_document) ? $normalized_document : '';
	$customer_type = sanitize_key((string) $customer_type);
	$cache_key = $normalized_document . ':' . $customer_type;

	if (isset($request_cache[$cache_key])) {
		return $request_cache[$cache_key] instanceof WP_User ? $request_cache[$cache_key] : null;
	}

	if ('' === $normalized_document) {
		$request_cache[$cache_key] = null;
		return null;
	}

	$keys = array('imania_document');
	if ('pf' === $customer_type) {
		$keys[] = 'billing_cpf';
	} elseif ('pj' === $customer_type) {
		$keys[] = 'billing_cnpj';
	} else {
		$keys[] = 'billing_cpf';
		$keys[] = 'billing_cnpj';
	}

	$meta_query = array('relation' => 'OR');
	foreach (array_unique($keys) as $key) {
		$meta_query[] = array(
			'key' => $key,
			'value' => $normalized_document,
		);
	}

	$query = new WP_User_Query(
		array(
			'fields' => 'all',
			'number' => 1,
			'count_total' => false,
			'meta_query' => $meta_query,
		)
	);

	$results = $query->get_results();
	$user = (!empty($results) && $results[0] instanceof WP_User) ? $results[0] : null;
	$request_cache[$cache_key] = $user;

	return $user;
}

/**
 * Resolve account type from user.
 *
 * @param WP_User $user User object.
 *
 * @return string|null
 */
function imania_store_resolve_customer_type_from_user(WP_User $user)
{
	$type = imania_store_resolve_customer_type($user->ID);
	if ('pf' === $type || 'pj' === $type) {
		return $type;
	}

	if (in_array('customer_pj', (array) $user->roles, true)) {
		return 'pj';
	}
	if (in_array('customer_pf', (array) $user->roles, true)) {
		return 'pf';
	}

	return null;
}

/**
 * Build unique username for new users.
 *
 * @param string $email User email.
 *
 * @return string
 */
function imania_store_generate_username_from_email($email)
{
	$email = sanitize_email((string) $email);
	$parts = explode('@', $email);
	$base = sanitize_user((string) $parts[0], true);
	$base = '' !== $base ? $base : 'cliente';

	$max_length = 60;
	$username = substr($base, 0, $max_length);
	$suffix = 1;

	while (username_exists($username)) {
		$suffix_str = (string) $suffix;
		$allowed = max(1, $max_length - strlen($suffix_str));
		$username = substr($base, 0, $allowed) . $suffix_str;
		$suffix++;
		if ($suffix > 9999) {
			$username = 'cliente' . wp_generate_password(8, false, false);
			break;
		}
	}

	return $username;
}

/**
 * Persist document/type metadata for user.
 *
 * @param int    $user_id              User id.
 * @param string $customer_type        pf|pj.
 * @param string $normalized_document  Document digits only.
 * @param string $email                Optional billing email.
 */
function imania_store_set_customer_identity_meta($user_id, $customer_type, $normalized_document, $email = '')
{
	$user_id = absint($user_id);
	$customer_type = sanitize_key((string) $customer_type);
	$normalized_document = preg_replace('/\D+/', '', (string) $normalized_document);
	$normalized_document = is_string($normalized_document) ? $normalized_document : '';
	$email = sanitize_email((string) $email);

	if ($user_id <= 0 || '' === $normalized_document || !in_array($customer_type, array('pf', 'pj'), true)) {
		return;
	}

	update_user_meta($user_id, 'imania_document', $normalized_document);
	update_user_meta($user_id, 'imania_document_type', 'pf' === $customer_type ? 'cpf' : 'cnpj');
	update_user_meta($user_id, 'imania_customer_type', $customer_type);
	update_user_meta($user_id, 'billing_persontype', 'pf' === $customer_type ? '1' : '2');

	if ('' !== $email && is_email($email)) {
		update_user_meta($user_id, 'billing_email', $email);
	}

	if ('pf' === $customer_type) {
		update_user_meta($user_id, 'billing_cpf', $normalized_document);
		delete_user_meta($user_id, 'billing_cnpj');
	} else {
		update_user_meta($user_id, 'billing_cnpj', $normalized_document);
		delete_user_meta($user_id, 'billing_cpf');
	}

	imania_store_assign_customer_typed_role($user_id, $customer_type);
}
