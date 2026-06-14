<?php
/**
 * Product catalog queries, filters and progressive loading.
 *
 * @package Imania_Store
 */

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Prevent WooCommerce from treating empty price inputs as a zero-price filter.
 */
function imania_store_normalize_catalog_price_request()
{
	foreach (array('min_price', 'max_price') as $price_key) {
		if (isset($_GET[$price_key]) && '' === trim((string) wp_unslash($_GET[$price_key]))) {
			unset($_GET[$price_key], $_REQUEST[$price_key]);
		}
	}
}
add_action('parse_request', 'imania_store_normalize_catalog_price_request', 5);

/**
 * Number of products rendered in each catalog batch.
 *
 * @return int
 */
function imania_store_catalog_per_page()
{
	return 20;
}

/**
 * Whether the current request is a WooCommerce product search.
 *
 * @return bool
 */
function imania_store_is_product_search()
{
	return function_exists('is_search')
		&& is_search()
		&& function_exists('is_post_type_archive')
		&& is_post_type_archive('product');
}

/**
 * Whether the current request uses the custom catalog.
 *
 * @return bool
 */
function imania_store_is_catalog_request()
{
	return function_exists('is_shop')
		&& (
			imania_store_is_product_search()
			|| is_shop()
			|| (function_exists('is_product_category') && is_product_category())
		);
}

/**
 * Return the selected product search ordering.
 *
 * @return string
 */
function imania_store_get_search_order()
{
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$order = isset($_GET['catalog_order']) ? sanitize_key(wp_unslash($_GET['catalog_order'])) : 'popularity';
	$allowed = array('popularity', 'title', 'price');

	return in_array($order, $allowed, true) ? $order : 'popularity';
}

/**
 * Return safe WooCommerce ordering arguments for product search results.
 *
 * @param string $order Selected catalog order.
 *
 * @return array{orderby:string,order:string,meta_key:string}
 */
function imania_store_get_search_ordering_args($order)
{
	$mapping = array(
		'popularity' => array('popularity', 'DESC'),
		'title' => array('title', 'ASC'),
		'price' => array('price', 'ASC'),
	);
	$selected = isset($mapping[$order]) ? $mapping[$order] : $mapping['popularity'];

	if (function_exists('WC') && WC()->query instanceof WC_Query) {
		return WC()->query->get_catalog_ordering_args($selected[0], $selected[1]);
	}

	return array(
		'orderby' => 'title' === $order ? 'title' : 'date',
		'order' => 'title' === $order ? 'ASC' : 'DESC',
		'meta_key' => '',
	);
}

/**
 * Sanitize selected category slugs.
 *
 * @param mixed $raw_categories Raw request value.
 *
 * @return string[]
 */
function imania_store_sanitize_catalog_categories($raw_categories)
{
	$categories = is_array($raw_categories) ? $raw_categories : array($raw_categories);
	$categories = array_filter(array_map('sanitize_title', array_map('wp_unslash', $categories)));

	if (empty($categories)) {
		return array();
	}

	$terms = get_terms(
		array(
			'taxonomy' => 'product_cat',
			'hide_empty' => false,
			'slug' => array_values(array_unique($categories)),
			'fields' => 'slugs',
		)
	);

	return is_wp_error($terms) ? array() : array_values($terms);
}

/**
 * Return the filters currently applied to the catalog.
 *
 * @return array{categories:string[],min_price:string,max_price:string}
 */
function imania_store_get_catalog_filters()
{
	// phpcs:disable WordPress.Security.NonceVerification.Recommended
	$categories = isset($_GET['catalog_category'])
		? imania_store_sanitize_catalog_categories($_GET['catalog_category'])
		: array();
	$min_price = isset($_GET['min_price']) ? wc_format_decimal(wp_unslash($_GET['min_price'])) : '';
	$max_price = isset($_GET['max_price']) ? wc_format_decimal(wp_unslash($_GET['max_price'])) : '';
	// phpcs:enable WordPress.Security.NonceVerification.Recommended

	if ('' !== $min_price && (float) $min_price < 0) {
		$min_price = '0';
	}
	if ('' !== $max_price && (float) $max_price < 0) {
		$max_price = '0';
	}
	if ('' !== $min_price && '' !== $max_price && (float) $min_price > (float) $max_price) {
		$temp = $min_price;
		$min_price = $max_price;
		$max_price = $temp;
	}

	return array(
		'categories' => $categories,
		'min_price' => $min_price,
		'max_price' => $max_price,
	);
}

