<?php
/**
 * Checkout: 1 address (saved addresses or the form), 2 shipping, 3 payment; order summary aside.
 *
 * @package Salimor
 * @version 9.4.0
 */

defined( 'ABSPATH' ) || exit;

$addresses = is_user_logged_in() ? salimor_addresses() : array();
$default   = '';
foreach ( $addresses as $id => $a ) {
	if ( ! empty( $a['default'] ) ) {
		$default = $id;
	}
}
$fields = $checkout->get_checkout_fields( 'billing' );
?>
<div class="ck">
	<div class="ph">
		<nav class="crumbs" aria-label="مسیر صفحه"><a href="<?php echo esc_url( home_url( '/' ) ); ?>">خانه</a><span aria-hidden="true">/</span><a href="<?php echo esc_url( wc_get_cart_url() ); ?>">سبد خرید</a><span aria-hidden="true">/</span><span>تسویه حساب</span></nav>
		<h1>تسویه حساب</h1>
	</div>

	<?php do_action( 'woocommerce_before_checkout_form', $checkout ); ?>

	<?php if ( ! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in() ) : ?>
		<div class="empty box">
			<?php echo salimor_icon( 'user' ); // phpcs:ignore ?>
			<p><b>برای ثبت سفارش ابتدا وارد شوید.</b></p>
			<p>ورود فقط با شماره موبایل و کد تأیید است.</p>
			<a class="btn b-ink" href="<?php echo esc_url( add_query_arg( 'redirect_to', rawurlencode( wc_get_checkout_url() ), wc_get_page_permalink( 'myaccount' ) ) ); ?>">ورود یا ثبت‌نام</a>
		</div>
		<?php
		return;
	endif;
	?>

	<form name="checkout" method="post" class="checkout woocommerce-checkout" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data" aria-label="تسویه حساب" novalidate>
		<div class="ck-grid">
			<div class="ck-main">
				<?php do_action( 'woocommerce_checkout_before_customer_details' ); ?>
				<section class="box ck-step" id="customer_details" aria-labelledby="ck1">
					<h2 id="ck1"><i class="num">۱</i>آدرس تحویل</h2>
					<?php if ( $addresses ) : ?>
						<div class="opts" role="radiogroup" aria-label="آدرس‌های ذخیره‌شده" id="ckAddr">
							<?php foreach ( $addresses as $id => $a ) : ?>
								<label class="opt">
									<input type="radio" name="salimor_addr" value="<?php echo esc_attr( $id ); ?>" <?php checked( $id, $default ); ?> data-addr="<?php echo esc_attr( wp_json_encode( array_intersect_key( $a, array_flip( salimor_address_keys() ) ) ) ); ?>">
									<span><b><?php echo esc_html( $a['title'] ); ?></b><small><?php echo esc_html( salimor_address_line( $a ) ); ?></small><small>گیرنده: <?php echo esc_html( trim( $a['first_name'] . ' ' . $a['last_name'] ) ); ?> · <span class="num"><?php echo esc_html( salimor_fa( $a['phone'] ) ); ?></span></small></span>
								</label>
							<?php endforeach; ?>
							<label class="opt">
								<input type="radio" name="salimor_addr" value="new">
								<span><b>آدرس جدید</b><small>به آدرس‌های شما هم اضافه می‌شود</small></span><?php echo salimor_icon( 'plus' ); // phpcs:ignore ?>
							</label>
						</div>
					<?php endif; ?>
					<div class="ck-fields<?php echo $addresses ? ' is-saved' : ''; ?>" id="ckFields">
						<div class="woocommerce-billing-fields__field-wrapper fgrid2">
							<?php
							foreach ( $fields as $key => $field ) {
								woocommerce_form_field( $key, $field, $checkout->get_value( $key ) );
							}
							?>
						</div>
						<?php if ( ! is_user_logged_in() && $checkout->is_registration_enabled() ) : ?>
							<div class="woocommerce-account-fields">
								<?php if ( ! $checkout->is_registration_required() ) : ?>
									<p class="form-row form-row-wide create-account"><label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox"><input class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" id="createaccount" <?php checked( ( true === $checkout->get_value( 'createaccount' ) || ( true === apply_filters( 'woocommerce_create_account_default_checked', false ) ) ), true ); ?> type="checkbox" name="createaccount" value="1"> <span>ساخت حساب کاربری</span></label></p>
								<?php endif; ?>
								<?php if ( $checkout->get_checkout_fields( 'account' ) ) : ?>
									<div class="create-account">
										<?php foreach ( $checkout->get_checkout_fields( 'account' ) as $key => $field ) : ?>
											<?php woocommerce_form_field( $key, $field, $checkout->get_value( $key ) ); ?>
										<?php endforeach; ?>
									</div>
								<?php endif; ?>
							</div>
						<?php endif; ?>
					</div>
					<?php do_action( 'woocommerce_checkout_shipping' ); ?>
				</section>
				<?php do_action( 'woocommerce_checkout_after_customer_details' ); ?>

				<?php if ( WC()->cart->needs_shipping() ) : ?>
					<section class="box ck-step" aria-labelledby="ck2">
						<h2 id="ck2"><i class="num">۲</i>روش ارسال</h2>
						<?php echo salimor_checkout_shipping_html(); // phpcs:ignore ?>
					</section>
				<?php endif; ?>

				<section class="box ck-step" aria-labelledby="ck3">
					<h2 id="ck3"><i class="num"><?php echo WC()->cart->needs_shipping() ? '۳' : '۲'; ?></i>پرداخت</h2>
					<?php woocommerce_checkout_payment(); ?>
				</section>
			</div>

			<aside class="ck-sum box" aria-label="خلاصه‌ی سفارش">
				<h2 class="sub-t">خلاصه‌ی سفارش</h2>
				<?php do_action( 'woocommerce_checkout_before_order_review' ); ?>
				<div id="order_review" class="woocommerce-checkout-review-order">
					<?php woocommerce_order_review(); ?>
				</div>
				<?php wc_get_template( 'checkout/terms.php' ); ?>
				<?php do_action( 'woocommerce_review_order_before_submit' ); ?>
				<button type="submit" class="btn b-ink wide" name="woocommerce_checkout_place_order" id="place_order" value="پرداخت و ثبت سفارش" data-value="پرداخت و ثبت سفارش">پرداخت و ثبت سفارش</button>
				<?php do_action( 'woocommerce_review_order_after_submit' ); ?>
				<?php wp_nonce_field( 'woocommerce-process_checkout', 'woocommerce-process-checkout-nonce' ); ?>
				<?php
				$terms   = wc_terms_and_conditions_page_id();
				$privacy = (int) get_option( 'wp_page_for_privacy_policy' );
				?>
				<p class="fine">با ثبت سفارش، <?php echo $terms ? '<a href="' . esc_url( get_permalink( $terms ) ) . '">قوانین و مقررات</a>' : 'قوانین و مقررات'; ?><?php echo $privacy ? ' و <a href="' . esc_url( get_permalink( $privacy ) ) . '">حریم خصوصی</a>' : ''; ?> را می‌پذیرید. پس از پرداخت به سالیمور برمی‌گردید.</p>
				<?php do_action( 'woocommerce_checkout_after_order_review' ); ?>
			</aside>
		</div>
	</form>

	<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>
</div>
