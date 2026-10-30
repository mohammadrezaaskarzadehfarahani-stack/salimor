<?php
/**
 * Contact form: messages are stored under «پیام‌ها» in the dashboard and e-mailed to the store.
 *
 * @package Salimor
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', function () {
	register_post_type( 'salimor_message', array(
		'labels'          => array( 'name' => 'پیام‌های تماس', 'singular_name' => 'پیام', 'menu_name' => 'پیام‌ها', 'edit_item' => 'پیام', 'search_items' => 'جستجوی پیام‌ها', 'not_found' => 'پیامی نیست.' ),
		'public'          => false,
		'show_ui'         => true,
		'show_in_menu'    => true,
		'menu_icon'       => 'dashicons-email-alt',
		'menu_position'   => 56,
		'supports'        => array( 'title', 'editor' ),
		'capability_type' => 'post',
		'capabilities'    => array( 'create_posts' => 'do_not_allow' ),
		'map_meta_cap'    => true,
	) );
} );

add_action( 'template_redirect', function () {
	if ( empty( $_POST['salimor_contact'] ) ) {
		return;
	}
	$e = array();
	if ( ! isset( $_POST['_slmnonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['_slmnonce'] ), 'salimor_contact' ) ) {
		$e['form'] = 'صفحه را دوباره باز کنید و دوباره بفرستید.';
	}
	if ( ! empty( $_POST['website'] ) ) { // honeypot: bots fill every field
		wp_safe_redirect( add_query_arg( 'sent', wp_rand( 100000, 999999 ), get_permalink() ) . '#ctf' );
		exit;
	}
	$name   = sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) );
	$mobile = salimor_mobile( wp_unslash( $_POST['mobile'] ?? '' ) ); // phpcs:ignore
	$topic  = sanitize_text_field( wp_unslash( $_POST['topic'] ?? '' ) );
	$order  = preg_replace( '/\D/', '', salimor_en_digits( wp_unslash( $_POST['order'] ?? '' ) ) ); // phpcs:ignore
	$msg    = sanitize_textarea_field( wp_unslash( $_POST['message'] ?? '' ) );
	if ( mb_strlen( $name ) < 3 ) {
		$e['name'] = 'نام و نام خانوادگی را وارد کنید.';
	}
	if ( ! $mobile ) {
		$e['mobile'] = 'شماره موبایل ۱۱ رقمی و با ۰۹ شروع می‌شود؛ مثل ۰۹۱۲۱۲۳۴۵۶۷.';
	}
	if ( mb_strlen( $msg ) < 10 ) {
		$e['message'] = 'پیام کوتاه است؛ دست‌کم ۱۰ نویسه بنویسید.';
	}
	if ( ! $e && ! salimor_hit( 'c' . salimor_client_ip(), 5, HOUR_IN_SECONDS ) ) {
		$e['form'] = 'تعداد پیام‌ها زیاد بود؛ کمی بعد دوباره بفرستید یا تماس بگیرید.';
	}
	if ( $e ) {
		$GLOBALS['salimor_contact_errors'] = $e;
		return;
	}
	$id   = wp_insert_post( array(
		'post_type'    => 'salimor_message',
		'post_status'  => 'private',
		'post_title'   => $topic . ' — ' . $name,
		'post_content' => "نام: $name\nموبایل: $mobile\nموضوع: $topic\n" . ( $order ? "شماره سفارش: $order\n" : '' ) . "\n$msg",
	) );
	$ref  = $id ? 1000 + $id : wp_rand( 100000, 999999 );
	$body = "پیام تازه از فرم تماس سایت (پیگیری $ref)\n\nنام: $name\nموبایل: $mobile\nموضوع: $topic\n" . ( $order ? "شماره سفارش: $order\n" : '' ) . "\n$msg\n\n" . admin_url( 'edit.php?post_type=salimor_message' );
	wp_mail( salimor_opt( 'email' ) ? salimor_opt( 'email' ) : get_option( 'admin_email' ), 'پیام تماس: ' . $topic, $body );
	wp_safe_redirect( add_query_arg( 'sent', $ref, get_permalink() ) . '#ctf' );
	exit;
} );
