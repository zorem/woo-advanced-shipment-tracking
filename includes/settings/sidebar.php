<?php
/**
 * AST PRO - New Settings UI: settings sidebar (Phase 1).
 *
 * Renders the level-2 section navigation from section-map.php plus the Quick Help
 * block. Section switching is handled client-side by ast-settings.js: each item's
 * data-section value matches a panel id (ast-section-{id}) rendered in settings-body.php.
 *
 * @package AST_Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! isset( $ast_sections ) ) {
	require_once __DIR__ . '/section-map.php';
	$ast_sections = ast_free_settings_section_map();
}
if ( ! isset( $ast_active_section ) ) {
	$ast_active_section = (string) key( $ast_sections );
}
?>
<aside class="zui-sidebar" id="ast-set-sidebar">

	<div class="zui-sidebar__mobile-head">
		<span class="zui-sidebar__mobile-title"><?php esc_html_e( 'AST Settings', 'woo-advanced-shipment-tracking' ); ?></span>
		<button type="button" class="zui-sidebar__close" data-ast-drawer-close data-zui-drawer-close aria-label="<?php esc_attr_e( 'Close menu', 'woo-advanced-shipment-tracking' ); ?>">
			<?php ast_free_settings_icon( 'x' ); ?>
		</button>
	</div>

	<nav class="zui-sidebar__nav" aria-label="<?php esc_attr_e( 'Settings sections', 'woo-advanced-shipment-tracking' ); ?>">
		<?php
		foreach ( $ast_sections as $ast_id => $ast_section ) :
			$ast_is_active = ( $ast_id === $ast_active_section );
			$ast_icon      = isset( $ast_section['icon'] ) ? $ast_section['icon'] : 'sliders';
			?>
			<button
				type="button"
				class="zui-sidebar__item<?php echo $ast_is_active ? ' is-active' : ''; ?>"
				data-section="<?php echo esc_attr( $ast_id ); ?>"
				data-zui-section="<?php echo esc_attr( $ast_id ); ?>"
				aria-controls="ast-section-<?php echo esc_attr( $ast_id ); ?>"
				<?php echo $ast_is_active ? 'aria-current="true"' : ''; ?>
			>
				<span class="zui-sidebar__icon"><?php ast_free_settings_icon( $ast_icon ); ?></span>
				<span class="zui-sidebar__label"><?php echo esc_html( $ast_section['label'] ); ?></span>
				<?php if ( ! empty( $ast_section['badge'] ) ) : ?>
					<span class="zui-sidebar__badge"><?php echo esc_html( $ast_section['badge'] ); ?></span>
				<?php endif; ?>
			</button>
			<?php
		endforeach;
		?>
	</nav>

	<?php require __DIR__ . '/partials/quick-help.php'; ?>

</aside>
