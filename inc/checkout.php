<?php
/**
 * Cart page, checkout and order: Iranian address fields, saved address book, shipping cards,
 * store-wide free-shipping threshold, «ارسال شد» order status with tracking code.
 *
 * @package Salimor
 */

defined( 'ABSPATH' ) || exit;

/** Persian/Arabic digits → Latin digits. */
function salimor_en_digits( $s ) {
	return strtr( (string) $s, array( '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4', '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9', '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4', '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9' ) );
}

/** Normalise an Iranian mobile number to 09xxxxxxxxx, or '' if it is not one. */
function salimor_mobile( $s ) {
	$s = preg_replace( '/\D+/', '', salimor_en_digits( $s ) );
	$s = preg_replace( '/^(0098|98)/', '0', $s );
	if ( 10 === strlen( $s ) && '9' === $s[0] ) {
		$s = '0' . $s;
	}
	return preg_match( '/^09\d{9}$/', $s ) ? $s : '';
}

/* ---------------------------------------------------------------------------
 * Cart, checkout and account pages always use the theme's classic templates,
 * even when the page content holds WooCommerce blocks.
 * ------------------------------------------------------------------------- */
add_filter( 'the_content', function ( $content ) {
	if ( ! in_the_loop() || ! is_main_query() ) {
		return $content;
	}
	if ( is_cart() && ! has_shortcode( $content, 'woocommerce_cart' ) ) {
		return do_shortcode( '[woocommerce_cart]' );
	}
	if ( is_checkout() && ! has_shortcode( $content, 'woocommerce_checkout' ) ) {
		return do_shortcode( '[woocommerce_checkout]' );
	}
	return $content;
}, 1 );
add_action( 'wp', function () {
	if ( is_cart() || is_checkout() ) {
		add_filter( 'render_block_woocommerce/cart', '__return_empty_string' );
		add_filter( 'render_block_woocommerce/checkout', '__return_empty_string' );
	}
} );

/* ---------------------------------------------------------------------------
 * Address fields
 * ------------------------------------------------------------------------- */
function salimor_tune_address_fields( $f, $prefix ) {
	$set = function ( $key, $props ) use ( &$f, $prefix ) {
		if ( isset( $f[ $prefix . $key ] ) ) {
			$f[ $prefix . $key ] = array_merge( $f[ $prefix . $key ], $props );
		}
	};
	$set( 'first_name', array( 'priority' => 10, 'class' => array( 'form-row-first' ) ) );
	$set( 'last_name', array( 'priority' => 20, 'class' => array( 'form-row-last' ) ) );
	$set( 'phone', array( 'priority' => 30, 'label' => 'شماره موبایل', 'required' => true, 'class' => array( 'form-row-wide' ), 'input_class' => array( 'ltr' ), 'placeholder' => '09121234567', 'custom_attributes' => array( 'inputmode' => 'numeric', 'autocomplete' => 'tel' ) ) );
	$set( 'country', array( 'priority' => 35 ) );
	$set( 'state', array( 'priority' => 40, 'class' => array( 'form-row-first', 'address-field' ) ) );
	$set( 'city', array( 'priority' => 50, 'class' => array( 'form-row-last', 'address-field' ) ) );
	$set( 'address_1', array( 'priority' => 60, 'label' => 'نشانی کامل', 'placeholder' => 'خیابان، کوچه، پلاک و واحد' ) );
	$set( 'postcode', array( 'priority' => 70, 'label' => 'کد پستی', 'class' => array( 'form-row-first', 'address-field' ), 'input_class' => array( 'ltr' ), 'placeholder' => '۱۰ رقم، بدون خط تیره', 'custom_attributes' => array( 'inputmode' => 'numeric', 'maxlength' => '10' ) ) );
	$set( 'email', array( 'priority' => 80, 'label' => 'ایمیل (اختیاری)', 'required' => false, 'class' => array( 'form-row-last' ), 'input_class' => array( 'ltr' ) ) );
	unset( $f[ $prefix . 'address_2' ], $f[ $prefix . 'company' ] );
	return $f;
}
add_filter( 'woocommerce_billing_fields', fn( $f ) => salimor_tune_address_fields( $f, 'billing_' ), 50 );
add_filter( 'woocommerce_shipping_fields', fn( $f ) => salimor_tune_address_fields( $f, 'shipping_' ), 50 );
/* Locale defaults (Persian WooCommerce) would otherwise re-apply their own order and labels. */
add_filter( 'woocommerce_get_country_locale', function ( $locale ) {
	$locale['IR'] = array_merge(
		$locale['IR'] ?? array(),
		array(
			'state'     => array_merge( $locale['IR']['state'] ?? array(), array( 'priority' => 40, 'required' => true, 'label' => 'استان' ) ),
			'city'      => array_merge( $locale['IR']['city'] ?? array(), array( 'priority' => 50 ) ),
			'address_1' => array_merge( $locale['IR']['address_1'] ?? array(), array( 'priority' => 60 ) ),
			'postcode'  => array_merge( $locale['IR']['postcode'] ?? array(), array( 'priority' => 70, 'required' => true ) ),
		)
	);
	return $locale;
}, 50 );

