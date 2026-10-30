<?php
/**
 * Salimor theme bootstrap.
 *
 * @package Salimor
 */

defined( 'ABSPATH' ) || exit;

define( 'SALIMOR_VERSION', '1.0.0' );
define( 'SALIMOR_DIR', get_template_directory() );
define( 'SALIMOR_URI', get_template_directory_uri() );

require SALIMOR_DIR . '/inc/icons.php';
require SALIMOR_DIR . '/inc/setup.php';
require SALIMOR_DIR . '/inc/customizer.php';
require SALIMOR_DIR . '/inc/assets.php';
require SALIMOR_DIR . '/inc/template-tags.php';
require SALIMOR_DIR . '/inc/walker.php';
require SALIMOR_DIR . '/inc/pwa.php';

if ( class_exists( 'WooCommerce' ) ) {
	require SALIMOR_DIR . '/inc/woocommerce.php';
}
