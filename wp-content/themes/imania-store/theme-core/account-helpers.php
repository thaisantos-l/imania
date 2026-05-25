<?php

/**
 * Add scoped body class for /conta/ custom layout behavior.
 *
 * @param string[] $classes Existing classes.
 *
 * @return string[]
 */
function imania_store_add_conta_body_class($classes)
{
	if (imania_store_is_conta_page()) {
		$classes[] = 'imania-page-conta';
	}
	if (function_exists('is_product') && is_product()) {
		$classes[] = 'imania-page-single-product';
	}

	return $classes;
}
add_filter('body_class', 'imania_store_add_conta_body_class');

/**
 * Get My Account URL.
 *
 * @return string
 */
function imania_store_get_my_account_url()
{
	return function_exists('wc_get_page_permalink') ? wc_get_page_permalink('myaccount') : home_url('/');
}

/**
 * Get auth page URL (/conta/).
 *
 * @return string
 */
function imania_store_get_conta_url()
{
	$page = get_page_by_path('conta');
	if ($page instanceof WP_Post) {
		$link = get_permalink($page);
		if (is_string($link) && '' !== $link) {
			return $link;
		}
	}

	return home_url('/conta/');
}

/**
 * Whether current request targets /conta/.
 *
 * @return bool
 */
function imania_store_is_conta_page()
{
	return function_exists('is_page') && is_page('conta');
}

/**
 * Redirect logged users from /conta/ to /minha-conta/.
 */
function imania_store_redirect_logged_user_from_conta()
{
	if (!imania_store_is_conta_page() || !is_user_logged_in()) {
		return;
	}

	wp_safe_redirect(imania_store_get_my_account_url());
	exit;
}
add_action('template_redirect', 'imania_store_redirect_logged_user_from_conta', 1);

/**
 * Allowed custom account endpoints.
 *
 * @return string[]
 */
function imania_store_get_allowed_account_endpoints()
{
	return array('profile', 'orders', 'wishlist', 'payment-methods');
}

/**
 * Return sanitized endpoint if allowed, or fallback profile.
 *
 * @param string $endpoint Endpoint slug.
 *
 * @return string
 */
function imania_store_sanitize_account_endpoint($endpoint)
{
	$endpoint = sanitize_key((string) $endpoint);
	return in_array($endpoint, imania_store_get_allowed_account_endpoints(), true) ? $endpoint : 'profile';
}

/**
 * Standardized JSON error for account ajax handlers.
 *
 * @param string $message Message.
 * @param int    $status  HTTP status code.
 * @param string $code    Error code.
 *
 * @return never
 */
function imania_store_send_account_json_error($message, $status = 400, $code = 'invalid_request')
{
	wp_send_json_error(
		array(
			'code' => sanitize_key((string) $code),
			'message' => (string) $message,
		),
		absint($status)
	);
}

/**
 * Standardized JSON success for account ajax handlers.
 *
 * @param array $data Payload.
 *
 * @return never
 */
function imania_store_send_account_json_success(array $data = array())
{
	wp_send_json_success($data);
}

/**
 * Resolve safe redirect URL for auth flows.
 *
 * @param string $fallback Fallback URL.
 *
 * @return string
 */
function imania_store_get_safe_auth_redirect_url($fallback = '')
{
	$fallback = '' !== (string) $fallback ? (string) $fallback : imania_store_get_my_account_url();
	$query_key = 'imania_redirect_to';

	if (class_exists('\Imania\PricingEngine\Support\MetaKeys')) {
		$query_key = (string) \Imania\PricingEngine\Support\MetaKeys::REDIRECT_QUERY_KEY;
	}

	$raw = isset($_REQUEST[$query_key]) ? wp_unslash($_REQUEST[$query_key]) : '';
	if (!is_string($raw) || '' === $raw) {
		return $fallback;
	}

	$decoded = base64_decode(sanitize_text_field($raw), true);
	$candidate = is_string($decoded) ? $decoded : '';
	if ('' === $candidate) {
		return $fallback;
	}

	$validated = wp_validate_redirect($candidate, $fallback);
	if ('' === $validated) {
		return $fallback;
	}

	$site_host = wp_parse_url(home_url(), PHP_URL_HOST);
	$target_host = wp_parse_url($validated, PHP_URL_HOST);
	if (!empty($site_host) && !empty($target_host) && $site_host !== $target_host) {
		return $fallback;
	}

	return $validated;
}
