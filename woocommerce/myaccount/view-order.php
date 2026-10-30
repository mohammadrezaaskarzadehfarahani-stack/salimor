<?php
/**
 * One order: timeline, tracking, items, delivery and payment.
 *
 * @package Salimor
 * @version 10.6.0
 */

defined( 'ABSPATH' ) || exit;

$step     = salimor_order_step( $order );
$tracking = $order->get_meta( '_salimor_tracking' );
$ship     = $order->get_shipping_methods();
$ship     = $ship ? salimor_split_label( reset( $ship )->get_name() )[0] : '';
$addr     = array( 'state' => $order->get_billing_state(), 'city' => $order->get_billing_city(), 'address_1' => $order->get_billing_address_1() );
$notes    = $order->get_customer_order_notes();
?>
<h1>سفارش <span class="num"><?php echo esc_html( salimor_fa( $order->get_order_number() ) ); ?></span></h1>
<p class="muted">ثبت‌شده در <?php echo esc_html( salimor_fa( wc_format_datetime( $order->get_date_created(), 'j F Y' ) ) ); ?> · <?php echo salimor_status_chip( $order ); // phpcs:ignore ?></p>

<?php if ( $step >= 0 ) : ?>
	<ol class="stepper">
		<?php foreach ( salimor_order_steps() as $i => $s ) : ?>
			<li class="<?php echo $i < $step ? 'done' : ( $i === $step ? 'cur' : '' ); ?>" <?php echo $i === $step ? 'aria-current="step"' : ''; ?>><i><?php echo $i < $step ? salimor_icon( 'check' ) : esc_html( salimor_fa( $i + 1 ) ); // phpcs:ignore ?></i><span><?php echo esc_html( $s ); ?></span></li>
		<?php endforeach; ?>
	</ol>
<?php endif; ?>
<?php if ( $tracking ) : ?>
	<div class="box note">کد رهگیری<?php echo $ship ? ' ' . esc_html( $ship ) : ''; ?>: <b class="num" style="direction:ltr;unicode-bidi:isolate"><?php echo esc_html( $tracking ); ?></b></div>
<?php endif; ?>
<?php if ( $order->needs_payment() ) : ?>
	<div class="box note">این سفارش هنوز پرداخت نشده است. <a href="<?php echo esc_url( $order->get_checkout_payment_url() ); ?>">پرداخت سفارش</a></div>
<?php endif; ?>

<div class="od-grid">
	<section class="box"><h2 class="sub-t">کالاها</h2>
		<?php
		foreach ( $order->get_items() as $item ) :
			$p    = $item->get_product();
			$meta = wp_strip_all_tags( wc_display_item_meta( $item, array( 'echo' => false, 'before' => '', 'after' => '', 'separator' => ' · ', 'label_before' => '', 'label_after' => ': ' ) ) );
			?>
			<div class="li">
				<span class="pic"><?php echo $p ? $p->get_image( 'thumbnail', array( 'alt' => '' ) ) : ''; // phpcs:ignore ?></span>
				<div><?php if ( $p && $p->is_visible() ) : ?><a href="<?php echo esc_url( $p->get_permalink() ); ?>"><b><?php echo esc_html( $item->get_name() ); ?></b></a><?php else : ?><b><?php echo esc_html( $item->get_name() ); ?></b><?php endif; ?>
					<small><?php echo esc_html( ( $meta ? $meta . ' · ' : '' ) . salimor_fa( $item->get_quantity() ) . ' عدد' ); ?></small></div>
				<span class="price num"><?php echo salimor_num( $order->get_line_subtotal( $item, true ) ); // phpcs:ignore ?><small>تومان</small></span>
			</div>
		<?php endforeach; ?>
	</section>
	<section class="box"><h2 class="sub-t">ارسال و پرداخت</h2>
		<dl class="kv">
			<?php if ( $ship ) : ?><dt>روش ارسال</dt><dd><?php echo esc_html( $ship ); ?></dd><?php endif; ?>
			<dt>تحویل به</dt><dd><?php echo esc_html( trim( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() ) . '، ' . salimor_address_line( $addr ) ); ?></dd>
			<dt>جمع کالاها</dt><dd class="num"><?php echo salimor_num( $order->get_subtotal() ); // phpcs:ignore ?> تومان</dd>
			<?php if ( $order->get_total_discount() ) : ?><dt>تخفیف</dt><dd class="num">−<?php echo salimor_num( $order->get_total_discount() ); // phpcs:ignore ?> تومان</dd><?php endif; ?>
			<dt>هزینه‌ی ارسال</dt><dd><?php echo (float) $order->get_shipping_total() > 0 ? salimor_num( $order->get_shipping_total() ) . ' تومان' : 'رایگان'; // phpcs:ignore ?></dd>
			<dt><?php echo $order->is_paid() ? 'پرداخت‌شده' : 'مبلغ سفارش'; ?></dt><dd class="num"><b><?php echo salimor_num( $order->get_total() ); // phpcs:ignore ?> تومان</b></dd>
			<?php if ( $order->get_payment_method_title() ) : ?><dt>روش پرداخت</dt><dd><?php echo esc_html( $order->get_payment_method_title() ); ?></dd><?php endif; ?>
		</dl>
		<?php if ( $order->has_status( 'completed' ) && get_page_by_path( 'contact' ) ) : ?>
			<a class="btn b-line wide" href="<?php echo esc_url( add_query_arg( 'order', $order->get_order_number(), get_permalink( get_page_by_path( 'contact' ) ) ) ); ?>">درخواست بازگشت یا تعویض</a>
		<?php endif; ?>
	</section>
</div>
<?php if ( $notes ) : ?>
	<section class="box"><h2 class="sub-t">پیام‌های فروشگاه</h2>
		<?php foreach ( $notes as $note ) : ?>
			<div class="note-i"><small class="num"><?php echo esc_html( salimor_fa( date_i18n( 'j F Y، H:i', strtotime( $note->comment_date ) ) ) ); ?></small><?php echo wp_kses_post( wpautop( $note->comment_content ) ); ?></div>
		<?php endforeach; ?>
	</section>
<?php endif; ?>
<?php do_action( 'woocommerce_view_order', $order_id ); ?>
