<?php
/**
 * My account: menu, wishlist endpoint, address book and profile forms, order cards.
 *
 * @package Salimor
 */

defined( 'ABSPATH' ) || exit;

/* ---------------------------------------------------------------------------
 * Menu and endpoints
 * ------------------------------------------------------------------------- */
add_action( 'init', function () {
	add_rewrite_endpoint( 'wishlist', EP_ROOT | EP_PAGES );
	if ( get_option( 'salimor_rewrite' ) !== SALIMOR_VERSION ) {
		flush_rewrite_rules( false );
		update_option( 'salimor_rewrite', SALIMOR_VERSION );
	}
} );
add_action( 'after_switch_theme', fn() => delete_option( 'salimor_rewrite' ) );
add_filter( 'woocommerce_get_query_vars', function ( $v ) {
	$v['wishlist'] = 'wishlist';
	return $v;
} );
add_filter( 'woocommerce_endpoint_wishlist_title', fn() => 'علاقه‌مندی‌ها' );

/** [ endpoint => [ label, icon ] ] */
function salimor_account_menu() {
	return array(
		'dashboard'       => array( 'داشبورد', 'home' ),
		'orders'          => array( 'سفارش‌ها', 'box' ),
		'edit-address'    => array( 'آدرس‌ها', 'pin' ),
		'wishlist'        => array( 'علاقه‌مندی‌ها', 'heart' ),
		'edit-account'    => array( 'اطلاعات حساب', 'user' ),
		'customer-logout' => array( 'خروج از حساب', 'out' ),
	);
}
add_filter( 'woocommerce_account_menu_items', function ( $items ) {
	$out = array();
	foreach ( salimor_account_menu() as $k => list( $label ) ) {
		$out[ $k ] = $label;
	}
	/* keep endpoints added by plugins (before «خروج») */
	foreach ( $items as $k => $label ) {
		if ( ! isset( $out[ $k ] ) && ! in_array( $k, array( 'downloads', 'payment-methods' ), true ) ) {
			$out = array_slice( $out, 0, -1, true ) + array( $k => $label ) + array_slice( $out, -1, 1, true );
		}
	}
	return $out;
}, 50 );

/** The current account section (for titles and the active menu item). */
function salimor_account_section() {
	global $wp;
	foreach ( array_keys( WC()->query->get_query_vars() ) as $ep ) {
		if ( isset( $wp->query_vars[ $ep ] ) ) {
			return 'view-order' === $ep ? 'orders' : $ep;
		}
	}
	return 'dashboard';
}

add_action( 'woocommerce_account_wishlist_endpoint', function () {
	$ids = array_filter( array_map( 'wc_get_product', salimor_wishlist() ), fn( $p ) => $p && $p->is_visible() );
	if ( ! $ids ) {
		echo '<div class="empty box">' . salimor_icon( 'heart' ) . '<p>هنوز محصولی را نپسندیده‌اید. روی قلب هر محصول بزنید تا این‌جا ذخیره شود.</p><a class="btn b-ink" href="' . esc_url( salimor_shop_url() ) . '">مشاهده محصولات</a></div>'; // phpcs:ignore
		return;
	}
	echo '<div class="grid wish-grid">';
	foreach ( $ids as $p ) {
		salimor_product_card( $p );
	}
	echo '</div>';
} );

/* ---------------------------------------------------------------------------
 * Orders
 * ------------------------------------------------------------------------- */
function salimor_status_chip( WC_Order $o ) {
	$step = salimor_order_step( $o );
	$cls  = $step >= 0 ? 'st' . $step : 'stx';
	$label = $step >= 0 ? salimor_order_steps()[ $step ] : wc_get_order_status_name( $o->get_status() );
	if ( 0 === $step && $o->needs_payment() ) {
		$label = 'در انتظار پرداخت';
	}
	return '<span class="st ' . esc_attr( $cls ) . '">' . esc_html( $label ) . '</span>';
}

