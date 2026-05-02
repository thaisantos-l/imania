<?php
/**
 * Home product card component.
 *
 * @package Imania_Store
 */

$product = isset( $args['product'] ) && $args['product'] instanceof WC_Product ? $args['product'] : null;

if ( ! $product ) {
	return;
}

$permalink = get_permalink( $product->get_id() );
$terms     = get_the_terms( $product->get_id(), 'product_cat' );
$cat_name  = ! is_wp_error( $terms ) && ! empty( $terms ) ? $terms[0]->name : '';
?>
<article class="imania-product-card" aria-label="<?php echo esc_attr( $product->get_name() ); ?>">
	<a class="imania-product-card__thumb" href="<?php echo esc_url( $permalink ); ?>">
		<?php echo wp_kses_post( $product->get_image( 'woocommerce_thumbnail', array( 'loading' => 'lazy' ) ) ); ?>
	</a>
	<div class="imania-product-card__content">
		<?php if ( $cat_name ) : ?>
			<p class="imania-product-card__cat"><?php echo esc_html( $cat_name ); ?></p>
		<?php endif; ?>
		<h3 class="imania-product-card__title">
			<a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $product->get_name() ); ?></a>
		</h3>
		<div class="imania-price"><?php echo wp_kses_post( $product->get_price_html() ); ?></div>
		<a class="imania-btn imania-btn--outline imania-btn--sm" href="<?php echo esc_url( $permalink ); ?>"><?php esc_html_e( 'Ver produto', 'imania-store' ); ?></a>
	</div>
</article>
