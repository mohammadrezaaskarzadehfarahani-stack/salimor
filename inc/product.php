<?php
/**
 * Product page data: gallery, variation options, spec table, cart lines, AJAX add-to-cart.
 *
 * @package Salimor
 */

defined( 'ABSPATH' ) || exit;

/** Main image, gallery images, then variation images not already shown. */
function salimor_gallery_ids( WC_Product $p ) {
	$ids = array_merge( array( $p->get_image_id() ), $p->get_gallery_image_ids() );
	if ( $p->is_type( 'variable' ) ) {
		foreach ( $p->get_children() as $vid ) {
			$v = wc_get_product( $vid );
			if ( $v && $v->get_image_id() ) {
				$ids[] = $v->get_image_id();
			}
		}
	}
	return array_values( array_unique( array_filter( array_map( 'intval', $ids ) ) ) );
}

/**
 * Selectable attributes of a variable product.
 *
 * @return array[] [ [ key => attribute_pa_color, label, tax, default, values => [ [ value, label, hex ] ] ] ]
 */
function salimor_variation_options( WC_Product_Variable $p ) {
	$out = array();
	foreach ( $p->get_variation_attributes() as $name => $opts ) {
		$is_tax = taxonomy_exists( $name );
		$values = array();
		if ( $is_tax ) {
			foreach ( wc_get_product_terms( $p->get_id(), $name, array( 'fields' => 'all' ) ) as $t ) {
				if ( in_array( $t->slug, $opts, true ) ) {
					$values[] = array( $t->slug, $t->name, (string) get_term_meta( $t->term_id, 'salimor_hex', true ) );
				}
			}
		} else {
			foreach ( $opts as $o ) {
				$values[] = array( $o, $o, '' );
			}
		}
		if ( ! $values ) {
			continue;
		}
		$default = $p->get_variation_default_attribute( $name );
		if ( '' === $default && 1 === count( $values ) ) {
			$default = $values[0][0];
		}
		$out[] = array(
			'key'     => 'attribute_' . sanitize_title( $name ),
			'label'   => wc_attribute_label( $name, $p ),
			'tax'     => $is_tax ? $name : '',
			'default' => $default,
			'values'  => $values,
		);
	}
	return $out;
}

/** Purchasable variations, compact, for the option picker. */
function salimor_variation_data( WC_Product_Variable $p ) {
	$out = array();
	foreach ( $p->get_children() as $vid ) {
		$v = wc_get_product( $vid );
		if ( ! $v || ! $v->exists() || ! $v->variation_is_visible() || ! $v->is_purchasable() ) {
			continue;
		}
		$out[] = array(
			'id'    => $vid,
			'attrs' => $v->get_variation_attributes(),
			'html'  => salimor_price_html( $v ),
			'stock' => $v->is_in_stock(),
			'img'   => (int) $v->get_image_id(),
			'max'   => $v->get_max_purchase_quantity() > 0 ? $v->get_max_purchase_quantity() : 999,
		);
	}
	return $out;
}

/** Spec rows: visible attributes, weight, size, SKU. */
function salimor_product_specs( WC_Product $p ) {
	$rows = array();
	foreach ( $p->get_attributes() as $a ) {
		if ( ! $a->get_visible() || ( $p->is_type( 'variable' ) && $a->get_variation() ) ) {
			continue;
		}
		$vals = $a->is_taxonomy() ? wc_get_product_terms( $p->get_id(), $a->get_name(), array( 'fields' => 'names' ) ) : $a->get_options();
		if ( $vals ) {
			$rows[] = array( wc_attribute_label( $a->get_name(), $p ), implode( '، ', $vals ), false );
		}
	}
	if ( $p->has_weight() ) {
		$rows[] = array( 'وزن', salimor_fa( wc_format_localized_decimal( $p->get_weight() ) ) . ' ' . ( 'g' === get_option( 'woocommerce_weight_unit' ) ? 'گرم' : get_option( 'woocommerce_weight_unit' ) ), false );
	}
	if ( $p->has_dimensions() ) {
		$rows[] = array( 'ابعاد', salimor_fa( wc_format_dimensions( $p->get_dimensions( false ) ) ), false );
	}
	if ( $p->get_sku() ) {
		$rows[] = array( 'کد محصول', $p->get_sku(), true );
	}
	return $rows;
}

/** What is in the cart now: { product-or-variation id: [ cart key, qty ] }. */
function salimor_cart_lines() {
	$out = array();
	if ( ! WC()->cart ) {
		return $out;
	}
	foreach ( WC()->cart->get_cart() as $key => $item ) {
		$id         = $item['variation_id'] ? $item['variation_id'] : $item['product_id'];
		$out[ $id ] = array( $key, (int) $item['quantity'] );
	}
	return (object) $out;
}

add_filter( 'woocommerce_add_to_cart_fragments', function ( $fragments ) {
	$fragments['salimor_lines'] = salimor_cart_lines();
	return $fragments;
} );

/**
 * AJAX add to cart that also takes chosen attributes (needed when a variation allows «any» value).
 * POST: product_id, variation_id, quantity, attributes[attribute_pa_x]=value.
 */
function salimor_add_ajax() {
	$pid  = absint( $_POST['product_id'] ?? 0 ); // phpcs:ignore WordPress.Security.NonceVerification
	$vid  = absint( $_POST['variation_id'] ?? 0 ); // phpcs:ignore
	$qty  = wc_stock_amount( wp_unslash( $_POST['quantity'] ?? 1 ) ); // phpcs:ignore
	$atts = array();
	foreach ( (array) ( $_POST['attributes'] ?? array() ) as $k => $v ) { // phpcs:ignore
		$k = sanitize_title( wp_unslash( $k ) );
		if ( 0 === strpos( $k, 'attribute_' ) ) {
			$atts[ $k ] = wc_clean( wp_unslash( $v ) );
		}
	}
	$product = wc_get_product( $pid );
	if ( ! $product || $qty < 1 ) {
		wp_send_json( array( 'error' => true, 'message' => 'این محصول پیدا نشد.' ) );
	}
	$ok = apply_filters( 'woocommerce_add_to_cart_validation', true, $pid, $qty, $vid, $atts );
	if ( $ok && false !== WC()->cart->add_to_cart( $pid, $qty, $vid, $atts ) ) {
		do_action( 'woocommerce_ajax_added_to_cart', $pid );
		wc_clear_notices();
		WC_AJAX::get_refreshed_fragments();
	}
	$errors = wc_get_notices( 'error' );
	wc_clear_notices();
	$msg = $errors ? wp_strip_all_tags( is_array( $errors[0] ) ? $errors[0]['notice'] : $errors[0] ) : 'افزودن به سبد انجام نشد.';
	wp_send_json( array( 'error' => true, 'message' => $msg ) );
}
add_action( 'wc_ajax_salimor_add', 'salimor_add_ajax' );

/* The theme renders its own product summary; reviews are not used. */
add_filter( 'woocommerce_product_tabs', function ( $tabs ) {
	unset( $tabs['reviews'] );
	return $tabs;
}, 98 );
