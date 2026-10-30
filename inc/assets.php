<?php
/**
 * Front-end assets: local fonts, stylesheet, script and its data.
 *
 * @package Salimor
 */

defined( 'ABSPATH' ) || exit;

add_action( 'wp_enqueue_scripts', function () {
	$css = SALIMOR_DIR . '/assets/css/main.css';
	$js  = SALIMOR_DIR . '/assets/js/main.js';

	wp_enqueue_style( 'salimor', SALIMOR_URI . '/assets/css/main.css', array(), (string) filemtime( $css ) );
	wp_enqueue_script( 'salimor', SALIMOR_URI . '/assets/js/main.js', array(), (string) filemtime( $js ), array( 'strategy' => 'defer', 'in_footer' => true ) );

	$data = array(
		'home'        => home_url( '/' ),
		'freeShipMin' => (int) salimor_opt( 'free_ship_min' ),
		'defaultSort' => function_exists( 'WC' ) ? apply_filters( 'woocommerce_default_catalog_orderby', get_option( 'woocommerce_default_catalog_orderby', 'date' ) ) : 'date',
		'i18n'        => array(
			'added'      => 'به سبد اضافه شد',
			'removed'    => 'از سبد خرید حذف شد',
			'wishOn'     => 'به علاقه‌مندی‌ها اضافه شد',
			'wishOff'    => 'از علاقه‌مندی‌ها حذف شد',
			'error'      => 'مشکلی پیش آمد؛ دوباره تلاش کنید.',
			'cart'       => 'سبد خرید',
			'cartN'      => 'سبد خرید، %s کالا',
		),
	);
	if ( class_exists( 'WooCommerce' ) ) {
		$data['wcAjax']   = WC_AJAX::get_endpoint( '%%endpoint%%' );
		$data['cartUrl']  = wc_get_cart_url();
		$data['checkout'] = wc_get_checkout_url();
		$data['nonce']    = wp_create_nonce( 'salimor-cart' );
		$data['ajax']     = admin_url( 'admin-ajax.php' );
	}
	wp_add_inline_script( 'salimor', 'window.SALIMOR=' . wp_json_encode( $data ) . ';', 'before' );
} );

/* Preload the variable body font so text renders in Vazirmatn on first paint. */
add_action( 'wp_head', function () {
	printf( '<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n", esc_url( SALIMOR_URI . '/assets/fonts/vazirmatn-var.woff2' ) );
	echo '<meta name="theme-color" content="#8EDDD6">' . "\n";
}, 1 );

/* The theme styles every WooCommerce screen itself. */
add_filter( 'woocommerce_enqueue_styles', '__return_empty_array' );
