<?php
/**
 * Installable web app (PWA): manifest, service worker, offline page, icons.
 *
 * The service worker is served from the site root (/?salimor_sw=1) so it can control every page.
 * It never caches HTML, the cart, checkout or account pages: only static theme assets and images,
 * plus a small offline page shown when the network is unavailable.
 *
 * @package Salimor
 */

defined( 'ABSPATH' ) || exit;

function salimor_pwa_url( $what ) {
	return add_query_arg( 'salimor_' . $what, '1', home_url( '/' ) );
}

add_action( 'wp_head', function () {
	$img = SALIMOR_URI . '/assets/img/';
	printf( '<link rel="manifest" href="%s">' . "\n", esc_url( salimor_pwa_url( 'manifest' ) ) );
	printf( '<link rel="apple-touch-icon" href="%s">' . "\n", esc_url( $img . 'apple-touch-icon.png' ) );
	if ( ! has_site_icon() ) {
		printf( '<link rel="icon" href="%s" type="image/png">' . "\n", esc_url( $img . 'icon-192.png' ) );
	}
	echo '<meta name="mobile-web-app-capable" content="yes">' . "\n";
	echo '<meta name="apple-mobile-web-app-capable" content="yes">' . "\n";
	echo '<meta name="apple-mobile-web-app-status-bar-style" content="default">' . "\n";
	printf( '<meta name="apple-mobile-web-app-title" content="%s">' . "\n", esc_attr( get_bloginfo( 'name' ) ) );
}, 2 );

add_action( 'template_redirect', function () {
	// phpcs:disable WordPress.Security.NonceVerification.Recommended -- read-only public endpoints.
	if ( isset( $_GET['salimor_manifest'] ) ) {
		salimor_pwa_manifest();
	}
	if ( isset( $_GET['salimor_sw'] ) ) {
		salimor_pwa_sw();
	}
	if ( isset( $_GET['salimor_offline'] ) ) {
		salimor_pwa_offline();
	}
	// phpcs:enable
}, 0 );

