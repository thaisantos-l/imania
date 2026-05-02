<?php
/**
 * Home hero featured product card.
 *
 * @package Imania_Store
 */

$product = isset( $args['product'] ) && $args['product'] instanceof WC_Product ? $args['product'] : null;
?>
<div class="imania-hero-product">
	<?php if ( $product ) : ?>
		<a class="imania-hero-product__image" href="<?php echo esc_url( get_permalink( $product->get_id() ) ); ?>">
			<?php echo wp_kses_post( $product->get_image( 'woocommerce_single', array( 'loading' => 'eager' ) ) ); ?>
		</a>
		<div class="imania-hero-product__meta">
			<p class="imania-hero-product__label"><?php esc_html_e( 'Produto em evidência', 'imania-store' ); ?></p>
			<h3><a href="<?php echo esc_url( get_permalink( $product->get_id() ) ); ?>"><?php echo esc_html( $product->get_name() ); ?></a></h3>
			<div class="imania-price"><?php echo wp_kses_post( $product->get_price_html() ); ?></div>
			<a class="imania-btn imania-btn--primary" href="<?php echo esc_url( get_permalink( $product->get_id() ) ); ?>"><?php esc_html_e( 'Ver produto', 'imania-store' ); ?></a>
		</div>
	<?php else : ?>
		<div class="imania-hero-product__empty">
			<p><?php esc_html_e( 'Cadastre produtos para destacar na Home.', 'imania-store' ); ?></p>
		</div>
	<?php endif; ?>
</div>
