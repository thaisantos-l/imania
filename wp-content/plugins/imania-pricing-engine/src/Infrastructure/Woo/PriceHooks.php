<?php

namespace Imania\PricingEngine\Infrastructure\Woo;

use Imania\PricingEngine\Domain\Customer\CustomerTypeResolver;
use Imania\PricingEngine\Domain\Pricing\PriceCalculator;
use Imania\PricingEngine\Support\MetaKeys;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class PriceHooks {

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
		add_filter( 'woocommerce_get_price_html', array( $this, 'hide_price_for_guests' ), 99, 2 );

		add_filter( 'woocommerce_product_get_price', array( $this, 'apply_customer_price' ), 99, 2 );
		add_filter( 'woocommerce_product_variation_get_price', array( $this, 'apply_customer_price' ), 99, 2 );

		add_filter( 'woocommerce_variation_prices_price', array( $this, 'apply_variation_prices' ), 99, 3 );
		add_filter( 'woocommerce_variation_prices_regular_price', array( $this, 'apply_variation_prices' ), 99, 3 );
	}

	/**
	 * @param string      $price_html Price html.
	 * @param \WC_Product $product Product object.
	 *
	 * @return string
	 */
	public function hide_price_for_guests( $price_html, $product ) {
		if ( is_user_logged_in() ) {
			return $price_html;
		}

		$my_account_url = wc_get_page_permalink( 'myaccount' );
		if ( empty( $my_account_url ) ) {
			$my_account_url = wp_login_url();
		}

		$current_url = $this->get_current_url();
		$target_url  = add_query_arg(
			MetaKeys::REDIRECT_QUERY_KEY,
			base64_encode( $current_url ),
			$my_account_url
		);

		return sprintf(
			'<a class="imania-login-to-price" href="%s">%s</a>',
			esc_url( $target_url ),
			esc_html__( 'Faca login para ver o preco.', 'imania-pricing-engine' )
		);
	}

	/**
	 * @param string|float $price Product price.
	 * @param \WC_Product  $product Product object.
	 *
	 * @return string|float
	 */
	public function apply_customer_price( $price, $product ) {
		if ( ! is_user_logged_in() ) {
			return $price;
		}

		if ( '' === $price || null === $price ) {
			return $price;
		}

		$customer_type = $this->customer_type_resolver->resolve();
		if ( ! $this->customer_type_resolver->is_valid( $customer_type ) ) {
			return $price;
		}

		if ( ! $product instanceof \WC_Product ) {
			return $price;
		}

		return $this->price_calculator->calculate_price( $product, $customer_type, $price );
	}

	/**
	 * @param string|float $price Variation price.
	 * @param \WC_Product_Variation $variation Variation product.
	 * @param \WC_Product_Variable  $product Parent variable product.
	 *
	 * @return string|float
	 */
	public function apply_variation_prices( $price, $variation, $product ) {
		if ( ! is_user_logged_in() ) {
			return $price;
		}

		$customer_type = $this->customer_type_resolver->resolve();
		if ( ! $this->customer_type_resolver->is_valid( $customer_type ) ) {
			return $price;
		}

		return $this->price_calculator->calculate_price( $variation, $customer_type, $price );
	}

	/**
	 * @return string
	 */
	private function get_current_url() {
		$scheme = is_ssl() ? 'https://' : 'http://';
		$host   = isset( $_SERVER['HTTP_HOST'] ) ? wp_unslash( $_SERVER['HTTP_HOST'] ) : '';
		$uri    = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '/';

		$url = $scheme . $host . $uri;
		return esc_url_raw( $url );
	}
}
