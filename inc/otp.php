<?php
/**
 * Login with mobile number + one-time SMS code, and order SMS.
 *
 * Providers: Kavenegar (verify/lookup) and MeliPayamak (shared pattern). Any other provider can be
 * plugged in with the `salimor_sms_send` filter. With no provider the account page falls back to
 * WooCommerce's username/password form — except on local/development sites, where a test mode
 * shows the code on screen so the flow can be tried without an SMS account.
 *
 * Security: codes are random 5-digit numbers stored only as a keyed hash, valid 2 minutes, 5 wrong
 * tries void the code; one send per number per 60 s, 5 per hour; 15 sends per IP per hour.
 * Accounts that can edit content (shop managers, editors, admins) cannot log in by SMS.
 *
 * @package Salimor
 */

defined( 'ABSPATH' ) || exit;

/* ---------------------------------------------------------------------------
 * Settings: WooCommerce → «پیامک و ورود»
 * ------------------------------------------------------------------------- */
function salimor_sms_opt( $key, $default = '' ) {
	$o = get_option( 'salimor_sms', array() );
	return isset( $o[ $key ] ) && '' !== $o[ $key ] ? $o[ $key ] : $default;
}

add_action( 'admin_menu', function () {
	add_submenu_page( 'woocommerce', 'پیامک و ورود با موبایل', 'پیامک و ورود', 'manage_woocommerce', 'salimor-sms', 'salimor_sms_page' );
}, 60 );

add_action( 'admin_init', function () {
	register_setting( 'salimor_sms', 'salimor_sms', array(
		'type'              => 'array',
		'sanitize_callback' => function ( $in ) {
			$out = array();
			foreach ( array( 'provider', 'api_key', 'otp_template', 'ship_template', 'sender' ) as $k ) {
				$out[ $k ] = sanitize_text_field( $in[ $k ] ?? '' );
			}
			$out['provider'] = in_array( $out['provider'], array( '', 'kavenegar', 'melipayamak' ), true ) ? $out['provider'] : '';
			return $out;
		},
	) );
} );

function salimor_sms_page() {
	$o = get_option( 'salimor_sms', array() );
	$v = fn( $k ) => esc_attr( $o[ $k ] ?? '' );
	?>
	<div class="wrap">
		<h1>پیامک و ورود با موبایل</h1>
		<p>با تنظیم سرویس پیامک، ورود و ثبت‌نام مشتری‌ها فقط با شماره موبایل و کد تأیید انجام می‌شود. بدون سرویس پیامک، فرم معمولی نام کاربری و رمز نمایش داده می‌شود.</p>
		<?php if ( salimor_otp_test_mode() ) : ?>
			<div class="notice notice-warning inline"><p><b>حالت آزمایشی فعال است:</b> این سایت «محلی/توسعه» است و کد تأیید روی صفحه نمایش داده می‌شود. روی سایت اصلی (production) این حالت خودبه‌خود خاموش است.</p></div>
		<?php endif; ?>
		<form method="post" action="options.php">
			<?php settings_fields( 'salimor_sms' ); ?>
			<table class="form-table" role="presentation">
				<tr><th scope="row"><label for="sp">سرویس پیامک</label></th><td>
					<select id="sp" name="salimor_sms[provider]">
						<option value="">— خاموش —</option>
						<option value="kavenegar" <?php selected( $o['provider'] ?? '', 'kavenegar' ); ?>>کاوه‌نگار (Verify Lookup)</option>
						<option value="melipayamak" <?php selected( $o['provider'] ?? '', 'melipayamak' ); ?>>ملی‌پیامک (ارسال با الگو)</option>
					</select></td></tr>
				<tr><th scope="row"><label for="sk">کلید API</label></th><td><input id="sk" class="regular-text" style="direction:ltr" name="salimor_sms[api_key]" value="<?php echo $v( 'api_key' ); // phpcs:ignore ?>" autocomplete="off"><p class="description">کاوه‌نگار: API Key از پنل. ملی‌پیامک: کلید کنسول (console.melipayamak.com).</p></td></tr>
				<tr><th scope="row"><label for="st">الگوی کد تأیید</label></th><td><input id="st" class="regular-text" style="direction:ltr" name="salimor_sms[otp_template]" value="<?php echo $v( 'otp_template' ); // phpcs:ignore ?>"><p class="description">کاوه‌نگار: نام الگو (مثلاً verify) با متغیر <code>%token</code>. ملی‌پیامک: کد الگو (bodyId) با متغیر <code>{0}</code>.<br>برای پر شدن خودکار کد در اندروید، خط آخر پیامک را <code>@<?php echo esc_html( wp_parse_url( home_url(), PHP_URL_HOST ) ); ?> #کد</code> بگذارید.</p></td></tr>
				<tr><th scope="row"><label for="ss">الگوی «سفارش ارسال شد» (اختیاری)</label></th><td><input id="ss" class="regular-text" style="direction:ltr" name="salimor_sms[ship_template]" value="<?php echo $v( 'ship_template' ); // phpcs:ignore ?>"><p class="description">وقتی وضعیت سفارش «ارسال شد» شود برای مشتری فرستاده می‌شود. متغیر اول شماره‌ی سفارش و متغیر دوم کد رهگیری است (کاوه‌نگار: <code>%token</code> و <code>%token2</code>؛ ملی‌پیامک: <code>{0}</code> و <code>{1}</code>).</p></td></tr>
			</table>
			<?php submit_button( 'ذخیره' ); ?>
		</form>
	</div>
	<?php
}

