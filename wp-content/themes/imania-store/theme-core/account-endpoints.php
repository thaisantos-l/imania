<?php

/**
 * Register wishlist endpoint on My Account.
 */
function imania_store_register_wishlist_endpoint()
{
	add_rewrite_endpoint('wishlist', EP_ROOT | EP_PAGES);
	add_rewrite_endpoint('profile', EP_ROOT | EP_PAGES);
}
add_action('init', 'imania_store_register_wishlist_endpoint');

/**
 * Flush rewrite rules on theme switch for custom endpoint.
 */
function imania_store_flush_rewrite_on_switch()
{
	imania_store_register_wishlist_endpoint();
	flush_rewrite_rules(false);
}
add_action('after_switch_theme', 'imania_store_flush_rewrite_on_switch');

/**
 * Flush endpoint rewrite once for active theme.
 */
function imania_store_maybe_flush_wishlist_endpoint()
{
	if ('1' === get_option('imania_account_endpoints_flushed')) {
		return;
	}

	imania_store_register_wishlist_endpoint();
	flush_rewrite_rules(false);
	update_option('imania_account_endpoints_flushed', '1', true);
}
add_action('init', 'imania_store_maybe_flush_wishlist_endpoint', 20);

/**
 * Add wishlist item to My Account menu.
 *
 * @param array<string,string> $items Menu items.
 *
 * @return array<string,string>
 */
function imania_store_add_wishlist_to_account_menu($items)
{
	return array(
		'profile' => __('InformaÃ§Ãµes do perfil', 'imania-store'),
		'orders' => __('Compras', 'imania-store'),
		'wishlist' => __('Wishlist', 'imania-store'),
		'payment-methods' => __('Pagamento', 'imania-store'),
	);
}
add_filter('woocommerce_account_menu_items', 'imania_store_add_wishlist_to_account_menu');

/**
 * Profile endpoint page title.
 *
 * @param string $title Default title.
 *
 * @return string
 */
function imania_store_profile_endpoint_title($title)
{
	return __('InformaÃ§Ãµes do perfil', 'imania-store');
}
add_filter('woocommerce_endpoint_profile_title', 'imania_store_profile_endpoint_title');

/**
 * Wishlist endpoint page title.
 *
 * @param string $title Default title.
 *
 * @return string
 */
function imania_store_wishlist_endpoint_title($title)
{
	return __('Minha wishlist', 'imania-store');
}
add_filter('woocommerce_endpoint_wishlist_title', 'imania_store_wishlist_endpoint_title');

/**
 * Render wishlist endpoint content.
 */
function imania_store_render_wishlist_endpoint_content()
{
	$user_id = get_current_user_id();
	if ($user_id <= 0) {
		echo '<p>' . esc_html__('FaÃ§a login para acessar sua wishlist.', 'imania-store') . '</p>';
		return;
	}

	$products = imania_store_get_wishlist_products($user_id);

	if (empty($products)) {
		echo '<p>' . esc_html__('Sua wishlist estÃ¡ vazia.', 'imania-store') . '</p>';
		return;
	}
	?>
	<div class="imania-wishlist-account" data-imania-wishlist-endpoint data-empty-text="<?php echo esc_attr__('Sua wishlist está vazia.', 'imania-store'); ?>">
		<div class="row g-3" data-imania-wishlist-grid>
			<?php foreach ($products as $product) : ?>
				<div class="col-12 col-md-6" data-wishlist-account-col>
					<div class="imania-wishlist-account__item" data-wishlist-account-item="<?php echo esc_attr($product->get_id()); ?>">
						<a class="imania-wishlist-account__thumb" href="<?php echo esc_url(get_permalink($product->get_id())); ?>">
							<?php echo wp_kses_post($product->get_image('woocommerce_thumbnail', array('loading' => 'lazy'))); ?>
						</a>
						<div class="imania-wishlist-account__content">
							<h3><a href="<?php echo esc_url(get_permalink($product->get_id())); ?>"><?php echo esc_html($product->get_name()); ?></a></h3>
							<div class="imania-price"><?php echo wp_kses_post($product->get_price_html()); ?></div>
							<div class="imania-wishlist-account__actions">
								<a class="imania-btn imania-btn--primary imania-btn--sm" href="<?php echo esc_url(get_permalink($product->get_id())); ?>"><?php esc_html_e('Ver produto', 'imania-store'); ?></a>
								<button type="button" class="imania-btn imania-btn--outline imania-btn--sm imania-wishlist-remove" data-imania-wishlist-toggle data-imania-wishlist-mode="remove" data-imania-remove-row data-product-id="<?php echo esc_attr($product->get_id()); ?>">
									<?php esc_html_e('Remover', 'imania-store'); ?>
								</button>
							</div>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
	<?php
}
add_action('woocommerce_account_wishlist_endpoint', 'imania_store_render_wishlist_endpoint_content');

