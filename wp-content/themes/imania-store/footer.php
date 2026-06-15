<?php
/**
 * The template for displaying the footer.
 *
 * @package Imania_Store
 */

// Fill these URLs when the official social profiles are available.
$footer_social_links = array(
	'instagram' => array(
		'label' => __('Instagram', 'imania-store'),
		'url' => '',
	),
	'whatsapp' => array(
		'label' => __('WhatsApp', 'imania-store'),
		'url' => '',
	),
	'email' => array(
		'label' => __('E-mail', 'imania-store'),
		'url' => '',
	),
);
// Images are rendered automatically after these files are added to assets/img/footer/.
$footer_badges = array(
	array(
		'file' => 'formas-de-pagamento.png',
		'alt' => __('Formas de pagamento', 'imania-store'),
	),
	array(
		'file' => 'loja-segura.png',
		'alt' => __('Loja segura', 'imania-store'),
	),
);
$footer_badge_directory = trailingslashit(get_template_directory()) . 'assets/img/footer/';
$footer_badge_uri = trailingslashit(get_template_directory_uri()) . 'assets/img/footer/';
$footer_locations = get_nav_menu_locations();
$available_footer_badges = array_filter(
	$footer_badges,
	static function ($badge) use ($footer_badge_directory) {
		return is_readable($footer_badge_directory . $badge['file']);
	}
);
?>

	<footer id="colophon" class="site-footer imania-footer">
		<div class="container imania-footer__container">
			<div class="imania-footer__grid">
				<div class="imania-footer__brand-column">
					<div class="imania-footer__logo">
						<?php
						the_custom_logo();
						if (!has_custom_logo()) :
							?>
							<a href="<?php echo esc_url(home_url('/')); ?>" rel="home">
								<?php bloginfo('name'); ?>
							</a>
							<?php
						endif;
						?>
					</div>

					<ul class="imania-footer__socials" aria-label="<?php esc_attr_e('Redes sociais', 'imania-store'); ?>">
						<?php foreach ($footer_social_links as $network => $social) : ?>
							<li>
								<?php if ('' !== $social['url']) : ?>
									<a
										href="<?php echo esc_url($social['url']); ?>"
										aria-label="<?php echo esc_attr($social['label']); ?>"
										<?php if ('email' !== $network) : ?>
											target="_blank"
											rel="noopener noreferrer"
										<?php endif; ?>
									>
								<?php else : ?>
									<span aria-label="<?php echo esc_attr($social['label']); ?>">
								<?php endif; ?>

									<?php if ('instagram' === $network) : ?>
										<svg viewBox="0 0 24 24" aria-hidden="true">
											<rect x="3" y="3" width="18" height="18" rx="5"></rect>
											<circle cx="12" cy="12" r="4"></circle>
											<circle cx="17.4" cy="6.6" r="1"></circle>
										</svg>
									<?php elseif ('whatsapp' === $network) : ?>
										<svg viewBox="0 0 24 24" aria-hidden="true">
											<path d="M20.5 11.8a8.5 8.5 0 0 1-12.6 7.4L3 20.5l1.3-4.7A8.5 8.5 0 1 1 20.5 11.8Z"></path>
											<path d="M8.2 7.8c.2-.5.4-.5.8-.5h.5c.2 0 .4.1.5.4l.8 1.9c.1.3.1.5-.1.7l-.6.8c-.2.2-.1.4 0 .6.7 1.2 1.7 2.1 2.9 2.8.2.1.4.1.6-.1l.9-1c.2-.2.4-.3.7-.2l1.9.9c.3.1.4.3.4.5 0 .4-.2 1.4-.9 1.9-.6.5-1.5.8-2.4.6-1.4-.3-3.1-1.1-4.9-2.8-1.4-1.3-2.4-2.9-2.7-4.2-.3-1.1 0-2 .4-2.5.3-.4.8-.8 1.2-.8Z"></path>
										</svg>
									<?php else : ?>
										<svg viewBox="0 0 24 24" aria-hidden="true">
											<rect x="3" y="5" width="18" height="14" rx="2"></rect>
											<path d="m4 7 8 6 8-6"></path>
										</svg>
									<?php endif; ?>

								<?php if ('' !== $social['url']) : ?>
									</a>
								<?php else : ?>
									</span>
								<?php endif; ?>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>

				<nav class="imania-footer__navigation" aria-label="<?php esc_attr_e('Menu principal do rodapé', 'imania-store'); ?>">
					<?php
					wp_nav_menu(
						array(
							'theme_location' => 'menu-1',
							'container' => false,
							'depth' => 1,
							'fallback_cb' => false,
							'menu_class' => 'imania-footer__menu',
						)
					);
					?>
				</nav>

				<?php foreach (array('footer-menu-2', 'footer-menu-3') as $footer_menu_location) : ?>
					<?php if (has_nav_menu($footer_menu_location)) : ?>
						<?php
						$footer_menu = isset($footer_locations[$footer_menu_location])
							? wp_get_nav_menu_object($footer_locations[$footer_menu_location])
							: false;
						$footer_menu_label = $footer_menu instanceof WP_Term
							? $footer_menu->name
							: __('Menu do rodapé', 'imania-store');
						?>
						<nav class="imania-footer__navigation" aria-label="<?php echo esc_attr($footer_menu_label); ?>">
							<?php
							wp_nav_menu(
								array(
									'theme_location' => $footer_menu_location,
									'container' => false,
									'depth' => 1,
									'fallback_cb' => false,
									'menu_class' => 'imania-footer__menu',
								)
							);
							?>
						</nav>
					<?php endif; ?>
				<?php endforeach; ?>

				<?php if (!empty($available_footer_badges)) : ?>
					<div class="imania-footer__security">
						<ul>
							<?php foreach ($available_footer_badges as $badge) : ?>
								<li>
									<img
										src="<?php echo esc_url($footer_badge_uri . $badge['file']); ?>"
										alt="<?php echo esc_attr($badge['alt']); ?>"
										loading="lazy"
										decoding="async"
									/>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>
				<?php endif; ?>
			</div>

			<div class="imania-footer__copyright">
				<p>
					<?php
					echo esc_html(
						sprintf(
							/* translators: %s: current year. */
							__('© %s Todos os direitos reservados.', 'imania-store'),
							wp_date('Y')
						)
					);
					?>
				</p>
			</div>
		</div>
	</footer><!-- #colophon -->

	<?php get_template_part('template-parts/modals/login-modal'); ?>
	<?php get_template_part('template-parts/modals/cart-drawer'); ?>
</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>
