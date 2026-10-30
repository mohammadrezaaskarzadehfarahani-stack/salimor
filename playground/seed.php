<?php
/**
 * Salimor demo data for WordPress Playground.
 * Runs once from blueprint.json (after WordPress, WooCommerce, Persian WooCommerce and the theme are installed).
 */

defined( 'ABSPATH' ) || exit;

require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

$dir = __DIR__ . '/sample/';

/* demo-only fixes (stock hold on SQLite, demo banner) */
wp_mkdir_p( WP_CONTENT_DIR . '/mu-plugins' );
copy( __DIR__ . '/demo-mu-plugin.php', WP_CONTENT_DIR . '/mu-plugins/salimor-demo.php' );
wp_set_current_user( 1 );

/* ---------- store settings ---------- */
foreach ( array(
	'woocommerce_coming_soon'                    => 'no',
	'woocommerce_store_pages_only'               => 'no',
	'woocommerce_currency'                       => 'IRT',
	'woocommerce_price_num_decimals'             => 0,
	'woocommerce_default_country'                => 'IR:THR',
	'woocommerce_allowed_countries'              => 'specific',
	'woocommerce_specific_allowed_countries'     => array( 'IR' ),
	'woocommerce_ship_to_countries'              => '',
	'woocommerce_ship_to_destination'            => 'billing_only',
	'woocommerce_enable_guest_checkout'          => 'no',
	'woocommerce_enable_checkout_login_reminder' => 'no',
	'woocommerce_enable_myaccount_registration'  => 'yes',
	'woocommerce_enable_reviews'                 => 'no',
	'woocommerce_task_list_hidden'               => 'yes',
	'woocommerce_onboarding_profile'             => array( 'skipped' => true ),
	'blogname'                                   => 'سالیمور',
	'blogdescription'                            => 'اکسسوری، زیورآلات و لوازم دیجیتال با الهام از نقش‌های ایرانی',
	'permalink_structure'                        => '/%postname%/',
) as $k => $v ) {
	update_option( $k, $v );
}
delete_transient( '_wc_activation_redirect' );

$img = function ( $file, $title ) use ( $dir ) {
	static $cache = array();
	if ( isset( $cache[ $file ] ) ) {
		return $cache[ $file ];
	}
	if ( ! file_exists( $dir . $file ) ) {
		return 0;
	}
	$tmp = wp_tempnam( basename( $file ) );
	copy( $dir . $file, $tmp );
	$id = media_handle_sideload( array( 'name' => basename( $file ), 'tmp_name' => $tmp ), 0, $title );
	return $cache[ $file ] = is_wp_error( $id ) ? 0 : $id;
};
$find = function ( $title, $type ) {
	$q = get_posts( array( 'post_type' => $type, 'title' => $title, 'post_status' => 'any', 'numberposts' => 1, 'fields' => 'ids' ) );
	return $q ? $q[0] : 0;
};

/* ---------- attributes ---------- */
foreach ( array( 'color' => 'رنگ', 'material' => 'جنس', 'size' => 'سایز' ) as $slug => $label ) {
	if ( ! wc_attribute_taxonomy_id_by_name( $slug ) ) {
		wc_create_attribute( array( 'name' => $label, 'slug' => $slug, 'type' => 'select', 'order_by' => 'menu_order' ) );
	}
	register_taxonomy( 'pa_' . $slug, 'product' );
}
$term = function ( $name, $tax ) {
	$t = term_exists( $name, $tax );
	$t = $t ? $t : wp_insert_term( $name, $tax );
	return get_term( $t['term_id'], $tax );
};
foreach ( array( 'طلایی' => '#C98A1E', 'نقره‌ای' => '#C7C2B6', 'سرمه‌ای' => '#14303F', 'قهوه‌ای' => '#7A5A3E', 'کرم' => '#D3C5AD', 'تیفانی' => '#8EDDD6' ) as $name => $hex ) {
	update_term_meta( $term( $name, 'pa_color' )->term_id, 'salimor_hex', $hex );
}

