<?php

namespace Imania\PricingEngine\Domain\Customer;

use Imania\PricingEngine\Support\MetaKeys;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class DocumentRepository {

	/**
	 * @param string   $normalized_document Document containing only digits.
	 * @param int|null $ignore_user_id Optional user to ignore.
	 *
	 * @return bool
	 */
	public function exists_for_another_user( $normalized_document, $ignore_user_id = null ) {
		if ( '' === $normalized_document ) {
			return false;
		}

		$query_args = array(
			'fields'      => 'ID',
			'number'      => 1,
			'count_total' => false,
			'meta_query'  => array(
				array(
					'key'   => MetaKeys::DOCUMENT_NUMBER,
					'value' => $normalized_document,
				),
			),
		);

		if ( ! empty( $ignore_user_id ) ) {
			$query_args['exclude'] = array( (int) $ignore_user_id );
		}

		$user_query = new \WP_User_Query( $query_args );
		return ! empty( $user_query->get_results() );
	}
}
