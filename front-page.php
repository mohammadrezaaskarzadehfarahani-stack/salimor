<?php
/**
 * Home page: hero, category panels, product shelves, perks, journal.
 *
 * @package Salimor
 */

defined( 'ABSPATH' ) || exit;
get_header();

$has_wc = class_exists( 'WooCommerce' );
?>
<section class="hero"><div class="wrap">
	<?php if ( $has_wc ) : ?>
		<form class="search-pill m-only" role="search" action="<?php echo esc_url( home_url( '/' ) ); ?>">
			<?php echo salimor_icon( 'search' ); // phpcs:ignore ?>
			<label class="sr" for="heroSearch">جستجوی محصول</label>
			<input id="heroSearch" type="search" name="s" placeholder="جستجو در انگشتر، ساعت، قاب…" enterkeyhint="search">
			<input type="hidden" name="post_type" value="product">
		</form>
	<?php endif; ?>
	<div class="hero-t">
		<div class="ht-copy">
			<p class="ht-kick"><?php echo esc_html( salimor_opt( 'hero_kicker' ) ); ?></p>
			<h1><?php echo esc_html( salimor_opt( 'hero_title' ) ); ?></h1>
			<p class="ht-lead"><?php echo esc_html( salimor_opt( 'hero_text' ) ); ?></p>
			<div class="row">
				<a class="btn ht-main" href="<?php echo esc_url( salimor_shop_url() ); ?>"><?php echo esc_html( salimor_opt( 'hero_btn' ) ); ?> <?php echo salimor_icon( 'arrow' ); // phpcs:ignore ?></a>
				<?php if ( $has_wc && wc_get_product_ids_on_sale() ) : ?>
					<a class="btn ht-ghost" href="#s-sale" data-scroll="s-sale"><?php echo esc_html( salimor_opt( 'hero_btn2' ) ); ?></a>
				<?php endif; ?>
			</div>
		</div>
		<div class="ht-visual ht-orosi"><?php salimor_svg( 'orosi' ); ?></div>
	</div>
</div></section>

