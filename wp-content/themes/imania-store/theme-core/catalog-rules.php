<?php

/**
 * Restrict purchasable products for guests.
 *
 * @param bool       $purchasable Current state.
 * @param WC_Product $product     Product object.
 *
 * @return bool
 */
function imania_store_restrict_guest_product_purchase($purchasable, $product)
{
	if (is_user_logged_in()) {
		return (bool) $purchasable;
	}

	return false;
}
add_filter('woocommerce_is_purchasable', 'imania_store_restrict_guest_product_purchase', 20, 2);
add_filter('woocommerce_variation_is_purchasable', 'imania_store_restrict_guest_product_purchase', 20, 2);

/**
 * Resolve related products from current product categories.
 *
 * @param int $product_id Product id.
 * @param int $limit      Results limit.
 *
 * @return WC_Product[]
 */
function imania_store_get_single_related_products($product_id, $limit = 4)
{
	static $request_cache = array();

	$product_id = absint($product_id);
	$limit = max(1, absint($limit));
	$cache_key = $product_id . ':' . $limit;

	if (isset($request_cache[$cache_key])) {
		return $request_cache[$cache_key];
	}

	if ($product_id <= 0 || !function_exists('wc_get_products')) {
		$request_cache[$cache_key] = array();
		return $request_cache[$cache_key];
	}

	$category_terms = wp_get_post_terms(
		$product_id,
		'product_cat',
		array(
			'orderby' => 'parent',
			'order' => 'DESC',
		)
	);
	$category_terms = is_wp_error($category_terms) ? array() : (array) $category_terms;
	$category_slug = '';

	if (!empty($category_terms)) {
		$deepest_term = null;
		$deepest_level = -1;

		foreach ($category_terms as $term) {
			if (!$term instanceof WP_Term) {
				continue;
			}

			$level = 0;
			$parent_id = (int) $term->parent;
			while ($parent_id > 0) {
				$parent_term = get_term($parent_id, 'product_cat');
				if (!$parent_term instanceof WP_Term || is_wp_error($parent_term)) {
					break;
				}
				$level++;
				$parent_id = (int) $parent_term->parent;
			}

			if ($level > $deepest_level) {
				$deepest_level = $level;
				$deepest_term = $term;
			}
		}

		if ($deepest_term instanceof WP_Term) {
			$category_slug = sanitize_title($deepest_term->slug);
		}
	}

	$args = array(
		'status' => 'publish',
		'limit' => $limit,
		'exclude' => array($product_id),
		'return' => 'objects',
		'stock_status' => 'instock',
		'visibility' => 'visible',
		'orderby' => 'date',
		'order' => 'DESC',
	);
	if ('' !== $category_slug) {
		$args['category'] = array($category_slug);
	}

	$products = wc_get_products($args);

	$request_cache[$cache_key] = is_array($products) ? $products : array();
	return $request_cache[$cache_key];
}

/**
 * Extract first WooCommerce notice message.
 *
 * @param string $type Notice type.
 *
 * @return string
 */
function imania_store_get_first_wc_notice_message($type = 'error')
{
	$notices = wc_get_notices($type);
	if (!is_array($notices) || empty($notices)) {
		return '';
	}

	$first = $notices[0];
	if (is_array($first) && !empty($first['notice'])) {
		return wp_strip_all_tags((string) $first['notice']);
	}
	if (is_string($first)) {
		return wp_strip_all_tags($first);
	}

	return '';
}
