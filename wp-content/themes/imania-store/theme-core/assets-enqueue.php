<?php

/**
 * Enqueue scripts and styles.
 */
function imania_store_scripts()
{
	$theme_js_path = get_template_directory() . '/assets/js/imania-theme.js';
	$account_orders_js_path = get_template_directory() . '/assets/js/account-orders.js';
	$conta_js_path = get_template_directory() . '/assets/js/conta.js';
	$single_product_js_path = get_template_directory() . '/assets/js/single-product.js';
	$cart_js_path = get_template_directory() . '/assets/js/cart.js';
	$checkout_js_path = get_template_directory() . '/assets/js/checkout.js';
	$catalog_js_path = get_template_directory() . '/assets/js/catalog.js';
	$theme_css_path = get_template_directory() . '/assets/css/main.css';
	$conta_css_path = get_template_directory() . '/assets/css/conta.css';
	$single_product_css_path = get_template_directory() . '/assets/css/single-product.css';
	$cart_css_path = get_template_directory() . '/assets/css/cart.css';
	$checkout_css_path = get_template_directory() . '/assets/css/checkout.css';
	$catalog_css_path = get_template_directory() . '/assets/css/catalog.css';
	$not_found_css_path = get_template_directory() . '/assets/css/404.css';
	$theme_js_ver = file_exists($theme_js_path) ? (string) filemtime($theme_js_path) : _S_VERSION;
	$account_orders_js_ver = file_exists($account_orders_js_path) ? (string) filemtime($account_orders_js_path) : _S_VERSION;
	$conta_js_ver = file_exists($conta_js_path) ? (string) filemtime($conta_js_path) : _S_VERSION;
	$single_product_js_ver = file_exists($single_product_js_path) ? (string) filemtime($single_product_js_path) : _S_VERSION;
	$cart_js_ver = file_exists($cart_js_path) ? (string) filemtime($cart_js_path) : _S_VERSION;
	$checkout_js_ver = file_exists($checkout_js_path) ? (string) filemtime($checkout_js_path) : _S_VERSION;
	$catalog_js_ver = file_exists($catalog_js_path) ? (string) filemtime($catalog_js_path) : _S_VERSION;
	$theme_css_ver = file_exists($theme_css_path) ? (string) filemtime($theme_css_path) : _S_VERSION;
	$conta_css_ver = file_exists($conta_css_path) ? (string) filemtime($conta_css_path) : _S_VERSION;
	$single_product_css_ver = file_exists($single_product_css_path) ? (string) filemtime($single_product_css_path) : _S_VERSION;
	$cart_css_ver = file_exists($cart_css_path) ? (string) filemtime($cart_css_path) : _S_VERSION;
	$checkout_css_ver = file_exists($checkout_css_path) ? (string) filemtime($checkout_css_path) : _S_VERSION;
	$catalog_css_ver = file_exists($catalog_css_path) ? (string) filemtime($catalog_css_path) : _S_VERSION;
	$not_found_css_ver = file_exists($not_found_css_path) ? (string) filemtime($not_found_css_path) : _S_VERSION;

	wp_enqueue_style('imania-store-fonts', 'https://fonts.googleapis.com/css2?family=Raleway:wght@400;500;600;700;800&display=swap', array(), null);
	wp_enqueue_style('imania-store-bootstrap-grid', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap-grid.min.css', array(), '5.3.3');
	wp_enqueue_style('imania-store-swiper', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css', array(), '11.2.8');
	wp_enqueue_style('imania-store-style', get_stylesheet_uri(), array(), _S_VERSION);
	wp_enqueue_style('imania-store-theme', get_template_directory_uri() . '/assets/css/main.css', array('imania-store-style', 'imania-store-bootstrap-grid', 'imania-store-swiper'), $theme_css_ver);
	wp_style_add_data('imania-store-style', 'rtl', 'replace');

	wp_enqueue_script('imania-store-navigation', get_template_directory_uri() . '/js/navigation.js', array(), _S_VERSION, true);
	wp_enqueue_script('imania-store-swiper', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js', array(), '11.2.8', true);
	wp_enqueue_script('imania-store-theme', get_template_directory_uri() . '/assets/js/imania-theme.js', array('imania-store-swiper'), $theme_js_ver, true);

	if (is_404()) {
		wp_enqueue_style('imania-store-not-found', get_template_directory_uri() . '/assets/css/404.css', array('imania-store-theme'), $not_found_css_ver);
	}

	$is_account_page = function_exists('is_account_page') && is_account_page();
	$is_logged_conta_page = imania_store_is_conta_page() && is_user_logged_in();
	if ($is_account_page || $is_logged_conta_page) {
		wp_enqueue_script('imania-account-orders', get_template_directory_uri() . '/assets/js/account-orders.js', array('imania-store-theme'), $account_orders_js_ver, true);
	}

	if (imania_store_should_render_auth_form()) {
		wp_enqueue_style('imania-store-conta', get_template_directory_uri() . '/assets/css/conta.css', array('imania-store-theme'), $conta_css_ver);
		wp_enqueue_script('imania-store-conta', get_template_directory_uri() . '/assets/js/conta.js', array(), $conta_js_ver, true);

		$redirect_token = isset($_GET['imania_redirect_to']) ? sanitize_text_field(wp_unslash($_GET['imania_redirect_to'])) : '';
		wp_localize_script(
			'imania-store-conta',
			'imaniaAuth',
			array(
				'ajaxUrl' => admin_url('admin-ajax.php'),
				'loginNonce' => wp_create_nonce('imania_account_login_nonce'),
				'registerNonce' => wp_create_nonce('imania_account_register_nonce'),
				'redirectToken' => $redirect_token,
				'myAccountUrl' => imania_store_get_my_account_url(),
				'messages' => array(
					'genericError' => __('Nao foi possivel concluir a solicitacao.', 'imania-store'),
					'loading' => __('Processando...', 'imania-store'),
					'loginSuccess' => __('Login realizado com sucesso.', 'imania-store'),
					'registerSuccess' => __('Cadastro realizado com sucesso.', 'imania-store'),
				),
			)
		);
	}

	if (function_exists('is_product') && is_product()) {
		wp_enqueue_style('imania-store-single-product', get_template_directory_uri() . '/assets/css/single-product.css', array('imania-store-theme'), $single_product_css_ver);
		wp_enqueue_script('imania-store-single-product', get_template_directory_uri() . '/assets/js/single-product.js', array('imania-store-swiper'), $single_product_js_ver, true);

		wp_localize_script(
			'imania-store-single-product',
			'imaniaSingleProduct',
			array(
				'ajaxUrl' => admin_url('admin-ajax.php'),
				'nonce' => wp_create_nonce('imania_single_product_nonce'),
				'isLoggedIn' => is_user_logged_in(),
				'loginUrl' => function_exists('imania_store_get_login_to_price_url') ? imania_store_get_login_to_price_url() : wp_login_url(),
				'messages' => array(
					'loading' => __('Processando...', 'imania-store'),
					'genericError' => __('Nao foi possivel adicionar o produto agora. Tente novamente.', 'imania-store'),
					'added' => __('Produto adicionado ao carrinho.', 'imania-store'),
				),
			)
		);
	}

	if (function_exists('is_cart') && is_cart()) {
		wp_enqueue_style('imania-store-cart', get_template_directory_uri() . '/assets/css/cart.css', array('imania-store-theme'), $cart_css_ver);
		wp_enqueue_script('imania-store-cart', get_template_directory_uri() . '/assets/js/cart.js', array(), $cart_js_ver, true);
		wp_localize_script(
			'imania-store-cart',
			'imaniaCartPage',
			array(
				'ajaxUrl' => admin_url('admin-ajax.php'),
				'nonce' => wp_create_nonce('imania_cart_page_nonce'),
				'messages' => array(
					'genericError' => __('Nao foi possivel atualizar seu carrinho agora.', 'imania-store'),
				),
			)
		);
	}

	if (function_exists('is_checkout') && is_checkout() && !(function_exists('is_wc_endpoint_url') && (is_wc_endpoint_url('order-pay') || is_wc_endpoint_url('order-received')))) {
		wp_enqueue_style('imania-store-checkout', get_template_directory_uri() . '/assets/css/checkout.css', array('imania-store-theme'), $checkout_css_ver);
		wp_enqueue_script('imania-store-checkout', get_template_directory_uri() . '/assets/js/checkout.js', array('jquery', 'wc-checkout'), $checkout_js_ver, true);
	}

	if (function_exists('imania_store_is_catalog_request') && imania_store_is_catalog_request()) {
		wp_enqueue_style('imania-store-catalog', get_template_directory_uri() . '/assets/css/catalog.css', array('imania-store-theme'), $catalog_css_ver);
		wp_enqueue_script('imania-store-catalog', get_template_directory_uri() . '/assets/js/catalog.js', array('imania-store-theme'), $catalog_js_ver, true);

		$filters = imania_store_get_catalog_filters();
		$current_term = function_exists('is_product_category') && is_product_category() ? get_queried_object() : null;
		wp_localize_script(
			'imania-store-catalog',
			'imaniaCatalog',
			array(
				'ajaxUrl' => class_exists('WC_AJAX') ? WC_AJAX::get_endpoint('imania_load_catalog') : '',
				'context' => function_exists('is_shop') && is_shop() ? 'shop' : 'category',
				'category' => $current_term instanceof WP_Term ? $current_term->slug : '',
				'filters' => $filters,
				'perPage' => imania_store_catalog_per_page(),
				'messages' => array(
					'loading' => __('Carregando produtos...', 'imania-store'),
					'loadMore' => __('Carregar mais', 'imania-store'),
					'genericError' => __('Nao foi possivel carregar mais produtos agora.', 'imania-store'),
					'end' => __('Voce chegou ao final dos produtos.', 'imania-store'),
					'summary' => __('Exibindo %1$d de %2$d produtos', 'imania-store'),
				),
			)
		);
	}

	$login_url = function_exists('imania_store_get_login_to_price_url') ? imania_store_get_login_to_price_url() : wp_login_url();
	$wishlist_url = function_exists('wc_get_account_endpoint_url') ? wc_get_account_endpoint_url('wishlist') : home_url('/');
	wp_localize_script(
		'imania-store-theme',
		'imaniaCartDrawer',
		array(
			'ajaxUrl' => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce('imania_cart_drawer_nonce'),
			'cartUrl' => function_exists('wc_get_cart_url') ? wc_get_cart_url() : home_url('/carrinho/'),
			'shopUrl' => function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/'),
			'messages' => array(
				'loading' => __('Carregando carrinho...', 'imania-store'),
				'genericError' => __('Nao foi possivel carregar seu carrinho agora.', 'imania-store'),
			),
		)
	);

	wp_localize_script(
		'imania-store-theme',
		'imaniaWishlist',
		array(
			'ajaxUrl' => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce('imania_wishlist_nonce'),
			'isLoggedIn' => is_user_logged_in(),
			'loginUrl' => $login_url,
			'wishlistUrl' => $wishlist_url,
			'messages' => array(
				'genericError' => __('NÃ£o foi possÃ­vel atualizar sua wishlist. Tente novamente.', 'imania-store'),
				'added' => __('Produto adicionado Ã  wishlist.', 'imania-store'),
				'removed' => __('Produto removido da wishlist.', 'imania-store'),
			),
		)
	);

	if ($is_account_page || $is_logged_conta_page) {
		$account_base_url = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('myaccount') : home_url('/');
		wp_localize_script(
			'imania-store-theme',
			'imaniaAccount',
			array(
				'ajaxUrl' => admin_url('admin-ajax.php'),
				'nonce' => wp_create_nonce('imania_account_nonce'),
				'profileNonce' => wp_create_nonce('imania_account_profile_nonce'),
				'baseUrl' => trailingslashit($account_base_url),
				'endpoints' => array(
					'profile' => function_exists('wc_get_account_endpoint_url') ? wc_get_account_endpoint_url('profile') : trailingslashit($account_base_url),
					'orders' => function_exists('wc_get_account_endpoint_url') ? wc_get_account_endpoint_url('orders') : trailingslashit($account_base_url),
					'wishlist' => function_exists('wc_get_account_endpoint_url') ? wc_get_account_endpoint_url('wishlist') : trailingslashit($account_base_url),
					'payment-methods' => function_exists('wc_get_account_endpoint_url') ? wc_get_account_endpoint_url('payment-methods') : trailingslashit($account_base_url),
				),
				'messages' => array(
					'genericError' => __('NÃ£o foi possÃ­vel carregar esta seÃ§Ã£o agora. Tente novamente.', 'imania-store'),
					'profileSaved' => __('Perfil atualizado com sucesso.', 'imania-store'),
					'profileSaveError' => __('Nao foi possivel salvar seu perfil agora.', 'imania-store'),
				),
			)
		);
	}

	if (is_singular() && comments_open() && get_option('thread_comments')) {
		wp_enqueue_script('comment-reply');
	}
}
add_action('wp_enqueue_scripts', 'imania_store_scripts');
