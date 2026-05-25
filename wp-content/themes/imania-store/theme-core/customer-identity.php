<?php

/**
 * Ensure PF/PJ customer roles exist with same capabilities as Woo customer.
 */
function imania_store_ensure_customer_pf_pj_roles()
{
	$customer_role = get_role('customer');
	if (!$customer_role instanceof WP_Role) {
		return;
	}

	$caps = is_array($customer_role->capabilities) ? $customer_role->capabilities : array('read' => true);
	$target_roles = array(
		'customer_pf' => __('Cliente PF', 'imania-store'),
		'customer_pj' => __('Cliente PJ', 'imania-store'),
	);

	foreach ($target_roles as $slug => $label) {
		$role = get_role($slug);
		if (!$role instanceof WP_Role) {
			add_role($slug, $label, $caps);
			continue;
		}

		foreach ($caps as $cap => $grant) {
			$role->add_cap($cap, (bool) $grant);
		}
	}
}
add_action('init', 'imania_store_ensure_customer_pf_pj_roles', 5);

/**
 * Resolve customer role from customer type.
 *
 * @param string $customer_type pf|pj.
 *
 * @return string
 */
function imania_store_get_customer_role_from_type($customer_type)
{
	return 'pj' === sanitize_key((string) $customer_type) ? 'customer_pj' : 'customer_pf';
}

/**
 * Assign PF/PJ role to a user.
 *
 * @param int    $user_id User id.
 * @param string $customer_type pf|pj.
 */
function imania_store_assign_customer_typed_role($user_id, $customer_type)
{
	$user_id = absint($user_id);
	if ($user_id <= 0) {
		return;
	}

	$role = imania_store_get_customer_role_from_type($customer_type);
	$user = get_userdata($user_id);
	if (!$user instanceof WP_User) {
		return;
	}

	$user->set_role($role);
}

/**
 * Resolve account cache version for user.
 *
 * @param int $user_id User id.
 *
 * @return int
 */
function imania_store_get_account_cache_version($user_id)
{
	$user_id = absint($user_id);
	if ($user_id <= 0) {
		return 1;
	}

	$version = (int) get_user_meta($user_id, 'imania_account_cache_version', true);
	return $version > 0 ? $version : 1;
}

/**
 * Invalidate account endpoint cache for user.
 *
 * @param int $user_id User id.
 */
function imania_store_invalidate_account_cache($user_id)
{
	$user_id = absint($user_id);
	if ($user_id <= 0) {
		return;
	}

	$next_version = imania_store_get_account_cache_version($user_id) + 1;
	update_user_meta($user_id, 'imania_account_cache_version', $next_version);
}

/**
 * Build account endpoint cache key.
 *
 * @param int    $user_id  User id.
 * @param string $endpoint Endpoint.
 * @param int    $page     Page.
 *
 * @return string
 */
function imania_store_get_account_endpoint_cache_key($user_id, $endpoint, $page = 1)
{
	$user_id = absint($user_id);
	$page = max(1, absint($page));
	$endpoint = imania_store_sanitize_account_endpoint($endpoint);
	$version = imania_store_get_account_cache_version($user_id);
	return sprintf('imania_account_html_%d_%s_%d_%d', $user_id, $endpoint, $page, $version);
}

/**
 * Get request-level cached wishlist products.
 *
 * @param int $user_id User id.
 *
 * @return WC_Product[]
 */
function imania_store_get_wishlist_products($user_id)
{
	static $request_cache = array();

	$user_id = absint($user_id);
	if ($user_id <= 0) {
		return array();
	}

	if (isset($request_cache[$user_id])) {
		return $request_cache[$user_id];
	}

	$ids = imania_store_get_wishlist_ids($user_id);
	if (empty($ids)) {
		$request_cache[$user_id] = array();
		return $request_cache[$user_id];
	}

	$products = wc_get_products(
		array(
			'include' => $ids,
			'limit' => -1,
			'orderby' => 'include',
			'status' => 'publish',
			'return' => 'objects',
		)
	);

	$request_cache[$user_id] = is_array($products) ? $products : array();
	return $request_cache[$user_id];
}

