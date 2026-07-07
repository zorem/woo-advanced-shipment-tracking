/**
 * AST PRO - New Settings UI shell (Phase 1).
 *
 * Plugin-specific UI wiring: custom multiselect, modal triggers, field-level
 * interactions. Section switching + mobile drawer toggling are handled by the
 * library's _wireShell (markup carries data-zui-section / data-zui-drawer-*).
 */
(function () {
	'use strict';

	function ready(fn) {
		if (document.readyState !== 'loading') {
			fn();
		} else {
			document.addEventListener('DOMContentLoaded', fn);
		}
		document.addEventListener('ast:tabswapped', fn);
	}

	ready(function () {
		var app = document.getElementById('ast-settings-app');
		if (!app) {
			return;
		}

		// Deep-link support: when URL carries #<section>, click the matching
		// sidebar item so the library's _wireShell handles the actual switch.
		if (window.location.hash) {
			var ast_hash_section = window.location.hash.replace('#', '');
			var ast_hash_item = app.querySelector('.zui-sidebar__item[data-zui-section="' + ast_hash_section + '"]');
			if (ast_hash_item) {
				ast_hash_item.click();
			}
		}

		/* ---- Generic dirty-state: ANY user input/change inside a section
		   enables that section's header save button. Programmatic events
		   (isTrusted === false) are skipped, so page-load triggers and
		   multiselect re-dispatches don't enable the button. Multiselect's
		   own commit() already enables it directly on user toggle.       ---- */
		function _astSectionDirty(e) {
			if (e.isTrusted === false) { return; }
			if (!e.target || !e.target.closest) { return; }
			var section = e.target.closest('.zui-section');
			if (!section) { return; }
			var btn = section.querySelector('.zui-section-header .woocommerce-save-button[disabled]');
			if (btn) { btn.disabled = false; }
		}
		app.addEventListener('input',  _astSectionDirty, true);
		app.addEventListener('change', _astSectionDirty, true);

		/* ---- Per-section snapshot + revert ----
		   On init, snapshot every input/select/textarea in each section.
		   When the user switches sections without saving, the section being
		   left is reverted to its snapshot (values reset, save button back
		   to disabled). After a successful save, the section is re-snapshot
		   so the new saved values become the baseline. */
		var _astSectionSnaps = {};

		function _astSnapshotSection(section) {
			if (!section) { return; }
			var key = section.getAttribute('data-zui-section') || '';
			if (!key) { return; }
			var snap = [];
			Array.prototype.forEach.call(
				section.querySelectorAll('input, textarea, select'),
				function (el) {
					var entry = { el: el };
					if (el.type === 'checkbox' || el.type === 'radio') {
						entry.checked = el.checked;
					} else if (el.multiple && el.options) {
						entry.selected = Array.prototype.filter.call(el.options, function (o) { return o.selected; })
							.map(function (o) { return o.value; });
					} else {
						entry.value = el.value;
					}
					snap.push(entry);
				}
			);
			_astSectionSnaps[key] = snap;
		}

		function _astRevertSection(section) {
			if (!section) { return; }
			var key = section.getAttribute('data-zui-section') || '';
			var snap = _astSectionSnaps[key];
			if (!snap) { return; }
			snap.forEach(function (entry) {
				var el = entry.el;
				if (!el || !el.isConnected) { return; }
				var changed = false;
				if (entry.checked !== undefined) {
					if (el.checked !== entry.checked) { el.checked = entry.checked; changed = true; }
				} else if (entry.selected !== undefined) {
					Array.prototype.forEach.call(el.options, function (o) {
						var should = entry.selected.indexOf(o.value) !== -1;
						if (o.selected !== should) { o.selected = should; changed = true; }
					});
				} else {
					if (el.value !== entry.value) { el.value = entry.value; changed = true; }
				}
				// Fire programmatic events for inputs that actually reverted so
				// dependent UI handlers (row dim, color preview, etc.) re-sync.
				// isTrusted is false, so the section dirty handler skips them;
				// the save button is re-disabled explicitly below.
				if (changed) {
					el.dispatchEvent(new Event('input', { bubbles: true }));
					el.dispatchEvent(new Event('change', { bubbles: true }));
				}
			});
			// Re-render any multiselect widget inside this section so its chip
			// view reflects the restored option.selected state.
			Array.prototype.forEach.call(section.querySelectorAll('[data-zui-multiselect]'), function (ms) {
				if (typeof ms._zuiRefresh === 'function') { ms._zuiRefresh(); }
			});
			// Section is now clean again — disable the save button.
			var btn = section.querySelector('.zui-section-header .woocommerce-save-button');
			if (btn) { btn.disabled = true; }
		}

		// Initial snapshot of every section currently in the DOM.
		Array.prototype.forEach.call(app.querySelectorAll('.zui-section[data-zui-section]'), _astSnapshotSection);

		// Track which section was active so we can revert it on switch.
		var _astActiveSection = (function () {
			var on = app.querySelector('.zui-section.is-active[data-zui-section]')
				|| app.querySelector('.zui-section[data-zui-section]:not([hidden])');
			return on ? on.getAttribute('data-zui-section') : '';
		})();

		// Re-snapshot a section after its save succeeds (its new values are now the baseline).
		jQuery(document).on('ajaxComplete', function (ev, xhr, settings) {
			var data = (settings && settings.data) ? settings.data : '';
			if (data.indexOf('wc_ast_settings_form_update') === -1) { return; }
			if (!xhr || xhr.status < 200 || xhr.status >= 300) { return; }
			if (!_astActiveSection) { return; }
			var section = app.querySelector('.zui-section[data-zui-section="' + _astActiveSection + '"]');
			if (section) { _astSnapshotSection(section); }
		});

		// Keep the URL in sync AND revert the previous section if unsaved.
		// We write `?section=<key>` (not a `#hash`) so PHP's layout-app.php can read
		// the active section via $_GET['section'] on refresh and render the correct
		// panel directly — without this the page briefly shows the default General
		// section before JS swaps it (flash of wrong content on refresh).
		app.addEventListener('zui:section', function (ev) {
			var newKey = ev.detail && ev.detail.section ? ev.detail.section : '';
			if (_astActiveSection && _astActiveSection !== newKey) {
				var prev = app.querySelector('.zui-section[data-zui-section="' + _astActiveSection + '"]');
				if (prev) {
					var prevBtn = prev.querySelector('.zui-section-header .woocommerce-save-button');
					if (prevBtn && !prevBtn.disabled) {
						_astRevertSection(prev);
					}
				}
			}
			_astActiveSection = newKey;
			if (window.history && window.history.replaceState && newKey) {
				try {
					var url = new URL(window.location.href);
					url.searchParams.set('section', newKey);
					url.hash = '';                              // clear any stale #section from older builds
					window.history.replaceState(null, '', url.pathname + url.search);
				} catch (err) { /* URL ctor not supported — silently skip */ }
			}
		});

		/* ---- Save button: immediate feedback while AJAX is in flight ---- */
		document.addEventListener('click', function (e) {
			var btn = e.target.closest('.zui-savebtn.woocommerce-save-button');
			if (!btn || btn.disabled || btn.classList.contains('is-saving')) { return; }

			var label      = btn.querySelector('.zui-savebtn__label');
			var origText   = label ? label.textContent : '';
			var savingText = btn.getAttribute('data-label-saving') || 'Saving\u2026';

			btn.classList.add('is-saving');
			if (label) { label.textContent = savingText; }

			var done = false;
			function finish(ok) {
				if (done) { return; }
				done = true;
				btn.classList.remove('is-saving');
				if (label) { label.textContent = origText; }
				if (ok) {
					// Snackbar shows "Data saved successfully" — skip the green
					// state and revert straight to the disabled baseline so the
					// button waits for the next dirty change.
					btn.disabled = true;
				}
			}

			// Listen on jQuery's ajaxComplete — fires after the settings AJAX finishes
			// (whether success or error).  Filter by action so unrelated AJAX is ignored.
			var saveActions = ['wc_ast_settings_form_update', 'wc_usage_tracking_form_update'];
			var jqHandler = function (ev, xhr, settings) {
				var data = (settings && settings.data) ? settings.data : '';
				var isOurs = saveActions.some(function (a) { return data.indexOf(a) !== -1; });
				if (!isOurs) { return; }
				jQuery(document).off('ajaxComplete', jqHandler);
				finish(xhr && xhr.status >= 200 && xhr.status < 300);
			};
			if (window.jQuery) { jQuery(document).on('ajaxComplete', jqHandler); }
		}, true);

		/* ---- CSV import: show/hide Import Day / Import Time rows by frequency ----
		   Re-implemented for the new .zui-row markup (legacy targets .multiple_select_li). */
		(function () {
			var freq = document.getElementById('import_frequency');
			if (!freq) {
				return;
			}
			var dayEl = document.getElementById('import_day');
			var timeEl = document.getElementById('import_time');
			var dayRow = dayEl ? dayEl.closest('.zui-row') : null;
			var timeRow = timeEl ? timeEl.closest('.zui-row') : null;

			function syncFrequency() {
				var v = freq.value;
				if (dayRow) {
					dayRow.style.display = (v === 'weekly') ? 'block' : 'none';
				}
				if (timeRow) {
					timeRow.style.display = (v === 'weekly' || v === 'daily') ? 'block' : 'none';
				}
			}

			freq.addEventListener('change', syncFrequency);
			syncFrequency();
		})();

		/* ---- Order Statuses (OSM): row dim, live color preview, save-enable on color ---- */
		// Scope save-button enable to ONLY the section that contains the field
		// that changed. Previously this used app.querySelectorAll which enabled
		// EVERY section's save button across the whole settings app — so a
		// color-picker change in Order Statuses also enabled General Settings'
		// save button, and switching sections left the wrong button dirty.
		function enableSectionSave(e) {
			var target = e && e.target;
			var scope  = (target && target.closest && target.closest('.zui-section')) || app;
			Array.prototype.forEach.call(
				scope.querySelectorAll('.zui-section-header .woocommerce-save-button[disabled]'),
				function (b) { b.disabled = false; }
			);
		}

		// Perceived-brightness auto-contrast — given a bg hex, returns black or white
		// so a foreground glyph (icon SVG) stays visible against any picked color.
		function autoContrast(hex) {
			hex = String(hex || '').replace('#', '');
			if (3 === hex.length) { hex = hex[0]+hex[0]+hex[1]+hex[1]+hex[2]+hex[2]; }
			if (6 !== hex.length) { return '#ffffff'; }
			var r = parseInt(hex.substr(0, 2), 16);
			var g = parseInt(hex.substr(2, 2), 16);
			var b = parseInt(hex.substr(4, 2), 16);
			var lum = (0.299 * r + 0.587 * g + 0.114 * b) / 255;
			return lum > 0.55 ? '#000000' : '#ffffff';
		}

		Array.prototype.forEach.call(app.querySelectorAll('.ast-set-osm-row[data-osm-row]'), function (row) {
			var toggle = row.querySelector('.zui-toggle__input');
			var colorInput = row.querySelector('.ast-set-osm-color');
			var iconBox = row.querySelector('[data-osm-icon]');
			var statusPill = row.querySelector('[data-osm-pill]');

			function syncEnabled() {
				if (!toggle) { return; }
				var on = toggle.checked;
				row.classList.toggle('is-disabled', !on);
				var bg = on && colorInput ? colorInput.value : '#cbd5e1';
				if (iconBox) {
					iconBox.style.backgroundColor = bg;
					iconBox.style.color = autoContrast(bg);   // SVG stays visible
				}
			}

			if (toggle) {
				toggle.addEventListener('change', syncEnabled);
				syncEnabled();   // sync initial state
			}
			if (colorInput) {
				var colorDot = row.querySelector('.ast-set-osm-color-dot');
				var colorHex = row.querySelector('.ast-set-osm-color-hex');
				colorInput.addEventListener('input', function () {
					var on = !toggle || toggle.checked;
					if (iconBox && on) {
						iconBox.style.backgroundColor = colorInput.value;
						iconBox.style.color = autoContrast(colorInput.value);   // re-auto-contrast SVG
					}
					if (statusPill && on) { statusPill.style.color = colorInput.value; }
					if (colorDot) { colorDot.style.backgroundColor = colorInput.value; }
					if (colorHex) { colorHex.textContent = colorInput.value.toUpperCase(); }
				});
				colorInput.addEventListener('change', enableSectionSave);
			}

			// Theme dropdown (Light / Dark Font) → opacity-only modifier on icon box
			// and title text. NO LONGER overrides icon SVG colour — autoContrast()
			// handles that based on bg, so the SVG is always visible.
			var themeSelect = row.querySelector('.custom_order_color_select');
			if (themeSelect) {
				themeSelect.addEventListener('change', function () {
					var fc = themeSelect.value || '#fff';
					var op = ('#fff' === fc) ? '0.6' : '1';
					if (iconBox)    { iconBox.style.opacity = op; }
					if (statusPill) { statusPill.style.opacity = op; }
				});
			}
		});

		// Rename "Completed"->"Shipped" is mutually exclusive with the standalone Shipped status.
		var renameToggle = document.getElementById('wc_ast_status_shipped');
		var shippedToggle = document.getElementById('wc_ast_status_new_shipped');
		if (renameToggle && shippedToggle) {
			renameToggle.addEventListener('change', function () {
				if (renameToggle.checked && shippedToggle.checked) {
					shippedToggle.checked = false;
					shippedToggle.dispatchEvent(new Event('change', { bubbles: true }));
				}
			});
		}

		/* ---------- Master toggle → dependent rows visibility ----------
		 * When a "master enable" toggle is OFF, hide every following sibling
		 * of the master's `.zui-row` (rows, save bars, test-connection rows,
		 * anything else inside the same card). Used by PayPal, Stripe and
		 * CSV Auto-Import sections — the dependent fields + buttons make no
		 * sense without the master enabled, so we keep the UI clean. */
		function bindMasterToggle(masterId) {
			var master = document.getElementById(masterId);
			if (!master) { return; }
			var masterRow = master.closest('.zui-row');
			if (!masterRow) { return; }

			// Prefer the master's surrounding `.zui-card` to gather ALL dependent rows
			// (every child of the card except the master's own row). More reliable than
			// `nextElementSibling` chains, which silently break when the master's row
			// is wrapped (e.g. in a sub-container) and miss the dependents entirely.
			var card = master.closest('.zui-card') || master.closest('.zui-section') || masterRow.parentElement;
			var dependents = [];
			if (card) {
				Array.prototype.forEach.call(card.children, function (el) {
					if (el !== masterRow) { dependents.push(el); }
				});
			}
			if (!dependents.length) {
				// Final fallback: original sibling-chain (kept for any unusual markup
				// where the master isn't a direct child of a `.zui-card`).
				var sib = masterRow.nextElementSibling;
				while (sib) { dependents.push(sib); sib = sib.nextElementSibling; }
			}
			if (!dependents.length) { return; }

			function sync() {
				var on = master.checked;
				dependents.forEach(function (el) {
					if (on) { el.removeAttribute('hidden'); }
					else    { el.setAttribute('hidden', ''); }
				});
			}

			// Always re-apply the initial visibility on every `ready()` fire — cheap, and
			// guarantees that page reloads (with the toggle saved as OFF) render with
			// dependents already hidden, no flash.
			sync();

			// Bind the change listener only ONCE. `ready()` re-fires on every
			// `ast:tabswapped` event so without this guard we would stack a new
			// listener every time → user toggles once, N AJAX calls + N snackbars fire.
			if (master.dataset.masterBound === '1') { return; }
			master.dataset.masterBound = '1';

			// Instant-save the toggle change (no Save-button click needed) — only this
			// key is updated server-side via the allowlisted endpoint; sibling fields
			// keep their saved values untouched. For the CSV master, the server also
			// reschedules/clears the cron immediately.
			function autoSave() {
				if ( !window.ast_settings || !ast_settings.ajax_url || !ast_settings.master_toggle_nonce ) { return; }
				var data = new FormData();
				data.append( 'action', 'ast_master_toggle_save' );
				data.append( 'nonce',  ast_settings.master_toggle_nonce );
				data.append( 'key',    masterId );
				data.append( 'value',  master.checked ? '1' : '0' );

				master.disabled = true;
				fetch( ast_settings.ajax_url, { method: 'POST', credentials: 'same-origin', body: data } )
					.then( function ( r ) { return r.json(); } )
					.then( function ( res ) {
						if ( res && res.success ) {
							// New value is now the saved baseline — update ONLY this toggle's
							// entry in the section snapshot so the tab-swap "revert if dirty"
							// logic doesn't undo this toggle. We deliberately do NOT touch
							// any other fields' snapshots or the section save button — if the
							// user had other dirty changes, they must still save via the
							// section's Save Changes button (legacy flow untouched).
							var section = master.closest( '.zui-section[data-zui-section]' );
							var key     = section ? section.getAttribute( 'data-zui-section' ) || '' : '';
							var snap    = key ? _astSectionSnaps[ key ] : null;
							if ( snap ) {
								for ( var i = 0; i < snap.length; i++ ) {
									if ( snap[ i ].el === master ) {
										snap[ i ].checked = master.checked;
										break;
									}
								}
							}
							if ( window.ZUI && ZUI.snackbar ) {
								ZUI.snackbar( 'Saved', { type: 'success', duration: 1500 } );
							}
						} else {
							// rollback toggle if server rejected
							master.checked = !master.checked;
							sync();
							if ( window.ZUI && ZUI.snackbar ) { ZUI.snackbar( 'Save failed', { type: 'error' } ); }
						}
					} )
					.catch( function () {
						master.checked = !master.checked;
						sync();
						if ( window.ZUI && ZUI.snackbar ) { ZUI.snackbar( 'Save failed', { type: 'error' } ); }
					} )
					.finally( function () { master.disabled = false; } );
			}

			master.addEventListener('change', function (e) {
				sync();
				// Only auto-save on genuine user clicks. `isTrusted === false` on
				// programmatic dispatches (e.g. tab-swap re-renders, dirty-state
				// trigger('change') calls) which must not fire a save.
				if ( e.isTrusted ) { autoSave(); }
			});
			// Initial visibility was already applied above (before the guard) so this
			// call would be redundant on first bind. Kept intentionally out.
		}

		// PRO-only master toggles (PayPal, Stripe, CSV FTP auto-import) — those fields
		// are not present in the free build, so bindings would no-op. Intentionally omitted.
	});
})();

