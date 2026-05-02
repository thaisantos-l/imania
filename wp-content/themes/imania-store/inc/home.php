<?php
/**
 * Home helpers for dynamic sections.
 *
 * @package Imania_Store
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get current URL.
 *
 * @return string
 */
function imania_store_get_current_url() {
	$scheme = is_ssl() ? 'https://' : 'http://';
	$host   = isset( $_SERVER['HTTP_HOST'] ) ? wp_unslash( $_SERVER['HTTP_HOST'] ) : '';
	$uri    = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '/';

	return esc_url_raw( $scheme . $host . $uri );
}

/**
 * Get login URL with optional redirect payload for pricing plugin.
 *
 * @return string
 */
function imania_store_get_login_to_price_url() {
	$my_account = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : wp_login_url();
	$current    = imania_store_get_current_url();
	$encoded    = base64_encode( $current ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode

	return add_query_arg( 'imania_redirect_to', $encoded, $my_account );
}

/**
 * Get home products by segment.
 *
 * @param string $segment Segment slug.
 * @param int    $limit   Product limit.
 *
 * @return array<WC_Product>
 */
function imania_store_get_home_products( $segment, $limit = 8 ) {
	static $cache = array();

	$segment = sanitize_key( $segment );
	$limit   = max( 1, absint( $limit ) );
	$key     = $segment . ':' . $limit;

	if ( isset( $cache[ $key ] ) ) {
		return $cache[ $key ];
	}

	if ( ! function_exists( 'wc_get_products' ) ) {
		$cache[ $key ] = array();
		return $cache[ $key ];
	}

	$args = array(
		'status'       => 'publish',
		'limit'        => $limit,
		'return'       => 'objects',
		'stock_status' => 'instock',
		'visibility'   => 'visible',
	);

	switch ( $segment ) {
		case 'featured':
			$args['featured'] = true;
			$args['orderby']  = 'date';
			$args['order']    = 'DESC';
			$products         = wc_get_products( $args );
			break;

		case 'bestsellers':
			$query    = new WC_Product_Query(
				array(
					'status'       => 'publish',
					'limit'        => $limit,
					'return'       => 'objects',
					'stock_status' => 'instock',
					'visibility'   => 'visible',
					'orderby'      => 'meta_value_num',
					'meta_key'     => 'total_sales',
					'order'        => 'DESC',
				)
			);
			$products = $query->get_products();
			break;

		case 'sale':
			$on_sale_ids = wc_get_product_ids_on_sale();
			if ( empty( $on_sale_ids ) ) {
				$products = array();
				break;
			}

			$args['include'] = array_slice( array_map( 'absint', $on_sale_ids ), 0, $limit );
			$args['orderby'] = 'include';
			$products        = wc_get_products( $args );
			break;

		case 'new':
		default:
			$args['orderby'] = 'date';
			$args['order']   = 'DESC';
			$products        = wc_get_products( $args );
			break;
	}

	if ( empty( $products ) && 'new' !== $segment ) {
		$args['orderby'] = 'date';
		$args['order']   = 'DESC';
		$products        = wc_get_products( $args );
	}

	$cache[ $key ] = is_array( $products ) ? $products : array();
	return $cache[ $key ];
}

/**
 * Get hero product.
 *
 * @return WC_Product|null
 */
function imania_store_get_home_hero_product() {
	$featured = imania_store_get_home_products( 'featured', 1 );
	if ( ! empty( $featured[0] ) ) {
		return $featured[0];
	}

	$latest = imania_store_get_home_products( 'new', 1 );
	return ! empty( $latest[0] ) ? $latest[0] : null;
}

/**
 * Get main product categories.
 *
 * @param int $limit Category limit.
 *
 * @return array<WP_Term>
 */
function imania_store_get_home_categories( $limit = 6 ) {
	$terms = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => true,
			'parent'     => 0,
			'number'     => absint( $limit ),
			'orderby'    => 'count',
			'order'      => 'DESC',
		)
	);

	return is_wp_error( $terms ) ? array() : $terms;
}

/**
 * Get simple store statistics.
 *
 * @return array<string,int>
 */
function imania_store_get_home_stats() {
	$product_count  = wp_count_posts( 'product' );
	$published      = isset( $product_count->publish ) ? (int) $product_count->publish : 0;
	$categories     = imania_store_get_home_categories( 50 );
	$category_count = count( $categories );

	return array(
		'products'   => $published,
		'categories' => $category_count,
		'highlights' => count( imania_store_get_home_products( 'featured', 8 ) ),
	);
}
