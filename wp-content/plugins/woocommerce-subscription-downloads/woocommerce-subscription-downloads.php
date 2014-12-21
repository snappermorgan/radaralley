<?php
/**
 * Plugin Name: WooCommerce Subscription Downloads
 * Plugin URI: http://www.woothemes.com/woocommerce/
 * Description: Associate downloadable products with a Subscription product in WooCommerce, and grant subscribers access to the associated downloads for the downloadable products.
 * Version: 1.0.0
 * Author: WooThemes
 * Author URI: http://woothemes.com
 * Text Domain: woocommerce-subscription-downloads
 * Domain Path: /languages
 *
 * @package  WC_Subscription_Downloads
 * @category Core
 * @author   WooThemes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Required functions
 */
if ( ! function_exists( 'woothemes_queue_update' ) ) {
	require_once( 'woo-includes/woo-functions.php' );
}

/**
 * Plugin updates
 */
woothemes_queue_update( plugin_basename( __FILE__ ), '5be9e21c13953253e4406d2a700382ec', '420458' );

if ( ! class_exists( 'WC_Subscription_Downloads' ) ) :

class WC_Subscription_Downloads {

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	const VERSION = '1.0.0';

	/**
	 * Instance of this class.
	 *
	 * @var object
	 */
	protected static $instance = null;

	/**
	 * Initialize the plugin public actions.
	 */
	private function __construct() {
		// Load plugin text domain.
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

		if ( class_exists( 'WooCommerce' ) && class_exists( 'WC_Subscriptions' ) ) {
			$this->includes();

			if ( is_admin() ) {
				$this->admin_includes();
			}
		} else {
			add_action( 'admin_notices', array( $this, 'woocommerce_missing_notice' ) );
		}
	}

	/**
	 * Return an instance of this class.
	 *
	 * @return object A single instance of this class.
	 */
	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @return void
	 */
	public function load_plugin_textdomain() {
		$domain = 'woocommerce-subscription-downloads';
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

		load_textdomain( $domain, trailingslashit( WP_LANG_DIR ) . $domain . '/' . $domain . '-' . $locale . '.mo' );
		load_plugin_textdomain( $domain, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Front-end actions.
	 *
	 * @return void
	 */
	protected function includes() {
		include_once 'includes/class-wc-subscription-downloads-order.php';
	}

	/**
	 * Admin actions.
	 *
	 * @return void
	 */
	protected function admin_includes() {
		include_once 'includes/class-wc-subscription-downloads-products.php';
		include_once 'includes/class-wc-subscription-downloads-ajax.php';
	}

	/**
	 * Install the plugin.
	 *
	 * @return void
	 */
	public static function install() {
		include_once 'includes/class-wc-subscription-downloads-install.php';
	}

	/**
	 * Get subscriptions from a downloadable product.
	 *
	 * @param  int $product_id
	 *
	 * @return array
	 */
	public static function get_subscriptions( $product_id ) {
		global $wpdb;

		$query = $wpdb->get_results( $wpdb->prepare( "SELECT subscription_id FROM {$wpdb->prefix}woocommerce_subscription_downloads WHERE product_id = %d", $product_id ), ARRAY_A );

		$subscriptions = array();
		foreach ( $query as $item ) {
			$subscriptions[] = $item['subscription_id'];
		}

		return $subscriptions;
	}

	/**
	 * Get downloadable products from a subscription.
	 *
	 * @param  int $subscription_id
	 *
	 * @return array
	 */
	public static function get_downloadable_products( $subscription_id ) {
		global $wpdb;

		$query = $wpdb->get_results( $wpdb->prepare( "SELECT product_id FROM {$wpdb->prefix}woocommerce_subscription_downloads WHERE subscription_id = %d", $subscription_id ), ARRAY_A );

		$products = array();
		foreach ( $query as $item ) {
			$products[] = $item['product_id'];
		}

		return $products;
	}

	/**
	 * Get order download files.
	 *
	 * @param  WC_Order $order Order data.
	 *
	 * @return array           Download data (name, file and download_url).
	 */
	public static function get_order_downloads( $order ) {
		$downloads = array();

		if ( 0 < sizeof( $order->get_items() ) && WC_Subscriptions_Order::order_contains_subscription( $order ) && $order->is_download_permitted() ) {
			foreach ( $order->get_items() as $item ) {
				$_product_id = $item['variation_id'] ? $item['variation_id'] : $item['product_id'];

				// Gets the downloadable products.
				$downloadable_products = WC_Subscription_Downloads::get_downloadable_products( $_product_id );

				if ( $downloadable_products ) {
					foreach ( $downloadable_products as $product_id ) {
						$_item = array(
							'product_id'   => $product_id,
							'variation_id' => ''
						);

						// Get the download data.
						$_downloads = $order->get_item_downloads( $_item );

						if ( empty( $_downloads ) ) {
							continue;
						}

						foreach ( $_downloads as $download ) {
						 	$downloads[] = $download;
						}
					}
				}
			}
		}

		return $downloads;
	}

	/**
	 * WooCommerce fallback notice.
	 *
	 * @return string
	 */
	public function woocommerce_missing_notice() {
		echo '<div class="error"><p>' . sprintf( __( 'WooCommerce Subscription Downloads depends on the last version of %s and %s to work!', 'woocommerce-subscription-downloads' ), '<a href="http://wordpress.org/extend/plugins/woocommerce/">' . __( 'WooCommerce', 'woocommerce-subscription-downloads' ) . '</a>', '<a href="http://www.woothemes.com/products/woocommerce-subscriptions/">' . __( 'WooCommerce Subscriptions', 'woocommerce-subscription-downloads' ) . '</a>' ) . '</p></div>';
	}
}

add_action( 'plugins_loaded', array( 'WC_Subscription_Downloads', 'get_instance' ), 0 );

/**
 * Install plugin.
 */
register_activation_hook( __FILE__, array( 'WC_Subscription_Downloads', 'install' ) );

endif;
