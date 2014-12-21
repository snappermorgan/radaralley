<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * WooCommerce Subscription Downloads Ajax.
 *
 * @package  WC_Subscription_Downloads_Ajax
 * @category Ajax
 * @author   WooThemes
 */
class WC_Subscription_Downloads_Ajax {

	/**
	 * Ajax actions.
	 */
	public function __construct() {
		add_action( 'wp_ajax_wc_subscription_downloads_search', array( $this, 'search_subscriptions' ) );
	}

	/**
	 * Search subscriptions.
	 *
	 * @return string
	 */
	public function search_subscriptions() {
		global $wpdb;

		check_ajax_referer( 'search-product-subscriptions', 'security' );

		header( 'Content-Type: application/json; charset=utf-8' );

		$term = wc_clean( stripslashes( $_GET['term'] ) );

		if ( empty( $term ) ) {
			die();
		}

		$found_subscriptions = array();

		$term = apply_filters( 'woocommerce_subscription_downloads_json_search_order_number', $term );

		$query_subscriptions = $wpdb->get_results( $wpdb->prepare( "
			SELECT ID, post_title
			FROM $wpdb->posts AS posts
				LEFT JOIN $wpdb->term_relationships AS t_relationships ON(posts.ID = t_relationships.object_id)
				LEFT JOIN $wpdb->term_taxonomy AS t_taxonomy ON(t_relationships.term_taxonomy_id = t_taxonomy.term_taxonomy_id)
				LEFT JOIN $wpdb->terms AS terms ON(t_taxonomy.term_id = terms.term_id)
			WHERE posts.post_type = 'product'
			AND posts.post_status = 'publish'
			AND posts.post_title LIKE %s
			AND t_taxonomy.taxonomy = 'product_type'
			AND terms.slug = 'subscription' OR terms.slug = 'variable-subscription'
			ORDER BY posts.post_date DESC
		", '%' . $term . '%' ) );

		if ( $query_subscriptions ) {
			foreach ( $query_subscriptions as $item ) {
				$order_number = apply_filters( 'woocommerce_order_number', _x( '#', 'hash before order number', 'woocommerce-subscription-downloads' ) . $item->ID, $item->ID );
				$found_subscriptions[ $item->ID ] = $order_number . ' &ndash; ' . esc_html( $item->post_title );
			}
		}

		echo json_encode( $found_subscriptions );
		die();
	}
}

new WC_Subscription_Downloads_Ajax;