/**
 * Resolve customer document label.
 *
 * @param int $user_id User id.
 *
 * @return string
 */
function imania_store_get_customer_document_label($user_id)
{
	$type = (string) get_user_meta($user_id, 'imania_customer_type', true);
	if ('pj' === $type || '2' === (string) get_user_meta($user_id, 'billing_persontype', true)) {
		return 'CNPJ';
	}

	return 'CPF';
}

/**
 * Resolve customer document value.
 *
 * @param int $user_id User id.
 *
 * @return string
 */
function imania_store_get_customer_document_value($user_id)
{
	$label = imania_store_get_customer_document_label($user_id);
	if ('CNPJ' === $label) {
		return (string) get_user_meta($user_id, 'billing_cnpj', true);
	}

	return (string) get_user_meta($user_id, 'billing_cpf', true);
}

/**
 * Render profile endpoint content.
 */
function imania_store_render_profile_endpoint_content()
{
	$user_id = get_current_user_id();
	if ($user_id <= 0) {
		echo '<p>' . esc_html__('FaÃ§a login para acessar seu perfil.', 'imania-store') . '</p>';
		return;
	}

	$user = get_userdata($user_id);
	if (!$user instanceof WP_User) {
		echo '<p>' . esc_html__('NÃ£o foi possÃ­vel carregar os dados do perfil.', 'imania-store') . '</p>';
		return;
	}

	$document_label = imania_store_get_customer_document_label($user_id);
	$document_value = imania_store_get_customer_document_value($user_id);
	$customer_type = imania_store_resolve_customer_type($user_id);
	$is_administrator = in_array('administrator', (array) $user->roles, true);
	$can_define_customer_type = $is_administrator && !in_array($customer_type, array('pf', 'pj'), true);
	if ($can_define_customer_type) {
		$document_label = 'CPF/CNPJ';
	}

	$billing_country = (string) get_user_meta($user_id, 'billing_country', true);
	$countries = function_exists('WC') ? WC()->countries : null;
	$country_name = $billing_country;
	if ($countries instanceof WC_Countries && isset($countries->countries[$billing_country])) {
		$country_name = (string) $countries->countries[$billing_country];
	}

	$template_args = array(
		'user' => $user,
		'document_label' => $document_label,
		'document_value' => $document_value,
		'can_define_customer_type' => $can_define_customer_type,
		'phone' => (string) get_user_meta($user_id, 'billing_phone', true),
		'address_1' => (string) get_user_meta($user_id, 'billing_address_1', true),
		'address_2' => (string) get_user_meta($user_id, 'billing_address_2', true),
		'number' => (string) get_user_meta($user_id, 'billing_number', true),
		'neighborhood' => (string) get_user_meta($user_id, 'billing_neighborhood', true),
		'postcode' => (string) get_user_meta($user_id, 'billing_postcode', true),
		'city' => (string) get_user_meta($user_id, 'billing_city', true),
		'state' => (string) get_user_meta($user_id, 'billing_state', true),
		'country' => $country_name,
	);

	wc_get_template('myaccount/profile.php', $template_args, '', get_template_directory() . '/woocommerce/');
}
add_action('woocommerce_account_profile_endpoint', 'imania_store_render_profile_endpoint_content');

/**
 * Render account endpoint content to string.
 *
 * @param string $endpoint Endpoint slug.
 *
 * @return string
 */
function imania_store_render_account_endpoint_to_string($endpoint)
{
	$endpoint = imania_store_sanitize_account_endpoint($endpoint);
	$user_id = get_current_user_id();
	$page = 1;
	if ('orders' === $endpoint) {
		$page = max(1, absint(get_query_var('orders')));
	}

	$cacheable_endpoints = array('orders', 'wishlist');
	if ($user_id > 0 && in_array($endpoint, $cacheable_endpoints, true)) {
		$cache_key = imania_store_get_account_endpoint_cache_key($user_id, $endpoint, $page);
		$cached = get_transient($cache_key);
		if (is_string($cached) && '' !== $cached) {
			return $cached;
		}
	}

	ob_start();

	switch ($endpoint) {
		case 'profile':
			do_action('woocommerce_account_profile_endpoint');
			break;
		case 'orders':
			do_action('woocommerce_account_orders_endpoint', 1);
			break;
		case 'wishlist':
			do_action('woocommerce_account_wishlist_endpoint');
			break;
		case 'payment-methods':
			do_action('woocommerce_account_payment-methods_endpoint');
			break;
		default:
			do_action('woocommerce_account_profile_endpoint');
			$endpoint = 'profile';
			break;
	}

	$html = (string) ob_get_clean();
	if (isset($cache_key) && '' !== $html) {
		set_transient($cache_key, $html, MINUTE_IN_SECONDS);
	}

	return $html;
}

