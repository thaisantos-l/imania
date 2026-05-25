<?php

/**
 * Get user wishlist product ids.
 *
 * @param int $user_id Optional user id.
 *
 * @return int[]
 */
function imania_store_get_wishlist_ids($user_id = 0)
{
	$user_id = $user_id > 0 ? (int) $user_id : get_current_user_id();
	if ($user_id <= 0) {
		return array();
	}

	$raw = get_user_meta($user_id, 'imania_wishlist_product_ids', true);
	if (!is_array($raw)) {
		return array();
	}

	$ids = array_values(array_unique(array_filter(array_map('absint', $raw))));
	return $ids;
}

/**
 * Save wishlist ids.
 *
 * @param int   $user_id User id.
 * @param int[] $ids Product ids.
 */
function imania_store_save_wishlist_ids($user_id, array $ids)
{
	$user_id = (int) $user_id;
	if ($user_id <= 0) {
		return;
	}

	$ids = array_values(array_unique(array_filter(array_map('absint', $ids))));
	update_user_meta($user_id, 'imania_wishlist_product_ids', $ids);
	imania_store_invalidate_account_cache($user_id);
}

/**
 * Check if product is in wishlist.
 *
 * @param int      $product_id Product id.
 * @param int|null $user_id Optional user id.
 *
 * @return bool
 */
function imania_store_is_in_wishlist($product_id, $user_id = null)
{
	$product_id = absint($product_id);
	if ($product_id <= 0) {
		return false;
	}

	if (null === $user_id) {
		$user_id = get_current_user_id();
	}

	$ids = imania_store_get_wishlist_ids((int) $user_id);
	return in_array($product_id, $ids, true);
}

/**
 * Toggle wishlist item for user.
 *
 * @param int    $user_id User id.
 * @param int    $product_id Product id.
 * @param string $mode toggle|add|remove.
 *
 * @return bool True when favorited after operation.
 */
function imania_store_update_wishlist_item($user_id, $product_id, $mode = 'toggle')
{
	$user_id = (int) $user_id;
	$product_id = absint($product_id);
	$mode = sanitize_key((string) $mode);
	if ($user_id <= 0 || $product_id <= 0) {
		return false;
	}

	$ids = imania_store_get_wishlist_ids($user_id);
	$index = array_search($product_id, $ids, true);
	$exists = false !== $index;

	if ('add' === $mode && !$exists) {
		$ids[] = $product_id;
		$exists = true;
	} elseif ('remove' === $mode && $exists) {
		unset($ids[$index]);
		$exists = false;
	} elseif ('toggle' === $mode) {
		if ($exists) {
			unset($ids[$index]);
			$exists = false;
		} else {
			$ids[] = $product_id;
			$exists = true;
		}
	}

	imania_store_save_wishlist_ids($user_id, $ids);
	return $exists;
}
