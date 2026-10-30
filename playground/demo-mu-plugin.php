<?php
/**
 * Plugin Name: Salimor Playground demo fixes
 * Description: Only for the WordPress Playground demo (SQLite in the browser). Not part of the theme.
 */

/* Playground's SQLite layer cannot run WooCommerce's stock-hold query; real hosting (MySQL) does not need this. */
add_filter( 'woocommerce_hold_stock_for_checkout', '__return_false' );

/* A short banner so visitors know this is a demo. */
add_action( 'wp_body_open', function () {
	echo '<div style="background:#0B2E33;color:#8EDDD6;text-align:center;font-size:13px;padding:6px 12px;position:relative;z-index:100">نسخه‌ی نمایشی — پرداخت واقعی انجام نمی‌شود</div>';
} );
