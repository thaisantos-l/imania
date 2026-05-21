<?php

namespace Imania\PricingEngine\Infrastructure\Woo;

use Imania\PricingEngine\Domain\Customer\CustomerTypeResolver;
use Imania\PricingEngine\Domain\Order\FirstPurchaseChecker;
use Imania\PricingEngine\Support\MetaKeys;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class CommercialRulesManager {
	const CHANNEL_VAREJO  = 'varejo';
	const CHANNEL_ATACADO = 'atacado';

	/**
	 * @var CustomerTypeResolver
	 */
	private $customer_type_resolver;

	/**
	 * @var FirstPurchaseChecker
	 */
	private $first_purchase_checker;

	public function __construct( CustomerTypeResolver $customer_type_resolver, FirstPurchaseChecker $first_purchase_checker ) {
		$this->customer_type_resolver = $customer_type_resolver;
		$this->first_purchase_checker = $first_purchase_checker;
	}

	public function register() {
		add_action( 'woocommerce_check_cart_items', array( $this, 'validate_cart_rules' ) );
		add_action( 'woocommerce_cart_calculate_fees', array( $this, 'apply_discounts' ), 30, 1 );
		add_filter( 'woocommerce_quantity_input_args', array( $this, 'adjust_quantity_min_for_atacado' ), 20, 2 );
		add_filter( 'woocommerce_add_to_cart_validation', array( $this, 'validate_add_to_cart_quantity' ), 20, 3 );
	}

	/**
	 * @param \WC_Cart $cart Cart.
	 */
	public function validate_cart_rules( $cart = null ) {
		if ( ! is_user_logged_in() || ! $this->is_cart_available() ) {
			return;
		}

		$cart = $cart instanceof \WC_Cart ? $cart : WC()->cart;
		if ( ! $cart instanceof \WC_Cart ) {
			return;
		}

		$customer_type = $this->customer_type_resolver->resolve();
		if ( ! $this->customer_type_resolver->is_valid( $customer_type ) ) {
			return;
		}

		$minimum = $this->get_minimum_order_value( $customer_type );
		$subtotal = $this->get_products_subtotal( $cart );

		if ( $subtotal + 0.0001 < $minimum ) {
			/* translators: 1: channel label, 2: minimum amount */
			wc_add_notice(
				sprintf(
					esc_html__( '%1$s exige pedido minimo de %2$s (sem frete).', 'imania-pricing-engine' ),
					esc_html( $this->get_channel_label( $customer_type ) ),
					wp_kses_post( wc_price( $minimum ) )
				),
				'error'
			);
		}

		if ( CustomerTypeResolver::PF !== $customer_type ) {
			return;
		}

		$min_units = $this->get_atacado_min_units();
		$invalid_products = array();
		foreach ( $cart->get_cart() as $item ) {
			$quantity = isset( $item['quantity'] ) ? (int) $item['quantity'] : 0;
			if ( $quantity <= 0 || $quantity >= $min_units ) {
				continue;
			}

			if ( empty( $item['data'] ) || ! $item['data'] instanceof \WC_Product ) {
				continue;
			}

			$invalid_products[] = $item['data']->get_name();
		}

		if ( empty( $invalid_products ) ) {
			return;
		}

		/* translators: 1: minimum units, 2: products list */
		wc_add_notice(
			sprintf(
				esc_html__( 'Atacado exige minimo de %1$d unidades por produto. Ajuste: %2$s.', 'imania-pricing-engine' ),
				(int) $min_units,
				esc_html( implode( ', ', array_unique( $invalid_products ) ) )
			),
			'error'
		);
	}

	/**
	 * @param \WC_Cart $cart Cart.
	 */
	public function apply_discounts( $cart ) {
		if ( is_admin() && ! wp_doing_ajax() ) {
			return;
		}

		if ( ! $cart instanceof \WC_Cart || ! is_user_logged_in() ) {
			return;
		}

		$customer_type = $this->customer_type_resolver->resolve();
		if ( ! $this->customer_type_resolver->is_valid( $customer_type ) ) {
			return;
		}

		$subtotal = $this->get_products_subtotal( $cart );
		$minimum  = $this->get_minimum_order_value( $customer_type );
		if ( $subtotal + 0.0001 < $minimum ) {
			return;
		}

		$this->apply_ten_plus_two_discount( $cart );
		$this->apply_first_purchase_discount( $cart, $customer_type, $subtotal );
	}

	/**
	 * @param array       $args Quantity args.
	 * @param \WC_Product $product Product.
	 *
	 * @return array
	 */
	public function adjust_quantity_min_for_atacado( $args, $product ) {
		if ( ! is_user_logged_in() ) {
			return $args;
		}

		$customer_type = $this->customer_type_resolver->resolve();
		if ( CustomerTypeResolver::PF !== $customer_type ) {
			return $args;
		}

		$min_units          = $this->get_atacado_min_units();
		$args['min_value']  = max( isset( $args['min_value'] ) ? (int) $args['min_value'] : 1, $min_units );
		$args['input_value'] = max( isset( $args['input_value'] ) ? (int) $args['input_value'] : $min_units, $min_units );

		return $args;
	}

	/**
	 * @param bool  $passed Current validation flag.
	 * @param int   $product_id Product id.
	 * @param int   $quantity Quantity.
	 *
	 * @return bool
	 */
	public function validate_add_to_cart_quantity( $passed, $product_id, $quantity ) {
		if ( ! $passed || ! is_user_logged_in() ) {
			return $passed;
		}

		$customer_type = $this->customer_type_resolver->resolve();
		if ( CustomerTypeResolver::PF !== $customer_type ) {
			return $passed;
		}

		$min_units = $this->get_atacado_min_units();
		if ( (int) $quantity >= $min_units ) {
			return $passed;
		}

		/* translators: %d: minimum units */
		wc_add_notice(
			sprintf(
				esc_html__( 'Para atacado, adicione no minimo %d unidades deste produto.', 'imania-pricing-engine' ),
				(int) $min_units
			),
			'error'
		);

		return false;
	}

	/**
	 * @param \WC_Cart $cart Cart.
	 */
	private function apply_ten_plus_two_discount( $cart ) {
		$enabled = (int) get_option( MetaKeys::OPTION_PROMO_TEN_PLUS_TWO_ENABLED, 1 );
		if ( 1 !== $enabled ) {
			return;
		}

		$discount = 0.0;
		foreach ( $cart->get_cart() as $item ) {
			if ( empty( $item['data'] ) || ! $item['data'] instanceof \WC_Product ) {
				continue;
			}

			$quantity = isset( $item['quantity'] ) ? (int) $item['quantity'] : 0;
			if ( $quantity < 12 ) {
				continue;
			}

			$bonus_units = (int) floor( $quantity / 12 ) * 2;
			if ( $bonus_units <= 0 ) {
				continue;
			}

			$line_total = isset( $item['line_total'] ) ? (float) $item['line_total'] : 0.0;
			if ( $line_total <= 0 ) {
				continue;
			}

			$unit_price = $line_total / max( 1, $quantity );
			$discount  += $unit_price * $bonus_units;
		}

		if ( $discount <= 0 ) {
			return;
		}

		$cart->add_fee( esc_html__( 'Promocao 10+2 Imania', 'imania-pricing-engine' ), -1 * $discount, false );
	}

	/**
	 * @param \WC_Cart $cart Cart.
	 * @param string   $customer_type pf|pj.
	 * @param float    $subtotal Subtotal without shipping.
	 */
	private function apply_first_purchase_discount( $cart, $customer_type, $subtotal ) {
		if ( CustomerTypeResolver::PJ !== $customer_type ) {
			return;
		}

		$discount_percent = (float) get_option( MetaKeys::OPTION_FIRST_PURCHASE_PERCENT, 10 );
		$discount_percent = max( 0, min( 100, $discount_percent ) );
		if ( $discount_percent <= 0 ) {
			return;
		}

		$user_id = get_current_user_id();
		if ( $user_id <= 0 ) {
			return;
		}

		$user      = get_userdata( $user_id );
		$email     = $user instanceof \WP_User ? (string) $user->user_email : '';
		$document  = (string) get_user_meta( $user_id, MetaKeys::DOCUMENT_NUMBER, true );
		$customer  = function_exists( 'WC' ) ? WC()->customer : null;

		if ( $customer instanceof \WC_Customer ) {
			$billing_email = (string) $customer->get_billing_email();
			if ( '' !== $billing_email ) {
				$email = $billing_email;
			}

			$billing_cpf  = (string) $customer->get_meta( 'billing_cpf', true );
			$billing_cnpj = (string) $customer->get_meta( 'billing_cnpj', true );
			if ( '' !== $billing_cpf ) {
				$document = preg_replace( '/\D+/', '', $billing_cpf );
			} elseif ( '' !== $billing_cnpj ) {
				$document = preg_replace( '/\D+/', '', $billing_cnpj );
			}
		}

		if ( ! $this->first_purchase_checker->is_first_purchase( $user_id, $email, (string) $document ) ) {
			return;
		}

		$discount_value = $subtotal * ( $discount_percent / 100 );
		if ( $discount_value <= 0 ) {
			return;
		}

		/* translators: %s: discount percent */
		$label = sprintf( esc_html__( 'Primeira compra (%s%%)', 'imania-pricing-engine' ), wc_format_decimal( $discount_percent, 0 ) );
		$cart->add_fee( $label, -1 * $discount_value, false );
	}

	/**
	 * @param string $customer_type pf|pj.
	 *
	 * @return float
	 */
	private function get_minimum_order_value( $customer_type ) {
		if ( CustomerTypeResolver::PF === $customer_type ) {
			return (float) get_option( MetaKeys::OPTION_ATACADO_MINIMUM, 350 );
		}

		return (float) get_option( MetaKeys::OPTION_VAREJO_MINIMUM, 49.9 );
	}

	/**
	 * @param \WC_Cart $cart Cart.
	 *
	 * @return float
	 */
	private function get_products_subtotal( $cart ) {
		$subtotal = (float) $cart->get_cart_contents_total();
		if ( $subtotal > 0 ) {
			return $subtotal;
		}

		return (float) $cart->get_subtotal();
	}

	/**
	 * @param string $customer_type pf|pj.
	 *
	 * @return string
	 */
	private function get_channel_label( $customer_type ) {
		if ( CustomerTypeResolver::PF === $customer_type ) {
			return esc_html__( 'Atacado', 'imania-pricing-engine' );
		}

		return esc_html__( 'Varejo', 'imania-pricing-engine' );
	}

	/**
	 * @return int
	 */
	private function get_atacado_min_units() {
		$units = (int) get_option( MetaKeys::OPTION_ATACADO_MIN_UNITS, 3 );
		return max( 1, $units );
	}

	/**
	 * @return bool
	 */
	private function is_cart_available() {
		return function_exists( 'WC' ) && WC()->cart instanceof \WC_Cart;
	}
}