foreach ( array( 'billing_phone', 'billing_postcode', 'shipping_phone', 'shipping_postcode' ) as $k ) {
	add_filter( "woocommerce_process_checkout_field_{$k}", fn( $v ) => trim( salimor_en_digits( $v ) ) );
}
add_action( 'woocommerce_after_checkout_validation', function ( $data, $errors ) {
	if ( ! empty( $data['billing_phone'] ) && ! salimor_mobile( $data['billing_phone'] ) ) {
		$errors->add( 'billing_phone_validation', 'شماره موبایل ۱۱ رقمی و با ۰۹ شروع می‌شود؛ مثل ۰۹۱۲۱۲۳۴۵۶۷.', array( 'id' => 'billing_phone' ) );
	}
	if ( ! empty( $data['billing_postcode'] ) && ! preg_match( '/^\d{10}$/', $data['billing_postcode'] ) ) {
		$errors->add( 'billing_postcode_validation', 'کد پستی ۱۰ رقم است.', array( 'id' => 'billing_postcode' ) );
	}
}, 10, 2 );
/* WooCommerce's own postcode/phone checks are for other formats. */
add_filter( 'woocommerce_validate_postcode', fn( $valid, $postcode, $country ) => 'IR' === $country ? (bool) preg_match( '/^\d{10}$/', salimor_en_digits( $postcode ) ) : $valid, 10, 3 );

/* ---------------------------------------------------------------------------
 * Address book (user meta), shared by checkout and the account page
 * ------------------------------------------------------------------------- */
function salimor_address_keys() {
	return array( 'title', 'first_name', 'last_name', 'phone', 'state', 'city', 'address_1', 'postcode' );
}

function salimor_addresses( $user_id = 0 ) {
	$user_id = $user_id ? $user_id : get_current_user_id();
	if ( ! $user_id ) {
		return array();
	}
	$list = get_user_meta( $user_id, '_salimor_addresses', true );
	$list = is_array( $list ) ? $list : array();
	if ( ! $list ) {
		/* Seed from the WooCommerce billing address the first time. */
		$c = new WC_Customer( $user_id );
		if ( $c->get_billing_address_1() ) {
			$list['a' . wp_rand( 1000, 9999 )] = array(
				'title'      => 'خانه',
				'first_name' => $c->get_billing_first_name(),
				'last_name'  => $c->get_billing_last_name(),
				'phone'      => $c->get_billing_phone(),
				'state'      => $c->get_billing_state(),
				'city'       => $c->get_billing_city(),
				'address_1'  => $c->get_billing_address_1(),
				'postcode'   => $c->get_billing_postcode(),
				'default'    => true,
			);
			update_user_meta( $user_id, '_salimor_addresses', $list );
		}
	}
	return $list;
}

function salimor_save_addresses( array $list, $user_id = 0 ) {
	$user_id = $user_id ? $user_id : get_current_user_id();
	if ( $list && ! array_filter( array_column( $list, 'default' ) ) ) {
		$list[ array_key_first( $list ) ]['default'] = true;
	}
	update_user_meta( $user_id, '_salimor_addresses', $list );
	/* Keep WooCommerce's billing address = the default address (used by emails, admin, gateways). */
	foreach ( $list as $a ) {
		if ( ! empty( $a['default'] ) ) {
			$c = new WC_Customer( $user_id );
			foreach ( array( 'first_name', 'last_name', 'phone', 'state', 'city', 'address_1', 'postcode' ) as $k ) {
				$c->{"set_billing_$k"}( $a[ $k ] ?? '' );
			}
			$c->set_billing_country( 'IR' );
			$c->save();
			break;
		}
	}
}

