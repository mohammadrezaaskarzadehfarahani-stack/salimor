<?php
/**
 * Shop, category and product-search listing: sticky category bar, filter panel (sheet on phones),
 * active-filter chips, sort, product grid. Filters work as plain GET links; main.js swaps results in place.
 *
 * @package Salimor
 * @version 8.6.0
 */

defined( 'ABSPATH' ) || exit;

get_header( 'shop' );

$term     = is_product_category() || is_product_tag() ? get_queried_object() : null;
$searchq  = is_search() ? get_search_query( false ) : '';
$title    = $searchq !== '' ? 'جستجوی «' . $searchq . '»' : ( $term ? $term->name : ( is_search() ? 'جستجو در فروشگاه' : woocommerce_page_title( false ) ) );
$intro    = $term ? wp_strip_all_tags( term_description( $term ) ) : ( is_search() ? '' : get_bloginfo( 'description' ) );
$top_cats = salimor_top_categories( 8 );
$cur_top  = salimor_current_top_cat();
$chosen   = salimor_chosen();
$active   = salimor_active_count();
$facets   = salimor_facets();
$total    = (int) wc_get_loop_prop( 'total' );
$orderby  = isset( $_GET['orderby'] ) ? wc_clean( wp_unslash( $_GET['orderby'] ) ) : apply_filters( 'woocommerce_default_catalog_orderby', get_option( 'woocommerce_default_catalog_orderby', 'date' ) ); // phpcs:ignore
$base     = salimor_listing_base_url();
?>
<div class="wrap shop-page" id="shopRoot">
	<div class="ph">
		<nav class="crumbs" aria-label="مسیر صفحه">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>">خانه</a><span aria-hidden="true">/</span>
			<?php if ( $term || is_search() ) : ?>
				<a href="<?php echo esc_url( salimor_shop_url() ); ?>">فروشگاه</a>
				<?php
				if ( $term && 'product_cat' === $term->taxonomy ) :
					foreach ( array_reverse( get_ancestors( $term->term_id, 'product_cat' ) ) as $aid ) :
						$a = get_term( $aid, 'product_cat' );
						?>
						<span aria-hidden="true">/</span><a href="<?php echo esc_url( get_term_link( $a ) ); ?>"><?php echo esc_html( $a->name ); ?></a>
						<?php
					endforeach;
				endif;
				?>
			<?php else : ?>
				<span>فروشگاه</span>
			<?php endif; ?>
		</nav>
		<h1><?php echo esc_html( $title ); ?></h1>
		<?php if ( $intro ) : ?><p><?php echo esc_html( $intro ); ?></p><?php endif; ?>
	</div>

	<?php if ( is_search() ) : ?>
		<form class="search-pill shop-search" role="search" action="<?php echo esc_url( home_url( '/' ) ); ?>">
			<?php echo salimor_icon( 'search' ); // phpcs:ignore ?>
			<label class="sr" for="shopS">جستجوی محصول</label>
			<input id="shopS" type="search" name="s" value="<?php echo esc_attr( $searchq ); ?>" placeholder="نام محصول، جنس یا رنگ…" enterkeyhint="search" <?php echo '' === $searchq ? 'autofocus' : ''; ?>>
			<input type="hidden" name="post_type" value="product">
		</form>
	<?php endif; ?>

	<?php if ( ! is_search() && count( $top_cats ) > 1 ) : ?>
		<div class="tools">
			<nav class="seg" aria-label="دسته‌بندی‌ها"><span class="pill" aria-hidden="true"></span>
				<a href="<?php echo esc_url( salimor_shop_url() ); ?>" <?php echo ! $cur_top ? 'aria-current="page"' : ''; ?>>همه</a>
				<?php foreach ( $top_cats as $c ) : ?>
					<a href="<?php echo esc_url( get_term_link( $c ) ); ?>" <?php echo $cur_top === $c->term_id ? 'aria-current="page"' : ''; ?>><?php echo esc_html( $c->name ); ?></a>
				<?php endforeach; ?>
			</nav>
		</div>
		<?php
		if ( $cur_top ) :
			$subs = get_terms( array( 'taxonomy' => 'product_cat', 'parent' => $cur_top, 'hide_empty' => true, 'orderby' => 'meta_value_num', 'meta_key' => 'order' ) ); // phpcs:ignore
			if ( $subs && ! is_wp_error( $subs ) ) :
				?>
				<nav class="subcats shop-subs" aria-label="زیردسته‌ها">
					<?php foreach ( $subs as $s ) : ?>
						<a class="sc" href="<?php echo esc_url( get_term_link( $s ) ); ?>" <?php echo $term && $term->term_id === $s->term_id ? 'aria-current="page"' : ''; ?>><?php echo esc_html( $s->name ); ?></a>
					<?php endforeach; ?>
				</nav>
				<?php
			endif;
		endif;
	endif;
	?>

	<?php woocommerce_output_all_notices(); ?>

	<div class="shop-lay">
		<form class="fpanel" id="fpanel" method="get" action="<?php echo esc_url( strtok( $base, '?' ) ); ?>" aria-labelledby="fp-t" <?php echo $facets ? '' : 'hidden'; ?>>
			<div class="grab" aria-hidden="true"></div>
			<div class="fp-h"><h2 id="fp-t">فیلترها</h2><button class="ib fp-x" id="fclose" type="button" aria-label="بستن فیلترها"><?php echo salimor_icon( 'close' ); // phpcs:ignore ?></button></div>
			<div class="fp-b" id="fpb">
				<?php foreach ( $facets as $g ) : ?>
					<fieldset class="fgrp">
						<legend><?php echo esc_html( $g['label'] ); ?></legend>
						<?php if ( 'color' === $g['type'] ) : ?><div class="fcols"><?php endif; ?>
						<?php
						foreach ( $g['options'] as list( $val, $label, $n, $on, $extra ) ) :
							$name = 'toggle' === $g['type'] ? $extra : $g['key'] . '[]';
							?>
							<label class="fo<?php echo $n ? '' : ' dim'; ?>">
								<input type="checkbox" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $val ); ?>" <?php checked( $on ); ?>>
								<?php if ( 'color' === $g['type'] ) : ?><i class="fdot" style="background:<?php echo esc_attr( $extra ?: '#ccc' ); ?>"></i><?php endif; ?>
								<span><?php echo esc_html( $label ); ?></span><small class="num"><?php echo esc_html( salimor_fa( $n ) ); ?></small>
							</label>
						<?php endforeach; ?>
						<?php if ( 'color' === $g['type'] ) : ?></div><?php endif; ?>
					</fieldset>
				<?php endforeach; ?>
				<?php if ( $searchq !== '' ) : ?><input type="hidden" name="s" value="<?php echo esc_attr( $searchq ); ?>"><input type="hidden" name="post_type" value="product"><?php endif; ?>
				<noscript><button class="btn b-ink" type="submit" style="width:100%;margin-block:12px">اعمال فیلترها</button></noscript>
			</div>
			<div class="fp-f">
				<a class="btn b-line" id="fclear" href="<?php echo esc_url( $base ); ?>" <?php echo $active ? '' : 'aria-disabled="true"'; ?>>پاک کردن همه</a>
				<button class="btn b-ink" id="fapply" type="submit"><?php echo $total ? esc_html( 'نمایش ' . salimor_fa( $total ) . ' محصول' ) : 'محصولی پیدا نشد'; ?></button>
			</div>
		</form>
		<div class="fscrim" id="fscrim"></div>

		<div class="shop-main" id="shopMain" aria-live="polite" aria-busy="false">
			<div class="count-row">
				<h2 class="sr">فهرست محصولات</h2>
				<?php if ( $facets ) : ?>
					<button class="fopen" id="fopen" type="button" aria-controls="fpanel" aria-expanded="false"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 6h16M7 12h10M10 18h4"/></svg>فیلترها<span class="fbadge num" id="fbadge" <?php echo $active ? '' : 'hidden'; ?>><?php echo esc_html( salimor_fa( $active ) ); ?></span></button>
				<?php endif; ?>
				<p class="count num" id="cnt"><?php echo esc_html( salimor_fa( $total ) . ' محصول' ); ?></p>
				<label class="sortb"><span class="sr">مرتب‌سازی</span><?php echo salimor_icon( 'sort' ); // phpcs:ignore ?>
					<select id="srt" name="orderby" form="fpanel">
						<?php foreach ( salimor_sort_options() as $k => $l ) : ?>
							<option value="<?php echo esc_attr( $k ); ?>" <?php selected( $orderby, $k ); ?>><?php echo esc_html( $l ); ?></option>
						<?php endforeach; ?>
					</select>
				</label>
			</div>

			<?php $chips = salimor_active_chips(); ?>
			<div class="fchips" id="fchips">
				<?php if ( $chips ) : ?>
					<?php foreach ( $chips as list( $p, $v, $l ) ) : ?>
						<a class="fchip" href="<?php echo esc_url( salimor_url_without( $p, $v ) ); ?>" data-rg="<?php echo esc_attr( $p ); ?>" data-rv="<?php echo esc_attr( $v ); ?>" aria-label="<?php echo esc_attr( 'حذف فیلتر ' . $l ); ?>"><?php echo esc_html( $l ); ?><?php echo salimor_icon( 'close' ); // phpcs:ignore ?></a>
					<?php endforeach; ?>
					<a class="fchip clear" href="<?php echo esc_url( $base ); ?>" data-rall>پاک کردن همه</a>
				<?php endif; ?>
			</div>

			<?php
			do_action( 'woocommerce_before_shop_loop' );
			if ( woocommerce_product_loop() && have_posts() ) :
				echo '<div class="grid g4 wc-grid" id="sg">';
				while ( have_posts() ) :
					the_post();
					wc_get_template_part( 'content', 'product' );
				endwhile;
				echo '</div>';
				woocommerce_pagination();
			else :
				?>
				<div class="grid g4" id="sg">
					<div class="empty box f-empty">
						<?php echo salimor_icon( 'search' ); // phpcs:ignore ?>
						<?php if ( $active ) : ?>
							<p><b>با این فیلترها محصولی پیدا نشد.</b></p><p>یکی از فیلترها را بردارید یا همه را پاک کنید.</p>
							<a class="btn b-ink" href="<?php echo esc_url( $base ); ?>" data-rall>پاک کردن فیلترها</a>
						<?php elseif ( $searchq !== '' ) : ?>
							<p><b>برای «<?php echo esc_html( $searchq ); ?>» محصولی پیدا نشد.</b></p><p>کلمه‌ی کوتاه‌تر یا دیگری را امتحان کنید.</p>
							<a class="btn b-line" href="<?php echo esc_url( salimor_shop_url() ); ?>">دیدن همه‌ی محصولات</a>
						<?php else : ?>
							<p><b>هنوز محصولی در این بخش نیست.</b></p>
							<a class="btn b-line" href="<?php echo esc_url( salimor_shop_url() ); ?>">دیدن همه‌ی محصولات</a>
						<?php endif; ?>
					</div>
				</div>
				<?php
			endif;
			do_action( 'woocommerce_after_shop_loop' );
			?>
		</div>
	</div>
</div>
<?php
get_footer( 'shop' );
