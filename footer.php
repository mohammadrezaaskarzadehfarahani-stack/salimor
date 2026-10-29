<?php
/**
 * Footer, bottom tab bar (phones), cart drawer, support menu and toast.
 *
 * @package Salimor
 */

defined( 'ABSPATH' ) || exit;
$has_wc = class_exists( 'WooCommerce' );
$tab    = salimor_current_tab();
$tg     = salimor_opt( 'telegram' );
$ig     = salimor_opt( 'instagram' );
$bale   = salimor_opt( 'bale' );
$chat   = trim( (string) salimor_opt( 'chat_code' ) );
?>
</main>

<footer class="site">
	<div class="f-tiles" aria-hidden="true"></div>
	<div class="wrap">
		<div class="f-top">
			<div class="f-brand">
				<a class="logo" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php salimor_svg( 'logo' ); ?><span><b><?php bloginfo( 'name' ); ?></b><small aria-hidden="true">SALIMOR</small></span></a>
				<p><?php echo esc_html( salimor_opt( 'brand_text' ) ); ?></p>
				<?php if ( $tg || $ig || $bale ) : ?>
					<div class="f-chips">
						<?php if ( $ig ) : ?><a href="<?php echo esc_url( 'https://instagram.com/' . $ig ); ?>" rel="noopener" target="_blank">اینستاگرام <span class="ltr-i">@<?php echo esc_html( $ig ); ?></span></a><?php endif; ?>
						<?php if ( $tg ) : ?><a href="<?php echo esc_url( 'https://t.me/' . $tg ); ?>" rel="noopener" target="_blank">تلگرام <span class="ltr-i">@<?php echo esc_html( $tg ); ?></span></a><?php endif; ?>
						<?php if ( $bale ) : ?><a href="<?php echo esc_url( 'https://ble.ir/' . $bale ); ?>" rel="noopener" target="_blank">بله <span class="ltr-i">@<?php echo esc_html( $bale ); ?></span></a><?php endif; ?>
					</div>
				<?php endif; ?>
			</div>
			<div class="f-links">
				<details class="f-acc"><summary>فروشگاه</summary>
					<?php
					$shop = array();
					if ( $has_wc ) {
						foreach ( salimor_top_categories( 4 ) as $t ) {
							$shop[ $t->name ] = get_term_link( $t );
						}
						$shop['همه محصولات'] = salimor_shop_url();
					}
					salimor_footer_menu( 'footer_1', $shop );
					?>
				</details>
				<details class="f-acc"><summary>راهنمای خرید</summary>
					<?php salimor_footer_menu( 'footer_2', array( 'ژورنال' => salimor_blog_url() ) ); ?>
				</details>
				<details class="f-acc"><summary>سالیمور</summary>
					<?php
					$about = array();
					if ( $has_wc ) {
						$about['حساب کاربری'] = wc_get_page_permalink( 'myaccount' );
					}
					$privacy = get_privacy_policy_url();
					if ( $privacy ) {
						$about['حریم خصوصی'] = $privacy;
					}
					salimor_footer_menu( 'footer_3', $about );
					?>
				</details>
			</div>
		</div>

		<div class="f-contact">
			<?php if ( salimor_opt( 'phone' ) ) : ?>
				<div><span class="ic"><?php echo salimor_icon( 'phone' ); // phpcs:ignore ?></span><div><small>تلفن پشتیبانی</small><b class="num"><?php echo esc_html( salimor_opt( 'phone' ) ); ?></b></div></div>
			<?php endif; ?>
			<?php if ( salimor_opt( 'email' ) ) : ?>
				<div><span class="ic"><?php echo salimor_icon( 'mail' ); // phpcs:ignore ?></span><div><small>ایمیل</small><b class="ltr-i"><?php echo esc_html( salimor_opt( 'email' ) ); ?></b></div></div>
			<?php endif; ?>
			<?php if ( salimor_opt( 'hours' ) ) : ?>
				<div><span class="ic"><?php echo salimor_icon( 'clock' ); // phpcs:ignore ?></span><div><small>ساعت پاسخگویی</small><b><?php echo esc_html( salimor_opt( 'hours' ) ); ?></b></div></div>
			<?php endif; ?>
		</div>

		<div class="f-trust">
			<?php
			$trust = (string) salimor_opt( 'trust_html' );
			if ( $trust ) {
				echo '<div class="seal-code">' . $trust . '</div>'; // phpcs:ignore -- admin-only setting (unfiltered_html).
			} elseif ( current_user_can( 'edit_theme_options' ) ) {
				echo '<div class="seal">' . salimor_icon( 'shield' ) . '<span>کد نماد اعتماد و ساماندهی را در «سفارشی‌سازی ← تنظیمات سالیمور» وارد کنید (این پیام فقط برای مدیر دیده می‌شود).</span></div>'; // phpcs:ignore
			}
			?>
		</div>
		<div class="fb"><span>© <?php echo esc_html( salimor_fa( wp_date( 'Y' ) ) . ' ' . get_bloginfo( 'name' ) ); ?>. همه حقوق محفوظ است.</span></div>
	</div>
</footer>

