<?php
/**
 * WooCommerce integration: product card, prices, cart drawer, AJAX cart, wishlist, colour swatches.
 *
 * @package Salimor
 */

defined( 'ABSPATH' ) || exit;

/* ---------------------------------------------------------------------------
 * Prices
 * ------------------------------------------------------------------------- */

/** 1890000 → «۱٬۸۹۰٬۰۰۰». */
function salimor_num( $amount ) {
	return salimor_fa( number_format( (float) $amount, 0, '', '٬' ) );
}

/** Card/drawer price block: optional struck-through regular price, current price, «تومان». */
function salimor_price_html( WC_Product $p ) {
	$prefix = '';
	if ( $p->is_type( 'variable' ) ) {
		$min     = (float) $p->get_variation_price( 'min', true );
		$max     = (float) $p->get_variation_price( 'max', true );
		$regular = (float) $p->get_variation_regular_price( 'min', true );
		$price   = $min;
		$prefix  = $min !== $max ? 'از ' : '';
	} else {
		$price   = (float) wc_get_price_to_display( $p );
		$regular = (float) wc_get_price_to_display( $p, array( 'price' => $p->get_regular_price() ) );
	}
	if ( ! $price && ! $regular ) {
		return '<span class="price">تماس بگیرید</span>';
	}
	$old = ( $p->is_on_sale() && $regular > $price ) ? '<s>' . salimor_num( $regular ) . '</s>' : '';
	return sprintf( '<span class="price num">%s%s%s<small>تومان</small></span>', $old, esc_html( $prefix ), salimor_num( $price ) );
}

/** Whole-percent discount for on-sale products, else 0. */
function salimor_discount( WC_Product $p ) {
	if ( ! $p->is_on_sale() ) {
		return 0;
	}
	$reg  = (float) ( $p->is_type( 'variable' ) ? $p->get_variation_regular_price( 'max' ) : $p->get_regular_price() );
	$sale = (float) ( $p->is_type( 'variable' ) ? $p->get_variation_sale_price( 'min' ) : $p->get_sale_price() );
	return ( $reg > 0 && $sale > 0 ) ? (int) round( ( 1 - $sale / $reg ) * 100 ) : 0;
}

/* ---------------------------------------------------------------------------
 * Colour swatches: hex code stored on «رنگ» (pa_color) attribute terms
 * ------------------------------------------------------------------------- */

function salimor_color_taxonomy() {
	return apply_filters( 'salimor_color_taxonomy', 'pa_color' );
}

add_action( 'init', function () {
	$tax = salimor_color_taxonomy();
	add_action( "{$tax}_add_form_fields", function () {
		echo '<div class="form-field"><label for="salimor_hex">کد رنگ</label><input type="color" name="salimor_hex" id="salimor_hex" value="#cccccc"><p>این رنگ در دایره‌های انتخاب رنگ نمایش داده می‌شود.</p></div>';
	} );
	add_action( "{$tax}_edit_form_fields", function ( $term ) {
		$hex = get_term_meta( $term->term_id, 'salimor_hex', true ) ?: '#cccccc';
		printf( '<tr class="form-field"><th><label for="salimor_hex">کد رنگ</label></th><td><input type="color" name="salimor_hex" id="salimor_hex" value="%s"></td></tr>', esc_attr( $hex ) );
	} );
	$save = function ( $term_id ) {
		if ( isset( $_POST['salimor_hex'] ) && current_user_can( 'manage_product_terms' ) ) { // phpcs:ignore WordPress.Security.NonceVerification -- core term screens verify their own nonce.
			update_term_meta( $term_id, 'salimor_hex', sanitize_hex_color( wp_unslash( $_POST['salimor_hex'] ) ) ); // phpcs:ignore
		}
	};
	add_action( "created_{$tax}", $save );
	add_action( "edited_{$tax}", $save );
}, 20 );

