<?php
/**
 * Shared account authentication forms.
 *
 * @package Imania_Store
 */

defined('ABSPATH') || exit;
?>
<section class="imania-auth" data-imania-auth data-customer-type="pf">
	<div class="imania-auth__container container">
		<div class="imania-auth__switch" role="tablist" aria-label="<?php esc_attr_e('Tipo de conta', 'imania-store'); ?>">
			<button type="button" class="imania-auth__switch-btn is-active" data-imania-auth-type="pf" aria-pressed="true"><?php esc_html_e('Pessoa Fisica', 'imania-store'); ?></button>
			<button type="button" class="imania-auth__switch-btn" data-imania-auth-type="pj" aria-pressed="false"><?php esc_html_e('Pessoa Juridica', 'imania-store'); ?></button>
		</div>

		<div class="imania-auth__content">
			<div class="imania-auth__panel">
				<h2 class="imania-auth__title"><?php esc_html_e('Ja tenho conta', 'imania-store'); ?></h2>
				<form class="imania-auth__form" data-imania-auth-form="login" novalidate>
					<label class="imania-auth__label" for="imania-auth-login-document" data-imania-doc-label><?php esc_html_e('CPF', 'imania-store'); ?></label>
					<input id="imania-auth-login-document" class="imania-auth__input" type="text" name="document" data-imania-doc-placeholder autocomplete="username" placeholder="<?php esc_attr_e('Digite seu CPF', 'imania-store'); ?>" required />

					<label class="imania-auth__label" for="imania-auth-login-password"><?php esc_html_e('Senha', 'imania-store'); ?></label>
					<input id="imania-auth-login-password" class="imania-auth__input" type="password" name="password" autocomplete="current-password" required />

					<a class="imania-auth__link" href="<?php echo esc_url(wp_lostpassword_url()); ?>"><?php esc_html_e('Esqueci minha senha', 'imania-store'); ?></a>

					<button type="submit" class="imania-btn imania-btn--primary imania-btn--sm" data-loading-text="<?php esc_attr_e('Acessando...', 'imania-store'); ?>">
						<?php esc_html_e('Acessar', 'imania-store'); ?>
					</button>

					<div class="imania-auth__notice" data-imania-auth-notice="login" aria-live="polite"></div>
				</form>
			</div>

			<div class="imania-auth__divider" aria-hidden="true"></div>

			<div class="imania-auth__panel">
				<h2 class="imania-auth__title imania-auth__title--highlight"><?php esc_html_e('Criar conta', 'imania-store'); ?></h2>
				<form class="imania-auth__form" data-imania-auth-form="register" novalidate>
					<label class="imania-auth__label" for="imania-auth-register-email"><?php esc_html_e('E-mail', 'imania-store'); ?></label>
					<input id="imania-auth-register-email" class="imania-auth__input" type="email" name="email" autocomplete="email" placeholder="<?php esc_attr_e('Digite seu e-mail', 'imania-store'); ?>" required />

					<label class="imania-auth__label" for="imania-auth-register-document" data-imania-doc-label><?php esc_html_e('CPF', 'imania-store'); ?></label>
					<input id="imania-auth-register-document" class="imania-auth__input" type="text" name="document" data-imania-doc-placeholder autocomplete="off" placeholder="<?php esc_attr_e('Digite seu CPF', 'imania-store'); ?>" required />

					<label class="imania-auth__label" for="imania-auth-register-password"><?php esc_html_e('Senha', 'imania-store'); ?></label>
					<input id="imania-auth-register-password" class="imania-auth__input" type="password" name="password" autocomplete="new-password" required />

					<button type="submit" class="imania-btn imania-btn--primary imania-btn--sm" data-loading-text="<?php esc_attr_e('Processando...', 'imania-store'); ?>">
						<?php esc_html_e('Prosseguir', 'imania-store'); ?>
					</button>

					<div class="imania-auth__notice" data-imania-auth-notice="register" aria-live="polite"></div>
				</form>
			</div>
		</div>
	</div>
</section>
