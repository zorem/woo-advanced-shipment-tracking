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
	 * Whether shared notice styles have already been emitted on this request.
	 *
	 * @var bool
	 */
	private $styles_emitted = false;

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

		add_action( 'admin_notices', array( $this, 'ast_review_admin_notice_388' ) );

		$page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';
		if ( 'woocommerce-advanced-shipment-tracking' !== $page ) {
			add_action( 'admin_notices', array( $this, 'ast_pro_notice_4_0' ) );
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
			'ast-review-update-notice-388'      => array( 'ast_review_dismiss_notice_388', 'ast_review_update_ignore_388' ),
			'ast-pro-notice-4-0'                => array( 'ast_pro_dismiss_notice_4_0',    'ast_notice_ignore_4_0' ),
			'ast-3-9-2-db-update-notice-ignore' => array( 'ast_db_update_dismiss_notice',  'ast_3_9_2_db_update_notice_ignore' ),
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
	 * Emit the shared notice + button CSS exactly once per request. The banner
	 * itself is the WP core `.notice` element; button styling bypasses
	 * `.button-primary` so WooCommerce admin CSS can't inject height/line-height
	 * on our CTAs. Accent color is driven by the `--ast-notice-accent` custom
	 * property so every notice reuses the same rules with only a color swap.
	 */
	private function emit_shared_notice_styles() {
		if ( $this->styles_emitted ) {
			return;
		}
		$this->styles_emitted = true;
		?>
		<style>
		.wp-core-ui .notice.ast-dismissable-notice {
			position: relative;
			padding-right: 38px;
			border-left-color: var(--ast-notice-accent, #3b64d3);
		}
		.wp-core-ui .notice.ast-dismissable-notice h2,
		.wp-core-ui .notice.ast-dismissable-notice h3 { margin-bottom: 5px; }
		.wp-core-ui .notice.ast-dismissable-notice a.notice-dismiss {
			padding: 9px;
			text-decoration: none;
		}
		.ast-dismissable-notice strong { font-weight: bold; }
		.ast-dismissable-notice .ts-updated-notice { margin: 1em 0 !important; }
		.ast-dismissable-notice .ast-notice-btn {
			display: inline-block !important;
			background: var(--ast-notice-accent, #3b64d3) !important;
			color: #fff !important;
			border: 1px solid var(--ast-notice-accent, #3b64d3) !important;
			border-radius: 3px !important;
			text-transform: uppercase !important;
			text-decoration: none !important;
			padding: 6px 14px !important;
			font-size: 12px !important;
			font-weight: 600 !important;
			height: auto !important;
			min-height: 0 !important;
			box-shadow: none !important;
			margin: 5px 6px 10px 0 !important;
			cursor: pointer;
			vertical-align: middle;
		}
		.ast-dismissable-notice .ast-notice-btn:hover,
		.ast-dismissable-notice .ast-notice-btn:focus {
			filter: brightness(0.88);
			color: #fff !important;
		}
		</style>
		<?php
	}

	/**
	 * Render a dismissible promotional notice.
	 *
	 * @param array $args {
	 *     @type string $query_arg     GET flag used to dismiss the notice.
	 *     @type string $nonce_action  Nonce action paired with $query_arg.
	 *     @type string $accent        Accent color hex (drives --ast-notice-accent).
	 *     @type string $content       Trusted HTML for the notice body (heading + copy).
	 *     @type string $primary_url   Optional primary CTA href.
	 *     @type string $primary_label Optional primary CTA label.
	 *     @type string $primary_target Optional primary CTA target attribute.
	 * }
	 */
	private function render_dismissible_notice( $args ) {
		$this->emit_shared_notice_styles();
		$dismiss_url    = $this->dismiss_url( $args['query_arg'], $args['nonce_action'] );
		$accent         = isset( $args['accent'] ) ? $args['accent'] : '#3b64d3';
		$primary_url    = isset( $args['primary_url'] ) ? $args['primary_url'] : '';
		$primary_label  = isset( $args['primary_label'] ) ? $args['primary_label'] : '';
		$primary_target = isset( $args['primary_target'] ) ? $args['primary_target'] : '_self';
		?>
		<div class="notice updated notice-success ast-dismissable-notice" style="--ast-notice-accent: <?php echo esc_attr( $accent ); ?>;">
			<a href="<?php echo esc_url( $dismiss_url ); ?>" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></a>
			<?php echo $args['content']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted internal markup ?>
			<?php if ( $primary_url ) : ?>
				<a class="ast-notice-btn" target="<?php echo esc_attr( $primary_target ); ?>" href="<?php echo esc_url( $primary_url ); ?>"><?php echo esc_html( $primary_label ); ?></a>
			<?php endif; ?>
			<a class="ast-notice-btn" href="<?php echo esc_url( $dismiss_url ); ?>"><?php esc_html_e( 'Dismiss', 'woo-advanced-shipment-tracking' ); ?></a>
		</div>
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

	public function ast_review_admin_notice_388() {
		if ( get_option( 'ast_review_update_ignore_388' ) ) {
			return;
		}

		ob_start();
		?>
		<h2><?php esc_html_e( '⭐ Enjoying AST? Leave Us a Review!', 'woo-advanced-shipment-tracking' ); ?></h2>
		<p><?php echo wp_kses_post( __( 'We hope <strong>Advanced Shipment Tracking</strong> has improved your order fulfillment workflow! Your feedback helps us grow and continue improving the plugin.', 'woo-advanced-shipment-tracking' ) ); ?></p>
		<p><?php esc_html_e( 'If you love using AST, we\'d really appreciate it if you could take a moment to leave us a 5-star review. It helps us keep improving and providing the best experience for you!', 'woo-advanced-shipment-tracking' ); ?></p>
		<p><?php esc_html_e( '👍 Support AST & Share Your Experience!', 'woo-advanced-shipment-tracking' ); ?></p>
		<?php
		$content = ob_get_clean();

		$this->render_dismissible_notice( array(
			'query_arg'      => 'ast-review-update-notice-388',
			'nonce_action'   => 'ast_review_dismiss_notice_388',
			'accent'         => '#3b64d3',
			'content'        => $content,
			'primary_url'    => 'https://wordpress.org/support/plugin/woo-advanced-shipment-tracking/reviews/#new-post',
			'primary_label'  => __( 'Leave a Review', 'woo-advanced-shipment-tracking' ),
			'primary_target' => '_blank',
		) );
	}

	/* -----------------------------------------------------------------
	 * Notice: AST PRO upsell (🚀)
	 * ----------------------------------------------------------------- */

	public function ast_pro_notice_4_0() {
		if ( get_option( 'ast_notice_ignore_4_0' ) ) {
			return;
		}

		ob_start();
		?>
		<h3 class="ts-updated-notice"><?php esc_html_e( '🚀 Upgrade to AST PRO – Automate Your Shipping Workflow', 'woo-advanced-shipment-tracking' ); ?></h3>
		<p><?php esc_html_e( 'Stop copy-pasting tracking numbers:', 'woo-advanced-shipment-tracking' ); ?></p>
		<ul>
			<li><?php esc_html_e( '✅ Auto-import tracking from 70+ shipping providers', 'woo-advanced-shipment-tracking' ); ?></li>
			<li><?php esc_html_e( '✅ Sync tracking to PayPal & Stripe to release funds faster', 'woo-advanced-shipment-tracking' ); ?></li>
			<li><?php esc_html_e( '✅ Update orders & notify customers in one click', 'woo-advanced-shipment-tracking' ); ?></li>
			<li><?php esc_html_e( '✅ Item-level tracking, custom statuses & bulk CSV import', 'woo-advanced-shipment-tracking' ); ?></li>
			<li><?php esc_html_e( '✅ Manage everything from one fulfillment dashboard', 'woo-advanced-shipment-tracking' ); ?></li>
		</ul>
		<p><?php echo wp_kses_post( __( '🎁 <strong>20% OFF</strong> with code <strong>ASTPRO20</strong> — new customers only!', 'woo-advanced-shipment-tracking' ) ); ?></p>
		<?php
		$content = ob_get_clean();

		$this->render_dismissible_notice( array(
			'query_arg'      => 'ast-pro-notice-4-0',
			'nonce_action'   => 'ast_pro_dismiss_notice_4_0',
			'accent'         => '#3b64d3',
			'content'        => $content,
			'primary_url'    => 'https://www.zorem.com/product/woocommerce-advanced-shipment-tracking/',
			'primary_label'  => __( '👉 Upgrade to AST PRO', 'woo-advanced-shipment-tracking' ),
			'primary_target' => '_blank',
		) );
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
