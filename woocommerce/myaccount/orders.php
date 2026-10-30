<?php
/**
 * Orders list.
 *
 * @package Salimor
 * @version 9.5.0
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_account_orders', $has_orders );

if ( $has_orders ) : ?>
	<div class="ord-list">
		<?php
		foreach ( $customer_orders->orders as $customer_order ) {
			$o = wc_get_order( $customer_order );
			if ( $o ) {
				salimor_order_card( $o );
			}
		}
		?>
	</div>
	<?php if ( 1 < $customer_orders->max_num_pages ) : ?>
		<div class="row pager">
			<?php if ( 1 !== $current_page ) : ?><a class="btn b-line" href="<?php echo esc_url( wc_get_endpoint_url( 'orders', $current_page - 1 ) ); ?>">سفارش‌های جدیدتر</a><?php endif; ?>
			<?php if ( intval( $customer_orders->max_num_pages ) !== $current_page ) : ?><a class="btn b-line" href="<?php echo esc_url( wc_get_endpoint_url( 'orders', $current_page + 1 ) ); ?>">سفارش‌های قدیمی‌تر</a><?php endif; ?>
		</div>
	<?php endif; ?>
<?php else : ?>
	<div class="empty box"><?php echo salimor_icon( 'box' ); // phpcs:ignore ?><p>هنوز سفارشی ثبت نکرده‌اید.</p><a class="btn b-ink" href="<?php echo esc_url( salimor_shop_url() ); ?>">شروع خرید</a></div>
<?php endif; ?>
<?php
do_action( 'woocommerce_after_account_orders', $has_orders );