/** Local/development site without a provider: show the code on screen. */
function salimor_otp_test_mode() {
	return ! salimor_sms_opt( 'provider' ) && in_array( wp_get_environment_type(), array( 'local', 'development' ), true );
}

function salimor_otp_enabled() {
	return (bool) apply_filters( 'salimor_otp_enabled', ( salimor_sms_opt( 'provider' ) && salimor_sms_opt( 'api_key' ) && salimor_sms_opt( 'otp_template' ) ) || salimor_otp_test_mode() );
}

/**
 * Send a pattern SMS. $args are the pattern variables in order.
 *
 * @return true|WP_Error
 */
function salimor_sms_send( $to, $template, array $args ) {
	$pre = apply_filters( 'salimor_sms_send', null, $to, $template, $args );
	if ( null !== $pre ) {
		return $pre;
	}
	$key = salimor_sms_opt( 'api_key' );
	switch ( salimor_sms_opt( 'provider' ) ) {
		case 'kavenegar':
			$q = array( 'receptor' => $to, 'template' => $template, 'token' => $args[0] ?? '' );
			if ( isset( $args[1] ) ) {
				$q['token2'] = $args[1];
			}
			if ( isset( $args[2] ) ) {
				$q['token3'] = $args[2];
			}
			$r = wp_remote_get( 'https://api.kavenegar.com/v1/' . rawurlencode( $key ) . '/verify/lookup.json?' . http_build_query( $q ), array( 'timeout' => 12 ) );
			if ( is_wp_error( $r ) ) {
				return $r;
			}
			$b = json_decode( wp_remote_retrieve_body( $r ), true );
			return 200 === (int) ( $b['return']['status'] ?? 0 ) ? true : new WP_Error( 'sms', $b['return']['message'] ?? 'kavenegar error' );
		case 'melipayamak':
			$r = wp_remote_post(
				'https://console.melipayamak.com/api/send/shared/' . rawurlencode( $key ),
				array(
					'timeout' => 12,
					'headers' => array( 'Content-Type' => 'application/json' ),
					'body'    => wp_json_encode( array( 'bodyId' => (int) $template, 'to' => $to, 'args' => array_map( 'strval', $args ) ) ),
				)
			);
			if ( is_wp_error( $r ) ) {
				return $r;
			}
			$b = json_decode( wp_remote_retrieve_body( $r ), true );
			return ( 200 === wp_remote_retrieve_response_code( $r ) && ! empty( $b['recId'] ) && (int) $b['recId'] > 20 ) ? true : new WP_Error( 'sms', $b['status'] ?? 'melipayamak error' );
	}
	return new WP_Error( 'sms', 'no provider' );
}

/* ---------------------------------------------------------------------------
 * OTP
 * ------------------------------------------------------------------------- */
