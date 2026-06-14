<?php

/**
 * Handle wishlist AJAX toggle.
 */
function imania_store_handle_wishlist_ajax()
{
	if ('POST' !== strtoupper((string) $_SERVER['REQUEST_METHOD'])) {
		imania_store_send_account_json_error(__('Metodo invalido.', 'imania-store'), 405, 'invalid_method');
	}

	$is_valid_nonce = check_ajax_referer('imania_wishlist_nonce', 'nonce', false);
	if (false === $is_valid_nonce) {
		imania_store_send_account_json_error(__('Falha de seguranca. Atualize a pagina e tente novamente.', 'imania-store'), 403, 'invalid_nonce');
	}

	if (!is_user_logged_in()) {
		imania_store_send_account_json_error(__('FaÃ§a login para adicionar Ã  wishlist.', 'imania-store'), 401, 'not_authenticated');
	}

	$product_id = isset($_POST['product_id']) ? absint(wp_unslash($_POST['product_id'])) : 0;
	$mode = isset($_POST['mode']) ? sanitize_key(wp_unslash($_POST['mode'])) : 'toggle';
	if ($product_id <= 0 || !in_array($mode, array('toggle', 'add', 'remove'), true)) {
		imania_store_send_account_json_error(__('Produto invÃ¡lido.', 'imania-store'), 400, 'invalid_product');
	}

	$product = wc_get_product($product_id);
	if (!$product instanceof WC_Product) {
		imania_store_send_account_json_error(__('Produto nÃ£o encontrado.', 'imania-store'), 404, 'product_not_found');
	}

	$user_id = get_current_user_id();
	if (!current_user_can('read', $user_id)) {
		imania_store_send_account_json_error(__('VocÃª nao tem permissao para esta acao.', 'imania-store'), 403, 'forbidden');
	}

	$is_favorited = imania_store_update_wishlist_item($user_id, $product_id, $mode);
	$count = count(imania_store_get_wishlist_ids($user_id));

	imania_store_send_account_json_success(
		array(
			'productId' => $product_id,
			'isFavorited' => $is_favorited,
			'count' => $count,
			'wishlistUrl' => function_exists('wc_get_account_endpoint_url') ? wc_get_account_endpoint_url('wishlist') : home_url('/'),
		)
	);
}
add_action('wp_ajax_imania_toggle_wishlist', 'imania_store_handle_wishlist_ajax');
