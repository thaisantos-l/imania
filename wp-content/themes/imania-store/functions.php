<?php
/**
 * Imania Store functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Imania_Store
 */

if (!defined('_S_VERSION')) {
	// Replace the version number of the theme on each release.
	define('_S_VERSION', '1.0.0');
}

/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which
 * runs before the init hook. The init hook is too late for some features, such
 * as indicating support for post thumbnails.
 */
function imania_store_setup()
{
	/*
	 * Make theme available for translation.
	 * Translations can be filed in the /languages/ directory.
	 * If you're building a theme based on Imania Store, use a find and replace
	 * to change 'imania-store' to the name of your theme in all the template files.
	 */
	load_theme_textdomain('imania-store', get_template_directory() . '/languages');

	// Add default posts and comments RSS feed links to head.
	add_theme_support('automatic-feed-links');

	/*
	 * Let WordPress manage the document title.
	 * By adding theme support, we declare that this theme does not use a
	 * hard-coded <title> tag in the document head, and expect WordPress to
	 * provide it for us.
	 */
	add_theme_support('title-tag');

	/*
	 * Enable support for Post Thumbnails on posts and pages.
	 *
	 * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
	 */
	add_theme_support('post-thumbnails');

	// This theme uses wp_nav_menu() in one location.
	register_nav_menus(
		array(
			'menu-1' => esc_html__('Primary', 'imania-store'),
		)
	);

	/*
	 * Switch default core markup for search form, comment form, and comments
	 * to output valid HTML5.
	 */
	add_theme_support(
		'html5',
		array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
			'style',
			'script',
		)
	);

	// Set up the WordPress core custom background feature.
	add_theme_support(
		'custom-background',
		apply_filters(
			'imania_store_custom_background_args',
			array(
				'default-color' => 'ffffff',
				'default-image' => '',
			)
		)
	);

	// Add theme support for selective refresh for widgets.
	add_theme_support('customize-selective-refresh-widgets');

	/**
	 * Add support for core custom logo.
	 *
	 * @link https://codex.wordpress.org/Theme_Logo
	 */
	add_theme_support(
		'custom-logo',
		array(
			'height' => 250,
			'width' => 250,
			'flex-width' => true,
			'flex-height' => true,
		)
	);
}
add_action('after_setup_theme', 'imania_store_setup');

/**
 * Register testimonials post type.
 */
function imania_store_register_imaniacos_post_type()
{
	$labels = array(
		'name' => __('Imaniacos', 'imania-store'),
		'singular_name' => __('Imaniaco', 'imania-store'),
		'menu_name' => __('Imaniacos', 'imania-store'),
		'name_admin_bar' => __('Imaniaco', 'imania-store'),
		'add_new' => __('Adicionar novo', 'imania-store'),
		'add_new_item' => __('Adicionar novo imaniaco', 'imania-store'),
		'new_item' => __('Novo imaniaco', 'imania-store'),
		'edit_item' => __('Editar imaniaco', 'imania-store'),
		'view_item' => __('Ver imaniaco', 'imania-store'),
		'all_items' => __('Todos os imaniacos', 'imania-store'),
		'search_items' => __('Buscar imaniacos', 'imania-store'),
		'not_found' => __('Nenhum imaniaco encontrado.', 'imania-store'),
		'not_found_in_trash' => __('Nenhum imaniaco encontrado na lixeira.', 'imania-store'),
	);

	$args = array(
		'labels' => $labels,
		'public' => true,
		'show_ui' => true,
		'show_in_menu' => true,
		'show_in_rest' => true,
		'menu_icon' => 'dashicons-format-quote',
		'supports' => array('title', 'thumbnail'),
		'has_archive' => true,
		'rewrite' => array('slug' => 'imaniacos'),
	);

	register_post_type('imaniaco', $args);
}
add_action('init', 'imania_store_register_imaniacos_post_type');

/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 */
function imania_store_content_width()
{
	$GLOBALS['content_width'] = apply_filters('imania_store_content_width', 640);
}
add_action('after_setup_theme', 'imania_store_content_width', 0);

/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
function imania_store_widgets_init()
{
	register_sidebar(
		array(
			'name' => esc_html__('Sidebar', 'imania-store'),
			'id' => 'sidebar-1',
			'description' => esc_html__('Add widgets here.', 'imania-store'),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget' => '</section>',
			'before_title' => '<h2 class="widget-title">',
			'after_title' => '</h2>',
		)
	);
}
add_action('widgets_init', 'imania_store_widgets_init');

/**
 * Enqueue scripts and styles.
 */