function salimor_client_ip() {
	return sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ?? '' ) ); // proxies' headers are spoofable; REMOTE_ADDR is not
}

/** Count hits in a rolling window; returns false when over the limit. */
function salimor_hit( $bucket, $limit, $window ) {
	$k = 'slm_rl_' . md5( $bucket );
	$v = get_transient( $k );
	$v = is_array( $v ) && $v['until'] > time() ? $v : array( 'n' => 0, 'until' => time() + $window );
	if ( $v['n'] >= $limit ) {
		return false;
	}
	++$v['n'];
	set_transient( $k, $v, $v['until'] - time() );
	return true;
}

function salimor_otp_json( $ok, $msg = '', array $extra = array() ) {
	wp_send_json( array_merge( array( 'ok' => $ok, 'message' => $msg ), $extra ), 200 );
}

function salimor_otp_send() {
	if ( ! salimor_otp_enabled() || ! check_ajax_referer( 'salimor-otp', 'nonce', false ) ) {
		salimor_otp_json( false, 'صفحه را دوباره باز کنید و دوباره تلاش کنید.' );
	}
	$phone = salimor_mobile( wp_unslash( $_POST['phone'] ?? '' ) ); // phpcs:ignore
	if ( ! $phone ) {
		salimor_otp_json( false, 'شماره موبایل ۱۱ رقمی و با ۰۹ شروع می‌شود؛ مثل ۰۹۱۲۱۲۳۴۵۶۷.', array( 'field' => 'phone' ) );
	}
	$key  = 'slm_otp_' . md5( $phone . wp_salt( 'nonce' ) );
	$prev = get_transient( $key );
	if ( is_array( $prev ) && $prev['sent'] > time() - 60 ) {
		salimor_otp_json( false, 'کد قبلی همین الان فرستاده شد؛ کمی صبر کنید.', array( 'wait' => 60 - ( time() - $prev['sent'] ) ) );
	}
	if ( ! salimor_hit( 'p' . $phone, 5, HOUR_IN_SECONDS ) || ! salimor_hit( 'i' . salimor_client_ip(), 15, HOUR_IN_SECONDS ) ) {
		salimor_otp_json( false, 'تعداد درخواست‌ها زیاد بود. یک ساعت دیگر دوباره تلاش کنید.' );
	}
	$code = (string) random_int( 10000, 99999 );
	set_transient( $key, array( 'h' => hash_hmac( 'sha256', $code, wp_salt( 'auth' ) . $phone ), 'exp' => time() + 120, 'tries' => 0, 'sent' => time() ), 10 * MINUTE_IN_SECONDS );

	$extra = array( 'wait' => 60 );
	if ( salimor_otp_test_mode() ) {
		$extra['test'] = $code;
	} else {
		$sent = salimor_sms_send( $phone, salimor_sms_opt( 'otp_template' ), array( $code ) );
		if ( is_wp_error( $sent ) ) {
			delete_transient( $key );
			if ( function_exists( 'wc_get_logger' ) ) {
				wc_get_logger()->warning( 'OTP SMS failed: ' . $sent->get_error_message(), array( 'source' => 'salimor-sms' ) );
			}
			salimor_otp_json( false, 'ارسال پیامک انجام نشد. چند دقیقه‌ی دیگر دوباره تلاش کنید.' );
		}
	}
	salimor_otp_json( true, '', $extra );
}
add_action( 'wc_ajax_salimor_otp_send', 'salimor_otp_send' );

