<?php

namespace Imania\PricingEngine\Domain\Order;

use Imania\PricingEngine\Support\RequestCache;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class FirstPurchaseChecker {

	/**
	 * @var RequestCache
	 */
	private $cache;

	/**
	 * @var string[]
	 */
	private $paid_statuses = array( 'wc-pending', 'wc-processing', 'wc-on-hold', 'wc-completed' );

	public function __construct( RequestCache $cache ) {
		$this->cache = $cache;
	}

	/**
	 * @param int    $user_id User id.
	 * @param string $email Billing e-mail.
	 * @param string $document CPF/CNPJ normalized.
	 *
	 * @return bool
	 */
	public function is_first_purchase( $user_id, $email, $document ) {
		$cache_key = sprintf( 'first-purchase:%d:%s:%s', (int) $user_id, (string) $email, (string) $document );

		return (bool) $this->cache->remember(
			$cache_key,
			function () use ( $user_id, $email, $document ) {
				if ( $this->has_order_by_customer( (int) $user_id ) ) {
					return false;
				}

				if ( '' !== $email && $this->has_order_by_email( $email ) ) {
					return false;
				}

				if ( '' !== $document && $this->has_order_by_document( $document ) ) {
					return false;
				}

				return true;
			}
		);
	}

	/**
	 * @param int $user_id User id.
	 *
	 * @return bool
	 */
	private function has_order_by_customer( $user_id ) {
		if ( $user_id <= 0 ) {
			return false;
		}

		$orders = wc_get_orders(
			array(
				'customer_id' => $user_id,
				'status'      => $this->paid_statuses,
				'limit'       => 1,
				'return'      => 'ids',
			)
		);

		return ! empty( $orders );
	}

	/**
	 * @param string $email Billing e-mail.
	 *
	 * @return bool
	 */
	private function has_order_by_email( $email ) {
		$orders = wc_get_orders(
			array(
				'billing_email' => sanitize_email( $email ),
				'status'        => $this->paid_statuses,
				'limit'         => 1,
				'return'        => 'ids',
			)
		);

		return ! empty( $orders );
	}

	/**
	 * @param string $document CPF/CNPJ.
	 *
	 * @return bool
	 */
	private function has_order_by_document( $document ) {
		$keys = array( '_billing_cpf', '_billing_cnpj', 'billing_cpf', 'billing_cnpj' );

		foreach ( $keys as $key ) {
			$orders = wc_get_orders(
				array(
					'status'     => $this->paid_statuses,
					'limit'      => 1,
					'return'     => 'ids',
					'meta_key'   => $key,
					'meta_value' => $document,
				)
			);

			if ( ! empty( $orders ) ) {
				return true;
			}
		}

		return false;
	}
}
