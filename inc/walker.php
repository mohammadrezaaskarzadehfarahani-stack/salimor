<?php
/**
 * Menu walker that prints plain <a> links (the desktop pill navigation has no list markup).
 *
 * @package Salimor
 */

defined( 'ABSPATH' ) || exit;

class Salimor_Flat_Walker extends Walker_Nav_Menu {
	public function start_lvl( &$output, $depth = 0, $args = null ) {}
	public function end_lvl( &$output, $depth = 0, $args = null ) {}
	public function end_el( &$output, $data_object, $depth = 0, $args = null ) {}

	public function start_el( &$output, $data_object, $depth = 0, $args = null, $current_object_id = 0 ) {
		$item    = $data_object;
		$current = in_array( 'current-menu-item', (array) $item->classes, true ) || in_array( 'current-menu-ancestor', (array) $item->classes, true );
		$output .= sprintf(
			'<a href="%s"%s%s>%s</a>',
			esc_url( $item->url ),
			$current ? ' aria-current="page"' : '',
			$item->target ? ' target="' . esc_attr( $item->target ) . '" rel="noopener"' : '',
			esc_html( $item->title )
		);
	}
}
