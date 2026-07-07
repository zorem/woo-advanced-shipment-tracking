<?php
/**
 * AST PRO - New Settings UI: field renderer (Phase 2).
 *
 * Consumes field-schema arrays (now owned by the section MODULES via their fields()
 * methods — see includes/settings/modules/settings-tab/ — plus the remaining FTP builder)
 * and emits NEW design markup, while preserving every save contract from the Control Contract:
 *
 *   - input name === schema key === $id (array shapes: {id}, {id}[], {id}[key])
 *   - value read via get_ast_settings($option_name,$id,$default)
 *     (legacy special-case: usage-tracking opt-ins read via get_option)
 *   - hidden "0" fallback for every checkbox / toggle / multiple_checkbox option
 *   - multiple_select  = ASSOC encoding (selected when stored[key]==1)
 *   - simple_multiple_select = FLAT encoding (selected when in_array)
 *   - JS hook classes preserved: ast-toggle, ast-settings-toggle, wc-enhanced-select,
 *     woocommerce-help-tip tipTip, radio class="{id}", ftp_test_* + ftp_data nonce
 *
 * This file ONLY renders fields. It does not fetch schema, save, open forms, or print
 * a card wrapper - section files (Phase 3) wrap the output in <div class="zui-card">.
 *
 * @package AST_Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'ast_free_settings_render_fields' ) ) {
	/**
	 * Render every field in a schema array.
	 *
	 * @param array $schema Output of an existing option builder.
	 * @return void
	 */
	function ast_free_settings_render_fields( $schema ) {
		foreach ( (array) $schema as $id => $field ) {
			ast_free_settings_render_field( (string) $id, (array) $field );
		}
	}
}

if ( ! function_exists( 'ast_free_settings_field_value' ) ) {
	/**
	 * Read the stored value the same way the save handler expects it.
	 *
	 * @param string $id    Field key (= input name).
	 * @param array  $field Field schema.
	 * @return mixed
	 */
	function ast_free_settings_field_value( $id, $field ) {
		$default     = isset( $field['default'] ) ? $field['default'] : '';
		$option_name = isset( $field['option_name'] ) ? $field['option_name'] : 'ast_general_settings';

		return get_ast_settings( $option_name, $id, $default );
	}
}

if ( ! function_exists( 'ast_free_settings_tooltip' ) ) {
	/**
	 * Echo the tipTip help icon (preserves the WooCommerce tipTip hook).
	 *
	 * @param array $field Field schema.
	 * @return void
	 */
	function ast_free_settings_tooltip( $field ) {
		if ( empty( $field['tooltip'] ) ) {
			return;
		}
		echo '<span class="zui-tooltip" tabindex="0" role="img" aria-label="' . esc_attr( $field['tooltip'] ) . '">';
		ast_free_settings_icon( 'help-circle' );
		echo '<span class="zui-tooltip__bubble">' . esc_html( $field['tooltip'] ) . '<span class="zui-tooltip__arrow"></span></span>';
		echo '</span>';
	}
}

if ( ! function_exists( 'ast_free_settings_help_line' ) ) {
	/**
	 * Echo a one-line field description under the label (matches the reference design).
	 *
	 * Sourced from the field's existing `tooltip` text (no schema change). The `?` help
	 * icon is kept on the label for parity with the reference.
	 *
	 * @param array $field Field schema.
	 * @return void
	 */
	function ast_free_settings_help_line( $field ) {
		if ( empty( $field['tooltip'] ) ) {
			return;
		}
		echo '<p class="zui-row__desc">' . esc_html( $field['tooltip'] ) . '</p>';
	}
}

