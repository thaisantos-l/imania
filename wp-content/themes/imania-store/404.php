<?php
/**
 * The template for displaying 404 pages.
 *
 * @package Imania_Store
 */

defined('ABSPATH') || exit;

status_header(404);
nocache_headers();

$shop_url = function_exists('wc_get_page_permalink')
	? wc_get_page_permalink('shop')
	: home_url('/');

get_header();
?>

<main id="primary" class="site-main imania-not-found">
	<section class="imania-not-found__section" aria-labelledby="imania-not-found-title">
		<div class="container imania-not-found__container">
			<div class="imania-not-found__visual" aria-hidden="true">
				<span class="imania-not-found__shape imania-not-found__shape--yellow"></span>
				<span class="imania-not-found__shape imania-not-found__shape--blue"></span>
				<span class="imania-not-found__shape imania-not-found__shape--red"></span>
				<p class="imania-not-found__code">404</p>
			</div>

			<div class="imania-not-found__content">
				<p class="imania-not-found__eyebrow"><?php esc_html_e('Ops! Algo saiu do lugar.', 'imania-store'); ?></p>
				<h1 id="imania-not-found-title"><?php esc_html_e('Página não encontrada', 'imania-store'); ?></h1>
				<p class="imania-not-found__description">
					<?php esc_html_e('O endereço que você acessou não existe ou foi movido. Busque o produto que procura ou continue navegando pela nossa loja.', 'imania-store'); ?>
				</p>

				<form class="imania-not-found__search" role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>">
					<label class="screen-reader-text" for="imania-not-found-search">
						<?php esc_html_e('Buscar produtos', 'imania-store'); ?>
					</label>
					<div class="imania-not-found__search-field">
						<svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">
							<circle cx="11" cy="11" r="6.75" stroke="currentColor" stroke-width="1.5"/>
							<path d="m16 16 4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
						</svg>
						<input
							id="imania-not-found-search"
							type="search"
							name="s"
							placeholder="<?php esc_attr_e('O que você está procurando?', 'imania-store'); ?>"
							required
						/>
						<input type="hidden" name="post_type" value="product"/>
					</div>
					<button type="submit"><?php esc_html_e('Buscar', 'imania-store'); ?></button>
				</form>

				<div class="imania-not-found__separator" aria-hidden="true">
					<span></span>
					<small><?php esc_html_e('ou', 'imania-store'); ?></small>
					<span></span>
				</div>

				<a class="imania-not-found__shop-link" href="<?php echo esc_url($shop_url); ?>">
					<span><?php esc_html_e('Ver nossa loja', 'imania-store'); ?></span>
					<svg width="19" height="19" viewBox="0 0 24 24" fill="none" aria-hidden="true">
						<path d="M5 12h14M14 7l5 5-5 5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>
				</a>
			</div>
		</div>
	</section>
</main>

<?php
get_footer();