/* ---------- categories ---------- */
$cat = function ( $name, $slug, $parent = 0, $desc = '', $order = 0 ) {
	$t = term_exists( $slug, 'product_cat' );
	$t = $t ? $t : wp_insert_term( $name, 'product_cat', array( 'slug' => $slug, 'parent' => $parent, 'description' => $desc ) );
	update_term_meta( $t['term_id'], 'order', $order );
	return (int) $t['term_id'];
};
$J   = $cat( 'زیورآلات', 'jewelry', 0, 'نقره ۹۲۵ و آب طلای ۱۸ عیار', 1 );
$F   = $cat( 'اکسسوری مد', 'fashion', 0, 'ساعت، کیف، عینک و شال', 2 );
$D   = $cat( 'دیجیتال', 'digital', 0, 'قاب، شارژر، هندزفری', 3 );
$sub = array(
	'ring'       => $cat( 'انگشتر', 'rings', $J ),
	'earrings'   => $cat( 'گوشواره', 'earrings', $J ),
	'necklace'   => $cat( 'گردنبند', 'necklaces', $J ),
	'bracelet'   => $cat( 'دستبند', 'bracelets', $J ),
	'watch'      => $cat( 'ساعت', 'watches', $F ),
	'sunglasses' => $cat( 'عینک', 'sunglasses', $F ),
	'bag'        => $cat( 'کیف', 'bags', $F ),
	'scarf'      => $cat( 'شال', 'scarves', $F ),
	'belt'       => $cat( 'کمربند', 'belts', $F ),
	'buds'       => $cat( 'هندزفری', 'earbuds', $D ),
	'case'       => $cat( 'قاب گوشی', 'cases', $D ),
	'charger'    => $cat( 'شارژر', 'chargers', $D ),
	'band'       => $cat( 'بند ساعت', 'watch-bands', $D ),
);
foreach ( array( 'jewelry' => 'ring', 'fashion' => 'bag', 'digital' => 'buds' ) as $slug => $art ) {
	$t = get_term_by( 'slug', $slug, 'product_cat' );
	if ( $t && ! get_term_meta( $t->term_id, 'thumbnail_id', true ) ) {
		update_term_meta( $t->term_id, 'thumbnail_id', $img( "$art.png", $t->name ) );
	}
}

/* ---------- products ---------- */
$sale_end = strtotime( '+3 weeks' );
$P        = array(
	array( 'انگشتر لونا', 'ring', 1890000, 0, array( 'طلایی', 'نقره‌ای' ), 'نقره', 128, 'نقره ۹۲۵ با آب طلای ۱۸ عیار' ),
	array( 'گوشواره قطره‌ای سورا', 'earrings', 2800000, 2450000, array( 'طلایی' ), 'نقره', 64, 'نقره ۹۲۵ با آب طلا' ),
	array( 'گردنبند آریا', 'necklace', 3400000, 2980000, array( 'طلایی', 'نقره‌ای' ), 'نقره', 91, 'نقره ۹۲۵، زنجیر ونیزی ۴۵ سانتی' ),
	array( 'دستبند چرم دون', 'bracelet', 1290000, 0, array( 'سرمه‌ای', 'قهوه‌ای' ), 'چرم', 42, 'چرم طبیعی گاوی، قفل استیل' ),
	array( 'ساعت مچی اوربیت', 'watch', 7900000, 0, array( 'سرمه‌ای', 'قهوه‌ای' ), 'استیل', 203, 'بدنه استیل ۳۱۶L، شیشه سافایر' ),
	array( 'عینک آفتابی نور', 'sunglasses', 4200000, 3650000, array( 'سرمه‌ای', 'قهوه‌ای' ), 'استات', 55, 'فریم استات، لنز پلاریزه UV400' ),
	array( 'کیف دستی میلا', 'bag', 5400000, 0, array( 'سرمه‌ای', 'کرم', 'تیفانی' ), 'چرم', 37, 'چرم طبیعی، آستر کتان' ),
	array( 'شال ابریشم یزد', 'scarf', 2500000, 2150000, array( 'کرم', 'تیفانی' ), 'ابریشم', 29, '۱۰۰٪ ابریشم، ۹۰×۹۰ سانتی' ),
	array( 'کمربند فرم', 'belt', 1900000, 1650000, array( 'سرمه‌ای', 'قهوه‌ای' ), 'چرم', 33, 'چرم طبیعی، سگک برنجی' ),
	array( 'هندزفری بی‌سیم اکو', 'buds', 4200000, 0, array( 'کرم', 'سرمه‌ای' ), 'پلاستیک و فلز', 312, 'بلوتوث ۵٫۳، ۲۸ ساعت باتری' ),
	array( 'قاب فیروزه', 'case', 690000, 0, array( 'تیفانی', 'کرم', 'سرمه‌ای' ), 'سیلیکون', 187, 'سیلیکون مات، لبه محافظ دوربین' ),
	array( 'شارژر ۳۵ وات ولت', 'charger', 1690000, 1450000, array( 'کرم', 'سرمه‌ای' ), 'پلاستیک و فلز', 98, 'GaN، خروجی USB-C' ),
	array( 'بند ساعت هوشمند لوپ', 'band', 1050000, 890000, array( 'تیفانی', 'کرم', 'سرمه‌ای' ), 'سیلیکون', 76, 'فلوروالاستومر، قفل پینی' ),
);
$gallery = array( 'ring', 'earrings', 'necklace', 'watch', 'bag', 'sunglasses', 'buds', 'case' );
foreach ( $P as $i => list( $name, $art, $reg, $sale, $cols, $mat, $sold, $desc ) ) {
	if ( $find( $name, 'product' ) ) {
		continue;
	}
	$p = new WC_Product_Simple();
	$p->set_name( $name );
	$p->set_status( 'publish' );
	$p->set_regular_price( (string) $reg );
	if ( $sale ) {
		$p->set_sale_price( (string) $sale );
		$p->set_date_on_sale_to( $sale_end );
	}
	$p->set_short_description( $desc . '. هر قطعه در جعبه‌ی هدیه‌ی سالیمور ارسال می‌شود.' );
	$p->set_description( $desc . '. طراحی شده برای استفاده‌ی روزمره، با جزئیاتی که از نزدیک دیده می‌شوند.' );
	$p->set_manage_stock( true );
	$p->set_stock_quantity( 12 );
	$p->set_sku( 'SLM-' . ( 101 + $i ) );
	$p->set_category_ids( array( $sub[ $art ] ) );
	$p->set_image_id( $img( "$art.png", $name ) );
	if ( in_array( $art, $gallery, true ) ) {
		$p->set_gallery_image_ids( array_filter( array( $img( "gal/$art-1.png", "$name ۲" ), $img( "gal/$art-2.png", "$name ۳" ), $img( "gal/$art-3.png", "$name ۴" ) ) ) );
	}
	$attrs = array();
	foreach ( array( 'pa_color' => $cols, 'pa_material' => array( $mat ) ) as $tax => $vals ) {
		$a = new WC_Product_Attribute();
		$a->set_id( wc_attribute_taxonomy_id_by_name( $tax ) );
		$a->set_name( $tax );
		$a->set_options( array_map( fn( $v ) => $term( $v, $tax )->term_id, $vals ) );
		$a->set_visible( true );
		$attrs[] = $a;
	}
	$p->set_attributes( $attrs );
	$p->set_date_created( time() - ( 13 - $i ) * DAY_IN_SECONDS * 3 );
	$id = $p->save();
	update_post_meta( $id, 'total_sales', $sold );
}

