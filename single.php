<?php
/**
 * Journal article.
 *
 * @package Salimor
 */

defined( 'ABSPATH' ) || exit;
get_header();
while ( have_posts() ) :
	the_post();
	$cat = get_the_category();
	?>
	<div class="wrap"><article class="article">
		<?php if ( has_post_thumbnail() ) : ?><div class="cover"><?php the_post_thumbnail( 'salimor-post', array( 'alt' => '' ) ); ?></div><?php endif; ?>
		<div class="meta"><?php if ( $cat ) : ?><b><?php echo esc_html( $cat[0]->name ); ?></b><?php endif; ?><span class="num"><?php echo esc_html( salimor_fa( get_the_date( 'j F Y' ) ) ); ?></span></div>
		<h1><?php the_title(); ?></h1>
		<div class="prose"><?php the_content(); ?></div>
		<div class="byline"><div class="av"><?php echo esc_html( mb_substr( get_the_author(), 0, 1 ) ); ?></div><div><b><?php the_author(); ?></b><small>تیم محتوای <?php bloginfo( 'name' ); ?></small></div></div>
	</article></div>
	<?php
endwhile;
get_footer();
