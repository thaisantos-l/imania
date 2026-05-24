<?php
/**
 * Custom simple product add-to-cart.
 *
 * @package Imania_Store
 */

defined('ABSPATH') || exit;

global $product;

if (!$product instanceof WC_Product || !$product->is_purchasable()) {
	return;
}

if ($product->is_in_stock()) :
	?>
	<form class="cart imania-single-product__cart" action="<?php echo esc_url(apply_filters('woocommerce_add_to_cart_form_action', $product->get_permalink())); ?>" method="post" enctype="multipart/form-data" data-imania-add-to-cart-form>
		<div class="imania-single-product__quantity">
			<?php
			woocommerce_quantity_input(
				array(
					'min_value' => $product->get_min_purchase_quantity(),
					'max_value' => $product->get_max_purchase_quantity(),
					'input_value' => isset($_POST['quantity']) ? wc_stock_amount(wp_unslash($_POST['quantity'])) : $product->get_min_purchase_quantity(), // phpcs:ignore WordPress.Security.NonceVerification.Missing
				)
			);
			?>
		</div>
		<div class="imania-single-product__actions">
			<button type="submit" name="add-to-cart" value="<?php echo esc_attr($product->get_id()); ?>" class="imania-btn imania-btn--primary imania-btn--sm imania-single-product__buy" data-imania-add-trigger>
				<?php esc_html_e('COMPRAR', 'imania-store'); ?>
			</button>
			<button type="submit" name="add-to-cart" value="<?php echo esc_attr($product->get_id()); ?>" class="imania-btn imania-btn--outline imania-btn--sm imania-single-product__add" data-imania-add-trigger>
				<?php esc_html_e('Adicionar ao carrinho', 'imania-store'); ?>
			</button>
		</div>
		<input type="hidden" name="product_id" value="<?php echo esc_attr($product->get_id()); ?>" />
		<input type="hidden" name="add-to-cart" value="<?php echo esc_attr($product->get_id()); ?>" />
	</form>
<?php endif; ?>
