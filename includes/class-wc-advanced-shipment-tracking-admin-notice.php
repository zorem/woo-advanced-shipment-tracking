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

	/**
	 * Orders shipped with tracking before the milestone upsell appears.
	 */
	const MILESTONE_ORDERS = 10;

	/**
	 * How long "Not now" / × hides the milestone upsell. AST is a daily tool,
	 * so the card must not come back every week.
	 */
	const MILESTONE_SNOOZE = 60 * DAY_IN_SECONDS;

	/**
	 * Order screens the milestone upsell is limited to: legacy CPT list + edit,
	 * and the HPOS orders page (list and edit share one screen id).
	 */
	const ORDER_SCREENS = array( 'edit-shop_order', 'shop_order', 'woocommerce_page_wc-orders' );

	/**
	 * AI Agent OS plugin basename — the launch card hides once it is active.
	 */
	const AGENT_OS_PLUGIN = 'ai-agent-os/ai-agent-os.php';

	/**
	 * Last moment of the AGENTOS40 launch offer, in the site's timezone.
	 */
	const AGENT_OS_OFFER_ENDS = '2026-11-15 23:59:59';

	/**
	 * Last moment of the EDDPRO40 launch offer, in the site's timezone. The card
	 * retires itself on its own once this passes - nothing else hides it.
	 */
	const EDD_PRO_OFFER_ENDS = '2026-11-15 23:59:59';

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
			add_action( 'admin_notices', array( $this, 'ast_pro_notice_4_0_4' ) );
		}

		// Milestone upsell — counts every order's first tracking number,
		// whichever path added it (meta box, CSV, REST API, helper functions).
		add_action( 'update_order_status_after_adding_tracking', array( $this, 'count_shipped_order' ), 20, 2 );
		add_action( 'admin_notices', array( $this, 'ast_milestone_notice' ) );

		// AI Agent OS launch announcement (one-off, permanently dismissible).
		add_action( 'admin_notices', array( $this, 'ast_agent_os_notice' ) );

		// Estimated Delivery Date PRO launch announcement (one-off, permanently dismissible).
		add_action( 'admin_notices', array( $this, 'ast_edd_pro_notice' ) );

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
			'ast-pro-notice-4-0-4'              => array( 'ast_pro_dismiss_notice_4_0_4',    'ast_notice_ignore_4_0_4' ),
			'ast-3-9-2-db-update-notice-ignore' => array( 'ast_db_update_dismiss_notice',    'ast_3_9_2_db_update_notice_ignore' ),
			'ast-agent-os-notice'               => array( 'ast_agent_os_dismiss_notice',     'ast_agent_os_notice_ignore' ),
			'ast-edd-pro-notice'                => array( 'ast_edd_pro_dismiss_notice',      'ast_edd_pro_notice_ignore' ),
		);
		foreach ( $map as $query_arg => list( $action, $option ) ) {
			if ( isset( $_GET[ $query_arg ], $_GET['nonce'] )
				&& wp_verify_nonce( sanitize_key( wp_unslash( $_GET['nonce'] ) ), $action ) ) {
				update_option( $option, 'true' );
			}
		}

		// The milestone card snoozes instead of setting a permanent flag.
		if ( isset( $_GET['ast-milestone-notice-snooze'], $_GET['nonce'] )
			&& wp_verify_nonce( sanitize_key( wp_unslash( $_GET['nonce'] ) ), 'ast_milestone_notice_snooze' ) ) {
			update_option( 'ast_milestone_notice_snoozed_until', time() + self::MILESTONE_SNOOZE, false );
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
		// Two upsell cards stacked on one screen read as nagging, so the
		// generic card steps aside wherever the milestone card is showing.
		// The Agent OS launch card takes the promo slot while it is up; the
		// PRO card comes back once that one is dismissed.
		return ! get_option( 'ast_notice_ignore_4_0_4' ) && ! $this->is_ast_settings_screen() && ! $this->milestone_notice_visible() && ! $this->agent_os_notice_visible() && ! $this->edd_pro_notice_visible();
	}

	/**
	 * AI Agent OS is a site-level purchase, so only administrators see it.
	 * Skipped on the AST settings screen (own upsells), wherever the
	 * milestone card is up (one promo card per screen), and once the plugin
	 * is already active.
	 */
	/**
	 * Estimated Delivery Date PRO launch card. Same rules as the other promo
	 * cards: administrators only, never on the AST settings screen (that screen
	 * runs its own upsells), and one promo card per screen - so it stands down
	 * wherever the milestone card is showing.
	 */
	private function edd_pro_notice_visible() {
		// The card is the coupon offer, so it retires itself when the offer ends.
		$offer_ends = new DateTime( self::EDD_PRO_OFFER_ENDS, wp_timezone() );
		if ( time() > $offer_ends->getTimestamp() ) {
			return false;
		}
		if ( get_option( 'ast_edd_pro_notice_ignore' ) || $this->is_ast_settings_screen() || ! current_user_can( 'manage_options' ) ) {
			return false;
		}
		return ! $this->milestone_notice_visible();
	}

	private function agent_os_notice_visible() {
		// The card is the coupon offer, so it retires itself when the offer ends.
		$offer_ends = new DateTime( self::AGENT_OS_OFFER_ENDS, wp_timezone() );
		if ( time() > $offer_ends->getTimestamp() ) {
			return false;
		}
		if ( get_option( 'ast_agent_os_notice_ignore' ) || $this->is_ast_settings_screen() || ! current_user_can( 'manage_options' ) ) {
			return false;
		}
		if ( function_exists( 'is_plugin_active' ) && is_plugin_active( self::AGENT_OS_PLUGIN ) ) {
			return false;
		}
		return ! $this->milestone_notice_visible();
	}

	private function is_orders_screen() {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		return $screen && in_array( $screen->id, self::ORDER_SCREENS, true );
	}

	private function milestone_notice_visible() {
		return $this->is_orders_screen()
			&& current_user_can( 'manage_woocommerce' )
			&& (int) get_option( 'ast_shipped_orders_count', 0 ) >= self::MILESTONE_ORDERS
			&& (int) get_option( 'ast_milestone_notice_snoozed_until', 0 ) < time();
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
		if ( ! $this->review_notice_visible() && ! $this->pro_notice_visible() && ! $this->milestone_notice_visible() && ! $this->agent_os_notice_visible() && ! $this->edd_pro_notice_visible() ) {
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
	 * @param string     $id          DOM id, also used by the behaviour script.
	 * @param string     $dismiss_url Raw nonced dismiss URL.
	 * @param array|null $brand       Emblem override (icon, emblem_bg, emblem_color) for
	 *                                cards about another product. Defaults to AST's.
	 */
	private function open_pnotice( $id, $dismiss_url, $brand = null ) {
		$this->zui_brand(); // Loads icons.php even when the emblem is overridden.
		$brand        = $brand ? $brand : $this->zui_brand();
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
	 * Rebuilt in 4.0.4 on the shared .zui-pnotice component, so the upsell card
	 * matches the review notice and the redesigned settings screens instead of
	 * the old core `.notice` banner with its own inline button CSS.
	 *
	 * The wording is the 4.0 copy, unchanged — only the shell around it moved.
	 * The checklist is five `__text` runs rather than a `<ul>`: the component
	 * styles a title and text runs, and a bare list in the body picks up
	 * whatever wp-admin happens to apply to `ul`/`li` on that screen, which
	 * varies and would fight the card's spacing. The ✅ already reads as the
	 * bullet, so nothing is lost.
	 *
	 * The dismiss option is version-tagged (4_0_4), following the same pattern
	 * as the other notices, so the redesigned card surfaces once for users who
	 * had dismissed the 4.0 version.
	 */
	public function ast_pro_notice_4_0_4() {
		if ( ! $this->pro_notice_visible() ) {
			return;
		}

		$dismiss_url = $this->dismiss_url( 'ast-pro-notice-4-0-4', 'ast_pro_dismiss_notice_4_0_4' );
		$this->open_pnotice( 'ast-pro-notice', $dismiss_url );
		?>
				<strong class="zui-pnotice__title"><?php esc_html_e( '🚀 Upgrade to AST PRO – Automate Your Shipping Workflow', 'woo-advanced-shipment-tracking' ); ?></strong>
				<p class="zui-pnotice__text"><?php esc_html_e( 'Stop copy-pasting tracking numbers:', 'woo-advanced-shipment-tracking' ); ?></p>
				<p class="zui-pnotice__text"><?php esc_html_e( '✅ Auto-import tracking from 70+ shipping providers', 'woo-advanced-shipment-tracking' ); ?></p>
				<p class="zui-pnotice__text"><?php esc_html_e( '✅ Sync tracking to PayPal & Stripe to release funds faster', 'woo-advanced-shipment-tracking' ); ?></p>
				<p class="zui-pnotice__text"><?php esc_html_e( '✅ Update orders & notify customers in one click', 'woo-advanced-shipment-tracking' ); ?></p>
				<p class="zui-pnotice__text"><?php esc_html_e( '✅ Item-level tracking, custom statuses & bulk CSV import', 'woo-advanced-shipment-tracking' ); ?></p>
				<p class="zui-pnotice__text"><?php esc_html_e( '✅ Manage everything from one fulfillment dashboard', 'woo-advanced-shipment-tracking' ); ?></p>
				<p class="zui-pnotice__text"><?php echo wp_kses_post( __( '🎁 <strong>20% OFF</strong> with code <strong>ASTPRO20</strong> — new customers only!', 'woo-advanced-shipment-tracking' ) ); ?></p>

				<div class="zui-pnotice__actions">
					<a class="zui-pnotice__btn" href="<?php echo esc_url( ast_pro_upgrade_url( 'admin-notice' ) ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( '👉 Upgrade to AST PRO', 'woo-advanced-shipment-tracking' ); ?></a>
					<button type="button" class="zui-pnotice__link"><?php esc_html_e( 'Dismiss', 'woo-advanced-shipment-tracking' ); ?></button>
				</div>
		<?php
		$this->close_pnotice( 'ast-pro-notice' );
	}

	/* -----------------------------------------------------------------
	 * Notice: shipped-orders milestone upsell (🎉)
	 * ----------------------------------------------------------------- */

	/**
	 * Count an order the first time it gets a tracking number. Runs after the
	 * item is saved, so a single stored item means this was the order's first —
	 * extra tracking numbers on the same order don't inflate the count.
	 *
	 * @param string   $status_shipped Unused.
	 * @param WC_Order $order          Order the tracking was added to.
	 */
	public function count_shipped_order( $status_shipped, $order ) {
		$items = $order->get_meta( '_wc_shipment_tracking_items', true );
		if ( ! is_array( $items ) || 1 !== count( $items ) ) {
			return;
		}
		update_option( 'ast_shipped_orders_count', (int) get_option( 'ast_shipped_orders_count', 0 ) + 1, false );
	}

	/**
	 * Second upgrade trigger, shown at a moment of value rather than during
	 * setup: once the store has shipped MILESTONE_ORDERS orders with AST, on
	 * the order screens where that work happens. The settings-screen prompt
	 * only reaches people while they configure, which most do once.
	 *
	 * Dismissal snoozes for MILESTONE_SNOOZE rather than hiding forever; when
	 * it returns, the count in the title has grown, so the card isn't a repeat.
	 *
	 * utm_content is unique to this placement so GA4 can compare it against
	 * the settings-screen prompt (utm_medium=plugin-setting).
	 */
	public function ast_milestone_notice() {
		if ( ! $this->milestone_notice_visible() ) {
			return;
		}

		$count       = (int) get_option( 'ast_shipped_orders_count', 0 );
		$upgrade_url = ast_pro_upgrade_url( 'orders-shipped-milestone' );

		$dismiss_url = $this->dismiss_url( 'ast-milestone-notice-snooze', 'ast_milestone_notice_snooze' );
		$this->open_pnotice( 'ast-milestone-notice', $dismiss_url );
		?>
				<strong class="zui-pnotice__title">
					<?php
					/* translators: %s: number of orders shipped with tracking */
					echo esc_html( sprintf( _n( '🎉 You\'ve shipped %s order with AST', '🎉 You\'ve shipped %s orders with AST', $count, 'woo-advanced-shipment-tracking' ), number_format_i18n( $count ) ) );
					?>
				</strong>
				<p class="zui-pnotice__text"><?php esc_html_e( 'Let AST PRO take the copy-pasting off your hands: auto-import tracking from 70+ shipping providers, sync it to PayPal & Stripe to release funds faster, and track individual items in an order.', 'woo-advanced-shipment-tracking' ); ?></p>

				<div class="zui-pnotice__actions">
					<a class="zui-pnotice__btn" href="<?php echo esc_url( $upgrade_url ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'See what AST PRO automates', 'woo-advanced-shipment-tracking' ); ?></a>
					<button type="button" class="zui-pnotice__link"><?php esc_html_e( 'Not now', 'woo-advanced-shipment-tracking' ); ?></button>
				</div>
		<?php
		$this->close_pnotice( 'ast-milestone-notice' );
	}

	/* -----------------------------------------------------------------
	 * Notice: AI Agent OS launch (🤖)
	 * ----------------------------------------------------------------- */

	/**
	 * Launch announcement for AI Agent OS, zorem's MCP governance plugin.
	 * Same .zui-pnotice shell as the other cards, re-coloured indigo with its
	 * own emblem so it reads as a different product rather than another AST
	 * upsell. The offer strip is local to this card — the component has no
	 * coupon slot — so its few rules are printed with the card, scoped by id.
	 *
	 * The link goes through ast_utm_url() with its own campaign, so launch
	 * traffic from AST shows up separately from the AST PRO upgrade links.
	 */
	public function ast_agent_os_notice() {
		if ( ! $this->agent_os_notice_visible() ) {
			return;
		}

		$product_url = ast_utm_url( 'https://www.zorem.com/product/ai-agent-os/', 'admin-notice', 'agent-os-launch' );
		$dismiss_url = $this->dismiss_url( 'ast-agent-os-notice', 'ast_agent_os_dismiss_notice' );
		?>
		<style>
			#ast-agent-os-notice { --zui-pnotice-accent: #4F5BD5; }
			#ast-agent-os-notice .zui-pnotice__btn { background: #4F5BD5; }
			#ast-agent-os-notice .zui-pnotice__btn:hover { background: #3F49B8; }
			#ast-agent-os-notice .ast-agent-os-offer { display: inline-flex; flex-wrap: wrap; align-items: center; gap: 8px; margin-block-start: 12px; padding: 10px 16px; background: #FFF5EB; border: 1px solid #F8C99B; border-radius: 8px; font-size: 14px; color: #475569; }
			#ast-agent-os-notice .ast-agent-os-offer strong { color: #C2410C; font-weight: 600; }
			#ast-agent-os-notice .ast-agent-os-offer code { padding: 3px 10px; background: #FFFFFF; border: 1px dashed #EA7A3F; border-radius: 6px; color: #C2410C; font-family: ui-monospace, SFMono-Regular, Consolas, monospace; font-size: 13px; font-weight: 700; letter-spacing: 0.06em; }
		</style>
		<?php
		$this->open_pnotice( 'ast-agent-os-notice', $dismiss_url, array(
			'icon'         => 'shield-check',
			'emblem_bg'    => '#4F5BD5',
			'emblem_color' => '#FFFFFF',
		) );
		?>
				<strong class="zui-pnotice__title"><?php esc_html_e( 'New from zorem: AI Agent OS — MCP Governance for WordPress AI Agents', 'woo-advanced-shipment-tracking' ); ?></strong>
				<p class="zui-pnotice__text"><?php esc_html_e( 'See every tool your plugins expose to AI agents and set clear permissions for each agent. Customer details stay hidden from agents that don\'t need them, and every action is logged.', 'woo-advanced-shipment-tracking' ); ?></p>

				<div class="ast-agent-os-offer">
					<strong><?php esc_html_e( 'Launch offer: 40% off', 'woo-advanced-shipment-tracking' ); ?></strong>
					<span><?php esc_html_e( '— use coupon code', 'woo-advanced-shipment-tracking' ); ?></span>
					<code>AGENTOS40</code>
					<span><?php esc_html_e( 'Valid until November 15, 2026.', 'woo-advanced-shipment-tracking' ); ?></span>
				</div>

				<div class="zui-pnotice__actions">
					<a class="zui-pnotice__btn" href="<?php echo esc_url( $product_url ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Get AI Agent OS', 'woo-advanced-shipment-tracking' ); ?></a>
					<button type="button" class="zui-pnotice__link"><?php esc_html_e( 'No thanks', 'woo-advanced-shipment-tracking' ); ?></button>
				</div>
		<?php
		$this->close_pnotice( 'ast-agent-os-notice' );
	}

	/* -----------------------------------------------------------------
	 * Notice: Estimated Delivery Date PRO launch (EDDPRO40)
	 * ----------------------------------------------------------------- */

	/**
	 * Launch announcement for the newest zorem plugin, built on the same
	 * .zui-pnotice card as the review and PRO notices. EDD PRO is a store-level
	 * purchase, so only administrators see it, and like the other promo cards it
	 * stays off the AST settings screen, which runs its own upsells.
	 *
	 * The offer strip is local to this card - the component has no coupon slot -
	 * so its few rules are printed with the card, scoped by id. The link carries
	 * its own campaign so EDD traffic from AST is separable in GA4.
	 */
	public function ast_edd_pro_notice() {
		if ( ! $this->edd_pro_notice_visible() ) {
			return;
		}

		$product_url = ast_utm_url( 'https://www.zorem.com/product/estimated-delivery-date-pro/', 'admin-notice', 'edd-pro-launch' );
		$dismiss_url = $this->dismiss_url( 'ast-edd-pro-notice', 'ast_edd_pro_dismiss_notice' );
		$offer_ends  = new DateTime( self::EDD_PRO_OFFER_ENDS, wp_timezone() );
		?>
		<style>
			#ast-edd-pro-notice .ast-edd-pro-offer { display: inline-flex; flex-wrap: wrap; align-items: center; gap: 8px; margin-block-start: 12px; padding: 10px 16px; background: #FFF5EB; border: 1px solid #F8C99B; border-radius: 8px; font-size: 14px; color: #475569; }
			#ast-edd-pro-notice .ast-edd-pro-offer strong { color: #C2410C; font-weight: 600; }
			#ast-edd-pro-notice .ast-edd-pro-offer code { padding: 3px 10px; background: #FFFFFF; border: 1px dashed #EA7A3F; border-radius: 6px; color: #C2410C; font-family: ui-monospace, SFMono-Regular, Consolas, monospace; font-size: 13px; font-weight: 700; letter-spacing: 0.06em; }
		</style>
		<?php
		$this->open_pnotice( 'ast-edd-pro-notice', $dismiss_url, array(
			'icon'         => 'truck',
			'emblem_bg'    => '#DBEAFE',
			'emblem_color' => '#2563EB',
		) );
		?>
				<strong class="zui-pnotice__title"><?php esc_html_e( 'New from zorem: Estimated Delivery Date PRO for WooCommerce', 'woo-advanced-shipment-tracking' ); ?></strong>
				<p class="zui-pnotice__text"><?php esc_html_e( 'Show accurate delivery dates on your product, cart and checkout pages, worked out from postal-code distance, your working days and a live order cut-off countdown. Fewer delivery questions from customers, and less cart abandonment.', 'woo-advanced-shipment-tracking' ); ?></p>

				<div class="ast-edd-pro-offer">
					<strong><?php esc_html_e( 'Launch offer: 40% off', 'woo-advanced-shipment-tracking' ); ?></strong>
					<span><?php esc_html_e( '— use coupon code', 'woo-advanced-shipment-tracking' ); ?></span>
					<code>EDDPRO40</code>
					<span>
						<?php
						/* translators: %s: date the launch offer ends. */
						printf( esc_html__( 'Valid until %s.', 'woo-advanced-shipment-tracking' ), esc_html( wp_date( 'F j, Y', $offer_ends->getTimestamp() ) ) );
						?>
					</span>
				</div>

				<div class="zui-pnotice__actions">
					<a class="zui-pnotice__btn" href="<?php echo esc_url( $product_url ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Get Estimated Delivery Date PRO', 'woo-advanced-shipment-tracking' ); ?></a>
					<button type="button" class="zui-pnotice__link"><?php esc_html_e( 'No thanks', 'woo-advanced-shipment-tracking' ); ?></button>
				</div>
		<?php
		$this->close_pnotice( 'ast-edd-pro-notice' );
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
