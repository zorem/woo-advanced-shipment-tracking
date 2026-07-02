<?php
/**
 * AST PRO — Settings UI icon helpers (thin alias-shim).
 *
 * The full Lucide-style SVG icon set now lives in the shared Zorem UI library
 * at `assets/zui/icons.php` (function `zui_get_icon` / `zui_icon`). This file
 * exists only to keep AST PRO's 97 historical call sites
 * (`ast_free_settings_get_icon()` / `ast_free_settings_icon()`) working
 * unchanged — every call is forwarded to the library so all Zorem plugins
 * stay visually in sync from a single source of icons.
 *
 * @package AST_Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once dirname( __FILE__, 4 ) . '/assets/zui/icons.php';

if ( ! function_exists( 'ast_free_settings_get_icon' ) ) {
	/**
	 * Return inline SVG markup for a named icon. Delegates to the ZUI library.
	 *
	 * @param string $name    Icon key.
	 * @param string $classes Extra CSS classes.
	 * @return string
	 */
	function ast_free_settings_get_icon( $name, $classes = '' ) {
		return zui_get_icon( $name, $classes );
	}
}

if ( ! function_exists( 'ast_free_settings_icon' ) ) {
	/**
	 * Echo a named inline SVG icon. Delegates to the ZUI library.
	 *
	 * @param string $name    Icon key.
	 * @param string $classes Extra CSS classes.
	 * @return void
	 */
	function ast_free_settings_icon( $name, $classes = '' ) {
		zui_icon( $name, $classes );
	}
}
