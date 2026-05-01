<?php

namespace Imania\PricingEngine\Domain\Pricing;

use Imania\PricingEngine\Support\RequestCache;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class PriceCalculator {

	/**
	 * @var PricingRuleResolver
	 */
	private $resolver;

	/**
	 * @var RequestCache
	 */
	private $cache;

	public function __construct( PricingRuleResolver $resolver, RequestCache $cache ) {
		$this->resolver = $resolver;
		$this->cache    = $cache;
	}

	/**
	 * @param \WC_Product $product Product object.
	 * @param string       $customer_type pf|pj.
	 * @param float|string $base_price Original price.
	 *
	 * @return string
	 */
	public function calculate_price( \WC_Product $product, $customer_type, $base_price ) {
		$cache_key = sprintf( 'price:%d:%s:%s', $product->get_id(), $customer_type, (string) $base_price );

		return $this->cache->remember(
			$cache_key,
			function () use ( $product, $customer_type, $base_price ) {
				$rule = $this->resolver->resolve( $product, $customer_type );
				if ( null === $rule ) {
					return (string) $base_price;
				}

				$base_value = (float) $base_price;
				if ( 'fixed' === $rule['mode'] ) {
					$final_price = max( 0, (float) $rule['value'] );
					return (string) wc_format_decimal( $final_price, wc_get_price_decimals() );
				}

				$discount = max( 0, min( 100, (float) $rule['value'] ) );
				$final    = max( 0, $base_value - ( $base_value * ( $discount / 100 ) ) );
				return (string) wc_format_decimal( $final, wc_get_price_decimals() );
			}
		);
	}
}
