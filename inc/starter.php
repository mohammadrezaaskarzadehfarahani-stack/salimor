<?php
/**
 * One-click setup (نمایش ← راه‌اندازی سالیمور): creates the information pages with editable starter
 * text, footer menus, classic cart/checkout pages and the recommended store settings.
 * Existing pages and menus are never overwritten.
 *
 * Also: [salimor_shipping_table] lists the store's real shipping methods and prices.
 *
 * @package Salimor
 */

defined( 'ABSPATH' ) || exit;

/* ---------------------------------------------------------------------------
 * Shipping table shortcode (always matches WooCommerce → Shipping)
 * ------------------------------------------------------------------------- */
add_shortcode( 'salimor_shipping_table', function () {
	if ( ! class_exists( 'WC_Shipping_Zones' ) ) {
		return '';
	}
	$rows = array();
	foreach ( WC_Shipping_Zones::get_zones() as $z ) {
		foreach ( $z['shipping_methods'] as $m ) {
			if ( 'yes' !== $m->enabled ) {
				continue;
			}
			list( $name, $desc ) = salimor_split_label( $m->get_title() );
			$cost                = 'flat_rate' === $m->id ? (float) salimor_en_digits( $m->get_option( 'cost' ) ) : null;
			$rows[ $name ]       = array( $name, $desc, null === $cost ? '—' : ( $cost > 0 ? salimor_num( $cost ) . ' تومان' : 'رایگان' ) );
		}
	}
	if ( ! $rows ) {
		return '<p class="box note">روش‌های ارسال هنوز تعریف نشده‌اند.</p>';
	}
	ob_start();
	?>
	<div class="tbl-wrap"><table class="tbl"><thead><tr><th scope="col">روش</th><th scope="col">زمان تحویل</th><th scope="col">هزینه</th></tr></thead><tbody>
	<?php foreach ( $rows as list( $n, $d, $c ) ) : ?>
		<tr><th scope="row"><?php echo esc_html( $n ); ?></th><td><?php echo esc_html( $d ? $d : '—' ); ?></td><td class="num"><?php echo esc_html( $c ); ?></td></tr>
	<?php endforeach; ?>
	</tbody></table></div>
	<?php if ( (int) salimor_opt( 'free_ship_min' ) && salimor_opt( 'free_ship_apply' ) ) : ?>
		<p class="box note">برای سفارش‌های بالای <b class="num"><?php echo salimor_num( salimor_opt( 'free_ship_min' ) ); // phpcs:ignore ?></b> تومان، ارسال با هر روشی رایگان است.</p>
	<?php endif; ?>
	<?php
	return ob_get_clean();
} );

/* ---------------------------------------------------------------------------
 * Starter content
 * ------------------------------------------------------------------------- */
function salimor_blocks_p( $t, $class = '' ) {
	return $class ? "<!-- wp:paragraph {\"className\":\"$class\"} -->\n<p class=\"$class\">$t</p>\n<!-- /wp:paragraph -->\n\n" : "<!-- wp:paragraph -->\n<p>$t</p>\n<!-- /wp:paragraph -->\n\n";
}
function salimor_blocks_h( $t, $level = 2 ) {
	$attr = 2 === $level ? '' : " {\"level\":$level}";
	return "<!-- wp:heading$attr -->\n<h$level class=\"wp-block-heading\">$t</h$level>\n<!-- /wp:heading -->\n\n";
}
function salimor_blocks_steps( array $items ) {
	$li = '';
	foreach ( $items as list( $b, $s ) ) {
		$li .= "<!-- wp:list-item -->\n<li><strong>$b</strong> $s</li>\n<!-- /wp:list-item -->\n";
	}
	return "<!-- wp:list {\"ordered\":true,\"className\":\"steps\"} -->\n<ol class=\"wp-block-list steps\">$li</ol>\n<!-- /wp:list -->\n\n";
}
function salimor_blocks_details( $q, $a ) {
	return "<!-- wp:details -->\n<details class=\"wp-block-details\"><summary>$q</summary><!-- wp:paragraph -->\n<p>$a</p>\n<!-- /wp:paragraph --></details>\n<!-- /wp:details -->\n\n";
}

