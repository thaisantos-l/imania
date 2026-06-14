<?php

namespace Imania\PricingEngine\Support;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class MetaKeys {
	const CUSTOMER_TYPE          = 'imania_customer_type';
	const DOCUMENT_NUMBER        = 'imania_document';
	const DOCUMENT_TYPE          = 'imania_document_type';

	const OPTION_GROUP                      = 'imania_pricing_engine_settings';
	const OPTION_VAREJO_MINIMUM             = 'imania_varejo_minimum';
	const OPTION_ATACADO_MINIMUM            = 'imania_atacado_minimum';
	const OPTION_FIRST_PURCHASE_PERCENT     = 'imania_first_purchase_discount_percent';
	const OPTION_ATACADO_MIN_UNITS          = 'imania_atacado_min_units_per_product';
	const OPTION_PROMO_TEN_PLUS_TWO_ENABLED = 'imania_promo_ten_plus_two_enabled';

	const REDIRECT_QUERY_KEY = 'imania_redirect_to';
}
