<?php
/**
 * One payment method card.
 *
 * @package Salimor
 * @version 3.5.0
 */

defined( 'ABSPATH' ) || exit;
?>
<li class="wc_payment_method payment_method_<?php echo esc_attr( $gateway->id ); ?>">
	<label class="opt" for="payment_method_<?php echo esc_attr( $gateway->id ); ?>">
		<input id="payment_method_<?php echo esc_attr( $gateway->id ); ?>" type="radio" class="input-radio" name="payment_method" value="<?php echo esc_attr( $gateway->id ); ?>" <?php checked( $gateway->chosen, true ); ?> data-order_button_text="<?php echo esc_attr( $gateway->order_button_text ); ?>">
		<span><b><?php echo wp_kses_post( $gateway->get_title() ); ?></b><?php if ( $gateway->get_description() && ! $gateway->has_fields() ) : ?><small><?php echo wp_kses_post( wp_strip_all_tags( $gateway->get_description() ) ); ?></small><?php endif; ?></span>
		<?php echo $gateway->get_icon() ? $gateway->get_icon() : salimor_icon( 'card' ); // phpcs:ignore ?>
	</label>
	<?php if ( $gateway->has_fields() ) : ?>
		<div class="payment_box payment_method_<?php echo esc_attr( $gateway->id ); ?>" <?php echo $gateway->chosen ? '' : 'style="display:none;"'; ?>><?php $gateway->payment_fields(); ?></div>
	<?php endif; ?>
</li>
