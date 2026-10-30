<?php
/**
 * Journal (blog home, categories, tags, author/date archives) and site search for posts.
 *
 * @package Salimor
 */

defined( 'ABSPATH' ) || exit;
get_header();
$blog_id = (int) get_option( 'page_for_posts' );
$cur     = is_category() ? get_queried_object_id() : 0;
if ( is_home() ) {
	$title = $blog_id ? get_the_title( $blog_id ) : 'ژورنال';
	$lead  = $blog_id && has_excerpt( $blog_id ) ? get_the_excerpt( $blog_id ) : 'راهنما، متریال و الهام؛ نوشته‌هایی برای انتخاب و نگهداری بهتر.';
} elseif ( is_search() ) {
	$title = 'نتیجه‌ی جستجو برای «' . get_search_query() . '»';
	$lead  = '';
} else {
	$title = wp_strip_all_tags( get_the_archive_title() );
	$lead  = wp_strip_all_tags( get_the_archive_description() );
}
$cats = get_categories( array( 'hide_empty' => true, 'exclude' => array( (int) get_option( 'default_category' ) ) ) );
?>
<div class="wrap info journal">
	<div class="ph">
		<nav class="crumbs" aria-label="مسیر صفحه"><a href="<?php echo esc_url( home_url( '/' ) ); ?>">خانه</a><span aria-hidden="true">/</span>
			<?php if ( is_home() ) : ?><span><?php echo esc_html( $title ); ?></span><?php else : ?><a href="<?php echo esc_url( salimor_blog_url() ); ?>">ژورنال</a><?php endif; ?>
		</nav>
		<h1><?php echo esc_html( $title ); ?></h1>
		<?php if ( $lead ) : ?><p><?php echo esc_html( $lead ); ?></p><?php endif; ?>
	</div>
	<?php if ( ( is_home() || is_category() ) && count( $cats ) > 1 ) : ?>
		<nav class="seg j-seg" aria-label="موضوع‌ها"><span class="pill" aria-hidden="true"></span>
			<a href="<?php echo esc_url( salimor_blog_url() ); ?>" <?php echo $cur ? '' : 'aria-current="page"'; ?>>همه</a>
			<?php foreach ( $cats as $c ) : ?>
				<a href="<?php echo esc_url( get_category_link( $c ) ); ?>" <?php echo $cur === $c->term_id ? 'aria-current="page"' : ''; ?>><?php echo esc_html( $c->name ); ?></a>
			<?php endforeach; ?>
		</nav>
	<?php endif; ?>
	<h2 class="sr">نوشته‌ها</h2>
	<?php if ( have_posts() ) : ?>
		<?php get_template_part( 'template-parts/journal-mag', null, array( 'posts' => $GLOBALS['wp_query']->posts ) ); ?>
		<?php
		the_posts_pagination( array( 'mid_size' => 1, 'prev_text' => 'جدیدتر', 'next_text' => 'قدیمی‌تر', 'class' => 'woocommerce-pagination' ) );
		?>
	<?php else : ?>
		<div class="empty box"><?php echo salimor_icon( 'book' ); // phpcs:ignore ?><p>هنوز نوشته‌ای این‌جا نیست.</p><a class="btn b-line" href="<?php echo esc_url( salimor_blog_url() ); ?>">همه‌ی نوشته‌ها</a></div>
	<?php endif; ?>
</div>
<?php
get_footer();
