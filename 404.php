<?php
/**
 * Not found.
 *
 * @package Salimor
 */

defined( 'ABSPATH' ) || exit;
get_header();
?>
<div class="wrap info narrow"><div class="done-card box nf">
	<div class="nf-art"><?php salimor_svg( 'orosi' ); ?></div>
	<h1>این صفحه پیدا نشد</h1>
	<p class="muted">ممکن است نشانی اشتباه وارد شده باشد یا صفحه جابه‌جا شده باشد.</p>
	<div class="row"><a class="btn b-ink" href="<?php echo esc_url( home_url( '/' ) ); ?>">صفحه‌ی اصلی</a><a class="btn b-line" href="<?php echo esc_url( salimor_shop_url() ); ?>">مشاهده محصولات</a></div>
</div></div>
<?php
get_footer();
