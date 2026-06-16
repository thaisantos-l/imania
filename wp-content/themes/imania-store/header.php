<?php
/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package Imania_Store
 */

?>
<!doctype html>
<html <?php language_attributes(); ?>>

<head>
	<meta charset="<?php bloginfo('charset'); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">

	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
	<?php wp_body_open(); ?>
	<div id="page" class="site">
		<a class="skip-link screen-reader-text"
			href="#primary"><?php esc_html_e('Skip to content', 'imania-store'); ?></a>

		<header id="masthead" class="site-header imania-header">
			<div class="imania-header-topbar">
				<p>
					<?php esc_html_e('Sua primeira compra com', 'imania-store'); ?>
					<strong><?php esc_html_e('10% off com cupom 1COMPRA', 'imania-store'); ?></strong>
				</p>
			</div>
			<div class="container">
				<div class="row align-items-center g-3 imania-header-main">
					<div class="col-7 col-lg-1">
						<div class="site-branding imania-branding">
							<?php
							the_custom_logo();
							if (!has_custom_logo()):
								?>
								<a class="imania-branding__name" href="<?php echo esc_url(home_url('/')); ?>"
									rel="home"><?php bloginfo('name'); ?></a>
								<?php
							endif;
							?>
						</div>
					</div>

					<div class="col-5 d-lg-none text-end">
						<button class="imania-menu-toggle" data-imania-menu-toggle aria-controls="primary-menu"
							aria-expanded="false">
							<span></span><span></span><span></span>
						</button>
					</div>

					<div class="col-12 col-lg-9">
						<nav id="site-navigation" class="main-navigation imania-navigation" data-imania-menu>
							<?php
							wp_nav_menu(
								array(
									'theme_location' => 'menu-1',
									'menu_id' => 'primary-menu',
									'container' => false,
								)
							);
							?>
						</nav>
					</div>

					<div class="col-lg-2 d-none d-lg-flex justify-content-end">
						<?php
						$my_account_url = function_exists('imania_store_get_my_account_url') ? imania_store_get_my_account_url() : home_url('/conta/');
						$cart_url = function_exists('wc_get_cart_url') ? wc_get_cart_url() : home_url('/');
						$search_url = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/');
						$favorites_url = function_exists('wc_get_account_endpoint_url') ? wc_get_account_endpoint_url('wishlist') : home_url('/');
						$header_icon_uri = trailingslashit(get_template_directory_uri()) . 'assets/img/header/';
						?>
						<div class="imania-header-actions">
							<a href="<?php echo esc_url($search_url); ?>"
								aria-label="<?php esc_attr_e('Buscar produtos', 'imania-store'); ?>">
								<img src="<?php echo esc_url($header_icon_uri . 'busca.png'); ?>" alt=""
									aria-hidden="true" />
							</a>
							<?php if (is_user_logged_in()): ?>
								<a href="<?php echo esc_url($favorites_url); ?>"
									aria-label="<?php esc_attr_e('Favoritos', 'imania-store'); ?>"
									data-imania-wishlist-link>
									<img src="<?php echo esc_url($header_icon_uri . 'favorito.png'); ?>" alt=""
										aria-hidden="true" />
									<?php
									$wishlist_count = count(imania_store_get_wishlist_ids());
									if ($wishlist_count > 0):
										?>
										<span class="imania-cart-count"
											data-imania-wishlist-count><?php echo esc_html($wishlist_count); ?></span>
									<?php endif; ?>
								</a>
							<?php else: ?>
								<button type="button" class="imania-header-actions__btn" data-imania-login-required
									aria-label="<?php esc_attr_e('Favoritos', 'imania-store'); ?>">
									<img src="<?php echo esc_url($header_icon_uri . 'favorito.png'); ?>" alt=""
										aria-hidden="true" />
								</button>
							<?php endif; ?>
							<button type="button" class="imania-header-actions__btn" data-imania-cart-drawer-trigger aria-controls="imania-cart-drawer" aria-expanded="false"
								aria-label="<?php esc_attr_e('Abrir carrinho', 'imania-store'); ?>">
								<img src="<?php echo esc_url($header_icon_uri . 'carrinho.png'); ?>" alt=""
									aria-hidden="true" />
								<?php if (function_exists('WC') && WC()->cart instanceof WC_Cart && WC()->cart->get_cart_contents_count() > 0): ?>
									<span class="imania-cart-count" data-imania-cart-count><?php echo esc_html(WC()->cart->get_cart_contents_count()); ?></span>
								<?php endif; ?>
							</button>
							<a href="<?php echo esc_url($my_account_url); ?>"
								aria-label="<?php esc_attr_e('Minha conta', 'imania-store'); ?>">
								<img src="<?php echo esc_url($header_icon_uri . 'conta.png'); ?>" alt=""
									aria-hidden="true" />
							</a>
						</div>
					</div>
				</div>
			</div>
		</header><!-- #masthead -->
