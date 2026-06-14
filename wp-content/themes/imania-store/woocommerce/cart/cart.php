<?php
/**
 * Cart Page
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/cart/cart.php.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 10.1.0
 */

defined('ABSPATH') || exit;

do_action('woocommerce_before_cart');
?>

<form class="woocommerce-cart-form imania-cart-page" action="<?php echo esc_url(wc_get_cart_url()); ?>" method="post">
	<?php do_action('woocommerce_before_cart_table'); ?>

	<div class="imania-cart-page__notice-area" data-imania-cart-notices aria-live="polite"></div>

	<div class="imania-cart-page__layout">
		<section class="imania-cart-page__items" aria-label="<?php esc_attr_e('Produtos no carrinho', 'imania-store'); ?>">
			<label class="imania-cart-page__select-all">
				<input type="checkbox" checked data-imania-cart-select-all>
				<span><?php esc_html_e('Todos os produtos', 'imania-store'); ?></span>
			</label>

			<div class="imania-cart-page__list woocommerce-cart-form__contents">
				<?php do_action('woocommerce_before_cart_contents'); ?>

				<?php
				foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
					$_product = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
					if (!($_product instanceof WC_Product)) {
						continue;
					}

					$product_id = apply_filters('woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key);
					$product_name = apply_filters('woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key);

					if (!$_product || !$_product->exists() || $cart_item['quantity'] <= 0 || !apply_filters('woocommerce_cart_item_visible', true, $cart_item, $cart_item_key)) {
						continue;
					}

					$product_permalink = apply_filters('woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink($cart_item) : '', $cart_item, $cart_item_key);
					$thumbnail = apply_filters('woocommerce_cart_item_thumbnail', $_product->get_image('woocommerce_thumbnail'), $cart_item, $cart_item_key);
					$quantity = (int) $cart_item['quantity'];

					if ($_product->is_sold_individually()) {
						$min_quantity = 1;
						$max_quantity = 1;
					} else {
						$min_quantity = 0;
						$max_quantity = $_product->get_max_purchase_quantity();
					}

					$product_quantity = woocommerce_quantity_input(
						array(
							'input_name' => "cart[{$cart_item_key}][qty]",
							'input_value' => $cart_item['quantity'],
							'max_value' => $max_quantity,
							'min_value' => $min_quantity,
							'product_name' => $product_name,
						),
						$_product,
						false
					);
					?>
					<article class="imania-cart-page__item <?php echo esc_attr(apply_filters('woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key)); ?>" data-imania-cart-row data-cart-item-key="<?php echo esc_attr($cart_item_key); ?>">
						<div class="imania-cart-page__item-head">
							<label class="imania-cart-page__item-check">
								<input type="checkbox" checked data-imania-cart-item-check>
								<span><?php echo esc_html(wp_strip_all_tags($product_name)); ?></span>
							</label>

							<?php
							echo apply_filters(
								'woocommerce_cart_item_remove_link',
								sprintf(
									'<a role="button" href="%s" class="imania-cart-page__remove remove" aria-label="%s" data-cart-item-key="%s" data-product_id="%s" data-product_sku="%s"><img src="%s" alt="" aria-hidden="true" /></a>',
									esc_url(wc_get_cart_remove_url($cart_item_key)),
									esc_attr(sprintf(__('Remove %s from cart', 'woocommerce'), wp_strip_all_tags($product_name))),
									esc_attr($cart_item_key),
									esc_attr($product_id),
									esc_attr($_product->get_sku()),
									esc_url(get_template_directory_uri() . '/assets/img/lixeira-carrinho.png')
								),
								$cart_item_key
							); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							?>
						</div>

						<div class="imania-cart-page__item-body">
							<a class="imania-cart-page__thumb" href="<?php echo esc_url($product_permalink ?: wc_get_cart_url()); ?>" aria-label="<?php echo esc_attr(wp_strip_all_tags($product_name)); ?>">
								<?php echo wp_kses_post($thumbnail); ?>
							</a>

							<div class="imania-cart-page__freight">
								<span><?php esc_html_e('Frete', 'imania-store'); ?></span>
								<strong><?php echo wp_kses_post(wc_price(10)); ?></strong>
							</div>

							<div class="imania-cart-page__details">
								<h2 class="imania-cart-page__product-title">
									<?php if ($product_permalink) : ?>
										<a href="<?php echo esc_url($product_permalink); ?>"><?php echo wp_kses_post($product_name); ?></a>
									<?php else : ?>
										<?php echo wp_kses_post($product_name); ?>
									<?php endif; ?>
								</h2>

								<?php
								do_action('woocommerce_after_cart_item_name', $cart_item, $cart_item_key);
								echo wc_get_formatted_cart_item_data($cart_item); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

								if ($_product->backorders_require_notification() && $_product->is_on_backorder($cart_item['quantity'])) {
									echo wp_kses_post(apply_filters('woocommerce_cart_item_backorder_notification', '<p class="backorder_notification">' . esc_html__('Available on backorder', 'woocommerce') . '</p>', $product_id));
								}
								?>

								<div class="imania-cart-page__buy-more">
									<span><?php esc_html_e('Adicionar mais:', 'imania-store'); ?></span>
									<?php echo apply_filters('woocommerce_cart_item_quantity', $product_quantity, $cart_item_key, $cart_item); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								</div>

								<div class="imania-cart-page__price" data-title="<?php esc_attr_e('Preco', 'imania-store'); ?>">
									<?php echo apply_filters('woocommerce_cart_item_price', WC()->cart->get_product_price($_product), $cart_item, $cart_item_key); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								</div>
							</div>
						</div>
					</article>
					<?php
				}
				?>

				<?php do_action('woocommerce_cart_contents'); ?>
				<?php do_action('woocommerce_after_cart_contents'); ?>
			</div>
		</section>

		<aside class="imania-cart-page__summary" aria-label="<?php esc_attr_e('Resumo da compra', 'imania-store'); ?>">
			<h2><?php esc_html_e('Resumo da compra', 'imania-store'); ?></h2>

			<?php if (wc_coupons_enabled()) : ?>
				<div class="imania-cart-page__coupon coupon">
					<label for="coupon_code"><?php esc_html_e('Adicionar Cupom', 'imania-store'); ?></label>
					<div class="imania-cart-page__coupon-row">
						<input type="text" name="coupon_code" class="input-text" id="coupon_code" value="" autocomplete="off" />
						<button type="submit" class="screen-reader-text" name="apply_coupon" value="<?php esc_attr_e('Apply coupon', 'woocommerce'); ?>">
							<?php esc_html_e('Apply coupon', 'woocommerce'); ?>
						</button>
					</div>
					<?php do_action('woocommerce_cart_coupon'); ?>
				</div>
			<?php endif; ?>

			<div class="imania-cart-page__totals" data-imania-cart-summary>
				<?php
				if (function_exists('imania_store_render_cart_page_summary_totals')) {
					echo imania_store_render_cart_page_summary_totals(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				}
				?>
			</div>

			<div class="imania-cart-page__actions">
				<button type="submit" class="imania-cart-page__update button" name="update_cart" value="<?php esc_attr_e('Update cart', 'woocommerce'); ?>" disabled>
					<?php esc_html_e('Atualizar carrinho', 'imania-store'); ?>
				</button>
				<a class="imania-btn imania-btn--primary imania-cart-page__checkout checkout-button button alt wc-forward" href="<?php echo esc_url(wc_get_checkout_url()); ?>">
					<?php esc_html_e('COMPRAR', 'imania-store'); ?>
				</a>
			</div>

			<?php do_action('woocommerce_cart_actions'); ?>
			<?php wp_nonce_field('woocommerce-cart', 'woocommerce-cart-nonce'); ?>
		</aside>
	</div>

	<?php do_action('woocommerce_after_cart_table'); ?>
</form>

<?php do_action('woocommerce_before_cart_collaterals'); ?>

<div class="cart-collaterals imania-cart-page__collaterals">
	<?php
	$imania_cart_totals_priority = has_action('woocommerce_cart_collaterals', 'woocommerce_cart_totals');
	if (false !== $imania_cart_totals_priority) {
		remove_action('woocommerce_cart_collaterals', 'woocommerce_cart_totals', $imania_cart_totals_priority);
	}

	do_action('woocommerce_cart_collaterals');

	if (false !== $imania_cart_totals_priority) {
		add_action('woocommerce_cart_collaterals', 'woocommerce_cart_totals', $imania_cart_totals_priority);
	}
	?>
</div>

<?php do_action('woocommerce_after_cart'); ?>
