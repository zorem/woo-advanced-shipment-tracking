<?php
/**
 * Order Statuses & Notifications — re-skinned for the new ZUI shell.
 *
 * Markup mirrors the AST PRO order-statuses module (rows + color pill + theme select +
 * Send Email checkbox) so the visual matches. Functionality is unchanged: input names,
 * IDs, nonces and the save action all preserve the legacy contracts exactly. Rendered
 * inside the new chrome by includes/settings/settings-body.php.
 *
 * @package wc_advanced_shipment_tracking
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$osm_rows  = $this->get_osm_data();
$rename_on = get_ast_settings( 'ast_general_settings', 'wc_ast_status_shipped', 1 );
$icons     = array(
	'partial_shipped'  => 'package',
	'shipped'          => 'truck',
	'delivered'        => 'shield-check',
	'updated_tracking' => 'package',
);
$descs = array(
	'partial_shipped'  => __( 'Order split into multiple shipments.', 'woo-advanced-shipment-tracking' ),
	'shipped'          => __( 'Package is with the carrier.', 'woo-advanced-shipment-tracking' ),
	'delivered'        => __( 'Package received by the customer.', 'woo-advanced-shipment-tracking' ),
	'updated_tracking' => __( 'Tracking details were updated.', 'woo-advanced-shipment-tracking' ),
);
?>
<div class="zui-card zui-card--osm">

	<?php /* Row 0: rename "Completed" -> "Shipped" */ ?>
	<div class="ast-set-osm-row ast-set-osm-row--rename">
		<div class="ast-set-osm-row__main">
			<label class="zui-toggle">
				<input type="hidden" name="wc_ast_status_shipped" value="0">
				<input type="checkbox" id="wc_ast_status_shipped" name="wc_ast_status_shipped" value="1" class="zui-toggle__input" <?php checked( (bool) $rename_on, true ); ?>>
				<span class="zui-toggle__track"><span class="zui-toggle__thumb"></span></span>
			</label>
			<div class="ast-set-osm-row__text">
				<span class="ast-set-osm-row__title"><?php esc_html_e( 'Rename "Completed" status to "Shipped"', 'woo-advanced-shipment-tracking' ); ?></span>
				<p class="ast-set-osm-row__desc"><?php esc_html_e( 'Rewrites the Completed order status label to Shipped across emails and order screens.', 'woo-advanced-shipment-tracking' ); ?></p>
			</div>
		</div>
		<a class="settings_edit ast-set-osm-cog" href="<?php echo esc_url( admin_url( 'admin.php?page=ast_customizer&email_type=completed' ) ); ?>" title="<?php esc_attr_e( 'Edit email template', 'woo-advanced-shipment-tracking' ); ?>">
			<?php ast_free_settings_icon( 'settings' ); ?>
		</a>
	</div>

	<?php
	foreach ( $osm_rows as $o_status => $data ) :

		$is_pro = ! empty( $data['pro'] );
		$enabled = $is_pro ? false : (bool) get_ast_settings( 'ast_general_settings', $data['id'] );

		// 'shipped' standalone row is mutually exclusive with the "Rename Completed→Shipped" toggle.
		if ( $rename_on && 'shipped' === $o_status ) {
			$enabled = false;
		}

		$label_color = $is_pro ? '#1e73be' : get_ast_settings( 'ast_general_settings', $data['label_color_field'], '#1e73be' );
		if ( ! preg_match( '/^#[0-9a-fA-F]{6}$/', (string) $label_color ) ) {
			$label_color = '#1e73be';
		}
		$font_color = $is_pro ? '#fff' : get_ast_settings( 'ast_general_settings', $data['font_color_field'], '#fff' );

		$email_val     = ( ! $is_pro && ! empty( $data['email_field'] ) ) ? get_ast_settings( 'ast_general_settings', $data['email_field'], '' ) : '';
		$email_checked = ( 'yes' === $email_val || 1 == $email_val ); // phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison

		$status_name = $enabled ? wc_get_order_status_name( $data['slug'] ) : $data['label'];
		$icon        = isset( $icons[ $o_status ] ) ? $icons[ $o_status ] : 'package';
		$desc        = isset( $descs[ $o_status ] ) ? $descs[ $o_status ] : '';

		// Auto-contrast icon SVG colour based on bg.
		$icon_bg     = $enabled ? $label_color : '#cbd5e1';
		$hex         = ltrim( $icon_bg, '#' );
		if ( 3 === strlen( $hex ) ) {
			$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
		}
		$r            = hexdec( substr( $hex, 0, 2 ) );
		$g            = hexdec( substr( $hex, 2, 2 ) );
		$b            = hexdec( substr( $hex, 4, 2 ) );
		$lum          = ( 0.299 * $r + 0.587 * $g + 0.114 * $b ) / 255;
		$icon_color   = ( $lum > 0.55 ) ? '#000000' : '#ffffff';
		$icon_opacity = ( '#fff' === $font_color ) ? '0.6' : '1';
		?>
		<div class="ast-set-osm-row<?php echo $enabled ? '' : ' is-disabled'; ?>" data-osm-row="<?php echo esc_attr( $o_status ); ?>">

			<div class="ast-set-osm-row__main">
				<?php if ( $is_pro ) : ?>
					<label class="zui-toggle" aria-disabled="true">
						<input type="checkbox" class="zui-toggle__input" disabled>
						<span class="zui-toggle__track"><span class="zui-toggle__thumb"></span></span>
					</label>
				<?php else : ?>
					<label class="zui-toggle">
						<input type="hidden" name="<?php echo esc_attr( $data['id'] ); ?>" value="0">
						<input type="checkbox" id="<?php echo esc_attr( $data['id'] ); ?>" name="<?php echo esc_attr( $data['id'] ); ?>" value="1" class="order_status_toggle zui-toggle__input" <?php checked( (bool) $enabled, true ); ?>>
						<span class="zui-toggle__track"><span class="zui-toggle__thumb"></span></span>
					</label>
				<?php endif; ?>

				<span class="ast-set-osm-icon" data-osm-icon style="background-color: <?php echo esc_attr( $icon_bg ); ?>; color: <?php echo esc_attr( $icon_color ); ?>; opacity: <?php echo esc_attr( $icon_opacity ); ?>;">
					<?php ast_free_settings_icon( $icon ); ?>
				</span>

				<div class="ast-set-osm-row__text">
					<span class="ast-set-osm-row__title">
						<?php $title_opacity = ( '#fff' === $font_color ) ? '0.6' : '1'; ?>
						<span class="ast-set-osm-status-pill" data-osm-pill style="color: <?php echo esc_attr( $label_color ); ?>; opacity: <?php echo esc_attr( $title_opacity ); ?>;">
							<?php echo esc_html( $status_name ); ?>
						</span>
						<?php if ( $is_pro ) : ?>
							<span class="zui-locked__badge">PRO</span>
						<?php else : ?>
							<span class="ast-set-osm-badge"><?php esc_html_e( 'Default', 'woo-advanced-shipment-tracking' ); ?></span>
						<?php endif; ?>
					</span>
					<?php if ( '' !== $desc ) : ?>
						<p class="ast-set-osm-row__desc"><?php echo esc_html( $desc ); ?></p>
					<?php endif; ?>
				</div>
			</div>

			<div class="ast-set-osm-controls">
				<div class="ast-set-osm-field">
					<span class="ast-set-osm-field__label"><?php esc_html_e( 'Color', 'woo-advanced-shipment-tracking' ); ?></span>
					<label class="ast-set-osm-color-pill">
						<?php /* No `id` — legacy shipping_row.js initialises wpColorPicker on the
						         specific id selectors and would replace the input with the WP
						         swatch widget. Removing the id keeps the native <input type="color"> intact. */ ?>
						<input type="color" class="ast-set-osm-color" name="<?php echo esc_attr( $data['label_color_field'] ); ?>" value="<?php echo esc_attr( $label_color ); ?>" <?php disabled( $is_pro, true ); ?>>
						<span class="ast-set-osm-color-dot" style="background-color: <?php echo esc_attr( $label_color ); ?>"></span>
						<span class="ast-set-osm-color-hex"><?php echo esc_html( strtoupper( $label_color ) ); ?></span>
					</label>
				</div>

				<div class="ast-set-osm-field">
					<span class="ast-set-osm-field__label"><?php esc_html_e( 'Theme', 'woo-advanced-shipment-tracking' ); ?></span>
					<div class="zui-select-wrap ast-set-osm-theme">
						<select class="custom_order_color_select zui-select" name="<?php echo esc_attr( $data['font_color_field'] ); ?>" id="<?php echo esc_attr( $data['font_color_field'] ); ?>" <?php disabled( $is_pro, true ); ?>>
							<option value="#fff" <?php selected( '#fff', $font_color ); ?>><?php esc_html_e( 'Light', 'woo-advanced-shipment-tracking' ); ?></option>
							<option value="#000" <?php selected( '#000', $font_color ); ?>><?php esc_html_e( 'Dark', 'woo-advanced-shipment-tracking' ); ?></option>
						</select>
						<span class="zui-select-chevron"><?php ast_free_settings_icon( 'chevron-down' ); ?></span>
					</div>
				</div>

				<?php if ( 'delivered' !== $o_status && ! empty( $data['email_field'] ) ) : ?>
					<div class="ast-set-osm-field">
						<span class="ast-set-osm-field__label"><?php esc_html_e( 'Email Notification', 'woo-advanced-shipment-tracking' ); ?></span>
						<label class="zui-checkbox ast-set-osm-email">
							<?php if ( ! $is_pro ) : ?>
								<input type="hidden" name="<?php echo esc_attr( $data['email_field'] ); ?>" value="0">
							<?php endif; ?>
							<input type="checkbox" id="<?php echo esc_attr( $data['email_field'] ); ?>" name="<?php echo esc_attr( $data['email_field'] ); ?>" value="1" class="zui-checkbox__input enable_order_status_email_input" <?php checked( $email_checked, true ); ?> <?php disabled( $is_pro, true ); ?>>
							<span class="zui-checkbox__box"></span>
							<span><?php esc_html_e( 'Send Email', 'woo-advanced-shipment-tracking' ); ?></span>
						</label>
					</div>
				<?php endif; ?>
			</div>

			<?php if ( ! empty( $data['edit_email'] ) ) : ?>
				<a class="settings_edit ast-set-osm-cog" href="<?php echo esc_url( $data['edit_email'] ); ?>" title="<?php esc_attr_e( 'Edit email template', 'woo-advanced-shipment-tracking' ); ?>">
					<?php ast_free_settings_icon( 'settings' ); ?>
				</a>
			<?php else : ?>
				<span class="ast-set-osm-cog-spacer" aria-hidden="true"></span>
			<?php endif; ?>

		</div>
		<?php
	endforeach;
	do_action( 'ast_orders_status_column_end' );
	?>

</div>

<?php wp_nonce_field( 'wc_ast_order_status_form', 'wc_ast_order_status_form_nonce' ); ?>
<input type="hidden" name="action" value="wc_ast_custom_order_status_form_update">
