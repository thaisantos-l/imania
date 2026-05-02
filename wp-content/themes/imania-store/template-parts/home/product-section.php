<?php
/**
 * Home product section.
 *
 * @package Imania_Store
 */

$segment  = isset( $args['segment'] ) ? sanitize_key( $args['segment'] ) : 'new';
$title    = isset( $args['title'] ) ? (string) $args['title'] : __( 'Produtos', 'imania-store' );
$products = imania_store_get_home_products( $segment, 8 );
$shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
?>
<section class="imania-product-section imania-product-section--<?php echo esc_attr( $segment ); ?>">
	<div class="container">
		<div class="imania-section-head">
			<h2><?php echo esc_html( $title ); ?></h2>
			<a href="<?php echo esc_url( $shop_url ); ?>" class="imania-btn imania-btn--ghost"><?php esc_html_e( 'Ver todos', 'imania-store' ); ?></a>
		</div>

		<?php if ( ! empty( $products ) ) : ?>
			<div class="row g-3 g-md-4">
				<?php foreach ( $products as $product ) : ?>
					<div class="col-6 col-md-4 col-xl-3">
						<?php get_template_part( 'template-parts/home/product-card', null, array( 'product' => $product ) ); ?>
					</div>
				<?php endforeach; ?>
			</div>
		<?php else : ?>
			<div class="imania-empty-state">
				<p><?php esc_html_e( 'Nenhum produto disponível nesta seção no momento.', 'imania-store' ); ?></p>
			</div>
		<?php endif; ?>
	</div>
</section>