/** Validate posted address data. Returns [ clean data, errors[field => message] ]. */
function salimor_read_address( array $src ) {
	$a = array();
	foreach ( salimor_address_keys() as $k ) {
		$a[ $k ] = sanitize_text_field( wp_unslash( $src[ $k ] ?? '' ) );
	}
	$a['address_1'] = sanitize_textarea_field( wp_unslash( $src['address_1'] ?? '' ) );
	$a['phone']     = salimor_mobile( $a['phone'] );
	$a['postcode']  = salimor_en_digits( $a['postcode'] );
	$states         = WC()->countries->get_states( 'IR' );
	$e              = array();
	if ( '' === $a['title'] ) {
		$e['title'] = 'یک عنوان کوتاه بنویسید؛ مثل «خانه».';
	}
	if ( mb_strlen( $a['first_name'] ) < 2 ) {
		$e['first_name'] = 'نام گیرنده را وارد کنید.';
	}
	if ( mb_strlen( $a['last_name'] ) < 2 ) {
		$e['last_name'] = 'نام خانوادگی گیرنده را وارد کنید.';
	}
	if ( ! $a['phone'] ) {
		$e['phone'] = 'موبایل ۱۱ رقمی و با ۰۹ شروع می‌شود.';
	}
	if ( $states && ! isset( $states[ $a['state'] ] ) ) {
		$e['state'] = 'استان را انتخاب کنید.';
	}
	if ( '' === $a['city'] ) {
		$e['city'] = 'شهر را وارد کنید.';
	}
	if ( mb_strlen( $a['address_1'] ) < 10 ) {
		$e['address_1'] = 'نشانی را کامل بنویسید: خیابان، کوچه، پلاک و واحد.';
	}
	if ( ! preg_match( '/^\d{10}$/', $a['postcode'] ) ) {
		$e['postcode'] = 'کد پستی ۱۰ رقم است.';
	}
	return array( $a, $e );
}

/** One-line address text. */
function salimor_address_line( array $a ) {
	$states = WC()->countries->get_states( 'IR' );
	$state  = $states[ $a['state'] ?? '' ] ?? ( $a['state'] ?? '' );
	return implode( '، ', array_filter( array( $state, $a['city'] ?? '', $a['address_1'] ?? '' ) ) );
}

/* After an order, add its address to the book when it is new. */
add_action( 'woocommerce_checkout_order_processed', function ( $order_id ) {
	$uid = get_current_user_id();
	$o   = wc_get_order( $order_id );
	if ( ! $uid || ! $o ) {
		return;
	}
	$list = salimor_addresses( $uid );
	$new  = array(
		'first_name' => $o->get_billing_first_name(),
		'last_name'  => $o->get_billing_last_name(),
		'phone'      => $o->get_billing_phone(),
		'state'      => $o->get_billing_state(),
		'city'       => $o->get_billing_city(),
		'address_1'  => $o->get_billing_address_1(),
		'postcode'   => $o->get_billing_postcode(),
	);
	foreach ( $list as $a ) {
		if ( $a['address_1'] === $new['address_1'] && $a['postcode'] === $new['postcode'] ) {
			return;
		}
	}
	$new['title']   = $list ? 'آدرس ' . salimor_fa( count( $list ) + 1 ) : 'خانه';
	$new['default'] = ! $list;
	$list[ 'a' . time() ] = $new;
	salimor_save_addresses( $list, $uid );
} );

/* Prefill checkout from the default saved address. */
add_filter( 'woocommerce_checkout_get_value', function ( $value, $input ) {
	if ( null !== $value || ! is_user_logged_in() || 0 !== strpos( $input, 'billing_' ) ) {
		return $value;
	}
	foreach ( salimor_addresses() as $a ) {
		if ( ! empty( $a['default'] ) ) {
			$k = substr( $input, 8 );
			return $a[ $k ] ?? $value;
		}
	}
	return $value;
}, 10, 2 );

/* ---------------------------------------------------------------------------
 * Shipping: cards in the checkout, store-wide free shipping above the threshold
 * ------------------------------------------------------------------------- */

/** Method title «پیک تهران | تحویل فردا» → [ name, description ]. */
function salimor_split_label( $label ) {
	$parts = array_map( 'trim', explode( '|', wp_strip_all_tags( $label ), 2 ) );
	return array( $parts[0], $parts[1] ?? '' );
}

