<?php
/**
 * Profile: name, optional e-mail, Jalali birth date; the login mobile is read-only.
 *
 * @package Salimor
 * @version 11.0.0
 */

defined( 'ABSPATH' ) || exit;

$u      = wp_get_current_user();
$e      = $GLOBALS['salimor_profile_errors'] ?? array();
$post   = fn( $k, $d ) => isset( $_POST[ $k ] ) ? sanitize_text_field( wp_unslash( $_POST[ $k ] ) ) : $d; // phpcs:ignore
$birth  = array_map( 'intval', explode( '/', (string) get_user_meta( $u->ID, 'salimor_birth', true ) ) + array( 0, 0, 0 ) );
$by     = (int) $post( 'by', $birth[0] ?? 0 );
$bm     = (int) $post( 'bm', $birth[1] ?? 0 );
$bd     = (int) $post( 'bd', $birth[2] ?? 0 );
$mobile = get_user_meta( $u->ID, 'salimor_mobile', true );
$jy     = salimor_jalali_year();
do_action( 'woocommerce_before_edit_account_form' );
?>
<form class="box form" method="post" id="pf" novalidate>
	<input type="hidden" name="salimor_profile" value="1"><?php wp_nonce_field( 'salimor_profile', '_slmnonce' ); ?>
	<div class="fgrid">
		<?php salimor_field( 'pn1', 'first_name', 'نام', $post( 'first_name', $u->first_name ), 'autocomplete="given-name" required', $e['first_name'] ?? '' ); ?>
		<?php salimor_field( 'pn2', 'last_name', 'نام خانوادگی', $post( 'last_name', $u->last_name ), 'autocomplete="family-name" required', $e['last_name'] ?? '' ); ?>
		<?php salimor_field( 'pe', 'email', 'ایمیل (اختیاری)', $post( 'email', $u->user_email ), 'type="email" class="ltr" autocomplete="email"', $e['email'] ?? '', 'فاکتور سفارش‌ها به این ایمیل هم فرستاده می‌شود' ); ?>
		<fieldset class="date3 fld" aria-describedby="pbd-h<?php echo isset( $e['birth'] ) ? ' pbd-e' : ''; ?>">
			<legend>تاریخ تولد (اختیاری)</legend>
			<div class="d3">
				<label class="sr" for="pbd">روز</label><select id="pbd" name="bd" <?php echo isset( $e['birth'] ) ? 'aria-invalid="true"' : ''; ?>><option value="">روز</option><?php for ( $i = 1; $i <= 31; $i++ ) : ?><option value="<?php echo esc_attr( $i ); ?>" <?php selected( $bd, $i ); ?>><?php echo esc_html( salimor_fa( $i ) ); ?></option><?php endfor; ?></select>
				<label class="sr" for="pbm">ماه</label><select id="pbm" name="bm" <?php echo isset( $e['birth'] ) ? 'aria-invalid="true"' : ''; ?>><option value="">ماه</option><?php foreach ( salimor_jalali_months() as $i => $m ) : ?><option value="<?php echo esc_attr( $i + 1 ); ?>" <?php selected( $bm, $i + 1 ); ?>><?php echo esc_html( $m ); ?></option><?php endforeach; ?></select>
				<label class="sr" for="pby">سال</label><select id="pby" name="by" <?php echo isset( $e['birth'] ) ? 'aria-invalid="true"' : ''; ?>><option value="">سال</option><?php for ( $y = $jy; $y > $jy - 95; $y-- ) : ?><option value="<?php echo esc_attr( $y ); ?>" <?php selected( $by, $y ); ?>><?php echo esc_html( salimor_fa( $y ) ); ?></option><?php endfor; ?></select>
			</div>
			<small class="hint" id="pbd-h">برای هدیه‌ی تولد؛ هر سه بخش را انتخاب کنید.</small>
			<?php if ( isset( $e['birth'] ) ) : ?><small class="err" id="pbd-e"><?php echo esc_html( $e['birth'] ); ?></small><?php endif; ?>
		</fieldset>
		<?php if ( $mobile ) : ?>
			<?php salimor_field( 'pm', '', 'شماره موبایل', salimor_fa( $mobile ), 'class="ltr" readonly', '', 'شماره‌ی ورود قابل تغییر نیست؛ برای تغییر با پشتیبانی تماس بگیرید.' ); ?>
		<?php endif; ?>
	</div>
	<button class="btn b-ink" type="submit">ذخیره تغییرات</button>
</form>
<?php
do_action( 'woocommerce_after_edit_account_form' );
