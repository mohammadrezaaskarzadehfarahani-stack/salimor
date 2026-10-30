<?php
/**
 * Theme supports, menus, image sizes, page-transition and prefetch settings.
 *
 * @package Salimor
 */

defined( 'ABSPATH' ) || exit;

add_action( 'after_setup_theme', function () {
	load_theme_textdomain( 'salimor', SALIMOR_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ) );
	add_theme_support( 'custom-logo', array( 'height' => 80, 'width' => 80, 'flex-width' => true ) );
	add_theme_support( 'responsive-embeds' );

	add_theme_support( 'woocommerce', array(
		'thumbnail_image_width' => 600,
		'single_image_width'    => 1100,
		'product_grid'          => array( 'default_columns' => 4, 'default_rows' => 4 ),
	) );
	add_theme_support( 'wc-product-gallery-slider' );

	register_nav_menus( array(
		'primary'  => __( 'منوی اصلی (دسکتاپ)', 'salimor' ),
		'footer_1' => __( 'فوتر: فروشگاه', 'salimor' ),
		'footer_2' => __( 'فوتر: راهنمای خرید', 'salimor' ),
		'footer_3' => __( 'فوتر: سالیمور', 'salimor' ),
	) );

	add_image_size( 'salimor-card', 600, 750, true );
	add_image_size( 'salimor-post', 900, 675, true );
} );

/* The demo's rich block styles are not used; keep the front end light. */
add_action( 'wp_enqueue_scripts', function () {
	if ( ! is_singular( 'post' ) ) {
		wp_dequeue_style( 'wp-block-library' );
		wp_dequeue_style( 'global-styles' );
		wp_dequeue_style( 'classic-theme-styles' );
	}
}, 100 );

/* App-like navigation: prefetch the next page as soon as the finger/pointer lands on a link. */
add_filter( 'wp_speculation_rules_configuration', function ( $config ) {
	if ( is_array( $config ) ) {
		$config['mode']      = 'prefetch';
		$config['eagerness'] = 'moderate';
	}
	return $config;
} );

/* Never prefetch pages that change state. */
add_filter( 'wp_speculation_rules_href_exclude_paths', function ( $paths ) {
	$extra = array( '/*add-to-cart=*', '/*wc-ajax=*', '/*customer-logout*', '/*remove_item=*', '/*remove_coupon=*', '/*action=logout*' );
	if ( function_exists( 'wc_get_page_permalink' ) ) {
		foreach ( array( 'cart', 'checkout', 'myaccount' ) as $page ) {
			$path = wp_parse_url( wc_get_page_permalink( $page ), PHP_URL_PATH );
			if ( $path && '/' !== $path ) {
				$extra[] = untrailingslashit( $path ) . '/*';
			}
		}
	}
	return array_merge( $paths, $extra );
} );

/* Body classes used by the stylesheet. */
add_filter( 'body_class', function ( $classes ) {
	$classes[] = 'has-tabs';
	if ( function_exists( 'is_product' ) && is_product() ) {
		$classes[] = 'pdp-mode';
	}
	return $classes;
} );

/* Shorter excerpts for journal cards. */
add_filter( 'excerpt_length', fn() => 28 );
add_filter( 'excerpt_more', fn() => '…' );

/* Fallback meta description; steps aside when an SEO plugin (Yoast, Rank Math, AIOSEO) is active. */
add_action( 'wp_head', function () {
	if ( defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) || defined( 'AIOSEO_VERSION' ) ) {
		return;
	}
	if ( is_singular() && has_excerpt() ) {
		$d = get_the_excerpt();
	} elseif ( is_tax() || is_category() ) {
		$d = term_description();
	} else {
		$d = get_bloginfo( 'description' );
	}
	$d = trim( wp_strip_all_tags( (string) $d ) );
	if ( $d ) {
		printf( '<meta name="description" content="%s">' . "\n", esc_attr( wp_trim_words( $d, 30, '…' ) ) );
	}
}, 2 );

/* Journal posts: products shown under the article. */
add_action( 'add_meta_boxes_post', function () {
	add_meta_box( 'salimor_products', 'محصولات مرتبط', function ( $post ) {
		wp_nonce_field( 'salimor_products', 'salimor_products_nonce' );
		printf( '<p><input type="text" name="salimor_products" value="%s" style="width:100%%;direction:ltr" placeholder="12, 34, 56"></p><p class="description">شناسه‌ی محصولات را با ویرگول جدا کنید (شناسه در فهرست محصولات، زیر نام هر محصول دیده می‌شود). اگر خالی بماند، پرفروش‌ها نمایش داده می‌شوند.</p>', esc_attr( get_post_meta( $post->ID, '_salimor_products', true ) ) );
	}, 'post', 'side' );
} );
add_action( 'save_post_post', function ( $id ) {
	if ( ! isset( $_POST['salimor_products_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['salimor_products_nonce'] ), 'salimor_products' ) || ! current_user_can( 'edit_post', $id ) ) {
		return;
	}
	$ids = array_filter( array_map( 'absint', explode( ',', salimor_en_digits_any( sanitize_text_field( wp_unslash( $_POST['salimor_products'] ?? '' ) ) ) ) ) );
	update_post_meta( $id, '_salimor_products', implode( ',', $ids ) );
} );
function salimor_en_digits_any( $s ) {
	return function_exists( 'salimor_en_digits' ) ? salimor_en_digits( $s ) : $s;
}
