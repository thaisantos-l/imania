<?php

namespace Imania\PricingEngine\Core;

use Imania\PricingEngine\Domain\Customer\CustomerTypeResolver;
use Imania\PricingEngine\Domain\Customer\DocumentRepository;
use Imania\PricingEngine\Domain\Customer\DocumentValidator;
use Imania\PricingEngine\Domain\Order\FirstPurchaseChecker;
use Imania\PricingEngine\Infrastructure\Admin\BusinessRulesSettingsPage;
use Imania\PricingEngine\Infrastructure\Auth\LoginRedirectHandler;
use Imania\PricingEngine\Infrastructure\Auth\RegistrationHandler;
use Imania\PricingEngine\Infrastructure\Woo\CommercialRulesManager;
use Imania\PricingEngine\Infrastructure\Woo\PriceHooks;
use Imania\PricingEngine\Support\RequestCache;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Plugin {

	public function boot() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}

		$request_cache         = new RequestCache();
		$customer_resolver     = new CustomerTypeResolver();
		$document_validator    = new DocumentValidator();
		$document_repository   = new DocumentRepository();
		$first_purchase_checker = new FirstPurchaseChecker( $request_cache );

		$registration_handler = new RegistrationHandler( $customer_resolver, $document_validator, $document_repository );
		$registration_handler->register();

		$redirect_handler = new LoginRedirectHandler();
		$redirect_handler->register();

		$price_hooks = new PriceHooks();
		$price_hooks->register();

		$commercial_rules = new CommercialRulesManager( $customer_resolver, $first_purchase_checker );
		$commercial_rules->register();

		$settings_page = new BusinessRulesSettingsPage();
		$settings_page->register();
	}
}
