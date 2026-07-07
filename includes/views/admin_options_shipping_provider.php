<?php
/**
 * Shipping Carriers tab — re-skinned to match AST PRO carriers screen.
 *
 * Visual chrome (toolbar, bulk-select banner, grid wrapper, add tile) mirrors
 * ast-pro/includes/settings/modules/shipping-carriers/carriers.php exactly so
 * the free and pro plugins read as siblings. Every JS hook class from the
 * legacy markup is preserved verbatim so shipping_row.js / ast-settings.js
 * keep working without modification. PHP logic and AJAX endpoints unchanged.
 *
 * @package wc_advanced_shipment_tracking
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SHIPMENT_TRACKING_PATH . '/assets/zui/icons.php';

$total_enable_providers = $wpdb->get_row(
	$wpdb->prepare(
		'SELECT COUNT(*) as total_providers FROM %1s WHERE display_in_order = 1',
		$this->table
	)
);

if ( isset( $_GET['open'] ) && 'synch_providers' == $_GET['open'] ) {
	?>
	<script>
		jQuery( document ).ready(function() {
			jQuery('.sync_provider_popup').show();
		});
	</script>
<?php } ?>
<section class="tab_section ast-set-carriers" id="shipping-providers-tab">

	<div class="shipping_provider_container">

		<?php do_action( 'before_shipping_provider_list' ); ?>

		<?php /* ===== Toolbar: search (left) + 3-dot menu (right) ===== */ ?>
		<div class="ast-set-carriers-toolbar provider_top">
			<div class="ast-set-carriers-search search_section">
				<span class="ast-set-carriers-search__icon">
					<?php zui_icon( 'search' ); ?>
				</span>
				<input class="ast-set-carriers-search__input zui-input provider_search_bar" type="text" name="search_provider" id="search_provider" placeholder="<?php esc_attr_e( 'Search by carrier…', 'woo-advanced-shipment-tracking' ); ?>" autocomplete="off">
			</div>

			<div class="zui-menu provider_settings" id="ast-carriers-menu">
				<a href="javaScript:void(0);" class="zui-menu__btn provider_settings_icon" id="provider-settings" aria-haspopup="true" aria-expanded="false" aria-label="<?php esc_attr_e( 'Carrier actions', 'woo-advanced-shipment-tracking' ); ?>">
					<?php zui_icon( 'more-vertical' ); ?>
				</a>
				<ul class="zui-menu__list provider-settings-ul" style="display:none;">
					<li><a href="javaScript:void(0);" class="zui-menu__item enable_carriers"><?php zui_icon( 'layers' ); ?><span><?php esc_html_e( 'Enable Carriers', 'woo-advanced-shipment-tracking' ); ?></span></a></li>
					<li><a href="javaScript:void(0);" class="zui-menu__item add_custom_carriers"><?php zui_icon( 'plus' ); ?><span><?php esc_html_e( 'Add Custom Carrier', 'woo-advanced-shipment-tracking' ); ?></span></a></li>
					<li class="ast-menu-bulk-section"><div class="zui-menu__sep"></div></li>
					<li class="ast-menu-bulk-section"><a href="javaScript:void(0);" class="zui-menu__item reset_providers" data-reset="1"><?php zui_icon( 'check' ); ?><span><?php esc_html_e( 'Select All', 'woo-advanced-shipment-tracking' ); ?></span></a></li>
					<li class="ast-menu-bulk-section"><a href="javaScript:void(0);" class="zui-menu__item reset_providers deselect" data-reset="0"><?php zui_icon( 'x' ); ?><span><?php esc_html_e( 'Deselect All', 'woo-advanced-shipment-tracking' ); ?></span></a></li>
					<li><div class="zui-menu__sep"></div></li>
					<li><a href="javaScript:void(0);" class="zui-menu__item zui-menu__item--accent sync_providers"><?php zui_icon( 'refresh-cw' ); ?><span><?php esc_html_e( 'Sync Carriers', 'woo-advanced-shipment-tracking' ); ?></span></a></li>
				</ul>
			</div>
		</div>

		<?php if ( $total_enable_providers->total_providers > 0 ) { ?>
			<?php /* ===== Bulk-select banner: SOME selected ===== */ ?>
			<div class="ast-set-carriers-bulkbar shipping-carriers-selected-provider-message">
				<p class="ast-set-carriers-bulkbar__info selected_provider_show_notice">
					<?php zui_icon( 'info' ); ?>
					<span>
						<strong id="selected_provider_total">0</strong> <?php esc_html_e( 'carriers selected.', 'woo-advanced-shipment-tracking' ); ?>
						<a class="ast-set-carriers-bulkbar__all remove_all_shipping_carrier"><?php esc_html_e( 'Click Here', 'woo-advanced-shipment-tracking' ); ?></a>
						<?php esc_html_e( 'if you want to select all carriers.', 'woo-advanced-shipment-tracking' ); ?>
					</span>
				</p>
				<button type="button" class="ast-set-carriers-bulkbar__remove" id="delete_provider_bulk" data-remove="selected-page" style="display:none;">
					<?php zui_icon( 'trash-2' ); ?><span><?php esc_html_e( 'Remove Selected', 'woo-advanced-shipment-tracking' ); ?></span>
				</button>
			</div>

			<?php /* ===== Bulk-select banner: ALL selected ===== */ ?>
			<div class="ast-set-carriers-bulkbar all-shipping-carriers-selected">
				<p class="ast-set-carriers-bulkbar__info all_carriers_selected">
					<?php zui_icon( 'info' ); ?>
					<span>
						<?php esc_html_e( 'All Carriers Selected.', 'woo-advanced-shipment-tracking' ); ?>
						<a class="ast-set-carriers-bulkbar__all remove_selected_shipping_carrier"><?php esc_html_e( 'Undo', 'woo-advanced-shipment-tracking' ); ?></a>
					</span>
				</p>
				<button type="button" class="ast-set-carriers-bulkbar__remove delete_provider_bulk" data-remove="all">
					<?php zui_icon( 'trash-2' ); ?><span><?php esc_html_e( 'Remove Selected', 'woo-advanced-shipment-tracking' ); ?></span>
				</button>
			</div>
		<?php } ?>

		<?php /* ===== Carrier grid (server-rendered) ===== */ ?>
		<div class="provider_list">
			<?php esc_html_e( $this->get_provider_html( 1 ) ); ?>
		</div>

		<input type="hidden" id="nonce_shipping_provider" value="<?php echo esc_attr( wp_create_nonce( 'nonce_shipping_provider' ) ); ?>">

		<?php /* ===== Modal: Enable Shipping Carriers (PRO zui-modal structure) ===== */ ?>
		<div class="zui-modal add_provider_popup" id="ast-modal-enable" data-modal="enable" hidden>
			<div class="zui-modal__backdrop" data-modal-close></div>
			<div class="zui-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="ast-modal-enable-title">
				<div class="zui-modal__head">
					<div>
						<h3 class="zui-modal__title" id="ast-modal-enable-title"><?php esc_html_e( 'Enable Shipping Carriers', 'woo-advanced-shipment-tracking' ); ?></h3>
						<p class="zui-modal__sub"><?php esc_html_e( 'Activate standard carrier definitions in WooCommerce', 'woo-advanced-shipment-tracking' ); ?></p>
					</div>
					<button type="button" class="zui-modal__close add_slidout_close" data-modal-close aria-label="<?php esc_attr_e( 'Close', 'woo-advanced-shipment-tracking' ); ?>"><?php zui_icon( 'x' ); ?></button>
				</div>

				<div class="zui-modal__search">
					<span class="zui-modal__search-icon search-carrier-icon"><?php zui_icon( 'search' ); ?></span>
					<input type="text" id="search_default_provider" class="zui-input provider_search_bar" placeholder="<?php esc_attr_e( 'Search by carrier / country…', 'woo-advanced-shipment-tracking' ); ?>" autocomplete="off">
				</div>

				<div class="zui-modal__body ast-set-enable-list" id="ast-enable-list">
					<section id="add_default_carrier_section">
						<div class="default_privder_list">
							<?php esc_html_e( $this->shipping_pagination_fun( 1 ) ); ?>
						</div>
					</section>
				</div>

				<?php /* Separate footer — pagination injected here by inline JS */ ?>
				<div class="zui-modal__foot ast-set-enable-foot" id="ast-enable-foot"></div>
			</div>
		</div>

		<?php /* ===== Modal: Add Custom Carrier (PRO zui-modal + upsell) ===== */ ?>
		<div class="zui-modal add_custom_carriers_popup" id="ast-modal-add" data-modal="add" hidden>
			<div class="zui-modal__backdrop" data-modal-close></div>
			<div class="zui-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="ast-modal-add-title">
				<div class="zui-modal__head">
					<div>
						<h3 class="zui-modal__title" id="ast-modal-add-title"><?php esc_html_e( 'Add Custom Carrier', 'woo-advanced-shipment-tracking' ); ?></h3>
						<p class="zui-modal__sub"><?php esc_html_e( 'Register a local or specialised tracking provider', 'woo-advanced-shipment-tracking' ); ?></p>
					</div>
					<button type="button" class="zui-modal__close add_slidout_custom_carriers_close" data-modal-close aria-label="<?php esc_attr_e( 'Close', 'woo-advanced-shipment-tracking' ); ?>"><?php zui_icon( 'x' ); ?></button>
				</div>

				<div class="zui-modal__body">
					<section id="add_customer_carrier_section">
						<div class="zui-lock-section">
							<div class="zui-lock-section__icon"><?php zui_icon( 'lock' ); ?></div>
							<h3 class="zui-lock-section__title">
								<?php esc_html_e( 'Unlock Add Custom Carrier', 'woo-advanced-shipment-tracking' ); ?>
								<span class="zui-locked__badge">PRO</span>
							</h3>
							<p class="zui-lock-section__desc">
								<?php esc_html_e( 'Upgrade to Advanced Shipment Tracking Pro and gain the power to add custom shipping carriers with your own tracking URLs.', 'woo-advanced-shipment-tracking' ); ?>
							</p>
							<a class="zui-btn-primary zui-lock-section__cta get_feature_span" href="https://www.zorem.com/product/woocommerce-advanced-shipment-tracking/?utm_source=wp-admin&utm_medium=provider-popup&utm_campaign=upgrad-to-pro" target="_blank" rel="noopener noreferrer">
								<?php esc_html_e( 'Upgrade to PRO', 'woo-advanced-shipment-tracking' ); ?>
							</a>
						</div>
					</section>
				</div>
			</div>
		</div>

		<?php /* ===== Modal: Edit Carrier (STATIC PRO upsell — pre-rendered) =====
			 The body is a fixed PRO upsell; only the carrier name / country / logo vary
			 and are filled from the clicked card by shipping_row.js (.edit_provider) — no
			 AJAX, instant open. Inline JS below mirrors the .slideout / hidden state from
			 this inner div onto the outer modal. */
			$ast_edit_upgrade_url = 'https://www.zorem.com/product/woocommerce-advanced-shipment-tracking/?utm_source=wp-admin&utm_medium=edit-carrier&utm_campaign=upgrad-to-pro';
			?>
		<div class="zui-modal" id="ast-modal-edit" data-modal="edit" hidden>
			<div class="zui-modal__backdrop" data-modal-close></div>
			<div class="zui-modal__dialog" role="dialog" aria-modal="true">
				<div class="edit_provider_popup ast-modal-edit-content">

					<div class="zui-modal__head zui-modal__head--edit slidout_header">
						<div class="zui-modal__head-row">
							<div class="slidout_header_title">
								<h3 class="zui-modal__title slidout_title"><?php esc_html_e( 'Edit Shipping Carrier', 'woo-advanced-shipment-tracking' ); ?></h3>
								<p class="zui-modal__sub slidout_subtitle">
									<?php esc_html_e( 'Customize display preferences for', 'woo-advanced-shipment-tracking' ); ?>
									<span class="ast-edit-carrier-name"></span>
								</p>
							</div>
							<button type="button" class="zui-modal__close slidout_close edit_slidout_close" data-modal-close aria-label="<?php esc_attr_e( 'Close', 'woo-advanced-shipment-tracking' ); ?>"><?php zui_icon( 'x' ); ?></button>
						</div>

						<div class="ast-set-edit-preview">
							<span class="ast-set-carrier-logo ast-edit-carrier-logo"></span>
							<div class="min-w-0">
								<h4 class="ast-edit-carrier-name-2"></h4>
								<p class="ast-edit-carrier-country"></p>
							</div>
						</div>
					</div>

					<div class="zui-modal__body slidout_body">
						<div class="zui-lock-section">
							<div class="zui-lock-section__icon"><?php zui_icon( 'lock' ); ?></div>
							<h3 class="zui-lock-section__title">
								<?php esc_html_e( 'Unlock Edit Shipping Carrier', 'woo-advanced-shipment-tracking' ); ?>
								<span class="zui-locked__badge">PRO</span>
							</h3>
							<p class="zui-lock-section__desc">
								<?php esc_html_e( 'Upgrade to Advanced Shipment Tracking Pro to set a default carrier, override display names, map API alias names, upload custom logos and customise the tracking URL pattern.', 'woo-advanced-shipment-tracking' ); ?>
							</p>
							<a class="zui-btn-primary zui-lock-section__cta get_feature_span" href="<?php echo esc_url( $ast_edit_upgrade_url ); ?>" target="_blank" rel="noopener noreferrer">
								<?php esc_html_e( 'Upgrade to PRO', 'woo-advanced-shipment-tracking' ); ?>
							</a>
						</div>
					</div>

				</div>
			</div>
		</div>

		<?php /* ===== Inline JS: toggle-off → call existing delete AJAX directly =====
			 Same endpoint (woocommerce_shipping_provider_delete), same nonce
			 (#nonce_shipping_provider), same confirm() prompt as the legacy Delete
			 link — no functionality change, only the UI gesture (toggle vs link). */ ?>
		<script>
		(function($){
			$(document).on('click', '#provider-settings', function(){
				var hasCarriers = $('.provider-grid-row.grid-row').attr('data-shippment-providers') !== 'false';
				$('.ast-menu-bulk-section').toggle( hasCarriers );
			});

			/* Toggle .is-selected on the carrier card when its bulk checkbox changes —
			   makes the floating check chip stay visible (PRO-style) and tints the
			   card border. shipping_row.js handles the actual selection logic; we
			   only mirror it visually. Also fired on .reset_providers click via
			   delegated change event below. */
			$(document).on('change', 'input.bulk_select_provider', function(){
				$(this).closest('.ast-set-carrier-card').toggleClass('is-selected', this.checked);
			});
			$(document).on('click', '.reset_providers', function(){
				// shipping_row.js sets checked state via .prop('checked', X) which
				// doesn't fire 'change'. Re-sync after the click runs.
				setTimeout(function(){
					$('.ast-set-carrier-card').each(function(){
						var checked = $(this).find('input.bulk_select_provider').is(':checked');
						$(this).toggleClass('is-selected', checked);
					});
				}, 0);
			});
			$(document).on('click', '.remove_all_shipping_carrier, .remove_selected_shipping_carrier', function(){
				setTimeout(function(){
					$('.ast-set-carrier-card').each(function(){
						var checked = $(this).find('input.bulk_select_provider').is(':checked');
						$(this).toggleClass('is-selected', checked);
					});
				}, 0);
			});

			/* zui-modal open/close — closes via [data-modal-close] (backdrop or close X) and Esc. */
			$(document).on('click', '[data-modal-close]', function(){
				$(this).closest('.zui-modal').attr('hidden', '');
				$('body').css('overflow', '');
			});
			$(document).on('keydown', function(e){
				if ( e.key === 'Escape' ) {
					$('.zui-modal').not('[hidden]').attr('hidden', '');
					$('body').css('overflow', '');
				}
			});

			/* Legacy slideOutForm/slideInForm shim — when shipping_row.js calls
			   $('.add_provider_popup').slideOutForm(), open the matching zui-modal. */
			if ( $.fn.slideOutForm ) {
				$.fn.slideOutForm = function(){
					var $el = $(this);
					$el.addClass('slideout');
					var $modal = $el.hasClass('zui-modal') ? $el : $el.closest('.zui-modal');
					if ( $modal.length ) {
						$modal.removeAttr('hidden');
						$('body').css('overflow', 'hidden');
					}
					return $el;
				};
				$.fn.slideInForm = function(){
					var $el = $(this);
					$el.removeClass('slideout');
					var $modal = $el.hasClass('zui-modal') ? $el : $el.closest('.zui-modal');
					if ( $modal.length ) {
						$modal.attr('hidden', '');
						$('body').css('overflow', '');
					}
					return $el;
				};
			}

			/* Move pagination from inside scroll body to the separate footer (PRO structure).
			   Pinned on initial render + after every AJAX response that touches the
			   pagination endpoints (shipping_pagination / filter / search). */
			function pinPagination(){
				var $pg = $('.add_provider_popup .default_privder_list .shipping_carriers_arrow_pagination');
				var $foot = $('#ast-enable-foot');
				if ( $pg.length && $foot.length ) {
					$foot.empty().append( $pg );
				}
			}
			$(function(){ pinPagination(); });
			// Reliable hook: re-pin after every relevant AJAX returns.
			$(document).ajaxComplete(function( ev, xhr, settings ){
				var data = ( settings && settings.data ) ? String(settings.data) : '';
				if ( data.indexOf('action=shipping_pagination') !== -1
				  || data.indexOf('action=paginate_shipping_provider_list') !== -1
				  || data.indexOf('action=filter_shipping_provider_list') !== -1
				  || data.indexOf('action=search_disabled_default_carrier') !== -1 ) {
					setTimeout( pinPagination, 0 );
				}
			});

			/* Mirror .slideout class from inner .edit_provider_popup to outer #ast-modal-edit.
			   shipping_row.js calls .slideOutForm() on the inner div (because it does
			   .html() on .edit_provider_popup which would wipe the dialog chrome if it
			   were on the outer). Watch for class mutation and propagate to the modal. */
			(function(){
				var $editInner = $('#ast-modal-edit .edit_provider_popup');
				var $editModal = $('#ast-modal-edit');
				if ( ! $editInner.length || ! $editModal.length || ! window.MutationObserver ) return;
				new MutationObserver(function(){
					if ( $editInner.hasClass('slideout') ) {
						$editModal.addClass('slideout').removeAttr('hidden');
					} else {
						$editModal.removeClass('slideout').attr('hidden', '');
					}
				}).observe( $editInner[0], { attributes: true, attributeFilter: ['class'] } );
			})();

			/* PRO-style "Loading…" text during pagination AJAX (plain text, no spinner). */
			var loadingHtml = '<div class="zui-modal__loading">Loading…</div>';
			$(document).on('click', '.add_provider_popup .arrow_pagination:not([disabled])', function(){
				$('.add_provider_popup .default_privder_list').html( loadingHtml );
			});

			/* Auto-search on keyup with debounce — shipping_row.js only fires search
			   on Enter, but PRO searches live as the user types. Trigger the existing
			   .search-carrier-icon click (which calls search_disabled_default_carrier
			   AJAX) after 300ms of inactivity. Same endpoint, same nonce. */
			var astSearchTimer = null;
			$(document).on('keyup', '#search_default_provider', function( e ){
				if ( e.which === 13 ) return; // Enter — let legacy handler fire
				$('.add_provider_popup .default_privder_list').html( loadingHtml );
				clearTimeout( astSearchTimer );
				astSearchTimer = setTimeout(function(){
					$('.add_provider_popup .search-carrier-icon').trigger('click');
				}, 300);
			});
		})(jQuery);

		(function($){
			$(document).on('change', '.ast-carrier-toggle', function(){
				var $tgl = $(this);
				if ( $tgl.is(':checked') ) { return; }

				var pid   = $tgl.data('pid');
				var nonce = $('#nonce_shipping_provider').val();
				var msg   = ( window.shipment_tracking_table_rows && shipment_tracking_table_rows.i18n && shipment_tracking_table_rows.i18n.delete_provider )
								? shipment_tracking_table_rows.i18n.delete_provider
								: 'Are you sure you want to delete this shipping carrier?';

				if ( ! confirm( msg ) ) {
					$tgl.prop('checked', true);
					return;
				}

				var $card = $tgl.closest('.ast-set-carrier-card');
				$card.css('opacity', 0.5).css('pointer-events', 'none');

				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'woocommerce_shipping_provider_delete',
						provider_id: pid,
						security: nonce
					},
					success: function( response ) {
						var $outer = $('.shipping_provider_container > .provider_list').first();
						if ( $outer.length ) {
							$outer.replaceWith( response );
						} else {
							$('.provider_list').first().replaceWith( response );
						}
					},
					error: function( xhr ) {
						$card.css('opacity', '').css('pointer-events', '');
						$tgl.prop('checked', true);
						console.error('[AST carrier delete] status=' + xhr.status + ' body=', xhr.responseText);
						alert('Failed to remove carrier (HTTP ' + xhr.status + '). Check browser console for details.');
					}
				});
			});
		})(jQuery);
		</script>

		<?php /* ===== Modal: Sync Shipping Carriers (PRO zui-modal — 3 states: idle / syncing / done) ===== */ ?>
		<div class="zui-modal sync_provider_popup" id="ast-modal-sync" data-modal="sync" hidden>
			<div class="zui-modal__backdrop" data-modal-close></div>
			<div class="zui-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="ast-modal-sync-title">
				<div class="zui-modal__head">
					<div>
						<h3 class="zui-modal__title" id="ast-modal-sync-title"><?php esc_html_e( 'Sync Shipping Carriers', 'woo-advanced-shipment-tracking' ); ?></h3>
						<p class="zui-modal__sub"><?php esc_html_e( 'Coordinate local formats with Zorem global servers', 'woo-advanced-shipment-tracking' ); ?></p>
					</div>
					<button type="button" class="zui-modal__close synch_slidout_close" data-modal-close aria-label="<?php esc_attr_e( 'Close', 'woo-advanced-shipment-tracking' ); ?>"><?php zui_icon( 'x' ); ?></button>
				</div>

				<div class="zui-modal__body">
					<?php /* State: idle */ ?>
					<div class="ast-set-sync-state" data-sync-state="idle">
						<p class="ast-set-sync-desc"><?php esc_html_e( 'Syncing the shipping carriers list adds or updates the pre-set built-in shipping carriers database schema, and will not affect any custom shipping carriers you registered manually.', 'woo-advanced-shipment-tracking' ); ?></p>
						<label class="zui-checkcard">
							<input type="checkbox" id="reset_tracking_providers" name="reset_tracking_providers" value="1">
							<span class="zui-checkcard__box"><?php zui_icon( 'check' ); ?></span>
							<span class="zui-checkcard__text">
								<strong><?php esc_html_e( 'Reset carriers database', 'woo-advanced-shipment-tracking' ); ?></strong>
								<small><?php esc_html_e( 'This will reset all your existing local shipping providers to default values', 'woo-advanced-shipment-tracking' ); ?></small>
							</span>
						</label>
						<button type="button" class="zui-btn-primary zui-btn-block sync_providers_btn" id="ast-sync-start"><?php zui_icon( 'refresh-cw' ); ?><span><?php esc_html_e( 'Sync Shipping Carriers', 'woo-advanced-shipment-tracking' ); ?></span></button>
					</div>

					<?php /* State: syncing */ ?>
					<div class="ast-set-sync-state" data-sync-state="syncing" hidden>
						<div class="ast-set-sync-spinner"><?php zui_icon( 'refresh-cw' ); ?></div>
						<h4 class="ast-set-sync-heading"><?php esc_html_e( 'Sync is currently in progress…', 'woo-advanced-shipment-tracking' ); ?></h4>
						<p class="ast-set-sync-note"><?php esc_html_e( 'Please wait. Do not close your page tab or browser workspace.', 'woo-advanced-shipment-tracking' ); ?></p>
						<div class="ast-set-sync-steps" id="ast-sync-steps"></div>
					</div>

					<?php /* State: done */ ?>
					<div class="ast-set-sync-state" data-sync-state="done" hidden>
						<div class="ast-set-sync-success">
							<span class="ast-set-sync-success__icon"><?php zui_icon( 'check' ); ?></span>
							<div>
								<p class="ast-set-sync-success__title"><?php esc_html_e( 'Database Synchronized!', 'woo-advanced-shipment-tracking' ); ?></p>
								<p class="ast-set-sync-success__sub"><?php esc_html_e( 'Merchant formats are fully up-to-date.', 'woo-advanced-shipment-tracking' ); ?></p>
							</div>
						</div>
						<div class="ast-set-sync-stats">
							<div class="ast-set-sync-stat"><span><?php esc_html_e( 'Carriers Added', 'woo-advanced-shipment-tracking' ); ?></span><strong class="ast-sync-added">0</strong></div>
							<div class="ast-set-sync-stat"><span><?php esc_html_e( 'Carriers Updated', 'woo-advanced-shipment-tracking' ); ?></span><strong class="ast-sync-updated">0</strong></div>
							<div class="ast-set-sync-stat"><span><?php esc_html_e( 'Carriers Deleted', 'woo-advanced-shipment-tracking' ); ?></span><strong class="ast-sync-deleted">0</strong></div>
						</div>

						<div class="ast-set-sync-detailswrap">
							<button type="button" class="ast-set-sync-detailstoggle" id="ast-sync-details-toggle"
								data-show-label="<?php esc_attr_e( 'view details', 'woo-advanced-shipment-tracking' ); ?>"
								data-hide-label="<?php esc_attr_e( 'hide details', 'woo-advanced-shipment-tracking' ); ?>"><?php esc_html_e( 'view details', 'woo-advanced-shipment-tracking' ); ?></button>
							<div class="ast-set-sync-details" id="ast-sync-details" hidden></div>
						</div>

						<button type="button" class="zui-btn-primary zui-btn-block close_synch_popup" data-modal-close><?php esc_html_e( 'Close', 'woo-advanced-shipment-tracking' ); ?></button>
					</div>
				</div>
			</div>
		</div>

		<script>
		(function($){
			var $modal = $('#ast-modal-sync');
			if ( ! $modal.length ) return;

			function setSyncState(state){
				$modal.find('.ast-set-sync-state').each(function(){
					if ( $(this).attr('data-sync-state') === state ) { $(this).removeAttr('hidden'); }
					else { $(this).attr('hidden', ''); }
				});
			}

			function addStep( text ){
				var $steps = $('#ast-sync-steps');
				var html = '<div class="ast-set-sync-step"><svg class="zui-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg><span></span></div>';
				var $d = $(html);
				$d.find('span').text( text );
				$steps.append( $d );
			}

			/* Reset to idle when the modal opens via legacy .sync_providers trigger. */
			$(document).on('click', '.sync_providers', function(){
				setSyncState('idle');
				$('#reset_tracking_providers').prop('checked', false);
			});

			/* Override the legacy .sync_providers_btn click — same AJAX, new UI flow. */
			$(document).off('click.astSync').on('click.astSync', '#ast-sync-start', function( e ){
				e.preventDefault();
				e.stopImmediatePropagation();
				var reset_checked = $('#reset_tracking_providers').is(':checked') ? 1 : 0;
				var nonce = $('#nonce_shipping_provider').val();

				setSyncState('syncing');
				$('#ast-sync-steps').empty();
				addStep( '<?php echo esc_js( __( 'Connecting to Zorem servers…', 'woo-advanced-shipment-tracking' ) ); ?>' );

				$.ajax({
					url: ajaxurl,
					data: { action: 'sync_providers', reset_checked: reset_checked, security: nonce },
					type: 'POST',
					dataType: 'json',
					success: function( response ){
						if ( response && response.html ) { $('.provider_list').replaceWith( response.html ); }
						$('.ast-sync-added').text( response.added || 0 );
						$('.ast-sync-updated').text( response.updated || 0 );
						$('.ast-sync-deleted').text( response.deleted || 0 );
						fillSyncDetails( response );
						setSyncState('done');
					},
					error: function(){
						setSyncState('idle');
					}
				});
			});

			function extractProviderList( html ){
				var tmp = document.createElement('div');
				tmp.innerHTML = html || '';
				Array.prototype.forEach.call( tmp.querySelectorAll('a'), function(a){ a.remove(); });
				var ul = tmp.querySelector('ul');
				if ( ! ul || ! ul.querySelector('li') ) return '';
				ul.className = '';
				ul.removeAttribute('id');
				Array.prototype.forEach.call( ul.querySelectorAll('li'), function(li){ li.className = ''; });
				return ul.outerHTML;
			}
			function renderSection( label, count, html ){
				var list = extractProviderList( html );
				if ( ! list || ! count ) return '';
				return '<div class="ast-set-sync-details__section"><div class="ast-set-sync-details__sectionhead">' + label + ' (' + count + ')</div>' + list + '</div>';
			}
			function fillSyncDetails( d ){
				var $box = $('#ast-sync-details');
				var $toggle = $('#ast-sync-details-toggle');
				if ( $toggle.length ) { $toggle.text( $toggle.attr('data-show-label') ); }
				if ( ! $box.length ) return;
				var sections =
					renderSection( 'Added',   parseInt( d.added,   10 ) || 0, d.added_html ) +
					renderSection( 'Updated', parseInt( d.updated, 10 ) || 0, d.updated_html ) +
					renderSection( 'Deleted', parseInt( d.deleted, 10 ) || 0, d.deleted_html );
				var dbIcon = '<svg class="zui-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M3 5v14a9 3 0 0 0 18 0V5"/><path d="M3 12a9 3 0 0 0 18 0"/></svg>';
				$box.html( '<div class="ast-set-sync-details__head">' + dbIcon + '<span>Synchronized Providers:</span></div>' + ( sections || '<div>✔ No changes — everything is already up to date.</div>' ) ).attr('hidden', '');
			}

			$(document).on('click', '#ast-sync-details-toggle', function(){
				var $box = $('#ast-sync-details');
				var $btn = $(this);
				if ( $box.attr('hidden') !== undefined && $box.attr('hidden') !== false ) {
					$box.removeAttr('hidden');
					$btn.text( $btn.attr('data-hide-label') );
				} else {
					$box.attr('hidden', '');
					$btn.text( $btn.attr('data-show-label') );
				}
			});
		})(jQuery);
		</script>
	</div>
</section>