/** Colours of a product as [ [name, hex], … ]. */
function salimor_product_colors( WC_Product $p ) {
	$tax = salimor_color_taxonomy();
	if ( ! taxonomy_exists( $tax ) ) {
		return array();
	}
	$out = array();
	foreach ( wc_get_product_terms( $p->get_id(), $tax, array( 'fields' => 'all' ) ) as $t ) {
		$out[] = array( $t->name, get_term_meta( $t->term_id, 'salimor_hex', true ) ?: '#cccccc' );
	}
	return $out;
}

/* ---------------------------------------------------------------------------
 * Wishlist: user meta for members, cookie for guests (merged on login)
 * ------------------------------------------------------------------------- */

function salimor_wishlist() {
	if ( is_user_logged_in() ) {
		return array_map( 'intval', (array) get_user_meta( get_current_user_id(), '_salimor_wishlist', true ) ?: array() );
	}
	$c = isset( $_COOKIE['salimor_wish'] ) ? sanitize_text_field( wp_unslash( $_COOKIE['salimor_wish'] ) ) : '';
	return array_values( array_filter( array_map( 'intval', explode( '.', $c ) ) ) );
}

function salimor_wish_ajax() {
	check_ajax_referer( 'salimor-cart', 'nonce' );
	$id   = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
	$list = salimor_wishlist();
	$on   = ! in_array( $id, $list, true );
	$list = $on ? array_merge( $list, array( $id ) ) : array_values( array_diff( $list, array( $id ) ) );
	if ( is_user_logged_in() ) {
		update_user_meta( get_current_user_id(), '_salimor_wishlist', $list );
	} else {
		wc_setcookie( 'salimor_wish', implode( '.', $list ), time() + YEAR_IN_SECONDS );
	}
	wp_send_json_success( array( 'on' => $on, 'count' => count( $list ) ) );
}
add_action( 'wc_ajax_salimor_wish', 'salimor_wish_ajax' );

add_action( 'wp_login', function ( $login, $user ) {
	if ( empty( $_COOKIE['salimor_wish'] ) ) {
		return;
	}
	$guest = array_filter( array_map( 'intval', explode( '.', sanitize_text_field( wp_unslash( $_COOKIE['salimor_wish'] ) ) ) ) );
	$saved = (array) get_user_meta( $user->ID, '_salimor_wishlist', true );
	update_user_meta( $user->ID, '_salimor_wishlist', array_values( array_unique( array_merge( $saved, $guest ) ) ) );
	wc_setcookie( 'salimor_wish', '', time() - HOUR_IN_SECONDS );
}, 10, 2 );

/* ---------------------------------------------------------------------------
 * Product card
 * ------------------------------------------------------------------------- */

