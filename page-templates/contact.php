<?php
/**
 * Template Name: تماس با ما (سالیمور)
 *
 * Contact cards (from Customizer → تنظیمات سالیمور → تماس) and a message form.
 *
 * @package Salimor
 */

defined( 'ABSPATH' ) || exit;
get_header();
$sent   = isset( $_GET['sent'] ) ? sanitize_text_field( wp_unslash( $_GET['sent'] ) ) : ''; // phpcs:ignore
$e      = $GLOBALS['salimor_contact_errors'] ?? array();
$v      = fn( $k, $d = '' ) => isset( $_POST[ $k ] ) ? sanitize_textarea_field( wp_unslash( $_POST[ $k ] ) ) : $d; // phpcs:ignore
$order  = isset( $_GET['order'] ) ? absint( $_GET['order'] ) : ''; // phpcs:ignore
$user   = wp_get_current_user();
$topics = array( 'پیگیری سفارش', 'مشاوره‌ی خرید و سایز', 'بازگشت یا تعویض کالا', 'همکاری', 'سایر' );
while ( have_posts() ) :
	the_post();
	?>
	<div class="wrap info page-c">
		<div class="ph">
			<nav class="crumbs" aria-label="مسیر صفحه"><a href="<?php echo esc_url( home_url( '/' ) ); ?>">خانه</a><span aria-hidden="true">/</span><span><?php the_title(); ?></span></nav>
			<h1><?php the_title(); ?></h1>
			<?php if ( has_excerpt() ) : ?><p><?php echo esc_html( get_the_excerpt() ); ?></p><?php endif; ?>
		</div>
		<?php if ( trim( get_the_content() ) ) : ?><div class="entry legal"><?php the_content(); ?></div><?php endif; ?>
		<div class="ct-grid">
			<div class="ct-cards">
				<?php
				$cards = array(
					array( 'phone', 'تلفن پشتیبانی', salimor_opt( 'phone' ), 'tel:' . preg_replace( '/[^\d+]/', '', salimor_en_digits( salimor_opt( 'phone' ) ) ), 'num' ),
					array( 'mail', 'ایمیل', salimor_opt( 'email' ), 'mailto:' . salimor_opt( 'email' ), 'ltr' ),
					array( 'clock', 'ساعت پاسخگویی', salimor_opt( 'hours' ), '', '' ),
					array( 'pin', 'دفتر و انبار', salimor_opt( 'address' ), '', '' ),
				);
				foreach ( $cards as list( $ic, $label, $val, $href, $cls ) ) :
					if ( ! $val ) {
						continue;
					}
					?>
					<div class="ct-card"><span class="ic" aria-hidden="true"><?php echo salimor_icon( $ic ); // phpcs:ignore ?></span><div><small><?php echo esc_html( $label ); ?></small>
						<?php if ( $href ) : ?><a href="<?php echo esc_url( $href ); ?>"><b class="<?php echo esc_attr( $cls ); ?>"><?php echo esc_html( 'num' === $cls ? salimor_fa( $val ) : $val ); ?></b></a><?php else : ?><b><?php echo esc_html( $val ); ?></b><?php endif; ?>
					</div></div>
				<?php endforeach; ?>
			</div>
			<form class="box form" id="ctf" method="post" action="<?php echo esc_url( get_permalink() ); ?>#ctf" novalidate>
				<h2>ارسال پیام</h2>
				<?php if ( $sent ) : ?>
					<p class="ok-msg" role="status"><?php echo salimor_icon( 'check' ); // phpcs:ignore ?> پیام شما ثبت شد. شماره‌ی پیگیری: <b class="num"><?php echo esc_html( salimor_fa( $sent ) ); ?></b></p>
				<?php endif; ?>
				<?php if ( isset( $e['form'] ) ) : ?><p class="woocommerce-error" role="alert"><?php echo esc_html( $e['form'] ); ?></p><?php endif; ?>
				<input type="hidden" name="salimor_contact" value="1"><?php wp_nonce_field( 'salimor_contact', '_slmnonce' ); ?>
				<div class="hp" aria-hidden="true"><label for="cweb">وب‌سایت</label><input id="cweb" name="website" tabindex="-1" autocomplete="off"></div>
				<?php salimor_field( 'cn', 'name', 'نام و نام خانوادگی', $v( 'name', trim( $user->first_name . ' ' . $user->last_name ) ), 'autocomplete="name" required', $e['name'] ?? '' ); ?>
				<?php salimor_field( 'cm', 'mobile', 'شماره موبایل', $v( 'mobile', salimor_fa( get_user_meta( $user->ID, 'salimor_mobile', true ) ) ), 'type="tel" inputmode="numeric" autocomplete="tel" class="ltr" placeholder="09121234567" required', $e['mobile'] ?? '', 'برای پاسخ تلفنی، در صورت نیاز' ); ?>
				<div class="fld"><label for="cs">موضوع</label><select id="cs" name="topic"><?php foreach ( $topics as $t ) : ?><option <?php selected( $v( 'topic', $order ? 'بازگشت یا تعویض کالا' : '' ), $t ); ?>><?php echo esc_html( $t ); ?></option><?php endforeach; ?></select></div>
				<?php salimor_field( 'co', 'order', 'شماره سفارش (اختیاری)', $v( 'order', $order ? (string) $order : '' ), 'inputmode="numeric" class="ltr" placeholder="14058"' ); ?>
				<div class="fld"><label for="cx">پیام</label><textarea id="cx" name="message" rows="5" required <?php echo isset( $e['message'] ) ? 'aria-invalid="true" aria-describedby="cx-e"' : ''; ?>><?php echo esc_textarea( $v( 'message' ) ); ?></textarea><?php echo isset( $e['message'] ) ? '<small class="err" id="cx-e">' . esc_html( $e['message'] ) . '</small>' : ''; ?></div>
				<button class="btn b-ink" type="submit">ارسال پیام</button>
			</form>
		</div>
	</div>
	<?php
endwhile;
get_footer();
