<?php

namespace Imania\PricingEngine\Domain\Pricing;

use Imania\PricingEngine\Domain\Customer\CustomerTypeResolver;
use Imania\PricingEngine\Support\MetaKeys;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class PricingRuleResolver {

	/**
	 * @param \WC_Product $product Product object.
	 * @param string       $customer_type pf|pj.
	 *
	 * @return array<string,mixed>|null
	 */
	public function resolve( \WC_Product $product, $customer_type ) {
		$product_rule = $this->resolve_from_product( $product, $customer_type );
		if ( null !== $product_rule ) {
			return $product_rule;
		}

		if ( $product->is_type( 'variation' ) ) {
			$parent_id = $product->get_parent_id();
			if ( $parent_id > 0 ) {
				$parent_product = wc_get_product( $parent_id );
				if ( $parent_product instanceof \WC_Product ) {
					$parent_rule = $this->resolve_from_product( $parent_product, $customer_type );
					if ( null !== $parent_rule ) {
						return $parent_rule;
					}
				}
			}
		}

		return $this->resolve_from_global_options( $customer_type );
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
}