/** Render one product card (same markup as the approved design). */
function salimor_product_card( $product ) {
	$p = $product instanceof WC_Product ? $product : wc_get_product( $product );
	if ( ! $p || ! $p->is_visible() ) {
		return;
	}
	$id     = $p->get_id();
	$name   = $p->get_name();
	$url    = $p->get_permalink();
	$cats   = wc_get_product_terms( $id, 'product_cat', array( 'fields' => 'names' ) );
	$parent = $cats ? $cats[0] : '';
	$top    = get_the_terms( $id, 'product_cat' );
	$disc   = salimor_discount( $p );
	$is_new = ( time() - (int) $p->get_date_created()?->getTimestamp() ) < 30 * DAY_IN_SECONDS;
	$wished = in_array( $id, salimor_wishlist(), true );
	$colors = salimor_product_colors( $p );
	$simple = $p->is_type( 'simple' ) && $p->is_purchasable() && $p->is_in_stock();
	$top_slug = '';
	if ( $top && ! is_wp_error( $top ) ) {
		$t        = $top[0];
		$anc      = get_ancestors( $t->term_id, 'product_cat' );
		$top_slug = $anc ? get_term( end( $anc ), 'product_cat' )->slug : $t->slug;
	}
	?>
	<article class="pc" data-cat="<?php echo esc_attr( $top_slug ); ?>">
		<div class="pic">
			<?php if ( $disc ) : ?>
				<span class="badge sale num"><?php echo esc_html( salimor_fa( $disc ) . '٪ تخفیف' ); ?></span>
			<?php elseif ( ! $p->is_in_stock() ) : ?>
				<span class="badge out">ناموجود</span>
			<?php elseif ( $is_new ) : ?>
				<span class="badge">جدید</span>
			<?php endif; ?>
			<button class="heart" type="button" data-wish="<?php echo esc_attr( $id ); ?>" aria-pressed="<?php echo $wished ? 'true' : 'false'; ?>" aria-label="<?php echo esc_attr( 'علاقه‌مندی ' . $name ); ?>"><?php echo salimor_icon( 'heart' ); // phpcs:ignore ?></button>
			<?php echo $p->get_image( 'salimor-card', array( 'class' => 'pc-img', 'loading' => 'lazy', 'alt' => $name ) ); // phpcs:ignore ?>
			<?php if ( $simple ) : ?>
				<button class="plus" type="button" data-add="<?php echo esc_attr( $id ); ?>" aria-label="<?php echo esc_attr( 'افزودن ' . $name . ' به سبد' ); ?>"><?php echo salimor_icon( 'plus' ); // phpcs:ignore ?></button>
			<?php elseif ( $p->is_in_stock() ) : ?>
				<a class="plus" href="<?php echo esc_url( $url ); ?>" aria-label="<?php echo esc_attr( 'انتخاب گزینه‌های ' . $name ); ?>" tabindex="-1"><?php echo salimor_icon( 'plus' ); // phpcs:ignore ?></a>
			<?php endif; ?>
		</div>
		<div>
			<h3><a class="stretch" href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $name ); ?></a></h3>
			<div class="sub"><span class="cat"><?php echo esc_html( $parent ); ?></span><?php echo salimor_price_html( $p ); // phpcs:ignore ?></div>
			<?php if ( $colors ) : ?>
				<div class="sws" role="group" aria-label="رنگ‌ها">
					<?php foreach ( $colors as $i => list( $cname, $hex ) ) : ?>
						<span class="sw" title="<?php echo esc_attr( $cname ); ?>"><i style="background:<?php echo esc_attr( $hex ); ?>"></i><span class="sr"><?php echo esc_html( $cname ); ?></span></span>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
	</article>
	<?php
}

/* Use the same card in every WooCommerce loop (shop, category, related products…). */
add_filter( 'woocommerce_product_loop_start', fn() => '<div class="grid g4 wc-grid">' );
add_filter( 'woocommerce_product_loop_end', fn() => '</div>' );

/* ---------------------------------------------------------------------------
 * Queries for the home shelves
 * ------------------------------------------------------------------------- */

function salimor_products( $args ) {
	return wc_get_products( array_merge( array( 'status' => 'publish', 'visibility' => 'catalog', 'limit' => 8 ), $args ) );
}

function salimor_bestsellers( $limit = 8 ) {
	$ids = get_posts( array(
		'post_type'      => 'product',
		'post_status'    => 'publish',
		'posts_per_page' => $limit,
		'fields'         => 'ids',
		'meta_key'       => 'total_sales', // phpcs:ignore WordPress.DB.SlowDBQuery
		'orderby'        => array( 'meta_value_num' => 'DESC', 'date' => 'DESC' ),
		'tax_query'      => WC()->query->get_tax_query(), // phpcs:ignore WordPress.DB.SlowDBQuery
	) );
	return array_filter( array_map( 'wc_get_product', $ids ) );
}

