<?php

namespace Imania\PricingEngine\Infrastructure\Auth;

use Imania\PricingEngine\Support\MetaKeys;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class LoginRedirectHandler {

	public function register() {
		add_filter( 'woocommerce_login_redirect', array( $this, 'handle_login_redirect' ), 10, 2 );
		add_filter( 'woocommerce_registration_redirect', array( $this, 'handle_registration_redirect' ) );
	}

	/**
	 * @param string   $redirect Default redirect URL.
	 * @param \WP_User $user User object.
	 *
	 * @return string
	 */
	public function handle_login_redirect( $redirect, $user ) {
		if ( ! $user instanceof \WP_User ) {
			return $redirect;
		}

		return $this->extract_safe_redirect( $redirect );
	}

	/**
	 * @param string $redirect Default URL.
	 *
	 * @return string
	 */
	public function handle_registration_redirect( $redirect ) {
		return $this->extract_safe_redirect( $redirect );
	}

	/**
	 * @param string $fallback Fallback URL.
	 *
	 * @return string
	 */
	private function extract_safe_redirect( $fallback ) {
		$raw = isset( $_REQUEST[ MetaKeys::REDIRECT_QUERY_KEY ] ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			? wp_unslash( $_REQUEST[ MetaKeys::REDIRECT_QUERY_KEY ] ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			: '';

		if ( empty( $raw ) ) {
			return $fallback;
		}

		$decoded  = base64_decode( sanitize_text_field( $raw ), true );
		$candidate = is_string( $decoded ) ? $decoded : '';
		if ( '' === $candidate ) {
			return $fallback;
		}

		$validated = wp_validate_redirect( $candidate, $fallback );
		if ( empty( $validated ) ) {
			return $fallback;
		}

		$site_host  = wp_parse_url( home_url(), PHP_URL_HOST );
		$target_host = wp_parse_url( $validated, PHP_URL_HOST );
		if ( ! empty( $site_host ) && ! empty( $target_host ) && $site_host !== $target_host ) {
			return $fallback;
		}

		return $validated;
	}
}