function salimor_pwa_manifest() {
	$img = SALIMOR_URI . '/assets/img/';
	$m   = array(
		'id'               => '/',
		'name'             => get_bloginfo( 'name' ),
		'short_name'       => get_bloginfo( 'name' ),
		'description'      => get_bloginfo( 'description' ),
		'lang'             => 'fa',
		'dir'              => 'rtl',
		'start_url'        => home_url( '/?source=pwa' ),
		'scope'            => home_url( '/' ),
		'display'          => 'standalone',
		'orientation'      => 'portrait',
		'background_color' => '#F1F9F7',
		'theme_color'      => '#8EDDD6',
		'icons'            => array(
			array( 'src' => $img . 'icon-192.png', 'sizes' => '192x192', 'type' => 'image/png' ),
			array( 'src' => $img . 'icon-512.png', 'sizes' => '512x512', 'type' => 'image/png' ),
			array( 'src' => $img . 'maskable-512.png', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'maskable' ),
		),
	);
	if ( class_exists( 'WooCommerce' ) ) {
		$m['shortcuts'] = array(
			array( 'name' => 'فروشگاه', 'url' => wc_get_page_permalink( 'shop' ) ),
			array( 'name' => 'سبد خرید', 'url' => wc_get_cart_url() ),
			array( 'name' => 'سفارش‌های من', 'url' => wc_get_account_endpoint_url( 'orders' ) ),
		);
	}
	nocache_headers();
	header( 'Content-Type: application/manifest+json; charset=utf-8' );
	echo wp_json_encode( $m, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
	exit;
}

function salimor_pwa_sw() {
	$version = SALIMOR_VERSION . '-' . filemtime( SALIMOR_DIR . '/assets/css/main.css' ) . '-' . filemtime( SALIMOR_DIR . '/assets/js/main.js' );
	$precache = array(
		salimor_pwa_url( 'offline' ),
		SALIMOR_URI . '/assets/fonts/vazirmatn-var.woff2',
		SALIMOR_URI . '/assets/fonts/lalezar-arabic.woff2',
		SALIMOR_URI . '/assets/img/icon-192.png',
	);
	$never = array( '/wp-admin', '/wp-login', 'wc-ajax', 'add-to-cart', '/cart', '/checkout', '/my-account', 'preview=', 'customize_changeset' );
	if ( class_exists( 'WooCommerce' ) ) {
		foreach ( array( wc_get_cart_url(), wc_get_checkout_url(), wc_get_page_permalink( 'myaccount' ) ) as $u ) {
			$never[] = wp_parse_url( $u, PHP_URL_PATH );
		}
	}
	header( 'Content-Type: application/javascript; charset=utf-8' );
	header( 'Service-Worker-Allowed: ' . ( wp_parse_url( home_url( '/' ), PHP_URL_PATH ) ?: '/' ) );
	header( 'Cache-Control: no-cache' );
	?>
/* Salimor service worker <?php echo esc_js( $version ); ?> */
const V = <?php echo wp_json_encode( $version ); ?>;
const STATIC = 'salimor-static-' + V, MEDIA = 'salimor-media';
const PRECACHE = <?php echo wp_json_encode( array_values( $precache ) ); ?>;
const OFFLINE = <?php echo wp_json_encode( salimor_pwa_url( 'offline' ) ); ?>;
const NEVER = <?php echo wp_json_encode( array_values( array_unique( array_filter( $never ) ) ) ); ?>;

self.addEventListener('install', e => { e.waitUntil(caches.open(STATIC).then(c => c.addAll(PRECACHE)).then(() => self.skipWaiting())); });
self.addEventListener('activate', e => {
  e.waitUntil(caches.keys().then(keys => Promise.all(keys.filter(k => k.startsWith('salimor-static-') && k !== STATIC).map(k => caches.delete(k)))).then(() => self.clients.claim()));
});

const trim = async (name, max) => { const c = await caches.open(name), k = await c.keys(); for (let i = 0; i < k.length - max; i++) await c.delete(k[i]); };

self.addEventListener('fetch', e => {
  const r = e.request, u = new URL(r.url);
  if (r.method !== 'GET' || u.origin !== location.origin || NEVER.some(p => u.pathname.startsWith(p) || u.search.includes(p))) return;

  /* Pages: always from the network (prices, stock and the cart must be live); offline page as fallback. */
  if (r.mode === 'navigate') {
    e.respondWith(fetch(r).catch(() => caches.match(OFFLINE)));
    return;
  }
  /* Theme files, fonts and product images: fast from cache, refreshed in the background. */
  if (/\/wp-content\/(themes|uploads)\//.test(u.pathname) || /\.(woff2|css|js|png|jpe?g|webp|avif|svg)$/.test(u.pathname)) {
    const bucket = u.pathname.includes('/uploads/') ? MEDIA : STATIC;
    e.respondWith(caches.open(bucket).then(async c => {
      const hit = await c.match(r);
      const net = fetch(r).then(res => { if (res.ok) { c.put(r, res.clone()); if (bucket === MEDIA) trim(MEDIA, 120); } return res; }).catch(() => hit);
      return hit || net;
    }));
  }
});
	<?php
	exit;
}

function salimor_pwa_offline() {
	nocache_headers();
	header( 'Content-Type: text/html; charset=utf-8' );
	$css = SALIMOR_URI . '/assets/css/main.css';
	?>
<!doctype html>
<html lang="fa" dir="rtl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<title>اتصال برقرار نیست · <?php echo esc_html( get_bloginfo( 'name' ) ); ?></title>
<link rel="stylesheet" href="<?php echo esc_url( $css ); ?>"></head>
<body><main class="wrap info narrow"><div class="done-card box nf" style="margin-top:12vh">
<div class="nf-art"><?php salimor_svg( 'orosi' ); ?></div>
<h1>اتصال به اینترنت برقرار نیست</h1>
<p class="muted">اتصال گوشی را بررسی کنید و دوباره تلاش کنید. سبد خرید شما ذخیره شده است.</p>
<div class="row"><button class="btn b-ink" onclick="location.reload()">تلاش دوباره</button></div>
</div></main></body></html>
	<?php
	exit;
}

/* Register the worker on the front end only. */
add_action( 'wp_footer', function () {
	if ( is_admin() || is_customize_preview() ) {
		return;
	}
	?>
<script>if('serviceWorker' in navigator){addEventListener('load',()=>navigator.serviceWorker.register(<?php echo wp_json_encode( salimor_pwa_url( 'sw' ) ); ?>,{scope:<?php echo wp_json_encode( wp_parse_url( home_url( '/' ), PHP_URL_PATH ) ?: '/' ); ?>}).catch(()=>{}));}</script>
	<?php
}, 99 );
