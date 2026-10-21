<?php
/**
 * AST - Settings sidebar: "Enjoying AST?" review card, shown under Quick Help.
 *
 * Same card AST PRO shows in its settings sidebar (includes/settings/partials/review-card.php
 * there): five stars, one line of copy, and a link to the review form. The free plugin ships
 * from wordpress.org, so the button points at the plugin's review form there - the same URL
 * the review admin notice uses (class-wc-advanced-shipment-tracking-admin-notice.php).
 *
 * @package WooCommerce_Advanced_Shipment_Tracking
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="ast-review-card">
	<h4 class="ast-review-card__title"><?php esc_html_e( 'Enjoying AST?', 'woo-advanced-shipment-tracking' ); ?></h4>
	<div class="ast-review-card__stars" aria-hidden="true">&#9733;&#9733;&#9733;&#9733;&#9733;</div>
	<p class="ast-review-card__text"><?php esc_html_e( 'If AST keeps your fulfillment on track and your customers informed, please leave us a quick review. It really helps!', 'woo-advanced-shipment-tracking' ); ?></p>
	<a class="zui-btn-primary zui-btn-block ast-review-card__btn" href="https://wordpress.org/support/plugin/woo-advanced-shipment-tracking/reviews/#new-post" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Leave a review', 'woo-advanced-shipment-tracking' ); ?></a>
</div>
