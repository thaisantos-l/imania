<?php

namespace Imania\PricingEngine\Infrastructure\Admin;

use Imania\PricingEngine\Support\MetaKeys;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class ProductPricingFields {

	public function register() {
		add_action( 'woocommerce_product_options_pricing', array( $this, 'render_product_fields' ) );
		add_action( 'woocommerce_variation_options_pricing', array( $this, 'render_variation_fields' ), 20, 3 );
		add_action( 'woocommerce_admin_process_product_object', array( $this, 'save_product_fields' ) );
		add_action( 'woocommerce_save_product_variation', array( $this, 'save_variation_fields' ), 10, 2 );
	}

	public function render_product_fields() {
		echo '<div class="options_group">';

		woocommerce_wp_select(
			array(
				'id'          => MetaKeys::PRICING_MODE,
				'label'       => esc_html__( 'Modo PF/PJ', 'imania-pricing-engine' ),
				'description' => esc_html__( 'Escolha entre preco fixo por tipo ou desconto percentual sobre o preco base.', 'imania-pricing-engine' ),
				'options'     => array(
					''         => esc_html__( 'Padrao WooCommerce', 'imania-pricing-engine' ),
					'fixed'    => esc_html__( 'Preco fixo', 'imania-pricing-engine' ),
					'discount' => esc_html__( 'Desconto percentual', 'imania-pricing-engine' ),
				),
			)
		);

		woocommerce_wp_text_input(
			array(
				'id'                => MetaKeys::PRICE_PF,
				'label'             => esc_html__( 'Preco PF', 'imania-pricing-engine' ),
				'desc_tip'          => true,
				'description'       => esc_html__( 'Valor para clientes PF quando o modo for preco fixo.', 'imania-pricing-engine' ),
				'type'              => 'text',
				'custom_attributes' => array( 'inputmode' => 'decimal' ),
			)
		);

		woocommerce_wp_text_input(
			array(
				'id'                => MetaKeys::PRICE_PJ,
				'label'             => esc_html__( 'Preco PJ', 'imania-pricing-engine' ),
				'desc_tip'          => true,
				'description'       => esc_html__( 'Valor para clientes PJ quando o modo for preco fixo.', 'imania-pricing-engine' ),
				'type'              => 'text',
				'custom_attributes' => array( 'inputmode' => 'decimal' ),
			)
		);

		woocommerce_wp_text_input(
			array(
				'id'                => MetaKeys::DISCOUNT_PF,
				'label'             => esc_html__( 'Desconto PF (%)', 'imania-pricing-engine' ),
				'desc_tip'          => true,
				'description'       => esc_html__( 'Percentual de desconto para PF quando o modo for desconto.', 'imania-pricing-engine' ),
				'type'              => 'text',
				'custom_attributes' => array( 'inputmode' => 'decimal' ),
			)
		);

		woocommerce_wp_text_input(
			array(
				'id'                => MetaKeys::DISCOUNT_PJ,
				'label'             => esc_html__( 'Desconto PJ (%)', 'imania-pricing-engine' ),
				'desc_tip'          => true,
				'description'       => esc_html__( 'Percentual de desconto para PJ quando o modo for desconto.', 'imania-pricing-engine' ),
				'type'              => 'text',
				'custom_attributes' => array( 'inputmode' => 'decimal' ),
			)
		);

		echo '</div>';
	}

	/**
	 * @param int     $loop Index.
	 * @param array   $variation_data Variation data.
	 * @param \WP_Post $variation Variation post object.
	 */
	public function render_variation_fields( $loop, $variation_data, $variation ) {
		$variation_id = $variation->ID;

		woocommerce_wp_select(
			array(
				'id'            => MetaKeys::PRICING_MODE . '[' . $variation_id . ']',
				'label'         => esc_html__( 'Modo PF/PJ', 'imania-pricing-engine' ),
				'wrapper_class' => 'form-row form-row-full',
				'value'         => get_post_meta( $variation_id, MetaKeys::PRICING_MODE, true ),
				'options'       => array(
					''         => esc_html__( 'Herdar do produto', 'imania-pricing-engine' ),
					'fixed'    => esc_html__( 'Preco fixo', 'imania-pricing-engine' ),
					'discount' => esc_html__( 'Desconto percentual', 'imania-pricing-engine' ),
				),
			)
		);

		$this->render_variation_input( $variation_id, MetaKeys::PRICE_PF, esc_html__( 'Preco PF', 'imania-pricing-engine' ) );
		$this->render_variation_input( $variation_id, MetaKeys::PRICE_PJ, esc_html__( 'Preco PJ', 'imania-pricing-engine' ) );
		$this->render_variation_input( $variation_id, MetaKeys::DISCOUNT_PF, esc_html__( 'Desconto PF (%)', 'imania-pricing-engine' ) );
		$this->render_variation_input( $variation_id, MetaKeys::DISCOUNT_PJ, esc_html__( 'Desconto PJ (%)', 'imania-pricing-engine' ) );
	}

	/**
	 * @param int    $variation_id Variation ID.
	 * @param string $meta_key Meta key.
	 * @param string $label Label.
	 */
	private function render_variation_input( $variation_id, $meta_key, $label ) {
		woocommerce_wp_text_input(
			array(
				'id'                => $meta_key . '[' . $variation_id . ']',
				'label'             => $label,
				'wrapper_class'     => 'form-row form-row-first',
				'value'             => get_post_meta( $variation_id, $meta_key, true ),
				'type'              => 'text',
				'custom_attributes' => array( 'inputmode' => 'decimal' ),
			)
		);
	}

	/**
	 * @param \WC_Product $product Product object.
	 */
	public function save_product_fields( $product ) {
		if ( ! current_user_can( 'edit_post', $product->get_id() ) ) {
			return;
		}

		$this->save_meta_from_post( $product->get_id(), MetaKeys::PRICING_MODE );
		$this->save_decimal_meta_from_post( $product->get_id(), MetaKeys::PRICE_PF );
		$this->save_decimal_meta_from_post( $product->get_id(), MetaKeys::PRICE_PJ );
		$this->save_decimal_meta_from_post( $product->get_id(), MetaKeys::DISCOUNT_PF );
		$this->save_decimal_meta_from_post( $product->get_id(), MetaKeys::DISCOUNT_PJ );
	}

	/**
	 * @param int $variation_id Variation ID.
	 */
	public function save_variation_fields( $variation_id ) {
		if ( ! current_user_can( 'edit_post', $variation_id ) ) {
			return;
		}

		$this->save_meta_from_variation_post( $variation_id, MetaKeys::PRICING_MODE );
		$this->save_decimal_meta_from_variation_post( $variation_id, MetaKeys::PRICE_PF );
		$this->save_decimal_meta_from_variation_post( $variation_id, MetaKeys::PRICE_PJ );
		$this->save_decimal_meta_from_variation_post( $variation_id, MetaKeys::DISCOUNT_PF );
		$this->save_decimal_meta_from_variation_post( $variation_id, MetaKeys::DISCOUNT_PJ );
	}

	/**
	 * @param int    $post_id Post ID.
	 * @param string $meta_key Meta key.
	 */
	private function save_meta_from_post( $post_id, $meta_key ) {
		$raw = isset( $_POST[ $meta_key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $meta_key ] ) ) : '';
		if ( MetaKeys::PRICING_MODE === $meta_key && ! in_array( $raw, array( '', 'fixed', 'discount' ), true ) ) {
			$raw = '';
		}

		if ( '' === $raw ) {
			delete_post_meta( $post_id, $meta_key );
			return;
		}

		update_post_meta( $post_id, $meta_key, $raw );
	}

	/**
	 * @param int    $post_id Post ID.
	 * @param string $meta_key Meta key.
	 */
	private function save_decimal_meta_from_post( $post_id, $meta_key ) {
		$raw = isset( $_POST[ $meta_key ] ) ? wc_format_decimal( wp_unslash( $_POST[ $meta_key ] ) ) : '';
		if ( '' === $raw ) {
			delete_post_meta( $post_id, $meta_key );
			return;
		}

		update_post_meta( $post_id, $meta_key, $raw );
	}

	/**
	 * @param int    $post_id Variation ID.
	 * @param string $meta_key Meta key.
	 */
	private function save_meta_from_variation_post( $post_id, $meta_key ) {
		$raw = isset( $_POST[ $meta_key ][ $post_id ] ) ? sanitize_text_field( wp_unslash( $_POST[ $meta_key ][ $post_id ] ) ) : '';
		if ( MetaKeys::PRICING_MODE === $meta_key && ! in_array( $raw, array( '', 'fixed', 'discount' ), true ) ) {
			$raw = '';
		}

		if ( '' === $raw ) {
			delete_post_meta( $post_id, $meta_key );
			return;
		}

		update_post_meta( $post_id, $meta_key, $raw );
	}

	/**
	 * @param int    $post_id Variation ID.
	 * @param string $meta_key Meta key.
	 */
	private function save_decimal_meta_from_variation_post( $post_id, $meta_key ) {
		$raw = isset( $_POST[ $meta_key ][ $post_id ] ) ? wc_format_decimal( wp_unslash( $_POST[ $meta_key ][ $post_id ] ) ) : '';
		if ( '' === $raw ) {
			delete_post_meta( $post_id, $meta_key );
			return;
		}

		update_post_meta( $post_id, $meta_key, $raw );
	}
}
