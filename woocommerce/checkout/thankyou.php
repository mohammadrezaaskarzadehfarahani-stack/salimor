<?php
/**
 * Order received.
 *
 * @package Salimor
 * @version 11.2.0
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="info narrow">
<?php if ( $order ) : ?>
	<?php do_action( 'woocommerce_before_thankyou', $order->get_id() ); ?>
	<?php if ( $order->has_status( 'failed' ) ) : ?>
		<div class="done-card box nf">
			<span class="done-ic fail" aria-hidden="true"><?php echo salimor_icon( 'close' ); // phpcs:ignore ?></span>
			<h1>پرداخت انجام نشد</h1>
			<p class="muted">سفارش <b class="num"><?php echo esc_html( salimor_fa( $order->get_order_number() ) ); ?></b> ثبت شده اما پرداخت آن کامل نشد. اگر مبلغی از حسابتان کم شده، تا ۷۲ ساعت به حسابتان برمی‌گردد.</p>
			<div class="row">
				<a class="btn b-ink" href="<?php echo esc_url( $order->get_checkout_payment_url() ); ?>">پرداخت دوباره</a>
				<?php if ( is_user_logged_in() ) : ?><a class="btn b-line" href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>">حساب کاربری</a><?php endif; ?>
			</div>
		</div>
	<?php else : ?>
		<div class="done-card box">
			<span class="done-ic" aria-hidden="true"><?php echo salimor_icon( 'check' ); // phpcs:ignore ?></span>
			<h1>سفارش شما ثبت شد</h1>
			<p>شماره‌ی سفارش: <b class="num"><?php echo esc_html( salimor_fa( $order->get_order_number() ) ); ?></b></p>
			<p class="muted">
				مبلغ <b class="num"><?php echo salimor_num( $order->get_total() ); // phpcs:ignore ?></b> تومان
				<?php echo $order->is_paid() ? 'پرداخت شد.' : 'ثبت شد.'; ?>
				وقتی سفارش ارسال شود، کد رهگیری را در صفحه‌ی سفارش می‌بینید.
			</p>
			<div class="row">
				<?php if ( is_user_logged_in() && $order->get_user_id() === get_current_user_id() ) : ?>
					<a class="btn b-ink" href="<?php echo esc_url( $order->get_view_order_url() ); ?>">پیگیری سفارش</a>
				<?php endif; ?>
				<a class="btn b-line" href="<?php echo esc_url( salimor_shop_url() ); ?>">ادامه‌ی خرید</a>
			</div>
		</div>
		<div class="thanks-extra">
			<?php do_action( 'woocommerce_thankyou_' . $order->get_payment_method(), $order->get_id() ); ?>
			<?php do_action( 'woocommerce_thankyou', $order->get_id() ); ?>
		</div>
	<?php endif; ?>
<?php else : ?>
	<div class="done-card box">
		<span class="done-ic" aria-hidden="true"><?php echo salimor_icon( 'check' ); // phpcs:ignore ?></span>
		<h1>سفارش شما ثبت شد</h1>
		<div class="row"><a class="btn b-line" href="<?php echo esc_url( salimor_shop_url() ); ?>">ادامه‌ی خرید</a></div>
	</div>
<?php endif; ?>
</div>
