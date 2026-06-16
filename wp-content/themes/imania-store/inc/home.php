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
	$my_account = function_exists( 'imania_store_get_my_account_url' ) ? imania_store_get_my_account_url() : home_url( '/conta/' );
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
 * Get home products by category slug.
 *
 * @param string $category_slug Category slug.
 * @param int    $limit         Product limit.
 *
 * @return array<WC_Product>
 */
function imania_store_get_home_products_by_category( $category_slug, $limit = 8 ) {
	static $cache = array();

	$category_slug = sanitize_title( $category_slug );
	$limit         = max( 1, absint( $limit ) );
	$key           = $category_slug . ':' . $limit;

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
		'orderby'      => 'date',
		'order'        => 'DESC',
	);

	if ( '' !== $category_slug ) {
		$args['category'] = array( $category_slug );
	}

	$products = wc_get_products( $args );
	if ( empty( $products ) ) {
		$products = imania_store_get_home_products( 'new', $limit );
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

/**
 * Get testimonial posts.
 *
 * @param int $limit Posts limit.
 *
 * @return array<WP_Post>
 */
function imania_store_get_testimonials( $limit = 4 ) {
	static $cache = array();

	$limit = max( 1, absint( $limit ) );
	$key   = 'testimonials:' . $limit;

	if ( isset( $cache[ $key ] ) ) {
		return $cache[ $key ];
	}

	$query = new WP_Query(
		array(
			'post_type'              => 'imaniaco',
			'post_status'            => 'publish',
			'posts_per_page'         => $limit,
			'orderby'                => 'date',
			'order'                  => 'DESC',
			'no_found_rows'          => true,
			'ignore_sticky_posts'    => true,
			'update_post_meta_cache' => true,
			'update_post_term_cache' => false,
		)
	);

	$cache[ $key ] = $query->have_posts() ? $query->posts : array();
	return $cache[ $key ];
}

/**
 * Get ACF field or fallback post meta.
 *
 * @param int    $post_id Post id.
 * @param string $field_name Field name.
 *
 * @return mixed
 */
function imania_store_get_custom_field( $post_id, $field_name ) {
	if ( function_exists( 'get_field' ) ) {
		return get_field( $field_name, $post_id );
	}

	return get_post_meta( $post_id, $field_name, true );
}

/**
 * Resolve image URL from ACF value.
 *
 * @param mixed  $value ACF image value.
 * @param string $size Image size.
 *
 * @return string
 */
function imania_store_resolve_image_url( $value, $size = 'thumbnail' ) {
	if ( empty( $value ) ) {
		return '';
	}

	if ( is_array( $value ) ) {
		if ( isset( $value['sizes'][ $size ] ) && is_string( $value['sizes'][ $size ] ) ) {
			return $value['sizes'][ $size ];
		}

		if ( isset( $value['url'] ) && is_string( $value['url'] ) ) {
			return $value['url'];
		}
	}

	if ( is_numeric( $value ) ) {
		$image = wp_get_attachment_image_url( (int) $value, $size );
		return is_string( $image ) ? $image : '';
	}

	return is_string( $value ) ? $value : '';
}
