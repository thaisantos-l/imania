<?php

namespace Imania\PricingEngine\Infrastructure\Admin;

use Imania\PricingEngine\Support\MetaKeys;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class SettingsPage {

	public function register() {
		add_action( 'admin_menu', array( $this, 'add_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	public function add_menu() {
		add_submenu_page(
			'woocommerce',
			esc_html__( 'Precificacao PF/PJ', 'imania-pricing-engine' ),
			esc_html__( 'Precificacao PF/PJ', 'imania-pricing-engine' ),
			'manage_woocommerce',
			'imania-pricing-settings',
			array( $this, 'render_page' )
		);
	}

	public function register_settings() {
		register_setting( MetaKeys::OPTION_GROUP, MetaKeys::OPTION_FALLBACK_MODE, array( 'sanitize_callback' => array( $this, 'sanitize_mode' ) ) );
		register_setting( MetaKeys::OPTION_GROUP, MetaKeys::OPTION_GLOBAL_PRICE_PF, array( 'sanitize_callback' => 'wc_format_decimal' ) );
		register_setting( MetaKeys::OPTION_GROUP, MetaKeys::OPTION_GLOBAL_PRICE_PJ, array( 'sanitize_callback' => 'wc_format_decimal' ) );
		register_setting( MetaKeys::OPTION_GROUP, MetaKeys::OPTION_GLOBAL_DISC_PF, array( 'sanitize_callback' => 'wc_format_decimal' ) );
		register_setting( MetaKeys::OPTION_GROUP, MetaKeys::OPTION_GLOBAL_DISC_PJ, array( 'sanitize_callback' => 'wc_format_decimal' ) );
		register_setting( MetaKeys::OPTION_GROUP, MetaKeys::OPTION_PRIORITY, array( 'sanitize_callback' => 'sanitize_text_field' ) );
	}

	/**
	 * @param string $mode Candidate mode.
	 *
	 * @return string
	 */
	public function sanitize_mode( $mode ) {
		$mode = sanitize_text_field( (string) $mode );
		if ( 'fixed' !== $mode && 'discount' !== $mode && '' !== $mode ) {
			return '';
		}

		return $mode;
	}

	public function render_page() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Precificacao PF/PJ', 'imania-pricing-engine' ); ?></h1>
			<form method="post" action="options.php">
				<?php settings_fields( MetaKeys::OPTION_GROUP ); ?>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="<?php echo esc_attr( MetaKeys::OPTION_FALLBACK_MODE ); ?>"><?php esc_html_e( 'Modo global de fallback', 'imania-pricing-engine' ); ?></label></th>
						<td>
							<select name="<?php echo esc_attr( MetaKeys::OPTION_FALLBACK_MODE ); ?>" id="<?php echo esc_attr( MetaKeys::OPTION_FALLBACK_MODE ); ?>">
								<option value=""><?php esc_html_e( 'Sem fallback global', 'imania-pricing-engine' ); ?></option>
								<option value="fixed" <?php selected( 'fixed', get_option( MetaKeys::OPTION_FALLBACK_MODE, '' ) ); ?>><?php esc_html_e( 'Preco fixo', 'imania-pricing-engine' ); ?></option>
								<option value="discount" <?php selected( 'discount', get_option( MetaKeys::OPTION_FALLBACK_MODE, '' ) ); ?>><?php esc_html_e( 'Desconto percentual', 'imania-pricing-engine' ); ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Preco global PF', 'imania-pricing-engine' ); ?></th>
						<td><input type="text" name="<?php echo esc_attr( MetaKeys::OPTION_GLOBAL_PRICE_PF ); ?>" value="<?php echo esc_attr( get_option( MetaKeys::OPTION_GLOBAL_PRICE_PF, '' ) ); ?>" /></td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Preco global PJ', 'imania-pricing-engine' ); ?></th>
						<td><input type="text" name="<?php echo esc_attr( MetaKeys::OPTION_GLOBAL_PRICE_PJ ); ?>" value="<?php echo esc_attr( get_option( MetaKeys::OPTION_GLOBAL_PRICE_PJ, '' ) ); ?>" /></td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Desconto global PF (%)', 'imania-pricing-engine' ); ?></th>
						<td><input type="text" name="<?php echo esc_attr( MetaKeys::OPTION_GLOBAL_DISC_PF ); ?>" value="<?php echo esc_attr( get_option( MetaKeys::OPTION_GLOBAL_DISC_PF, '' ) ); ?>" /></td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Desconto global PJ (%)', 'imania-pricing-engine' ); ?></th>
						<td><input type="text" name="<?php echo esc_attr( MetaKeys::OPTION_GLOBAL_DISC_PJ ); ?>" value="<?php echo esc_attr( get_option( MetaKeys::OPTION_GLOBAL_DISC_PJ, '' ) ); ?>" /></td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Prioridade de regra', 'imania-pricing-engine' ); ?></th>
						<td>
							<input type="text" name="<?php echo esc_attr( MetaKeys::OPTION_PRIORITY ); ?>" value="<?php echo esc_attr( get_option( MetaKeys::OPTION_PRIORITY, 'product,category,global' ) ); ?>" class="regular-text" />
							<p class="description"><?php esc_html_e( 'Padrao recomendado: product,category,global', 'imania-pricing-engine' ); ?></p>
						</td>
					</tr>
				</table>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}
}