/* Two products with options (colour/size) to show the variation picker. */
$to_variable = function ( $name, array $axes, callable $prices ) use ( $find, $term ) {
	$pid = $find( $name, 'product' );
	if ( ! $pid || wc_get_product( $pid )->is_type( 'variable' ) ) {
		return;
	}
	wp_set_object_terms( $pid, 'variable', 'product_type' );
	$p      = new WC_Product_Variable( $pid );
	$attrs  = array();
	$matrix = array( array() );
	foreach ( $p->get_attributes() as $k => $a ) {
		if ( ! isset( $axes[ $k ] ) ) {
			$attrs[] = $a;
		}
	}
	foreach ( $axes as $tax => $vals ) {
		$terms = array_map( fn( $v ) => $term( $v, $tax ), $vals );
		$a     = new WC_Product_Attribute();
		$a->set_id( wc_attribute_taxonomy_id_by_name( $tax ) );
		$a->set_name( $tax );
		$a->set_options( wp_list_pluck( $terms, 'term_id' ) );
		$a->set_visible( true );
		$a->set_variation( true );
		$attrs[] = $a;
		$next    = array();
		foreach ( $matrix as $m ) {
			foreach ( $terms as $t ) {
				$next[] = $m + array( $tax => $t->slug );
			}
		}
		$matrix = $next;
	}
	$p->set_attributes( $attrs );
	$p->set_manage_stock( false );
	$p->save();
	foreach ( $matrix as $i => $m ) {
		$v = new WC_Product_Variation();
		$v->set_parent_id( $pid );
		$v->set_attributes( $m );
		list( $reg, $sale ) = $prices( $m, $i );
		$v->set_regular_price( (string) $reg );
		if ( $sale ) {
			$v->set_sale_price( (string) $sale );
		}
		$v->set_manage_stock( true );
		$v->set_stock_quantity( 3 === $i ? 0 : 8 );
		$v->save();
	}
	WC_Product_Variable::sync( $pid );
	wc_delete_product_transients( $pid );
};
$to_variable( 'انگشتر لونا', array( 'pa_color' => array( 'طلایی', 'نقره‌ای' ), 'pa_size' => array( '۵۲', '۵۴', '۵۶', '۵۸' ) ), fn( $m ) => array( false !== strpos( urldecode( $m['pa_color'] ), 'طلایی' ) ? 1890000 : 1690000, 0 ) );
$to_variable( 'کیف دستی میلا', array( 'pa_color' => array( 'سرمه‌ای', 'کرم', 'تیفانی' ) ), fn( $m, $i ) => array( 5400000, 2 === $i ? 4900000 : 0 ) );
wc_delete_product_transients();

