<?php
/**
 * Custom single product content layout.
 *
 * @package Imania_Store
 */

defined('ABSPATH') || exit;

global $product;

do_action('woocommerce_before_single_product');

if (post_password_required()) {
	echo get_the_password_form(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	return;
}

if (!$product instanceof WC_Product) {
	return;
}

$product_id = $product->get_id();
$gallery_ids = array_filter(array_map('absint', (array) $product->get_gallery_image_ids()));
$featured_image_id = absint($product->get_image_id());
$image_ids = array();
$gallery_images = array();

if ($featured_image_id > 0) {
	$featured_url = wp_get_attachment_image_url($featured_image_id, 'woocommerce_single');
	if (is_string($featured_url) && '' !== $featured_url) {
		$image_ids[] = $featured_image_id;
	}
}

foreach ($gallery_ids as $gallery_image_id) {
	if ($gallery_image_id <= 0 || in_array($gallery_image_id, $image_ids, true)) {
		continue;
	}
	$gallery_url = wp_get_attachment_image_url($gallery_image_id, 'woocommerce_single');
	if (!is_string($gallery_url) || '' === $gallery_url) {
		continue;
	}
	$image_ids[] = $gallery_image_id;
}

foreach ($image_ids as $image_id) {
	$thumb_html = wp_get_attachment_image($image_id, 'woocommerce_thumbnail', false, array('loading' => 'lazy'));
	$single_html = wp_get_attachment_image($image_id, 'woocommerce_single', false, array('loading' => 'lazy'));
	$single_src_data = wp_get_attachment_image_src($image_id, 'woocommerce_single');
	$single_url = is_array($single_src_data) && isset($single_src_data[0]) ? (string) $single_src_data[0] : '';
	$single_srcset = wp_get_attachment_image_srcset($image_id, 'woocommerce_single');
	$single_sizes = wp_get_attachment_image_sizes($image_id, 'woocommerce_single');
	$image_alt = get_post_meta($image_id, '_wp_attachment_image_alt', true);

	if (!is_string($thumb_html) || '' === $thumb_html || !is_string($single_html) || '' === $single_html) {
		continue;
	}
	if ('' === $single_url) {
		continue;
	}

	$gallery_images[] = array(
		'id' => $image_id,
		'thumb_html' => $thumb_html,
		'single_html' => $single_html,
		'single_url' => $single_url,
		'single_srcset' => is_string($single_srcset) ? $single_srcset : '',
		'single_sizes' => is_string($single_sizes) ? $single_sizes : '',
		'image_alt' => is_string($image_alt) ? $image_alt : '',
	);
}

$has_gallery = count($gallery_images) > 1;
$is_logged_in = is_user_logged_in();
$rating_count = $product->get_rating_count();
$review_count = $product->get_review_count();
$average_rating = $product->get_average_rating();
$short_description = $product->get_short_description();
$related_products = imania_store_get_single_related_products($product_id, 4);
?>
<section id="product-<?php the_ID(); ?>" <?php wc_product_class('imania-single-product', $product); ?> data-imania-single-product data-loading-state="loading">
	<div class="container">
		<div class="imania-single-product__skeleton" data-imania-single-skeleton aria-hidden="true">
			<div class="imania-single-product__skeleton-main"></div>
			<div class="imania-single-product__skeleton-info"></div>
		</div>

		<div class="imania-single-product__notice" data-imania-single-notice aria-live="polite"></div>

		<div class="imania-single-product__layout" data-imania-single-layout>
			<div class="imania-single-product__media <?php echo $has_gallery ? 'imania-single-product__media--has-gallery' : 'imania-single-product__media--single'; ?>">
				<?php if ($has_gallery) : ?>
					<div class="imania-single-product__thumbs-rail" data-imania-thumbs-rail>
						<button class="imania-single-product__thumbs-arrow imania-single-product__thumbs-arrow--up" type="button" data-imania-thumbs-prev aria-label="<?php esc_attr_e('Imagem anterior', 'imania-store'); ?>">
							<span aria-hidden="true">&#9650;</span>
						</button>
						<div class="imania-single-product__thumbs swiper" data-imania-single-thumbs>
							<div class="swiper-wrapper">
								<?php foreach ($gallery_images as $image_item) : ?>
									<div
										class="swiper-slide"
										data-imania-main-src="<?php echo esc_url($image_item['single_url']); ?>"
										data-imania-main-srcset="<?php echo esc_attr($image_item['single_srcset']); ?>"
										data-imania-main-sizes="<?php echo esc_attr($image_item['single_sizes']); ?>"
										data-imania-main-alt="<?php echo esc_attr($image_item['image_alt']); ?>"
									><?php echo wp_kses_post($image_item['thumb_html']); ?></div>
								<?php endforeach; ?>
							</div>
						</div>
						<button class="imania-single-product__thumbs-arrow imania-single-product__thumbs-arrow--down" type="button" data-imania-thumbs-next aria-label="<?php esc_attr_e('Próxima imagem', 'imania-store'); ?>">
							<span aria-hidden="true">&#9660;</span>
						</button>
					</div>
					<div class="imania-single-product__image imania-single-product__image--dynamic" data-imania-main-image-wrap>
						<img
							data-imania-main-image
							src="<?php echo esc_url($gallery_images[0]['single_url']); ?>"
							<?php if (!empty($gallery_images[0]['single_srcset'])) : ?>srcset="<?php echo esc_attr($gallery_images[0]['single_srcset']); ?>"<?php endif; ?>
							<?php if (!empty($gallery_images[0]['single_sizes'])) : ?>sizes="<?php echo esc_attr($gallery_images[0]['single_sizes']); ?>"<?php endif; ?>
							alt="<?php echo esc_attr($gallery_images[0]['image_alt']); ?>"
							loading="eager"
						/>
					</div>
				<?php else : ?>
					<div class="imania-single-product__image">
						<?php
						if (!empty($gallery_images[0]['single_html'])) {
							echo wp_kses_post($gallery_images[0]['single_html']);
						} else {
							echo wp_kses_post(wc_placeholder_img('woocommerce_single'));
						}
						?>
					</div>
				<?php endif; ?>
			</div>

			<div class="imania-single-product__summary">
				<h1 class="imania-single-product__title"><?php echo esc_html($product->get_name()); ?></h1>

				<?php if ($short_description) : ?>
					<div class="imania-single-product__excerpt"><?php echo wp_kses_post(wpautop($short_description)); ?></div>
				<?php endif; ?>

				<?php if (wc_review_ratings_enabled() && $rating_count > 0) : ?>
					<div class="imania-single-product__rating">
						<?php echo wp_kses_post(wc_get_rating_html($average_rating, $rating_count)); ?>
						<span><?php echo esc_html(sprintf(_n('%d avaliacao', '%d avaliacoes', $review_count, 'imania-store'), $review_count)); ?></span>
					</div>
				<?php endif; ?>

				<div class="imania-single-product__price"><?php echo wp_kses_post($product->get_price_html()); ?></div>

				<?php if ($is_logged_in) : ?>
					<div class="imania-single-product__cart-wrap" data-imania-single-cart-wrap>
						<?php woocommerce_template_single_add_to_cart(); ?>
					</div>
				<?php else : ?>
					<div class="imania-single-product__guest-lock">
						<a class="imania-btn imania-btn--primary imania-btn--sm" href="<?php echo esc_url(imania_store_get_login_to_price_url()); ?>">
							<?php esc_html_e('Faca login para comprar', 'imania-store'); ?>
						</a>
					</div>
				<?php endif; ?>
			</div>
		</div>

		<section class="imania-single-product__recommended" data-imania-single-recommended data-loading-state="loading">
			<div class="imania-single-product__recommended-head">
				<h2><?php esc_html_e('Já que você gostou disso, acha que vai gostar desses também:', 'imania-store'); ?></h2>
			</div>

			<div class="imania-single-product__recommended-skeleton" data-imania-recommended-skeleton aria-hidden="true">
				<span></span><span></span><span></span><span></span>
			</div>

			<div class="row g-3 g-lg-4" data-imania-recommended-grid>
				<?php foreach ($related_products as $related_product) : ?>
					<div class="col-6 col-lg-3">
						<?php
						get_template_part(
							'template-parts/home/product-card',
							null,
							array(
								'product' => $related_product,
								'variant' => 'showcase',
							)
						);
						?>
					</div>
				<?php endforeach; ?>
			</div>
		</section>
	</div>
</section>

<?php do_action('woocommerce_after_single_product'); ?>