/**
 * Force a predictable catalog query and apply Shop category filters.
 *
 * @param WP_Query $query WooCommerce product query.
 */
function imania_store_configure_catalog_query($query)
{
	if (is_admin() || !$query instanceof WP_Query || !imania_store_is_catalog_request()) {
		return;
	}

	$query->set('posts_per_page', imania_store_catalog_per_page());

	if (imania_store_is_product_search()) {
		$ordering_args = imania_store_get_search_ordering_args(imania_store_get_search_order());
		$query->set('orderby', $ordering_args['orderby']);
		$query->set('order', $ordering_args['order']);
		if (!empty($ordering_args['meta_key'])) {
			$query->set('meta_key', $ordering_args['meta_key']);
		}
		return;
	}

	$query->set(
		'orderby',
		array(
			'date' => 'DESC',
			'ID' => 'DESC',
		)
	);
	$query->set('order', 'DESC');

	if (!is_shop()) {
		return;
	}

	$filters = imania_store_get_catalog_filters();
	if (empty($filters['categories'])) {
		return;
	}

	$tax_query = (array) $query->get('tax_query');
	$tax_query[] = array(
		'taxonomy' => 'product_cat',
		'field' => 'slug',
		'terms' => $filters['categories'],
		'operator' => 'IN',
		'include_children' => true,
	);
	$query->set('tax_query', $tax_query);
}
add_action('woocommerce_product_query', 'imania_store_configure_catalog_query', 30);

/**
 * Keep the default ordering aligned with the required newest-first catalog.
 *
 * @param array $args Ordering arguments.
 *
 * @return array
 */
function imania_store_catalog_ordering_args($args)
{
	if (!imania_store_is_catalog_request()) {
		return $args;
	}

	if (imania_store_is_product_search()) {
		return $args;
	}

	return array(
		'orderby' => 'date ID',
		'order' => 'DESC',
	);
}
add_filter('woocommerce_get_catalog_ordering_args', 'imania_store_catalog_ordering_args', 30);

/**
 * Get global catalog price bounds from WooCommerce's indexed lookup table.
 *
 * @return array{min:int,max:int}
 */
function imania_store_get_catalog_price_bounds()
{
	$cached = get_transient('imania_catalog_price_bounds');
	if (is_array($cached) && isset($cached['min'], $cached['max'])) {
		return $cached;
	}

	global $wpdb;

	$lookup_table = $wpdb->wc_product_meta_lookup;
	$result = $wpdb->get_row(
		"SELECT FLOOR(MIN(lookup.min_price)) AS min_price, CEIL(MAX(lookup.max_price)) AS max_price
		FROM {$lookup_table} lookup
		INNER JOIN {$wpdb->posts} products ON products.ID = lookup.product_id
		WHERE products.post_type = 'product'
			AND products.post_status = 'publish'
			AND lookup.max_price > 0",
		ARRAY_A
	);

	$bounds = array(
		'min' => isset($result['min_price']) ? max(0, (int) $result['min_price']) : 0,
		'max' => isset($result['max_price']) ? max(0, (int) $result['max_price']) : 0,
	);
	set_transient('imania_catalog_price_bounds', $bounds, 12 * HOUR_IN_SECONDS);

	return $bounds;
}

/**
 * Invalidate cached price bounds when a product changes.
 */
function imania_store_clear_catalog_price_bounds()
{
	delete_transient('imania_catalog_price_bounds');
}
add_action('woocommerce_update_product', 'imania_store_clear_catalog_price_bounds');
add_action('woocommerce_delete_product', 'imania_store_clear_catalog_price_bounds');

/**
 * Return the category tree used by the Shop filter.
 *
 * @return WP_Term[]
 */
function imania_store_get_catalog_categories()
{
	$terms = get_terms(
		array(
			'taxonomy' => 'product_cat',
			'hide_empty' => true,
			'orderby' => 'name',
			'order' => 'ASC',
		)
	);

	return is_wp_error($terms) ? array() : $terms;
}

/**
 * Render hierarchical category checkboxes.
 *
 * @param WP_Term[] $terms Terms.
 * @param string[]  $selected Selected slugs.
 * @param int       $parent Parent term id.
 */