function salimor_starter_pages() {
	$brand = get_bloginfo( 'name' );
	$vals  = '';
	foreach ( array( array( 'متریال واقعی', 'عیار نقره و طلا روی فاکتور درج می‌شود و چرم‌ها طبیعی‌اند. هر ادعایی که می‌کنیم قابل بررسی است.' ), array( 'ماندگاری', 'قطعه‌هایی انتخاب می‌کنیم که با گذشت زمان زیباتر شوند، نه این‌که بعد از یک فصل کنار گذاشته شوند.' ), array( 'خرید آسوده', 'هفت روز مهلت بازگشت، تعویض سایز رایگان و پشتیبانی که واقعاً جواب می‌دهد.' ) ) as list( $h, $p ) ) {
		$vals .= "<!-- wp:column {\"className\":\"val\"} -->\n<div class=\"wp-block-column val\">" . salimor_blocks_h( $h, 3 ) . salimor_blocks_p( $p ) . "</div>\n<!-- /wp:column -->\n";
	}
	$faq = '';
	foreach ( array(
		'سفارش و پرداخت'  => array( array( 'چطور سفارش ثبت کنم؟', 'محصول را به سبد اضافه کنید، از سبد خرید «ادامه و پرداخت» را بزنید، آدرس و روش ارسال را انتخاب کنید و از طریق درگاه بانکی پرداخت کنید.' ), array( 'چه روش‌های پرداختی دارید؟', 'پرداخت آنلاین با همه‌ی کارت‌های عضو شتاب.' ), array( 'آیا ثبت‌نام برای خرید لازم است؟', 'بله؛ ورود فقط با شماره موبایل و کد تأیید انجام می‌شود و رمز عبور ندارد.' ) ),
		'ارسال'           => array( array( 'هزینه‌ی ارسال چقدر است؟', 'هزینه‌ی هر روش در صفحه‌ی «ارسال و بازگشت» آمده است. برای سفارش‌های بالای حد ارسال رایگان، ارسال رایگان است.' ), array( 'سفارشم کی می‌رسد؟', 'تهران با پیک فردا تحویل می‌شود؛ سایر شهرها با پست پیشتاز ۲ تا ۴ روز کاری و با تیپاکس ۱ تا ۳ روز کاری.' ), array( 'چطور سفارشم را پیگیری کنم؟', 'از حساب کاربری، بخش سفارش‌ها؛ کد رهگیری مرسوله هم همان‌جا نمایش داده می‌شود.' ) ),
		'بازگشت و تعویض' => array( array( 'تا چند روز می‌توانم کالا را برگردانم؟', 'تا ۷ روز پس از تحویل، به شرط سالم بودن کالا و بسته‌بندی.' ), array( 'تعویض سایز انگشتر هزینه دارد؟', 'خیر؛ تعویض سایز تا ۷ روز رایگان است.' ) ),
		'نگهداری محصول'  => array( array( 'نقره‌ام تیره شده؛ چه کنم؟', 'با دستمال مخصوص نقره تمیزش کنید و از خمیردندان استفاده نکنید. راهنمای کامل در ژورنال آمده است.' ), array( 'گارانتی لوازم دیجیتال چطور است؟', 'هندزفری و شارژرها ۱۲ ماه گارانتی تعویض دارند.' ) ),
	) as $group => $qs ) {
		$faq .= salimor_blocks_h( $group );
		foreach ( $qs as list( $q, $a ) ) {
			$faq .= salimor_blocks_details( $q, $a );
		}
	}
	$legal = function ( array $items ) {
		$out = '';
		foreach ( $items as list( $h, $p ) ) {
			$out .= salimor_blocks_h( $h ) . salimor_blocks_p( $p );
		}
		return $out;
	};
	return array(
		'about'    => array(
			'title'    => 'داستان ' . $brand,
			'excerpt'  => $brand . ' فروشگاه اکسسوری‌های روزمره است؛ زیورآلات نقره، ساعت و کیف چرم و لوازم جانبی دیجیتال، با رنگ‌ها و نقش‌هایی که از معماری ایرانی گرفته‌ایم.',
			'template' => 'page-templates/about.php',
			'content'  => salimor_blocks_h( 'چرا ' . $brand . '؟' )
				. salimor_blocks_p( 'ما دنبال اکسسوری‌هایی بودیم که هم هر روز بشود از آن‌ها استفاده کرد و هم سال‌ها بمانند. چیزی که در بازار پیدا می‌کردیم یا ارزان و کم‌دوام بود یا گران و دور از دسترس.' )
				. salimor_blocks_p( $brand . ' را ساختیم تا این فاصله را پر کنیم: قطعه‌هایی با متریال درست، قیمت منصفانه و طراحی‌ای که ریشه در فیروزه‌ای کاشی‌ها و طاق‌نماهای ایران دارد.' )
				. "<!-- wp:more -->\n<!--more-->\n<!-- /wp:more -->\n\n"
				. salimor_blocks_h( 'آنچه برایمان مهم است' )
				. "<!-- wp:columns {\"className\":\"vals\"} -->\n<div class=\"wp-block-columns vals\">$vals</div>\n<!-- /wp:columns -->\n\n"
				. salimor_blocks_h( 'از کارگاه تا خانه‌ی شما' )
				. salimor_blocks_steps( array( array( 'انتخاب', 'هر قطعه را پیش از ورود به فروشگاه از نزدیک می‌بینیم و امتحان می‌کنیم.' ), array( 'کنترل کیفیت', 'عیارسنجی نقره، بررسی دوخت چرم و تست شارژ لوازم دیجیتال.' ), array( 'بسته‌بندی', 'جعبه‌ی هدیه، کیسه‌ی پارچه‌ای و کارت دست‌نویس.' ), array( 'ارسال', 'تهران یک‌روزه با پیک، سایر شهرها با پست پیشتاز یا تیپاکس.' ) ) ),
		),
		'contact'  => array(
			'title'    => 'تماس با ما',
			'excerpt'  => 'پاسخ پیام‌ها در ساعات کاری حداکثر تا ۲۴ ساعت داده می‌شود. برای پیگیری سفارش، شماره‌ی سفارش را همراه داشته باشید.',
			'template' => 'page-templates/contact.php',
			'content'  => '',
		),
		'faq'      => array(
			'title'   => 'پرسش‌های پرتکرار',
			'excerpt' => 'پاسخ سؤال‌های رایج درباره‌ی سفارش، ارسال، بازگشت کالا و نگهداری محصولات.',
			'content' => $faq . salimor_blocks_p( 'پاسخ سؤالتان را پیدا نکردید؟ <a href="' . esc_url( home_url( '/contact/' ) ) . '">با پشتیبانی تماس بگیرید</a>.', 'box note' ),
		),
		'shipping' => array(
			'title'   => 'ارسال و بازگشت کالا',
			'excerpt' => 'هزینه و زمان هر روش ارسال، و شرایط بازگشت و تعویض.',
			'content' => salimor_blocks_h( 'روش‌های ارسال' )
				. "<!-- wp:shortcode -->\n[salimor_shipping_table]\n<!-- /wp:shortcode -->\n\n"
				. salimor_blocks_h( 'بازگشت و تعویض' )
				. salimor_blocks_steps( array( array( 'درخواست', 'تا ۷ روز پس از تحویل، از حساب کاربری یا با پشتیبانی درخواست ثبت کنید.' ), array( 'ارسال کالا', 'کالا را در بسته‌بندی اصلی برای ما بفرستید؛ برای تعویض سایز هزینه‌ی ارسال با ماست.' ), array( 'بررسی', 'کالا ظرف ۲ روز کاری بررسی می‌شود.' ), array( 'بازپرداخت یا تعویض', 'مبلغ ظرف ۳ روز کاری به کارت شما برمی‌گردد یا کالای جدید ارسال می‌شود.' ) ) )
				. salimor_blocks_p( 'گوشواره‌ها به دلایل بهداشتی فقط در صورت سالم بودن پلمب قابل بازگشت‌اند.', 'hint-line' ),
		),
		'terms'    => array(
			'title'   => 'قوانین و مقررات',
			'excerpt' => 'آخرین به‌روزرسانی: ' . salimor_fa( wp_date( 'j F Y' ) ),
			'content' => $legal( array( array( 'پذیرش شرایط', 'استفاده از ' . $brand . ' و ثبت سفارش به معنی پذیرش این شرایط است.' ), array( 'قیمت و موجودی', 'قیمت‌ها به تومان است. در صورت تمام شدن موجودی پس از پرداخت، مبلغ کامل ظرف ۲۴ ساعت بازگردانده می‌شود.' ), array( 'حساب کاربری', 'حفظ دسترسی به شماره موبایل ثبت‌شده بر عهده‌ی کاربر است.' ), array( 'بازگشت کالا', 'شرایط کامل در صفحه‌ی ارسال و بازگشت آمده است.' ) ) ),
		),
		'privacy'  => array(
			'title'   => 'حریم خصوصی',
			'excerpt' => 'آخرین به‌روزرسانی: ' . salimor_fa( wp_date( 'j F Y' ) ),
			'content' => $legal( array( array( 'اطلاعاتی که جمع می‌کنیم', 'شماره موبایل، نام، نشانی و سوابق سفارش؛ فقط برای پردازش و ارسال سفارش.' ), array( 'اطلاعاتی که جمع نمی‌کنیم', 'اطلاعات کارت بانکی در ' . $brand . ' ذخیره نمی‌شود و پرداخت مستقیم در درگاه بانک انجام می‌شود.' ), array( 'اشتراک‌گذاری', 'نشانی و شماره‌ی تماس فقط در اختیار شرکت حمل‌ونقل همان سفارش قرار می‌گیرد.' ), array( 'حذف اطلاعات', 'با درخواست از پشتیبانی، حساب و اطلاعات شما حذف می‌شود.' ) ) ),
		),
	);
}

