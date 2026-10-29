<?php
/**
 * Shop filters: price ranges, every product attribute (colour, material, …), on sale, new, in stock.
 *
 * One small index per listing (shop, category or search) holds each product's price, attribute terms
 * and flags. The index answers both questions a faceted filter asks: which products match, and how
 * many products each option would leave. It is cached per category and rebuilt when products change.
 *
 * URL format (shareable, works without JavaScript):
 *   ?price=p1,p2&color=gold,silver&material=leather&sale=1&new=1&stock=1&orderby=price
 *
 * @package Salimor
 */

defined( 'ABSPATH' ) || exit;

/** Price ranges in toman: key => [label, min, max). */
function salimor_price_ranges() {
	return apply_filters(
		'salimor_price_ranges',
		array(
			'p1' => array( 'زیر ۱ میلیون', 0, 1000000 ),
			'p2' => array( '۱ تا ۳ میلیون', 1000000, 3000000 ),
			'p3' => array( '۳ تا ۵ میلیون', 3000000, 5000000 ),
			'p4' => array( 'بالای ۵ میلیون', 5000000, PHP_INT_MAX ),
		)
	);
}

/** Days a product counts as «جدید» (cards use the same rule). */
function salimor_new_days() {
	return (int) apply_filters( 'salimor_new_days', 30 );
}

/** Attribute taxonomies offered as filters: [ 'color' => [ 'tax' => 'pa_color', 'label' => 'رنگ' ], … ]. */
function salimor_filter_attributes() {
	static $out = null;
	if ( null !== $out ) {
		return $out;
	}
	$out      = array();
	$reserved = array( 'price', 'sale', 'new', 'stock', 'orderby', 's', 'post_type', 'paged', 'page', 'product_cat', 'p' );
	foreach ( wc_get_attribute_taxonomies() as $a ) {
		$param         = in_array( $a->attribute_name, $reserved, true ) ? 'a_' . $a->attribute_name : $a->attribute_name;
		$out[ $param ] = array( 'tax' => wc_attribute_taxonomy_name( $a->attribute_name ), 'label' => $a->attribute_label );
	}
	return $out;
}

/** Is this request a product listing the filters apply to? */
function salimor_is_listing() {
	return function_exists( 'is_shop' ) && ( is_shop() || is_product_category() || is_product_tag() || ( is_search() && 'product' === get_query_var( 'post_type' ) ) );
}

