<?php
/**
 * Generic page. WooCommerce cart, checkout and account pages print their own headings.
 *
 * @package Salimor
 */

defined( 'ABSPATH' ) || exit;
get_header();
$wc_page = function_exists( 'is_cart' ) && ( is_cart() || is_checkout() || is_account_page() );
while ( have_posts() ) :
	the_post();
	if ( $wc_page ) :
		?>
		<div class="wrap wc-page"><?php the_content(); ?></div>
		<?php
	else :
		?>
		<div class="wrap info narrow page-c">
			<div class="ph">
				<nav class="crumbs" aria-label="مسیر صفحه"><a href="<?php echo esc_url( home_url( '/' ) ); ?>">خانه</a><span aria-hidden="true">/</span><span><?php the_title(); ?></span></nav>
				<h1><?php the_title(); ?></h1>
				<?php if ( has_excerpt() ) : ?><p><?php echo esc_html( get_the_excerpt() ); ?></p><?php endif; ?>
			</div>
			<?php
			$legal_ids = array_filter( array( function_exists( 'wc_terms_and_conditions_page_id' ) ? wc_terms_and_conditions_page_id() : 0, (int) get_option( 'wp_page_for_privacy_policy' ) ) );
			if ( 2 === count( $legal_ids ) && in_array( get_the_ID(), $legal_ids, true ) ) :
				?>
				<nav class="seg legal-tabs" aria-label="قوانین"><span class="pill" aria-hidden="true"></span>
					<?php foreach ( $legal_ids as $lid ) : ?>
						<a href="<?php echo esc_url( get_permalink( $lid ) ); ?>" <?php echo get_the_ID() === $lid ? 'aria-current="page"' : ''; ?>><?php echo esc_html( get_the_title( $lid ) ); ?></a>
					<?php endforeach; ?>
				</nav>
			<?php endif; ?>
			<div class="entry legal"><?php the_content(); ?></div>
		</div>
		<?php
	endif;
endwhile;
get_footer();