/** Run the setup. Returns a list of what was done. */
function salimor_run_setup( $store_settings = true ) {
	$done = array();
	$ids  = array();
	foreach ( salimor_starter_pages() as $slug => $p ) {
		$existing = get_page_by_path( $slug );
		if ( $existing && 'trash' !== $existing->post_status ) {
			$ids[ $slug ] = $existing->ID;
			continue;
		}
		$id = wp_insert_post( array(
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_name'    => $slug,
			'post_title'   => $p['title'],
			'post_excerpt' => $p['excerpt'],
			'post_content' => $p['content'],
		) );
		if ( $id && ! is_wp_error( $id ) ) {
			if ( ! empty( $p['template'] ) ) {
				update_post_meta( $id, '_wp_page_template', $p['template'] );
			}
			$ids[ $slug ] = $id;
			$done[]       = 'برگه‌ی «' . $p['title'] . '» ساخته شد.';
		}
	}
	add_post_type_support( 'page', 'excerpt' );
	if ( isset( $ids['terms'] ) && ! wc_terms_and_conditions_page_id() ) {
		update_option( 'woocommerce_terms_page_id', $ids['terms'] );
		$done[] = 'برگه‌ی قوانین به ووکامرس معرفی شد.';
	}
	if ( isset( $ids['privacy'] ) ) {
		$cur = (int) get_option( 'wp_page_for_privacy_policy' );
		if ( ! $cur || 'publish' !== get_post_status( $cur ) ) {
			update_option( 'wp_page_for_privacy_policy', $ids['privacy'] );
			$done[] = 'برگه‌ی حریم خصوصی تنظیم شد.';
		}
	}
	/* Cart and checkout: classic shortcodes (the theme's designed templates). */
	foreach ( array( 'cart' => '[woocommerce_cart]', 'checkout' => '[woocommerce_checkout]' ) as $page => $sc ) {
		$pid = wc_get_page_id( $page );
		if ( $pid > 0 && false === strpos( get_post_field( 'post_content', $pid ), $sc ) ) {
			wp_update_post( array( 'ID' => $pid, 'post_content' => "<!-- wp:shortcode -->\n$sc\n<!-- /wp:shortcode -->" ) );
			$done[] = 'برگه‌ی ' . ( 'cart' === $page ? 'سبد خرید' : 'تسویه حساب' ) . ' به قالب کلاسیک تبدیل شد.';
		}
	}
	$shop = wc_get_page_id( 'shop' );
	if ( $shop > 0 && preg_match( '/^shop$/i', get_the_title( $shop ) ) ) {
		wp_update_post( array( 'ID' => $shop, 'post_title' => 'فروشگاه' ) );
	}
	/* Footer menus (only for empty locations). */
	$locations = get_theme_mod( 'nav_menu_locations', array() );
	$menus     = array(
		'footer_1' => array( 'فوتر — فروشگاه', array_merge( array_map( fn( $t ) => array( 'term', $t ), salimor_top_categories( 4 ) ), array( array( 'url', 'همه محصولات', salimor_shop_url() ) ) ) ),
		'footer_2' => array( 'فوتر — راهنمای خرید', array( array( 'page', 'shipping' ), array( 'page', 'faq' ), array( 'url', 'پیگیری سفارش', wc_get_account_endpoint_url( 'orders' ) ), array( 'url', 'ژورنال', salimor_blog_url() ) ) ),
		'footer_3' => array( 'فوتر — درباره', array( array( 'page', 'about' ), array( 'page', 'contact' ), array( 'page', 'terms' ), array( 'page', 'privacy' ) ) ),
	);
	foreach ( $menus as $loc => list( $name, $items ) ) {
		if ( ! empty( $locations[ $loc ] ) && wp_get_nav_menu_object( $locations[ $loc ] ) ) {
			continue;
		}
		$menu = wp_get_nav_menu_object( $name );
		$mid  = $menu ? $menu->term_id : wp_create_nav_menu( $name );
		if ( is_wp_error( $mid ) ) {
			continue;
		}
		if ( ! $menu ) {
			foreach ( $items as $it ) {
				if ( 'term' === $it[0] ) {
					wp_update_nav_menu_item( $mid, 0, array( 'menu-item-type' => 'taxonomy', 'menu-item-object' => 'product_cat', 'menu-item-object-id' => $it[1]->term_id, 'menu-item-status' => 'publish' ) );
				} elseif ( 'page' === $it[0] && isset( $ids[ $it[1] ] ) ) {
					wp_update_nav_menu_item( $mid, 0, array( 'menu-item-type' => 'post_type', 'menu-item-object' => 'page', 'menu-item-object-id' => $ids[ $it[1] ], 'menu-item-status' => 'publish', 'menu-item-title' => 'about' === $it[1] ? 'درباره ما' : '' ) );
				} elseif ( 'url' === $it[0] ) {
					wp_update_nav_menu_item( $mid, 0, array( 'menu-item-type' => 'custom', 'menu-item-title' => $it[1], 'menu-item-url' => $it[2], 'menu-item-status' => 'publish' ) );
				}
			}
		}
		$locations[ $loc ] = $mid;
		$done[]            = 'فهرست «' . $name . '» ساخته شد.';
	}
	set_theme_mod( 'nav_menu_locations', $locations );

	if ( $store_settings ) {
		update_option( 'woocommerce_allowed_countries', 'specific' );
		update_option( 'woocommerce_specific_allowed_countries', array( 'IR' ) );
		update_option( 'woocommerce_ship_to_countries', '' );
		update_option( 'woocommerce_ship_to_destination', 'billing_only' );
		update_option( 'woocommerce_currency', 'IRT' );
		update_option( 'woocommerce_enable_reviews', 'no' );
		$done[] = 'تنظیمات پیشنهادی فروشگاه اعمال شد (فروش در ایران، تومان، آدرس تحویل = آدرس مشتری، بدون دیدگاه محصول).';
	}
	update_option( 'salimor_setup_done', SALIMOR_VERSION );
	flush_rewrite_rules( false );
	return $done;
}

