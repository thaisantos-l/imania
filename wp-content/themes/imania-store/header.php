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
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">

	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div id="page" class="site">
	<a class="skip-link screen-reader-text" href="#primary"><?php esc_html_e( 'Skip to content', 'imania-store' ); ?></a>

	<header id="masthead" class="site-header imania-header">
		<div class="imania-header-topbar">
			<p>
				<?php esc_html_e( 'Sua primeira compra com', 'imania-store' ); ?>
				<strong><?php esc_html_e( '10% off com cupom 1COMPRA', 'imania-store' ); ?></strong>
			</p>
		</div>
		<div class="container">
			<div class="row align-items-center g-3 imania-header-main">
				<div class="col-7 col-lg-2">
					<div class="site-branding imania-branding">
						<?php
						the_custom_logo();
						if ( ! has_custom_logo() ) :
							?>
							<a class="imania-branding__name" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a>
							<?php
						endif;
						?>
					</div>
				</div>

				<div class="col-5 d-lg-none text-end">
					<button class="imania-menu-toggle" data-imania-menu-toggle aria-controls="primary-menu" aria-expanded="false">
						<span></span><span></span><span></span>
					</button>
				</div>

				<div class="col-12 col-lg-8">
					<nav id="site-navigation" class="main-navigation imania-navigation" data-imania-menu>
						<?php
						wp_nav_menu(
							array(
								'theme_location' => 'menu-1',
								'menu_id'        => 'primary-menu',
								'container'      => false,
							)
						);
						?>
					</nav>
				</div>

				<div class="col-lg-2 d-none d-lg-flex justify-content-end">
					<?php
					$my_account_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : wp_login_url();
					$cart_url       = function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/' );
					$search_url     = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
					$favorites_url  = home_url( '/' );
					$header_icon_uri = trailingslashit( get_template_directory_uri() ) . 'assets/img/header/';
					?>
					<div class="imania-header-actions">
						<a href="<?php echo esc_url( $search_url ); ?>" aria-label="<?php esc_attr_e( 'Buscar produtos', 'imania-store' ); ?>">
							<img src="<?php echo esc_url( $header_icon_uri . 'busca.png' ); ?>" alt="" aria-hidden="true" />
						</a>
						<a href="<?php echo esc_url( $favorites_url ); ?>" aria-label="<?php esc_attr_e( 'Favoritos', 'imania-store' ); ?>">
							<img src="<?php echo esc_url( $header_icon_uri . 'favorito.png' ); ?>" alt="" aria-hidden="true" />
						</a>
						<a href="<?php echo esc_url( $cart_url ); ?>" aria-label="<?php esc_attr_e( 'Carrinho', 'imania-store' ); ?>">
							<img src="<?php echo esc_url( $header_icon_uri . 'carrinho.png' ); ?>" alt="" aria-hidden="true" />
						</a>
						<a href="<?php echo esc_url( $my_account_url ); ?>" aria-label="<?php esc_attr_e( 'Minha conta', 'imania-store' ); ?>">
							<img src="<?php echo esc_url( $header_icon_uri . 'conta.png' ); ?>" alt="" aria-hidden="true" />
						</a>
					</div>
				</div>
			</div>
		</div>
	</header><!-- #masthead -->
