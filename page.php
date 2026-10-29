<?php
/**
 * Generic page (also hosts WooCommerce cart, checkout and account until their own templates are built).
 *
 * @package Salimor
 */

defined( 'ABSPATH' ) || exit;
get_header();
while ( have_posts() ) :
	the_post();
	?>
	<div class="wrap info page-c">
		<div class="ph"><h1><?php the_title(); ?></h1></div>
		<div class="entry"><?php the_content(); ?></div>
	</div>
	<?php
endwhile;
get_footer();
