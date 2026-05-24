<?php

namespace Imania\PricingEngine\Infrastructure\Auth;

use Imania\PricingEngine\Domain\Customer\CustomerTypeResolver;
use Imania\PricingEngine\Domain\Customer\DocumentRepository;
use Imania\PricingEngine\Domain\Customer\DocumentValidator;
use Imania\PricingEngine\Support\MetaKeys;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class RegistrationHandler {

	/**
	 * @var CustomerTypeResolver
	 */
	private $type_resolver;

	/**
	 * @var DocumentValidator
	 */
	private $document_validator;

	/**
	 * @var DocumentRepository
	 */
	private $document_repository;

	public function __construct( CustomerTypeResolver $type_resolver, DocumentValidator $document_validator, DocumentRepository $document_repository ) {
		$this->type_resolver      = $type_resolver;
		$this->document_validator = $document_validator;
		$this->document_repository = $document_repository;
	}

	public function register() {
		add_action( 'woocommerce_register_form_start', array( $this, 'render_fields' ) );
		add_filter( 'woocommerce_registration_errors', array( $this, 'validate_registration' ), 10, 3 );
		add_action( 'woocommerce_created_customer', array( $this, 'save_customer_meta' ) );
	}

	public function render_fields() {
		$customer_type = isset( $_POST['imania_customer_type'] ) ? sanitize_text_field( wp_unslash( $_POST['imania_customer_type'] ) ) : '';
		$document      = isset( $_POST['imania_document'] ) ? sanitize_text_field( wp_unslash( $_POST['imania_document'] ) ) : '';

		wp_nonce_field( 'imania_register_customer', 'imania_register_nonce' );
		?>
		<p class="form-row form-row-wide">
			<label for="imania_customer_type"><?php esc_html_e( 'Tipo de cliente', 'imania-pricing-engine' ); ?>&nbsp;<span class="required">*</span></label>
			<select name="imania_customer_type" id="imania_customer_type" required>
				<option value=""><?php esc_html_e( 'Selecione', 'imania-pricing-engine' ); ?></option>
				<option value="pf" <?php selected( 'pf', $customer_type ); ?>><?php esc_html_e( 'Pessoa Fisica (PF)', 'imania-pricing-engine' ); ?></option>
				<option value="pj" <?php selected( 'pj', $customer_type ); ?>><?php esc_html_e( 'Pessoa Juridica (PJ)', 'imania-pricing-engine' ); ?></option>
			</select>
		</p>
		<p class="form-row form-row-wide">
			<label for="imania_document"><?php esc_html_e( 'CPF ou CNPJ', 'imania-pricing-engine' ); ?>&nbsp;<span class="required">*</span></label>
			<input type="text" class="input-text" name="imania_document" id="imania_document" value="<?php echo esc_attr( $document ); ?>" required />
		</p>
		<?php
	}

	/**
	 * @param \WP_Error $errors Existing errors.
	 * @param string    $username Username.
	 * @param string    $email Email.
	 *
	 * @return \WP_Error
	 */
	public function validate_registration( $errors, $username = '', $email = '' ) {
		$nonce = isset( $_POST['imania_register_nonce' ] ) ? sanitize_text_field( wp_unslash( $_POST['imania_register_nonce'] ) ) : '';
		if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, 'imania_register_customer' ) ) {
			$errors->add( 'imania_nonce_error', esc_html__( 'Falha de seguranca no cadastro. Atualize a pagina e tente novamente.', 'imania-pricing-engine' ) );
			return $errors;
		}

		$customer_type = isset( $_POST['imania_customer_type'] ) ? sanitize_text_field( wp_unslash( $_POST['imania_customer_type'] ) ) : '';
		if ( ! $this->type_resolver->is_valid( $customer_type ) ) {
			$errors->add( 'imania_customer_type_error', esc_html__( 'Selecione se a conta e PF ou PJ.', 'imania-pricing-engine' ) );
			return $errors;
		}

		$document_raw = isset( $_POST['imania_document'] ) ? sanitize_text_field( wp_unslash( $_POST['imania_document'] ) ) : '';
		$document     = $this->document_validator->normalize( $document_raw );

		if ( CustomerTypeResolver::PF === $customer_type && ! $this->document_validator->is_valid_cpf( $document ) ) {
			$errors->add( 'imania_cpf_error', esc_html__( 'CPF invalido.', 'imania-pricing-engine' ) );
			return $errors;
		}

		if ( CustomerTypeResolver::PJ === $customer_type && ! $this->document_validator->is_valid_cnpj( $document ) ) {
			$errors->add( 'imania_cnpj_error', esc_html__( 'CNPJ invalido.', 'imania-pricing-engine' ) );
			return $errors;
		}

		if ( $this->document_repository->exists_for_another_user( $document ) ) {
			$errors->add( 'imania_document_duplicated', esc_html__( 'Este documento ja esta vinculado a outra conta.', 'imania-pricing-engine' ) );
		}

		return $errors;
	}

	/**
	 * @param int $customer_id New user id.
	 */
	public function save_customer_meta( $customer_id ) {
		if ( empty( $customer_id ) ) {
			return;
		}

		$customer_type = isset( $_POST['imania_customer_type'] ) ? sanitize_text_field( wp_unslash( $_POST['imania_customer_type'] ) ) : '';
		$document_raw  = isset( $_POST['imania_document'] ) ? sanitize_text_field( wp_unslash( $_POST['imania_document'] ) ) : '';
		$document      = $this->document_validator->normalize( $document_raw );

		if ( ! $this->type_resolver->is_valid( $customer_type ) || '' === $document ) {
			return;
		}

		update_user_meta( $customer_id, MetaKeys::CUSTOMER_TYPE, $customer_type );
		update_user_meta( $customer_id, MetaKeys::DOCUMENT_NUMBER, $document );
		update_user_meta( $customer_id, MetaKeys::DOCUMENT_TYPE, CustomerTypeResolver::PF === $customer_type ? 'cpf' : 'cnpj' );

		if ( CustomerTypeResolver::PF === $customer_type ) {
			update_user_meta( $customer_id, 'billing_persontype', '1' );
			update_user_meta( $customer_id, 'billing_cpf', $document );
			$target_role = 'customer_pf';
		} else {
			update_user_meta( $customer_id, 'billing_persontype', '2' );
			update_user_meta( $customer_id, 'billing_cnpj', $document );
			$target_role = 'customer_pj';
		}

		$user = get_userdata( $customer_id );
		if ( $user instanceof \WP_User ) {
			$user->set_role( $target_role );
		}
	}
}