<nav class="tabbar" aria-label="ناوبری اپ">
	<ul>
		<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>" data-tab="home"<?php echo 'home' === $tab ? ' aria-current="page"' : ''; ?>><?php echo salimor_icon( 'home' ); // phpcs:ignore ?>خانه</a></li>
		<?php if ( $has_wc ) : ?>
		<li><a href="<?php echo esc_url( salimor_shop_url() ); ?>" data-tab="shop"<?php echo 'shop' === $tab ? ' aria-current="page"' : ''; ?>><?php echo salimor_icon( 'grid' ); // phpcs:ignore ?>فروشگاه</a></li>
		<?php endif; ?>
		<li><a href="<?php echo esc_url( home_url( '/?s=&post_type=product' ) ); ?>" data-tab="search"<?php echo 'search' === $tab ? ' aria-current="page"' : ''; ?>><?php echo salimor_icon( 'search' ); // phpcs:ignore ?>جستجو</a></li>
		<li><a href="<?php echo esc_url( salimor_blog_url() ); ?>" data-tab="blog"<?php echo 'blog' === $tab ? ' aria-current="page"' : ''; ?>><?php echo salimor_icon( 'book' ); // phpcs:ignore ?>ژورنال</a></li>
		<?php if ( $has_wc ) : $n = salimor_cart_count(); ?>
		<li><button id="cartTab" type="button" aria-label="<?php echo esc_attr( $n ? 'سبد خرید، ' . salimor_fa( $n ) . ' کالا' : 'سبد خرید' ); ?>" aria-controls="sheet" aria-expanded="false" data-cart-open><?php echo salimor_icon( 'bag' ); // phpcs:ignore ?>سبد<span class="dot num" data-count <?php echo $n ? '' : 'hidden'; ?>><?php echo esc_html( salimor_fa( $n ) ); ?></span></button></li>
		<?php endif; ?>
	</ul>
</nav>

<?php if ( $has_wc ) : ?>
<div class="scrim" id="scrim"></div>
<aside class="sheet" id="sheet" role="dialog" aria-modal="true" aria-labelledby="sheet-t" aria-hidden="true">
	<div class="grab" aria-hidden="true"></div>
	<div class="sh-h"><h2 id="sheet-t" style="font-size:22px">سبد خرید</h2><button class="ib" type="button" data-close aria-label="بستن"><?php echo salimor_icon( 'close' ); // phpcs:ignore ?></button></div>
	<div class="sheet-inner" id="sheetInner"><?php echo salimor_cart_drawer_inner(); // phpcs:ignore ?></div>
</aside>
<?php endif; ?>

<?php if ( $chat || $tg || $ig || $bale ) : ?>
<button class="chat-fab" id="chatFab" type="button" aria-label="پشتیبانی آنلاین" aria-controls="supMenu" aria-expanded="false"><?php echo salimor_icon( 'chatdots' ); // phpcs:ignore ?><svg class="fx" viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg><i class="chat-dot" aria-hidden="true"></i></button>
<div class="sup-scrim" id="supScrim" hidden></div>
<div class="sup-menu" id="supMenu" hidden aria-label="راه‌های تماس با پشتیبانی">
	<p class="sup-t">از کدام راه با ما در ارتباط باشید؟</p>
	<?php if ( $chat ) : ?>
		<button class="sup-i" type="button" data-sup="chat"><span class="si c1"><?php echo salimor_icon( 'chat' ); // phpcs:ignore ?></span><span><b>چت آنلاین</b><small>همین‌جا، پاسخ در چند دقیقه</small></span></button>
	<?php endif; ?>
	<?php if ( $tg ) : ?>
		<a class="sup-i" href="<?php echo esc_url( 'https://t.me/' . $tg ); ?>" target="_blank" rel="noopener"><span class="si c2"><?php echo salimor_icon( 'telegram' ); // phpcs:ignore ?></span><span><b>تلگرام</b><small class="ltr-i">@<?php echo esc_html( $tg ); ?></small></span></a>
	<?php endif; ?>
	<?php if ( $ig ) : ?>
		<a class="sup-i" href="<?php echo esc_url( 'https://ig.me/m/' . $ig ); ?>" target="_blank" rel="noopener"><span class="si c3"><?php echo salimor_icon( 'instagram' ); // phpcs:ignore ?></span><span><b>دایرکت اینستاگرام</b><small class="ltr-i">@<?php echo esc_html( $ig ); ?></small></span></a>
	<?php endif; ?>
	<?php if ( $bale ) : ?>
		<a class="sup-i" href="<?php echo esc_url( 'https://ble.ir/' . $bale ); ?>" target="_blank" rel="noopener"><span class="si c4"><?php echo salimor_icon( 'bale' ); // phpcs:ignore ?></span><span><b>پیام‌رسان بله</b><small class="ltr-i">@<?php echo esc_html( $bale ); ?></small></span></a>
	<?php endif; ?>
</div>
<?php endif; ?>

<div class="install-hint" id="installHint" hidden role="dialog" aria-labelledby="ih-t">
	<span class="ih-ic"><?php salimor_svg( 'logo' ); ?></span>
	<div><b id="ih-t">نصب اپ <?php bloginfo( 'name' ); ?></b><small id="ihText">با یک لمس، روی صفحه‌ی اصلی گوشی.</small></div>
	<button class="btn b-ink" type="button" id="ihBtn">نصب</button>
	<button class="ib" type="button" id="ihX" aria-label="بستن"><?php echo salimor_icon( 'close' ); // phpcs:ignore ?></button>
</div>

<div class="toast" id="toast" role="status" aria-live="polite"></div>

<?php
if ( $chat ) {
	echo $chat; // phpcs:ignore -- admin-only setting (unfiltered_html): the live-chat provider's embed code.
}
wp_footer();
?>
</body>
</html>
