<?php
/**
 * Order summary in the checkout aside (refreshed by WooCommerce when address/shipping/coupons change).
 *
 * @package Salimor
 * @version 11.2.0
 */

defined( 'ABSPATH' ) || exit;

$cart = WC()->cart;
?>
<div class="woocommerce-checkout-review-order-table ck-review">
	<?php do_action( 'woocommerce_review_order_before_cart_contents' ); ?>
	<?php
	foreach ( $cart->get_cart() as $key => $item ) :
		$p = apply_filters( 'woocommerce_cart_item_product', $item['data'], $item, $key );
		if ( ! $p instanceof WC_Product || ! $p->exists() || $item['quantity'] <= 0 || ! apply_filters( 'woocommerce_checkout_cart_item_visible', true, $item, $key ) ) {
			continue;
		}
		$meta = trim( wp_strip_all_tags( wc_get_formatted_cart_item_data( $item, true ) ) );
		?>
		<div class="li cart_item">
			<span class="pic"><?php echo $p->get_image( 'thumbnail', array( 'alt' => '' ) ); // phpcs:ignore ?></span>
			<div><b><?php echo esc_html( $p->get_name() ); ?></b><small><?php echo esc_html( ( $meta ? str_replace( "\n", ' · ', $meta ) . ' · ' : '' ) . salimor_fa( $item['quantity'] ) . ' عدد' ); ?></small></div>
			<span class="price num"><?php echo salimor_num( $item['line_subtotal'] + $item['line_subtotal_tax'] ); // phpcs:ignore ?><small>تومان</small></span>
		</div>
	<?php endforeach; ?>
	<?php do_action( 'woocommerce_review_order_after_cart_contents' ); ?>

	<div class="sum-row"><span>جمع کالاها</span><span class="num"><?php echo salimor_num( $cart->get_subtotal() + $cart->get_subtotal_tax() ); // phpcs:ignore ?> تومان</span></div>
	<?php foreach ( $cart->get_coupons() as $code => $coupon ) : ?>
		<div class="sum-row disc"><span>کد تخفیف <span class="ltr-i"><?php echo esc_html( $code ); ?></span> <a href="<?php echo esc_url( add_query_arg( 'remove_coupon', rawurlencode( $code ), wc_get_checkout_url() ) ); ?>" class="woocommerce-remove-coupon" data-coupon="<?php echo esc_attr( $code ); ?>">حذف</a></span><span class="num">−<?php echo salimor_num( $cart->get_coupon_discount_amount( $code, $cart->display_cart_ex_tax ) ); // phpcs:ignore ?> تومان</span></div>
	<?php endforeach; ?>
	<?php if ( $cart->needs_shipping() ) : ?>
		<?php
		$methods = array();
		foreach ( WC()->shipping()->get_packages() as $i => $pkg ) {
			$chosen = WC()->session->get( 'chosen_shipping_methods' )[ $i ] ?? '';
			if ( isset( $pkg['rates'][ $chosen ] ) ) {
				$methods[] = salimor_split_label( $pkg['rates'][ $chosen ]->get_label() )[0];
			}
		}
		$ship = (float) $cart->get_shipping_total() + (float) $cart->get_shipping_tax();
		?>
		<div class="sum-row"><span>ارسال<?php echo $methods ? ' (' . esc_html( implode( '، ', $methods ) ) . ')' : ''; ?></span><span class="num"><?php echo $methods ? ( $ship > 0 ? salimor_num( $ship ) . ' تومان' : 'رایگان' ) : 'پس از انتخاب آدرس'; // phpcs:ignore ?></span></div>
	<?php endif; ?>
	<?php foreach ( $cart->get_fees() as $fee ) : ?>
		<div class="sum-row"><span><?php echo esc_html( $fee->name ); ?></span><span class="num"><?php echo salimor_num( $fee->total + $fee->tax ); // phpcs:ignore ?> تومان</span></div>
	<?php endforeach; ?>
	<?php if ( wc_tax_enabled() && ! $cart->display_prices_including_tax() ) : ?>
		<div class="sum-row"><span>مالیات بر ارزش افزوده</span><span class="num"><?php echo salimor_num( $cart->get_taxes_total() ); // phpcs:ignore ?> تومان</span></div>
	<?php endif; ?>
	<?php do_action( 'woocommerce_review_order_before_order_total' ); ?>
	<div class="tot order-total"><span>مبلغ قابل پرداخت</span><span class="num"><?php echo salimor_num( $cart->get_total( 'edit' ) ); // phpcs:ignore ?> تومان</span></div>
	<?php do_action( 'woocommerce_review_order_after_order_total' ); ?>
</div>
