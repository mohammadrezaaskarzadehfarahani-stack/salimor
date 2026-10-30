<?php
/**
 * Login / sign-up. With an SMS provider (or on a local test site): mobile number + one-time code.
 * Otherwise WooCommerce's username/password form in the same card.
 *
 * @package Salimor
 * @version 9.9.0
 */

defined( 'ABSPATH' ) || exit;

$redirect = isset( $_GET['redirect_to'] ) ? wp_validate_redirect( esc_url_raw( wp_unslash( $_GET['redirect_to'] ) ), '' ) : ''; // phpcs:ignore
$terms    = wc_terms_and_conditions_page_id();
$privacy  = (int) get_option( 'wp_page_for_privacy_policy' );
$fine     = 'ورود شما به معنی پذیرش ' . ( $terms ? '<a href="' . esc_url( get_permalink( $terms ) ) . '">قوانین</a>' : 'قوانین' ) . ( $privacy ? ' و <a href="' . esc_url( get_permalink( $privacy ) ) . '">حریم خصوصی</a>' : '' ) . ' ' . esc_html( get_bloginfo( 'name' ) ) . ' است.';
do_action( 'woocommerce_before_customer_login_form' );
?>
<div class="auth">
	<div class="auth-card box">
		<div class="auth-mark" aria-hidden="true"><?php salimor_svg( 'logo' ); ?></div>
		<h1>ورود یا ثبت‌نام</h1>
		<?php woocommerce_output_all_notices(); ?>

		<?php if ( salimor_otp_enabled() ) : ?>
			<div id="otp" data-nonce="<?php echo esc_attr( wp_create_nonce( 'salimor-otp' ) ); ?>" data-redirect="<?php echo esc_url( $redirect ); ?>">
				<form id="lf1" novalidate>
					<p class="muted">شماره موبایل خود را وارد کنید. اگر حساب نداشته باشید، ساخته می‌شود.</p>
					<?php salimor_field( 'lp', 'phone', 'شماره موبایل', '', 'type="tel" inputmode="numeric" autocomplete="tel" class="ltr big" placeholder="09121234567" required' ); ?>
					<button class="btn b-ink wide" type="submit">ارسال کد تأیید</button>
				</form>
				<form id="lf2" novalidate hidden>
					<p class="muted" id="lsent"></p>
					<?php salimor_field( 'lc', 'code', 'کد تأیید ۵ رقمی', '', 'inputmode="numeric" autocomplete="one-time-code" maxlength="5" class="ltr big code" required', '', salimor_otp_test_mode() ? 'حالت آزمایشی: کد روی همین صفحه نمایش داده می‌شود.' : '' ); ?>
					<button class="btn b-ink wide" type="submit">ورود</button>
					<div class="auth-alt"><button type="button" class="link" id="ledit">ویرایش شماره</button><button type="button" class="link" id="lresend" disabled>ارسال دوباره</button></div>
				</form>
			</div>
			<noscript><p class="box note">برای ورود با پیامک، جاوااسکریپت مرورگر را روشن کنید.</p></noscript>
		<?php else : ?>
			<form class="woocommerce-form woocommerce-form-login login form" method="post" novalidate>
				<?php do_action( 'woocommerce_login_form_start' ); ?>
				<p class="form-row"><label for="username">نام کاربری یا ایمیل</label><input type="text" class="input-text ltr" name="username" id="username" autocomplete="username" value="<?php echo ! empty( $_POST['username'] ) && is_string( $_POST['username'] ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; // phpcs:ignore ?>" required></p>
				<p class="form-row"><label for="password">رمز عبور</label><input class="input-text ltr" type="password" name="password" id="password" autocomplete="current-password" required></p>
				<?php do_action( 'woocommerce_login_form' ); ?>
				<p class="form-row"><label class="checkbox"><input name="rememberme" type="checkbox" id="rememberme" value="forever" checked> مرا به خاطر بسپار</label></p>
				<?php wp_nonce_field( 'woocommerce-login', 'woocommerce-login-nonce' ); ?>
				<?php if ( $redirect ) : ?><input type="hidden" name="redirect" value="<?php echo esc_url( $redirect ); ?>"><?php endif; ?>
				<button type="submit" class="btn b-ink wide" name="login" value="ورود">ورود</button>
				<a class="link" href="<?php echo esc_url( wp_lostpassword_url() ); ?>">رمز را فراموش کرده‌اید؟</a>
				<?php do_action( 'woocommerce_login_form_end' ); ?>
			</form>
			<?php if ( 'yes' === get_option( 'woocommerce_enable_myaccount_registration' ) ) : ?>
				<details class="reg"><summary>حساب ندارید؟ ثبت‌نام</summary><div class="bd">
					<form method="post" class="woocommerce-form woocommerce-form-register register form" novalidate <?php do_action( 'woocommerce_register_form_tag' ); ?>>
						<?php do_action( 'woocommerce_register_form_start' ); ?>
						<?php if ( 'no' === get_option( 'woocommerce_registration_generate_username' ) ) : ?>
							<p class="form-row"><label for="reg_username">نام کاربری</label><input type="text" class="input-text ltr" name="username" id="reg_username" autocomplete="username" required></p>
						<?php endif; ?>
						<p class="form-row"><label for="reg_email">ایمیل</label><input type="email" class="input-text ltr" name="email" id="reg_email" autocomplete="email" required></p>
						<?php if ( 'no' === get_option( 'woocommerce_registration_generate_password' ) ) : ?>
							<p class="form-row"><label for="reg_password">رمز عبور</label><input type="password" class="input-text ltr" name="password" id="reg_password" autocomplete="new-password" required></p>
						<?php else : ?>
							<p class="muted">لینک ساخت رمز عبور به ایمیل شما فرستاده می‌شود.</p>
						<?php endif; ?>
						<?php do_action( 'woocommerce_register_form' ); ?>
						<?php wp_nonce_field( 'woocommerce-register', 'woocommerce-register-nonce' ); ?>
						<button type="submit" class="btn b-line wide" name="register" value="ثبت‌نام">ثبت‌نام</button>
						<?php do_action( 'woocommerce_register_form_end' ); ?>
					</form>
				</div></details>
			<?php endif; ?>
		<?php endif; ?>
		<p class="fine"><?php echo $fine; // phpcs:ignore ?></p>
	</div>
</div>
<?php
do_action( 'woocommerce_after_customer_login_form' );
