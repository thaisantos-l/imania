<?php

namespace Imania\PricingEngine\Infrastructure\Woo;

use Imania\PricingEngine\Support\MetaKeys;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class PriceHooks {

	public function register() {
		add_filter( 'woocommerce_get_price_html', array( $this, 'hide_price_for_guests' ), 99, 2 );
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
