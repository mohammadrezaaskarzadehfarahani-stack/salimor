<?php
/**
 * Fallback template (journal listing, archives, search). Dedicated templates follow in the next phases.
 *
 * @package Salimor
 */

defined( 'ABSPATH' ) || exit;
get_header();
?>
<div class="wrap info">
	<div class="ph">
		<h1><?php
		if ( is_home() ) {
			echo esc_html( get_the_title( (int) get_option( 'page_for_posts' ) ) ?: 'ژورنال' );
		} elseif ( is_search() ) {
			echo esc_html( 'نتیجه‌ی جستجو برای «' . get_search_query() . '»' );
		} else {
			the_archive_title();
		}
		?></h1>
	</div>
	<?php if ( have_posts() ) : ?>
		<?php get_template_part( 'template-parts/journal-mag', null, array( 'posts' => $GLOBALS['wp_query']->posts ) ); ?>
		<?php the_posts_pagination( array( 'mid_size' => 1 ) ); ?>
	<?php else : ?>
		<div class="empty box"><p>چیزی پیدا نشد.</p></div>
	<?php endif; ?>
</div>
<?php
get_footer();
