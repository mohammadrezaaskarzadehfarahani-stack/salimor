<?php
/**
 * Product in a WooCommerce loop — rendered with the theme's product card.
 *
 * @package Salimor
 * @version 9.4.0
 */

defined( 'ABSPATH' ) || exit;

global $product;
if ( empty( $product ) || ! $product->is_visible() ) {
	return;
}
salimor_product_card( $product );