/* ---------- shipping and payment ---------- */
if ( ! WC_Shipping_Zones::get_zones() ) {
	$z = new WC_Shipping_Zone();
	$z->set_zone_name( 'ایران' );
	$z->add_location( 'IR', 'country' );
	$z->save();
	foreach ( array( array( 'پیک تهران | تحویل فردا (فقط تهران)', 95000 ), array( 'پست پیشتاز | ۲ تا ۴ روز کاری', 75000 ), array( 'تیپاکس | ۱ تا ۳ روز کاری', 120000 ) ) as list( $t, $c ) ) {
		$iid = $z->add_shipping_method( 'flat_rate' );
		update_option( "woocommerce_flat_rate_{$iid}_settings", array( 'title' => $t, 'tax_status' => 'none', 'cost' => (string) $c ) );
	}
}
update_option( 'woocommerce_cod_settings', array( 'enabled' => 'yes', 'title' => 'پرداخت آنلاین (نسخه‌ی نمایشی)', 'description' => 'در این نسخه‌ی نمایشی پرداخت واقعی انجام نمی‌شود.', 'instructions' => '' ) );

/* ---------- pages ---------- */
$page = function ( $title, $slug ) {
	$e = get_page_by_path( $slug );
	return $e ? $e->ID : wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => $title, 'post_name' => $slug ) );
};
update_option( 'show_on_front', 'page' );
update_option( 'page_on_front', $page( 'خانه', 'home' ) );
update_option( 'page_for_posts', $page( 'ژورنال', 'journal' ) );
wp_update_post( array( 'ID' => (int) get_option( 'page_for_posts' ), 'post_excerpt' => 'راهنما، متریال و الهام؛ نوشته‌هایی برای انتخاب و نگهداری بهتر.' ) );

/* ---------- journal ---------- */
$body = str_repeat( "<!-- wp:paragraph -->\n<p>بیشتر انگشترهایی که پس فرستاده می‌شوند ایرادی ندارند؛ فقط اندازه‌شان درست نیست. برای پیدا کردن سایز دقیق لازم نیست به طلافروشی بروید. یک نخ، یک خط‌کش و چند دقیقه وقت کافی است.</p>\n<!-- /wp:paragraph -->\n\n", 5 );
foreach ( array(
	array( 'چطور سایز انگشتر را در خانه اندازه بگیریم', 'ring', 'راهنما' ),
	array( 'نقره ۹۲۵ چیست و چرا تیره می‌شود', 'necklace', 'متریال' ),
	array( 'فیروزه‌ای یزد؛ رنگی که از کاشی به کمد ما رسید', 'scarf', 'الهام' ),
	array( 'لایه‌لایه پوشیدن گردنبند بدون شلوغی', 'journal', 'استایل' ),
) as $i => list( $title, $art, $c ) ) {
	if ( $find( $title, 'post' ) ) {
		continue;
	}
	$cid = term_exists( $c, 'category' );
	$cid = $cid ? $cid : wp_insert_term( $c, 'category' );
	$id  = wp_insert_post( array( 'post_status' => 'publish', 'post_title' => $title, 'post_content' => $body, 'post_category' => array( (int) $cid['term_id'] ), 'post_date' => gmdate( 'Y-m-d H:i:s', time() - $i * 7 * DAY_IN_SECONDS ) ) );
	set_post_thumbnail( $id, $img( "$art.png", $title ) );
}
wp_delete_post( 1, true ); // «Hello world»

/* ---------- theme setup: info pages, footer menus, classic cart/checkout ---------- */
if ( function_exists( 'salimor_run_setup' ) ) {
	salimor_run_setup( true );
}
update_option( 'woocommerce_currency', 'IRT' );
flush_rewrite_rules( false );
