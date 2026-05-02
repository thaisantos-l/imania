<?php
/**
 * Front page template.
 *
 * @package Imania_Store
 */

get_header();

$hero_product = imania_store_get_home_hero_product();
$categories   = imania_store_get_home_categories( 8 );
$stats        = imania_store_get_home_stats();
$shop_url     = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
?>
<main id="primary" class="site-main imania-home">
	<section class="imania-hero">
		<div class="container">
			<div class="row align-items-center g-4 g-lg-5">
				<div class="col-12 col-lg-6">
					<div class="imania-hero__content">
						<p class="imania-kicker"><?php esc_html_e( 'Loja oficial', 'imania-store' ); ?></p>
						<h1 class="imania-hero__title"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></h1>
						<p class="imania-hero__subtitle"><?php echo esc_html( get_bloginfo( 'description' ) ); ?></p>
						<div class="imania-hero__actions">
							<a class="imania-btn imania-btn--primary" href="<?php echo esc_url( $shop_url ); ?>"><?php esc_html_e( 'Ver catálogo', 'imania-store' ); ?></a>
							<a class="imania-btn imania-btn--outline" href="<?php echo esc_url( imania_store_get_login_to_price_url() ); ?>"><?php esc_html_e( 'Login para preço', 'imania-store' ); ?></a>
						</div>
						<div class="imania-hero__stats">
							<div class="imania-stat">
								<strong><?php echo esc_html( number_format_i18n( $stats['products'] ) ); ?></strong>
								<span><?php esc_html_e( 'Produtos', 'imania-store' ); ?></span>
							</div>
							<div class="imania-stat">
								<strong><?php echo esc_html( number_format_i18n( $stats['categories'] ) ); ?></strong>
								<span><?php esc_html_e( 'Categorias', 'imania-store' ); ?></span>
							</div>
							<div class="imania-stat">
								<strong><?php echo esc_html( number_format_i18n( $stats['highlights'] ) ); ?></strong>
								<span><?php esc_html_e( 'Destaques', 'imania-store' ); ?></span>
							</div>
						</div>
					</div>
				</div>
				<div class="col-12 col-lg-6">
					<?php get_template_part( 'template-parts/home/hero', null, array( 'product' => $hero_product ) ); ?>
				</div>
			</div>
		</div>
	</section>

	<section class="imania-categories">
		<div class="container">
			<div class="imania-section-head">
				<h2><?php esc_html_e( 'Categorias principais', 'imania-store' ); ?></h2>
			</div>
			<div class="row g-2 g-md-3">
				<?php foreach ( $categories as $category ) : ?>
					<div class="col-6 col-md-4 col-lg-3">
						<a class="imania-chip" href="<?php echo esc_url( get_term_link( $category ) ); ?>">
							<span><?php echo esc_html( $category->name ); ?></span>
							<small><?php echo esc_html( number_format_i18n( $category->count ) ); ?></small>
						</a>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<?php
	get_template_part(
		'template-parts/home/product-section',
		null,
		array(
			'segment' => 'featured',
			'title'   => __( 'Destaques da semana', 'imania-store' ),
		)
	);

	get_template_part(
		'template-parts/home/product-section',
		null,
		array(
			'segment' => 'bestsellers',
			'title'   => __( 'Mais vendidos', 'imania-store' ),
		)
	);

	get_template_part(
		'template-parts/home/product-section',
		null,
		array(
			'segment' => 'new',
			'title'   => __( 'Novidades', 'imania-store' ),
		)
	);

	get_template_part(
		'template-parts/home/product-section',
		null,
		array(
			'segment' => 'sale',
			'title'   => __( 'Ofertas da semana', 'imania-store' ),
		)
	);
	?>
</main>
<?php
get_footer();
