<?php
/**
 * Front page template.
 *
 * @package Imania_Store
 */

get_header();

$categories = imania_store_get_home_categories(8);
$shop_url = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/');
$showcase_title = __('Ímãs divertidos', 'imania-store');
$showcase_products = imania_store_get_home_products('new', 4);

$showcase_4_title = __('Lousas & Planners', 'imania-store');
$showcase_4_category = 'lousas-de-geladeira';
$showcase_4_products = imania_store_get_home_products_by_category($showcase_4_category, 4);
$showcase_4_target_url = $shop_url;

$showcase_4_term = get_term_by('slug', $showcase_4_category, 'product_cat');
if ($showcase_4_term instanceof \WP_Term) {
	$showcase_4_link = get_term_link($showcase_4_term);
	if (!is_wp_error($showcase_4_link)) {
		$showcase_4_target_url = $showcase_4_link;
	}
}
$promo_title_start = __('Faça a sua geladeira', 'imania-store');
$promo_title_highlight = __('sorrir!', 'imania-store');
$promo_text = __('Com mais de 1000 mil modelos diferentes para você encher o carrinho.', 'imania-store');
$promo_2_title_strong = __('Seus amigos', 'imania-store');
$promo_2_title_rest = __('também querem geladeiras sorridentes!', 'imania-store');
$promo_2_media_label = __('Banner ímãs presenteáveis', 'imania-store');
$testimonials = imania_store_get_testimonials(4);
$testimonials_title_a = __('IMANIA', 'imania-store');
$testimonials_title_b = __('COS PELO MUNDO', 'imania-store');
?>
<main id="primary" class="site-main imania-home">
	<section class="imania-hero">
		<?php get_template_part('template-parts/home/hero'); ?>
	</section>

	<section class="imania-showcase" aria-label="<?php echo esc_attr($showcase_title); ?>">
		<div class="container">
			<div class="imania-showcase__head">
				<h2><?php echo esc_html($showcase_title); ?></h2>
				<a class="imania-showcase__more" href="<?php echo esc_url($shop_url); ?>">
					<span><?php esc_html_e('Veja mais', 'imania-store'); ?></span>
					<svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false">
						<circle cx="12" cy="12" r="10.5" stroke="currentColor" />
						<path d="M12 7V17M7 12H17" stroke="currentColor" stroke-linecap="round" />
					</svg>
				</a>
			</div>
			<div class="imania-showcase__line"></div>

			<?php if (!empty($showcase_products)): ?>
				<div class="row g-3 g-lg-4">
					<?php foreach ($showcase_products as $product): ?>
						<div class="col-6 col-lg-3">
							<?php get_template_part('template-parts/home/product-card', null, array('product' => $product, 'variant' => 'showcase')); ?>
						</div>
					<?php endforeach; ?>
				</div>
			<?php else: ?>
				<div class="imania-empty-state">
					<p><?php esc_html_e('Nenhum produto disponível nesta seção no momento.', 'imania-store'); ?></p>
				</div>
			<?php endif; ?>
		</div>
	</section>

	<section class="imania-promo-split imania-promo-split--section-3" aria-label="<?php esc_attr_e('Destaque institucional', 'imania-store'); ?>">
		<div class="container-fluid px-0">
			<div class="row g-0">
				<div class="col-12 col-lg-6">
					<div class="imania-promo-split__content">
						<h2>
							<?php echo esc_html($promo_title_start); ?>
							<strong><?php echo esc_html($promo_title_highlight); ?></strong>
						</h2>
						<p><?php echo esc_html($promo_text); ?></p>
						<a class="imania-btn imania-btn--primary imania-btn--with-icon"
							href="<?php echo esc_url($shop_url); ?>">
							<span><?php esc_html_e('Veja mais', 'imania-store'); ?></span>
							<svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"
								focusable="false">
								<circle cx="12" cy="12" r="10.5" stroke="currentColor" />
								<path d="M12 7V17M7 12H17" stroke="currentColor" stroke-linecap="round" />
							</svg>
						</a>
					</div>
				</div>
				<div class="col-12 col-lg-6">
					<div class="imania-promo-split__media"
						aria-label="<?php esc_attr_e('Área de vídeo', 'imania-store'); ?>">
						<span>[video]</span>
					</div>
				</div>
			</div>
		</div>
	</section>

	<section class="imania-showcase" aria-label="<?php echo esc_attr($showcase_4_title); ?>">
		<div class="container">
			<div class="imania-showcase__head">
				<h2><?php echo esc_html($showcase_4_title); ?></h2>
				<a class="imania-showcase__more" href="<?php echo esc_url($showcase_4_target_url); ?>">
					<span><?php esc_html_e('Veja mais', 'imania-store'); ?></span>
					<svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false">
						<circle cx="12" cy="12" r="10.5" stroke="currentColor" />
						<path d="M12 7V17M7 12H17" stroke="currentColor" stroke-linecap="round" />
					</svg>
				</a>
			</div>
			<div class="imania-showcase__line"></div>

			<?php if (!empty($showcase_4_products)): ?>
				<div class="row g-3 g-lg-4">
					<?php foreach ($showcase_4_products as $product): ?>
						<div class="col-6 col-lg-3">
							<?php get_template_part('template-parts/home/product-card', null, array('product' => $product, 'variant' => 'showcase')); ?>
						</div>
					<?php endforeach; ?>
				</div>
			<?php else: ?>
				<div class="imania-empty-state">
					<p><?php esc_html_e('Nenhum produto disponível nesta seção no momento.', 'imania-store'); ?></p>
				</div>
			<?php endif; ?>
		</div>
	</section>

	<section class="imania-promo-split imania-promo-split--reverse imania-promo-split--section-5"
		aria-label="<?php esc_attr_e('Destaque presenteáveis', 'imania-store'); ?>">
		<div class="container-fluid px-0">
			<div class="row g-0">
				<div class="col-12 col-lg-6">
					<div class="imania-promo-split__media"
						aria-label="<?php esc_attr_e('Banner presenteáveis', 'imania-store'); ?>">
						<span><?php echo esc_html($promo_2_media_label); ?></span>
					</div>
				</div>
				<div class="col-12 col-lg-6">
					<div class="imania-promo-split__content">
						<h2>
							<strong><?php echo esc_html($promo_2_title_strong); ?></strong>
							<?php echo esc_html($promo_2_title_rest); ?>
						</h2>
						<a class="imania-btn imania-btn--primary imania-btn--with-icon"
							href="<?php echo esc_url($shop_url); ?>">
							<span><?php esc_html_e('Veja mais', 'imania-store'); ?></span>
							<svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"
								focusable="false">
								<circle cx="12" cy="12" r="10.5" stroke="currentColor" />
								<path d="M12 7V17M7 12H17" stroke="currentColor" stroke-linecap="round" />
							</svg>
						</a>
					</div>
				</div>
			</div>
		</div>
	</section>

	<section class="imania-testimonials" aria-label="<?php esc_attr_e('Depoimentos de clientes', 'imania-store'); ?>">
		<div class="container">
			<div class="imania-testimonials__head">
				<h2><span><?php echo esc_html($testimonials_title_a); ?></span><?php echo esc_html($testimonials_title_b); ?></h2>
			</div>

			<?php if (!empty($testimonials)): ?>
				<div class="row g-3 g-lg-4">
					<?php foreach ($testimonials as $testimonial): ?>
						<?php
						$testimonial_id = (int) $testimonial->ID;
						$client_name = get_the_title($testimonial_id);
						$client_city = (string) imania_store_get_custom_field($testimonial_id, 'local_do_cliente');
						$product_name = (string) imania_store_get_custom_field($testimonial_id, 'nome_do_produto');
						$product_value = (string) imania_store_get_custom_field($testimonial_id, 'valor_do_produto');
						$product_photo = imania_store_get_custom_field($testimonial_id, 'foto_do_produto');
						$product_thumb = imania_store_resolve_image_url($product_photo, 'thumbnail');
						$product_photo_main = imania_store_resolve_image_url($product_photo, 'medium_large');
						$client_photo = get_the_post_thumbnail_url($testimonial_id, 'medium_large');
						$cta_url_raw = (string) imania_store_get_custom_field($testimonial_id, 'url_produto');
						$cta_url = '' !== $cta_url_raw ? esc_url_raw($cta_url_raw) : $shop_url;
						if ('' === $cta_url) {
							$cta_url = $shop_url;
						}
						?>
						<div class="col-6 col-lg-3">
							<article class="imania-testimonial-card" aria-label="<?php echo esc_attr($client_name); ?>">
								<div class="imania-testimonial-card__top">
									<span class="imania-testimonial-card__dot" aria-hidden="true"></span>
									<div class="imania-testimonial-card__client">
										<h3><?php echo esc_html($client_name); ?></h3>
										<?php if ('' !== $client_city): ?>
											<p><?php echo esc_html($client_city); ?></p>
										<?php endif; ?>
									</div>
								</div>
								<div class="imania-testimonial-card__photo">
									<?php if ($product_photo_main): ?>
										<img src="<?php echo esc_url($product_photo_main); ?>" alt="<?php echo esc_attr($product_name); ?>" loading="lazy" />
									<?php elseif ($client_photo): ?>
										<img src="<?php echo esc_url($client_photo); ?>" alt="<?php echo esc_attr($client_name); ?>" loading="lazy" />
									<?php endif; ?>
								</div>
								<div class="imania-testimonial-card__bottom">
									<div class="imania-testimonial-card__product-thumb">
										<?php if ('' !== $product_thumb): ?>
											<img src="<?php echo esc_url($product_thumb); ?>" alt="<?php echo esc_attr($product_name); ?>" loading="lazy" />
										<?php endif; ?>
									</div>
									<div class="imania-testimonial-card__meta">
										<p class="imania-testimonial-card__product-name"><?php echo esc_html($product_name); ?></p>
										<p class="imania-testimonial-card__product-price"><?php echo esc_html($product_value); ?></p>
									</div>
									<a class="imania-testimonial-card__cta" href="<?php echo esc_url($cta_url); ?>"><?php esc_html_e('EU QUERO TAMBÉM!', 'imania-store'); ?></a>
								</div>
							</article>
						</div>
					<?php endforeach; ?>
				</div>
			<?php else: ?>
				<div class="imania-empty-state">
					<p><?php esc_html_e('Cadastre depoimentos para exibir nesta seção.', 'imania-store'); ?></p>
				</div>
			<?php endif; ?>
		</div>
	</section>


</main>
<?php
get_footer();