/**
 * AST PRO - Shipping Carriers tab interactions (Phase 3: full AJAX wiring).
 *
 * UI: 3-dot menu, modal open/close, bulk-select.
 * Data: reuses the EXISTING AJAX endpoints (exact nonces/params) for every mutation.
 * Editing / adding carriers is a PRO-only feature — the free build only renders the
 * upsell modals, so no edit/add form wiring exists here.
 */
(function () {
	'use strict';

	function ready(fn) {
		if (document.readyState !== 'loading') { fn(); }
		else { document.addEventListener('DOMContentLoaded', fn); }
		document.addEventListener('ast:tabswapped', fn);
	}

	ready(function () {
		var root = document.getElementById('ast-set-carriers');
		if (!root) { return; }

		var ajaxurl = window.ajaxurl || (window.location.origin + '/wp-admin/admin-ajax.php');
		var NONCE = root.getAttribute('data-nonce'); // nonce_shipping_provider

		var grid = document.getElementById('ast-carriers-grid');
		var bulkbar = document.getElementById('ast-carriers-bulkbar');
		var bulkCount = bulkbar ? bulkbar.querySelector('.ast-bulk-count') : null;
		var addTileHTML = (grid && grid.querySelector('.ast-set-carrier-add-tile'))
			? grid.querySelector('.ast-set-carrier-add-tile').outerHTML : '';
		var currentSearch = '';

		/* ---------- AJAX helpers ---------- */
		function post(data) {
			var body = new URLSearchParams();
			Object.keys(data).forEach(function (k) {
				var v = data[k];
				if (Array.isArray(v)) { v.forEach(function (item) { body.append(k + '[]', item); }); }
				else { body.append(k, v); }
			});
			return fetch(ajaxurl, {
				method: 'POST',
				credentials: 'same-origin',
				headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
				body: body.toString()
			});
		}
		function postForm(form) {
			return fetch(ajaxurl, { method: 'POST', credentials: 'same-origin', body: new FormData(form) });
		}
		function busy(el, on) { if (el) { el.classList.toggle('is-busy', !!on); } }

		/* ---------- Modals ---------- */
		function setSyncState(state) {
			var modal = document.getElementById('ast-modal-sync');
			if (!modal) { return; }
			Array.prototype.forEach.call(modal.querySelectorAll('.ast-set-sync-state'), function (el) {
				if (el.getAttribute('data-sync-state') === state) { el.removeAttribute('hidden'); }
				else { el.setAttribute('hidden', ''); }
			});
		}
		function openModal(name) {
			var modal = document.getElementById('ast-modal-' + name);
			if (!modal) { return null; }
			if (name === 'sync') { setSyncState('idle'); }
			modal.removeAttribute('hidden');
			return modal;
		}
		function closeAllModals() {
			Array.prototype.forEach.call(root.querySelectorAll('.zui-modal:not([hidden])'), function (m) {
				m.setAttribute('hidden', '');
			});
		}
		root.addEventListener('click', function (e) {
			var closer = e.target.closest('[data-modal-close]');
			if (closer) { var m = closer.closest('.zui-modal'); if (m) { m.setAttribute('hidden', ''); } }
		});
		document.addEventListener('keydown', function (e) { if (e.key === 'Escape') { closeAllModals(); } });

		/* ---------- 3-dot menu ---------- */
		var menu = document.getElementById('ast-carriers-menu');
		var menuBtn = document.getElementById('ast-carriers-menu-btn');
		var menuList = menu ? menu.querySelector('.zui-menu__list') : null;
		function closeMenu() {
			if (menuList) { menuList.setAttribute('hidden', ''); }
			if (menuBtn) { menuBtn.setAttribute('aria-expanded', 'false'); }
		}
		if (menuBtn && menuList) {
			menuBtn.addEventListener('click', function (e) {
				e.stopPropagation();
				if (menuList.hasAttribute('hidden')) { menuList.removeAttribute('hidden'); menuBtn.setAttribute('aria-expanded', 'true'); }
				else { closeMenu(); }
			});
			document.addEventListener('click', function (e) { if (menu && !menu.contains(e.target)) { closeMenu(); } });
		}

		/* ---------- Bulk select ---------- */
		function refreshBulkBar() {
			var n = grid ? grid.querySelectorAll('.ast-carrier-select:checked').length : 0;
			if (bulkCount) { bulkCount.textContent = String(n); }
			if (bulkbar) { if (n > 0) { bulkbar.removeAttribute('hidden'); } else { bulkbar.setAttribute('hidden', ''); } }
		}
		function setAllSelected(state) {
			if (!grid) { return; }
			Array.prototype.forEach.call(grid.querySelectorAll('.ast-carrier-select'), function (cb) {
				cb.checked = state;
				var card = cb.closest('.ast-set-carrier-card');
				if (card) { card.classList.toggle('is-selected', state); }
			});
			refreshBulkBar();
		}
		if (grid) {
			grid.addEventListener('change', function (e) {
				var cb = e.target.closest('.ast-carrier-select');
				if (!cb) { return; }
				var card = cb.closest('.ast-set-carrier-card');
				if (card) { card.classList.toggle('is-selected', cb.checked); }
				refreshBulkBar();
			});
		}

		/* ---------- Menu / tile / banner actions ---------- */
		root.addEventListener('click', function (e) {
			var btn = e.target.closest('[data-action]');
			if (!btn || !root.contains(btn)) { return; }
			switch (btn.getAttribute('data-action')) {
				case 'enable': openModal('enable'); loadDisabled(true); break;
				case 'add': openModal('add'); break;
				case 'sync': openModal('sync'); break;
				case 'select-all':
				case 'select-all-global': setAllSelected(true); break;
				case 'deselect-all': setAllSelected(false); break;
			}
			closeMenu();
		});

		/* ---------- Grid refresh (new design) ---------- */
		function refreshGrid() {
			busy(grid, true);
			return post({ action: 'ast_carriers_grid', security: NONCE, search: currentSearch })
				.then(function (r) { return r.text(); })
				.then(function (html) {
					if (grid) { grid.innerHTML = addTileHTML + html; }
					busy(grid, false);
					refreshBulkBar();
				})
				.catch(function () { busy(grid, false); });
		}

		/* ---------- Card toggle: enable/disable ---------- */
		if (grid) {
			grid.addEventListener('change', function (e) {
				var cb = e.target.closest('.ast-carrier-status');
				if (!cb) { return; }
				post({ action: 'update_shipment_status', security: NONCE, id: cb.getAttribute('data-pid'), checked: cb.checked ? 1 : 0 })
					.then(function () { refreshGrid(); });
			});
		}

		/* ---------- Main search ---------- */
		var searchInput = document.getElementById('ast-carrier-search');
		if (searchInput) {
			var st;
			searchInput.addEventListener('input', function () {
				clearTimeout(st);
				st = setTimeout(function () { currentSearch = searchInput.value.trim(); refreshGrid(); }, 300);
			});
		}

		/* ---------- Remove Selected (bulk) ---------- */
		var removeBtn = document.getElementById('ast-carriers-remove-selected');
		if (removeBtn) {
			removeBtn.addEventListener('click', function () {
				var ids = [];
				Array.prototype.forEach.call(grid.querySelectorAll('.ast-carrier-select:checked'), function (cb) { ids.push(cb.value); });
				if (!ids.length) { return; }
				// Show loading state — bulk delete + grid refresh can take several seconds with many carriers.
				var originalHtml = removeBtn.innerHTML;
				var count = ids.length;
				removeBtn.disabled = true;
				removeBtn.classList.add('is-loading');
				removeBtn.innerHTML = '<span class="ast-set-carriers-bulkbar__spinner" aria-hidden="true"></span><span>Removing…</span>';
				function restoreRemoveBtn() {
					removeBtn.disabled = false;
					removeBtn.classList.remove('is-loading');
					removeBtn.innerHTML = originalHtml;
				}
				post({ action: 'update_provider_status', security: NONCE, providers_id: ids, data_remove_selected: 'selected-page' })
					.then(function () { return refreshGrid(); })
					.then(function () {
						restoreRemoveBtn();
						if (window.ZUI && ZUI.snackbar) {
							ZUI.snackbar(count + (count === 1 ? ' carrier removed' : ' carriers removed'), { type: 'success' });
						}
					})
					.catch(function () {
						restoreRemoveBtn();
						if (window.ZUI && ZUI.snackbar) {
							ZUI.snackbar('Failed to remove carriers', { type: 'error' });
						}
					});
			});
		}

		/* ---------- Enable modal ---------- */
		var enableModal = document.getElementById('ast-modal-enable');
		var enableList = document.getElementById('ast-enable-list');
		var enableSearchEl = document.getElementById('ast-enable-search');
		var enableState = { page: 1, pages: 1, search: '' };

		function updateEnablePager() {
			if (!enableModal) { return; }
			var pg = enableModal.querySelector('.ast-enable-page');
			var pgs = enableModal.querySelector('.ast-enable-pages');
			if (pg) { pg.textContent = enableState.page; }
			if (pgs) { pgs.textContent = enableState.pages; }
			var prev = enableModal.querySelector('[data-enable-prev]');
			var next = enableModal.querySelector('[data-enable-next]');
			if (prev) { prev.disabled = enableState.page <= 1; }
			if (next) { next.disabled = enableState.page >= enableState.pages; }
		}
		function loadDisabled(reset) {
			if (reset) { enableState.page = 1; enableState.search = enableSearchEl ? enableSearchEl.value.trim() : ''; }
			if (enableList) { enableList.innerHTML = '<div class="zui-modal__loading">Loading…</div>'; }
			post({ action: 'ast_carriers_disabled', security: NONCE, page: enableState.page, search: enableState.search })
				.then(function (r) { return r.json(); })
				.then(function (res) {
					if (!res || !res.success) { return; }
					if (enableList) { enableList.innerHTML = res.data.html; }
					enableState.page = res.data.page;
					enableState.pages = res.data.pages;
					updateEnablePager();
				});
		}
		if (enableSearchEl) {
			var et;
			enableSearchEl.addEventListener('input', function () {
				clearTimeout(et);
				et = setTimeout(function () { loadDisabled(true); }, 300);
			});
		}
		if (enableModal) {
			enableModal.addEventListener('click', function (e) {
				if (e.target.closest('[data-enable-prev]')) { if (enableState.page > 1) { enableState.page--; loadDisabled(false); } return; }
				if (e.target.closest('[data-enable-next]')) { if (enableState.page < enableState.pages) { enableState.page++; loadDisabled(false); } return; }
				var enableBtn = e.target.closest('.ast-set-enable-btn');
				if (enableBtn && !enableBtn.classList.contains('is-added')) {
					enableBtn.disabled = true;
					post({ action: 'update_shipment_status', security: NONCE, id: enableBtn.getAttribute('data-pid'), checked: 1 })
						.then(function () {
							// Keep the row in place; just mark it Added + disable. Grid updates in the background.
							enableBtn.classList.add('is-added');
							enableBtn.textContent = enableBtn.getAttribute('data-added-label') || 'Added';
							refreshGrid();
						})
						.catch(function () { enableBtn.disabled = false; });
				}
			});
		}

		/* ---------- Sync ---------- */
		var syncStartBtn = document.getElementById('ast-sync-start');
		if (syncStartBtn) {
			syncStartBtn.addEventListener('click', function () {
				var reset = document.getElementById('ast-sync-reset');
				var steps = document.getElementById('ast-sync-steps');
				setSyncState('syncing');
				if (steps) { steps.innerHTML = ''; addStep(steps, 'Connecting to Zorem servers…'); }
				post({ action: 'sync_providers', security: NONCE, reset_checked: (reset && reset.checked) ? 1 : 0 })
					.then(function (r) { return r.json(); })
					.then(function (res) {
						var d = (res && typeof res.added !== 'undefined') ? res : (res && res.data ? res.data : {});
						setText('.ast-sync-added', d.added || 0);
						setText('.ast-sync-updated', d.updated || 0);
						setText('.ast-sync-deleted', d.deleted || 0);
						fillSyncDetails(d);
						setSyncState('done');
						refreshGrid();
					})
					.catch(function () { setSyncState('idle'); });
			});
		}
		function addStep(container, text) {
			var d = document.createElement('div');
			d.className = 'ast-set-sync-step';
			d.innerHTML = '<svg class="zui-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg><span></span>';
			d.querySelector('span').textContent = text;
			container.appendChild(d);
		}
		function setText(sel, val) {
			var el = root.querySelector(sel);
			if (el) { el.textContent = String(val); }
		}
		/* Extract just the <ul>…</ul> list of provider names out of the legacy
		 * `added_html` / `updated_html` / `deleted_html` blobs produced server-side
		 * (which also include "view / hide details" anchor tags we don't want).
		 * Strips the legacy class `.updated_details` (and the row IDs) because the
		 * old `admin.css` styles it `display:none` + paints `<li>`s as chunky cyan
		 * pills — both unwanted inside the new sync details box. */
		function extractProviderList(html) {
			var tmp = document.createElement('div');
			tmp.innerHTML = html || '';
			Array.prototype.forEach.call(tmp.querySelectorAll('a'), function (a) { a.remove(); });
			var ul = tmp.querySelector('ul');
			if (!ul) { return ''; }
			if (!ul.querySelector('li')) { return ''; }
			ul.className = '';                       // drop .updated_details (legacy display:none)
			ul.removeAttribute('id');                // drop e.g. id="added_providers"
			Array.prototype.forEach.call(ul.querySelectorAll('li'), function (li) {
				li.className = '';                   // defensive: drop any legacy classes on items too
			});
			return ul.outerHTML;
		}
		function renderSection(label, count, html) {
			var list = extractProviderList(html);
			if (!list || !count) { return ''; }
			return '<div class="ast-set-sync-details__section">' +
				'<div class="ast-set-sync-details__sectionhead">' + label + ' (' + count + ')</div>' +
				list +
				'</div>';
		}
		function fillSyncDetails(d) {
			var box = document.getElementById('ast-sync-details');
			var toggle = document.getElementById('ast-sync-details-toggle');
			if (toggle) { toggle.textContent = toggle.getAttribute('data-show-label') || 'view details'; }
			if (!box) { return; }
			var sections =
				renderSection('Added',   parseInt(d.added,   10) || 0, d.added_html) +
				renderSection('Updated', parseInt(d.updated, 10) || 0, d.updated_html) +
				renderSection('Deleted', parseInt(d.deleted, 10) || 0, d.deleted_html);
			var dbIcon = '<svg class="zui-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M3 5v14a9 3 0 0 0 18 0V5"/><path d="M3 12a9 3 0 0 0 18 0"/></svg>';
			box.innerHTML = '<div class="ast-set-sync-details__head">' + dbIcon + '<span>Synchronized Providers:</span></div>' +
				(sections || '<div>✔ No changes — everything is already up to date.</div>');
			box.setAttribute('hidden', '');
		}

		var syncDetailsToggle = document.getElementById('ast-sync-details-toggle');
		if (syncDetailsToggle) {
			syncDetailsToggle.addEventListener('click', function () {
				var box = document.getElementById('ast-sync-details');
				if (!box) { return; }
				if (box.hasAttribute('hidden')) {
					box.removeAttribute('hidden');
					syncDetailsToggle.textContent = syncDetailsToggle.getAttribute('data-hide-label') || 'hide details';
				} else {
					box.setAttribute('hidden', '');
					syncDetailsToggle.textContent = syncDetailsToggle.getAttribute('data-show-label') || 'view details';
				}
			});
		}

		/* ---------- Logo upload / remove ---------- */
		function syncLogoRemove(wrap) {
			if (!wrap) { return; }
			var urlInput = wrap.querySelector('.ast-carrier-thumb-url');
			var rm = wrap.querySelector('.ast-carrier-logo-remove');
			if (!rm) { return; }
			if (urlInput && urlInput.value) { rm.removeAttribute('hidden'); }
			else { rm.setAttribute('hidden', ''); }
		}
		root._astSyncLogoRemove = syncLogoRemove;

		root.addEventListener('click', function (e) {
			// Remove logo -> clears the field; on save the carrier reverts to its default logo.
			var rm = e.target.closest('.ast-carrier-logo-remove');
			if (rm) {
				e.preventDefault();
				var w = rm.closest('.zui-upload');
				if (w) {
					var u = w.querySelector('.ast-carrier-thumb-url');
					var i = w.querySelector('.ast-carrier-thumb-id');
					if (u) { u.value = ''; }
					if (i) { i.value = '0'; }
				}
				rm.setAttribute('hidden', '');
				return;
			}

			var up = e.target.closest('.ast-carrier-upload');
			if (!up) { return; }
			e.preventDefault();
			if (!window.wp || !window.wp.media) { return; }
			var frame = window.wp.media({ title: 'Select carrier logo', button: { text: 'Use logo' }, multiple: false });
			frame.on('select', function () {
				var att = frame.state().get('selection').first().toJSON();
				var wrap = up.closest('.zui-upload');
				if (wrap) {
					var urlInput = wrap.querySelector('.ast-carrier-thumb-url');
					var idInput = wrap.querySelector('.ast-carrier-thumb-id');
					if (urlInput) { urlInput.value = att.url; }
					if (idInput) { idInput.value = att.id; }
					syncLogoRemove(wrap);
				}
			});
			frame.open();
		});
	});
})();

