<?php

namespace Imania\PricingEngine\Domain\Pricing;

use Imania\PricingEngine\Domain\Customer\CustomerTypeResolver;
use Imania\PricingEngine\Support\MetaKeys;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class PricingRuleResolver {

	/**
	 * @var string[]
	 */
	private $allowed_priority = array( 'product', 'category', 'global' );

	/**
	 * @param \WC_Product $product Product object.
	 * @param string       $customer_type pf|pj.
	 *
	 * @return array<string,mixed>|null
	 */
	public function resolve( \WC_Product $product, $customer_type ) {
		foreach ( $this->get_priority_chain() as $scope ) {
			if ( 'product' === $scope ) {
				$product_rule = $this->resolve_from_product_scope( $product, $customer_type );
				if ( null !== $product_rule ) {
					return $product_rule;
				}
			}

			if ( 'category' === $scope ) {
				$category_rule = $this->resolve_from_category_scope( $product, $customer_type );
				if ( null !== $category_rule ) {
					return $category_rule;
				}
			}

			if ( 'global' === $scope ) {
				$global_rule = $this->resolve_from_global_options( $customer_type );
				if ( null !== $global_rule ) {
					return $global_rule;
				}
			}
		}

		return null;
	}

	/**
	 * @param \WC_Product $product Product object.
	 * @param string       $customer_type pf|pj.
	 *
	 * @return array<string,mixed>|null
	 */
	private function resolve_from_product( \WC_Product $product, $customer_type ) {
		$mode = (string) $product->get_meta( MetaKeys::PRICING_MODE, true );
		if ( 'fixed' !== $mode && 'discount' !== $mode ) {
			return null;
		}

		$value = null;
		if ( 'fixed' === $mode ) {
			$value = CustomerTypeResolver::PF === $customer_type
				? $product->get_meta( MetaKeys::PRICE_PF, true )
				: $product->get_meta( MetaKeys::PRICE_PJ, true );
		} else {
			$value = CustomerTypeResolver::PF === $customer_type
				? $product->get_meta( MetaKeys::DISCOUNT_PF, true )
				: $product->get_meta( MetaKeys::DISCOUNT_PJ, true );
		}

		if ( '' === $value || null === $value ) {
			return null;
		}

		return array(
			'mode'  => $mode,
			'value' => (float) wc_format_decimal( $value ),
			'scope' => 'product',
		);
	}

	/**
	 * @param \WC_Product $product Product object.
	 * @param string      $customer_type pf|pj.
	 *
	 * @return array<string,mixed>|null
	 */
	private function resolve_from_product_scope( \WC_Product $product, $customer_type ) {
		$rule = $this->resolve_from_product( $product, $customer_type );
		if ( null !== $rule ) {
			return $rule;
		}

		if ( $product->is_type( 'variation' ) ) {
			$parent_id = $product->get_parent_id();
			if ( $parent_id > 0 ) {
				$parent_product = wc_get_product( $parent_id );
				if ( $parent_product instanceof \WC_Product ) {
					return $this->resolve_from_product( $parent_product, $customer_type );
				}
			}
		}

		return null;
	}

	/**
	 * @param \WC_Product $product Product object.
	 * @param string      $customer_type pf|pj.
	 *
	 * @return array<string,mixed>|null
	 */
	private function resolve_from_category_scope( \WC_Product $product, $customer_type ) {
		$product_id = $product->get_id();
		if ( $product->is_type( 'variation' ) ) {
			$product_id = $product->get_parent_id();
		}

		if ( $product_id <= 0 ) {
			return null;
		}

		$term_ids = wc_get_product_term_ids( $product_id, 'product_cat' );
		if ( empty( $term_ids ) ) {
			return null;
		}

		foreach ( $term_ids as $term_id ) {
			$rule = $this->resolve_from_category_term( (int) $term_id, $customer_type );
			if ( null !== $rule ) {
				return $rule;
			}
		}

		return null;
	}

	/**
	 * @param int    $term_id Product category id.
	 * @param string $customer_type pf|pj.
	 *
	 * @return array<string,mixed>|null
	 */
	private function resolve_from_category_term( $term_id, $customer_type ) {
		$mode = (string) get_term_meta( $term_id, MetaKeys::CATEGORY_PRICING_MODE, true );
		if ( 'fixed' !== $mode && 'discount' !== $mode ) {
			return null;
		}

		if ( 'fixed' === $mode ) {
			$value = CustomerTypeResolver::PF === $customer_type
				? get_term_meta( $term_id, MetaKeys::CATEGORY_PRICE_PF, true )
				: get_term_meta( $term_id, MetaKeys::CATEGORY_PRICE_PJ, true );
		} else {
			$value = CustomerTypeResolver::PF === $customer_type
				? get_term_meta( $term_id, MetaKeys::CATEGORY_DISCOUNT_PF, true )
				: get_term_meta( $term_id, MetaKeys::CATEGORY_DISCOUNT_PJ, true );
		}

		if ( '' === $value || null === $value ) {
			return null;
		}

		return array(
			'mode'  => $mode,
			'value' => (float) wc_format_decimal( $value ),
			'scope' => 'category',
		);
	}

	/**
	 * @param string $customer_type pf|pj.
	 *
	 * @return array<string,mixed>|null
	 */
	private function resolve_from_global_options( $customer_type ) {
		$mode = get_option( MetaKeys::OPTION_FALLBACK_MODE, '' );
		if ( 'fixed' !== $mode && 'discount' !== $mode ) {
			return null;
		}

		if ( 'fixed' === $mode ) {
			$value = CustomerTypeResolver::PF === $customer_type
				? get_option( MetaKeys::OPTION_GLOBAL_PRICE_PF, '' )
				: get_option( MetaKeys::OPTION_GLOBAL_PRICE_PJ, '' );
		} else {
			$value = CustomerTypeResolver::PF === $customer_type
				? get_option( MetaKeys::OPTION_GLOBAL_DISC_PF, '' )
				: get_option( MetaKeys::OPTION_GLOBAL_DISC_PJ, '' );
		}

		if ( '' === $value || null === $value ) {
			return null;
		}

		return array(
			'mode'  => $mode,
			'value' => (float) wc_format_decimal( $value ),
			'scope' => 'global',
		);
	}

	/**
	 * @return string[]
	 */
	private function get_priority_chain() {
		$raw       = (string) get_option( MetaKeys::OPTION_PRIORITY, 'product,category,global' );
		$requested = array_filter( array_map( 'trim', explode( ',', strtolower( $raw ) ) ) );
		if ( empty( $requested ) ) {
			return $this->allowed_priority;
		}

		$sanitized = array();
		foreach ( $requested as $scope ) {
			if ( in_array( $scope, $this->allowed_priority, true ) && ! in_array( $scope, $sanitized, true ) ) {
				$sanitized[] = $scope;
			}
		}

		if ( empty( $sanitized ) ) {
			return $this->allowed_priority;
		}

		foreach ( $this->allowed_priority as $fallback_scope ) {
			if ( ! in_array( $fallback_scope, $sanitized, true ) ) {
				$sanitized[] = $fallback_scope;
			}
		}

		return $sanitized;
	}
}
