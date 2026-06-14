<?php
/**
 * Imania Store functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Imania_Store
 */

if (!defined('_S_VERSION')) {
	// Replace the version number of the theme on each release.
	define('_S_VERSION', '1.0.0');
}

$imania_theme_core_dir = get_template_directory() . '/theme-core';
$imania_theme_core_files = array(
	'theme-setup.php',
	'assets-enqueue.php',
	'account-helpers.php',
	'customer-identity.php',
	'catalog-rules.php',
	'auth-identity.php',
	'wishlist-domain.php',
	'account-endpoints.php',
	'auth-ajax.php',
	'single-product-ajax.php',
	'wishlist-ajax.php',
	'cart-drawer-ajax.php',
	'cart-page-ajax.php',
	'checkout.php',
);

foreach ($imania_theme_core_files as $imania_theme_core_file) {
	require_once $imania_theme_core_dir . '/' . $imania_theme_core_file;
}

$imania_inc_dir = get_template_directory() . '/inc';
$imania_inc_files = array(
	'custom-header.php',
	'template-tags.php',
	'template-functions.php',
	'home.php',
	'customizer.php',
);

foreach ($imania_inc_files as $imania_inc_file) {
	require_once $imania_inc_dir . '/' . $imania_inc_file;
}

if (defined('JETPACK__VERSION')) {
	require_once $imania_inc_dir . '/jetpack.php';
}

if (class_exists('WooCommerce')) {
	require_once $imania_inc_dir . '/woocommerce.php';
}