add_filter( 'woocommerce_package_rates', function ( $rates, $package ) {
	$min = (int) salimor_opt( 'free_ship_min' );
	if ( ! $min || ! salimor_opt( 'free_ship_apply' ) ) {
		return $rates;
	}
	$sum = 0;
	foreach ( $package['contents'] as $item ) {
		$sum += (float) $item['line_subtotal'] + (float) $item['line_subtotal_tax'];
	}
	if ( $sum >= $min ) {
		foreach ( $rates as $rate ) {
			$rate->set_cost( 0 );
			$rate->set_taxes( array() );
		}
	}
	return $rates;
}, 50, 2 );

/** Shipping options as selectable cards (checkout step 2). */
function salimor_checkout_shipping_html() {
	if ( ! WC()->cart || ! WC()->cart->needs_shipping() ) {
		return '<div id="ckShip"><p class="muted">این سفارش نیازی به ارسال ندارد.</p></div>';
	}
	WC()->cart->calculate_shipping();
	$chosen = WC()->session->get( 'chosen_shipping_methods' );
	ob_start();
	echo '<div id="ckShip">';
	foreach ( WC()->shipping()->get_packages() as $i => $package ) {
		$rates = $package['rates'];
		if ( ! $rates ) {
			echo '<p class="box note">' . ( WC()->customer->get_billing_state() ? 'برای این نشانی روش ارسالی تعریف نشده است؛ لطفاً با پشتیبانی تماس بگیرید.' : 'استان و شهر را وارد کنید تا روش‌های ارسال نمایش داده شوند.' ) . '</p>';
			continue;
		}
		$cur = $chosen[ $i ] ?? array_key_first( $rates );
		echo '<div class="opts" role="radiogroup" aria-label="روش ارسال" id="shipping_method">';
		foreach ( $rates as $rate ) {
			list( $name, $desc ) = salimor_split_label( $rate->get_label() );
			$cost                = (float) $rate->get_cost() + array_sum( $rate->get_taxes() );
			$id                  = 'shipping_method_' . $i . '_' . sanitize_title( $rate->get_id() );
			printf(
				'<label class="opt" for="%1$s"><input type="radio" name="shipping_method[%2$d]" data-index="%2$d" id="%1$s" value="%3$s" class="shipping_method" %4$s><span><b>%5$s</b>%6$s</span><em class="num">%7$s</em></label>',
				esc_attr( $id ),
				(int) $i,
				esc_attr( $rate->get_id() ),
				checked( $rate->get_id(), $cur, false ),
				esc_html( $name ),
				$desc ? '<small>' . esc_html( $desc ) . '</small>' : '',
				$cost > 0 ? salimor_num( $cost ) . ' تومان' : 'رایگان' // phpcs:ignore
			);
		}
		echo '</div>';
	}
	echo '</div>';
	return ob_get_clean();
}
add_filter( 'woocommerce_update_order_review_fragments', function ( $f ) {
	$f['#ckShip'] = salimor_checkout_shipping_html();
	return $f;
} );

/* ---------------------------------------------------------------------------
 * Order status «ارسال شد» + tracking code
 * ------------------------------------------------------------------------- */
add_action( 'init', function () {
	register_post_status( 'wc-shipped', array(
		'label'                     => 'ارسال شد',
		'public'                    => true,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
		'exclude_from_search'       => false,
		/* translators: %s: count */
		'label_count'               => _n_noop( 'ارسال شد <span class="count">(%s)</span>', 'ارسال شد <span class="count">(%s)</span>' ),
	) );
} );
add_filter( 'wc_order_statuses', function ( $s ) {
	$out = array();
	foreach ( $s as $k => $v ) {
		$out[ $k ] = $v;
		if ( 'wc-processing' === $k ) {
			$out['wc-shipped'] = 'ارسال شد';
		}
	}
	return $out;
} );
add_filter( 'woocommerce_order_is_paid_statuses', fn( $s ) => array_merge( $s, array( 'shipped' ) ) );
add_filter( 'woocommerce_reports_order_statuses', fn( $s ) => is_array( $s ) ? array_merge( $s, array( 'shipped' ) ) : $s );

/** Step of an order in the customer timeline: 0 ثبت، 1 آماده‌سازی، 2 ارسال، 3 تحویل; -1 = not on the timeline. */
function salimor_order_step( WC_Order $o ) {
	switch ( $o->get_status() ) {
		case 'pending':
		case 'on-hold':
			return 0;
		case 'processing':
			return 1;
		case 'shipped':
			return 2;
		case 'completed':
			return 3;
		default:
			return -1;
	}
}
function salimor_order_steps() {
	return array( 'ثبت شد', 'آماده‌سازی', 'ارسال شد', 'تحویل شد' );
}