if ( ! function_exists( 'ast_free_settings_render_multiselect' ) ) {
	/**
	 * Render a custom multiselect (chips + dropdown) backed by a hidden native <select>.
	 *
	 * The native <select multiple name="{id}[]"> remains the save source of truth (correct
	 * name, value encoding, id-based dirty hook). ast-settings.js builds the chip/dropdown UI
	 * and writes selections back to it, dispatching `change` so existing handlers still fire.
	 * No Select2 (the wc-enhanced-select class is intentionally omitted here).
	 *
	 * @param string $id    Field key.
	 * @param array  $field Field schema.
	 * @param bool   $assoc true = multiple_select (stored[key]==1); false = simple_multiple_select (in_array).
	 * @return void
	 */
	function ast_free_settings_render_multiselect( $id, $field, $assoc ) {
		$stored      = ast_free_settings_field_value( $id, $field );
		$stored      = is_array( $stored ) ? $stored : array();
		$placeholder = ! empty( $field['placeholder'] ) ? $field['placeholder'] : __( 'Select one or more…', 'woo-advanced-shipment-tracking' );

		/**
		 * Locked option keys for this multiselect — rendered as chips without an
		 * "×" remove icon and ignored by the dropdown's toggle handler. Used to
		 * pin required defaults (e.g. "processing" + "partial-shipped" on the
		 * Fulfillment Order Statuses field). Filter receives an empty array; the
		 * field schema and assoc-mode are passed for context.
		 */
		$locked_keys = apply_filters( "ast_settings_locked_options_{$id}", array(), $field, $assoc );
		$locked_keys = is_array( $locked_keys ) ? array_map( 'strval', $locked_keys ) : array();

		// Pre-compute selected state per option for SSR chips + native select.
		$options = array();
		foreach ( (array) $field['options'] as $key => $opt ) {
			$label = ( is_array( $opt ) && isset( $opt['status'] ) ) ? $opt['status'] : ( is_array( $opt ) ? '' : $opt );
			$sel   = $assoc ? ( isset( $stored[ $key ] ) && 1 == $stored[ $key ] ) : in_array( $key, $stored ); // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict -- legacy loose match.
			$lock  = in_array( (string) $key, $locked_keys, true );
			$options[] = array(
				'key'    => (string) $key,
				'label'  => (string) $label,
				'sel'    => (bool) $sel,
				'locked' => (bool) $lock,
			);
		}
		?>
		<div class="zui-ms" data-zui-multiselect data-placeholder="<?php echo esc_attr( $placeholder ); ?>">
			<select multiple id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $id ); ?>[]" class="zui-ms__native" hidden>
				<?php foreach ( $options as $o ) : ?>
					<option value="<?php echo esc_attr( $o['key'] ); ?>" <?php selected( $o['sel'], true ); ?> <?php echo $o['locked'] ? 'data-locked="1"' : ''; ?>><?php echo esc_html( $o['label'] ); ?></option>
				<?php endforeach; ?>
			</select>

			<div class="zui-ms__control" tabindex="0" role="combobox" aria-haspopup="listbox" aria-expanded="false">
				<div class="zui-ms__chips">
					<?php
					$has_selected = false;
					foreach ( $options as $o ) :
						if ( ! $o['sel'] ) {
							continue;
						}
						$has_selected = true;
						$chip_class   = 'zui-ms__chip' . ( $o['locked'] ? ' is-locked' : '' );
						?>
						<span class="<?php echo esc_attr( $chip_class ); ?>" data-value="<?php echo esc_attr( $o['key'] ); ?>">
							<span class="zui-ms__chip-label"><?php echo esc_html( $o['label'] ); ?></span>
							<?php if ( ! $o['locked'] ) : ?>
								<button type="button" class="zui-ms__chip-remove" aria-label="<?php echo esc_attr( $o['label'] ); ?>">&times;</button>
							<?php endif; ?>
						</span>
					<?php endforeach; ?>
					<?php if ( ! $has_selected ) : ?>
						<span class="zui-ms__placeholder"><?php echo esc_html( $placeholder ); ?></span>
					<?php endif; ?>
				</div>
				<span class="zui-ms__chevron"><?php ast_free_settings_icon( 'chevron-down' ); ?></span>
			</div>

			<div class="zui-ms__dropdown" role="listbox" hidden></div>
		</div>
		<?php
	}
}

