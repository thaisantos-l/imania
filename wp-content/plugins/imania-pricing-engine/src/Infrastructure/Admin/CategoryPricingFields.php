<?php

namespace Imania\PricingEngine\Infrastructure\Admin;

use Imania\PricingEngine\Support\MetaKeys;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class CategoryPricingFields {

	public function register() {
		add_action( 'product_cat_add_form_fields', array( $this, 'render_add_fields' ) );
		add_action( 'product_cat_edit_form_fields', array( $this, 'render_edit_fields' ), 10, 2 );
		add_action( 'created_product_cat', array( $this, 'save_category_fields' ) );
		add_action( 'edited_product_cat', array( $this, 'save_category_fields' ) );
	}

	public function render_add_fields() {
		$this->render_fields_table();
	}

	/**
	 * @param \WP_Term $term Category term.
	 */
	public function render_edit_fields( $term ) {
		$this->render_fields_table( $term );
	}

	/**
	 * @param int $term_id Category term id.
	 */
	public function save_category_fields( $term_id ) {
		if ( ! current_user_can( 'manage_product_terms' ) ) {
			return;
		}

		if ( ! isset( $_POST['imania_category_pricing_nonce'] ) ) {
			return;
		}

		$nonce = sanitize_text_field( wp_unslash( $_POST['imania_category_pricing_nonce'] ) );
		if ( ! wp_verify_nonce( $nonce, 'imania_save_category_pricing' ) ) {
			return;
		}

		$this->save_text_meta( $term_id, MetaKeys::CATEGORY_PRICING_MODE, array( '', 'fixed', 'discount' ) );
		$this->save_decimal_meta( $term_id, MetaKeys::CATEGORY_PRICE_PF );
		$this->save_decimal_meta( $term_id, MetaKeys::CATEGORY_PRICE_PJ );
		$this->save_decimal_meta( $term_id, MetaKeys::CATEGORY_DISCOUNT_PF );
		$this->save_decimal_meta( $term_id, MetaKeys::CATEGORY_DISCOUNT_PJ );
	}

	/**
	 * @param \WP_Term|null $term Category term.
	 */
	private function render_fields_table( $term = null ) {
		$term_id  = $term instanceof \WP_Term ? $term->term_id : 0;
		$mode     = $term_id > 0 ? get_term_meta( $term_id, MetaKeys::CATEGORY_PRICING_MODE, true ) : '';
		$price_pf = $term_id > 0 ? get_term_meta( $term_id, MetaKeys::CATEGORY_PRICE_PF, true ) : '';
		$price_pj = $term_id > 0 ? get_term_meta( $term_id, MetaKeys::CATEGORY_PRICE_PJ, true ) : '';
		$disc_pf  = $term_id > 0 ? get_term_meta( $term_id, MetaKeys::CATEGORY_DISCOUNT_PF, true ) : '';
		$disc_pj  = $term_id > 0 ? get_term_meta( $term_id, MetaKeys::CATEGORY_DISCOUNT_PJ, true ) : '';

		wp_nonce_field( 'imania_save_category_pricing', 'imania_category_pricing_nonce' );

		$mode_options = array(
			''         => esc_html__( 'Herdar da prioridade global', 'imania-pricing-engine' ),
			'fixed'    => esc_html__( 'Preco fixo PF/PJ', 'imania-pricing-engine' ),
			'discount' => esc_html__( 'Desconto percentual PF/PJ', 'imania-pricing-engine' ),
		);

		$is_edit = $term instanceof \WP_Term;
		if ( ! $is_edit ) {
			?>
			<div class="form-field">
				<label for="<?php echo esc_attr( MetaKeys::CATEGORY_PRICING_MODE ); ?>"><?php esc_html_e( 'Modo de precificacao PF/PJ', 'imania-pricing-engine' ); ?></label>
				<select id="<?php echo esc_attr( MetaKeys::CATEGORY_PRICING_MODE ); ?>" name="<?php echo esc_attr( MetaKeys::CATEGORY_PRICING_MODE ); ?>">
					<?php foreach ( $mode_options as $value => $label ) : ?>
						<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $mode, $value ); ?>><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<?php
			$this->render_add_input( MetaKeys::CATEGORY_PRICE_PF, __( 'Preco PF', 'imania-pricing-engine' ), $price_pf );
			$this->render_add_input( MetaKeys::CATEGORY_PRICE_PJ, __( 'Preco PJ', 'imania-pricing-engine' ), $price_pj );
			$this->render_add_input( MetaKeys::CATEGORY_DISCOUNT_PF, __( 'Desconto PF (%)', 'imania-pricing-engine' ), $disc_pf );
			$this->render_add_input( MetaKeys::CATEGORY_DISCOUNT_PJ, __( 'Desconto PJ (%)', 'imania-pricing-engine' ), $disc_pj );
			return;
		}
		?>
		<tr class="form-field">
			<th scope="row"><label for="<?php echo esc_attr( MetaKeys::CATEGORY_PRICING_MODE ); ?>"><?php esc_html_e( 'Modo de precificacao PF/PJ', 'imania-pricing-engine' ); ?></label></th>
			<td>
				<select id="<?php echo esc_attr( MetaKeys::CATEGORY_PRICING_MODE ); ?>" name="<?php echo esc_attr( MetaKeys::CATEGORY_PRICING_MODE ); ?>">
					<?php foreach ( $mode_options as $value => $label ) : ?>
						<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $mode, $value ); ?>><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>
				<p class="description"><?php esc_html_e( 'Regra aplicada quando a prioridade incluir categoria.', 'imania-pricing-engine' ); ?></p>
			</td>
		</tr>
		<?php
		$this->render_edit_input( MetaKeys::CATEGORY_PRICE_PF, __( 'Preco PF', 'imania-pricing-engine' ), $price_pf );
		$this->render_edit_input( MetaKeys::CATEGORY_PRICE_PJ, __( 'Preco PJ', 'imania-pricing-engine' ), $price_pj );
		$this->render_edit_input( MetaKeys::CATEGORY_DISCOUNT_PF, __( 'Desconto PF (%)', 'imania-pricing-engine' ), $disc_pf );
		$this->render_edit_input( MetaKeys::CATEGORY_DISCOUNT_PJ, __( 'Desconto PJ (%)', 'imania-pricing-engine' ), $disc_pj );
	}

	/**
	 * @param string $meta_key Meta key.
	 * @param string $label Label.
	 * @param string $value Value.
	 */
	private function render_add_input( $meta_key, $label, $value ) {
		?>
		<div class="form-field">
			<label for="<?php echo esc_attr( $meta_key ); ?>"><?php echo esc_html( $label ); ?></label>
			<input type="text" id="<?php echo esc_attr( $meta_key ); ?>" name="<?php echo esc_attr( $meta_key ); ?>" value="<?php echo esc_attr( $value ); ?>" inputmode="decimal" />
		</div>
		<?php
	}

	/**
	 * @param string $meta_key Meta key.
	 * @param string $label Label.
	 * @param string $value Value.
	 */
	private function render_edit_input( $meta_key, $label, $value ) {
		?>
		<tr class="form-field">
			<th scope="row"><label for="<?php echo esc_attr( $meta_key ); ?>"><?php echo esc_html( $label ); ?></label></th>
			<td><input type="text" id="<?php echo esc_attr( $meta_key ); ?>" name="<?php echo esc_attr( $meta_key ); ?>" value="<?php echo esc_attr( $value ); ?>" inputmode="decimal" /></td>
		</tr>
		<?php
	}

	/**
	 * @param int      $term_id Term id.
	 * @param string   $meta_key Meta key.
	 * @param string[] $allowed Allowed values.
	 */
	private function save_text_meta( $term_id, $meta_key, array $allowed ) {
		$raw = isset( $_POST[ $meta_key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $meta_key ] ) ) : '';
		if ( ! in_array( $raw, $allowed, true ) ) {
			$raw = '';
		}

		if ( '' === $raw ) {
			delete_term_meta( $term_id, $meta_key );
			return;
		}

		update_term_meta( $term_id, $meta_key, $raw );
	}

	/**
	 * @param int    $term_id Term id.
	 * @param string $meta_key Meta key.
	 */
	private function save_decimal_meta( $term_id, $meta_key ) {
		$raw = isset( $_POST[ $meta_key ] ) ? wc_format_decimal( wp_unslash( $_POST[ $meta_key ] ) ) : '';
		if ( '' === $raw ) {
			delete_term_meta( $term_id, $meta_key );
			return;
		}

		update_term_meta( $term_id, $meta_key, $raw );
	}
}