/**
 * AST PRO - CSV Import tab UI helpers.

/**
 * AST PRO - CSV Import tab UI helpers.
 *
 * The actual parse + per-row import + progress/logs is driven by the existing
 * shipping_row.js (bound to #wc_ast_upload_csv_form and the preserved hooks). This
 * module only adds new-design conveniences: the Choose-file button + filename label,
 * drag-and-drop into the file input, and counting success/fail for the Done stats.
 */
(function () {
	'use strict';

	function ready(fn) {
		if (document.readyState !== 'loading') { fn(); }
		else { document.addEventListener('DOMContentLoaded', fn); }
		document.addEventListener('ast:tabswapped', fn);
	}

	ready(function () {
		var root = document.getElementById('ast-set-csv');
		if (!root) { return; }

		var fileInput = document.getElementById('trcking_csv_file');
		var chooseBtn = document.getElementById('ast-csv-choose');
		var fileLabel = document.getElementById('ast-csv-filename');
		var drop = document.getElementById('ast-csv-drop');
		var dropEmpty = drop ? drop.querySelector('.ast-set-csv-drop__empty') : null;
		var dropSelected = document.getElementById('ast-csv-selected');
		var selectedName = document.getElementById('ast-csv-selected-name');
		var selectedMeta = document.getElementById('ast-csv-selected-meta');
		var changeBtn = document.getElementById('ast-csv-change');
		var removeBtn = document.getElementById('ast-csv-remove');

		function fmtBytes(n) {
			if (n < 1024) { return n + ' B'; }
			if (n < 1024 * 1024) { return (n / 1024).toFixed(1) + ' KB'; }
			return (n / (1024 * 1024)).toFixed(1) + ' MB';
		}

		function setFilename() {
			var file = (fileInput && fileInput.files && fileInput.files[0]) ? fileInput.files[0] : null;
			if (fileLabel) {
				fileLabel.textContent = file ? file.name : 'No file chosen';
			}
			if (drop) {
				drop.classList.toggle('is-filled', !!file);
			}
			if (dropEmpty) {
				if (file) { dropEmpty.setAttribute('hidden', ''); } else { dropEmpty.removeAttribute('hidden'); }
			}
			if (dropSelected) {
				if (file) { dropSelected.removeAttribute('hidden'); } else { dropSelected.setAttribute('hidden', ''); }
			}
			if (file && selectedName) { selectedName.textContent = file.name; }
			if (file && selectedMeta) { selectedMeta.textContent = fmtBytes(file.size) + ' \u00B7 Ready to upload'; }
		}

		if (chooseBtn && fileInput) {
			chooseBtn.addEventListener('click', function () { fileInput.click(); });
		}
		if (fileInput) {
			fileInput.addEventListener('change', setFilename);
		}
		if (changeBtn && fileInput) {
			changeBtn.addEventListener('click', function (e) { e.stopPropagation(); fileInput.click(); });
		}
		if (removeBtn && fileInput) {
			removeBtn.addEventListener('click', function (e) {
				e.stopPropagation();
				fileInput.value = '';
				setFilename();
			});
		}

		/* ---------- Drag & drop into the file input ---------- */
		if (drop && fileInput) {
			['dragenter', 'dragover'].forEach(function (ev) {
				drop.addEventListener(ev, function (e) { e.preventDefault(); drop.classList.add('is-drag'); });
			});
			['dragleave', 'dragend'].forEach(function (ev) {
				drop.addEventListener(ev, function () { drop.classList.remove('is-drag'); });
			});
			drop.addEventListener('drop', function (e) {
				e.preventDefault();
				drop.classList.remove('is-drag');
				var file = (e.dataTransfer && e.dataTransfer.files) ? e.dataTransfer.files[0] : null;
				if (!file) { return; }
				if (!/\.csv$/i.test(file.name)) { alert('Please drop a .csv file.'); return; }
				try {
					var dt = new DataTransfer();
					dt.items.add(file);
					fileInput.files = dt.files;
				} catch (err) { /* DataTransfer unsupported - user can use Choose file */ }
				setFilename();
			});
			drop.addEventListener('click', function (e) {
				if (drop.classList.contains('is-filled')) { return; }
				fileInput.click();
			});
		}

		/* ---------- Done-state success/fail stats ----------
		   shipping_row.js adds .csv_import_done to .bulk_upload_status_div when finished;
		   count the result <li>s by class to fill the stat widgets. */
		var statusDiv = root.querySelector('.bulk_upload_status_div');
		if (statusDiv && window.MutationObserver) {
			var obs = new MutationObserver(function () {
				if (!statusDiv.classList.contains('csv_import_done')) { return; }
				var ok = 0, fail = 0;
				Array.prototype.forEach.call(root.querySelectorAll('.csv_upload_status li'), function (li) {
					if (li.className === 'success') { ok++; } else { fail++; }
				});
				var okEl = root.querySelector('.ast-csv-stat-success');
				var failEl = root.querySelector('.ast-csv-stat-failed');
				if (okEl) { okEl.textContent = String(ok); }
				if (failEl) { failEl.textContent = String(fail); }
			});
			obs.observe(statusDiv, { attributes: true, attributeFilter: ['class'] });
		}

		// Reset stat numbers when the user restarts (shipping_row.js handles the rest).
		var again = root.querySelector('.csv_upload_again');
		if (again) {
			again.addEventListener('click', function () {
				var okEl = root.querySelector('.ast-csv-stat-success');
				var failEl = root.querySelector('.ast-csv-stat-failed');
				if (okEl) { okEl.textContent = '0'; }
				if (failEl) { failEl.textContent = '0'; }
				setFilename();
			});
		}
	});
})();

