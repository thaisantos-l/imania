<?php
/**
 * Login modal used by wishlist actions.
 *
 * @package Imania_Store
 */

$login_url = function_exists('imania_store_get_login_to_price_url')
	? imania_store_get_login_to_price_url()
	: (function_exists('imania_store_get_my_account_url') ? imania_store_get_my_account_url() : home_url('/conta/'));
$register_url = function_exists('imania_store_get_my_account_url') ? imania_store_get_my_account_url() : home_url('/conta/');
?>
<div class="imania-modal" data-imania-login-modal hidden aria-hidden="true">
	<div class="imania-modal__overlay" data-imania-modal-close></div>
	<div class="imania-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="imania-login-modal-title">
		<button type="button" class="imania-modal__close" data-imania-modal-close aria-label="<?php esc_attr_e('Fechar modal', 'imania-store'); ?>">&times;</button>
		<h2 id="imania-login-modal-title"><?php esc_html_e('Entre para favoritar produtos', 'imania-store'); ?></h2>
		<p><?php esc_html_e('Faça login ou crie sua conta para salvar produtos na sua wishlist.', 'imania-store'); ?></p>
		<div class="imania-modal__actions">
			<a class="imania-btn imania-btn--primary" href="<?php echo esc_url($login_url); ?>"><?php esc_html_e('Fazer login', 'imania-store'); ?></a>
			<a class="imania-btn imania-btn--outline" href="<?php echo esc_url($register_url); ?>"><?php esc_html_e('Criar conta', 'imania-store'); ?></a>
		</div>
	</div>
</div>
