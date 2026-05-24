<?php
/**
 * Account profile endpoint content.
 *
 * @package Imania_Store
 */

defined('ABSPATH') || exit;

$user = isset($user) && $user instanceof WP_User ? $user : wp_get_current_user();

$document_label = isset($document_label) ? (string) $document_label : 'CPF';
$document_value = isset($document_value) ? (string) $document_value : '';
$phone = isset($phone) ? (string) $phone : '';
$address_1 = isset($address_1) ? (string) $address_1 : '';
$address_2 = isset($address_2) ? (string) $address_2 : '';
$number = isset($number) ? (string) $number : '';
$neighborhood = isset($neighborhood) ? (string) $neighborhood : '';
$postcode = isset($postcode) ? (string) $postcode : '';
$city = isset($city) ? (string) $city : '';
$state = isset($state) ? (string) $state : '';
$country = isset($country) ? (string) $country : '';
?>
<section class="imania-account-profile" aria-labelledby="imania-account-profile-title">
	<h2 id="imania-account-profile-title"><?php esc_html_e('Informações do Perfil', 'imania-store'); ?></h2>

	<form class="imania-account-profile__form" method="post" action="" novalidate>
		<?php wp_nonce_field('imania_account_profile_nonce', 'imania_account_profile_nonce'); ?>
		<div class="imania-account-profile__grid imania-account-profile__grid--2">
			<div class="imania-account-profile__field">
				<label for="imania_profile_first_name"><?php esc_html_e('Nome', 'imania-store'); ?></label>
				<input type="text" id="imania_profile_first_name" name="account_first_name" value="<?php echo esc_attr($user->first_name); ?>" />
			</div>
			<div class="imania-account-profile__field">
				<label for="imania_profile_last_name"><?php esc_html_e('Sobrenome', 'imania-store'); ?></label>
				<input type="text" id="imania_profile_last_name" name="account_last_name" value="<?php echo esc_attr($user->last_name); ?>" />
			</div>
		</div>

		<div class="imania-account-profile__grid imania-account-profile__grid--2">
			<div class="imania-account-profile__field">
				<label for="imania_profile_document"><?php echo esc_html($document_label); ?></label>
				<input type="text" id="imania_profile_document" name="imania_document" value="<?php echo esc_attr($document_value); ?>" />
			</div>
			<div class="imania-account-profile__field">
				<label for="imania_profile_phone"><?php esc_html_e('Telefone', 'imania-store'); ?></label>
				<input type="text" id="imania_profile_phone" name="billing_phone" value="<?php echo esc_attr($phone); ?>" />
			</div>
		</div>

		<div class="imania-account-profile__grid imania-account-profile__grid--2">
			<div class="imania-account-profile__field">
				<label for="imania_profile_email"><?php esc_html_e('E-mail', 'imania-store'); ?></label>
				<input type="email" id="imania_profile_email" name="account_email" value="<?php echo esc_attr($user->user_email); ?>" />
				<a href="<?php echo esc_url(wc_get_account_endpoint_url('edit-account')); ?>"><?php esc_html_e('Alterar meu e-mail', 'imania-store'); ?></a>
			</div>
			<div class="imania-account-profile__field">
				<label for="imania_profile_password"><?php esc_html_e('Senha', 'imania-store'); ?></label>
				<input type="password" id="imania_profile_password" name="password_1" value="" autocomplete="new-password" />
				<a href="<?php echo esc_url(wc_get_account_endpoint_url('edit-account')); ?>"><?php esc_html_e('Alterar minha senha', 'imania-store'); ?></a>
			</div>
		</div>

		<div class="imania-account-profile__grid imania-account-profile__grid--address">
			<div class="imania-account-profile__field">
				<label for="imania_profile_address_1"><?php esc_html_e('Endereço', 'imania-store'); ?></label>
				<input type="text" id="imania_profile_address_1" name="billing_address_1" value="<?php echo esc_attr($address_1); ?>" />
			</div>
			<div class="imania-account-profile__field">
				<label for="imania_profile_number"><?php esc_html_e('Nº', 'imania-store'); ?></label>
				<input type="text" id="imania_profile_number" name="billing_number" value="<?php echo esc_attr($number); ?>" />
			</div>
		</div>

		<div class="imania-account-profile__field">
			<label for="imania_profile_address_2"><?php esc_html_e('Complemento', 'imania-store'); ?></label>
			<input type="text" id="imania_profile_address_2" name="billing_address_2" value="<?php echo esc_attr($address_2); ?>" />
		</div>

		<div class="imania-account-profile__grid imania-account-profile__grid--address">
			<div class="imania-account-profile__field">
				<label for="imania_profile_neighborhood"><?php esc_html_e('Bairro', 'imania-store'); ?></label>
				<input type="text" id="imania_profile_neighborhood" name="billing_neighborhood" value="<?php echo esc_attr($neighborhood); ?>" />
			</div>
			<div class="imania-account-profile__field">
				<label for="imania_profile_postcode"><?php esc_html_e('CEP', 'imania-store'); ?></label>
				<input type="text" id="imania_profile_postcode" name="billing_postcode" value="<?php echo esc_attr($postcode); ?>" />
			</div>
		</div>

		<div class="imania-account-profile__grid imania-account-profile__grid--3">
			<div class="imania-account-profile__field">
				<label for="imania_profile_city"><?php esc_html_e('Cidade', 'imania-store'); ?></label>
				<input type="text" id="imania_profile_city" name="billing_city" value="<?php echo esc_attr($city); ?>" />
			</div>
			<div class="imania-account-profile__field">
				<label for="imania_profile_state"><?php esc_html_e('Estado', 'imania-store'); ?></label>
				<input type="text" id="imania_profile_state" name="billing_state" value="<?php echo esc_attr($state); ?>" />
			</div>
			<div class="imania-account-profile__field">
				<label for="imania_profile_country"><?php esc_html_e('País', 'imania-store'); ?></label>
				<input type="text" id="imania_profile_country" name="billing_country_label" value="<?php echo esc_attr($country); ?>" />
			</div>
		</div>

		<div class="imania-account-profile__actions">
			<button type="reset" class="imania-btn imania-btn--ghost"><?php esc_html_e('Cancelar', 'imania-store'); ?></button>
			<button type="button" class="imania-btn imania-btn--primary"><?php esc_html_e('Salvar', 'imania-store'); ?></button>
		</div>
	</form>
</section>
