<?php
/**
 * Custom checkout layout.
 *
 * This template keeps WooCommerce checkout hooks and fragments intact while
 * presenting the flow in three visual steps.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.4.0
 */

defined('ABSPATH') || exit;

do_action('woocommerce_before_checkout_form', $checkout);

if (!$checkout->is_registration_enabled() && $checkout->is_registration_required() && !is_user_logged_in()) {
	echo esc_html(apply_filters('woocommerce_checkout_must_be_logged_in_message', __('You must be logged in to checkout.', 'woocommerce')));
	return;
}
?>

<form name="checkout" method="post" class="checkout woocommerce-checkout imania-checkout" action="<?php echo esc_url(wc_get_checkout_url()); ?>" enctype="multipart/form-data" aria-labelledby="imania-checkout-title" data-imania-checkout>
	<div class="imania-checkout__viewport container">
		<header class="imania-checkout__header">
			<h1 id="imania-checkout-title"><?php esc_html_e('Finalizar compra', 'imania-store'); ?></h1>

			<div class="imania-checkout__steps-wrap">
				<nav class="imania-checkout__steps" aria-label="<?php esc_attr_e('Etapas do checkout', 'imania-store'); ?>">
					<button type="button" class="imania-checkout__step is-active" data-imania-checkout-step-target="details" aria-current="step">
						<span class="imania-checkout__step-index">1</span>
						<span><?php esc_html_e('Dados', 'imania-store'); ?></span>
					</button>
					<button type="button" class="imania-checkout__step" data-imania-checkout-step-target="payment">
						<span class="imania-checkout__step-index">2</span>
						<span><?php esc_html_e('Pagamento', 'imania-store'); ?></span>
					</button>
					<button type="button" class="imania-checkout__step" data-imania-checkout-step-target="review">
						<span class="imania-checkout__step-index">3</span>
						<span><?php esc_html_e('Revisao', 'imania-store'); ?></span>
					</button>
				</nav>
			</div>
		</header>

		<div class="imania-checkout__layout">
			<div class="imania-checkout__main">
				<?php if ($checkout->get_checkout_fields()) : ?>
					<section class="imania-checkout__panel is-active" data-imania-checkout-step-panel="details" aria-labelledby="imania-checkout-details-title">
						<h2 id="imania-checkout-details-title"><?php esc_html_e('Informacoes de entrega', 'imania-store'); ?></h2>

						<?php do_action('woocommerce_checkout_before_customer_details'); ?>

						<div id="customer_details" class="imania-checkout__fields">
							<div class="imania-checkout__fields-section">
								<?php do_action('woocommerce_checkout_billing'); ?>
							</div>

							<div class="imania-checkout__fields-section">
								<?php do_action('woocommerce_checkout_shipping'); ?>
							</div>
						</div>

						<?php do_action('woocommerce_checkout_after_customer_details'); ?>

						<div class="imania-checkout__actions">
							<button type="button" class="imania-checkout__button imania-checkout__button--primary" data-imania-checkout-next="payment">
								<?php esc_html_e('Continuar para pagamento', 'imania-store'); ?>
							</button>
						</div>
					</section>
				<?php endif; ?>

				<?php do_action('woocommerce_checkout_before_order_review_heading'); ?>

				<div id="order_review" class="woocommerce-checkout-review-order imania-checkout__review-order">
					<?php
					remove_action('woocommerce_checkout_order_review', 'woocommerce_order_review', 10);
					remove_action('woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20);
					do_action('woocommerce_checkout_before_order_review');
					do_action('woocommerce_checkout_order_review');
					add_action('woocommerce_checkout_order_review', 'woocommerce_order_review', 10);
					add_action('woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20);
					?>

					<section class="imania-checkout__panel" data-imania-checkout-step-panel="payment" aria-labelledby="imania-checkout-payment-title" hidden>
						<h2 id="imania-checkout-payment-title"><?php esc_html_e('Forma de pagamento', 'imania-store'); ?></h2>
						<?php woocommerce_checkout_payment(); ?>

						<div class="imania-checkout__actions">
							<button type="button" class="imania-checkout__button imania-checkout__button--ghost" data-imania-checkout-prev="details">
								<?php esc_html_e('Voltar', 'imania-store'); ?>
							</button>
							<button type="button" class="imania-checkout__button imania-checkout__button--primary" data-imania-checkout-next="review">
								<?php esc_html_e('Revisar pedido', 'imania-store'); ?>
							</button>
						</div>
					</section>

					<section class="imania-checkout__panel imania-checkout__panel--summary" data-imania-checkout-step-panel="review" aria-labelledby="imania-checkout-review-title" hidden>
						<h2 id="imania-checkout-review-title"><?php esc_html_e('Revisao do pedido', 'imania-store'); ?></h2>
						<?php woocommerce_order_review(); ?>

						<div class="imania-checkout__place-order" data-imania-place-order-target></div>

						<div class="imania-checkout__actions">
							<button type="button" class="imania-checkout__button imania-checkout__button--ghost" data-imania-checkout-prev="payment">
								<?php esc_html_e('Voltar', 'imania-store'); ?>
							</button>
						</div>
					</section>

					<?php do_action('woocommerce_checkout_after_order_review'); ?>
				</div>
			</div>

			<aside class="imania-checkout__aside" aria-label="<?php esc_attr_e('Resumo da compra', 'imania-store'); ?>">
				<div class="imania-checkout__aside-card">
					<h2><?php esc_html_e('Resumo da compra', 'imania-store'); ?></h2>
					<div class="imania-checkout__aside-content" data-imania-checkout-summary-clone></div>
				</div>
			</aside>
		</div>
	</div>
</form>

<?php do_action('woocommerce_after_checkout_form', $checkout); ?>
