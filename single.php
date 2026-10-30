<?php
/**
 * Journal article, with products linked to it (meta box «محصولات مرتبط»).
 *
 * @package Salimor
 */

defined( 'ABSPATH' ) || exit;
get_header();
while ( have_posts() ) :
	the_post();
	$cat  = get_the_category();
	$mins = max( 1, (int) ceil( count( preg_split( '/\s+/u', wp_strip_all_tags( get_the_content() ) ) ) / 200 ) );
	?>
	<div class="wrap"><article class="article">
		<?php if ( has_post_thumbnail() ) : ?>
			<div class="cover"><?php the_post_thumbnail( 'salimor-post', array( 'alt' => '' ) ); ?><a class="ib m-only" href="<?php echo esc_url( salimor_blog_url() ); ?>" style="position:absolute;top:14px;right:14px" aria-label="بازگشت به ژورنال"><?php echo salimor_icon( 'back' ); // phpcs:ignore ?></a></div>
		<?php endif; ?>
		<div class="meta"><?php if ( $cat ) : ?><a href="<?php echo esc_url( get_category_link( $cat[0] ) ); ?>"><b><?php echo esc_html( $cat[0]->name ); ?></b></a><?php endif; ?><span class="num"><?php echo esc_html( salimor_fa( get_the_date( 'j F Y' ) ) ); ?></span><span class="num"><?php echo esc_html( salimor_fa( $mins ) . ' دقیقه مطالعه' ); ?></span></div>
		<h1><?php the_title(); ?></h1>
		<div class="prose"><?php the_content(); ?></div>
		<div class="byline"><div class="av" aria-hidden="true"><?php echo esc_html( mb_substr( get_the_author(), 0, 1 ) ); ?></div><div><b><?php the_author(); ?></b><small>تیم محتوای <?php bloginfo( 'name' ); ?></small></div></div>
	</article></div>
	<?php
	if ( function_exists( 'wc_get_product' ) ) :
		$ids   = array_filter( array_map( 'absint', explode( ',', (string) get_post_meta( get_the_ID(), '_salimor_products', true ) ) ) );
		$prods = array_filter( array_map( 'wc_get_product', $ids ), fn( $p ) => $p && $p->is_visible() );
		$kick  = 'در این نوشته';
		if ( ! $prods ) {
			$prods = salimor_bestsellers( 4 );
			$kick  = 'پیشنهاد ' . get_bloginfo( 'name' );
		}
		if ( $prods ) :
			?>
			<section class="related-s"><div class="wrap">
				<?php salimor_section_head( $kick, 'محصولات مرتبط', salimor_shop_url(), 'همه محصولات' ); ?>
				<div class="rail"><?php foreach ( $prods as $p ) { salimor_product_card( $p ); } ?></div>
			</div></section>
			<?php
		endif;
	endif;
endwhile;
get_footer();
