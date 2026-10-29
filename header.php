<?php
/**
 * Site header: skip link, top bar with logo, desktop navigation and actions.
 *
 * @package Salimor
 */

defined( 'ABSPATH' ) || exit;
$has_wc = class_exists( 'WooCommerce' );
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<?php salimor_svg( 'defs' ); ?>
<a class="skip" href="#main">رفتن به محتوای اصلی</a>

<header class="top" id="top">
	<div class="wrap bar">
		<a class="logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
			<?php
			if ( has_custom_logo() ) {
				echo wp_get_attachment_image( get_theme_mod( 'custom_logo' ), 'thumbnail', false, array( 'alt' => '' ) );
			} else {
				salimor_svg( 'logo' );
			}
			?>
			<span><b><?php bloginfo( 'name' ); ?></b><small aria-hidden="true">SALIMOR</small></span>
		</a>

		<nav class="dnav" aria-label="منوی اصلی">
			<?php
			if ( has_nav_menu( 'primary' ) ) {
				wp_nav_menu( array( 'theme_location' => 'primary', 'container' => false, 'depth' => 1, 'items_wrap' => '%3$s', 'walker' => new Salimor_Flat_Walker() ) );
			} else {
				$links = array( 'خانه' => home_url( '/' ) );
				if ( $has_wc ) {
					$links['فروشگاه'] = salimor_shop_url();
					foreach ( salimor_top_categories( 3 ) as $t ) {
						$links[ $t->name ] = get_term_link( $t );
					}
				}
				$links['ژورنال'] = salimor_blog_url();
				$here = home_url( add_query_arg( array() ) );
				foreach ( $links as $label => $url ) {
					$current = untrailingslashit( strtok( $url, '?' ) ) === untrailingslashit( strtok( $here, '?' ) );
					printf( '<a href="%s"%s>%s</a>', esc_url( $url ), $current ? ' aria-current="page"' : '', esc_html( $label ) );
				}
			}
			?>
		</nav>

		<div class="acts">
			<a class="ib d-only" href="<?php echo esc_url( home_url( '/?s=&post_type=product' ) ); ?>" aria-label="جستجو"><?php echo salimor_icon( 'search' ); // phpcs:ignore ?></a>
			<?php if ( $has_wc ) : ?>
				<a class="ib" href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" aria-label="<?php echo is_user_logged_in() ? 'حساب کاربری' : 'ورود یا ثبت‌نام'; ?>"><?php echo salimor_icon( 'user' ); // phpcs:ignore ?></a>
				<?php $n = salimor_cart_count(); ?>
				<button class="ib d-only" id="cartTop" type="button" aria-label="<?php echo esc_attr( $n ? 'سبد خرید، ' . salimor_fa( $n ) . ' کالا' : 'سبد خرید' ); ?>" aria-controls="sheet" aria-expanded="false" data-cart-open><?php echo salimor_icon( 'bag' ); // phpcs:ignore ?><span class="dot num" data-count <?php echo $n ? '' : 'hidden'; ?>><?php echo esc_html( salimor_fa( $n ) ); ?></span></button>
			<?php endif; ?>
		</div>
	</div>
</header>

<main id="main" tabindex="-1">