/** On-sale products, biggest discount first, and the latest scheduled sale end (timestamp or 0). */
function salimor_on_sale( $limit = 8 ) {
	$ids  = wc_get_product_ids_on_sale();
	$list = array_filter( array_map( 'wc_get_product', array_slice( $ids, 0, 60 ) ), fn( $p ) => $p && $p->is_visible() && 'publish' === $p->get_status() );
	usort( $list, fn( $a, $b ) => salimor_discount( $b ) <=> salimor_discount( $a ) );
	$end = 0;
	foreach ( $list as $p ) {
		$to = $p->get_date_on_sale_to();
		if ( $to ) {
			$end = max( $end, $to->getTimestamp() );
		}
	}
	return array( array_slice( $list, 0, $limit ), $end > time() ? $end : 0, count( $list ) );
}

/** Top-level product categories (without «Uncategorized»). */
function salimor_top_categories( $limit = 3 ) {
	$terms = get_terms( array(
		'taxonomy'   => 'product_cat',
		'parent'     => 0,
		'hide_empty' => true,
		'exclude'    => array( (int) get_option( 'default_product_cat' ) ),
		'orderby'    => 'meta_value_num',
		'meta_key'   => 'order', // phpcs:ignore WordPress.DB.SlowDBQuery
		'number'     => $limit,
	) );
	return is_wp_error( $terms ) ? array() : $terms;
}

/** Category image: its own thumbnail, else the newest product's image. */
function salimor_term_image( WP_Term $term, $size = 'salimor-card' ) {
	$thumb = (int) get_term_meta( $term->term_id, 'thumbnail_id', true );
	if ( $thumb ) {
		return wp_get_attachment_image( $thumb, $size, false, array( 'alt' => '', 'loading' => 'lazy' ) );
	}
	$p = wc_get_products( array( 'category' => array( $term->slug ), 'limit' => 1, 'status' => 'publish' ) );
	return $p ? $p[0]->get_image( $size, array( 'alt' => '', 'loading' => 'lazy' ) ) : '';
}

/* ---------------------------------------------------------------------------
 * Cart drawer (bottom sheet on phones, side panel on desktop)
 * ------------------------------------------------------------------------- */

function salimor_cart_count() {
	return WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
}

/** Inner markup of the drawer: items (#cb) and totals/actions (#cf). */
function salimor_cart_drawer_inner() {
	$cart = WC()->cart;
	ob_start();
	if ( ! $cart || $cart->is_empty() ) {
		?>
		<div class="sh-b" id="cb"><div class="empty"><?php echo salimor_icon( 'bag' ); // phpcs:ignore ?><p style="color:var(--ink);font-weight:600">سبد خرید خالی است</p><p style="font-size:14px">چند قطعه از کالکشن پاییز را ببینید.</p></div></div>
		<div class="sh-f" id="cf"><a class="btn b-ink" href="<?php echo esc_url( salimor_shop_url() ); ?>">رفتن به فروشگاه</a></div>
		<?php
		return ob_get_clean();
	}
	$sum  = (float) $cart->get_subtotal() + (float) $cart->get_subtotal_tax();
	$min  = (int) salimor_opt( 'free_ship_min' );
	$left = $min ? max( 0, $min - $sum ) : 0;
	?>
	<div class="sh-b" id="cb">
		<?php if ( $min ) : ?>
			<div class="ship"><?php echo $left ? 'فقط <b class="num" style="color:var(--ink)">' . salimor_num( $left ) . '</b> تومان تا ارسال رایگان' : 'ارسال این سفارش رایگان است'; // phpcs:ignore ?><div class="tr"><i style="width:<?php echo esc_attr( min( 100, round( $sum / $min * 100 ) ) ); ?>%"></i></div></div>
		<?php endif; ?>
		<?php foreach ( $cart->get_cart() as $key => $item ) :
			$p = $item['data'];
			if ( ! $p || ! $p->exists() ) {
				continue;
			}
			$opts = wc_get_formatted_cart_item_data( $item, true );
			?>
			<div class="ci">
				<a class="pic" href="<?php echo esc_url( $p->get_permalink() ); ?>" tabindex="-1"><?php echo $p->get_image( 'thumbnail', array( 'alt' => '' ) ); // phpcs:ignore ?></a>
				<div style="min-width:0">
					<h4><?php echo esc_html( $p->get_name() ); ?></h4>
					<?php if ( trim( $opts ) ) : ?><small><?php echo esc_html( str_replace( "\n", ' · ', trim( $opts ) ) ); ?></small><?php endif; ?>
					<div class="mq"><button type="button" data-q="<?php echo esc_attr( $key ); ?>" data-d="-1" aria-label="کم کردن">−</button><span class="num"><?php echo esc_html( salimor_fa( $item['quantity'] ) ); ?></span><button type="button" data-q="<?php echo esc_attr( $key ); ?>" data-d="1" aria-label="اضافه کردن">+</button></div>
				</div>
				<div style="text-align:left"><span class="price num"><?php echo salimor_num( $item['line_subtotal'] + $item['line_subtotal_tax'] ); // phpcs:ignore ?><small>تومان</small></span><br><button type="button" class="rm" data-rm="<?php echo esc_attr( $key ); ?>">حذف</button></div>
			</div>
		<?php endforeach; ?>
	</div>
	<div class="sh-f" id="cf">
		<div class="sum-row"><span>جمع کالاها</span><span class="num"><?php echo salimor_num( $sum ); // phpcs:ignore ?> تومان</span></div>
		<div class="sum-row"><span>هزینه ارسال</span><span><?php echo $min && ! $left ? 'رایگان' : 'در مرحله‌ی بعد محاسبه می‌شود'; ?></span></div>
		<a class="btn b-ink" href="<?php echo esc_url( wc_get_checkout_url() ); ?>">ادامه و پرداخت</a>
		<p class="pay-note">پرداخت امن با همه‌ی کارت‌های عضو شتاب</p>
	</div>
	<?php
	return ob_get_clean();
}