/**
 * AST PRO - License tab usage-tracking save.
 *
 * Plugin grid filter/search + secure-connection clock are handled generically
 * by ZUI._wireLicense() in the shared library. License activate/deactivate
 * still lives in admin_pro.js (#wc_ast_pro_addons_form).
 */
(function () {
	'use strict';

	function ready(fn) {
		if (document.readyState !== 'loading') { fn(); }
		else { document.addEventListener('DOMContentLoaded', fn); }
		document.addEventListener('ast:tabswapped', fn);
	}

	ready(function () {
		var root = document.getElementById('zui-lic');
		if (!root) { return; }

		var ajaxurl = window.ajaxurl || (window.location.origin + '/wp-admin/admin-ajax.php');

		var form = document.getElementById('ast-usage-tracking-form');
		if (form && !form._astBound) {
			form._astBound = true;
			form.addEventListener('submit', function (e) {
				e.preventDefault();
				var btn = document.querySelector('.zui-lic-savebtn');
				if (btn && window.ZUI && ZUI.btnSaving) { ZUI.btnSaving(btn); }
				fetch(ajaxurl, { method: 'POST', credentials: 'same-origin', body: new FormData(form) })
					.then(function (r) { return r.text(); })
					.then(function () {
						if (btn && window.ZUI && ZUI.btnSaved) { ZUI.btnSaved(btn); }
						if (window.ZUI && ZUI.snackbar) {
							ZUI.snackbar('Data saved successfully', { type: 'success' });
						}
					})
					.catch(function () {
						if (btn) { btn.classList.remove('is-saving'); }
						if (window.ZUI && ZUI.snackbar) {
							ZUI.snackbar('Save failed', { type: 'error' });
						}
					});
			});
		}
	});
})();


