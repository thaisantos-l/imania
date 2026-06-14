<?php
/**
 * Product catalog for the Shop and product category archives.
 *
 * @package Imania_Store
 * @see https://woocommerce.com/document/template-structure/
 */

defined('ABSPATH') || exit;

get_header('shop');
do_action('woocommerce_before_main_content');

$filters = imania_store_get_catalog_filters();
$price_bounds = imania_store_get_catalog_price_bounds();
$is_shop_catalog = is_shop();
$current_term = is_product_category() ? get_queried_object() : null;
$category_slug = $current_term instanceof WP_Term ? $current_term->slug : '';
$catalog_context = $is_shop_catalog ? 'shop' : 'category';
$catalog_title = woocommerce_page_title(false);
$catalog_description = $current_term instanceof WP_Term ? term_description($current_term) : '';
$current_page = max(1, (int) get_query_var('paged'));
$total_products = (int) $GLOBALS['wp_query']->found_posts;
$shown_products = min($total_products, $current_page * imania_store_catalog_per_page());
$next_page_url = $current_page < (int) $GLOBALS['wp_query']->max_num_pages
	? imania_store_get_catalog_page_url($catalog_context, $category_slug, $current_page + 1, $filters)
	: '';
?>
<section
	class="imania-catalog"
	data-imania-catalog
	data-context="<?php echo esc_attr($catalog_context); ?>"
	data-category="<?php echo esc_attr($category_slug); ?>"
	data-current-page="<?php echo esc_attr((string) $current_page); ?>"
	data-total-products="<?php echo esc_attr((string) $total_products); ?>"
>
	<div class="container imania-catalog__container">
		<header class="imania-catalog__header">
			<div>
				<h1><?php echo esc_html($catalog_title); ?></h1>
				<?php if ('' !== trim(wp_strip_all_tags($catalog_description))) : ?>
					<div class="imania-catalog__description">
						<?php echo wp_kses_post($catalog_description); ?>
					</div>
				<?php endif; ?>
			</div>
			<button
				class="imania-catalog__filter-toggle"
				type="button"
				aria-expanded="false"
				aria-controls="imania-catalog-filters"
				data-imania-catalog-filter-toggle
			>
				<span><?php esc_html_e('Filtros', 'imania-store'); ?></span>
				<svg width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true">
					<path d="M4 6h16M7 12h10M10 18h4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
				</svg>
			</button>
		</header>

		<div class="imania-catalog__line"></div>
		<?php woocommerce_output_all_notices(); ?>

		<div class="imania-catalog__layout">
			<button
				class="imania-catalog__backdrop"
				type="button"
				aria-label="<?php esc_attr_e('Fechar filtros', 'imania-store'); ?>"
				data-imania-catalog-filter-close
				hidden
			></button>

			<aside class="imania-catalog-filter" id="imania-catalog-filters" data-imania-catalog-filter>
				<div class="imania-catalog-filter__mobile-head">
					<strong><?php esc_html_e('Filtre sua busca', 'imania-store'); ?></strong>
					<button type="button" data-imania-catalog-filter-close aria-label="<?php esc_attr_e('Fechar filtros', 'imania-store'); ?>">×</button>
				</div>

				<form method="get" action="<?php echo esc_url($is_shop_catalog ? wc_get_page_permalink('shop') : get_term_link($current_term)); ?>">
					<fieldset class="imania-catalog-filter__group">
						<legend><?php esc_html_e('Preço', 'imania-store'); ?></legend>
						<div class="imania-catalog-filter__price">
							<label>
								<span><?php esc_html_e('Mínimo', 'imania-store'); ?></span>
								<input
									type="number"
									name="min_price"
									min="<?php echo esc_attr((string) $price_bounds['min']); ?>"
									max="<?php echo esc_attr((string) $price_bounds['max']); ?>"
									step="0.01"
									value="<?php echo esc_attr($filters['min_price']); ?>"
									placeholder="<?php echo esc_attr(wc_format_localized_price($price_bounds['min'])); ?>"
								/>
							</label>
							<span aria-hidden="true">—</span>
							<label>
								<span><?php esc_html_e('Máximo', 'imania-store'); ?></span>
								<input
									type="number"
									name="max_price"
									min="<?php echo esc_attr((string) $price_bounds['min']); ?>"
									max="<?php echo esc_attr((string) $price_bounds['max']); ?>"
									step="0.01"
									value="<?php echo esc_attr($filters['max_price']); ?>"
									placeholder="<?php echo esc_attr(wc_format_localized_price($price_bounds['max'])); ?>"
								/>
							</label>
						</div>
					</fieldset>

					<?php if ($is_shop_catalog) : ?>
						<fieldset class="imania-catalog-filter__group imania-catalog-filter__group--categories">
							<legend><?php esc_html_e('Categorias', 'imania-store'); ?></legend>
							<?php imania_store_render_catalog_category_options(imania_store_get_catalog_categories(), $filters['categories']); ?>
						</fieldset>
					<?php endif; ?>

					<div class="imania-catalog-filter__actions">
						<button class="imania-catalog-filter__apply" type="submit"><?php esc_html_e('Aplicar filtros', 'imania-store'); ?></button>
						<a href="<?php echo esc_url($is_shop_catalog ? wc_get_page_permalink('shop') : get_term_link($current_term)); ?>">
							<?php esc_html_e('Limpar filtros', 'imania-store'); ?>
						</a>
					</div>
				</form>
			</aside>

			<div class="imania-catalog__results">
				<div class="imania-catalog__summary" role="status" aria-live="polite">
					<p>
						<?php
						echo esc_html(
							sprintf(
								/* translators: 1: shown products, 2: total products. */
								__('Exibindo %1$d de %2$d produtos', 'imania-store'),
								$shown_products,
								$total_products
							)
						);
						?>
					</p>
				</div>

				<?php if (woocommerce_product_loop()) : ?>
					<div class="imania-catalog-grid" data-imania-catalog-grid>
						<?php
						$catalog_product_index = 0;
						while (have_posts()) {
							the_post();
							$product = wc_get_product(get_the_ID());
							if (!$product instanceof WC_Product || !$product->is_visible()) {
								continue;
							}
							?>
							<div class="imania-catalog-grid__item">
								<?php
								get_template_part(
									'template-parts/home/product-card',
									null,
									array(
										'product' => $product,
										'variant' => 'catalog',
										'image_loading' => $catalog_product_index < 4 ? 'eager' : 'lazy',
										'image_priority' => 0 === $catalog_product_index ? 'high' : 'auto',
									)
								);
								?>
							</div>
							<?php
							$catalog_product_index++;
						}
						?>
					</div>

					<div class="imania-catalog__load">
						<?php if ('' !== $next_page_url) : ?>
							<a
								class="imania-catalog__load-more"
								href="<?php echo esc_url($next_page_url); ?>"
								data-imania-catalog-load-more
							>
								<span><?php esc_html_e('Carregar mais', 'imania-store'); ?></span>
							</a>
						<?php else : ?>
							<p class="imania-catalog__end"><?php esc_html_e('Você chegou ao final dos produtos.', 'imania-store'); ?></p>
						<?php endif; ?>
					</div>
				<?php else : ?>
					<div class="imania-catalog__empty">
						<h2><?php esc_html_e('Nenhum produto encontrado', 'imania-store'); ?></h2>
						<p><?php esc_html_e('Tente ajustar ou limpar os filtros da busca.', 'imania-store'); ?></p>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>
<?php
do_action('woocommerce_after_main_content');
get_footer('shop');
