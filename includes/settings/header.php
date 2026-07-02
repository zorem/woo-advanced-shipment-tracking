<?php
/**
 * AST PRO - New Settings UI: global header bar + top navigation (Phase 1).
 *
 * Brand bar (logo, docs, notifications) and the top-level module tabs.
 *
 * Top tabs are sourced from the existing, filterable get_ast_tab_settings_data().
 * The Settings tab is the active (new UI) tab; every other tab links to the legacy
 * screen (?tab=<slug>), per the Phase 1 coexistence decision (Settings = new UI,
 * all other modules = legacy UI).
 *
 * Rendered as a panel by layout-app.php (method scope) -> $this is AST_Pro_Admin.
 *
 * @package AST_Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Pull the tab definitions from the plugin's admin class so this header stays in sync
// with whatever tabs the legacy UI registers (and any third-party hooks on ast_menu_tab_options).
$ast_tabs     = wc_advanced_shipment_tracking()->admin->get_ast_tab_settings_data();
$ast_base_url = admin_url( 'admin.php?page=woocommerce-advanced-shipment-tracking' );

// Active top tab: a pre-set value wins (e.g. dashboard sets 'unfulfilled-orders'),
// otherwise derive from the URL (defaults to Settings).
if ( ! isset( $ast_active_top_tab ) || '' === $ast_active_top_tab ) {
	$ast_active_top_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'settings'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only view state.
}

// Brand cluster — pulled from the ZUI library registry so every consumer plugin
// (AST, ALP, CBR, CEV, SMS, SRE) renders its own chrome from one source of truth.
// Fallback values preserve the legacy AST PRO brand if the registry lookup fails.
require_once SHIPMENT_TRACKING_PATH . '/assets/zui/brand.php';
$ast_brand = zui_get_plugin_brand( 'woo-advanced-shipment-tracking/woocommerce-advanced-shipment-tracking.php' );
if ( ! is_array( $ast_brand ) ) {
	$ast_brand = array(
		'name'         => 'AST',
		'badge'        => 'FREE',
		'tagline'      => __( 'Shipment Tracking', 'woo-advanced-shipment-tracking' ),
		'icon'         => 'package',
		'emblem_bg'    => '#DBEAFE',
		'emblem_color' => '#2563EB',
	);
}
$ast_emblem_style = sprintf(
	'--zui-brand-emblem-bg:%s;--zui-brand-emblem-color:%s;',
	$ast_brand['emblem_bg'],
	$ast_brand['emblem_color']
);
?>
<header class="zui-header" id="ast-set-header">

	<div class="zui-header__bar">

		<div class="zui-header__lead">
			<?php /* Menu toggle (mobile) — only visible when the active tab has a sidebar.
			         In AST PRO only the Settings tab renders a sidebar; on other tabs the
			         toggle is hidden via JS on tab-swap. `hidden` attribute is the initial
			         state for non-settings tabs; JS keeps it in sync as the user navigates. */ ?>
			<button type="button" class="zui-header__menu-toggle" data-ast-drawer-toggle data-zui-drawer-toggle data-zui-sidebar-tabs="settings" aria-label="<?php esc_attr_e( 'Open settings menu', 'woo-advanced-shipment-tracking' ); ?>"<?php echo ( 'settings' === $ast_active_top_tab ) ? '' : ' hidden'; ?>>
				<?php ast_free_settings_icon( 'menu' ); ?>
			</button>

			<div class="zui-brand">
				<span class="zui-brand__emblem" aria-hidden="true" style="<?php echo esc_attr( $ast_emblem_style ); ?>">
					<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="20" height="20"><path d="M11 21.73a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73z"/><path d="M12 22V12"/><path d="m3.3 7 7.703 4.734a2 2 0 0 0 1.994 0L20.7 7"/><path d="m7.5 4.27 9 5.15"/></svg>
				</span>
				<span class="zui-brand__name"><?php echo esc_html( $ast_brand['name'] ); ?></span>
				<span class="zui-brand__badge"><?php echo esc_html( $ast_brand['badge'] ); ?></span>
				<span class="zui-brand__tagline"><?php echo esc_html( $ast_brand['tagline'] ); ?></span>
			</div>
		</div>

		<div class="zui-header-actions">
			<a class="zui-header-action zui-header-action--with-label" href="https://docs.zorem.com/" target="_blank" rel="noreferrer noopener">
				<?php ast_free_settings_icon( 'book-open' ); ?>
				<span class="zui-header-action__label"><?php esc_html_e( 'Docs Portal', 'woo-advanced-shipment-tracking' ); ?></span>
			</a>
			<?php /* Notifications bell — hidden for now, kept in markup for future use. */ ?>
			<button type="button" class="zui-header-action zui-header-action--icon-only" aria-label="<?php esc_attr_e( 'Notifications', 'woo-advanced-shipment-tracking' ); ?>" hidden>
				<?php ast_free_settings_icon( 'bell' ); ?>
			</button>
		</div>

	</div>

	<nav class="zui-tabs" aria-label="<?php esc_attr_e( 'AST Pro modules', 'woo-advanced-shipment-tracking' ); ?>">
		<?php
		foreach ( (array) $ast_tabs as $ast_tab ) :
			if ( empty( $ast_tab['show'] ) ) {
				continue;
			}

			$ast_slug      = isset( $ast_tab['data-tab'] ) ? $ast_tab['data-tab'] : '';
			$ast_is_active = ( $ast_slug === $ast_active_top_tab );

			if ( isset( $ast_tab['type'] ) && 'link' === $ast_tab['type'] && ! empty( $ast_tab['link'] ) ) {
				$ast_href = $ast_tab['link'];
			} else {
				$ast_href = add_query_arg( 'tab', $ast_slug, $ast_base_url );
			}

			$ast_tab_count = 0;
			?>
			<a
				class="zui-tabs__item<?php echo $ast_is_active ? ' is-active' : ''; ?>"
				href="<?php echo esc_url( $ast_href ); ?>"
				<?php echo $ast_is_active ? 'aria-current="page"' : ''; ?>
			>
				<?php echo esc_html( $ast_tab['title'] ); ?>
				<?php if ( ! empty( $ast_tab['badge'] ) ) : ?>
					<span class="zui-tabs__badge zui-tabs__badge--pro"><?php echo esc_html( $ast_tab['badge'] ); ?></span>
				<?php elseif ( $ast_tab_count > 0 ) : ?>
					<span class="zui-tabs__badge"><?php echo esc_html( $ast_tab_count ); ?></span>
				<?php endif; ?>
			</a>
			<?php
		endforeach;
		?>
	</nav>

</header>
