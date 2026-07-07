=== Advanced Shipment Tracking for WooCommerce ===
Contributors: zorem, gaurav1092, eranzorem, satishzorem
Tags: shipment tracking, order tracking, tracking number, fulfillment, shipping notifications
Requires at least: 5.3
Tested up to: 7.0
Stable tag: 4.0.1
Requires PHP: 7.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

The #1 WooCommerce shipment tracking plugin. Add tracking, auto-update order status, send branded emails. 1010+ carriers. Trusted by 60,000+ stores.

== Description ==

**Trusted by 60,000+ WooCommerce stores worldwide.** Advanced Shipment Tracking (AST) helps WooCommerce store owners add tracking numbers to orders and automatically share them with customers — reducing "Where is my order?" support tickets and keeping shoppers informed from checkout to delivery.

When you ship an order, your customer instantly gets a tracking link in their order email and on their **My Account > Orders** page. No external accounts, no complex setup, no monthly fees.

**HPOS Compatible** · **WooCommerce Block Checkout Compatible** · **1010+ Carriers** · **Free & Open Source**

= Why 60,000+ Stores Choose AST =

Whether you ship 5 orders a day or 5,000, AST fits into your workflow:

* **Add Tracking Numbers Easily** — Add one or multiple tracking numbers per order directly from the Edit Order page or the orders list.
* **"Shipped" & "Partially Shipped" Statuses** — Rename the default WooCommerce "Completed" status to "Shipped" and use "Partially Shipped" for split shipments, so customers always know what's going on.
* **Customizable Tracking Widget** — A responsive tracking display appears in order emails and on the My Account > Orders page. Customize colors, layout, and content with a live preview customizer.
* **1010+ Shipping Carriers Worldwide** — Predefined tracking links for USPS, UPS, FedEx, DHL, Royal Mail, Australia Post, Canada Post, Delhivery, ePacket, Yodel, La Poste, Correos, Japan Post, China Post, and hundreds more. [View the full carrier list](https://docs.zorem.com/docs/ast-free/shipping-carriers/#shipping-carriers-list-options/).
* **Shipment Tracking REST API** — Let third-party shipping services, ERPs, and label generators push tracking data to your orders via the WooCommerce REST API. [API documentation](https://docs.zorem.com/docs/ast-free/add-tracking-to-orders/shipment-tracking-api/).
* **Bulk CSV Import** — Upload a CSV file to add tracking numbers to hundreds of orders at once. Perfect for high-volume stores and dropshippers. [CSV import guide](https://docs.zorem.com/docs/ast-free/add-tracking-to-orders/csv-import/).
* **Works with the Shipping Tools You Already Use** — Compatible with ShipStation, WooCommerce Shipping, Ordoro, Royal Mail Click & Drop, Sendcloud, Printful, AliExpress dropshipping, and other major shipping label generators.
* **HPOS & Block Checkout Ready** — Fully compatible with WooCommerce High-Performance Order Storage (custom order tables) and the new Cart and Checkout blocks.
* **PayPal Tracking Foundation** — AST structures your tracking data correctly so it can sync with PayPal (automated sync available in AST PRO).
* **Built for Performance** — Lightweight, optimized code with zero measurable impact on site load times.

= How It Works =

1. Install and activate AST.
2. Select your default shipping carriers from the settings page.
3. Add a tracking number to an order — manually, via CSV, or through the REST API.
4. AST automatically includes the tracking info and a "Track" link in the order email and the customer's My Account page.

That's it. No complex setup, no external accounts required.

= Works With Your Existing Plugins =

AST is tested and compatible with popular WooCommerce plugins, including shipping label generators, email customizers (Kadence, YayMail, WP HTML Mail), custom order number plugins, multi-vendor solutions (Dokan, WCFM), SMS notification plugins, and PDF invoice tools.

[Full compatibility list](https://docs.zorem.com/docs/ast-free/compatibility/)

= Automate Tracking Updates with TrackShip =

Want to go beyond adding tracking numbers? [TrackShip for WooCommerce](https://wordpress.org/plugins/trackship-for-woocommerce/) monitors your shipments in real time across 1010+ carriers and proactively notifies your customers at every stage — in transit, out for delivery, and delivered.

With TrackShip you can:

* Reduce "Where is my order?" inquiries with automatic delivery updates via email and SMS.
* Offer a branded tracking page on your store instead of sending customers to carrier websites.
* Automate order status updates based on actual shipment events.
* Analyze shipping performance and delivery times.

[Learn more about TrackShip](https://trackship.com/)

= Need More Power? Try AST PRO =

[AST PRO](https://www.zorem.com/product/woocommerce-advanced-shipment-tracking/) adds features built for high-volume and multi-channel stores:

* **Shipping Service Integrations** — Built-in connections with ShipStation, WooCommerce Shipping, Ordoro, Royal Mail Click & Drop, Sendcloud, Pirate Ship, Stamps.com, Printful, and more.
* **Fulfillment Dashboard** — Manage all shipments from a centralized dashboard.
* **Item-Level Tracking** — Assign tracking numbers to individual order items and quantities.
* **Custom Email Templates** — Enhanced, responsive email notifications with the built-in tracking widget.
* **Auto-Detect Carriers** — Automatically identify the shipping carrier based on the tracking number format.
* **Custom & White-Labeled Carriers** — Define your own shipping carriers with custom names, logos, and tracking URLs.
* **Automated CSV Import via FTP/SFTP** — Schedule recurring bulk imports.
* **PayPal Tracking Sync** — Automatically export tracking data to PayPal to reduce disputes and release payment holds.
* **Stripe Tracking Sync** — Automatically sync tracking to Stripe transactions.
* **Priority Support** — Get faster, dedicated assistance.

[Get AST PRO](https://www.zorem.com/product/woocommerce-advanced-shipment-tracking/)

= Translations =

AST is fully translatable and already available in English, German, Hebrew, Hindi, Italian, Norwegian, Russian, Swedish, Turkish, Bulgarian, Danish, Spanish, French, Greek, Portuguese (Brazil), and Dutch.

Want to help? [Submit a translation](https://docs.zorem.com/docs/ast-free/translations/#submit-translation-files).

= Documentation & Support =

Step-by-step setup guides, tutorials, and developer code snippets are available in the [AST documentation](https://docs.zorem.com/docs/ast-free/).

Need help? Visit the [support forum](https://wordpress.org/support/plugin/woo-advanced-shipment-tracking/).

= More Plugins by Zorem =

* [TrackShip for WooCommerce](https://wordpress.org/plugins/trackship-for-woocommerce/) — Real-time shipment monitoring and delivery notifications.
* [SMS for WooCommerce](https://wordpress.org/plugins/sms-for-woocommerce/) — Send SMS order notifications to customers.
* [Zorem Local Pickup Pro](https://www.zorem.com/product/zorem-local-pickup-pro/) — Local pickup and store pickup for WooCommerce.

Explore all plugins at [zorem.com](https://www.zorem.com/).

== Installation ==

1. Go to **Plugins > Add New** in your WordPress admin and search for "Advanced Shipment Tracking".
2. Click **Install Now**, then **Activate**.
3. Navigate to **WooCommerce > Shipment Tracking** to select your default shipping carriers and configure settings.
4. Open any order, add a tracking number and shipping carrier, and the tracking info will be included in the customer's order email and My Account page.

Alternatively, upload the `woo-advanced-shipment-tracking` folder to `/wp-content/plugins/` and activate through the Plugins menu.

== Frequently Asked Questions ==

= How is AST different from the official WooCommerce Shipment Tracking extension? =

AST is built for store owners who want more than basic tracking links. Compared to the official WooCommerce Shipment Tracking extension, AST adds: 1010+ pre-configured shipping carriers (vs ~50), a customizable tracking widget with live preview, a complete "Shipped / Partially Shipped" fulfillment workflow, bulk CSV import, a Shipment Tracking REST API, and automatic data migration from the official plugin. AST is free, open source, and trusted by 60,000+ active WooCommerce stores.

= I'm migrating from WooCommerce Shipment Tracking (by WooCommerce). Will my data carry over? =

Yes. When you activate AST, it automatically detects and migrates existing shipment tracking data from the official WooCommerce Shipment Tracking extension, so your customers won't lose access to their tracking information.

= Is AST compatible with HPOS (High-Performance Order Storage)? =

Yes. AST has fully declared HPOS compatibility and works with WooCommerce's High-Performance Order Storage (custom order tables) for fast performance on high-volume stores.

= Does AST work with WooCommerce's new Cart and Checkout blocks? =

Yes. AST is compatible with the WooCommerce Block Checkout. Tracking information displays correctly in all block-based email templates and on the My Account page regardless of whether you use the classic shortcode checkout or the new block checkout.

= Will AST slow down my WooCommerce store? =

No. AST is built for performance with lightweight, optimized code. It loads tracking-related assets only on the admin order pages and on the customer-facing tracking widget. There is no measurable impact on page load speed, Core Web Vitals, or checkout performance.

= How do I add a tracking number to a WooCommerce order? =

Open any order from **WooCommerce > Orders**, and you'll see a Shipment Tracking meta box. Select a shipping carrier, enter the tracking number, and save. The tracking info and a link to track the shipment will automatically appear in the customer's order email and on their My Account > Orders page.

= Where do my customers see their tracking information? =

Tracking details appear in two places: in the order status email (Shipped/Completed) and on the customer's My Account > View Order page. If you use the [WooCommerce order tracking shortcode](https://docs.woocommerce.com/document/woocommerce-shortcodes/#page-shortcodes), guest customers can also view their tracking info by entering their email and order ID.

= Can I customize how the tracking info looks in emails? =

Yes. AST includes a built-in customizer with live preview where you can control the design, layout, colors, and content of the tracking info widget displayed in emails and on the My Account page. See the [tracking widget customization guide](https://docs.zorem.com/docs/ast-free/setup-configuration/customize-the-email-notifications/#customize-the-tracking-widget).

= Can I add multiple tracking numbers to one order? =

Yes. You can add as many tracking numbers as needed to a single order. All tracking entries will be displayed to your customer in the order email and on their My Account page.

= Can I assign tracking numbers to specific products in an order? =

Item-level tracking (assigning tracking numbers to specific line items and quantities) is available in [AST PRO](https://www.zorem.com/product/woocommerce-advanced-shipment-tracking/).

= Does AST integrate with PayPal tracking? =

The free version of AST stores your tracking data in the correct WooCommerce metadata structure so it's ready for PayPal tracking. Automatic synchronization of tracking numbers to PayPal transactions (which helps release payment holds and reduce "Item Not Received" disputes) is available in [AST PRO](https://www.zorem.com/product/woocommerce-advanced-shipment-tracking/).

= Does AST automatically track my shipments and send delivery notifications? =

AST handles adding tracking information to orders and sharing it with customers. For automatic shipment monitoring, real-time status updates, and proactive delivery notifications across 1010+ carriers, install [TrackShip for WooCommerce](https://wordpress.org/plugins/trackship-for-woocommerce/).

= My shipping carrier is not on the list. Can I add a custom carrier? =

AST includes 1010+ predefined carriers. If yours is missing, you can suggest it on our [feature request board](https://feedback.zorem.com/ast) or the [support forum](https://wordpress.org/support/plugin/woo-advanced-shipment-tracking/). Adding your own custom carriers with custom tracking URLs is available in [AST PRO](https://www.zorem.com/product/woocommerce-advanced-shipment-tracking/).

= Can I import tracking numbers in bulk from a CSV file? =

Yes. Use the built-in [CSV import tool](https://docs.zorem.com/docs/ast-free/add-tracking-to-orders/csv-import/) to add tracking numbers to multiple orders at once. Each row in the CSV corresponds to one tracking number entry.

= Can I use AST for dropshipping (AliExpress, CJ Dropshipping, Spocket, etc.)? =

Yes. AST is widely used by WooCommerce dropshipping stores. You can bulk import tracking numbers from your supplier's CSV file, or use the REST API to push tracking data from third-party fulfillment platforms. The 1010+ supported carriers include all major dropshipping logistics providers — ePacket, Yanwen, Yun Express, China Post, 4PX, and others.

= Does AST work with multi-vendor marketplaces like Dokan or WCFM? =

Yes. AST is compatible with Dokan, WCFM Marketplace, and Product Vendors. Vendors can add their own tracking numbers to their orders, and customers receive tracking information through the standard WooCommerce email and My Account flows.

= Does AST support custom order numbers from other plugins? =

Yes. AST is compatible with popular custom order number plugins. You can use custom order numbers (not just the default WooCommerce order ID) in the CSV import. See the [compatibility list](https://docs.zorem.com/docs/ast-free/compatibility/) for supported plugins.

= Is there a REST API to add tracking numbers programmatically? =

Yes. If you use external shipping services or fulfillment tools that work with the WooCommerce REST API, they can use the [AST shipment tracking API endpoint](https://docs.zorem.com/docs/ast-free/add-tracking-to-orders/shipment-tracking-api/) to create, update, and delete tracking entries on orders.

== Screenshots ==

1. Add shipment tracking numbers to orders from the Edit Order page with carrier selection and tracking number fields.
2. Customize the tracking info widget design for emails and My Account using the built-in live preview customizer.
3. The Shipping Carriers settings page — search, enable, and manage 1010+ carriers.
4. Tracking info displayed on the customer's My Account > View Order page.
5. Shipment tracking details shown in the WooCommerce order email notification.
6. Bulk CSV import tool to add tracking numbers to multiple orders at once.
7. The Shipment Tracking REST API allows third-party services to push tracking data.
8. Order list showing shipment tracking column with tracking numbers and carrier info.
9. Fulfillment workflow settings — configure Shipped, Partially Shipped, and Delivered statuses.
10. AST settings page for customizing order statuses, email display, and tracking behavior.

== Changelog ==

= 4.0.1 =
* Dev – Tested with WooCommerce 10.9.3
* Fix – Hardened the CSV import against a SQL injection vulnerability (CVE-2026-57773).

= 4.0 =
* Dev – Tested with WooCommerce 10.8.1 and WordPress 7.0
* New – Completely redesigned the AST FREE admin interface with a modern, cleaner look.

= 3.9.2 =
* Dev - Tested with WooCommerce 10.7.0
* Fix – Replaced deprecated wc_enqueue_js with wp_add_inline_script for WooCommerce 10.4.0+ compatibility
* Fix - Updated version header in customer-completed-order.php email template for WooCommerce 10.4.0 compatibility

= 3.9.1 =
* Dev - Tested with WooCommerce 10.6.1 and WordPress 6.9.4
* Dev - Added support for %phone_number% variable in shipping carrier tracking URLs for dynamic replacement
* Fix - Corrected German translation for "Track" button from "Spur" to "Verfolgen"
* Security - Fixed a vulnerability reported via Patchstack affecting Advanced Shipment Tracking for WooCommerce.

= 3.9 =
* Dev - Tested with WooCommerce 10.5.0 and WordPress 6.9.1

= 3.8.9 =
* Dev - Tested with WooCommerce 10.4.2 and WordPress 6.9
* Fix - Added compatibility handling for RouteApp plugin to prevent conflicts with tracking metadata updates that caused infinite loading when adding tracking.

= 3.8.8 =
* Dev - Tested with WooCommerce 10.3.5
* Enhancement – Updated "Shipping Carrier Not Found!" message with a suggestion to sync carriers for the latest list.
* Fix – Updated deprecated WooCommerce script handles (jquery-blockui, jquery-tiptip, serializejson) to new handles (wc-jquery-blockui, wc-jquery-tiptip, wc-serializejson) for compatibility with WooCommerce 10.3+.

= 3.8.7 =
* Dev - Tested with WooCommerce 10.2.2 and WordPress 6.8.3
* Improvement - Responsive Design of Tracking Widget on My Account Order Details Page.

= 3.8.6 =
* Dev - Tested with WooCommerce 10.1.2
* Fix – Prevented duplicate tracking numbers and incorrect order status changes when pressing Enter in Add Tracking sidebar.
* Fix – Resolved fatal error when tracking numbers contain a percent symbol (%).

= 3.8.5 =
* Dev - Tested with WooCommerce 10.0.4 and WordPress 6.8.2 
* Improvement – Improved email preview to show tracking widget only for selected statuses.
* Fix - Tooltip Not Working with WooCommerce 10

= 3.8.4 =
* Dev - Tested with WooCommerce 9.9.4
* Dev - Updated shipped date format to use hyphens (e.g., dd-mm-YYYY) for clarity.
* Fix - Resolved issue where "Enable Carriers" button did not open the sidebar on the Shipping Carriers page.

= 3.8.3 =
* Dev - Tested with WooCommerce 9.9.3
* Fix - Undefined array key "wc-delivered", "wc-partial-shipped" warning in `wc_orders_count()` usage.

= 3.8.2 =
* Dev - Tested with WooCommerce 9.8.5
* Dev - Update Spanish language translation file

= 3.8.1 =
* Dev - Tested with WooCommerce 9.8.4 and WordPress 6.8.1
* Dev - Re-add Option to Add Tracking on Order Update Hook

= 3.8.0 =
* Fix – Resolved issue with dismissing notices.

= 3.7.9 =
* Dev - Tested with WooCommerce 9.8.1 and WordPress 6.8 
* Dev - Added option to log Shipment Tracking API requests in WooCommerce logs.
* Enhancement - Show tracking source (e.g., "Manual", "CSV", "API") in the Shipment Tracking meta box.

= 3.7.8 =
* Enhancement - Redesigned the AST settings page.
* Fix - Corrected translation loading timing for the WooCommerce text domain.
* Fix - Fixed preg_replace parameter handling to ensure proper functionality.
* Fix - Resolved incorrect usage of wpdb::prepare, ensuring the correct number of placeholders match the passed arguments.
* Fix - Fixed the Datepicker initialization issue.

= 3.7.7 =
* Dev - Tested with WooCommerce 9.7.1
* Fix - Fixed an issue where refund orders were not handled correctly, preventing potential errors.
* Fix - Fixed incorrect function call `_load_textdomain_just_in_time`, ensuring translation loading for the WooCommerce domain occurs at the correct stage

= 3.7.6 =
* Dev - Tested with WooCommerce 9.6.2 and WordPress 6.7.2  
* Fix - Resolved CSS override issue with WooCommerce Orders Activity Panel  
* Fix - Fixed incorrect function call `_load_textdomain_just_in_time`, ensuring translation loading for the WooCommerce domain occurs at the correct stage

= 3.7.5 =
* Dev - Tested with WooCommerce 9.6.0
* Dev - Added Version Parameter to Shipping Carrier Image URL to Prevent Caching
* Fix - Partially Shipped and Shipped order status email not disabling from Order status & Notification panel
* Fix - Resolved a design compatibility issue with WooCommerce version 9.6.0

= 3.7.4 =
* Enhancement - Use custom tracking provider name if exist
* Dev - Tested with WooCommerce 9.5.1

= 3.7.3 =
* Dev - Tested with WooCommerce 9.4.3 and WordPress 6.7.1
* Fix - Warning - _load_textdomain_just_in_time was called incorrectly. Translation loading for the ast-pro domain was triggered too early.
* Fix - Warning - Undefined array key tracking_id

= 3.7.2 =
* Dev - Added an admin notice for Black Friday Sale
* Fix - PHP Deprecated: preg_replace(): Passing null to parameter #3

= 3.7.1 =
* Fix - Translation issue with WordPress 6.7
* Dev - Tested plugin with WordPress 6.7
* Dev - Tested with WooCommerce 9.4.1

= 3.7.0 =
* Enhancement - Move Usage Tracking panel from settings to license tab
* Dev - added 'manage_woocommerce' permission through filter
* Dev - Tested with WPML 4.7 and update the documentation
* Dev - Tested plugin with WordPress 6.6.2
* Dev - Tested with WooCommerce 9.3.3
* Fix - Updated tracking order status email doesn't work

= 3.6.9 =
* Dev - Tested plugin with WordPress 6.6.1
* Dev - Tested with WooCommerce 9.2.3
* Fix - Resolved an issue where the Tracking Info email template was not being overridden in the theme/child-theme

= 3.6.8 =
* Improvement - Updated the string "selected" to use a non-translatable function for better compatibility.
* Dev - Tested plugin with WordPress 6.5.5
* Dev - Tested with WooCommerce 9.0.2
* Fix - Fixed the issue where custom email content was not being saved properly.

= 3.6.7 =
* Enhancement - Improved the tracking info template design for responsive
* Dev - Tested plugin with WordPress 6.5.4
* Dev - Tested with WooCommerce 9.0.0

= 3.6.6 =
* Add - UTM link for all the external links to zorem.com
* Dev - Tested plugin with WordPress 6.5.2
* Dev - Tested with WooCommerce 8.8.2
* Dev - Add nonce in all the admin message dismissable URL
* Dev - updated the Synch providers API call URL
* Fix - translation issue on Add Tracking slideout
* Fix - Deprecated warnings
* Fix - "Creation of dynamic property WC_Advanced_Shipment_Tracking_Actions::$providers is deprecated"

= 3.6.5 =
* Dev - Test plugin with WordPress 6.4.2
* Dev - change date format to 'Y-m-d' in the shipment tracking API response
* Fix - Undefined variable $src 

= 3.6.4 =
* Fix - $ is not a function while add Tracking

= 3.6.3 =
* Enhancement - Update the settings page design
* Enhancement - Updated the Shipping Carriers design
* Dev - Test plugin with WordPress 6.4
* Dev - Compatibility with PHP 8.2
* Improve - the add tracking in edit order details page
* Fix - Undefined property: stdClass::$custom_tracking_url
* Fix - Undefined variable $fluid_hide_shipping_date
* Fix - Remove Mark as Shipped from actions column when the order status is Shipped

= 3.6.2 =
* Fix - Shipping carrier not showing on add tracking sidebar

= 3.6.1 =
* Fix - Shipping carrier not found in Shipping Carriers list
* Dev - added a filter "wc_ast_default_mark_shipped" to unchecked the uncheck the Mark order as: Shipped checkbox

= 3.6 =
* Enhancement - Update the design of the Add tracking popup
* Enhancement - Updated the design of Shipping Carriers list
* Enhancement - Change the terminology - Shipping providers" to "Shipping Carriers"
* Dev - Add script in footer for Open the Track Button link in a new tab option
* Dev - Tested plugin with WordPress 6.3.1
* Dev - Tested with WooCommerce 8.2
* Improve - Duplicate Queries on orders list
* Fix - search by country name not working in shipping providers list
* Fix - Undefined variable $tracking_number on woo-advanced-shipment-tracking/includes/class-wc-advanced-shipment-tracking.php on line 1076

= 3.5.3 =
* Enhancement - Improve the Shipping providers list page header design
* Enhancement - Add an option in the settings for Usage Tracking
* Enhancement - Update the Usage data sign-up box content
* Dev - Remove the trackship tracking page funtinality and add a filter on ast_tracking_link for TrackShip to use
* Fix - Vulnerable to Cross Site Request Forgery (CSRF)

= 3.5.2 =
* Enhancement - Added admin message for Survey
* Dev - Add validation in CSV Import when added short tracking number
* Dev - Remove the TrackSip message for TrackShip connected but TrackShip For WooCommerce is not installed
* Dev - Add Plain Fluid Tracking Info template
* Dev - Delete old tracking from TrackShip while Replace tracking information in CSV Import
* Dev - Change tracking info email template structure from Div to Table
* Fix - Customizer save issue in Firefox

= 3.5.1 =
* Fix - Fatal Error - Error message: Uncaught Automaic\WooCommerce\Vendor\League\Container\Exception\NotFoundException: Alias (Automaic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController) is not being managed by the container

= 3.5 =
* Add - Declared HPOS compatibility.
* Dev - Change TrackShip tracking page link
* Dev - In Shipment tracking REST API response use store date format for shipped date
* Enhancement - Upgrade the settings page design
* Enhancement - Open tracking link on new tab in My Account page
* Fix - Order status Email content formating issue

= 3.4.7 =
* Fix - "syntax error, unexpected ')' in class-wc-advanced-shipment-tracking.php on line 916" with PHP 7.2

= 3.4.6 =
* Enhancement - Move the fulfillment workflow panel on top in the settings page and updated the design of it
* Enhancement - Added "Display shipped/tracker section" option in the Tracking Widget customizer
* Fix - Translation issue in Tracking Widget
* Dev - tested with WooCommerce 6.8.0

= 3.4.5 =
* Enhancement - Added completed email in the AST customizer so you can customizer Completed email subject, heading and content
* Enhancement - Implement the new customizer for tracking widget and custom order status
* Enhancement - Re - design Tracking info widget
* Enhancement - Improve the Go Pro popup design
* Enhancement - Remove the Integrations tab from Shipment Tracking settings page
* Dev - tested with WooCommerce 6.7.0 and WordPress 6.0.1

= 3.4.4 =
* Dev - tested with WooCommerce 6.6.1
* Fix - Updated tracking email not sent

= 3.4.3 =
* Fix - Change esc_html__ to esc_html for dynamic variable in plain tracking info email template

= 3.4.2 =
* Dev - Change CURL to wp_remote_get to get the shipping provider image
* Dev - Remove text-domain from all the dynamic variables
* Dev - Improved the CSV Import functionality to work with shipping provider slug
* Dev - tested with WooCommerce 6.6

= 3.4.1 =
* Enhancement - Added AST PRO promotion message box in shipment tracking settings page
* Enhancement - Improved the design of TrackShip promotion page
* Dev - Removed the shipping providers zip file from plugin
* Dev - Improved code security
* Fix - Shipped date issue in spanish

= 3.4 =
* Enhancement - When sync providers, provider is disabled by default
* Dev - Update Greek translation files
* Dev - Test plugin with WooCommerce 6.5.1 and WordPress 6.0
* Dev - Test plugin with WooCommerce Multilingual 5.0.0
* Dev - Added compatibility with the Weglot Translation plugin
* Fix - Sync providers issue on plugin activation
* Fix - Date issue when adding tracking from CSV/API/Manually

= 3.3.2 =
* Enhancement - Make Shipped and Partially Shipped unremovable from Order Emails Display option
* Enhancement - Add Docs, Support and Review link on plugins page
* Enhancement - Added Sync providers Message on Shipping Providers list settings page
* Dev - Updated Croatian Translation Files
* Dev - Updated Deprecated jQuery code for jQuery keyup function
* Dev - Improve the synch providers functionality based on provider_slug
* Dev - Tested with WP 5.9.2 and WC 6.3.1
* Fix - Fixed error when add tracking information to order for PHP 8
* Fix - Fixed synch providers issue for J&T provider

= 3.3.1 =
* Dev - Added back the Delivered order status
* Dev - Added ParcelForce Integration
* Dev - Added admin message for database synch

= 3.3 =
* Dev - Removed TrackShip functionality
* Enhancement - Add line break for tracking # - orders list page
* Enhancement - Change uninstall > deactivate when deactivating the plugin
* Enhancement - Updated the design of settings page
* Enhancement - Updated the design of Go Pro page
* Enhancement - Remove clickable link from shipment tracking column in orders list page

= 3.2.9 =
* Enhancement - Updated addons page design
* Enhancement - Remove preview order option from tracking info and order status email customizer and set default dummy data
* Fix - Warning: Invalid argument supplied for foreach() in plugins/woo-advanced-shipment-tracking/includes/class-wc-advanced-shipment-tracking-admin.php on line 1801

= 3.2.8 =
* Dev - Removed the 'manage_woocommerce' capability when add tracking to orders

= 3.2.7 =
* Dev - Improve code security

= 3.2.6 =
* Fix - Fixed shipping provider search issue in Shipping provider settings page
* Dev - Improve code quality and security
* Enhancement - Added go pro lightbox in shipping provider page and integration page

= 3.2.5 =
* Dev - Added condition for standalone pro version
* Enhancement - Updated settings page design
* Dev - Removed Add custom provider functionality from shipping provider list
* Dev - Removed Edit custom provider functionality from shipping provider list
* Dev - Improved the security

= 3.2.4.1 =
* Enhancement - Added TrackShip menu inside Shipment Tracking if TrackShip not connected

= 3.2.4 =
* Dev - Tested with WordPress 5.7.2 and WooCommerce 5.3
* Dev - Added admin notice for the TrackShip For WooCommerce plugin
* Dev - Added WPML compatibility with TrackShip connection 

= 3.2.3 =
* Fix - Fixed warning - Undefined array key "wc_ast_tracking_page_customize_btn" on file "wp-content/plugins/woo-advanced-shipment-tracking/…class-wc-advanced-shipment-tracking-trackship.php" line: 435
* Dev - Tracking page on carriers website in Orders which are not tracked by TrackShip
* Dev - Tested with WooCommerce 5.2.0

= 3.2.2.4 =
* Fix - Fixed issue with default shipping provider not automatically select on Add Tracking form
* Dev - When user migrate from WooCommerce Shipment Tracking(official) to Advanced Shipment Tracking for WooCommerce plugin it will automatically migrate shipment tracking data
* Fix - Fix search provides design issue for small screen on shipping providers list page
* Dev - Improve TrackShip Tracking page design and make Estimate delivery date multilingual compatible
* Fix - Fixed - Failed Attempt filter not working on orders page

= 3.2.2.3 =
* Enhancement - Added datepicker on Add tracking lightbox on orders page
* Enhancement - Removed the multiple API name option
* Dev - Updated Admin html function

= 3.2.2.2 =
* Enhancement - Update order details, Shipping address and billing address email template
* Enhancement - Added admin notice and WooCommerce inbox message for Advanced Shipment Tracking PRO
* Enhancement - Split Shipment Tracking customizer and TrackShip customizer
* Fix - Fixed issue with the" Reset providers database" option in Synch providers

= 3.2.2.1 =
* Enhancement - Updated Settings page header design
* Enhancement - Updated TrackShip Settings page header design
* Enhancement - Updated Email order details template for shipment status emails
* Fix - Fixed Trackship shipment status customizer issue
* Fix - Fixed database warnings on plugin installation