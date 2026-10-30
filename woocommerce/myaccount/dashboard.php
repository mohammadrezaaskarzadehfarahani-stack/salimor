<?php
/**
 * Account dashboard.
 *
 * @package Salimor
 * @version 4.4.0
 */

defined( 'ABSPATH' ) || exit;

$active = salimor_customer_orders( -1, array( 'pending', 'on-hold', 'processing', 'shipped' ) );
$last   = salimor_customer_orders( 1 );
$user   = wp_get_current_user();
$icons  = salimor_account_menu();
?>
<div class="stats">
	<a href="<?php echo esc_url( wc_get_account_endpoint_url( 'orders' ) ); ?>" class="stat"><b class="num"><?php echo esc_html( salimor_fa( count( $active ) ) ); ?></b><span>سفارش در جریان</span></a>
	<a href="<?php echo esc_url( wc_get_account_endpoint_url( 'wishlist' ) ); ?>" class="stat"><b class="num"><?php echo esc_html( salimor_fa( count( salimor_wishlist() ) ) ); ?></b><span>علاقه‌مندی</span></a>
	<a href="<?php echo esc_url( wc_get_account_endpoint_url( 'edit-address' ) ); ?>" class="stat"><b class="num"><?php echo esc_html( salimor_fa( count( salimor_addresses() ) ) ); ?></b><span>آدرس ذخیره‌شده</span></a>
</div>
<?php if ( $last ) : ?>
	<h2 class="sub-t">آخرین سفارش</h2>
	<?php salimor_order_card( $last[0] ); ?>
<?php else : ?>
	<div class="empty box"><?php echo salimor_icon( 'box' ); // phpcs:ignore ?><p>هنوز سفارشی ثبت نکرده‌اید.</p><a class="btn b-ink" href="<?php echo esc_url( salimor_shop_url() ); ?>">شروع خرید</a></div>
<?php endif; ?>
<?php if ( ! $user->first_name ) : ?>
	<div class="box note">برای ارسال سریع‌تر، <a href="<?php echo esc_url( wc_get_account_endpoint_url( 'edit-account' ) ); ?>">نام و اطلاعات حساب</a> را کامل کنید.</div>
<?php endif; ?>
<nav class="acc-menu-m" aria-label="بخش‌های حساب">
	<?php foreach ( wc_get_account_menu_items() as $ep => $label ) : ?>
		<?php if ( 'dashboard' === $ep ) { continue; } ?>
		<?php if ( 'customer-logout' === $ep ) : ?>
			<a class="out" href="<?php echo esc_url( wc_logout_url() ); ?>"><?php echo salimor_icon( 'out' ); // phpcs:ignore ?><span><?php echo esc_html( $label ); ?></span></a>
		<?php else : ?>
			<a href="<?php echo esc_url( wc_get_account_endpoint_url( $ep ) ); ?>"><?php echo salimor_icon( $icons[ $ep ][1] ?? 'arrow' ); // phpcs:ignore ?><span><?php echo esc_html( $label ); ?></span><?php echo salimor_icon( 'arrow' ); // phpcs:ignore ?></a>
		<?php endif; ?>
	<?php endforeach; ?>
</nav>
<?php
do_action( 'woocommerce_account_dashboard' );
