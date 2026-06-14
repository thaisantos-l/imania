<?php

namespace Imania\PricingEngine\Infrastructure\Admin;

use Imania\PricingEngine\Support\MetaKeys;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class BusinessRulesSettingsPage {

	public function register() {
		add_action( 'admin_menu', array( $this, 'add_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	public function add_menu() {
		add_submenu_page(
			'woocommerce',
			esc_html__( 'Regras Comerciais V2', 'imania-pricing-engine' ),
			esc_html__( 'Regras Comerciais V2', 'imania-pricing-engine' ),
			'manage_woocommerce',
			'imania-business-rules-v2',
			array( $this, 'render_page' )
		);
	}

	public function register_settings() {
		register_setting( MetaKeys::OPTION_GROUP, MetaKeys::OPTION_VAREJO_MINIMUM, array( 'sanitize_callback' => 'wc_format_decimal' ) );
		register_setting( MetaKeys::OPTION_GROUP, MetaKeys::OPTION_ATACADO_MINIMUM, array( 'sanitize_callback' => 'wc_format_decimal' ) );
		register_setting( MetaKeys::OPTION_GROUP, MetaKeys::OPTION_FIRST_PURCHASE_PERCENT, array( 'sanitize_callback' => array( $this, 'sanitize_discount_percent' ) ) );
		register_setting( MetaKeys::OPTION_GROUP, MetaKeys::OPTION_ATACADO_MIN_UNITS, array( 'sanitize_callback' => array( $this, 'sanitize_units' ) ) );
		register_setting( MetaKeys::OPTION_GROUP, MetaKeys::OPTION_PROMO_TEN_PLUS_TWO_ENABLED, array( 'sanitize_callback' => array( $this, 'sanitize_toggle' ) ) );
	}

	/**
	 * @param mixed $value Candidate discount.
	 *
	 * @return string
	 */
	public function sanitize_discount_percent( $value ) {
		$discount = (float) wc_format_decimal( $value );
		$discount = max( 0, min( 100, $discount ) );
		return (string) wc_format_decimal( $discount );
	}

	/**
	 * @param mixed $value Candidate units.
	 *
	 * @return int
	 */
	public function sanitize_units( $value ) {
		$units = (int) $value;
		return max( 1, $units );
	}

	/**
	 * @param mixed $value Candidate toggle.
	 *
	 * @return int
	 */
	public function sanitize_toggle( $value ) {
		return (int) ( 1 === (int) $value );
	}

	public function render_page() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Regras Comerciais V2 (Varejo/Atacado)', 'imania-pricing-engine' ); ?></h1>
			<form method="post" action="options.php">
				<?php settings_fields( MetaKeys::OPTION_GROUP ); ?>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="<?php echo esc_attr( MetaKeys::OPTION_VAREJO_MINIMUM ); ?>"><?php esc_html_e( 'Pedido minimo Varejo (PJ)', 'imania-pricing-engine' ); ?></label></th>
						<td>
							<input type="text" id="<?php echo esc_attr( MetaKeys::OPTION_VAREJO_MINIMUM ); ?>" name="<?php echo esc_attr( MetaKeys::OPTION_VAREJO_MINIMUM ); ?>" value="<?php echo esc_attr( get_option( MetaKeys::OPTION_VAREJO_MINIMUM, '49.90' ) ); ?>" />
							<p class="description"><?php esc_html_e( 'Valor minimo sem frete para Varejo.', 'imania-pricing-engine' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="<?php echo esc_attr( MetaKeys::OPTION_ATACADO_MINIMUM ); ?>"><?php esc_html_e( 'Pedido minimo Atacado (PF)', 'imania-pricing-engine' ); ?></label></th>
						<td>
							<input type="text" id="<?php echo esc_attr( MetaKeys::OPTION_ATACADO_MINIMUM ); ?>" name="<?php echo esc_attr( MetaKeys::OPTION_ATACADO_MINIMUM ); ?>" value="<?php echo esc_attr( get_option( MetaKeys::OPTION_ATACADO_MINIMUM, '350.00' ) ); ?>" />
							<p class="description"><?php esc_html_e( 'Valor minimo sem frete para Atacado.', 'imania-pricing-engine' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="<?php echo esc_attr( MetaKeys::OPTION_FIRST_PURCHASE_PERCENT ); ?>"><?php esc_html_e( 'Desconto de primeira compra Varejo (%)', 'imania-pricing-engine' ); ?></label></th>
						<td>
							<input type="text" id="<?php echo esc_attr( MetaKeys::OPTION_FIRST_PURCHASE_PERCENT ); ?>" name="<?php echo esc_attr( MetaKeys::OPTION_FIRST_PURCHASE_PERCENT ); ?>" value="<?php echo esc_attr( get_option( MetaKeys::OPTION_FIRST_PURCHASE_PERCENT, '10' ) ); ?>" />
							<p class="description"><?php esc_html_e( 'Aplicado automaticamente para PJ na primeira compra elegivel.', 'imania-pricing-engine' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="<?php echo esc_attr( MetaKeys::OPTION_ATACADO_MIN_UNITS ); ?>"><?php esc_html_e( 'Minimo de unidades por produto (Atacado)', 'imania-pricing-engine' ); ?></label></th>
						<td>
							<input type="number" min="1" id="<?php echo esc_attr( MetaKeys::OPTION_ATACADO_MIN_UNITS ); ?>" name="<?php echo esc_attr( MetaKeys::OPTION_ATACADO_MIN_UNITS ); ?>" value="<?php echo esc_attr( get_option( MetaKeys::OPTION_ATACADO_MIN_UNITS, 3 ) ); ?>" />
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="<?php echo esc_attr( MetaKeys::OPTION_PROMO_TEN_PLUS_TWO_ENABLED ); ?>"><?php esc_html_e( 'Promocao 10 + 2', 'imania-pricing-engine' ); ?></label></th>
						<td>
							<label>
								<input type="checkbox" id="<?php echo esc_attr( MetaKeys::OPTION_PROMO_TEN_PLUS_TWO_ENABLED ); ?>" name="<?php echo esc_attr( MetaKeys::OPTION_PROMO_TEN_PLUS_TWO_ENABLED ); ?>" value="1" <?php checked( 1, (int) get_option( MetaKeys::OPTION_PROMO_TEN_PLUS_TWO_ENABLED, 1 ) ); ?> />
								<?php esc_html_e( 'Ativar desconto automatico da promocao 10 + 2 para todos os produtos.', 'imania-pricing-engine' ); ?>
							</label>
						</td>
					</tr>
				</table>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}
}
