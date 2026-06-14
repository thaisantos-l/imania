<?php
/**
 * Custom Orders endpoint content.
 *
 * @package Imania_Store
 */

defined('ABSPATH') || exit;

do_action('woocommerce_before_account_orders', $has_orders);
?>

<section class="imania-account-orders" aria-labelledby="imania-account-orders-title">
	<h2 id="imania-account-orders-title"><?php esc_html_e('Compras', 'imania-store'); ?></h2>

	<?php if ($has_orders) : ?>
		<div class="imania-orders-list" data-imania-orders-root>
			<?php foreach ($customer_orders->orders as $customer_order_id) : ?>
				<?php
				$order = wc_get_order($customer_order_id);
				if (!$order instanceof WC_Order) {
					continue;
				}

				$order_number = $order->get_order_number();
				$order_status = wc_get_order_status_name($order->get_status());
				$order_date = $order->get_date_created() ? wc_format_datetime($order->get_date_created()) : '';
				$payment_method = $order->get_payment_method_title();
				if ('' === $payment_method) {
					$payment_method = __('Não informado', 'imania-store');
				}

				$order_items = $order->get_items('line_item');
				$panel_id = 'imania-order-panel-' . absint($order->get_id());
				?>
				<article class="imania-order-item" data-imania-order-item>
					<button
						type="button"
						class="imania-order-item__trigger"
						data-imania-order-toggle
						aria-expanded="false"
						aria-controls="<?php echo esc_attr($panel_id); ?>"
					>
						<span>
							<?php
							printf(
								/* translators: 1: order number, 2: order date, 3: order status */
								esc_html__('Pedido #%1$s | %2$s | %3$s', 'imania-store'),
								esc_html($order_number),
								esc_html($order_date),
								esc_html($order_status)
							);
							?>
						</span>
					</button>

					<div id="<?php echo esc_attr($panel_id); ?>" class="imania-order-item__panel" data-imania-order-panel hidden>
						<?php if (!empty($order_items)) : ?>
							<div class="imania-order-item__table-head">
								<span><?php esc_html_e('Nome do produto', 'imania-store'); ?></span>
								<span><?php esc_html_e('Preço', 'imania-store'); ?></span>
								<span><?php esc_html_e('Quantidade', 'imania-store'); ?></span>
								<span><?php esc_html_e('Valor', 'imania-store'); ?></span>
								<span><?php esc_html_e('Forma de pagamento', 'imania-store'); ?></span>
							</div>

							<div class="imania-order-item__table-body">
								<?php foreach ($order_items as $item) : ?>
									<?php
									$product = $item->get_product();
									$product_name = $item->get_name();
									$product_url = $product instanceof WC_Product ? get_permalink($product->get_id()) : '';
									$quantity = (int) $item->get_quantity();
									$line_total = (float) $item->get_total();
									$unit_price = $quantity > 0 ? $line_total / $quantity : $line_total;
									?>
									<div class="imania-order-item__row">
										<div class="imania-order-item__cell">
											<?php if ('' !== $product_url) : ?>
												<a href="<?php echo esc_url($product_url); ?>" target="_blank" rel="noopener noreferrer">
													<?php echo esc_html($product_name); ?>
												</a>
											<?php else : ?>
												<span><?php echo esc_html($product_name); ?></span>
											<?php endif; ?>
										</div>
										<div class="imania-order-item__cell"><?php echo wp_kses_post(wc_price($unit_price, array('currency' => $order->get_currency()))); ?></div>
										<div class="imania-order-item__cell"><?php echo esc_html((string) $quantity); ?></div>
										<div class="imania-order-item__cell"><?php echo wp_kses_post(wc_price($line_total, array('currency' => $order->get_currency()))); ?></div>
										<div class="imania-order-item__cell"><?php echo esc_html($payment_method); ?></div>
									</div>
								<?php endforeach; ?>
							</div>
						<?php else : ?>
							<p class="imania-order-item__empty"><?php esc_html_e('Sem itens neste pedido.', 'imania-store'); ?></p>
						<?php endif; ?>
					</div>
				</article>
			<?php endforeach; ?>
		</div>

		<?php if (1 < $customer_orders->max_num_pages) : ?>
			<div class="imania-orders-pagination">
				<?php if (1 !== $current_page) : ?>
					<a href="<?php echo esc_url(wc_get_endpoint_url('orders', $current_page - 1)); ?>">
						<?php esc_html_e('Anterior', 'imania-store'); ?>
					</a>
				<?php endif; ?>

				<?php if (intval($customer_orders->max_num_pages) !== $current_page) : ?>
					<a href="<?php echo esc_url(wc_get_endpoint_url('orders', $current_page + 1)); ?>">
						<?php esc_html_e('Próximo', 'imania-store'); ?>
					</a>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	<?php else : ?>
		<div class="imania-order-item__empty-box">
			<p><?php esc_html_e('Você ainda não possui compras registradas.', 'imania-store'); ?></p>
		</div>
	<?php endif; ?>
</section>

<?php do_action('woocommerce_after_account_orders', $has_orders); ?>
