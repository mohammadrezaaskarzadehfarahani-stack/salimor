<?php
/**
 * Account side menu (desktop). Phones use the list on the dashboard.
 *
 * @package Salimor
 * @version 9.3.0
 */

defined( 'ABSPATH' ) || exit;

$user    = wp_get_current_user();
$name    = trim( $user->first_name . ' ' . $user->last_name );
$mobile  = get_user_meta( $user->ID, 'salimor_mobile', true );
$section = salimor_account_section();
$icons   = salimor_account_menu();
do_action( 'woocommerce_before_account_navigation' );
?>
<aside class="acc-side" aria-label="منوی حساب کاربری">
	<div class="acc-who"><span class="av" aria-hidden="true"><?php echo esc_html( mb_substr( $name ? $name : 'س', 0, 1 ) ); ?></span><div><b><?php echo esc_html( $name ? $name : 'کاربر سالیمور' ); ?></b><small class="num"><?php echo esc_html( $mobile ? salimor_fa( $mobile ) : $user->user_email ); ?></small></div></div>
	<nav class="woocommerce-MyAccount-navigation">
		<?php foreach ( wc_get_account_menu_items() as $ep => $label ) : ?>
			<?php if ( 'customer-logout' === $ep ) : ?>
				<a class="acc-out" href="<?php echo esc_url( wc_logout_url() ); ?>"><?php echo salimor_icon( 'out' ); // phpcs:ignore ?><?php echo esc_html( $label ); ?></a>
			<?php else : ?>
				<a href="<?php echo esc_url( wc_get_account_endpoint_url( $ep ) ); ?>" <?php echo $section === $ep ? 'aria-current="page"' : ''; ?>><?php echo salimor_icon( $icons[ $ep ][1] ?? 'arrow' ); // phpcs:ignore ?><?php echo esc_html( $label ); ?></a>
			<?php endif; ?>
		<?php endforeach; ?>
	</nav>
</aside>
<?php
do_action( 'woocommerce_after_account_navigation' );
