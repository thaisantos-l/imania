<?php

namespace Imania\PricingEngine\Domain\Customer;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class DocumentValidator {

	/**
	 * @param string $document Raw document.
	 *
	 * @return string
	 */
	public function normalize( $document ) {
		return preg_replace( '/\D+/', '', (string) $document );
	}

	/**
	 * @param string $document Raw CPF.
	 *
	 * @return bool
	 */
	public function is_valid_cpf( $document ) {
		$cpf = $this->normalize( $document );
		if ( 11 !== strlen( $cpf ) || preg_match( '/^(\d)\1+$/', $cpf ) ) {
			return false;
		}

		for ( $t = 9; $t < 11; $t++ ) {
			$sum = 0;
			for ( $c = 0; $c < $t; $c++ ) {
				$sum += (int) $cpf[ $c ] * ( ( $t + 1 ) - $c );
			}

			$digit = ( ( 10 * $sum ) % 11 ) % 10;
			if ( (int) $cpf[ $t ] !== $digit ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * @param string $document Raw CNPJ.
	 *
	 * @return bool
	 */
	public function is_valid_cnpj( $document ) {
		$cnpj = sprintf( '%014s', $this->normalize( $document ) );
		if ( 14 !== strlen( $cnpj ) || preg_match( '/^(\d)\1+$/', $cnpj ) ) {
			return false;
		}

		$weights_1 = array( 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2 );
		$weights_2 = array( 6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2 );

		$sum_1 = 0;
		for ( $i = 0; $i < 12; $i++ ) {
			$sum_1 += (int) $cnpj[ $i ] * $weights_1[ $i ];
		}

		$digit_1 = $sum_1 % 11;
		$digit_1 = $digit_1 < 2 ? 0 : 11 - $digit_1;
		if ( (int) $cnpj[12] !== $digit_1 ) {
			return false;
		}

		$sum_2 = 0;
		for ( $i = 0; $i < 13; $i++ ) {
			$sum_2 += (int) $cnpj[ $i ] * $weights_2[ $i ];
		}

		$digit_2 = $sum_2 % 11;
		$digit_2 = $digit_2 < 2 ? 0 : 11 - $digit_2;

		return (int) $cnpj[13] === $digit_2;
	}
}
