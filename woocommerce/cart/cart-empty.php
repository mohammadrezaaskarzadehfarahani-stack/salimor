<?php
/**
 * Empty cart.
 *
 * @package Salimor
 * @version 7.0.1
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_cart_is_empty' );
?>
<div class="info narrow">
	<div class="ph"><h1>سبد خرید</h1></div>
	<div class="empty box">
		<?php echo salimor_icon( 'bag' ); // phpcs:ignore ?>
		<p><b>سبد خرید خالی است.</b></p>
		<p>چند قطعه از تازه‌های فروشگاه را ببینید.</p>
		<a class="btn b-ink" href="<?php echo esc_url( salimor_shop_url() ); ?>">رفتن به فروشگاه</a>
	</div>
</div>
