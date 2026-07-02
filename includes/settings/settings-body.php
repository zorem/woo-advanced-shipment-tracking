<?php
/**
 * AST - New Settings UI: Settings-tab body (sidebar + section panels).
 *
 * Required from layout-app.php inside the Settings tab panel. Expects:
 *   $ast_sections        — array from ast_free_settings_section_map()
 *   $ast_active_section  — slug of the section to show first
 *   $this                — WC_Advanced_Shipment_Tracking_Admin (so $this->get_html_ul()
 *                          and friends work for legacy field rendering).
 *
 * Each active section submits through the SAME #wc_ast_settings_form
 * (action: wc_ast_settings_form_update) — identical contract to the legacy UI.
 *
 * @package wc_advanced_shipment_tracking
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="zui-layout">

	<div class="zui-sidebar__overlay" data-ast-drawer-close data-zui-drawer-close></div>

	<?php require __DIR__ . '/sidebar.php'; ?>

	<main class="zui-content" id="ast-set-content">
		<form id="wc_ast_settings_form" method="post" action="" enctype="multipart/form-data" novalidate>
			<?php
			$ast_has_live = false;

			foreach ( $ast_sections as $ast_id => $ast_section ) :
				$ast_is_active   = ( $ast_id === $ast_active_section );
				$ast_render_mode = isset( $ast_section['render_mode'] ) ? $ast_section['render_mode'] : 'locked';
				// Legacy sections own real form fields, so their header save button
				// must be the dirty-state-aware "live" variant; locked sections keep
				// the inert button (read-only PRO preview).
				$ast_section_live = ( 'legacy' === $ast_render_mode );

				if ( 'legacy' === $ast_render_mode ) {
					$ast_has_live = true;
				}
				?>
				<section
					id="ast-section-<?php echo esc_attr( $ast_id ); ?>"
					class="zui-section<?php echo $ast_is_active ? ' is-active' : ''; ?>"
					data-section="<?php echo esc_attr( $ast_id ); ?>"
					data-zui-section="<?php echo esc_attr( $ast_id ); ?>"
					<?php echo $ast_is_active ? '' : 'hidden'; ?>
				>
					<?php require __DIR__ . '/partials/section-header.php'; ?>

					<?php
					if ( 'legacy' === $ast_render_mode ) {

						echo '<div class="zui-card">';

						// 'source' identifies the legacy content to render:
						//   - 'general'       → $this->get_html_ul( $this->get_add_tracking_options() )
						//   - 'tracking-api'  → $this->get_html_ul( $this->get_shipment_tracking_api_options() )
						//   - absolute file path → require the partial (e.g. admin_options_osm.php)
						$ast_source = isset( $ast_section['source'] ) ? $ast_section['source'] : '';

						if ( 'general' === $ast_source && method_exists( $this, 'get_add_tracking_options' ) ) {
							$this->get_html_ul( $this->get_add_tracking_options() );
						} elseif ( 'tracking-api' === $ast_source && method_exists( $this, 'get_shipment_tracking_api_options' ) ) {
							$this->get_html_ul( $this->get_shipment_tracking_api_options() );
						} elseif ( $ast_source && file_exists( $ast_source ) ) {
							require $ast_source;
						}

						echo '</div>';

					} elseif ( 'locked' === $ast_render_mode ) {
						$ast_locked_src = isset( $ast_section['source'] ) ? $ast_section['source'] : '';
						if ( $ast_locked_src && file_exists( $ast_locked_src ) ) {
							// Section has a richer locked preview (mirrors PRO's field layout) — load it.
							require $ast_locked_src;
						} else {
							// Fallback: a minimal upgrade-to-PRO card.
							?>
							<div class="zui-card zui-locked">
								<div class="zui-locked__head">
									<span class="zui-locked__badge"><?php echo esc_html( isset( $ast_section['badge'] ) ? $ast_section['badge'] : 'PRO' ); ?></span>
									<h3 class="zui-locked__title"><?php echo esc_html( $ast_section['label'] ); ?></h3>
								</div>
								<p class="zui-locked__text"><?php echo esc_html( isset( $ast_section['sub'] ) ? $ast_section['sub'] : '' ); ?></p>
							</div>
							<?php
						}
					}
					?>
				</section>
				<?php
			endforeach;

			if ( $ast_has_live ) :
				wp_nonce_field( 'wc_ast_settings_form', 'wc_ast_settings_form_nonce' );
				?>
				<input type="hidden" name="action" value="wc_ast_settings_form_update">
				<?php
			endif;
			?>
		</form>
	</main>

</div>
