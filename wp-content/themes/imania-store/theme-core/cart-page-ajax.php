<?php
/**
 * AJAX handlers and render helpers for the cart page.
 *
 * @package Imania_Store
 */

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Render the custom cart page totals fragment.
 *
 * @return string
 */
function imania_store_render_cart_page_summary_totals()
{
	if (!function_exists('WC') || !WC()->cart instanceof WC_Cart) {
		return '';
	}

	ob_start();
	?>
	<div class="imania-cart-page__total-row cart-subtotal">
		<span><?php esc_html_e('Produtos', 'imania-store'); ?></span>
		<strong><?php wc_cart_totals_subtotal_html(); ?></strong>
	</div>

	<?php if (WC()->cart->needs_shipping() && WC()->cart->show_shipping()) : ?>
		<?php do_action('woocommerce_cart_totals_before_shipping'); ?>
		<div class="imania-cart-page__total-row shipping">
			<span><?php esc_html_e('Frete', 'imania-store'); ?></span>
			<strong>
				<?php
				if (method_exists(WC()->cart, 'get_cart_shipping_total')) {
					echo wp_kses_post(WC()->cart->get_cart_shipping_total());
				} else {
					echo wp_kses_post(wc_price(WC()->cart->get_shipping_total()));
				}
				?>
			</strong>
		</div>
		<?php do_action('woocommerce_cart_totals_after_shipping'); ?>
	<?php else : ?>
		<div class="imania-cart-page__total-row shipping">
			<span><?php esc_html_e('Frete', 'imania-store'); ?></span>
			<strong><?php echo wp_kses_post(wc_price(0)); ?></strong>
		</div>
	<?php endif; ?>

	<?php foreach (WC()->cart->get_coupons() as $code => $coupon) : ?>
		<div class="imania-cart-page__total-row cart-discount coupon-<?php echo esc_attr(sanitize_title($code)); ?>">
			<span><?php wc_cart_totals_coupon_label($coupon); ?></span>
			<strong><?php wc_cart_totals_coupon_html($coupon); ?></strong>
		</div>
	<?php endforeach; ?>

	<?php if (0 === count(WC()->cart->get_coupons())) : ?>
		<div class="imania-cart-page__total-row imania-cart-page__total-row--coupon">
			<span><?php esc_html_e('Cupom', 'imania-store'); ?></span>
			<strong><?php echo wp_kses_post(wc_price(0)); ?></strong>
		</div>
	<?php endif; ?>

	<?php foreach (WC()->cart->get_fees() as $fee) : ?>
		<div class="imania-cart-page__total-row fee">
			<span><?php echo esc_html($fee->name); ?></span>
			<strong><?php wc_cart_totals_fee_html($fee); ?></strong>
		</div>
	<?php endforeach; ?>

	<?php
	if (wc_tax_enabled() && !WC()->cart->display_prices_including_tax()) {
		if ('itemized' === get_option('woocommerce_tax_total_display')) {
			foreach (WC()->cart->get_tax_totals() as $code => $tax) {
				?>
				<div class="imania-cart-page__total-row tax-rate tax-rate-<?php echo esc_attr(sanitize_title($code)); ?>">
					<span><?php echo esc_html($tax->label); ?></span>
					<strong><?php echo wp_kses_post($tax->formatted_amount); ?></strong>
				</div>
				<?php
			}
		} else {
			?>
			<div class="imania-cart-page__total-row tax-total">
				<span><?php echo esc_html(WC()->countries->tax_or_vat()); ?></span>
				<strong><?php wc_cart_totals_taxes_total_html(); ?></strong>
			</div>
			<?php
		}
	}
	?>

	<?php do_action('woocommerce_cart_totals_before_order_total'); ?>

	<div class="imania-cart-page__total-row imania-cart-page__total-row--order order-total">
		<span><?php esc_html_e('Total', 'imania-store'); ?></span>
		<strong><?php wc_cart_totals_order_total_html(); ?></strong>
	</div>

	<?php do_action('woocommerce_cart_totals_after_order_total'); ?>
	<?php
	return ob_get_clean();
}

/**
 * Render the cart empty state used after AJAX removal.
 *
 * @return string
 */
function imania_store_render_cart_page_empty_state()
{
	$shop_url = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/');

	ob_start();
	?>
	<div class="imania-cart-page__empty imania-empty-state" data-imania-cart-page-empty>
		<h2><?php esc_html_e('Seu carrinho esta vazio.', 'imania-store'); ?></h2>
		<p><?php esc_html_e('Escolha seus produtos favoritos e volte aqui para revisar sua compra.', 'imania-store'); ?></p>
		<a class="imania-btn imania-btn--primary" href="<?php echo esc_url($shop_url); ?>">
			<?php esc_html_e('Continuar comprando', 'imania-store'); ?>
		</a>
	</div>
	<?php
	return ob_get_clean();
}

/**
 * Handle cart item removal from the custom cart page.
 */
function imania_store_handle_cart_page_remove_item_ajax()
{
	if ('POST' !== strtoupper((string) $_SERVER['REQUEST_METHOD'])) {
		imania_store_send_account_json_error(__('Metodo invalido.', 'imania-store'), 405, 'invalid_method');
	}

	$is_valid_nonce = check_ajax_referer('imania_cart_page_nonce', 'nonce', false);
	if (false === $is_valid_nonce) {
		imania_store_send_account_json_error(__('Falha de seguranca. Atualize a pagina e tente novamente.', 'imania-store'), 403, 'invalid_nonce');
	}

	if (!function_exists('WC') || !WC()->cart instanceof WC_Cart) {
		imania_store_send_account_json_error(__('Carrinho indisponivel no momento.', 'imania-store'), 500, 'cart_unavailable');
	}

	$cart_item_key = isset($_POST['cart_item_key']) ? wc_clean(wp_unslash($_POST['cart_item_key'])) : '';
	$cart = WC()->cart;
	$cart_item = $cart_item_key && isset($cart->cart_contents[$cart_item_key]) ? $cart->cart_contents[$cart_item_key] : null;

	if (!$cart_item) {
		imania_store_send_account_json_error(__('Item nao encontrado no carrinho.', 'imania-store'), 404, 'cart_item_not_found');
	}

	$product = isset($cart_item['data']) && $cart_item['data'] instanceof WC_Product ? $cart_item['data'] : null;
	$product_name = $product ? $product->get_name() : __('Produto', 'imania-store');

	if (false === $cart->remove_cart_item($cart_item_key)) {
		imania_store_send_account_json_error(__('Nao foi possivel remover este item agora.', 'imania-store'), 500, 'remove_failed');
	}

	$cart->calculate_totals();

	wc_clear_notices();
	wc_add_notice(
		sprintf(
			/* translators: %s: product name. */
			__('"%s" removido do carrinho.', 'imania-store'),
			wp_strip_all_tags($product_name)
		),
		'success'
	);

	imania_store_send_account_json_success(
		array(
			'cartItemKey' => $cart_item_key,
			'count' => $cart->get_cart_contents_count(),
			'isEmpty' => $cart->is_empty(),
			'noticeHtml' => wc_print_notices(true),
			'summaryHtml' => imania_store_render_cart_page_summary_totals(),
			'emptyHtml' => $cart->is_empty() ? imania_store_render_cart_page_empty_state() : '',
		)
	);
}
add_action('wp_ajax_imania_cart_page_remove_item', 'imania_store_handle_cart_page_remove_item_ajax');
add_action('wp_ajax_nopriv_imania_cart_page_remove_item', 'imania_store_handle_cart_page_remove_item_ajax');