/**
 * AST PRO - New Settings UI: single-page tab switching (CSS show/hide, zero fetch).
 *
 * Every tab is rendered into its own .zui-tab-panel on load (layout-app.php), so
 * clicking a top tab just shows that panel and hides the rest - instant, no reload, no
 * loading. All tab JS modules already initialised on load (every panel is in the DOM).
 * External links (e.g. Unfulfilled Orders) + modified clicks fall through to normal nav.
 */
(function () {
	'use strict';

	function init() {
		var app = document.getElementById('ast-settings-app');
		if (!app) { return; }

		var PAGE = 'page=woocommerce-advanced-shipment-tracking';

		function tabOf(url) { var m = String(url).match(/[?&]tab=([^&#]+)/); return m ? decodeURIComponent(m[1]) : 'settings'; }

		function setActive(tab) {
			Array.prototype.forEach.call(app.querySelectorAll('.zui-tabs__item'), function (a) {
				var aTab = (a.getAttribute('href') || '').match(/[?&]tab=([^&#]+)/);
				var on = !!aTab && decodeURIComponent(aTab[1]) === tab;
				a.classList.toggle('is-active', on);
				if (on) { a.setAttribute('aria-current', 'page'); } else { a.removeAttribute('aria-current'); }
			});
		}

		function show(tab) {
			var found = false;
			Array.prototype.forEach.call(app.querySelectorAll('.zui-tab-panel'), function (p) {
				var on = p.getAttribute('data-tab') === tab;
				if (on) { p.removeAttribute('hidden'); found = true; } else { p.setAttribute('hidden', ''); }
			});
			// Library helper — hides the header menu toggle on tabs without a sidebar
			// (driven by `data-zui-sidebar-tabs` on the toggle element).
			if (window.ZUI && typeof window.ZUI.syncSidebarToggles === 'function') {
				window.ZUI.syncSidebarToggles(tab);
			}
			return found;
		}

		app.addEventListener('click', function (e) {
			var a = e.target.closest('.zui-tabs__item');
			if (!a || !app.contains(a)) { return; }
			var href = a.getAttribute('href') || '';
			if (href.indexOf(PAGE) === -1) { return; }            // external link -> normal nav
			if (e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) { return; }
			var tab = tabOf(a.href);
			e.preventDefault();
			if (show(tab)) {
				setActive(tab);
				window.history.pushState({ ast: 1 }, '', a.href);
				window.scrollTo(0, 0);
			} else {
				window.location.href = a.href; // no panel for this tab -> fall back to nav
			}
		});

		window.addEventListener('popstate', function () {
			if (window.location.href.indexOf(PAGE) === -1) { return; }
			var tab = tabOf(window.location.href);
			if (show(tab)) { setActive(tab); }
		});
	}

	if (document.readyState !== 'loading') { init(); }
	else { document.addEventListener('DOMContentLoaded', init); }
})();

/**
 * AST PRO - Fulfillment Dashboard: sub-tab switching (Unfulfilled / Recently Fulfilled).
 *
 * The two DataTables sections stay exactly as fulfillment.js renders them; this just
 * shows the active one and tells DataTables to recalc column widths (a table initialised
 * while hidden mis-measures its columns).
 */
(function () {
	'use strict';

	function ready(fn) {
		if (document.readyState !== 'loading') { fn(); }
		else { document.addEventListener('DOMContentLoaded', fn); }
	}

	ready(function () {
		var root = document.getElementById('ast-set-fd');
		if (!root) { return; }

		var tabs = root.querySelectorAll('.ast-set-fd-tab');
		var crumb = document.getElementById('ast-fd-crumb');

		function adjustTables() {
			if (!(window.jQuery && jQuery.fn && jQuery.fn.dataTable)) { return; }
			['#fullfilments_table', '#fulfilled_order_table'].forEach(function (sel) {
				try {
					if (jQuery.fn.dataTable.isDataTable(sel)) { jQuery(sel).DataTable().columns.adjust(); }
				} catch (e) { /* not ready yet */ }
			});
		}

		function showSection(id) {
			Array.prototype.forEach.call(root.querySelectorAll('.ast-set-fd-body .tab_section'), function (s) {
				s.classList.toggle('is-fd-shown', s.id === id);
			});
			setTimeout(adjustTables, 30);
		}

		/* ---- Tab count badges: read the real total record count from DataTables ---- */
		function setBadge(id, n) {
			var el = document.getElementById(id);
			if (!el) { return; }
			el.textContent = n;
			if (n > 0) { el.removeAttribute('hidden'); } else { el.setAttribute('hidden', ''); }
		}

		function updateCounts() {
			if (!(window.jQuery && jQuery.fn && jQuery.fn.dataTable)) { return; }
			var map = {
				'#fullfilments_table': 'ast-fd-count-unfulfilled'
			};
			Object.keys(map).forEach(function (sel) {
				try {
					if (jQuery.fn.dataTable.isDataTable(sel)) {
						var info = jQuery(sel).DataTable().page.info();
						setBadge(map[sel], info.recordsTotal);
					}
				} catch (e) { /* not ready yet */ }
			});
		}

		if (window.jQuery) {
			jQuery(document).on('draw.dt', '#fullfilments_table', updateCounts);
		}

		showSection('unfulfilled_orders_content');

		Array.prototype.forEach.call(tabs, function (btn) {
			btn.addEventListener('click', function () {
				Array.prototype.forEach.call(tabs, function (b) { b.classList.toggle('is-active', b === btn); });
				showSection(btn.getAttribute('data-fdtab'));
				if (crumb) { crumb.textContent = btn.getAttribute('data-crumb') || ''; }
			});
		});
	});
})();

/**
 * CSV Import tab: Manual / SFTP-FTP Automation sub-tab toggle.
 *
 * Only the visual sub-tab switch is wired here. The Automated (FTP/SFTP) panel is a
 * PRO-only upsell preview in the free build, so it has no save/test wiring.
 */
(function () {
	'use strict';

	function ready(fn) {
		if (document.readyState !== 'loading') { fn(); }
		else { document.addEventListener('DOMContentLoaded', fn); }
	}

	function initCsv() {
		var root = document.getElementById('ast-set-csv');
		if (!root || root.dataset.csvInit) { return; }
		root.dataset.csvInit = '1';

		var tabs = root.querySelectorAll('.zui-segmented__item');
		var subs = root.querySelectorAll('.ast-set-csv-sub');

		Array.prototype.forEach.call(tabs, function (btn) {
			btn.addEventListener('click', function () {
				var key = btn.getAttribute('data-csvtab');
				Array.prototype.forEach.call(tabs, function (b) {
					var on = b === btn;
					b.classList.toggle('is-active', on);
					b.setAttribute('aria-selected', on ? 'true' : 'false');
				});
				Array.prototype.forEach.call(subs, function (s) {
					if (s.getAttribute('data-csvsub') === key) { s.removeAttribute('hidden'); }
					else { s.setAttribute('hidden', ''); }
				});
			});
		});
	}

	ready(initCsv);
	document.addEventListener('ast:tabswapped', initCsv);
})();

/**
 * Shipping Carriers — DB update notice interactions.
 *
 * Wires the "Update Carriers" and dismiss buttons of the notice rendered by
 * WC_Advanced_Shipment_Tracking_Admin_Notice::ast_db_update_notice(). Mirrors
 * the AST PRO UX: inline success/error state, silent dismiss via the URL that
 * the notice carries in data-dismiss-url. The AJAX endpoint is the same
 * sync_providers handler used by the sync modal.
 */
(function ($) {
	'use strict';

	function initUpdateNotice() {
		var $notice = $('#ast-db-update-notice');
		if ( ! $notice.length || $notice.data('ast-bound') ) { return; }
		$notice.data('ast-bound', true);

		var dismissUrl = $notice.attr('data-dismiss-url');
		var $updateBtn  = $('#ast-notice-update-btn');
		var $dismissBtn = $('#ast-notice-dismiss-btn');
		var origLabel   = $updateBtn.text().trim();

		function dismissNotice() {
			$notice.fadeOut(150);
			if ( dismissUrl ) {
				$.get( dismissUrl );
			}
		}

		$dismissBtn.on('click', dismissNotice);

		$updateBtn.on('click', function () {
			var nonce = $('#nonce_shipping_provider').val();
			$updateBtn.prop('disabled', true).text('Updating…');

			$.ajax({
				url: (window.ajaxurl || ''),
				type: 'POST',
				dataType: 'json',
				data: { action: 'sync_providers', security: nonce, reset_checked: 0 }
			})
			.done(function (res) {
				var d = ( res && typeof res.added !== 'undefined' ) ? res : ( res && res.data ? res.data : {} );
				var count = ( parseInt(d.added, 10) || 0 ) + ( parseInt(d.updated, 10) || 0 );

				$notice.removeClass('zui-notice--info zui-notice--error').addClass('zui-notice--success');
				$notice.find('.zui-notice__icon').html('<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" width="20" height="20"><polyline points="20 6 9 17 4 12"/></svg>');
				$notice.find('.zui-notice__title').text('Carriers updated successfully!');
				$notice.find('.zui-notice__text').text( count ? count + ' carrier' + (count === 1 ? '' : 's') + ' added or updated.' : 'Your carriers list is already up to date.' );
				$notice.find('.ast-carriers-update-notice__actions').hide();

				setTimeout(dismissNotice, 4000);
			})
			.fail(function () {
				$notice.removeClass('zui-notice--info zui-notice--success').addClass('zui-notice--error');
				$notice.find('.zui-notice__icon').html('<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="20" height="20"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>');
				$notice.find('.zui-notice__title').text('Update failed. Please try again.');
				$notice.find('.zui-notice__text').text('');
				$updateBtn.prop('disabled', false).text(origLabel);
			});
		});
	}

	$(document).ready(initUpdateNotice);
	$(document).on('ast:tabswapped', initUpdateNotice);
})(jQuery);
