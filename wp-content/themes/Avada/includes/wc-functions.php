<?php
/**
 * Override core-WooCommerce functions.
 *
 * @author     ThemeFusion
 * @link       http://theme-fusion.com
 * @package    Avada
 * @subpackage Core
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Display cross-sell template.
 *
 * @param int    $posts_per_page Number of posts in the query.
 * @param int    $columns        Number of culumns.
 * @param string $orderby        Determines how the query will order the posts.
 * @param string $order          Determines how the query will order the posts.
 */
function woocommerce_cross_sell_display( $posts_per_page = 3, $columns = 3, $orderby = 'rand', $order = 'desc' ) {

	global $woocommerce_loop;

	$attributes = array(
		'posts_per_page' => $posts_per_page,
		'orderby'        => $orderby,
		'columns'        => $columns,
	);

	if ( version_compare( WC_VERSION, '3.0', '>=' ) ) {
		// Get visble cross sells then sort them at random.
		$cross_sells = array_filter( array_map( 'wc_get_product', WC()->cart->get_cross_sells() ), 'wc_products_array_filter_visible' );

		// Handle orderby and limit results.
		$orderby        = apply_filters( 'woocommerce_cross_sells_orderby', $orderby );
		$cross_sells    = wc_products_array_orderby( $cross_sells, $orderby, $order );
		$posts_per_page = apply_filters( 'woocommerce_cross_sells_total', $posts_per_page );
		$cross_sells    = $posts_per_page > 0 ? array_slice( $cross_sells, 0, $posts_per_page ) : $cross_sells;

		$attributes['cross_sells'] = $cross_sells;
		$woocommerce_loop['columns'] = $columns;
	}

	wc_get_template( 'cart/cross-sells.php', $attributes );
}

/**
 * Gets the shipping calculator template.
 */
function woocommerce_shipping_calculator() {
	if ( ! is_cart() ) {
		wc_get_template( 'cart/shipping-calculator.php' );
	}
}

/* Omit closing PHP tag to avoid "Headers already sent" issues. */
