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
				'baseUrl' => trailingslashit($account_base_url),
				'endpoints' => array(
					'profile' => function_exists('wc_get_account_endpoint_url') ? wc_get_account_endpoint_url('profile') : trailingslashit($account_base_url),
					'orders' => function_exists('wc_get_account_endpoint_url') ? wc_get_account_endpoint_url('orders') : trailingslashit($account_base_url),
					'wishlist' => function_exists('wc_get_account_endpoint_url') ? wc_get_account_endpoint_url('wishlist') : trailingslashit($account_base_url),
					'payment-methods' => function_exists('wc_get_account_endpoint_url') ? wc_get_account_endpoint_url('payment-methods') : trailingslashit($account_base_url),
				),
				'messages' => array(
					'genericError' => __('NÃ£o foi possÃ­vel carregar esta seÃ§Ã£o agora. Tente novamente.', 'imania-store'),
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
	if (!is_user_logged_in()) {
		echo '<p>' . esc_html__('FaÃ§a login para acessar sua wishlist.', 'imania-store') . '</p>';
		return;
	}

	$ids = imania_store_get_wishlist_ids();
	if (empty($ids)) {
		echo '<p>' . esc_html__('Sua wishlist estÃ¡ vazia.', 'imania-store') . '</p>';
		return;
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
	$endpoint = sanitize_key((string) $endpoint);
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

	return (string) ob_get_clean();
}

/**
 * Handle account endpoint AJAX load.
 */
function imania_store_handle_account_endpoint_ajax()
{
	check_ajax_referer('imania_account_nonce', 'nonce');

	if (!is_user_logged_in()) {
		wp_send_json_error(
			array(
				'message' => __('FaÃ§a login para acessar esta Ã¡rea.', 'imania-store'),
			),
			401
		);
	}

	$endpoint = isset($_POST['endpoint']) ? sanitize_key(wp_unslash($_POST['endpoint'])) : 'profile';
	$allowed = array('profile', 'orders', 'wishlist', 'payment-methods');
	if (!in_array($endpoint, $allowed, true)) {
		$endpoint = 'profile';
	}

	$html = imania_store_render_account_endpoint_to_string($endpoint);
	wp_send_json_success(
		array(
			'endpoint' => $endpoint,
			'html' => $html,
		)
	);
}
add_action('wp_ajax_imania_account_endpoint', 'imania_store_handle_account_endpoint_ajax');

/**
 * Handle wishlist AJAX toggle.
 */
function imania_store_handle_wishlist_ajax()
{
	check_ajax_referer('imania_wishlist_nonce', 'nonce');

	if (!is_user_logged_in()) {
		wp_send_json_error(
			array('message' => __('FaÃ§a login para adicionar Ã  wishlist.', 'imania-store')),
			401
		);
	}

	$product_id = isset($_POST['product_id']) ? absint(wp_unslash($_POST['product_id'])) : 0;
	$mode = isset($_POST['mode']) ? sanitize_key(wp_unslash($_POST['mode'])) : 'toggle';
	if ($product_id <= 0 || !in_array($mode, array('toggle', 'add', 'remove'), true)) {
		wp_send_json_error(array('message' => __('Produto invÃ¡lido.', 'imania-store')), 400);
	}

	$product = wc_get_product($product_id);
	if (!$product instanceof WC_Product) {
		wp_send_json_error(array('message' => __('Produto nÃ£o encontrado.', 'imania-store')), 404);
	}

	$is_favorited = imania_store_update_wishlist_item(get_current_user_id(), $product_id, $mode);
	$count = count(imania_store_get_wishlist_ids());

	wp_send_json_success(
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

