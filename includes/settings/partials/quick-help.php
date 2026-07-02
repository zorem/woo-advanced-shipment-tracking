<?php
/**
 * AST PRO - New Settings UI: Quick Help sidebar block (Phase 1).
 *
 * Static help links (Documentation, Support). Visual reference: ast-fulfillment-manager
 * QuickHelp block.
 *
 * @package AST_Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="zui-quickhelp">
	<h4 class="zui-quickhelp__title"><?php esc_html_e( 'Quick Help', 'woo-advanced-shipment-tracking' ); ?></h4>
	<p class="zui-quickhelp__text"><?php esc_html_e( 'Learn how to configure AST Pro for best results.', 'woo-advanced-shipment-tracking' ); ?></p>

	<div class="zui-quickhelp__links">
		<a class="zui-quickhelp__link" href="https://docs.zorem.com/" target="_blank" rel="noreferrer noopener">
			<span><?php esc_html_e( 'View Documentation', 'woo-advanced-shipment-tracking' ); ?></span>
			<?php ast_free_settings_icon( 'arrow-right' ); ?>
		</a>
		<a class="zui-quickhelp__link zui-quickhelp__link--muted" href="https://wordpress.org/support/plugin/woo-advanced-shipment-tracking/#new-topic-0" target="_blank" rel="noreferrer noopener">
			<span><?php esc_html_e( 'Get Support', 'woo-advanced-shipment-tracking' ); ?></span>
			<?php ast_free_settings_icon( 'arrow-right' ); ?>
		</a>
	</div>

	<div class="zui-quickhelp__art" aria-hidden="true">
		<span class="zui-quickhelp__glow"></span>
		<span class="zui-quickhelp__sphere"></span>

		<span class="zui-quickhelp__paper zui-quickhelp__paper--1">
			<span class="zui-quickhelp__line zui-quickhelp__line--brand"></span>
			<span class="zui-quickhelp__line zui-quickhelp__line--mid"></span>
			<span class="zui-quickhelp__line zui-quickhelp__line--short"></span>
		</span>
		<span class="zui-quickhelp__paper zui-quickhelp__paper--2">
			<span class="zui-quickhelp__line zui-quickhelp__line--accent"></span>
			<span class="zui-quickhelp__line zui-quickhelp__line--mid"></span>
			<span class="zui-quickhelp__line zui-quickhelp__line--mid"></span>
			<span class="zui-quickhelp__line zui-quickhelp__line--short"></span>
		</span>

		<span class="zui-quickhelp__check">&#10003;</span>
	</div>
</div>