/**
 * Handle account endpoint AJAX load.
 */
function imania_store_handle_account_endpoint_ajax()
{
	if ('POST' !== strtoupper((string) $_SERVER['REQUEST_METHOD'])) {
		imania_store_send_account_json_error(__('Metodo invalido.', 'imania-store'), 405, 'invalid_method');
	}

	$is_valid_nonce = check_ajax_referer('imania_account_nonce', 'nonce', false);
	if (false === $is_valid_nonce) {
		imania_store_send_account_json_error(__('Falha de seguranca. Atualize a pagina e tente novamente.', 'imania-store'), 403, 'invalid_nonce');
	}

	if (!is_user_logged_in()) {
		imania_store_send_account_json_error(__('FaÃ§a login para acessar esta Ã¡rea.', 'imania-store'), 401, 'not_authenticated');
	}

	$user_id = get_current_user_id();
	if (!current_user_can('read', $user_id)) {
		imania_store_send_account_json_error(__('VocÃª nao tem permissao para acessar esta Ã¡rea.', 'imania-store'), 403, 'forbidden');
	}

	$endpoint = isset($_POST['endpoint']) ? sanitize_key(wp_unslash($_POST['endpoint'])) : 'profile';
	$endpoint = imania_store_sanitize_account_endpoint($endpoint);
	$page = isset($_POST['page']) ? absint(wp_unslash($_POST['page'])) : 1;
	$page = max(1, $page);

	if ('orders' === $endpoint) {
		set_query_var('orders', $page);
	}

	$html = imania_store_render_account_endpoint_to_string($endpoint);
	imania_store_send_account_json_success(
		array(
			'endpoint' => $endpoint,
			'page' => $page,
			'html' => $html,
		)
	);
}
add_action('wp_ajax_imania_account_endpoint', 'imania_store_handle_account_endpoint_ajax');

/**
 * Handle account profile save via AJAX.
 */
