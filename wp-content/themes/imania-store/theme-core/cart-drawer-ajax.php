<?php
/**
 * AJAX handlers and render helpers for the cart drawer.
 *
 * @package Imania_Store
 */

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Render cart drawer content based on the current WooCommerce cart.
 *
 * @return string
 */
function imania_store_render_cart_drawer_content()
{
	if (!function_exists('WC') || !WC()->cart instanceof WC_Cart) {
		ob_start();
		?>
		<div class="imania-cart-drawer__empty" data-imania-cart-empty>
			<p><?php esc_html_e('Carrinho indisponivel no momento.', 'imania-store'); ?></p>
		</div>
		<?php
		return ob_get_clean();
	}

	$cart = WC()->cart;
	$cart_items = $cart->get_cart();
	$shop_url = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/');
	$cart_url = function_exists('wc_get_cart_url') ? wc_get_cart_url() : home_url('/carrinho/');

	ob_start();
	if (empty($cart_items)) :
		?>
		<div class="imania-cart-drawer__empty" data-imania-cart-empty>
			<span class="imania-cart-drawer__empty-icon" aria-hidden="true">🛒</span>
			<h3><?php esc_html_e('Carrinho vazio.', 'imania-store'); ?></h3>
			<p><?php esc_html_e('Escolha seus produtos favoritos e volte aqui para revisar sua compra.', 'imania-store'); ?></p>
		</div>
		<div class="imania-cart-drawer__footer">
			<a class="imania-btn imania-btn--primary imania-cart-drawer__cta" href="<?php echo esc_url($shop_url); ?>">
				<?php esc_html_e('Continuar comprando', 'imania-store'); ?>
			</a>
		</div>
		<?php
		return ob_get_clean();
	endif;
	?>
	<div class="imania-cart-drawer__content">
		<div class="imania-cart-drawer__items" data-imania-cart-items>
			<?php foreach ($cart_items as $cart_item_key => $cart_item) : ?>
				<?php
				$product = isset($cart_item['data']) && $cart_item['data'] instanceof WC_Product ? $cart_item['data'] : null;
				if (!$product || !$product->exists() || (int) ($cart_item['quantity'] ?? 0) <= 0) {
					continue;
				}

				$product_name = $product->get_name();
				$quantity = (int) $cart_item['quantity'];
				$product_permalink = $product->is_visible() ? $product->get_permalink($cart_item) : '';
				$thumbnail = $product->get_image('woocommerce_thumbnail', array('loading' => 'lazy', 'decoding' => 'async'));
				$line_subtotal = isset($cart_item['line_subtotal']) ? (float) $cart_item['line_subtotal'] : 0.0;
				$line_total = isset($cart_item['line_total']) ? (float) $cart_item['line_total'] : $line_subtotal;
				$unit_price = $quantity > 0 ? $line_total / $quantity : (float) $product->get_price();
				?>
			<article class="imania-cart-drawer__item" data-cart-item-key="<?php echo esc_attr($cart_item_key); ?>">
				<a class="imania-cart-drawer__thumb" href="<?php echo esc_url($product_permalink ?: $cart_url); ?>" aria-label="<?php echo esc_attr($product_name); ?>">
					<?php echo wp_kses_post($thumbnail); ?>
				</a>
				<div class="imania-cart-drawer__item-content">
					<h3 class="imania-cart-drawer__item-title">
						<?php if ($product_permalink) : ?>
							<a href="<?php echo esc_url($product_permalink); ?>"><?php echo esc_html($product_name); ?></a>
						<?php else : ?>
							<?php echo esc_html($product_name); ?>
						<?php endif; ?>
					</h3>
					<div class="imania-cart-drawer__item-meta">
						<span><?php esc_html_e('Quantidade:', 'imania-store'); ?> <?php echo esc_html($quantity); ?></span>
						<strong><?php echo wp_kses_post(wc_price($unit_price)); ?></strong>
					</div>
				</div>
			</article>
			<?php endforeach; ?>
		</div>
		<div class="imania-cart-drawer__bottom">
			<div class="imania-cart-drawer__summary" aria-label="<?php esc_attr_e('Resumo do carrinho', 'imania-store'); ?>">
				<div class="imania-cart-drawer__summary-row">
					<span><?php esc_html_e('Produtos', 'imania-store'); ?></span>
					<strong><?php echo wp_kses_post($cart->get_cart_subtotal()); ?></strong>
				</div>
				<div class="imania-cart-drawer__summary-row imania-cart-drawer__summary-row--total">
					<span><?php esc_html_e('Total', 'imania-store'); ?></span>
					<strong><?php echo wp_kses_post($cart->get_total()); ?></strong>
				</div>
			</div>
			<div class="imania-cart-drawer__footer">
				<a class="imania-btn imania-btn--outline imania-cart-drawer__continue" href="<?php echo esc_url($shop_url); ?>">
					<?php esc_html_e('Continuar comprando', 'imania-store'); ?>
				</a>
				<a class="imania-btn imania-btn--primary imania-cart-drawer__cta" href="<?php echo esc_url($cart_url); ?>">
					<?php esc_html_e('Ir para carrinho', 'imania-store'); ?>
				</a>
			</div>
		</div>
	</div>
	<?php
	return ob_get_clean();
}

/**
 * Return current cart drawer HTML via AJAX.
 */
function imania_store_handle_cart_drawer_ajax()
{
	if ('POST' !== strtoupper((string) $_SERVER['REQUEST_METHOD'])) {
		imania_store_send_account_json_error(__('Metodo invalido.', 'imania-store'), 405, 'invalid_method');
	}

	$is_valid_nonce = check_ajax_referer('imania_cart_drawer_nonce', 'nonce', false);
	if (false === $is_valid_nonce) {
		imania_store_send_account_json_error(__('Falha de seguranca. Atualize a pagina e tente novamente.', 'imania-store'), 403, 'invalid_nonce');
	}

	if (!function_exists('WC') || !WC()->cart instanceof WC_Cart) {
		imania_store_send_account_json_error(__('Carrinho indisponivel no momento.', 'imania-store'), 500, 'cart_unavailable');
	}

	WC()->cart->calculate_totals();

	imania_store_send_account_json_success(
		array(
			'html' => imania_store_render_cart_drawer_content(),
			'count' => WC()->cart->get_cart_contents_count(),
			'cartUrl' => function_exists('wc_get_cart_url') ? wc_get_cart_url() : home_url('/carrinho/'),
		)
	);
}
add_action('wp_ajax_imania_cart_drawer', 'imania_store_handle_cart_drawer_ajax');
add_action('wp_ajax_nopriv_imania_cart_drawer', 'imania_store_handle_cart_drawer_ajax');