if ( ! function_exists( 'ast_free_settings_field_desc' ) ) {
	/**
	 * Echo the input description / desc_url link.
	 *
	 * @param array  $field Field schema.
	 * @param string $style 'logs' (tgl_checkbox log fields) | 'link' (text with desc_url).
	 * @return void
	 */
	function ast_free_settings_field_desc( $field, $style = 'link' ) {
		if ( empty( $field['input_desc'] ) ) {
			return;
		}
		$desc = $field['input_desc'];

		if ( ! empty( $field['desc_url'] ) && 'logs' === $style ) {
			echo '<p class="zui-row__hint">' . esc_html( $desc ) . ' <a class="ptw_a" target="_blank" rel="noopener noreferrer" href="' . esc_url( $field['desc_url'] ) . '">' . esc_html__( 'View Logs', 'woo-advanced-shipment-tracking' ) . '</a></p>';
		} elseif ( ! empty( $field['desc_url'] ) ) {
			echo '<p class="zui-row__hint"><a target="_blank" rel="noopener noreferrer" href="' . esc_url( $field['desc_url'] ) . '">' . esc_html( $desc ) . '</a></p>';
		} else {
			echo '<p class="zui-row__hint">' . esc_html( $desc ) . '</p>';
		}
	}
}

if ( ! function_exists( 'ast_free_settings_render_field' ) ) {
	/**
	 * Render a single field as new-design markup (contract-preserving).
	 *
	 * @param string $id    Field key (= input name).
	 * @param array  $field Field schema.
	 * @return void
	 */
	function ast_free_settings_render_field( $id, $field ) {

		// Respect the existing visibility gate.
		if ( ! isset( $field['show'] ) || ! $field['show'] ) {
			return;
		}

		$type      = isset( $field['type'] ) ? $field['type'] : 'text';
		$title     = isset( $field['title'] ) ? $field['title'] : '';
		$row_class = isset( $field['class'] ) ? $field['class'] : '';

		switch ( $type ) {

			/* ---- Toggle (tgl_checkbox) + plain checkbox ---- */
			case 'tgl_checkbox':
			case 'checkbox':
				$is_on    = (bool) ast_free_settings_field_value( $id, $field );
				$is_tgl   = ( 'tgl_checkbox' === $type );
				$cb_class = $is_tgl ? 'ast-toggle ast-settings-toggle zui-toggle__input' : 'zui-checkbox__input';
				?>
				<div class="zui-row zui-row--inline <?php echo esc_attr( $row_class ); ?>">
					<div class="zui-row__head">
						<span class="zui-row__label"><?php echo esc_html( $title ); ?><?php ast_free_settings_tooltip( $field ); ?></span><?php ast_free_settings_help_line( $field ); ?>
						<?php ast_free_settings_field_desc( $field, 'logs' ); ?>
					</div>
					<div class="zui-row__control">
						<label class="<?php echo $is_tgl ? 'zui-toggle' : 'zui-checkbox'; ?>">
							<input type="hidden" name="<?php echo esc_attr( $id ); ?>" value="0">
							<input type="checkbox" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $id ); ?>" value="1" class="<?php echo esc_attr( $cb_class ); ?>" <?php checked( $is_on, true ); ?> <?php disabled( ! empty( $field['disabled'] ), true ); ?>>
							<?php if ( $is_tgl ) : ?>
								<span class="zui-toggle__track"><span class="zui-toggle__thumb"></span></span>
							<?php else : ?>
								<span class="zui-checkbox__box"></span>
							<?php endif; ?>
						</label>
					</div>
				</div>
				<?php
				break;

			/* ---- Radio (rendered as radio cards) ---- */
			case 'radio':
				$current = ast_free_settings_field_value( $id, $field );
				?>
				<div class="zui-row <?php echo esc_attr( $row_class ); ?>">
					<div class="zui-row__head">
						<span class="zui-row__label"><?php echo esc_html( $title ); ?><?php ast_free_settings_tooltip( $field ); ?></span><?php ast_free_settings_help_line( $field ); ?>
					</div>
					<div class="zui-row__control zui-radio-cards">
						<?php
						$ast_opt_desc = isset( $field['option_desc'] ) && is_array( $field['option_desc'] ) ? $field['option_desc'] : array();
						foreach ( (array) $field['options'] as $key => $val ) :
							$ast_od = isset( $ast_opt_desc[ $key ] ) ? $ast_opt_desc[ $key ] : '';
							?>
							<label class="zui-radio-card <?php echo esc_attr( $id ); ?> <?php echo ( (string) $current === (string) $key ) ? 'is-selected' : ''; ?>" for="<?php echo esc_attr( $id . '_' . $key ); ?>">
								<input type="radio" id="<?php echo esc_attr( $id . '_' . $key ); ?>" name="<?php echo esc_attr( $id ); ?>" value="<?php echo esc_attr( $key ); ?>" class="<?php echo esc_attr( $id ); ?> zui-radio-card__input" <?php checked( (string) $current, (string) $key ); ?>>
								<span class="zui-radio-card__body">
									<span class="zui-radio-card__label"><?php echo esc_html( $val ); ?></span>
									<?php if ( '' !== $ast_od ) : ?>
										<span class="zui-radio-card__desc"><?php echo esc_html( $ast_od ); ?></span>
									<?php endif; ?>
								</span>
							</label>
						<?php endforeach; ?>
					</div>
				</div>
				<?php
				break;

			/* ---- Text / Password / Number ---- */
			case 'text':
			case 'password':
			case 'number':
				$value       = ast_free_settings_field_value( $id, $field );
				$placeholder = isset( $field['placeholder'] ) ? $field['placeholder'] : '';
				// Mask credential/secret fields even when the legacy schema declares them as 'text'.
				$input_type  = ( 'text' === $type && false !== strpos( $id, 'secret' ) ) ? 'password' : $type;
				// Set autocomplete so the browser doesn't auto-fill the user's WP
				// login credentials into API-key / FTP forms, and to silence the
				// DOM warning about missing autocomplete on credential pairs.
				$autocomplete = '';
				if ( 'password' === $input_type ) {
					$autocomplete = 'new-password';
				} elseif ( 'text' === $input_type ) {
					$credential_patterns = array( 'client_id', 'username', 'consumer_key', 'public_key', 'api_key', 'user_id' );
					foreach ( $credential_patterns as $pat ) {
						if ( false !== strpos( $id, $pat ) ) {
							$autocomplete = 'username';
							break;
						}
					}
				}
				?>
				<div class="zui-row <?php echo esc_attr( $row_class ); ?>">
					<div class="zui-row__head">
						<span class="zui-row__label"><?php echo esc_html( $title ); ?><?php ast_free_settings_tooltip( $field ); ?></span><?php ast_free_settings_help_line( $field ); ?>
					</div>
					<div class="zui-row__control">
						<input type="<?php echo esc_attr( $input_type ); ?>" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $id ); ?>" value="<?php echo esc_attr( $value ); ?>" placeholder="<?php echo esc_attr( $placeholder ); ?>" class="input-text regular-input zui-input"<?php if ( '' !== $autocomplete ) : ?> autocomplete="<?php echo esc_attr( $autocomplete ); ?>"<?php endif; ?>>
						<?php ast_free_settings_field_desc( $field, 'link' ); ?>
					</div>
				</div>
				<?php
				break;

			/* ---- Single select ---- */
			case 'select':
				$current = ast_free_settings_field_value( $id, $field );
				?>
				<div class="zui-row <?php echo esc_attr( $row_class ); ?>">
					<div class="zui-row__head">
						<span class="zui-row__label"><?php echo esc_html( $title ); ?><?php ast_free_settings_tooltip( $field ); ?></span><?php ast_free_settings_help_line( $field ); ?>
					</div>
					<div class="zui-row__control">
						<div class="zui-select-wrap">
							<select id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $id ); ?>" class="zui-select">
								<?php foreach ( (array) $field['options'] as $key => $label ) : ?>
									<option value="<?php echo esc_attr( $key ); ?>" <?php selected( (string) $current, (string) $key ); ?>><?php echo esc_html( $label ); ?></option>
								<?php endforeach; ?>
							</select>
							<span class="zui-select-chevron"><?php ast_free_settings_icon( 'chevron-down' ); ?></span>
						</div>
					</div>
				</div>
				<?php
				break;

			/* ---- Multi-selects: custom chip/dropdown widget (assoc vs flat) ---- */
			case 'multiple_select':
			case 'simple_multiple_select':
				?>
				<div class="zui-row <?php echo esc_attr( $row_class ); ?>">
					<div class="zui-row__head">
						<span class="zui-row__label"><?php echo esc_html( $title ); ?><?php ast_free_settings_tooltip( $field ); ?></span><?php ast_free_settings_help_line( $field ); ?>
					</div>
					<div class="zui-row__control">
						<?php ast_free_settings_render_multiselect( $id, $field, ( 'multiple_select' === $type ) ); ?>
					</div>
				</div>
				<?php
				break;

			/* ---- Multiple checkbox (ASSOC 0/1 per option) ---- */
			case 'multiple_checkbox':
				$stored = ast_free_settings_field_value( $id, $field );
				$stored = is_array( $stored ) ? $stored : array();
				?>
				<div class="zui-row <?php echo esc_attr( $row_class ); ?>">
					<div class="zui-row__head">
						<span class="zui-row__label"><?php echo esc_html( $title ); ?></span>
					</div>
					<?php if ( ! empty( $field['desc'] ) ) : ?>
						<p class="zui-row__desc"><?php echo esc_html( $field['desc'] ); ?></p>
					<?php endif; ?>
					<div class="zui-row__control zui-checkgrid">
						<?php
						foreach ( (array) $field['options'] as $key => $opt ) :
							$label = ( is_array( $opt ) && isset( $opt['status'] ) ) ? $opt['status'] : ( is_array( $opt ) ? '' : $opt );
							?>
							<label class="zui-checkitem">
								<input type="hidden" name="<?php echo esc_attr( $id ); ?>[<?php echo esc_attr( $key ); ?>]" value="0">
								<input type="checkbox" name="<?php echo esc_attr( $id ); ?>[<?php echo esc_attr( $key ); ?>]" value="1" <?php checked( isset( $stored[ $key ] ) && 1 == $stored[ $key ], true ); ?>>
								<span><?php echo esc_html( $label ); ?></span>
							</label>
						<?php endforeach; ?>
					</div>
				</div>
				<?php
				break;

			/* ---- Button (deep-link, e.g. customizer) ---- */
			case 'button':
				?>
				<div class="zui-row <?php echo esc_attr( $row_class ); ?>">
					<div class="zui-row__head">
						<span class="zui-row__label"><?php echo esc_html( $title ); ?><?php ast_free_settings_tooltip( $field ); ?></span><?php ast_free_settings_help_line( $field ); ?>
					</div>
					<?php if ( ! empty( $field['customize_link'] ) ) : ?>
						<div class="zui-row__control">
							<a href="<?php echo esc_url( $field['customize_link'] ); ?>" class="zui-btn-secondary ts_customizer_btn"><?php esc_html_e( 'Customize', 'woo-advanced-shipment-tracking' ); ?></a>
						</div>
					<?php endif; ?>
				</div>
				<?php
				break;

			default:
				/*
				 * Unknown type: extension point so custom field types can render without
				 * editing core. With no hooks attached, output is empty — byte-identical to
				 * the previous behavior (unknown types render nothing).
				 * Handlers receive the field id + schema and should echo their own markup.
				 */
				do_action( 'ast_settings_render_field', $type, $id, $field );
				do_action( "ast_settings_render_field_{$type}", $id, $field );
				break;
		}
	}
}