function salimor_order_card( WC_Order $o ) {
	$step = salimor_order_step( $o );
	?>
	<article class="ord box">
		<div class="ord-h"><div><b>سفارش <span class="num"><?php echo esc_html( salimor_fa( $o->get_order_number() ) ); ?></span></b><small><?php echo esc_html( salimor_fa( wc_format_datetime( $o->get_date_created(), 'j F Y' ) ) ); ?></small></div><?php echo salimor_status_chip( $o ); // phpcs:ignore ?></div>
		<div class="ord-items">
			<?php
			$n = 0;
			foreach ( $o->get_items() as $item ) :
				$p = $item->get_product();
				if ( ++$n > 5 ) {
					break;
				}
				?>
				<span class="pic" title="<?php echo esc_attr( $item->get_name() ); ?>"><?php echo $p ? $p->get_image( 'thumbnail', array( 'alt' => '' ) ) : ''; // phpcs:ignore ?></span>
			<?php endforeach; ?>
		</div>
		<?php if ( $step >= 0 ) : ?>
			<div class="track" role="img" aria-label="<?php echo esc_attr( 'وضعیت: ' . salimor_order_steps()[ $step ] ); ?>"><?php foreach ( salimor_order_steps() as $i => $s ) : ?><i class="<?php echo $i <= $step ? 'on' : ''; ?>"></i><?php endforeach; ?></div>
		<?php endif; ?>
		<div class="ord-f"><span>مبلغ: <b class="num"><?php echo salimor_num( $o->get_total() ); // phpcs:ignore ?></b> تومان</span><a class="more" href="<?php echo esc_url( $o->get_view_order_url() ); ?>">جزئیات سفارش <?php echo salimor_icon( 'arrow' ); // phpcs:ignore ?></a></div>
	</article>
	<?php
}

function salimor_customer_orders( $limit = -1, $statuses = array() ) {
	return wc_get_orders( array(
		'customer' => get_current_user_id(),
		'limit'    => $limit,
		'orderby'  => 'date',
		'order'    => 'DESC',
		'status'   => $statuses ? $statuses : array_keys( wc_get_order_statuses() ),
	) );
}

/* ---------------------------------------------------------------------------
 * Address book form (account page)
 * ------------------------------------------------------------------------- */
