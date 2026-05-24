<?php
/**
 * Custom My Account layout.
 *
 * @package Imania_Store
 */

defined('ABSPATH') || exit;

$user_id = get_current_user_id();
$user = $user_id > 0 ? get_userdata($user_id) : null;

$profile_name = '';
$profile_email = '';
if ($user instanceof WP_User) {
	$profile_name = trim($user->first_name . ' ' . $user->last_name);
	if ('' === $profile_name) {
		$profile_name = $user->display_name;
	}
	$profile_email = $user->user_email;
}

$items = wc_get_account_menu_items();
$allowed = array('profile', 'orders', 'wishlist', 'payment-methods');
$menu_items = array();
foreach ($allowed as $endpoint) {
	if (isset($items[$endpoint])) {
		$menu_items[$endpoint] = $items[$endpoint];
	}
}

$current_endpoint = '';
foreach (array_keys($menu_items) as $endpoint) {
	if (wc_is_current_account_menu_item($endpoint)) {
		$current_endpoint = $endpoint;
		break;
	}
}

if ('' === $current_endpoint || 'dashboard' === $current_endpoint) {
	$current_endpoint = 'profile';
}
?>
<section class="imania-account" data-imania-account data-current-endpoint="<?php echo esc_attr($current_endpoint); ?>">
	

	<div class="imania-account__viewport container">
		<div class="imania-account__layout">
			<aside class="imania-account__left">
				<div class="imania-account__top">
					<div class="imania-account__avatar" aria-hidden="true"></div>
					<?php if ('' !== $profile_name) : ?>
						<p class="imania-account__name"><?php echo esc_html($profile_name); ?></p>
					<?php endif; ?>
					<?php if ('' !== $profile_email) : ?>
						<p class="imania-account__email"><?php echo esc_html($profile_email); ?></p>
					<?php endif; ?>
				</div>

				<nav class="imania-account__nav" aria-label="<?php esc_attr_e('Navegacao da conta', 'imania-store'); ?>">
					<ul>
						<?php foreach ($menu_items as $endpoint => $label) : ?>
							<?php $is_active = $current_endpoint === $endpoint; ?>
							<li class="<?php echo $is_active ? 'is-active' : ''; ?>">
								<a
									href="<?php echo esc_url(wc_get_account_endpoint_url($endpoint)); ?>"
									data-imania-account-nav
									data-endpoint="<?php echo esc_attr($endpoint); ?>"
									<?php echo $is_active ? 'aria-current="page"' : ''; ?>
								>
									<?php echo esc_html($label); ?>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				</nav>
			</aside>

			<div class="imania-account__divider" aria-hidden="true"></div>

			<div class="imania-account__content" data-imania-account-content>
				<div class="imania-account__skeleton" data-imania-account-skeleton hidden>
					<div class="imania-account__skeleton-title"></div>
					<div class="imania-account__skeleton-grid">
						<span></span><span></span><span></span><span></span>
						<span></span><span></span><span></span><span></span>
					</div>
					<div class="imania-account__skeleton-line"></div>
					<div class="imania-account__skeleton-line"></div>
					<div class="imania-account__skeleton-line imania-account__skeleton-line--sm"></div>
				</div>
				<div class="imania-account__content-inner" data-imania-account-content-inner>
					<?php if ('profile' === $current_endpoint) : ?>
						<?php do_action('woocommerce_account_profile_endpoint'); ?>
					<?php else : ?>
						<?php do_action('woocommerce_account_content'); ?>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>
</section>
