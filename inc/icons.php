<?php
/**
 * Inline SVG icon set (stroke icons, 24px grid). Icons are decorative by default.
 */
defined('ABSPATH') || exit;

function salimor_icons() {
	return array(
		'arrow' => '<svg viewBox="0 0 24 24"><path d="M15 6l-6 6 6 6"/></svg>',
		'back' => '<svg viewBox="0 0 24 24"><path d="M9 6l6 6-6 6"/></svg>',
		'heart' => '<svg viewBox="0 0 24 24"><path d="M12 20s-7.5-4.6-7.5-10A4.3 4.3 0 0 1 12 7.5 4.3 4.3 0 0 1 19.5 10c0 5.4-7.5 10-7.5 10Z"/></svg>',
		'plus' => '<svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>',
		'check' => '<svg viewBox="0 0 24 24"><path d="m5 12.5 4.5 4.5L19 7.5"/></svg>',
		'truck' => '<svg viewBox="0 0 24 24"><path d="M2.5 6.5h11v9h-11zM13.5 9.5h4l3 3v3h-7"/><circle cx="6.5" cy="17.5" r="1.8"/><circle cx="16.5" cy="17.5" r="1.8"/></svg>',
		'shield' => '<svg viewBox="0 0 24 24"><path d="M12 3l7.5 3v5.5c0 4.5-3.2 8-7.5 9.5-4.3-1.5-7.5-5-7.5-9.5V6z"/><path d="m8.5 12 2.5 2.5 4.5-5"/></svg>',
		'ret' => '<svg viewBox="0 0 24 24"><path d="M9 7 4.5 11.5 9 16"/><path d="M5 11.5h9.5a5 5 0 0 1 0 10H11"/></svg>',
		'gift' => '<svg viewBox="0 0 24 24"><rect x="3.5" y="9" width="17" height="11" rx="1.5"/><path d="M3.5 13h17M12 9v11M12 9c-2-4-6-4-6-1.5S10 9 12 9Zm0 0c2-4 6-4 6-1.5S14 9 12 9Z"/></svg>',
		'sort' => '<svg viewBox="0 0 24 24"><path d="M7 5v14M4 16l3 3 3-3M17 19V5M14 8l3-3 3 3"/></svg>',
		'star' => '<svg viewBox="0 0 24 24"><path d="M12 3l2.7 5.6 6.1.9-4.4 4.3 1 6.1L12 17l-5.4 2.9 1-6.1L3.2 9.5l6.1-.9Z"/></svg>',
		'bag' => '<svg viewBox="0 0 24 24"><path d="M5 8h14l-1.2 11.2a1 1 0 0 1-1 .8H7.2a1 1 0 0 1-1-.8L5 8Z"/><path d="M9 8V6.5a3 3 0 0 1 6 0V8"/></svg>',
		'phone' => '<svg viewBox="0 0 24 24"><path d="M6.5 3.5h3l1.5 4-2 1.5a11 11 0 0 0 6 6l1.5-2 4 1.5v3a2 2 0 0 1-2 2A16 16 0 0 1 4.5 5.5a2 2 0 0 1 2-2Z"/></svg>',
		'mail' => '<svg viewBox="0 0 24 24"><rect x="3.5" y="5.5" width="17" height="13" rx="2"/><path d="m4 7 8 6 8-6"/></svg>',
		'pin' => '<svg viewBox="0 0 24 24"><path d="M12 21s-6.5-6-6.5-11a6.5 6.5 0 0 1 13 0c0 5-6.5 11-6.5 11Z"/><circle cx="12" cy="10" r="2.4"/></svg>',
		'clock' => '<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="8.5"/><path d="M12 7.5V12l3 2"/></svg>',
		'user' => '<svg viewBox="0 0 24 24"><circle cx="12" cy="8.5" r="3.8"/><path d="M4.5 20c1.4-3.6 4.3-5.4 7.5-5.4s6.1 1.8 7.5 5.4"/></svg>',
		'box' => '<svg viewBox="0 0 24 24"><path d="M3.5 7.5 12 3l8.5 4.5v9L12 21l-8.5-4.5Z"/><path d="M3.5 7.5 12 12l8.5-4.5M12 12v9"/></svg>',
		'home' => '<svg viewBox="0 0 24 24"><path d="M4 11.5 12 5l8 6.5V19a1 1 0 0 1-1 1h-4.5v-5h-5v5H5a1 1 0 0 1-1-1Z"/></svg>',
		'out' => '<svg viewBox="0 0 24 24"><path d="M10 4H6a1.5 1.5 0 0 0-1.5 1.5v13A1.5 1.5 0 0 0 6 20h4"/><path d="M14 8l-4 4 4 4M10 12h10"/></svg>',
		'edit' => '<svg viewBox="0 0 24 24"><path d="M4 20h4L19 9l-4-4L4 16Z"/><path d="m13.5 6.5 4 4"/></svg>',
		'trash' => '<svg viewBox="0 0 24 24"><path d="M5 7h14M10 7V5h4v2M7 7l1 13h8l1-13"/></svg>',
		'card' => '<svg viewBox="0 0 24 24"><rect x="3" y="5.5" width="18" height="13" rx="2"/><path d="M3 10h18M7 15h4"/></svg>',
		'chat' => '<svg viewBox="0 0 24 24"><path d="M5 5h14a1 1 0 0 1 1 1v9a1 1 0 0 1-1 1H10l-5 4v-4H5a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Z"/></svg>',
		'leaf' => '<svg viewBox="0 0 24 24"><path d="M5 19c0-8 5-13 14-14-1 9-6 14-14 14Z"/><path d="M5 19 13 11"/></svg>',
		'gem' => '<svg viewBox="0 0 24 24"><path d="M6 4h12l3 5-9 11L3 9Z"/><path d="M3 9h18M9 4l3 16 3-16"/></svg>',
		'search' => '<svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="6.5"/><path d="m20 20-4-4"/></svg>',
		'grid' => '<svg viewBox="0 0 24 24"><rect x="4" y="4" width="7" height="7" rx="2"/><rect x="13" y="4" width="7" height="7" rx="3.5"/><rect x="4" y="13" width="7" height="7" rx="3.5"/><rect x="13" y="13" width="7" height="7" rx="2"/></svg>',
		'book' => '<svg viewBox="0 0 24 24"><path d="M5 5.5A1.5 1.5 0 0 1 6.5 4H18v14H6.5A1.5 1.5 0 0 0 5 19.5Z"/><path d="M5 19.5A1.5 1.5 0 0 0 6.5 21H18v-3"/><path d="M9 8h5"/></svg>',
		'close' => '<svg viewBox="0 0 24 24"><path d="M6 6l12 12M18 6 6 18"/></svg>',
		'menu' => '<svg viewBox="0 0 24 24"><path d="M4 7h16M4 12h16M4 17h10"/></svg>',
		'telegram' => '<svg viewBox="0 0 24 24"><path d="M20.5 4.5 3.5 11l6 2 2 6 3.5-4.5 4.5 3.5Z"/><path d="m9.5 13 8-6"/></svg>',
		'instagram' => '<svg viewBox="0 0 24 24"><rect x="4" y="4" width="16" height="16" rx="5"/><circle cx="12" cy="12" r="3.6"/><circle cx="16.8" cy="7.2" r=".6" fill="currentColor"/></svg>',
		'bale' => '<svg viewBox="0 0 24 24"><path d="M12 4a8 8 0 0 1 0 16H6a2 2 0 0 1-2-2v-6a8 8 0 0 1 8-8Z"/><path d="M9.5 9.5h3a1.5 1.5 0 0 1 0 3h-3v-3Zm0 3h3.5a1.5 1.5 0 0 1 0 3H9.5Z"/></svg>',
		'chatdots' => '<svg viewBox="0 0 24 24"><path d="M5 5h14a1 1 0 0 1 1 1v9a1 1 0 0 1-1 1H10l-5 4v-4H5a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Z"/><path d="M8.5 10.5h.01M12 10.5h.01M15.5 10.5h.01" stroke-width="2.6" stroke-linecap="round"/></svg>',
		'download' => '<svg viewBox="0 0 24 24"><path d="M12 4v11M7 10l5 5 5-5M5 20h14"/></svg>',
		'share' => '<svg viewBox="0 0 24 24"><path d="M12 15V4M8 8l4-4 4 4M6 12v7a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1v-7"/></svg>',
	);
}

/** Echo-safe icon markup; aria-hidden unless a label is given. */
function salimor_icon( $name, $label = '' ) {
	$icons = salimor_icons();
	if ( empty( $icons[ $name ] ) ) {
		return '';
	}
	$attr = $label ? ' role="img" aria-label="' . esc_attr( $label ) . '"' : ' aria-hidden="true" focusable="false"';
	return preg_replace( '/^<svg/', '<svg' . $attr, $icons[ $name ] );
}
