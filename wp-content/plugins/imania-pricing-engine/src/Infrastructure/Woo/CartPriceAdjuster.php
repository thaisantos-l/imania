<?php

namespace Imania\PricingEngine\Infrastructure\Woo;

use Imania\PricingEngine\Domain\Customer\CustomerTypeResolver;
use Imania\PricingEngine\Domain\Pricing\PriceCalculator;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class CartPriceAdjuster {

	/**
	 * @var CustomerTypeResolver
	 */
	private $customer_type_resolver;

	/**
	 * @var PriceCalculator
	 */
	private $price_calculator;

	public function __construct( CustomerTypeResolver $customer_type_resolver, PriceCalculator $price_calculator ) {
		$this->customer_type_resolver = $customer_type_resolver;
		$this->price_calculator       = $price_calculator;
	}

	public function register() {
		add_action( 'woocommerce_before_calculate_totals', array( $this, 'apply_cart_prices' ), 99 );
	}

	/**
	 * @param \WC_Cart $cart Cart object.
	 */
	public function apply_cart_prices( $cart ) {
		if ( is_admin() && ! wp_doing_ajax() ) {
			return;
		}

		if ( ! is_user_logged_in() ) {
			return;
		}

		if ( ! $cart instanceof \WC_Cart ) {
			return;
		}

		$customer_type = $this->customer_type_resolver->resolve();
		if ( ! $this->customer_type_resolver->is_valid( $customer_type ) ) {
			return;
		}

		foreach ( $cart->get_cart() as $item_key => $item ) {
			if ( empty( $item['data'] ) || ! $item['data'] instanceof \WC_Product ) {
				continue;
			}

			$product = $item['data'];

			if ( isset( $item['imania_original_price'] ) ) {
				$base_price = $item['imania_original_price'];
			} else {
				$base_price = $product->get_price( 'edit' );
				$cart->cart_contents[ $item_key ]['imania_original_price'] = $base_price;
			}

			if ( '' === $base_price || null === $base_price ) {
				continue;
			}

			$new_price = $this->price_calculator->calculate_price( $product, $customer_type, $base_price );
			$product->set_price( $new_price );
		}
	}
}
