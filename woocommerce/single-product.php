<?php
/**
 * Product page: swipeable gallery with arrows, options (variations), sticky buy bar that turns into
 * in-cart controls after adding, details, related products.
 *
 * @package Salimor
 * @version 1.6.4
 */

defined( 'ABSPATH' ) || exit;

get_header( 'shop' );

while ( have_posts() ) :
	the_post();
	$product = wc_get_product( get_the_ID() );
	if ( ! $product ) {
		continue;
	}
	$GLOBALS['product'] = $product;
	do_action( 'woocommerce_before_single_product' );
	if ( post_password_required() ) {
		echo get_the_password_form(); // phpcs:ignore
		continue;
	}

	$pid      = $product->get_id();
	$name     = $product->get_name();
	$variable = $product->is_type( 'variable' );
	$cat_ids  = $product->get_category_ids();
	$cat      = $cat_ids ? get_term( $cat_ids[0], 'product_cat' ) : null;
	$disc     = salimor_discount( $product );
	$is_new   = ( time() - (int) $product->get_date_created()?->getTimestamp() ) < salimor_new_days() * DAY_IN_SECONDS;
	$wished   = in_array( $pid, salimor_wishlist(), true );
	$images   = salimor_gallery_ids( $product );
	$options  = $variable ? salimor_variation_options( $product ) : array();
	$vdata    = $variable ? salimor_variation_data( $product ) : array();
	$buyable  = $product->is_purchasable() && ( $variable ? (bool) $vdata : $product->is_in_stock() );
	$kind     = $product->is_type( array( 'simple', 'variable' ) );
	$in_cart  = salimor_cart_lines();
	$back     = $cat && ! is_wp_error( $cat ) ? get_term_link( $cat ) : salimor_shop_url();
	$max_qty  = $product->get_max_purchase_quantity();
	?>
	<div class="wrap">
	<div id="product-<?php echo esc_attr( $pid ); ?>" <?php wc_product_class( 'pdp', $product ); ?> data-pid="<?php echo esc_attr( $pid ); ?>">
		<div class="gal">
			<div class="main" id="gm" tabindex="-1" role="region" aria-roledescription="گالری" aria-label="<?php echo esc_attr( 'تصاویر ' . $name ); ?>">
				<a class="ib back m-only" href="<?php echo esc_url( $back ); ?>" aria-label="<?php echo esc_attr( 'بازگشت به ' . ( $cat && ! is_wp_error( $cat ) ? $cat->name : 'فروشگاه' ) ); ?>"><?php echo salimor_icon( 'back' ); // phpcs:ignore ?></a>
				<button class="heart" type="button" data-wish="<?php echo esc_attr( $pid ); ?>" aria-pressed="<?php echo $wished ? 'true' : 'false'; ?>" aria-label="<?php echo esc_attr( 'علاقه‌مندی ' . $name ); ?>"><?php echo salimor_icon( 'heart' ); // phpcs:ignore ?></button>
				<?php if ( $disc ) : ?><span class="badge sale num gal-badge"><?php echo esc_html( salimor_fa( $disc ) . '٪ تخفیف' ); ?></span><?php endif; ?>
				<?php
				if ( $images ) :
					foreach ( $images as $i => $img_id ) :
						echo wp_get_attachment_image(
							$img_id,
							'woocommerce_single',
							false,
							array(
								'class'         => 'gi' . ( 0 === $i ? ' on' : '' ),
								'data-i'        => $i,
								'data-id'       => $img_id,
								'alt'           => 0 === $i ? $name : $name . ' — تصویر ' . salimor_fa( $i + 1 ),
								'loading'       => 0 === $i ? 'eager' : 'lazy',
								'fetchpriority' => 0 === $i ? 'high' : 'auto',
								'sizes'         => '(min-width:900px) 52vw, 100vw',
								'aria-hidden'   => 0 === $i ? 'false' : 'true',
							)
						);
					endforeach;
				else :
					echo wc_placeholder_img( 'woocommerce_single', array( 'class' => 'gi on' ) ); // phpcs:ignore
				endif;
				?>
				<?php if ( count( $images ) > 1 ) : ?>
					<button class="gal-nav prev" id="gprev" type="button" aria-label="تصویر قبلی"><?php echo salimor_icon( 'back' ); // phpcs:ignore ?></button>
					<button class="gal-nav next" id="gnext" type="button" aria-label="تصویر بعدی"><?php echo salimor_icon( 'arrow' ); // phpcs:ignore ?></button>
					<span class="gal-count num" id="gcount" aria-live="polite"><?php echo esc_html( '۱ از ' . salimor_fa( count( $images ) ) ); ?></span>
				<?php endif; ?>
			</div>
			<?php if ( count( $images ) > 1 ) : ?>
				<div class="dots" role="group" aria-label="تصاویر">
					<?php foreach ( $images as $i => $img_id ) : ?>
						<button type="button" data-go="<?php echo esc_attr( $i ); ?>" aria-pressed="<?php echo 0 === $i ? 'true' : 'false'; ?>" aria-label="<?php echo esc_attr( 'تصویر ' . salimor_fa( $i + 1 ) ); ?>"><i></i></button>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>

		<div class="pi">
			<nav class="crumbs d-only" aria-label="مسیر صفحه">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>">خانه</a><span aria-hidden="true">/</span>
				<a href="<?php echo esc_url( salimor_shop_url() ); ?>">فروشگاه</a>
				<?php if ( $cat && ! is_wp_error( $cat ) ) : ?><span aria-hidden="true">/</span><a href="<?php echo esc_url( $back ); ?>"><?php echo esc_html( $cat->name ); ?></a><?php endif; ?>
			</nav>
			<p class="kick" style="margin-top:6px"><?php echo esc_html( ( $cat && ! is_wp_error( $cat ) ? $cat->name : '' ) . ( $is_new ? ' · جدید' : '' ) ); ?></p>
			<h1 class="product_title"><?php echo esc_html( $name ); ?></h1>
			<div class="row1"><span id="pPrice"><?php echo salimor_price_html( $product ); // phpcs:ignore ?></span>
				<?php if ( ! $product->is_in_stock() ) : ?><span class="stock out">ناموجود</span><?php elseif ( $product->managing_stock() && $product->get_stock_quantity() && $product->get_stock_quantity() <= 3 ) : ?><span class="stock low num"><?php echo esc_html( 'فقط ' . salimor_fa( $product->get_stock_quantity() ) . ' عدد باقی مانده' ); ?></span><?php endif; ?>
			</div>
			<?php if ( $product->get_short_description() ) : ?>
				<div class="desc"><?php echo wp_kses_post( wpautop( $product->get_short_description() ) ); ?></div>
			<?php endif; ?>

			<?php
			foreach ( $options as $o ) :
				$is_color = $o['tax'] === salimor_color_taxonomy();
				$is_size  = (bool) preg_match( '/size|سایز|اندازه/u', $o['key'] . $o['label'] );
				?>
				<div class="opt" role="group" aria-labelledby="lb-<?php echo esc_attr( $o['key'] ); ?>" data-attr="<?php echo esc_attr( $o['key'] ); ?>">
					<div class="lb"><span id="lb-<?php echo esc_attr( $o['key'] ); ?>"><?php echo esc_html( $o['label'] ); ?><b class="sel-name" aria-hidden="true"></b></span>
						<?php if ( $is_size && salimor_opt( 'size_guide' ) ) : ?><a href="<?php echo esc_url( salimor_opt( 'size_guide' ) ); ?>">راهنمای سایز</a><?php endif; ?>
					</div>
					<div class="<?php echo $is_color ? 'colors' : 'sizes'; ?>">
						<?php foreach ( $o['values'] as list( $val, $label, $hex ) ) : ?>
							<button type="button" data-val="<?php echo esc_attr( $val ); ?>" data-label="<?php echo esc_attr( $label ); ?>" aria-pressed="<?php echo $o['default'] === $val ? 'true' : 'false'; ?>"
								<?php if ( $is_color ) : ?>aria-label="<?php echo esc_attr( $label ); ?>" title="<?php echo esc_attr( $label ); ?>" style="background:<?php echo esc_attr( $hex ?: '#ccc' ); ?>"<?php else : ?>class="num"<?php endif; ?>><?php echo $is_color ? '' : esc_html( $label ); ?></button>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endforeach; ?>

			<?php if ( $kind ) : ?>
				<div class="buybar<?php echo $buyable ? '' : ' off'; ?>" id="buybar">
					<div class="bp"><span id="bPrice"><?php echo salimor_price_html( $product ); // phpcs:ignore ?></span><small id="bpnote"><?php echo $buyable ? 'ارسال از فردا' : 'فعلاً موجود نیست'; ?></small></div>
					<?php if ( $buyable ) : ?>
						<div class="qty d-only"><button type="button" id="qm" aria-label="کم کردن">−</button><output id="qv" class="num">۱</output><button type="button" id="qp" aria-label="اضافه کردن">+</button></div>
						<button class="btn b-ink" type="button" id="add" data-name="<?php echo esc_attr( $name ); ?>">افزودن به سبد</button>
						<div class="incart" id="incart" hidden>
							<div class="mq qty"><button type="button" data-pq="-1" aria-label="یکی کمتر">−</button><span class="num" id="pqn" aria-live="polite">۱</span><button type="button" data-pq="1" aria-label="یکی بیشتر">+</button></div>
							<button class="btn b-ink" type="button" id="viewCart"><?php echo salimor_icon( 'bag' ); // phpcs:ignore ?>مشاهده سبد</button>
						</div>
					<?php else : ?>
						<button class="btn b-line" type="button" disabled>ناموجود</button>
					<?php endif; ?>
				</div>
				<script type="application/json" id="pdpData"><?php echo wp_json_encode( array( 'id' => $pid, 'variable' => $variable, 'variations' => $vdata, 'lines' => $in_cart, 'max' => $max_qty > 0 ? $max_qty : 999 ) ); ?></script>
			<?php else : ?>
				<div class="buybar static"><?php woocommerce_template_single_add_to_cart(); ?></div>
			<?php endif; ?>

			<?php
			$perks = array();
			foreach ( array( array( 'truck', 1 ), array( 'shield', 2 ), array( 'ret', 3 ) ) as list( $ic, $k ) ) {
				if ( salimor_opt( "perk{$k}_t" ) ) {
					$perks[] = array( $ic, salimor_opt( "perk{$k}_t" ) );
				}
			}
			if ( $perks ) :
				?>
				<div class="feat"><?php foreach ( $perks as list( $ic, $t ) ) : ?><div><?php echo salimor_icon( $ic ); // phpcs:ignore ?><?php echo esc_html( $t ); ?></div><?php endforeach; ?></div>
			<?php endif; ?>

			<?php $content = trim( get_the_content() ); ?>
			<?php if ( $content ) : ?>
				<details open><summary>توضیحات</summary><div class="bd prose"><?php the_content(); ?></div></details>
			<?php endif; ?>
			<?php $specs = salimor_product_specs( $product ); ?>
			<?php if ( $specs ) : ?>
				<details <?php echo $content ? '' : 'open'; ?>><summary>مشخصات</summary><div class="bd"><table class="spec">
					<?php foreach ( $specs as list( $k, $v, $ltr ) ) : ?>
						<tr><td><?php echo esc_html( $k ); ?></td><td class="num"<?php echo $ltr ? ' style="direction:ltr"' : ''; ?>><?php echo esc_html( $v ); ?></td></tr>
					<?php endforeach; ?>
				</table></div></details>
			<?php endif; ?>
			<?php if ( salimor_opt( 'pdp_ship' ) ) : ?>
				<details><summary>ارسال و بازگشت</summary><div class="bd"><?php echo esc_html( salimor_opt( 'pdp_ship' ) ); ?></div></details>
			<?php endif; ?>
			<?php do_action( 'salimor_product_after_details', $product ); ?>
		</div>
	</div>
	</div>

	<?php
	$related = array_filter( array_map( 'wc_get_product', wc_get_related_products( $pid, 8 ) ) );
	if ( $related ) :
		?>
		<section class="related-s"><div class="wrap">
			<?php salimor_section_head( 'از همین دسته', 'شاید بپسندید', '', '' ); ?>
			<div class="rail"><?php foreach ( $related as $r ) { salimor_product_card( $r ); } ?></div>
		</div></section>
	<?php endif; ?>

	<?php
	if ( isset( WC()->structured_data ) ) {
		WC()->structured_data->generate_product_data( $product );
	}
	do_action( 'woocommerce_after_single_product' );
endwhile;

get_footer( 'shop' );
