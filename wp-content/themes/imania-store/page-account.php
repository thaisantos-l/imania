<?php
/**
 * Unified account page template.
 *
 * @package Imania_Store
 */

defined('ABSPATH') || exit;

get_header();
?>

<main id="primary" class="site-main <?php echo imania_store_should_render_auth_form() ? 'imania-auth-page' : 'imania-account-page'; ?>">
	<?php if (imania_store_should_render_auth_form()) : ?>
		<?php get_template_part('template-parts/account/auth'); ?>
	<?php else : ?>
		<?php echo do_shortcode('[woocommerce_my_account]'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	<?php endif; ?>
</main>

<?php
get_footer();
