<?php

namespace Imania\PricingEngine\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Bootstrap {

	/**
	 * @var Plugin|null
	 */
	private static $plugin = null;

	public static function init() {
		if ( null !== self::$plugin ) {
			return;
		}

		self::$plugin = new Plugin();
		self::$plugin->boot();
	}
}
