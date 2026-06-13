<?php
/**
 * Cart drawer modal.
 *
 * @package Imania_Store
 */

$initial_count = function_exists('WC') && WC()->cart instanceof WC_Cart ? WC()->cart->get_cart_contents_count() : 0;
?>
<div id="imania-cart-drawer" class="imania-cart-drawer" data-imania-cart-drawer hidden aria-hidden="true">
	<div class="imania-cart-drawer__overlay" data-imania-cart-drawer-close></div>
	<aside class="imania-cart-drawer__panel" role="dialog" aria-modal="true" aria-labelledby="imania-cart-drawer-title" tabindex="-1">
		<header class="imania-cart-drawer__header">
			<div>
				<p class="imania-cart-drawer__eyebrow"><?php esc_html_e('Resumo da compra', 'imania-store'); ?></p>
				<h2 id="imania-cart-drawer-title"><?php esc_html_e('Meu carrinho', 'imania-store'); ?></h2>
			</div>
			<button type="button" class="imania-cart-drawer__close" data-imania-cart-drawer-close aria-label="<?php esc_attr_e('Fechar carrinho', 'imania-store'); ?>">&times;</button>
		</header>

		<div class="imania-cart-drawer__body" data-imania-cart-drawer-content aria-live="polite" aria-busy="false">
			<?php
			if (function_exists('imania_store_render_cart_drawer_content')) {
				echo imania_store_render_cart_drawer_content(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
			?>
		</div>
	</aside>
</div>