/** Read one list parameter: accepts «a,b» and «a[]=…». */
function salimor_param_list( $key ) {
	if ( ! isset( $_GET[ $key ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		return array();
	}
	$v = wp_unslash( $_GET[ $key ] ); // phpcs:ignore
	$v = is_array( $v ) ? $v : explode( ',', (string) $v );
	return array_values( array_unique( array_filter( array_map( 'sanitize_title', $v ) ) ) );
}

/** The filters chosen in the current URL. */
function salimor_chosen() {
	static $c = null;
	if ( null !== $c ) {
		return $c;
	}
	$ranges = salimor_price_ranges();
	$c      = array(
		'price' => array_values( array_intersect( salimor_param_list( 'price' ), array_keys( $ranges ) ) ),
		'attr'  => array(),
		'sale'  => ! empty( $_GET['sale'] ), // phpcs:ignore
		'new'   => ! empty( $_GET['new'] ),  // phpcs:ignore
		'stock' => ! empty( $_GET['stock'] ), // phpcs:ignore
	);
	foreach ( salimor_filter_attributes() as $param => $a ) {
		$vals = salimor_param_list( $param );
		if ( $vals ) {
			$c['attr'][ $param ] = $vals;
		}
	}
	return $c;
}

function salimor_active_count( $c = null ) {
	$c = $c ?? salimor_chosen();
	return count( $c['price'] ) + array_sum( array_map( 'count', $c['attr'] ) ) + (int) $c['sale'] + (int) $c['new'] + (int) $c['stock'];
}

/** Cache generation: bumped whenever a product, its terms or stock change. */
function salimor_index_gen() {
	return (int) get_option( 'salimor_index_gen', 1 );
}
$salimor_bump = function () {
	update_option( 'salimor_index_gen', salimor_index_gen() + 1, false );
};
foreach ( array( 'woocommerce_update_product', 'woocommerce_new_product', 'woocommerce_delete_product', 'woocommerce_trash_product', 'woocommerce_product_set_stock_status', 'woocommerce_variation_set_stock_status', 'woocommerce_update_product_variation' ) as $h ) {
	add_action( $h, $salimor_bump );
}
add_action( 'set_object_terms', function ( $object_id ) use ( $salimor_bump ) {
	if ( 'product' === get_post_type( $object_id ) ) {
		$salimor_bump();
	}
} );

/**
 * Products of the current listing, before filters: [ id => [ price, terms[param]=>slugs, sale, new, stock ] ].
 */
function salimor_listing_index() {
	static $idx = null;
	if ( null !== $idx ) {
		return $idx;
	}
	$args = array(
		'status'     => 'publish',
		'limit'      => -1,
		'return'     => 'ids',
		'visibility' => is_search() ? 'search' : 'catalog',
	);
	$key  = 'all';
	if ( is_product_category() || is_product_tag() ) {
		$t                                             = get_queried_object();
		$args[ is_product_tag() ? 'tag' : 'category' ] = array( $t->slug );
		$key                                           = $t->taxonomy . $t->term_id;
	}
	if ( is_search() ) {
		$args['s'] = get_search_query( false );
		$key       = '';
	}
	$cache_key = $key ? 'salimor_idx_' . md5( $key . '|' . salimor_index_gen() ) : '';
	if ( $cache_key ) {
		$idx = get_transient( $cache_key );
		if ( is_array( $idx ) ) {
			return $idx;
		}
	}

	$ids = wc_get_products( $args );
	if ( 'yes' === get_option( 'woocommerce_hide_out_of_stock_items' ) ) {
		$args['stock_status'] = array( 'instock', 'onbackorder' );
		$ids                  = wc_get_products( $args );
	}
	update_meta_cache( 'post', $ids );
	$attrs = salimor_filter_attributes();
	update_object_term_cache( $ids, 'product' );
	$on_sale = array_flip( wc_get_product_ids_on_sale() );
	$cutoff  = time() - salimor_new_days() * DAY_IN_SECONDS;
	$idx     = array();
	foreach ( $ids as $id ) {
		$prices = array_filter( array_map( 'floatval', (array) get_post_meta( $id, '_price', false ) ), fn( $x ) => $x > 0 );
		$terms  = array();
		foreach ( $attrs as $param => $a ) {
			$tt = get_the_terms( $id, $a['tax'] );
			if ( $tt && ! is_wp_error( $tt ) ) {
				$terms[ $param ] = wp_list_pluck( $tt, 'slug' );
			}
		}
		$idx[ $id ] = array(
			'price' => $prices ? min( $prices ) : 0,
			'terms' => $terms,
			'sale'  => isset( $on_sale[ $id ] ),
			'new'   => strtotime( get_post_field( 'post_date_gmt', $id ) . ' UTC' ) > $cutoff,
			'stock' => in_array( get_post_meta( $id, '_stock_status', true ), array( 'instock', 'onbackorder' ), true ),
		);
	}
	if ( $cache_key ) {
		set_transient( $cache_key, $idx, DAY_IN_SECONDS );
	}
	return $idx;
}

/** Does one indexed product pass the chosen filters? $skip ignores one group (for that group's counts). */
function salimor_passes( array $row, array $c, $skip = '' ) {
	if ( 'price' !== $skip && $c['price'] ) {
		$ranges = salimor_price_ranges();
		$ok     = false;
		foreach ( $c['price'] as $k ) {
			if ( $row['price'] >= $ranges[ $k ][1] && $row['price'] < $ranges[ $k ][2] ) {
				$ok = true;
				break;
			}
		}
		if ( ! $ok ) {
			return false;
		}
	}
	foreach ( $c['attr'] as $param => $vals ) {
		if ( $param !== $skip && ! array_intersect( $vals, $row['terms'][ $param ] ?? array() ) ) {
			return false;
		}
	}
	foreach ( array( 'sale', 'new', 'stock' ) as $flag ) {
		if ( $flag !== $skip && $c[ $flag ] && ! $row[ $flag ] ) {
			return false;
		}
	}
	return true;
}

/* Narrow the main product query to the matching IDs; WooCommerce keeps sorting and paging. */
add_action( 'woocommerce_product_query', function ( WP_Query $q ) {
	if ( ! salimor_active_count() ) {
		return;
	}
	$c   = salimor_chosen();
	$ids = array();
	foreach ( salimor_listing_index() as $id => $row ) {
		if ( salimor_passes( $row, $c ) ) {
			$ids[] = $id;
		}
	}
	$q->set( 'post__in', $ids ? $ids : array( 0 ) );
} );

/* Defaults: newest first, and no rating sort (the store shows no ratings). */
add_filter( 'woocommerce_default_catalog_orderby', fn( $o ) => 'menu_order' === $o ? 'date' : $o );
add_filter( 'loop_shop_per_page', fn() => 24 );

/** Sort options shown in the shop. */
function salimor_sort_options() {
	return array(
		'date'       => 'جدیدترین',
		'price'      => 'ارزان‌ترین',
		'price-desc' => 'گران‌ترین',
		'popularity' => 'پرفروش‌ترین',
	);
}

/**
 * Filter groups for the panel, each with its options and live counts.
 *
 * @return array[] [ [ 'key', 'label', 'type' => check|toggle, 'options' => [ [ value, label, count, checked, hex ] ] ] ]
 */
function salimor_facets() {
	$idx    = salimor_listing_index();
	$c      = salimor_chosen();
	$count  = function ( $skip, callable $test ) use ( $idx, $c ) {
		$n = 0;
		foreach ( $idx as $row ) {
			if ( $test( $row ) && salimor_passes( $row, $c, $skip ) ) {
				++$n;
			}
		}
		return $n;
	};
	$groups = array();

	$opts = array();
	foreach ( salimor_price_ranges() as $k => list( $label, $min, $max ) ) {
		$n = $count( 'price', fn( $r ) => $r['price'] >= $min && $r['price'] < $max );
		if ( $n || in_array( $k, $c['price'], true ) ) {
			$opts[] = array( $k, $label, $n, in_array( $k, $c['price'], true ), '' );
		}
	}
	if ( count( $opts ) > 1 || $c['price'] ) {
		$groups[] = array( 'key' => 'price', 'label' => 'محدوده‌ی قیمت', 'type' => 'check', 'options' => $opts );
	}

	$color_tax = salimor_color_taxonomy();
	foreach ( salimor_filter_attributes() as $param => $a ) {
		$present = array();
		foreach ( $idx as $row ) {
			foreach ( $row['terms'][ $param ] ?? array() as $slug ) {
				$present[ $slug ] = true;
			}
		}
		if ( ! $present ) {
			continue;
		}
		$terms = get_terms( array( 'taxonomy' => $a['tax'], 'slug' => array_keys( $present ), 'hide_empty' => false, 'orderby' => 'menu_order' ) );
		if ( is_wp_error( $terms ) || count( $terms ) < 2 && empty( $c['attr'][ $param ] ) ) {
			continue;
		}
		$opts = array();
		foreach ( $terms as $t ) {
			$on     = in_array( $t->slug, $c['attr'][ $param ] ?? array(), true );
			$hex    = $a['tax'] === $color_tax ? (string) get_term_meta( $t->term_id, 'salimor_hex', true ) : '';
			$opts[] = array( urldecode( $t->slug ), $t->name, $count( $param, fn( $r ) => in_array( $t->slug, $r['terms'][ $param ] ?? array(), true ) ), $on, $hex );
		}
		$groups[] = array( 'key' => $param, 'label' => $a['label'], 'type' => $a['tax'] === $color_tax ? 'color' : 'check', 'options' => $opts );
	}

	$toggles = array();
	foreach ( array( 'sale' => 'فقط تخفیف‌دارها', 'new' => 'فقط محصولات جدید', 'stock' => 'فقط کالاهای موجود' ) as $flag => $label ) {
		$n = $count( $flag, fn( $r ) => $r[ $flag ] );
		if ( 'stock' === $flag && $n === count( array_filter( $idx, fn( $r ) => salimor_passes( $r, $c, 'stock' ) ) ) && ! $c['stock'] ) {
			continue; // everything shown is in stock: the toggle would do nothing
		}
		if ( $n || $c[ $flag ] ) {
			$toggles[] = array( '1', $label, $n, $c[ $flag ], $flag );
		}
	}
	if ( $toggles ) {
		$groups[] = array( 'key' => 'flags', 'label' => 'پیشنهادها', 'type' => 'toggle', 'options' => $toggles );
	}
	return $groups;
}

/** Removable chips for the active filters: [ [ param, value, label ] ]. */
function salimor_active_chips() {
	$c      = salimor_chosen();
	$chips  = array();
	$ranges = salimor_price_ranges();
	foreach ( $c['price'] as $k ) {
		$chips[] = array( 'price', $k, $ranges[ $k ][0] );
	}
	$attrs = salimor_filter_attributes();
	foreach ( $c['attr'] as $param => $vals ) {
		foreach ( $vals as $slug ) {
			$t       = get_term_by( 'slug', $slug, $attrs[ $param ]['tax'] );
			$chips[] = array( $param, urldecode( $slug ), ( $t ? $t->name : urldecode( $slug ) ) );
		}
	}
	foreach ( array( 'sale' => 'تخفیف‌دار', 'new' => 'جدید', 'stock' => 'موجود' ) as $flag => $label ) {
		if ( $c[ $flag ] ) {
			$chips[] = array( $flag, '1', $label );
		}
	}
	return $chips;
}

/** Current listing URL without any filter (keeps search and sort). */
function salimor_listing_base_url() {
	if ( is_product_category() || is_product_tag() ) {
		$url = get_term_link( get_queried_object() );
	} elseif ( is_search() ) {
		$url = add_query_arg( array( 's' => get_search_query( false ), 'post_type' => 'product' ), home_url( '/' ) );
	} else {
		$url = salimor_shop_url();
	}
	if ( ! empty( $_GET['orderby'] ) ) { // phpcs:ignore
		$url = add_query_arg( 'orderby', sanitize_key( wp_unslash( $_GET['orderby'] ) ), $url ); // phpcs:ignore
	}
	return $url;
}

/** URL with one filter value removed (for chips without JavaScript). */
function salimor_url_without( $param, $value ) {
	$c    = salimor_chosen();
	$args = array();
	if ( $c['price'] ) {
		$args['price'] = implode( ',', $c['price'] );
	}
	foreach ( $c['attr'] as $p => $vals ) {
		$args[ $p ] = implode( ',', array_map( 'urldecode', $vals ) );
	}
	foreach ( array( 'sale', 'new', 'stock' ) as $f ) {
		if ( $c[ $f ] ) {
			$args[ $f ] = '1';
		}
	}
	if ( isset( $args[ $param ] ) ) {
		$left = array_diff( explode( ',', $args[ $param ] ), array( $value ) );
		if ( $left && ! in_array( $param, array( 'sale', 'new', 'stock' ), true ) ) {
			$args[ $param ] = implode( ',', $left );
		} else {
			unset( $args[ $param ] );
		}
	}
	return add_query_arg( array_map( 'rawurlencode', $args ), salimor_listing_base_url() );
}

/** The current top-level category (for the sticky category bar). */
function salimor_current_top_cat() {
	if ( ! is_product_category() ) {
		return 0;
	}
	$t   = get_queried_object();
	$anc = get_ancestors( $t->term_id, 'product_cat' );
	return $anc ? (int) end( $anc ) : (int) $t->term_id;
}
