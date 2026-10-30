<?php
/**
 * Template Name: درباره ما (سالیمور)
 *
 * The text before the «بیشتر» (More) block sits beside the orosi window; the rest follows below.
 *
 * @package Salimor
 */

defined( 'ABSPATH' ) || exit;
get_header();
while ( have_posts() ) :
	the_post();
	$raw   = str_replace( array( '<!-- wp:more -->', '<!-- /wp:more -->' ), '', get_post_field( 'post_content', get_the_ID() ) );
	$parts = get_extended( $raw );
	$main  = apply_filters( 'the_content', $parts['main'] ); // phpcs:ignore
	$rest  = $parts['extended'] ? apply_filters( 'the_content', $parts['extended'] ) : ''; // phpcs:ignore
	$cpage = get_page_by_path( 'contact' );
	?>
	<div class="wrap info page-c about-pg">
		<div class="ph">
			<nav class="crumbs" aria-label="مسیر صفحه"><a href="<?php echo esc_url( home_url( '/' ) ); ?>">خانه</a><span aria-hidden="true">/</span><span><?php the_title(); ?></span></nav>
			<h1><?php the_title(); ?></h1>
			<?php if ( has_excerpt() ) : ?><p><?php echo esc_html( get_the_excerpt() ); ?></p><?php endif; ?>
		</div>
		<section class="ab-intro">
			<div class="ab-art" aria-hidden="true"><?php salimor_svg( 'orosi' ); ?></div>
			<div class="ab-text entry"><?php echo $main; // phpcs:ignore ?></div>
		</section>
		<?php if ( $rest ) : ?><div class="entry"><?php echo $rest; // phpcs:ignore ?></div><?php endif; ?>
		<section class="cta-band">
			<div><h2>سؤالی دارید؟</h2><p>از پیگیری سفارش تا مشاوره‌ی سایز، تیم پشتیبانی کنار شماست.</p></div>
			<div class="row">
				<?php if ( $cpage ) : ?><a class="btn b-ink" href="<?php echo esc_url( get_permalink( $cpage ) ); ?>">تماس با ما</a><?php endif; ?>
				<a class="btn b-line" href="<?php echo esc_url( salimor_shop_url() ); ?>">مشاهده محصولات</a>
			</div>
		</section>
	</div>
	<?php
endwhile;
get_footer();
