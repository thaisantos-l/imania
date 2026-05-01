<?php

namespace Imania\PricingEngine\Support;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class RequestCache {

	/**
	 * @var array<string,mixed>
	 */
	private $data = array();

	/**
	 * @param string   $key Cache key.
	 * @param callable $resolver Lazy resolver.
	 *
	 * @return mixed
	 */
	public function remember( $key, callable $resolver ) {
		if ( array_key_exists( $key, $this->data ) ) {
			return $this->data[ $key ];
		}

		$this->data[ $key ] = $resolver();
		return $this->data[ $key ];
	}

	/**
	 * @param string $prefix Cache key prefix.
	 */
	public function forget_by_prefix( $prefix ) {
		foreach ( array_keys( $this->data ) as $key ) {
			if ( 0 === strpos( $key, $prefix ) ) {
				unset( $this->data[ $key ] );
			}
		}
	}
}