function imania_store_scripts()
{
	$theme_js_path = get_template_directory() . '/assets/js/imania-theme.js';
	$account_orders_js_path = get_template_directory() . '/assets/js/account-orders.js';
	$theme_css_path = get_template_directory() . '/assets/css/main.css';
	$theme_js_ver = file_exists($theme_js_path) ? (string) filemtime($theme_js_path) : _S_VERSION;
	$account_orders_js_ver = file_exists($account_orders_js_path) ? (string) filemtime($account_orders_js_path) : _S_VERSION;
	$theme_css_ver = file_exists($theme_css_path) ? (string) filemtime($theme_css_path) : _S_VERSION;

	wp_enqueue_style('imania-store-fonts', 'https://fonts.googleapis.com/css2?family=Raleway:wght@400;500;600;700;800&display=swap', array(), null);
	wp_enqueue_style('imania-store-bootstrap-grid', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap-grid.min.css', array(), '5.3.3');
	wp_enqueue_style('imania-store-swiper', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css', array(), '11.2.8');
	wp_enqueue_style('imania-store-style', get_stylesheet_uri(), array(), _S_VERSION);
	wp_enqueue_style('imania-store-theme', get_template_directory_uri() . '/assets/css/main.css', array('imania-store-style', 'imania-store-bootstrap-grid', 'imania-store-swiper'), $theme_css_ver);
	wp_style_add_data('imania-store-style', 'rtl', 'replace');

	wp_enqueue_script('imania-store-navigation', get_template_directory_uri() . '/js/navigation.js', array(), _S_VERSION, true);
	wp_enqueue_script('imania-store-swiper', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js', array(), '11.2.8', true);
	wp_enqueue_script('imania-store-theme', get_template_directory_uri() . '/assets/js/imania-theme.js', array('imania-store-swiper'), $theme_js_ver, true);
	if (function_exists('is_account_page') && is_account_page()) {
		wp_enqueue_script('imania-account-orders', get_template_directory_uri() . '/assets/js/account-orders.js', array('imania-store-theme'), $account_orders_js_ver, true);
	}

	$login_url = function_exists('imania_store_get_login_to_price_url') ? imania_store_get_login_to_price_url() : wp_login_url();
	$wishlist_url = function_exists('wc_get_account_endpoint_url') ? wc_get_account_endpoint_url('wishlist') : home_url('/');
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

	if (function_exists('is_account_page') && is_account_page()) {
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

/**
 * Get user wishlist product ids.
 *
 * @param int $user_id Optional user id.
 *
 * @return int[]
 */
function imania_store_get_wishlist_ids($user_id = 0)
{
	$user_id = $user_id > 0 ? (int) $user_id : get_current_user_id();
	if ($user_id <= 0) {
		return array();
	}

	$raw = get_user_meta($user_id, 'imania_wishlist_product_ids', true);
	if (!is_array($raw)) {
		return array();
	}

	$ids = array_values(array_unique(array_filter(array_map('absint', $raw))));
	return $ids;
}

/**
 * Save wishlist ids.
 *
 * @param int   $user_id User id.
 * @param int[] $ids Product ids.
 */
function imania_store_save_wishlist_ids($user_id, array $ids)
{
	$user_id = (int) $user_id;
	if ($user_id <= 0) {
		return;
	}

	$ids = array_values(array_unique(array_filter(array_map('absint', $ids))));
	update_user_meta($user_id, 'imania_wishlist_product_ids', $ids);
	imania_store_invalidate_account_cache($user_id);
}

/**
 * Check if product is in wishlist.
 *
 * @param int      $product_id Product id.
 * @param int|null $user_id Optional user id.
 *
 * @return bool
 */
function imania_store_is_in_wishlist($product_id, $user_id = null)
{
	$product_id = absint($product_id);
	if ($product_id <= 0) {
		return false;
	}

	if (null === $user_id) {
		$user_id = get_current_user_id();
	}

	$ids = imania_store_get_wishlist_ids((int) $user_id);
	return in_array($product_id, $ids, true);
}

/**
 * Toggle wishlist item for user.
 *
 * @param int    $user_id User id.
 * @param int    $product_id Product id.
 * @param string $mode toggle|add|remove.
 *
 * @return bool True when favorited after operation.
 */
function imania_store_update_wishlist_item($user_id, $product_id, $mode = 'toggle')
{
	$user_id = (int) $user_id;
	$product_id = absint($product_id);
	$mode = sanitize_key((string) $mode);
	if ($user_id <= 0 || $product_id <= 0) {
		return false;
	}

	$ids = imania_store_get_wishlist_ids($user_id);
	$index = array_search($product_id, $ids, true);
	$exists = false !== $index;

	if ('add' === $mode && !$exists) {
		$ids[] = $product_id;
		$exists = true;
	} elseif ('remove' === $mode && $exists) {
		unset($ids[$index]);
		$exists = false;
	} elseif ('toggle' === $mode) {
		if ($exists) {
			unset($ids[$index]);
			$exists = false;
		} else {
			$ids[] = $product_id;
			$exists = true;
		}
	}

	imania_store_save_wishlist_ids($user_id, $ids);
	return $exists;
}

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

	if (!current_user_can('edit_user', $user_id)) {
		imania_store_send_account_json_error(__('VocÃª nao tem permissao para editar este perfil.', 'imania-store'), 403, 'forbidden');
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

	if ('' === $email || !is_email($email)) {
		imania_store_send_account_json_error(__('Informe um e-mail valido.', 'imania-store'), 422, 'invalid_email');
	}

	$email_owner_id = email_exists($email);
	if ($email_owner_id && (int) $email_owner_id !== $user_id) {
		imania_store_send_account_json_error(__('Este e-mail ja esta em uso por outra conta.', 'imania-store'), 422, 'email_in_use');
	}

	$customer_type = imania_store_resolve_customer_type($user_id);
	if (!in_array($customer_type, array('pf', 'pj'), true)) {
		imania_store_send_account_json_error(__('Tipo de cliente invalido para esta conta.', 'imania-store'), 422, 'invalid_customer_type');
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

/**
 * Implement the Custom Header feature.
 */
require get_template_directory() . '/inc/custom-header.php';

/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/inc/template-tags.php';

/**
 * Functions which enhance the theme by hooking into WordPress.
 */
require get_template_directory() . '/inc/template-functions.php';

/**
 * Home helpers.
 */
require get_template_directory() . '/inc/home.php';

/**
 * Customizer additions.
 */
require get_template_directory() . '/inc/customizer.php';

/**
 * Load Jetpack compatibility file.
 */
if (defined('JETPACK__VERSION')) {
	require get_template_directory() . '/inc/jetpack.php';
}

/**
 * Load WooCommerce compatibility file.
 */
if (class_exists('WooCommerce')) {
	require get_template_directory() . '/inc/woocommerce.php';
}

