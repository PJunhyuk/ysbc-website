<?php
defined('ABSPATH') or die('No direct access');

/**
 * Shortcode attributes
 * @var $atts
 * @var $style
 * @var $columns
 * @var $el_class
 * @var $content - shortcode content
 * Shortcode class
 * @var $this WPBakeryShortCode_Testimonials_Container
 */
$style = $columns = '';
$atts = vc_map_get_attributes( $this->getShortcode(), $atts );
extract( $atts );

$container_classes = array('testimonials');
$container_classes[] = 'testimonials-' . $columns . '-columns';

$css_class = apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, implode(' ', $container_classes), $this->settings['base'], $atts );

$output = '
	<div class="' . esc_attr( $css_class ) . '">
		' . ( ( '' === trim( $content ) ) ? __( 'Empty. Edit page to add content here.', 'ventcamp' ) : wpb_js_remove_wpautop( $content ) ) . '
	</div>
';

echo $output;