/* Fragments refreshed after every cart change (consumed by assets/js/main.js, no jQuery needed). */
add_filter( 'woocommerce_add_to_cart_fragments', function ( $fragments ) {
	$n                             = salimor_cart_count();
	$fragments['salimor_drawer']   = salimor_cart_drawer_inner();
	$fragments['salimor_count']    = $n;
	return $fragments;
} );

/* Update / remove a cart line from the drawer. */
function salimor_update_qty_ajax() {
	check_ajax_referer( 'salimor-cart', 'nonce' );
	$key = isset( $_POST['key'] ) ? wc_clean( wp_unslash( $_POST['key'] ) ) : '';
	$qty = isset( $_POST['qty'] ) ? max( 0, (int) $_POST['qty'] ) : 0;
	$item = WC()->cart->get_cart_item( $key );
	if ( ! $item ) {
		wp_send_json_error( array( 'message' => 'این کالا دیگر در سبد نیست.' ), 404 );
	}
	if ( $qty ) {
		WC()->cart->set_quantity( $key, $qty, true );
	} else {
		WC()->cart->remove_cart_item( $key );
	}
	WC_AJAX::get_refreshed_fragments();
}
add_action( 'wc_ajax_salimor_update_qty', 'salimor_update_qty_ajax' );

/* We render our own drawer; WooCommerce's jQuery fragment script is not needed. */
add_action( 'wp_enqueue_scripts', function () {
	wp_dequeue_script( 'wc-cart-fragments' );
}, 100 );

/* Basic wrappers so WooCommerce pages sit inside the theme layout until they get custom templates. */
remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
add_action( 'woocommerce_before_main_content', fn() => print( '<div class="wrap wc-wrap">' ), 10 );
add_action( 'woocommerce_after_main_content', fn() => print( '</div>' ), 10 );
remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );

/* The shop template prints notices, count, sort and pagination itself. */
remove_action( 'woocommerce_before_shop_loop', 'woocommerce_output_all_notices', 10 );
remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );
remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );
remove_action( 'woocommerce_after_shop_loop', 'woocommerce_pagination', 10 );

require_once __DIR__ . '/shop.php';
require_once __DIR__ . '/product.php';
