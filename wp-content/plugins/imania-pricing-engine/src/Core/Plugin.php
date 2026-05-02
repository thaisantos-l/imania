<?php

namespace Imania\PricingEngine\Core;

use Imania\PricingEngine\Domain\Customer\CustomerTypeResolver;
use Imania\PricingEngine\Domain\Customer\DocumentRepository;
use Imania\PricingEngine\Domain\Customer\DocumentValidator;
use Imania\PricingEngine\Domain\Pricing\PriceCalculator;
use Imania\PricingEngine\Domain\Pricing\PricingRuleResolver;
use Imania\PricingEngine\Infrastructure\Admin\CategoryPricingFields;
use Imania\PricingEngine\Infrastructure\Admin\ProductPricingFields;
use Imania\PricingEngine\Infrastructure\Admin\SettingsPage;
use Imania\PricingEngine\Infrastructure\Auth\LoginRedirectHandler;
use Imania\PricingEngine\Infrastructure\Auth\RegistrationHandler;
use Imania\PricingEngine\Infrastructure\Woo\CartPriceAdjuster;
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

		$request_cache       = new RequestCache();
		$customer_resolver   = new CustomerTypeResolver();
		$document_validator  = new DocumentValidator();
		$document_repository = new DocumentRepository();
		$rule_resolver       = new PricingRuleResolver();
		$price_calculator    = new PriceCalculator( $rule_resolver, $request_cache );

		$registration_handler = new RegistrationHandler( $customer_resolver, $document_validator, $document_repository );
		$registration_handler->register();

		$redirect_handler = new LoginRedirectHandler();
		$redirect_handler->register();

		$price_hooks = new PriceHooks( $customer_resolver, $price_calculator );
		$price_hooks->register();

		$cart_adjuster = new CartPriceAdjuster( $customer_resolver, $price_calculator );
		$cart_adjuster->register();

		$admin_fields = new ProductPricingFields();
		$admin_fields->register();

		$category_fields = new CategoryPricingFields();
		$category_fields->register();

		$settings_page = new SettingsPage();
		$settings_page->register();
	}
}