add_action( 'init', fn() => add_post_type_support( 'page', 'excerpt' ) );

add_action( 'admin_menu', function () {
	add_theme_page( 'راه‌اندازی سالیمور', 'راه‌اندازی سالیمور', 'manage_options', 'salimor-setup', function () {
		$done = get_transient( 'salimor_setup_result' );
		delete_transient( 'salimor_setup_result' );
		?>
		<div class="wrap">
			<h1>راه‌اندازی سالیمور</h1>
			<?php if ( is_array( $done ) ) : ?>
				<div class="notice notice-success"><p><b>انجام شد.</b></p><ul style="list-style:disc;padding-inline-start:20px"><?php foreach ( $done ? $done : array( 'همه‌چیز از قبل آماده بود.' ) as $d ) : ?><li><?php echo esc_html( $d ); ?></li><?php endforeach; ?></ul></div>
			<?php endif; ?>
			<p>با این دکمه، برگه‌های «درباره ما»، «تماس»، «پرسش‌های پرتکرار»، «ارسال و بازگشت»، «قوانین» و «حریم خصوصی» با متن پیش‌فرض و قابل ویرایش ساخته می‌شوند و فهرست‌های فوتر تنظیم می‌شوند. برگه‌ها و فهرست‌هایی که از قبل وجود دارند تغییر نمی‌کنند.</p>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="salimor_setup"><?php wp_nonce_field( 'salimor_setup' ); ?>
				<p><label><input type="checkbox" name="store" value="1" checked> تنظیمات پیشنهادی فروشگاه هم اعمال شود (فروش فقط در ایران، واحد تومان، آدرس تحویل همان آدرس مشتری، بدون دیدگاه محصول)</label></p>
				<?php submit_button( 'راه‌اندازی' ); ?>
			</form>
		</div>
		<?php
	} );
} );

add_action( 'admin_post_salimor_setup', function () {
	if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'salimor_setup' ) ) {
		wp_die( 'دسترسی ندارید.' );
	}
	set_transient( 'salimor_setup_result', salimor_run_setup( ! empty( $_POST['store'] ) ), 60 );
	wp_safe_redirect( admin_url( 'themes.php?page=salimor-setup' ) );
	exit;
} );

add_action( 'admin_notices', function () {
	$screen = get_current_screen();
	if ( get_option( 'salimor_setup_done' ) || ! current_user_can( 'manage_options' ) || ! $screen || ! in_array( $screen->id, array( 'dashboard', 'themes' ), true ) ) {
		return;
	}
	printf( '<div class="notice notice-info"><p>قالب سالیمور فعال است. برای ساخت برگه‌های اطلاعاتی و فهرست‌های فوتر، <a href="%s"><b>راه‌اندازی سالیمور</b></a> را اجرا کنید.</p></div>', esc_url( admin_url( 'themes.php?page=salimor-setup' ) ) );
} );
