<?php

/**
 * Handle single product add-to-cart via AJAX.
 */
function imania_store_handle_single_add_to_cart_ajax()
{
	if ('POST' !== strtoupper((string) $_SERVER['REQUEST_METHOD'])) {
		imania_store_send_account_json_error(__('Metodo invalido.', 'imania-store'), 405, 'invalid_method');
	}

	$is_valid_nonce = check_ajax_referer('imania_single_product_nonce', 'nonce', false);
	if (false === $is_valid_nonce) {
		imania_store_send_account_json_error(__('Falha de seguranca. Atualize a pagina e tente novamente.', 'imania-store'), 403, 'invalid_nonce');
	}

	if (!is_user_logged_in()) {
		imania_store_send_account_json_error(__('Faca login para comprar.', 'imania-store'), 401, 'not_authenticated');
	}

	if (!function_exists('WC') || !WC()->cart instanceof WC_Cart) {
		imania_store_send_account_json_error(__('Carrinho indisponivel no momento.', 'imania-store'), 500, 'cart_unavailable');
	}

	$product_id = isset($_POST['product_id']) ? absint(wp_unslash($_POST['product_id'])) : 0;
	$quantity = isset($_POST['quantity']) ? wc_stock_amount(wp_unslash($_POST['quantity'])) : 1;
	$variation_id = isset($_POST['variation_id']) ? absint(wp_unslash($_POST['variation_id'])) : 0;

	$product = wc_get_product($variation_id > 0 ? $variation_id : $product_id);
	if (!$product instanceof WC_Product) {
		imania_store_send_account_json_error(__('Produto invalido.', 'imania-store'), 404, 'invalid_product');
	}

	$quantity = max(1, (int) $quantity);
	$variation = array();
	$posted_attributes = isset($_POST['variation']) && is_array($_POST['variation']) ? (array) wp_unslash($_POST['variation']) : array();
	foreach ($posted_attributes as $key => $value) {
		$attribute_key = wc_clean((string) $key);
		if (0 === strpos($attribute_key, 'attribute_')) {
			$variation[$attribute_key] = wc_clean((string) $value);
		}
	}

	if (!empty($_POST) && empty($variation)) {
		foreach ($_POST as $key => $value) {
			$attribute_key = wc_clean((string) $key);
			if (0 !== strpos($attribute_key, 'attribute_')) {
				continue;
			}
			$variation[$attribute_key] = wc_clean((string) wp_unslash($value));
		}
	}

	wc_clear_notices();
	$passed = apply_filters('woocommerce_add_to_cart_validation', true, $product_id, $quantity, $variation_id, $variation);
	if (!$passed) {
		$message = imania_store_get_first_wc_notice_message('error');
		wc_clear_notices();
		imania_store_send_account_json_error(
			'' !== $message ? $message : __('Nao foi possivel adicionar o produto.', 'imania-store'),
			422,
			'add_to_cart_validation_failed'
		);
	}

	$added_key = WC()->cart->add_to_cart($product_id, $quantity, $variation_id, $variation);
	if (!$added_key) {
		$message = imania_store_get_first_wc_notice_message('error');
		wc_clear_notices();
		imania_store_send_account_json_error(
			'' !== $message ? $message : __('Nao foi possivel adicionar o produto ao carrinho.', 'imania-store'),
			422,
			'add_to_cart_failed'
		);
	}

	WC()->cart->calculate_totals();
	wc_clear_notices();

	imania_store_send_account_json_success(
		array(
			'message' => __('Produto adicionado ao carrinho.', 'imania-store'),
			'productId' => $product_id,
			'variationId' => $variation_id,
			'count' => WC()->cart->get_cart_contents_count(),
			'cartUrl' => function_exists('wc_get_cart_url') ? wc_get_cart_url() : home_url('/'),
		)
	);
}
add_action('wp_ajax_imania_single_add_to_cart', 'imania_store_handle_single_add_to_cart_ajax');
add_action('wp_ajax_nopriv_imania_single_add_to_cart', 'imania_store_handle_single_add_to_cart_ajax');
