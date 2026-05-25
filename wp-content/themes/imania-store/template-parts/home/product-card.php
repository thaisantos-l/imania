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
$product_id = $product->get_id();
$is_favorited = imania_store_is_in_wishlist( $product_id );

$card_image_ids = array_merge(
	array( (int) $product->get_image_id() ),
	array_map( 'absint', (array) $product->get_gallery_image_ids() )
);
$card_image_ids = array_values( array_unique( array_filter( $card_image_ids ) ) );
$card_image_ids = array_slice( $card_image_ids, 0, 3 );
$gallery_items  = array();

foreach ( $card_image_ids as $image_id ) {
	$image_url = wp_get_attachment_image_url( $image_id, 'woocommerce_thumbnail' );
	if ( ! $image_url ) {
		continue;
	}

	$image_alt = (string) get_post_meta( $image_id, '_wp_attachment_image_alt', true );
	if ( '' === $image_alt ) {
		$image_alt = $name;
	}

	$gallery_items[] = array(
		'src'    => esc_url_raw( $image_url ),
		'srcset' => (string) wp_get_attachment_image_srcset( $image_id, 'woocommerce_thumbnail' ),
		'sizes'  => (string) wp_get_attachment_image_sizes( $image_id, 'woocommerce_thumbnail' ),
		'alt'    => $image_alt,
	);
}

if ( empty( $gallery_items ) ) {
	$gallery_items[] = array(
		'src'    => esc_url_raw( wc_placeholder_img_src( 'woocommerce_thumbnail' ) ),
		'srcset' => '',
		'sizes'  => '',
		'alt'    => $name,
	);
}

$active_image = $gallery_items[0];
$dot_count    = count( $gallery_items );

if ( 'showcase' === $variant ) :
	?>
	<article
		class="imania-product-card imania-product-card--showcase"
		aria-label="<?php echo esc_attr( $name ); ?>"
		data-imania-product-card-gallery="<?php echo esc_attr( wp_json_encode( $gallery_items ) ); ?>"
	>
		<a class="imania-product-card__thumb" href="<?php echo esc_url( $permalink ); ?>">
			<img
				src="<?php echo esc_url( $active_image['src'] ); ?>"
				alt="<?php echo esc_attr( $active_image['alt'] ); ?>"
				loading="lazy"
				decoding="async"
				data-imania-product-card-image
				<?php if ( '' !== $active_image['srcset'] ) : ?>
					srcset="<?php echo esc_attr( $active_image['srcset'] ); ?>"
				<?php endif; ?>
				<?php if ( '' !== $active_image['sizes'] ) : ?>
					sizes="<?php echo esc_attr( $active_image['sizes'] ); ?>"
				<?php endif; ?>
			/>
		</a>
		<div class="imania-product-card__dots" role="tablist" aria-label="<?php esc_attr_e( 'Galeria do produto', 'imania-store' ); ?>">
			<?php for ( $i = 0; $i < $dot_count; $i++ ) : ?>
				<button
					type="button"
					class="<?php echo 0 === $i ? 'is-active' : ''; ?>"
					data-imania-product-card-dot
					data-slide-index="<?php echo esc_attr( $i ); ?>"
					aria-label="<?php echo esc_attr( sprintf( __( 'Ver imagem %d', 'imania-store' ), $i + 1 ) ); ?>"
					aria-current="<?php echo 0 === $i ? 'true' : 'false'; ?>"
				></button>
			<?php endfor; ?>
		</div>
		<div class="imania-product-card__meta-line">
			<h3 class="imania-product-card__title">
				<a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $name ); ?></a>
			</h3>
			<div class="imania-price"><?php echo wp_kses_post( $product->get_price_html() ); ?></div>
		</div>
		<div class="imania-product-card__actions">
			<button class="imania-product-card__wishlist<?php echo $is_favorited ? ' is-active' : ''; ?>" type="button" data-imania-wishlist-toggle data-product-id="<?php echo esc_attr( $product_id ); ?>" aria-pressed="<?php echo $is_favorited ? 'true' : 'false'; ?>" aria-label="<?php esc_attr_e( 'Adicionar aos favoritos', 'imania-store' ); ?>">
				<svg width="28" height="28" viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false">
					<path d="M12 20.4s-7-4.4-9.3-8.2C.8 8.9 2.2 5 6 5c2.4 0 3.7 1.5 4 2 .3-.5 1.6-2 4-2 3.8 0 5.2 3.9 3.3 7.2-2.3 3.8-9.3 8.2-9.3 8.2Z" stroke="currentColor" fill="none" stroke-width="1.4" stroke-linejoin="round"/>
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
		<div class="imania-product-card__footer-actions">
			<button class="imania-product-card__wishlist imania-product-card__wishlist--compact<?php echo $is_favorited ? ' is-active' : ''; ?>" type="button" data-imania-wishlist-toggle data-product-id="<?php echo esc_attr( $product_id ); ?>" aria-pressed="<?php echo $is_favorited ? 'true' : 'false'; ?>" aria-label="<?php esc_attr_e( 'Adicionar aos favoritos', 'imania-store' ); ?>">
				<svg width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false">
					<path d="M12 20.4s-7-4.4-9.3-8.2C.8 8.9 2.2 5 6 5c2.4 0 3.7 1.5 4 2 .3-.5 1.6-2 4-2 3.8 0 5.2 3.9 3.3 7.2-2.3 3.8-9.3 8.2-9.3 8.2Z" stroke="currentColor" fill="none" stroke-width="1.4" stroke-linejoin="round"/>
				</svg>
			</button>
			<a class="imania-btn imania-btn--outline imania-btn--sm" href="<?php echo esc_url( $permalink ); ?>"><?php esc_html_e( 'Ver produto', 'imania-store' ); ?></a>
		</div>
	</div>
</article>
