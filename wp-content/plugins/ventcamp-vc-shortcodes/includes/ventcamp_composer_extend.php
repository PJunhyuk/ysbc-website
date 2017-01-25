<?php
defined('ABSPATH') or die('No direct access');

// shortcode vc_number_param
vc_add_shortcode_param('number', 'vc_number_param');

// Remove tabs params
vc_remove_param('vc_tta_tabs', 'spacing' );
vc_remove_param('vc_tta_tabs', 'gap' );

if( !function_exists('vc_number_param') ) {
	function vc_number_param( $settings, $value ) {

		$min_max = '';

		if(isset($settings['min']) && is_numeric(intval($settings['min']))){
			$min_max = ' min="' . intval($settings['min']) . '" ';
		}

		if(isset($settings['min']) && is_numeric(intval($settings['max']))){
			$min_max = ' max="' . intval($settings['max']) . '" ';
		}

		return '<div class="vc_number_param_block">'
			.'<input name="' . esc_attr( $settings['param_name'] ) . '" class="wpb_vc_param_value wpb-textinput vc_number_param' .
			esc_attr( $settings['param_name'] ) . ' ' . $min_max . ' ' .
			esc_attr( $settings['type'] ) . '_field" type="number" value="' . esc_attr( $value ) . '" />' .
			'</div>';
	}
}