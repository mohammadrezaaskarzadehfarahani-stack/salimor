<?php
/**
 * Account shell: side menu (desktop) + section content.
 *
 * @package Salimor
 * @version 3.5.0
 */

defined( 'ABSPATH' ) || exit;

$section = salimor_account_section();
$menu    = salimor_account_menu();
$titles  = array( 'dashboard' => '', 'orders' => 'سفارش‌ها', 'edit-address' => 'آدرس‌ها', 'wishlist' => 'علاقه‌مندی‌ها', 'edit-account' => 'اطلاعات حساب' );
$user    = wp_get_current_user();
$name    = trim( $user->first_name . ' ' . $user->last_name );
global $wp;
?>
<div class="acc">
	<?php do_action( 'woocommerce_account_navigation' ); ?>
	<div class="acc-main woocommerce-MyAccount-content">
		<?php if ( 'dashboard' !== $section ) : ?>
			<a class="acc-back" href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>"><?php echo salimor_icon( 'back' ); // phpcs:ignore ?>پنل کاربری</a>
		<?php endif; ?>
		<?php if ( 'dashboard' === $section ) : ?>
			<h1><?php echo esc_html( $name ? 'سلام، ' . $user->first_name : 'سلام' ); ?></h1>
		<?php elseif ( ! isset( $wp->query_vars['view-order'] ) ) : ?>
			<h1><?php echo esc_html( $titles[ $section ] ?? wc_page_endpoint_title( '' ) ); ?></h1>
		<?php endif; ?>
		<?php woocommerce_output_all_notices(); ?>
		<?php do_action( 'woocommerce_account_content' ); ?>
	</div>
</div>
