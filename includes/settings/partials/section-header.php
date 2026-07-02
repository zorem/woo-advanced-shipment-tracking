<?php
/**
 * AST PRO - New Settings UI: generic section-header banner (Phase 1).
 *
 * Expects $ast_section (one entry from section-map.php) to be in scope. Reused by
 * every section panel: brand icon tile + title + subtitle + Save button.
 *
 * Phase 1: the Save button is intentionally INERT (disabled, and it does NOT carry
 * the .woocommerce-save-button hook), so it cannot trigger wc_ast_settings_form_update
 * before fields exist. The live save hook + dirty-state enabling are added in Phase 3.
 *
 * @package AST_Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! isset( $ast_section ) || ! is_array( $ast_section ) ) {
	return;
}

$ast_icon  = isset( $ast_section['icon'] ) ? $ast_section['icon'] : 'sliders';
// Header uses the descriptive heading/description (distinct from the sidebar label/sub).
$ast_title = ! empty( $ast_section['heading'] ) ? $ast_section['heading'] : ( isset( $ast_section['label'] ) ? $ast_section['label'] : '' );
$ast_sub   = ! empty( $ast_section['description'] ) ? $ast_section['description'] : ( isset( $ast_section['sub'] ) ? $ast_section['sub'] : '' );
$ast_badge  = ! empty( $ast_section['badge'] ) ? $ast_section['badge'] : '';
$ast_action = ( ! empty( $ast_section['header_action'] ) && is_array( $ast_section['header_action'] ) ) ? $ast_section['header_action'] : array();
?>
<div class="zui-section-header">

	<div class="zui-section-header__main">
		<span class="zui-section-header__icon"><?php ast_free_settings_icon( $ast_icon ); ?></span>
		<div class="zui-section-header__text">
			<div class="zui-section-header__titlewrap">
				<h2 class="zui-section-header__title"><?php echo esc_html( $ast_title ); ?></h2>
				<?php if ( '' !== $ast_badge ) : ?>
					<span class="zui-section-header__badge"><?php echo esc_html( $ast_badge ); ?></span>
				<?php endif; ?>
			</div>
			<?php if ( '' !== $ast_sub ) : ?>
				<p class="zui-section-header__sub"><?php echo esc_html( $ast_sub ); ?></p>
			<?php endif; ?>
		</div>
	</div>

	<div class="zui-section-header__actions">

		<?php if ( ! empty( $ast_action['label'] ) ) : ?>
			<a class="zui-section-header__action" href="<?php echo esc_url( isset( $ast_action['url'] ) ? $ast_action['url'] : '#' ); ?>">
				<?php if ( ! empty( $ast_action['icon'] ) ) : ?>
					<?php ast_free_settings_icon( $ast_action['icon'] ); ?>
				<?php endif; ?>
				<span><?php echo esc_html( $ast_action['label'] ); ?></span>
			</a>
		<?php endif; ?>

		<?php $ast_live = isset( $ast_section_live ) ? (bool) $ast_section_live : false; ?>
		<?php if ( $ast_live ) : ?>
			<?php /* Live save: .woocommerce-save-button binds the existing serialize+AJAX; span.ast-accordion-btn is enabled by the existing dirty-state handlers. Starts disabled. */ ?>
			<span class="zui-savebtn-wrap ast-accordion-btn">
				<button type="button" class="zui-savebtn woocommerce-save-button btn_ast2" disabled
					data-label-saving="<?php esc_attr_e( 'Saving…', 'woo-advanced-shipment-tracking' ); ?>"
					data-label-saved="<?php esc_attr_e( 'Saved', 'woo-advanced-shipment-tracking' ); ?>">
					<span class="zui-savebtn__label"><?php esc_html_e( 'Save Changes', 'woo-advanced-shipment-tracking' ); ?></span>
					<span class="zui-savebtn__spinner spinner workflow_spinner"></span>
				</button>
			</span>
		<?php else : ?>
			<button type="button" class="zui-savebtn zui-savebtn--inert" disabled aria-disabled="true">
				<span class="zui-savebtn__label"><?php esc_html_e( 'Save Changes', 'woo-advanced-shipment-tracking' ); ?></span>
			</button>
		<?php endif; ?>

	</div>

</div>