function imania_store_render_catalog_category_options($terms, $selected, $parent = 0)
{
	$children = array_filter(
		$terms,
		static function ($term) use ($parent) {
			return $term instanceof WP_Term && (int) $term->parent === (int) $parent;
		}
	);

	if (empty($children)) {
		return;
	}
	?>
	<ul class="imania-catalog-filter__category-list">
		<?php foreach ($children as $term) : ?>
			<li>
				<label>
					<input
						type="checkbox"
						name="catalog_category[]"
						value="<?php echo esc_attr($term->slug); ?>"
						<?php checked(in_array($term->slug, $selected, true)); ?>
					/>
					<span><?php echo esc_html($term->name); ?></span>
					<small><?php echo esc_html((string) $term->count); ?></small>
				</label>
				<?php imania_store_render_catalog_category_options($terms, $selected, (int) $term->term_id); ?>
			</li>
		<?php endforeach; ?>
	</ul>
	<?php
}

/**
 * Render cards for a collection of product posts.
 *
 * @param WP_Post[] $posts Product posts.
 *
 * @return string
 */
function imania_store_render_catalog_cards($posts)
{
	ob_start();

	foreach ($posts as $post) {
		$product = wc_get_product($post);
		if (!$product instanceof WC_Product || !$product->is_visible()) {
			continue;
		}
		?>
		<div class="imania-catalog-grid__item">
			<?php
			get_template_part(
				'template-parts/home/product-card',
				null,
				array(
					'product' => $product,
					'variant' => 'catalog',
				)
			);
			?>
		</div>
		<?php
	}

	return (string) ob_get_clean();
}

/**
 * Add indexed price clauses to progressive catalog queries.
 *
 * @param array    $clauses Query clauses.
 * @param WP_Query $query Query.
 *
 * @return array
 */
function imania_store_catalog_price_clauses($clauses, $query)
{
	if (!$query->get('imania_catalog_query')) {
		return $clauses;
	}

	$min_price = $query->get('imania_min_price');
	$max_price = $query->get('imania_max_price');
	if ('' === $min_price && '' === $max_price) {
		return $clauses;
	}

	global $wpdb;

	$lookup_table = $wpdb->wc_product_meta_lookup;
	if (false === strpos($clauses['join'], 'imania_catalog_lookup')) {
		$clauses['join'] .= " INNER JOIN {$lookup_table} imania_catalog_lookup ON {$wpdb->posts}.ID = imania_catalog_lookup.product_id ";
	}

	$minimum = '' !== $min_price ? (float) $min_price : 0;
	$maximum = '' !== $max_price ? (float) $max_price : PHP_INT_MAX;
	$clauses['where'] .= $wpdb->prepare(
		' AND NOT (%f < imania_catalog_lookup.min_price OR %f > imania_catalog_lookup.max_price) ',
		$maximum,
		$minimum
	);

	return $clauses;
}
add_filter('posts_clauses', 'imania_store_catalog_price_clauses', 20, 2);

/**
 * Build a native catalog page URL for progressive enhancement.
 *
 * @param string $context Shop, category or search.
 * @param string $category_slug Current archive category.
 * @param int    $page Page number.
 * @param array  $filters Active filters.
 * @param string $search_term Product search term.
 * @param string $search_order Product search ordering.
 *
 * @return string
 */
function imania_store_get_catalog_page_url($context, $category_slug, $page, $filters, $search_term = '', $search_order = '')
{
	$base_url = wc_get_page_permalink('shop');
	if ('search' === $context) {
		$base_url = home_url('/');
	} elseif ('category' === $context && '' !== $category_slug) {
		$term = get_term_by('slug', $category_slug, 'product_cat');
		$term_url = $term instanceof WP_Term ? get_term_link($term) : '';
		if (!is_wp_error($term_url) && '' !== $term_url) {
			$base_url = $term_url;
		}
	}

	if ($page > 1) {
		$base_url = trailingslashit($base_url) . user_trailingslashit('page/' . absint($page), 'paged');
	}

	$query_args = array();
	if ('shop' === $context && !empty($filters['categories'])) {
		$query_args['catalog_category'] = $filters['categories'];
	}
	if ('' !== $filters['min_price']) {
		$query_args['min_price'] = $filters['min_price'];
	}
	if ('' !== $filters['max_price']) {
		$query_args['max_price'] = $filters['max_price'];
	}
	if ('search' === $context) {
		$query_args['s'] = $search_term;
		$query_args['post_type'] = 'product';
		$query_args['catalog_order'] = in_array($search_order, array('popularity', 'title', 'price'), true)
			? $search_order
			: 'popularity';
	}

	return empty($query_args) ? $base_url : add_query_arg($query_args, $base_url);
}

