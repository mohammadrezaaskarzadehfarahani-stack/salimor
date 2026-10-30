<?php
/**
 * Cart page: the same live lines as the cart drawer, with a summary column.
 *
 * @package Salimor
 * @version 11.2.0
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_cart' );
?>
<div class="cart-pg">
	<div class="ph">
		<nav class="crumbs" aria-label="مسیر صفحه"><a href="<?php echo esc_url( home_url( '/' ) ); ?>">خانه</a><span aria-hidden="true">/</span><span>سبد خرید</span></nav>
		<h1>سبد خرید</h1>
	</div>
	<div class="cart-page" id="cartPage" aria-live="polite"><?php echo salimor_cart_drawer_inner( true ); // phpcs:ignore ?></div>
	<?php do_action( 'woocommerce_after_cart_table' ); ?>
</div>
<?php
do_action( 'woocommerce_after_cart' );
