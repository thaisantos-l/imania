<?php

namespace Imania\PricingEngine\Support;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class MetaKeys {
	const CUSTOMER_TYPE          = 'imania_customer_type';
	const DOCUMENT_NUMBER        = 'imania_document';
	const DOCUMENT_TYPE          = 'imania_document_type';

	const PRICING_MODE           = '_imania_pricing_mode';
	const PRICE_PF               = '_imania_price_pf';
	const PRICE_PJ               = '_imania_price_pj';
	const DISCOUNT_PF            = '_imania_discount_pf';
	const DISCOUNT_PJ            = '_imania_discount_pj';

	const OPTION_GROUP           = 'imania_pricing_engine_settings';
	const OPTION_FALLBACK_MODE   = 'imania_fallback_mode';
	const OPTION_GLOBAL_PRICE_PF = 'imania_global_price_pf';
	const OPTION_GLOBAL_PRICE_PJ = 'imania_global_price_pj';
	const OPTION_GLOBAL_DISC_PF  = 'imania_global_discount_pf';
	const OPTION_GLOBAL_DISC_PJ  = 'imania_global_discount_pj';
	const OPTION_PRIORITY        = 'imania_pricing_priority';

	const REDIRECT_QUERY_KEY     = 'imania_redirect_to';
}