/* Tracking code box on the order edit screen (classic and HPOS). */
add_action( 'add_meta_boxes', function () {
	$screen = function_exists( 'wc_get_page_screen_id' ) ? wc_get_page_screen_id( 'shop-order' ) : 'shop_order';
	add_meta_box( 'salimor_tracking', 'کد رهگیری مرسوله', function ( $post_or_order ) {
		$o = $post_or_order instanceof WC_Order ? $post_or_order : wc_get_order( $post_or_order->ID );
		wp_nonce_field( 'salimor_tracking', 'salimor_tracking_nonce' );
		printf( '<p><input type="text" name="salimor_tracking" value="%s" style="width:100%%;direction:ltr" placeholder="مثلاً 1234567890"></p><p class="description">پس از ذخیره، در صفحه‌ی سفارش مشتری نمایش داده می‌شود. وضعیت را روی «ارسال شد» بگذارید.</p>', esc_attr( $o ? $o->get_meta( '_salimor_tracking' ) : '' ) );
	}, $screen, 'side', 'high' );
} );
$salimor_save_tracking = function ( $order_id ) {
	if ( ! isset( $_POST['salimor_tracking_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['salimor_tracking_nonce'] ), 'salimor_tracking' ) || ! current_user_can( 'edit_shop_orders' ) ) {
		return;
	}
	$o = wc_get_order( $order_id );
	if ( $o ) {
		$o->update_meta_data( '_salimor_tracking', sanitize_text_field( wp_unslash( $_POST['salimor_tracking'] ?? '' ) ) );
		$o->save_meta_data();
	}
};
add_action( 'woocommerce_process_shop_order_meta', $salimor_save_tracking, 30 ); // before WooCommerce saves the status (40), so the «ارسال شد» SMS has the code

/* Thank-you page text: no generic WooCommerce paragraph. */
add_filter( 'woocommerce_thankyou_order_received_text', fn() => '' );

/* Checkout: «ادامه» button label and quiet coupon toggle. */
add_filter( 'woocommerce_order_button_text', fn() => 'پرداخت و ثبت سفارش' );
add_filter( 'woocommerce_checkout_coupon_message', fn() => 'کد تخفیف دارید؟ <a href="#" class="showcoupon">وارد کنید</a>' );

/* Product names in the order summary link nowhere; quantities are shown by the theme. */
add_filter( 'woocommerce_checkout_cart_item_quantity', '__return_empty_string' );

/* The theme shows its own confirmation card; keep the hook for analytics/gateways but drop the default tables. */
remove_action( 'woocommerce_thankyou', 'woocommerce_order_details_table', 10 );

/* Native selects for province/city: lighter and friendlier on phones than the search dropdown. */
add_action( 'wp_enqueue_scripts', function () {
	if ( is_checkout() || is_account_page() ) {
		wp_dequeue_script( 'selectWoo' );
		wp_dequeue_style( 'select2' );
	}
}, 100 );

/* Short Persian messages for missing fields. */
add_filter( 'woocommerce_checkout_required_field_notice', fn( $notice, $label ) => sprintf( '«%s» را وارد کنید.', wp_strip_all_tags( $label ) ), 10, 2 );

/* First checkout of a new customer: start from the account name and mobile. */
add_filter( 'woocommerce_checkout_get_value', function ( $value, $input ) {
	if ( ( null !== $value && '' !== $value ) || ! is_user_logged_in() ) {
		return $value;
	}
	$u = wp_get_current_user();
	switch ( $input ) {
		case 'billing_first_name':
			return $u->first_name ? $u->first_name : $value;
		case 'billing_last_name':
			return $u->last_name ? $u->last_name : $value;
		case 'billing_phone':
			$m = get_user_meta( $u->ID, 'salimor_mobile', true );
			return $m ? $m : $value;
	}
	return $value;
}, 20, 2 );

/* The summary already links the terms and privacy pages. */
remove_action( 'woocommerce_checkout_terms_and_conditions', 'wc_checkout_privacy_policy_text', 20 );

/* Acceptance is stated under the pay button (with links), so no separate checkbox. */
add_filter( 'woocommerce_checkout_show_terms', '__return_false' );