function salimor_otp_verify() {
	if ( ! salimor_otp_enabled() || ! check_ajax_referer( 'salimor-otp', 'nonce', false ) ) {
		salimor_otp_json( false, 'صفحه را دوباره باز کنید و دوباره تلاش کنید.' );
	}
	$phone = salimor_mobile( wp_unslash( $_POST['phone'] ?? '' ) ); // phpcs:ignore
	$code  = preg_replace( '/\D/', '', salimor_en_digits( wp_unslash( $_POST['code'] ?? '' ) ) ); // phpcs:ignore
	$key   = 'slm_otp_' . md5( $phone . wp_salt( 'nonce' ) );
	$rec   = $phone ? get_transient( $key ) : false;
	if ( ! is_array( $rec ) || $rec['exp'] < time() ) {
		salimor_otp_json( false, 'کد منقضی شده است؛ «ارسال دوباره» را بزنید.', array( 'field' => 'code' ) );
	}
	if ( 5 !== strlen( $code ) || ! hash_equals( $rec['h'], hash_hmac( 'sha256', $code, wp_salt( 'auth' ) . $phone ) ) ) {
		++$rec['tries'];
		if ( $rec['tries'] >= 5 ) {
			delete_transient( $key );
			salimor_otp_json( false, 'چند بار کد اشتباه وارد شد؛ کد تازه بگیرید.', array( 'field' => 'code' ) );
		}
		set_transient( $key, $rec, 10 * MINUTE_IN_SECONDS );
		salimor_otp_json( false, 5 !== strlen( $code ) ? 'کد تأیید ۵ رقم است.' : 'کد واردشده درست نیست؛ دوباره بررسی کنید.', array( 'field' => 'code' ) );
	}
	delete_transient( $key );

	$users = get_users( array( 'meta_key' => 'salimor_mobile', 'meta_value' => $phone, 'number' => 2, 'fields' => 'all' ) ); // phpcs:ignore
	$user  = $users ? $users[0] : null;
	if ( $user && user_can( $user, 'edit_posts' ) ) {
		salimor_otp_json( false, 'برای این حساب از ورود با نام کاربری و رمز استفاده کنید.' );
	}
	$created = false;
	if ( ! $user ) {
		$login = username_exists( $phone ) ? $phone . '-' . wp_rand( 100, 999 ) : $phone;
		$id    = wp_insert_user( array( 'user_login' => $login, 'user_pass' => wp_generate_password( 32, true, true ), 'role' => 'customer', 'display_name' => $phone, 'nickname' => $phone ) );
		if ( is_wp_error( $id ) ) {
			salimor_otp_json( false, 'ساخت حساب انجام نشد؛ با پشتیبانی تماس بگیرید.' );
		}
		update_user_meta( $id, 'salimor_mobile', $phone );
		update_user_meta( $id, 'billing_phone', $phone );
		update_user_meta( $id, 'billing_country', 'IR' );
		$user    = get_user_by( 'id', $id );
		$created = true;
		do_action( 'salimor_customer_registered', $id, $phone );
	}
	wp_set_current_user( $user->ID );
	wp_set_auth_cookie( $user->ID, true, is_ssl() );
	do_action( 'wp_login', $user->user_login, $user );

	$home = wc_get_page_permalink( 'myaccount' );
	$to   = ! empty( $_POST['redirect'] ) ? wp_validate_redirect( wp_unslash( $_POST['redirect'] ), $home ) : $home; // phpcs:ignore
	salimor_otp_json( true, '', array( 'redirect' => $to, 'created' => $created ) );
}
add_action( 'wc_ajax_salimor_otp_verify', 'salimor_otp_verify' );

/* Customers created by SMS have no e-mail: let WooCommerce save their account without one. */
add_filter( 'woocommerce_save_account_details_required_fields', function ( $f ) {
	unset( $f['account_email'], $f['account_display_name'] );
	return $f;
} );

/* «ارسال شد» SMS with the tracking code. */
add_action( 'woocommerce_order_status_shipped', function ( $order_id ) {
	$tpl = salimor_sms_opt( 'ship_template' );
	$o   = wc_get_order( $order_id );
	if ( ! $tpl || ! $o || ! salimor_sms_opt( 'provider' ) ) {
		return;
	}
	$to = salimor_mobile( $o->get_billing_phone() );
	if ( $to ) {
		$r = salimor_sms_send( $to, $tpl, array( $o->get_order_number(), $o->get_meta( '_salimor_tracking' ) ? $o->get_meta( '_salimor_tracking' ) : '-' ) );
		$o->add_order_note( is_wp_error( $r ) ? 'پیامک «ارسال شد» فرستاده نشد: ' . $r->get_error_message() : 'پیامک «ارسال شد» برای مشتری فرستاده شد.' );
	}
} );