function imania_store_handle_account_profile_save_ajax()
{
	if ('POST' !== strtoupper((string) $_SERVER['REQUEST_METHOD'])) {
		imania_store_send_account_json_error(__('Metodo invalido.', 'imania-store'), 405, 'invalid_method');
	}

	$is_valid_nonce = check_ajax_referer('imania_account_profile_nonce', 'nonce', false);
	if (false === $is_valid_nonce) {
		imania_store_send_account_json_error(__('Falha de seguranca. Atualize a pagina e tente novamente.', 'imania-store'), 403, 'invalid_nonce');
	}

	$user_id = get_current_user_id();
	if ($user_id <= 0) {
		imania_store_send_account_json_error(__('FaÃ§a login para editar seu perfil.', 'imania-store'), 401, 'not_authenticated');
	}

	$first_name = isset($_POST['account_first_name']) ? sanitize_text_field(wp_unslash($_POST['account_first_name'])) : '';
	$last_name = isset($_POST['account_last_name']) ? sanitize_text_field(wp_unslash($_POST['account_last_name'])) : '';
	$email = isset($_POST['account_email']) ? sanitize_email(wp_unslash($_POST['account_email'])) : '';
	$password = isset($_POST['password_1']) ? (string) wp_unslash($_POST['password_1']) : '';
	$phone = isset($_POST['billing_phone']) ? sanitize_text_field(wp_unslash($_POST['billing_phone'])) : '';
	$address_1 = isset($_POST['billing_address_1']) ? sanitize_text_field(wp_unslash($_POST['billing_address_1'])) : '';
	$address_2 = isset($_POST['billing_address_2']) ? sanitize_text_field(wp_unslash($_POST['billing_address_2'])) : '';
	$number = isset($_POST['billing_number']) ? sanitize_text_field(wp_unslash($_POST['billing_number'])) : '';
	$neighborhood = isset($_POST['billing_neighborhood']) ? sanitize_text_field(wp_unslash($_POST['billing_neighborhood'])) : '';
	$postcode = isset($_POST['billing_postcode']) ? sanitize_text_field(wp_unslash($_POST['billing_postcode'])) : '';
	$city = isset($_POST['billing_city']) ? sanitize_text_field(wp_unslash($_POST['billing_city'])) : '';
	$state = isset($_POST['billing_state']) ? sanitize_text_field(wp_unslash($_POST['billing_state'])) : '';
	$document = isset($_POST['imania_document']) ? sanitize_text_field(wp_unslash($_POST['imania_document'])) : '';
	$posted_customer_type = isset($_POST['imania_customer_type']) ? sanitize_key(wp_unslash($_POST['imania_customer_type'])) : '';

	if ('' === $email || !is_email($email)) {
		imania_store_send_account_json_error(__('Informe um e-mail valido.', 'imania-store'), 422, 'invalid_email');
	}

	$email_owner_id = email_exists($email);
	if ($email_owner_id && (int) $email_owner_id !== $user_id) {
		imania_store_send_account_json_error(__('Este e-mail ja esta em uso por outra conta.', 'imania-store'), 422, 'email_in_use');
	}

	$customer_type = imania_store_resolve_customer_type($user_id);
	if (!in_array($customer_type, array('pf', 'pj'), true)) {
		$user = get_userdata($user_id);
		$is_administrator = $user instanceof WP_User && in_array('administrator', (array) $user->roles, true);
		if ($is_administrator && in_array($posted_customer_type, array('pf', 'pj'), true)) {
			$customer_type = $posted_customer_type;
		} else {
			imania_store_send_account_json_error(__('Selecione o tipo de pessoa desta conta.', 'imania-store'), 422, 'invalid_customer_type');
		}
	}

	$document_validation = imania_store_validate_customer_document($customer_type, $document);
	if (empty($document_validation['valid'])) {
		$message = 'pf' === $customer_type ? __('CPF invalido.', 'imania-store') : __('CNPJ invalido.', 'imania-store');
		imania_store_send_account_json_error($message, 422, (string) $document_validation['error']);
	}

	$normalized_document = (string) $document_validation['normalized'];
	if (imania_store_document_exists_for_another_user($normalized_document, $user_id)) {
		imania_store_send_account_json_error(__('Este documento ja esta vinculado a outra conta.', 'imania-store'), 422, 'duplicated_document');
	}

	$updated_user = wp_update_user(
		array(
			'ID' => $user_id,
			'first_name' => $first_name,
			'last_name' => $last_name,
			'user_email' => $email,
		)
	);

	if (is_wp_error($updated_user)) {
		imania_store_send_account_json_error(__('Nao foi possivel atualizar seus dados de conta.', 'imania-store'), 500, 'user_update_failed');
	}

	if ('' !== $password) {
		if (strlen($password) < 8) {
			imania_store_send_account_json_error(__('A senha deve ter no minimo 8 caracteres.', 'imania-store'), 422, 'weak_password');
		}
		wp_set_password($password, $user_id);
		wp_set_current_user($user_id);
		wp_set_auth_cookie($user_id, true);
	}

	update_user_meta($user_id, 'billing_phone', $phone);
	update_user_meta($user_id, 'billing_address_1', $address_1);
	update_user_meta($user_id, 'billing_address_2', $address_2);
	update_user_meta($user_id, 'billing_number', $number);
	update_user_meta($user_id, 'billing_neighborhood', $neighborhood);
	update_user_meta($user_id, 'billing_postcode', $postcode);
	update_user_meta($user_id, 'billing_city', $city);
	update_user_meta($user_id, 'billing_state', strtoupper($state));
	update_user_meta($user_id, 'imania_document', $normalized_document);
	update_user_meta($user_id, 'imania_document_type', 'pf' === $customer_type ? 'cpf' : 'cnpj');
	update_user_meta($user_id, 'imania_customer_type', $customer_type);

	if ('pf' === $customer_type) {
		update_user_meta($user_id, 'billing_persontype', '1');
		update_user_meta($user_id, 'billing_cpf', $normalized_document);
		delete_user_meta($user_id, 'billing_cnpj');
	} else {
		update_user_meta($user_id, 'billing_persontype', '2');
		update_user_meta($user_id, 'billing_cnpj', $normalized_document);
		delete_user_meta($user_id, 'billing_cpf');
	}
	imania_store_assign_customer_typed_role($user_id, $customer_type);

	imania_store_invalidate_account_cache($user_id);
	if (function_exists('WC') && WC()->cart instanceof WC_Cart) {
		WC()->cart->calculate_totals();
		WC()->cart->set_session();
	}

	imania_store_send_account_json_success(
		array(
			'message' => __('Perfil atualizado com sucesso.', 'imania-store'),
			'user' => array(
				'firstName' => $first_name,
				'lastName' => $last_name,
				'email' => $email,
			),
		)
	);
}
add_action('wp_ajax_imania_account_profile_save', 'imania_store_handle_account_profile_save_ajax');
