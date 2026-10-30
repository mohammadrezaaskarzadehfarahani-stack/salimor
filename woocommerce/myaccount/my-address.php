<?php
/**
 * Address book (replaces WooCommerce's single billing/shipping address).
 *
 * @package Salimor
 * @version 9.3.0
 */

defined( 'ABSPATH' ) || exit;

$list   = salimor_addresses();
$errors = $GLOBALS['salimor_addr_errors'] ?? array();
$posted = $GLOBALS['salimor_addr_values'] ?? null;
$edit   = isset( $_GET['edit'] ) ? sanitize_key( wp_unslash( $_GET['edit'] ) ) : ''; // phpcs:ignore
$adding = isset( $_GET['new'] ) || ( $posted && ! $posted['id'] ); // phpcs:ignore
$nonce  = wp_create_nonce( 'salimor_address' );
?>
<div id="adrz">
	<?php if ( ! $list && ! $adding ) : ?>
		<div class="empty box"><?php echo salimor_icon( 'pin' ); // phpcs:ignore ?><p>هنوز آدرسی ذخیره نکرده‌اید.</p></div>
	<?php endif; ?>
	<?php foreach ( $list as $id => $a ) : ?>
		<?php if ( $edit === $id || ( $posted && $posted['id'] === $id ) ) : ?>
			<?php salimor_address_form( $posted ? $posted : $a + array( 'id' => $id ), $errors ); ?>
			<?php continue; ?>
		<?php endif; ?>
		<article class="adr box">
			<div class="adr-h"><b><?php echo esc_html( $a['title'] ); ?></b><?php if ( ! empty( $a['default'] ) ) : ?><span class="st st3">پیش‌فرض</span><?php endif; ?></div>
			<p><?php echo esc_html( salimor_address_line( $a ) ); ?></p>
			<small>کد پستی: <span class="num"><?php echo esc_html( salimor_fa( $a['postcode'] ) ); ?></span> · گیرنده: <?php echo esc_html( trim( $a['first_name'] . ' ' . $a['last_name'] ) ); ?> · <span class="num"><?php echo esc_html( salimor_fa( $a['phone'] ) ); ?></span></small>
			<div class="adr-a">
				<a class="link" href="<?php echo esc_url( add_query_arg( 'edit', $id, wc_get_account_endpoint_url( 'edit-address' ) ) ); ?>#af"><?php echo salimor_icon( 'edit' ); // phpcs:ignore ?>ویرایش</a>
				<?php if ( empty( $a['default'] ) ) : ?>
					<form method="post"><input type="hidden" name="_slmnonce" value="<?php echo esc_attr( $nonce ); ?>"><input type="hidden" name="salimor_addr_action" value="default"><input type="hidden" name="addr_id" value="<?php echo esc_attr( $id ); ?>"><button class="link" type="submit">انتخاب به‌عنوان پیش‌فرض</button></form>
				<?php endif; ?>
				<button class="link danger" type="button" data-adel="<?php echo esc_attr( $id ); ?>" aria-expanded="false" aria-controls="cf-<?php echo esc_attr( $id ); ?>"><?php echo salimor_icon( 'trash' ); // phpcs:ignore ?>حذف</button>
			</div>
			<form class="confirm" id="cf-<?php echo esc_attr( $id ); ?>" method="post" hidden>
				<span>این آدرس حذف شود؟</span>
				<input type="hidden" name="_slmnonce" value="<?php echo esc_attr( $nonce ); ?>"><input type="hidden" name="salimor_addr_action" value="delete"><input type="hidden" name="addr_id" value="<?php echo esc_attr( $id ); ?>">
				<button class="btn b-sm danger-b" type="submit">حذف آدرس</button><button class="btn b-sm b-line" type="button" data-adelno>انصراف</button>
			</form>
		</article>
	<?php endforeach; ?>
</div>
<?php if ( $adding ) : ?>
	<?php salimor_address_form( $posted ? $posted : array(), $errors ); ?>
<?php elseif ( ! $edit && ! $posted ) : ?>
	<a class="btn b-ink" id="aadd" href="<?php echo esc_url( add_query_arg( 'new', 1, wc_get_account_endpoint_url( 'edit-address' ) ) ); ?>#af"><?php echo salimor_icon( 'plus' ); // phpcs:ignore ?>افزودن آدرس جدید</a>
<?php endif; ?>
