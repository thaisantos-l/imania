<?php
/**
 * Custom variation add-to-cart area.
 *
 * @package Imania_Store
 */

defined('ABSPATH') || exit;

global $product;
?>
<div class="woocommerce-variation-add-to-cart variations_button imania-single-product__cart" data-imania-add-to-cart-form>
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
		<button type="submit" class="single_add_to_cart_button imania-btn imania-btn--primary imania-btn--sm imania-single-product__buy" data-imania-add-trigger>
			<?php esc_html_e('COMPRAR', 'imania-store'); ?>
		</button>
		<button type="submit" class="single_add_to_cart_button imania-btn imania-btn--outline imania-btn--sm imania-single-product__add" data-imania-add-trigger>
			<?php esc_html_e('Adicionar ao carrinho', 'imania-store'); ?>
		</button>
	</div>

	<input type="hidden" name="add-to-cart" value="<?php echo absint($product->get_id()); ?>" />
	<input type="hidden" name="product_id" value="<?php echo absint($product->get_id()); ?>" />
	<input type="hidden" name="variation_id" class="variation_id" value="0" />
</div>
