<?php
/**
 * AST - New Settings UI shell: SINGLE-PAGE app (all tabs rendered once).
 *
 * Renders every top tab's content into its own .zui-tab-panel (active shown, rest
 * hidden) so switching tabs is a pure CSS show/hide in ast-settings.js — instant,
 * no fetch, no reload.
 *
 * @package wc_advanced_shipment_tracking
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/partials/icons.php';
require_once __DIR__ . '/section-map.php';
require_once __DIR__ . '/renderer.php';

// Settings-tab section state (consumed by settings-body.php).
$ast_sections       = ast_free_settings_section_map();
$ast_active_section = isset( $_GET['section'] ) ? sanitize_key( wp_unslash( $_GET['section'] ) ) : 'general'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only view state.
if ( ! isset( $ast_sections[ $ast_active_section ] ) ) {
	$ast_active_section = (string) key( $ast_sections );
}

$ast_active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'settings'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only view state.

// Top tab slug -> full-width module file ('' = the special Settings body).
// Free version: each non-settings tab points to its EXISTING legacy view file. Phase 3
// will restyle those view files in place — no new PRO modules are referenced here.
// Filterable so add-ons can register their own panels via ast_settings_app_tabs.
$ast_app_tabs = apply_filters( 'ast_settings_app_tabs', array(
	'settings'           => '',
	'shipping-providers' => SHIPMENT_TRACKING_PATH . '/includes/views/admin_options_shipping_provider.php',
	'csv-import'         => SHIPMENT_TRACKING_PATH . '/includes/views/admin_options_bulk_upload.php',
	'bulk-paste'         => SHIPMENT_TRACKING_PATH . '/includes/views/admin_options_bulk_paste.php',
	'integrations'       => SHIPMENT_TRACKING_PATH . '/includes/views/admin_options_integrations.php',
	'addons'             => SHIPMENT_TRACKING_PATH . '/includes/views/admin_options_addons.php',
	'trackship'          => SHIPMENT_TRACKING_PATH . '/includes/views/admin_options_trackship_integration.php',
) );
if ( ! isset( $ast_app_tabs[ $ast_active_tab ] ) ) {
	$ast_active_tab = 'settings';
}
?>
<div class="ast-settings-app zui-scope" id="ast-settings-app">

	<?php do_action( 'before_ast_settings' ); ?>

	<?php require __DIR__ . '/header.php'; ?>

	<?php foreach ( $ast_app_tabs as $ast_tab => $ast_src ) : ?>
		<div class="zui-tab-panel" data-tab="<?php echo esc_attr( $ast_tab ); ?>" <?php echo ( $ast_tab === $ast_active_tab ) ? '' : 'hidden'; ?>>
			<?php
			if ( 'settings' === $ast_tab ) {
				require __DIR__ . '/settings-body.php';
			} else {
				// Support core relative paths AND absolute paths from the ast_settings_app_tabs
				// filter; guard with file_exists so a bad registration can never fatal.
				$ast_panel = ( '' !== $ast_src && file_exists( $ast_src ) ) ? $ast_src : __DIR__ . '/' . $ast_src;
				if ( file_exists( $ast_panel ) ) :
					?>
					<div class="zui-layout zui-layout--full">
						<main class="zui-content zui-content--full">
							<?php require $ast_panel; ?>
						</main>
					</div>
					<?php
				endif;
			}
			?>
		</div>
	<?php endforeach; ?>

</div>
