<?php

if ( ! class_exists( 'WC_AST_Logger' ) ) {
	class WC_AST_Logger {

		/**
		 * The single instance of the class.
		 *
		 * @var WC_AST_Logger|null
		 */
		protected static $instance = null;

		public $logger;
		public $context;

		/**
		 * Get the singleton instance.
		 *
		 * @param string $source
		 * @return WC_AST_Logger
		 */
		public static function get_instance( $source = 'wc_ast_default_log' ) {
			if ( self::$instance === null ) {
				self::$instance = new self( $source );
			}
			return self::$instance;
		}

		/**
		 * Logs tracking-related events in a simplified format.
		 *
		 * @param string $source      Log source identifier.
		 * @param mixed  $log_content Content to be logged (array or string).
		 */
		public static function log_event( $source, $log_content = '' ) {
			$logger  = wc_get_logger();
			$context = array( 'source' => $source );

			// If the content is an array, encode it for better readability in the log
			if ( is_array( $log_content ) ) {
				$log_content = wp_json_encode( $log_content, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );
			}

			$logger->debug( $log_content, $context );
		}

	}
}
