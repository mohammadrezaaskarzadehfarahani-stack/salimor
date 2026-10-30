<?php
/**
 * Payment methods as selectable cards. The «پرداخت» button lives in the order summary.
 *
 * @package Salimor
 * @version 10.9.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! wp_doing_ajax() ) {
	do_action( 'woocommerce_review_order_before_payment' );
}
?>
<div id="payment" class="woocommerce-checkout-payment">
	<?php if ( WC()->cart && WC()->cart->needs_payment() ) : ?>
		<ul class="wc_payment_methods payment_methods methods opts" aria-label="روش پرداخت">
			<?php
			if ( ! empty( $available_gateways ) ) {
				foreach ( $available_gateways as $gateway ) {
					wc_get_template( 'checkout/payment-method.php', array( 'gateway' => $gateway ) );
				}
			} else {
				echo '<li class="box note">هنوز روش پرداختی فعال نشده است. لطفاً با پشتیبانی تماس بگیرید.</li>';
			}
			?>
		</ul>
	<?php else : ?>
		<p class="muted">این سفارش نیازی به پرداخت ندارد.</p>
	<?php endif; ?>
	<noscript><button type="submit" class="btn b-line" name="woocommerce_checkout_update_totals" value="به‌روزرسانی">به‌روزرسانی مبلغ</button></noscript>
</div>
<?php
if ( ! wp_doing_ajax() ) {
	do_action( 'woocommerce_review_order_after_payment' );
}
