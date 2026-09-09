<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_Advanced_Shipment_Tracking_Admin_Notice {

	/**
	 * Instance of this class.
	 *
	 * @var object Class Instance
	 */
	private static $instance;

	public function __construct() {
		$this->init();
	}

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function init() {
		add_action( 'admin_init', array( $this, 'handle_dismissals' ) );

		// Priority 20 so admin_styles() (priority 4) has already run and
		// wp_style_is( 'zui' ) reports the settings-screen bundle correctly.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_notice_assets' ), 20 );

		add_action( 'admin_notices', array( $this, 'ast_review_admin_notice_4_0_2' ) );

		if ( ! $this->is_ast_settings_screen() ) {
			add_action( 'admin_notices', array( $this, 'ast_pro_notice_4_0_3' ) );
		}

		// Shortcode used by the settings screen to inject a status message inline.
		add_shortcode( 'ast_settings_admin_notice', array( $this, 'ast_settings_admin_notice' ) );

		// Shipping Carriers tab — DB update notice (above search).
		add_action( 'before_shipping_provider_list', array( $this, 'ast_db_update_notice' ) );
	}

	/* -----------------------------------------------------------------
	 * Shared helpers
	 * ----------------------------------------------------------------- */

	/**
	 * Verify the incoming dismiss request and persist the ignore flag.
	 * Registered dismiss endpoints live in one place so the caller stays trivial.
	 */
	public function handle_dismissals() {
		$map = array(
			'ast-review-update-notice-4-0-2'    => array( 'ast_review_dismiss_notice_4_0_2', 'ast_review_update_ignore_4_0_2' ),
			'ast-pro-notice-4-0-3'              => array( 'ast_pro_dismiss_notice_4_0_3',    'ast_notice_ignore_4_0_3' ),
			'ast-3-9-2-db-update-notice-ignore' => array( 'ast_db_update_dismiss_notice',    'ast_3_9_2_db_update_notice_ignore' ),
		);
		foreach ( $map as $query_arg => list( $action, $option ) ) {
			if ( isset( $_GET[ $query_arg ], $_GET['nonce'] )
				&& wp_verify_nonce( sanitize_key( wp_unslash( $_GET['nonce'] ) ), $action ) ) {
				update_option( $option, 'true' );
			}
		}
	}

	/**
	 * Build a nonced dismiss URL for the current request. Returned raw so callers
	 * can pick the right escaping for their context — esc_url() for an href,
	 * esc_attr() for a data-* attribute. Double-escaping breaks the URL because
	 * esc_url() converts `&` to `&amp;`, and esc_attr() over that yields
	 * `&amp;amp;`, which the browser only single-decodes when JS reads it. That
	 * left $.get() firing a malformed URL where only the first query arg reached
	 * the server, so the dismiss nonce check silently failed.
	 */
	private function dismiss_url( $query_arg, $nonce_action ) {
		return add_query_arg( array(
			$query_arg => 'true',
			'nonce'    => wp_create_nonce( $nonce_action ),
		) );
	}

	/**
	 * The AST settings screen renders its own in-page upsells, so the PRO card
	 * is suppressed there. Kept as one predicate because both the hook
	 * registration and the asset gate have to agree on it.
	 */
	private function is_ast_settings_screen() {
		$page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only screen check.
		return 'woocommerce-advanced-shipment-tracking' === $page;
	}

	private function review_notice_visible() {
		return ! get_option( 'ast_review_update_ignore_4_0_2' );
	}

	private function pro_notice_visible() {
		return ! get_option( 'ast_notice_ignore_4_0_3' ) && ! $this->is_ast_settings_screen();
	}

	/**
	 * Load the .zui-pnotice component for notices that render on every admin
	 * screen. The settings screens already pull it in through the zui.css
	 * aggregator, so this only fires where that bundle is absent — the file is
	 * authored to work outside `.zui-scope` with hardcoded token fallbacks.
	 */
	public function enqueue_notice_assets() {
		// Both .zui-pnotice cards need this file, and they are dismissed
		// independently — gating on the review flag alone left the PRO card
		// unstyled for anyone who had already dismissed the review notice.
		if ( ! $this->review_notice_visible() && ! $this->pro_notice_visible() ) {
			return;
		}

		$zui_dir  = wc_advanced_shipment_tracking()->plugin_dir_url() . 'assets/zui/';
		$zui_path = wc_advanced_shipment_tracking()->get_plugin_path() . '/assets/zui/';
		$css_file = $zui_path . 'css/components/plugin-notice.css';
		// Versioned off the file itself, not the library VERSION: a consumer-side
		// edit to the component leaves that version untouched, so browsers keep
		// serving the stale copy and the card renders half-styled until a manual
		// reload. The mtime changes with every edit and stays cacheable after.
		$zui_ver  = file_exists( $css_file ) ? filemtime( $css_file ) : wc_advanced_shipment_tracking()->version;
		wp_enqueue_style( 'zui-pnotice', $zui_dir . 'css/components/plugin-notice.css', array(), $zui_ver );
	}

	/**
	 * AST's entry from the shared ZUI brand registry — icon key plus the emblem
	 * colour pair, so a notice emblem matches the settings-header emblem without
	 * hardcoding either. Falls back to the AST values if the lookup ever misses.
	 *
	 * @return array
	 */
	private function zui_brand() {
		require_once SHIPMENT_TRACKING_PATH . '/assets/zui/brand.php';
		require_once SHIPMENT_TRACKING_PATH . '/assets/zui/icons.php';

		$brand = zui_get_plugin_brand( 'woo-advanced-shipment-tracking/woocommerce-advanced-shipment-tracking.php' );
		if ( ! is_array( $brand ) ) {
			$brand = array(
				'icon'         => 'package',
				'emblem_bg'    => '#DBEAFE',
				'emblem_color' => '#2563EB',
			);
		}
		return $brand;
	}

	/**
	 * Open a .zui-pnotice card with the library's documented shell: brand
	 * emblem, then the body wrapper the caller fills. Both cards carry the same
	 * emblem, so building it in one place keeps them in sync with the
	 * settings-header emblem via brand.php.
	 *
	 * @param string $id          DOM id, also used by the behaviour script.
	 * @param string $dismiss_url Raw nonced dismiss URL.
	 */
	private function open_pnotice( $id, $dismiss_url ) {
		$brand        = $this->zui_brand();
		$emblem_style = sprintf(
			'--zui-pnotice-avatar-bg:%s;--zui-pnotice-avatar-color:%s;',
			$brand['emblem_bg'],
			$brand['emblem_color']
		);
		?>
		<div class="zui-pnotice" id="<?php echo esc_attr( $id ); ?>" role="status" data-dismiss-url="<?php echo esc_attr( $dismiss_url ); ?>">

			<span class="zui-pnotice__avatar" aria-hidden="true" style="<?php echo esc_attr( $emblem_style ); ?>">
				<?php zui_icon( $brand['icon'] ); ?>
			</span>

			<div class="zui-pnotice__body">
		<?php
	}

	/**
	 * Close the card started by open_pnotice() and emit its behaviour script.
	 *
	 * @param string $id DOM id passed to open_pnotice().
	 */
	private function close_pnotice( $id ) {
		?>
			</div>

			<button type="button" class="zui-pnotice__close" aria-label="<?php esc_attr_e( 'Dismiss', 'woo-advanced-shipment-tracking' ); ?>">&times;</button>
		</div>
		<?php
		$this->pnotice_behavior_script( $id );
	}

	/**
	 * Gutter fix + dismissal for one .zui-pnotice card, matching the
	 * component's JS contract (nothing is auto-wired; the consumer decides).
	 * Printed per card rather than once at admin_footer so the margin is set
	 * before the card is painted — at footer time the browser has already laid
	 * the notice out and the correction shows up as a visible sideways jump.
	 *
	 * @param string $id DOM id of the card.
	 */
	private function pnotice_behavior_script( $id ) {
		?>
		<script>
		( function () {
			var notice = document.getElementById( <?php echo wp_json_encode( $id ); ?> );
			if ( ! notice ) {
				return;
			}

			// Left gutter. wp-admin normally supplies it through #wpcontent's
			// 20px padding-left, but any plugin screen running a full-bleed app
			// zeroes that padding — and CSS cannot ask whether it is still
			// there. Naming those screens breaks the moment another plugin uses
			// a different wrapper, so measure what the page actually has and top
			// it up to 20px. Works on every screen without knowing any of them.
			var content = document.getElementById( 'wpcontent' );
			var pad     = content ? parseFloat( getComputedStyle( content ).paddingLeft ) || 0 : 0;
			if ( pad < 20 ) {
				notice.style.marginLeft = ( 20 - pad ) + 'px';
			}

			notice.addEventListener( 'click', function ( e ) {
				// The CTA opens in a new tab and must leave this page alone.
				// Nothing here touches it, but admin screens are full of
				// document-level click handlers from other plugins, so the event
				// is stopped at the card rather than left to bubble into one.
				if ( e.target.closest( '.zui-pnotice__btn' ) ) {
					e.stopPropagation();
					return;
				}

				// Only the explicit dismiss controls store the flag. Opening the
				// review or pricing page is not proof the user acted on it —
				// plenty of people get sidetracked on the way — so the card
				// stays until the user says so themselves.
				if ( ! e.target.closest( '.zui-pnotice__close, .zui-pnotice__link' ) ) {
					return;
				}
				notice.setAttribute( 'hidden', '' );
				fetch( notice.dataset.dismissUrl, { credentials: 'same-origin' } );
			} );
		}() );
		</script>
		<?php
	}

	/* -----------------------------------------------------------------
	 * Notice: settings admin message shortcode
	 * ----------------------------------------------------------------- */

	public function ast_settings_admin_notice() {
		ob_start();
		include 'views/admin_message_panel.php';
		return ob_get_clean();
	}

	/* -----------------------------------------------------------------
	 * Notice: review request (⭐)
	 * ----------------------------------------------------------------- */

	/**
	 * Built on the shared .zui-pnotice library component
	 * (assets/zui/css/components/plugin-notice.css) — the branded plugin
	 * call-out card: round brand emblem, title, copy, action row, close.
	 *
	 * Markup follows the library's documented structure exactly. Core's
	 * `.notice` classes are deliberately absent — the guide calls that out,
	 * because wp-admin's notice CSS fights the card's border, padding and
	 * margins. Emblem icon + colours come from brand.php so the circle stays
	 * in sync with the settings-header emblem.
	 *
	 * Dismissal reuses the same nonced endpoint as the other notices
	 * (handle_dismissals()); the inline script just hides the card and pings
	 * that URL, matching the component's JS contract.
	 */
	public function ast_review_admin_notice_4_0_2() {
		if ( ! $this->review_notice_visible() ) {
			return;
		}

		$dismiss_url = $this->dismiss_url( 'ast-review-update-notice-4-0-2', 'ast_review_dismiss_notice_4_0_2' );
		$this->open_pnotice( 'ast-review-notice', $dismiss_url );
		?>
				<strong class="zui-pnotice__title"><?php esc_html_e( '⭐ Enjoying AST? Leave Us a Review!', 'woo-advanced-shipment-tracking' ); ?></strong>
				<p class="zui-pnotice__text"><?php echo wp_kses_post( __( 'We hope <strong>Advanced Shipment Tracking</strong> has improved your order fulfillment workflow! Your feedback helps us grow and continue improving the plugin.', 'woo-advanced-shipment-tracking' ) ); ?></p>
				<p class="zui-pnotice__text"><?php esc_html_e( 'If you love using AST, we\'d really appreciate it if you could take a moment to leave us a 5-star review. It helps us keep improving and providing the best experience for you!', 'woo-advanced-shipment-tracking' ); ?></p>
				<p class="zui-pnotice__text"><?php esc_html_e( '👍 Support AST & Share Your Experience!', 'woo-advanced-shipment-tracking' ); ?></p>

				<div class="zui-pnotice__actions">
					<a class="zui-pnotice__btn" href="https://wordpress.org/support/plugin/woo-advanced-shipment-tracking/reviews/#new-post" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Leave a Review', 'woo-advanced-shipment-tracking' ); ?></a>
					<button type="button" class="zui-pnotice__link"><?php esc_html_e( 'Dismiss', 'woo-advanced-shipment-tracking' ); ?></button>
				</div>
		<?php
		$this->close_pnotice( 'ast-review-notice' );
	}

	/* -----------------------------------------------------------------
	 * Notice: AST PRO upsell (🚀)
	 * ----------------------------------------------------------------- */

	/**
	 * Rebuilt in 4.0.3 on the shared .zui-pnotice component, so the upsell card
	 * matches the review notice and the redesigned settings screens instead of
	 * the old core `.notice` banner with its own inline button CSS.
	 *
	 * The copy moved from a five-item checklist to two short paragraphs: the
	 * component styles a title and `__text` runs, not lists, and a bare `<ul>`
	 * in the body inherits whatever wp-admin happens to apply. Every feature
	 * from the old list is still named — auto-import, PayPal/Stripe sync,
	 * one-click updates, item-level tracking, CSV import, the dashboard.
	 *
	 * The dismiss option is version-tagged (4_0_3), following the same pattern
	 * as the other notices, so the refreshed card surfaces once for users who
	 * had dismissed the 4.0 version.
	 */
	public function ast_pro_notice_4_0_3() {
		if ( ! $this->pro_notice_visible() ) {
			return;
		}

		$dismiss_url = $this->dismiss_url( 'ast-pro-notice-4-0-3', 'ast_pro_dismiss_notice_4_0_3' );
		$this->open_pnotice( 'ast-pro-notice', $dismiss_url );
		?>
				<strong class="zui-pnotice__title"><?php esc_html_e( '🚀 Upgrade to AST PRO — Automate Your Shipping Workflow', 'woo-advanced-shipment-tracking' ); ?></strong>
				<p class="zui-pnotice__text"><?php echo wp_kses_post( __( 'Still adding tracking numbers by hand? <strong>AST PRO</strong> imports them automatically from ShipStation, WooCommerce Shipping, Sendcloud, Pirate Ship, Ordoro, Royal Mail Click &amp; Drop, Stamps.com and Printful — the moment your shipping label is created.', 'woo-advanced-shipment-tracking' ) ); ?></p>
				<p class="zui-pnotice__text"><?php echo wp_kses_post( __( 'It also syncs tracking to <strong>PayPal and Stripe</strong> to release payment holds and reduce &ldquo;Item Not Received&rdquo; disputes, auto-detects the carrier from the tracking number, and adds item-level tracking, custom and white-labeled carriers, scheduled FTP/SFTP imports, and one fulfillment dashboard for every shipment.', 'woo-advanced-shipment-tracking' ) ); ?></p>
				<p class="zui-pnotice__text"><?php echo wp_kses_post( __( '🎁 <strong>20% OFF</strong> with code <strong>ASTPRO20</strong> — new customers only.', 'woo-advanced-shipment-tracking' ) ); ?></p>

				<div class="zui-pnotice__actions">
					<a class="zui-pnotice__btn" href="https://www.zorem.com/product/woocommerce-advanced-shipment-tracking/" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Upgrade to AST PRO', 'woo-advanced-shipment-tracking' ); ?></a>
					<button type="button" class="zui-pnotice__link"><?php esc_html_e( 'Maybe later', 'woo-advanced-shipment-tracking' ); ?></button>
				</div>
		<?php
		$this->close_pnotice( 'ast-pro-notice' );
	}

	/* -----------------------------------------------------------------
	 * Notice: Shipping-carriers DB update (above search on carriers tab)
	 * -----------------------------------------------------------------
	 * Built on the shared .zui-notice library component
	 * (assets/zui/css/components/notice.css). Variants (--info / --success /
	 * --error) drive accent + tint, so we don't ship a separate colour system.
	 * The dismiss option name is version-tagged (matches PRO's ast_3_2_...
	 * pattern), so bumping the plugin version resurfaces the notice for
	 * existing users after a carrier-DB refresh ships.
	 */
	public function ast_db_update_notice() {
		if ( get_option( 'ast_3_9_2_db_update_notice_ignore' ) ) {
			return;
		}
		$dismiss_url = $this->dismiss_url( 'ast-3-9-2-db-update-notice-ignore', 'ast_db_update_dismiss_notice' );
		?>
		<div class="zui-notice zui-notice--info ast-carriers-update-notice" id="ast-db-update-notice" role="status" data-dismiss-url="<?php echo esc_attr( $dismiss_url ); ?>">
			<span class="zui-notice__icon">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="20" height="20"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-.08-3.8"/></svg>
			</span>
			<div class="zui-notice__body">
				<strong class="zui-notice__title"><?php esc_html_e( 'New shipping carriers available', 'woo-advanced-shipment-tracking' ); ?></strong>
				<p class="zui-notice__text"><?php esc_html_e( 'Update your carriers list to add the latest providers.', 'woo-advanced-shipment-tracking' ); ?></p>
			</div>
			<div class="ast-carriers-update-notice__actions">
				<button type="button" class="zui-btn-primary" id="ast-notice-update-btn">
					<?php esc_html_e( 'Update Carriers', 'woo-advanced-shipment-tracking' ); ?>
				</button>
			</div>
			<button type="button" class="zui-notice__close" id="ast-notice-dismiss-btn" aria-label="<?php esc_attr_e( 'Dismiss', 'woo-advanced-shipment-tracking' ); ?>">&times;</button>
		</div>
		<?php
	}
}