<?php if ( $has_wc ) :
	$cats = salimor_top_categories( 3 );
	if ( $cats ) : ?>
	<section style="padding-top:8px"><div class="wrap">
		<?php salimor_section_head( 'دسته‌بندی‌ها', 'دنیای ' . get_bloginfo( 'name' ), salimor_shop_url() ); ?>
		<div class="catx">
			<?php foreach ( $cats as $i => $t ) : ?>
				<a class="cx c<?php echo esc_attr( $i % 3 + 1 ); ?>" href="<?php echo esc_url( get_term_link( $t ) ); ?>">
					<span class="cx-txt"><b><?php echo esc_html( $t->name ); ?></b><?php if ( $t->description ) : ?><span><?php echo esc_html( wp_strip_all_tags( $t->description ) ); ?></span><?php endif; ?></span>
					<span class="cx-go" aria-hidden="true"><?php echo salimor_icon( 'arrow' ); // phpcs:ignore ?></span>
					<span class="cx-img"><?php echo salimor_term_image( $t ); // phpcs:ignore ?></span>
				</a>
			<?php endforeach; ?>
		</div>
		<?php
		$subs = get_terms( array( 'taxonomy' => 'product_cat', 'parent__in' => wp_list_pluck( $cats, 'term_id' ), 'hide_empty' => true, 'number' => 12, 'orderby' => 'count', 'order' => 'DESC' ) );
		if ( $subs && ! is_wp_error( $subs ) ) : ?>
			<div class="subcats" aria-label="زیردسته‌ها">
				<?php foreach ( $subs as $s ) : ?>
					<a class="sc press" href="<?php echo esc_url( get_term_link( $s ) ); ?>"><span><?php echo salimor_term_image( $s, 'thumbnail' ); // phpcs:ignore ?></span><?php echo esc_html( $s->name ); ?></a>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div></section>
	<?php endif; ?>

	<?php
	$new  = salimor_products( array( 'orderby' => 'date', 'order' => 'DESC', 'limit' => 8 ) );
	$best = salimor_bestsellers( 8 );
	list( $sale, $sale_end, $sale_count ) = salimor_on_sale( 8 );
	$shelves = array_filter( array(
		's-new'  => $new ? array( 'تازه‌ها', count( $new ) ) : null,
		's-best' => $best ? array( 'پرفروش‌ها', count( $best ) ) : null,
		's-sale' => $sale ? array( 'تخفیف‌دارها', $sale_count ) : null,
	) );
	if ( $shelves ) : ?>
	<div class="shelves">
		<?php if ( count( $shelves ) > 1 ) : ?>
		<nav class="shelf-nav" aria-label="قفسه‌های محصولات"><div class="wrap"><div class="seg shelf-seg" role="group"><span class="pill" aria-hidden="true"></span>
			<?php $first = true; foreach ( $shelves as $id => list( $label, $n ) ) : ?>
				<button type="button" data-shelf="<?php echo esc_attr( $id ); ?>" aria-pressed="<?php echo $first ? 'true' : 'false'; ?>"><?php echo esc_html( $label ); ?> <small class="num"><?php echo esc_html( salimor_fa( $n ) ); ?></small></button>
			<?php $first = false; endforeach; ?>
		</div></div></nav>
		<?php endif; ?>

		<?php if ( $new ) : ?>
		<section class="shelf" id="s-new"><div class="wrap">
			<?php salimor_section_head( 'تازه رسیده', 'تازه‌های فصل', add_query_arg( 'orderby', 'date', salimor_shop_url() ) ); ?>
			<?php if ( count( $cats ) > 1 ) : ?>
				<div style="margin-bottom:18px"><div class="seg" role="group" aria-label="فیلتر دسته"><span class="pill" aria-hidden="true"></span>
					<button type="button" data-hseg="" aria-pressed="true">همه</button>
					<?php foreach ( $cats as $t ) : ?><button type="button" data-hseg="<?php echo esc_attr( $t->slug ); ?>" aria-pressed="false"><?php echo esc_html( $t->name ); ?></button><?php endforeach; ?>
				</div></div>
			<?php endif; ?>
			<div class="grid g4" id="homeGrid"><?php foreach ( $new as $p ) { salimor_product_card( $p ); } ?></div>
			<p class="empty" id="homeGridEmpty" hidden>در این دسته هنوز محصول تازه‌ای نیست.</p>
		</div></section>
		<?php endif; ?>

		<?php if ( $best ) : ?>
		<section class="shelf band" id="s-best"><div class="wrap">
			<?php salimor_section_head( 'محبوب‌ترین‌ها', 'پرفروش‌ترین‌ها', add_query_arg( 'orderby', 'popularity', salimor_shop_url() ) ); ?>
			<div class="rail"><?php foreach ( $best as $p ) { salimor_product_card( $p ); } ?></div>
		</div></section>
		<?php endif; ?>

		<?php if ( $sale ) :
			ob_start();
			salimor_svg( 'pomegranate' );
			$pom = ob_get_clean(); ?>
		<section class="shelf" id="s-sale"><div class="wrap">
			<div class="sale-head" id="sale">
				<div class="sh2" style="margin:0"><div><p class="kick" style="color:var(--brick)"><?php echo esc_html( salimor_opt( 'sale_kicker' ) ); ?></p><h2 class="with-pom"><?php echo $pom; // phpcs:ignore ?>تخفیف‌دارها</h2></div><a class="more" href="<?php echo esc_url( add_query_arg( 'on_sale', '1', salimor_shop_url() ) ); ?>">همه <?php echo salimor_icon( 'arrow' ); // phpcs:ignore ?></a></div>
				<?php if ( $sale_end ) : ?>
					<div class="cd" role="timer" aria-label="زمان باقی‌مانده تا پایان حراج"><span>تا پایان حراج</span><div class="cd-box num" data-end="<?php echo esc_attr( $sale_end ); ?>"></div></div>
				<?php endif; ?>
			</div>
			<div class="grid g4"><?php foreach ( $sale as $p ) { salimor_product_card( $p ); } ?></div>
		</div></section>
		<?php endif; ?>
	</div>
	<?php endif; ?>

	<section><div class="wrap"><div class="perks">
		<?php foreach ( array( array( 'truck', 1 ), array( 'shield', 2 ), array( 'ret', 3 ), array( 'gift', 4 ) ) as list( $ic, $k ) ) :
			if ( ! salimor_opt( "perk{$k}_t" ) ) {
				continue;
			} ?>
			<div class="perk"><?php echo salimor_icon( $ic ); // phpcs:ignore ?><div><b><?php echo esc_html( salimor_opt( "perk{$k}_t" ) ); ?></b><span><?php echo esc_html( salimor_opt( "perk{$k}_d" ) ); ?></span></div></div>
		<?php endforeach; ?>
	</div></div></section>
	<div class="tile-div" aria-hidden="true"></div>
<?php endif; ?>

<?php
$posts = get_posts( array( 'numberposts' => 4, 'post_status' => 'publish', 'ignore_sticky_posts' => true ) );
if ( $posts ) : ?>
<section style="padding-top:0"><div class="wrap">
	<?php salimor_section_head( 'ژورنال', 'خواندنی‌ها', salimor_blog_url() ); ?>
	<?php get_template_part( 'template-parts/journal-mag', null, array( 'posts' => $posts ) ); ?>
</div></section>
<?php endif; ?>

<?php
get_footer();
