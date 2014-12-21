<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * WooCommerce Subscription Downloads Order.
 *
 * @package  WC_Subscription_Downloads_Order
 * @category Order
 * @author   WooThemes
 */
class WC_Subscription_Downloads_Order {

	/**
	 * Order actions.
	 */
	public function __construct() {
		add_action( 'woocommerce_grant_product_download_permissions', array( $this, 'download_permissions' ) );
		add_action( 'woocommerce_email_after_order_table', array( $this, 'email_list_downloads' ), 10, 3 );
	}

	/**
	 * Save the download permissions in the order.
	 *
	 * @param  int $order_id Order ID.
	 *
	 * @return void
	 */
	public function download_permissions( $order_id ) {
		$order = new WC_Order( $order_id );

		// Checks if the order is an inscription.
		if ( WC_Subscriptions_Order::order_contains_subscription( $order ) ) {
			foreach ( $order->get_items() as $item ) {
				$_product_id = $item['variation_id'] ? $item['variation_id'] : $item['product_id'];

				// Gets the downloadable products.
				$downloadable_products = WC_Subscription_Downloads::get_downloadable_products( $_product_id );

				if ( $downloadable_products ) {
					foreach ( $downloadable_products as $product_id ) {
						$_product = get_product( $product_id );

						// Adds the downloadable files to the order/subscription.
						if ( $_product && $_product->exists() && $_product->is_downloadable() ) {
							$downloads = $_product->get_files();

							foreach ( array_keys( $downloads ) as $download_id ) {
								wc_downloadable_file_permission( $download_id, $product_id, $order );
							}
						}
					}
				}
			}
		}

	}

	/**
	 * List the downloads in order emails.
	 *
	 * @param  WC_Order $order         Order data
	 * @param  bool     $sent_to_admin Sent or not to admin.
	 * @param  bool     $plain_text    Plain or HTML email.
	 * @return string                  List of downloads.
	 */
	public function email_list_downloads( $order, $sent_to_admin = false, $plain_text = false ) {
		if ( $sent_to_admin && ! in_array( $order->status, array( 'processing', 'completed' ) ) ) {
			return;
		}

		$downloads = WC_Subscription_Downloads::get_order_downloads( $order );

		if ( $downloads && $plain_text ) {
			$html = apply_filters( 'woocommerce_subscription_downloads_my_downloads_title', __( 'Available downloads', 'woocommerce-subscription-downloads' ) );
			$html .= PHP_EOL . PHP_EOL;

			foreach ( $downloads as $download ) {
				$html .= $download['name'] . ': ' . $download['download_url'] . PHP_EOL;
			}

			$html .= PHP_EOL;
			$html .= '****************************************************';
			$html .= PHP_EOL . PHP_EOL;

			echo $html;

		} elseif ( $downloads && ! $plain_text ) {
			$html = '<h2>' . apply_filters( 'woocommerce_subscription_downloads_my_downloads_title', __( 'Available downloads', 'woocommerce-subscription-downloads' ) ) . '</h2>';

			$html .= '<table cellspacing="0" cellpadding="0" style="width: 100%; vertical-align: top;" border="0">';
				$html .= '<tr>';
					$html .= '<td valign="top">';
						$html .= '<ul class="digital-downloads">';
							foreach ( $downloads as $download ) {
								$html .= sprintf( '<li><a href="%1$s" title="%2$s" target="_blank">%2$s</a></li>', $download['download_url'], $download['name'] );
							}
						$html .= '</ul>';
					$html .= '</td>';
				$html .= '</tr>';
			$html .= '</table>';

			echo $html;
		}
	}
}

new WC_Subscription_Downloads_Order;
