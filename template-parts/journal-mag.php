<?php
/**
 * Magazine layout: one lead post and a list of smaller posts.
 *
 * @package Salimor
 * @var array $args { posts: WP_Post[] }
 */

defined( 'ABSPATH' ) || exit;

$list = $args['posts'] ?? array();
if ( ! $list ) {
	return;
}
$lead = array_shift( $list );

$meta = function ( $post, $with_date ) {
	$cat  = get_the_category( $post->ID );
	$cat  = $cat ? $cat[0]->name : '';
	$mins = max( 1, (int) ceil( count( preg_split( '/\s+/u', wp_strip_all_tags( $post->post_content ) ) ) / 200 ) );
	$tail = $with_date ? salimor_fa( get_the_date( 'j F Y', $post ) ) : salimor_fa( $mins ) . ' دقیقه';
	return '<div class="meta">' . ( $cat ? '<b>' . esc_html( $cat ) . '</b>' : '' ) . '<span class="num">' . esc_html( $tail ) . '</span></div>';
};
?>
<div class="mag">
	<article class="lead-post">
		<?php if ( has_post_thumbnail( $lead ) ) : ?><div class="lp-img"><?php echo get_the_post_thumbnail( $lead, 'salimor-post', array( 'alt' => '', 'loading' => 'lazy' ) ); ?></div><?php endif; ?>
		<?php echo $meta( $lead, true ); // phpcs:ignore ?>
		<h3><a class="stretch" href="<?php echo esc_url( get_permalink( $lead ) ); ?>"><?php echo esc_html( get_the_title( $lead ) ); ?></a></h3>
		<p><?php echo esc_html( get_the_excerpt( $lead ) ); ?></p>
	</article>
	<?php if ( $list ) : ?>
	<div class="plist">
		<?php foreach ( $list as $p ) : ?>
			<article class="pitem">
				<div class="pic"><?php echo get_the_post_thumbnail( $p, 'thumbnail', array( 'alt' => '', 'loading' => 'lazy' ) ); ?></div>
				<div><?php echo $meta( $p, false ); // phpcs:ignore ?><h3><a class="stretch" href="<?php echo esc_url( get_permalink( $p ) ); ?>"><?php echo esc_html( get_the_title( $p ) ); ?></a></h3></div>
			</article>
		<?php endforeach; ?>
	</div>
	<?php endif; ?>
</div>
