<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package Imania_Store
 */

?>

	<footer id="colophon" class="site-footer imania-footer">
		<div class="container">
			<div class="row g-3 align-items-center">
				<div class="col-12 col-md-6">
					<p class="imania-footer__brand"><?php bloginfo( 'name' ); ?></p>
					<p class="imania-footer__text"><?php bloginfo( 'description' ); ?></p>
				</div>
				<div class="col-12 col-md-6 text-md-end">
					<p class="imania-footer__text">
						<?php
						echo esc_html(
							sprintf(
								/* translators: %s: current year. */
								__( '© %s Todos os direitos reservados.', 'imania-store' ),
								wp_date( 'Y' )
							)
						);
						?>
					</p>
				</div>
			</div>
		</div>
	</footer><!-- #colophon -->
</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>
