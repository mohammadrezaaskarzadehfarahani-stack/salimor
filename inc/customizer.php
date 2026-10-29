<?php
/**
 * Store settings editable from Appearance → Customize → «تنظیمات سالیمور».
 *
 * @package Salimor
 */

defined( 'ABSPATH' ) || exit;

/** Defaults in one place so templates never print an empty string. */
function salimor_defaults() {
	return array(
		'hero_kicker'    => 'کالکشن پاییز ۱۴۰۵ رسید',
		'hero_title'     => 'اکسسوری‌هایی برای هر روزِ شما',
		'hero_text'      => 'انگشتر و گردنبند نقره، ساعت و کیف چرم، قاب و هندزفری؛ همه در یک‌جا، با طراحی‌ای که هر روز دوستش دارید.',
		'hero_btn'       => 'مشاهده محصولات',
		'hero_btn2'      => 'تخفیف‌های پاییزه',
		'brand_text'     => 'اکسسوری با الهام از کاشی‌های فیروزه‌ای و طاق‌نماهای یزد.',
		'phone'          => '۰۲۱-۹۱۰۰۰۰۰۰',
		'email'          => 'hello@salimor.ir',
		'hours'          => 'شنبه تا پنجشنبه، ۹ تا ۱۸',
		'telegram'       => 'salimor',
		'instagram'      => 'salimor',
		'bale'           => 'salimor',
		'chat_code'      => '',
		'trust_html'     => '',
		'free_ship_min'  => 2000000,
		'sale_kicker'    => 'حراج پاییزه',
		'perk1_t'        => 'ارسال یک‌روزه',
		'perk1_d'        => 'تهران؛ سایر شهرها ۲ تا ۴ روز',
		'perk2_t'        => 'ضمانت عیار',
		'perk2_d'        => 'درج عیار روی فاکتور',
		'perk3_t'        => '۷ روز بازگشت',
		'perk3_d'        => 'تعویض سایز رایگان',
		'perk4_t'        => 'جعبه هدیه',
		'perk4_d'        => 'با کارت دست‌نویس',
		'pdp_ship'       => 'تهران یک‌روزه، سایر شهرها ۲ تا ۴ روز کاری. تا ۷ روز پس از تحویل می‌توانید کالا را تعویض یا مرجوع کنید.',
		'size_guide'     => '',
	);
}

/** Read a setting with its default. */
function salimor_opt( $key ) {
	$d = salimor_defaults();
	return get_theme_mod( 'salimor_' . $key, $d[ $key ] ?? '' );
}

add_action( 'customize_register', function ( WP_Customize_Manager $c ) {
	$c->add_panel( 'salimor', array( 'title' => 'تنظیمات سالیمور', 'priority' => 20 ) );

	$sections = array(
		'salimor_hero'    => array( 'بخش اول صفحه‌ی اصلی', array(
			'hero_kicker' => array( 'برچسب بالای عنوان', 'text' ),
			'hero_title'  => array( 'عنوان', 'text' ),
			'hero_text'   => array( 'توضیح', 'textarea' ),
			'hero_btn'    => array( 'دکمه‌ی اصلی', 'text' ),
			'hero_btn2'   => array( 'دکمه‌ی دوم (به بخش تخفیف‌ها)', 'text' ),
			'sale_kicker' => array( 'برچسب بخش تخفیف‌دارها', 'text' ),
		) ),
		'salimor_contact' => array( 'اطلاعات تماس و شبکه‌ها', array(
			'brand_text' => array( 'معرفی کوتاه در فوتر', 'textarea' ),
			'phone'      => array( 'تلفن پشتیبانی', 'text' ),
			'email'      => array( 'ایمیل', 'email' ),
			'hours'      => array( 'ساعت پاسخگویی', 'text' ),
			'telegram'   => array( 'آیدی تلگرام (بدون @)', 'text' ),
			'instagram'  => array( 'آیدی اینستاگرام (بدون @)', 'text' ),
			'bale'       => array( 'آیدی بله (بدون @)', 'text' ),
		) ),
		'salimor_perks'   => array( 'مزیت‌ها', array(
			'perk1_t' => array( 'مزیت ۱', 'text' ), 'perk1_d' => array( 'توضیح ۱', 'text' ),
			'perk2_t' => array( 'مزیت ۲', 'text' ), 'perk2_d' => array( 'توضیح ۲', 'text' ),
			'perk3_t' => array( 'مزیت ۳', 'text' ), 'perk3_d' => array( 'توضیح ۳', 'text' ),
			'perk4_t' => array( 'مزیت ۴', 'text' ), 'perk4_d' => array( 'توضیح ۴', 'text' ),
		) ),
		'salimor_store'   => array( 'فروشگاه', array(
			'free_ship_min' => array( 'حداقل خرید برای ارسال رایگان (تومان، فقط برای نمایش نوار پیشرفت در سبد)', 'number' ),
			'pdp_ship'      => array( 'متن «ارسال و بازگشت» در صفحه‌ی محصول', 'textarea' ),
			'size_guide'    => array( 'لینک راهنمای سایز (کنار گزینه‌ی سایز نمایش داده می‌شود)', 'url' ),
		) ),
	);

	foreach ( $sections as $sid => list( $title, $fields ) ) {
		$c->add_section( $sid, array( 'title' => $title, 'panel' => 'salimor' ) );
		foreach ( $fields as $key => list( $label, $type ) ) {
			$sanitize = 'textarea' === $type ? 'sanitize_textarea_field' : ( 'number' === $type ? 'absint' : ( 'email' === $type ? 'sanitize_email' : ( 'url' === $type ? 'esc_url_raw' : 'sanitize_text_field' ) ) );
			$c->add_setting( 'salimor_' . $key, array( 'default' => salimor_defaults()[ $key ], 'sanitize_callback' => $sanitize ) );
			$c->add_control( 'salimor_' . $key, array( 'label' => $label, 'section' => $sid, 'type' => $type ) );
		}
	}

	/* Code snippets: only users allowed to post unfiltered HTML may edit these. */
	$c->add_section( 'salimor_code', array( 'title' => 'چت آنلاین و نمادها', 'panel' => 'salimor', 'description' => 'کد چت آنلاین (مثلاً رایچت یا گفتینو) و کد نماد اعتماد/ساماندهی را از سایت همان سرویس کپی کنید.' ) );
	foreach ( array( 'chat_code' => 'کد چت آنلاین', 'trust_html' => 'کد نماد اعتماد الکترونیکی و ساماندهی' ) as $key => $label ) {
		$c->add_setting( 'salimor_' . $key, array(
			'default'           => '',
			'capability'        => 'unfiltered_html',
			'sanitize_callback' => fn( $v ) => current_user_can( 'unfiltered_html' ) ? $v : wp_kses_post( $v ),
		) );
		$c->add_control( 'salimor_' . $key, array( 'label' => $label, 'section' => 'salimor_code', 'type' => 'textarea' ) );
	}
} );