/**
 * Resolve customer type from plugin rules when available.
 *
 * @param int $user_id User id.
 *
 * @return string|null
 */
function imania_store_resolve_customer_type($user_id)
{
	$user_id = absint($user_id);
	if ($user_id <= 0) {
		return null;
	}

	if (class_exists('\Imania\PricingEngine\Domain\Customer\CustomerTypeResolver')) {
		$resolver = new \Imania\PricingEngine\Domain\Customer\CustomerTypeResolver();
		$type = $resolver->resolve($user_id);
		return is_string($type) && '' !== $type ? $type : null;
	}

	$type = (string) get_user_meta($user_id, 'imania_customer_type', true);
	if ('pf' === $type || 'pj' === $type) {
		return $type;
	}

	$billing_type = (string) get_user_meta($user_id, 'billing_persontype', true);
	if ('1' === $billing_type) {
		return 'pf';
	}
	if ('2' === $billing_type) {
		return 'pj';
	}

	return null;
}

/**
 * Validate customer document according to type.
 *
 * @param string $customer_type pf|pj
 * @param string $document_raw Raw document.
 *
 * @return array{valid:bool,normalized:string,error:string}
 */
function imania_store_validate_customer_document($customer_type, $document_raw)
{
	$customer_type = sanitize_key((string) $customer_type);
	$normalized = preg_replace('/\D+/', '', (string) $document_raw);
	$normalized = is_string($normalized) ? $normalized : '';

	if ('pf' !== $customer_type && 'pj' !== $customer_type) {
		return array('valid' => false, 'normalized' => $normalized, 'error' => 'invalid_type');
	}

	if (class_exists('\Imania\PricingEngine\Domain\Customer\DocumentValidator')) {
		$validator = new \Imania\PricingEngine\Domain\Customer\DocumentValidator();
		if ('pf' === $customer_type) {
			$valid = $validator->is_valid_cpf($normalized);
			return array('valid' => (bool) $valid, 'normalized' => $normalized, 'error' => $valid ? '' : 'invalid_cpf');
		}

		$valid = $validator->is_valid_cnpj($normalized);
		return array('valid' => (bool) $valid, 'normalized' => $normalized, 'error' => $valid ? '' : 'invalid_cnpj');
	}

	$expected = 'pf' === $customer_type ? 11 : 14;
	$is_valid_length = strlen($normalized) === $expected;
	return array(
		'valid' => $is_valid_length,
		'normalized' => $normalized,
		'error' => $is_valid_length ? '' : ('pf' === $customer_type ? 'invalid_cpf' : 'invalid_cnpj'),
	);
}

/**
 * Check if a normalized document is already linked to another account.
 *
 * @param string $normalized_document Document with digits only.
 * @param int    $ignore_user_id      User id to ignore.
 *
 * @return bool
 */
function imania_store_document_exists_for_another_user($normalized_document, $ignore_user_id)
{
	$normalized_document = preg_replace('/\D+/', '', (string) $normalized_document);
	$normalized_document = is_string($normalized_document) ? $normalized_document : '';
	$ignore_user_id = absint($ignore_user_id);

	if ('' === $normalized_document) {
		return false;
	}

	if (class_exists('\Imania\PricingEngine\Domain\Customer\DocumentRepository')) {
		$repository = new \Imania\PricingEngine\Domain\Customer\DocumentRepository();
		return (bool) $repository->exists_for_another_user($normalized_document, $ignore_user_id);
	}

	$query = new WP_User_Query(
		array(
			'fields' => 'ID',
			'number' => 1,
			'count_total' => false,
			'exclude' => $ignore_user_id > 0 ? array($ignore_user_id) : array(),
			'meta_query' => array(
				array(
					'key' => 'imania_document',
					'value' => $normalized_document,
				),
			),
		)
	);

	return !empty($query->get_results());
}

