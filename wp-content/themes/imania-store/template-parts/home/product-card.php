<?php
/**
 * Home product card component.
 *
 * @package Imania_Store
 */

$product = isset( $args['product'] ) && $args['product'] instanceof WC_Product ? $args['product'] : null;
$variant = isset( $args['variant'] ) ? sanitize_key( $args['variant'] ) : 'default';

if ( ! $product ) {
	return;
}

$permalink = get_permalink( $product->get_id() );
$terms     = get_the_terms( $product->get_id(), 'product_cat' );
$cat_name  = ! is_wp_error( $terms ) && ! empty( $terms ) ? $terms[0]->name : '';
$name      = $product->get_name();

$gallery_ids = $product->get_gallery_image_ids();
$media_count = 1 + count( $gallery_ids );
$dot_count   = min( 3, max( 1, $media_count ) );

if ( 'showcase' === $variant ) :
	?>
	<article class="imania-product-card imania-product-card--showcase" aria-label="<?php echo esc_attr( $name ); ?>">
		<a class="imania-product-card__thumb" href="<?php echo esc_url( $permalink ); ?>">
			<?php echo wp_kses_post( $product->get_image( 'woocommerce_thumbnail', array( 'loading' => 'lazy' ) ) ); ?>
		</a>
		<div class="imania-product-card__dots" aria-hidden="true">
			<?php for ( $i = 0; $i < $dot_count; $i++ ) : ?>
				<span class="<?php echo 0 === $i ? 'is-active' : ''; ?>"></span>
			<?php endfor; ?>
		</div>
		<div class="imania-product-card__meta-line">
			<h3 class="imania-product-card__title">
				<a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $name ); ?></a>
			</h3>
			<div class="imania-price"><?php echo wp_kses_post( $product->get_price_html() ); ?></div>
		</div>
		<div class="imania-product-card__actions">
			<button class="imania-product-card__wishlist" type="button" aria-label="<?php esc_attr_e( 'Adicionar aos favoritos', 'imania-store' ); ?>">
				<svg width="28" height="28" viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false">
					<path d="M12 20.4s-7-4.4-9.3-8.2C.8 8.9 2.2 5 6 5c2.4 0 3.7 1.5 4 2 .3-.5 1.6-2 4-2 3.8 0 5.2 3.9 3.3 7.2-2.3 3.8-9.3 8.2-9.3 8.2Z" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"/>
				</svg>
			</button>
			<a class="imania-product-card__buy" href="<?php echo esc_url( $permalink ); ?>"><?php esc_html_e( 'COMPRAR', 'imania-store' ); ?></a>
		</div>
	</article>
	<?php
	return;
endif;
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