add_action( 'template_redirect', function () {
	if ( empty( $_POST['salimor_addr_action'] ) || ! is_user_logged_in() ) {
		return;
	}
	if ( ! isset( $_POST['_slmnonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['_slmnonce'] ), 'salimor_address' ) ) {
		wc_add_notice( 'نشست شما منقضی شده بود؛ دوباره تلاش کنید.', 'error' );
		return;
	}
	$list   = salimor_addresses();
	$id     = sanitize_key( wp_unslash( $_POST['addr_id'] ?? '' ) );
	$action = sanitize_key( wp_unslash( $_POST['salimor_addr_action'] ) );
	$back   = wc_get_account_endpoint_url( 'edit-address' );
	if ( 'delete' === $action && isset( $list[ $id ] ) ) {
		unset( $list[ $id ] );
		salimor_save_addresses( $list );
		wc_add_notice( 'آدرس حذف شد.' );
	} elseif ( 'default' === $action && isset( $list[ $id ] ) ) {
		foreach ( $list as $k => &$a ) {
			$a['default'] = $k === $id;
		}
		unset( $a );
		salimor_save_addresses( $list );
		wc_add_notice( 'آدرس پیش‌فرض تغییر کرد.' );
	} elseif ( 'save' === $action ) {
		list( $a, $errors ) = salimor_read_address( $_POST ); // phpcs:ignore
		if ( $errors ) {
			$GLOBALS['salimor_addr_errors'] = $errors;
			$GLOBALS['salimor_addr_values'] = $a + array( 'id' => $id );
			return;
		}
		if ( ! $id || ! isset( $list[ $id ] ) ) {
			$id        = 'a' . time();
			$a['default'] = ! $list;
		} else {
			$a['default'] = ! empty( $list[ $id ]['default'] );
		}
		$list[ $id ] = $a;
		salimor_save_addresses( $list );
		wc_add_notice( 'آدرس ذخیره شد.' );
	}
	$redirect = ! empty( $_POST['redirect'] ) ? wp_validate_redirect( wp_unslash( $_POST['redirect'] ), $back ) : $back; // phpcs:ignore
	wp_safe_redirect( $redirect );
	exit;
} );

/** Field helper matching the demo's .fld markup. */
function salimor_field( $id, $name, $label, $value, $attrs = '', $err = '', $help = '' ) {
	$desc = trim( ( $err ? $id . '-e' : '' ) . ( $help ? ' ' . $id . '-h' : '' ) );
	printf(
		'<div class="fld"><label for="%1$s">%2$s</label><input id="%1$s" name="%3$s" value="%4$s" %5$s %6$s%7$s>%8$s%9$s</div>',
		esc_attr( $id ),
		esc_html( $label ),
		esc_attr( $name ),
		esc_attr( $value ),
		$attrs, // phpcs:ignore -- static attribute strings from templates
		$err ? 'aria-invalid="true" ' : '',
		$desc ? 'aria-describedby="' . esc_attr( $desc ) . '"' : '',
		$help ? '<small class="hint" id="' . esc_attr( $id ) . '-h">' . esc_html( $help ) . '</small>' : '',
		$err ? '<small class="err" id="' . esc_attr( $id ) . '-e">' . esc_html( $err ) . '</small>' : ''
	);
}

/** The add/edit address form. */
function salimor_address_form( array $a = array(), array $e = array(), $redirect = '' ) {
	$states = WC()->countries->get_states( 'IR' );
	$a      = wp_parse_args( $a, array( 'id' => '', 'title' => '', 'first_name' => '', 'last_name' => '', 'phone' => get_user_meta( get_current_user_id(), 'salimor_mobile', true ), 'state' => 'THR', 'city' => '', 'address_1' => '', 'postcode' => '' ) );
	?>
	<form class="box form" method="post" id="af" novalidate>
		<h2 class="sub-t"><?php echo $a['id'] ? 'ویرایش آدرس' : 'آدرس جدید'; ?></h2>
		<input type="hidden" name="salimor_addr_action" value="save"><input type="hidden" name="addr_id" value="<?php echo esc_attr( $a['id'] ); ?>">
		<?php if ( $redirect ) : ?><input type="hidden" name="redirect" value="<?php echo esc_url( $redirect ); ?>"><?php endif; ?>
		<?php wp_nonce_field( 'salimor_address', '_slmnonce' ); ?>
		<div class="fgrid">
			<?php salimor_field( 'at', 'title', 'عنوان آدرس', $a['title'], 'placeholder="خانه، محل کار…" required', $e['title'] ?? '' ); ?>
			<?php salimor_field( 'am', 'phone', 'موبایل گیرنده', salimor_fa( $a['phone'] ), 'type="tel" inputmode="numeric" class="ltr" autocomplete="tel" required', $e['phone'] ?? '' ); ?>
			<?php salimor_field( 'af1', 'first_name', 'نام گیرنده', $a['first_name'], 'autocomplete="given-name" required', $e['first_name'] ?? '' ); ?>
			<?php salimor_field( 'af2', 'last_name', 'نام خانوادگی گیرنده', $a['last_name'], 'autocomplete="family-name" required', $e['last_name'] ?? '' ); ?>
			<div class="fld"><label for="ap">استان</label><select id="ap" name="state" autocomplete="address-level1" <?php echo isset( $e['state'] ) ? 'aria-invalid="true"' : ''; ?>>
				<?php foreach ( $states as $code => $name ) : ?><option value="<?php echo esc_attr( $code ); ?>" <?php selected( $a['state'], $code ); ?>><?php echo esc_html( $name ); ?></option><?php endforeach; ?>
			</select></div>
			<?php salimor_field( 'ac', 'city', 'شهر', $a['city'], 'autocomplete="address-level2" required', $e['city'] ?? '' ); ?>
		</div>
		<div class="fld"><label for="al">نشانی کامل</label><textarea id="al" name="address_1" rows="3" autocomplete="street-address" placeholder="خیابان، کوچه، پلاک و واحد" <?php echo isset( $e['address_1'] ) ? 'aria-invalid="true" aria-describedby="al-e"' : ''; ?>><?php echo esc_textarea( $a['address_1'] ); ?></textarea><?php echo isset( $e['address_1'] ) ? '<small class="err" id="al-e">' . esc_html( $e['address_1'] ) . '</small>' : ''; ?></div>
		<div class="fgrid">
			<?php salimor_field( 'az', 'postcode', 'کد پستی', salimor_fa( $a['postcode'] ), 'inputmode="numeric" class="ltr" maxlength="10" autocomplete="postal-code" required', $e['postcode'] ?? '', '۱۰ رقم، بدون خط تیره' ); ?>
		</div>
		<div class="row"><button class="btn b-ink" type="submit">ذخیره آدرس</button><a class="btn b-line" href="<?php echo esc_url( wc_get_account_endpoint_url( 'edit-address' ) ); ?>" id="acancel">انصراف</a></div>
	</form>
	<?php
}

/* ---------------------------------------------------------------------------
 * Profile (name, optional e-mail, Jalali birth date)
 * ------------------------------------------------------------------------- */
function salimor_jalali_months() {
	return array( 'فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور', 'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند' );
}

/** Current Jalali year (approximate is fine for the year list). */
function salimor_jalali_year() {
	$y = (int) gmdate( 'Y' );
	return (int) gmdate( 'n' ) > 3 || ( 3 === (int) gmdate( 'n' ) && (int) gmdate( 'j' ) >= 21 ) ? $y - 621 : $y - 622;
}

add_action( 'template_redirect', function () {
	if ( empty( $_POST['salimor_profile'] ) || ! is_user_logged_in() ) {
		return;
	}
	if ( ! isset( $_POST['_slmnonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['_slmnonce'] ), 'salimor_profile' ) ) {
		wc_add_notice( 'نشست شما منقضی شده بود؛ دوباره تلاش کنید.', 'error' );
		return;
	}
	$fn    = sanitize_text_field( wp_unslash( $_POST['first_name'] ?? '' ) );
	$ln    = sanitize_text_field( wp_unslash( $_POST['last_name'] ?? '' ) );
	$email = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
	$raw   = sanitize_text_field( wp_unslash( $_POST['email'] ?? '' ) );
	$d     = absint( $_POST['bd'] ?? 0 );
	$m     = absint( $_POST['bm'] ?? 0 );
	$y     = absint( $_POST['by'] ?? 0 );
	$e     = array();
	if ( mb_strlen( $fn ) < 2 ) {
		$e['first_name'] = 'نام را وارد کنید.';
	}
	if ( mb_strlen( $ln ) < 2 ) {
		$e['last_name'] = 'نام خانوادگی را وارد کنید.';
	}
	if ( '' !== $raw && ! is_email( $email ) ) {
		$e['email'] = 'ایمیل کامل نیست؛ نمونه: name@example.com';
	} elseif ( $email && email_exists( $email ) && email_exists( $email ) !== get_current_user_id() ) {
		$e['email'] = 'این ایمیل برای حساب دیگری ثبت شده است.';
	}
	$filled = count( array_filter( array( $d, $m, $y ) ) );
	$max    = $m && $m <= 6 ? 31 : 30;
	if ( $filled && $filled < 3 ) {
		$e['birth'] = 'روز، ماه و سال را کامل انتخاب کنید.';
	} elseif ( 3 === $filled && ( $m > 12 || $d > $max || $y > salimor_jalali_year() || $y < salimor_jalali_year() - 100 ) ) {
		$e['birth'] = sprintf( '%s %s روز دارد.', salimor_jalali_months()[ max( 0, min( 11, $m - 1 ) ) ], salimor_fa( $max ) );
	}
	if ( $e ) {
		$GLOBALS['salimor_profile_errors'] = $e;
		return;
	}
	$uid = get_current_user_id();
	wp_update_user( array( 'ID' => $uid, 'first_name' => $fn, 'last_name' => $ln, 'display_name' => trim( "$fn $ln" ), 'user_email' => $email ) );
	update_user_meta( $uid, 'salimor_birth', 3 === $filled ? sprintf( '%04d/%02d/%02d', $y, $m, $d ) : '' );
	$c = new WC_Customer( $uid );
	if ( ! $c->get_billing_first_name() ) {
		$c->set_billing_first_name( $fn );
		$c->set_billing_last_name( $ln );
	}
	if ( $email ) {
		$c->set_billing_email( $email );
	}
	$c->save();
	wc_add_notice( 'تغییرات ذخیره شد.' );
	wp_safe_redirect( wc_get_account_endpoint_url( 'edit-account' ) );
	exit;
} );

/* After logging in from the account page, go back where the shopper came from. */
add_filter( 'woocommerce_login_redirect', function ( $to ) {
	return isset( $_REQUEST['redirect_to'] ) ? wp_validate_redirect( wp_unslash( $_REQUEST['redirect_to'] ), $to ) : $to; // phpcs:ignore
} );

/* Mobile number in the admin user list and profile. */
add_filter( 'manage_users_columns', function ( $c ) {
	$c['salimor_mobile'] = 'موبایل';
	return $c;
} );
add_filter( 'manage_users_custom_column', function ( $v, $col, $uid ) {
	return 'salimor_mobile' === $col ? esc_html( get_user_meta( $uid, 'salimor_mobile', true ) ) : $v;
}, 10, 3 );

/* view-order.php shows the items itself. */
remove_action( 'woocommerce_view_order', 'woocommerce_order_details_table', 10 );
