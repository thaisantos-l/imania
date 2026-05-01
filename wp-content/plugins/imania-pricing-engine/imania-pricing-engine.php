<?php
/**
 * Plugin Name: Imania Pricing Engine
 * Description: Regra de precificacao PF/PJ para WooCommerce com seguranca e compatibilidade com plugins de frete/pagamento.
 * Version: 0.1.0
 * Author: Imania
 * Text Domain: imania-pricing-engine
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'IMANIA_PRICING_ENGINE_VERSION', '0.1.0' );
define( 'IMANIA_PRICING_ENGINE_FILE', __FILE__ );
define( 'IMANIA_PRICING_ENGINE_PATH', plugin_dir_path( __FILE__ ) );
define( 'IMANIA_PRICING_ENGINE_URL', plugin_dir_url( __FILE__ ) );

spl_autoload_register(
	static function ( $class ) {
		$prefix   = 'Imania\\PricingEngine\\';
		$base_dir = IMANIA_PRICING_ENGINE_PATH . 'src/';

		$length = strlen( $prefix );
		if ( 0 !== strncmp( $prefix, $class, $length ) ) {
			return;
		}

		$relative_class = substr( $class, $length );
		$file           = $base_dir . str_replace( '\\', '/', $relative_class ) . '.php';

		if ( file_exists( $file ) ) {
			require $file;
		}
	}
);

add_action(
	'plugins_loaded',
	static function () {
		\Imania\PricingEngine\Core\Bootstrap::init();
	},
	20
);
