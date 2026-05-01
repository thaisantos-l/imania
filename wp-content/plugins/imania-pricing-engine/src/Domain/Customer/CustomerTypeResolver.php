<?php

namespace Imania\PricingEngine\Domain\Customer;

use Imania\PricingEngine\Support\MetaKeys;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class CustomerTypeResolver {
	const PF = 'pf';
	const PJ = 'pj';

	/**
	 * @param int|null $user_id Optional user id.
	 *
	 * @return string|null
	 */
	public function resolve( $user_id = null ) {
		if ( null === $user_id ) {
			$user_id = get_current_user_id();
		}

		if ( empty( $user_id ) ) {
			return null;
		}

		$type = get_user_meta( $user_id, MetaKeys::CUSTOMER_TYPE, true );
		if ( self::PF === $type || self::PJ === $type ) {
			return $type;
		}

		$billing_person_type = get_user_meta( $user_id, 'billing_persontype', true );
		if ( '1' === (string) $billing_person_type ) {
			return self::PF;
		}

		if ( '2' === (string) $billing_person_type ) {
			return self::PJ;
		}

		return null;
	}

	/**
	 * @param mixed $type Candidate type.
	 *
	 * @return bool
	 */
	public function is_valid( $type ) {
		return self::PF === $type || self::PJ === $type;
	}
}
