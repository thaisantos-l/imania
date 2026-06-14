<?php
/**
 * Home banner slider.
 *
 * @package Imania_Store
 */

$banner_dir      = trailingslashit( get_template_directory() ) . 'assets/img/home';
$banner_base_url = trailingslashit( get_template_directory_uri() ) . 'assets/img/home';
$slides          = array();

if ( is_dir( $banner_dir ) ) {
	$files = glob( $banner_dir . '/*.{jpg,jpeg,png,webp,avif}', GLOB_BRACE );
	if ( ! empty( $files ) ) {
		sort( $files );
		foreach ( $files as $file ) {
			$filename = wp_basename( $file );
			$slides[] = array(
				'url' => trailingslashit( $banner_base_url ) . rawurlencode( $filename ),
				'alt' => trim( ucwords( str_replace( array( '-', '_' ), ' ', pathinfo( $filename, PATHINFO_FILENAME ) ) ) ),
			);
		}
	}
}
?>
<div class="imania-home-banner" data-imania-banner aria-label="<?php esc_attr_e( 'Banners rotativos da home', 'imania-store' ); ?>">
	<?php if ( ! empty( $slides ) ) : ?>
		<div class="swiper imania-home-banner__swiper" data-imania-banner-swiper>
			<div class="swiper-wrapper">
			<?php foreach ( $slides as $index => $slide ) : ?>
				<figure class="swiper-slide imania-home-banner__slide<?php echo 0 === $index ? ' is-active' : ''; ?>">
					<img
						src="<?php echo esc_url( $slide['url'] ); ?>"
						alt="<?php echo esc_attr( $slide['alt'] ); ?>"
						loading="<?php echo 0 === $index ? 'eager' : 'lazy'; ?>"
					/>
				</figure>
			<?php endforeach; ?>
			</div>
			<?php if ( count( $slides ) > 1 ) : ?>
				<div class="swiper-pagination imania-home-banner__pagination" data-imania-banner-pagination></div>
			<?php endif; ?>
		</div>
	<?php else : ?>
		<div class="imania-home-banner__empty">
			<p><?php esc_html_e( 'Adicione imagens em assets/img/home para exibir o banner.', 'imania-store' ); ?></p>
		</div>
	<?php endif; ?>
</div>
