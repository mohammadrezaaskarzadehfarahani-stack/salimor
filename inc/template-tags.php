<?php
/**
 * Small template helpers.
 *
 * @package Salimor
 */

defined( 'ABSPATH' ) || exit;

/** Persian digits for numbers we print ourselves (prices come from WooCommerce). */
function salimor_fa( $n ) {
	return strtr( (string) $n, array( '0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴', '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹' ) );
}

/** Include an SVG partial from template-parts. */
function salimor_svg( $name ) {
	get_template_part( 'template-parts/svg', $name );
}

/** Section heading row used across the home page. */
function salimor_section_head( $kicker, $title, $more_url = '', $more_label = 'همه', $title_extra = '' ) {
	?>
	<div class="sh2">
		<div>
			<?php if ( $kicker ) : ?><p class="kick"><?php echo esc_html( $kicker ); ?></p><?php endif; ?>
			<h2<?php echo $title_extra ? ' class="with-pom"' : ''; ?>><?php echo $title_extra; // phpcs:ignore -- trusted SVG partial. ?><?php echo esc_html( $title ); ?></h2>
		</div>
		<?php if ( $more_url ) : ?>
			<a class="more" href="<?php echo esc_url( $more_url ); ?>"><?php echo esc_html( $more_label ); ?> <?php echo salimor_icon( 'arrow' ); // phpcs:ignore ?></a>
		<?php endif; ?>
	</div>
	<?php
}

/** URL of the shop page, with fallback when WooCommerce is off. */
function salimor_shop_url() {
	return function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
}

/** URL of the journal (posts page). */
function salimor_blog_url() {
	$id = (int) get_option( 'page_for_posts' );
	return $id ? get_permalink( $id ) : home_url( '/?post_type=post' );
}

/** Which bottom tab is active on this request. */
function salimor_current_tab() {
	if ( is_front_page() ) {
		return 'home';
	}
	if ( is_search() ) {
		return 'search';
	}
	if ( is_home() || is_singular( 'post' ) || is_category() || is_tag() ) {
		return 'blog';
	}
	if ( function_exists( 'is_woocommerce' ) && ( is_woocommerce() || is_product() ) ) {
		return 'shop';
	}
	return '';
}

/** Footer menu with a sensible fallback list. */
function salimor_footer_menu( $location, $fallback ) {
	if ( has_nav_menu( $location ) ) {
		wp_nav_menu( array( 'theme_location' => $location, 'container' => false, 'depth' => 1, 'items_wrap' => '<ul>%3$s</ul>' ) );
		return;
	}
	echo '<ul>';
	foreach ( $fallback as $label => $url ) {
		printf( '<li><a href="%s">%s</a></li>', esc_url( $url ), esc_html( $label ) );
	}
	echo '</ul>';
}