/**
 * Handle progressive product loading.
 */
function imania_store_handle_catalog_load()
{
	$page = isset($_POST['page']) ? min(500, max(2, absint($_POST['page']))) : 2;
	$context = isset($_POST['context']) ? sanitize_key(wp_unslash($_POST['context'])) : 'shop';
	$category_slug = isset($_POST['category']) ? sanitize_title(wp_unslash($_POST['category'])) : '';
	$categories = isset($_POST['categories'])
		? imania_store_sanitize_catalog_categories($_POST['categories'])
		: array();
	$min_price = isset($_POST['min_price']) ? wc_format_decimal(wp_unslash($_POST['min_price'])) : '';
	$max_price = isset($_POST['max_price']) ? wc_format_decimal(wp_unslash($_POST['max_price'])) : '';
	$search_term = isset($_POST['search']) ? sanitize_text_field(wp_unslash($_POST['search'])) : '';
	$search_order = isset($_POST['order']) ? sanitize_key(wp_unslash($_POST['order'])) : 'popularity';

	if (!in_array($context, array('shop', 'category', 'search'), true)) {
		$context = 'shop';
	}
	if (!in_array($search_order, array('popularity', 'title', 'price'), true)) {
		$search_order = 'popularity';
	}

	$tax_query = array('relation' => 'AND');
	$visibility = wc_get_product_visibility_term_ids();
	$excluded_visibility = array_filter(
		array(
			isset($visibility['exclude-from-catalog']) ? $visibility['exclude-from-catalog'] : 0,
			'yes' === get_option('woocommerce_hide_out_of_stock_items') && isset($visibility['outofstock'])
				? $visibility['outofstock']
				: 0,
		)
	);

	if (!empty($excluded_visibility)) {
		$tax_query[] = array(
			'taxonomy' => 'product_visibility',
			'field' => 'term_taxonomy_id',
			'terms' => array_map('absint', $excluded_visibility),
			'operator' => 'NOT IN',
		);
	}

	if ('category' === $context && '' !== $category_slug) {
		$tax_query[] = array(
			'taxonomy' => 'product_cat',
			'field' => 'slug',
			'terms' => array($category_slug),
			'include_children' => true,
		);
	} elseif ('shop' === $context && !empty($categories)) {
		$tax_query[] = array(
			'taxonomy' => 'product_cat',
			'field' => 'slug',
			'terms' => $categories,
			'operator' => 'IN',
			'include_children' => true,
		);
	}

	$query_args = array(
		'post_type' => 'product',
		'post_status' => 'publish',
		'posts_per_page' => imania_store_catalog_per_page(),
		'paged' => $page,
		'orderby' => array(
			'date' => 'DESC',
			'ID' => 'DESC',
		),
		'order' => 'DESC',
		'ignore_sticky_posts' => true,
		'tax_query' => $tax_query,
		'imania_catalog_query' => true,
		'imania_min_price' => $min_price,
		'imania_max_price' => $max_price,
	);

	if ('search' === $context) {
		$ordering_args = imania_store_get_search_ordering_args($search_order);
		$query_args['s'] = $search_term;
		$query_args['orderby'] = $ordering_args['orderby'];
		$query_args['order'] = $ordering_args['order'];
		if (!empty($ordering_args['meta_key'])) {
			$query_args['meta_key'] = $ordering_args['meta_key'];
		}
	}

	$query = new WP_Query($query_args);

	$filters = array(
		'categories' => $categories,
		'min_price' => $min_price,
		'max_price' => $max_price,
	);
	$has_more = $page < (int) $query->max_num_pages;

	wp_send_json_success(
		array(
			'html' => imania_store_render_catalog_cards($query->posts),
			'page' => $page,
			'maxPages' => (int) $query->max_num_pages,
			'hasMore' => $has_more,
			'nextUrl' => $has_more
				? imania_store_get_catalog_page_url(
					$context,
					$category_slug,
					$page + 1,
					$filters,
					$search_term,
					$search_order
				)
				: '',
		)
	);
}
add_action('wc_ajax_imania_load_catalog', 'imania_store_handle_catalog_load');
