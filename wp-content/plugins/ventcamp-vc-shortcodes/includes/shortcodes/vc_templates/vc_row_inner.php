<?php
$output = $vsc_id = $width = $vsc_parallax = $vsc_bg_image = $vsc_bg_position = $vsc_bg_repeat =
$vsc_bg_size = $vsc_bg_color = $video_url_attr = $vsc_text_color = $vsc_class = $vsc_youtube_url =
$css = $bg_image = $bg_color = $vsc_bg_gradient = $vsc_custom_style = '';

extract(shortcode_atts(array(
    'vsc_id'                => '',
    'vsc_bg_image'          => '',
    'width'                 => '',
    'vsc_parallax'          => false,
    'vsc_bg_position'       => 'center',
    'vsc_bg_repeat'         => '',
    'vsc_bg_size'           => 'cover',
    'vsc_bg_color'          => '',
    'options'               => '',
    'vsc_text_color'        => '',
    'vsc_class'             => '',
    'vsc_youtube_url'       => '',
    'vsc_youtube_options'   => '',
    'vsc_youtube_controls'  => 'left',
    'el_class'              => '',
    'css'                   => '',
    'vsc_bg_gradient'       => '',
    'vsc_row_type'          => ''
), $atts));

// Base
//----------------------------------------------------------
wp_enqueue_script( 'wpb_composer_front_js' );

// Id attribute params
//----------------------------------------------------------
$rnd_id = ventcamp_random_id(3);
$token = wp_generate_password(5, false, false);
$vsc_id = preg_replace('/\s+/', '', $vsc_id);

if(empty($vsc_id)) {
    $vsc_id = 'vsc_row_' .  ventcamp_random_id(10);

}

$output_id = ' id="'.$vsc_id.'"';


// Youtube params
//----------------------------------------------------------
if ( $vsc_youtube_url != '' ) {
    wp_enqueue_script( 'youtube-video-bg', get_template_directory_uri().'/js/lib/jquery.mb.YTPlayer.min.js', array('jquery'), false, true );

    if ( $vsc_youtube_controls != 'none' ) {
        wp_enqueue_script( 'ui-slider', get_template_directory_uri().'/js/lib/jquery-ui-slider.min.js', array('jquery'), false, true);
    }

    $vsc_youtube_options = explode( ',', $vsc_youtube_options );

    if ( in_array('autoplay', $vsc_youtube_options) ) {
        $video_autoplay = 'false';

    }else {
        $video_autoplay = 'true';

    }

    if ( in_array('sound', $vsc_youtube_options) ) {
        $video_mute = 'true';

    }else {
        $video_mute = 'false';

    }

    $video_url_attr = ' data-video-url="' . $vsc_youtube_url . '" ';

    $video_bg_attr  = ' data-property="{videoURL: \'' . $vsc_youtube_url . '\', containment: \'#' . $vsc_id . '\',';
    $video_bg_attr .= ' autoPlay:' . $video_autoplay . ', realfullscreen: true, stopMovieOnBlur: false,';
    $video_bg_attr .= ' addRaster: true, showControls: false, mute:' . $video_mute . ',';
    $video_bg_attr .= ' startAt:0, opacity:1, gaTrack: false}"';

    $video_controls_attrs = ' data-controls="' . $vsc_youtube_controls . '" data-autoplay="' . $video_autoplay . '"';
    $video_controls_attrs .= ' data-mute="' . $video_mute . '"';

    $yt_video_bg_attrs = $video_url_attr . $video_bg_attr . $video_controls_attrs;

}else {
    $yt_video_bg_attrs = '';

}


// Row class
//----------------------------------------------------------
$vsc_class = $this->getExtraClass($el_class);

// base classes
$base_css_classes = array(
    'vc_row',
    'vc_inner',
    'vc_row-fluid',
    $el_class,
    vc_shortcode_custom_css_class( $css ),
);

$base_css_classes = preg_replace('/\s+/', ' ', apply_filters(
    VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG,
    implode( ' ', array_filter( $base_css_classes )),
    $this->settings['base'],
    $atts
));

// layout classes
$options = explode(',', $options);

if ( $width == '' ) {
    $layout_classes = ' container';

} else if ( $width == 'stretch_row' ) {
    $layout_classes = ' row';

} else if ( $width == 'stretch_row_content' ) {
    $layout_classes = ' clearfix';

} else if ( $width == 'stretch_row_content_no_spaces' ) {
    $layout_classes = ' row';

}

if ( in_array('window_height', $options) ) {
    $layout_classes .= ' window-height';

}

if ( in_array( 'centered', $options ) ) {
    $layout_classes .= ' centered-content';

}

// paralax class
if ( !empty($vsc_bg_image) && ($vsc_parallax == true) ) {
    $parallax_bg = ' parallax-bg-row';
}else {
    $parallax_bg = '';

}

// youtube background class
if ( !empty($vsc_youtube_url) ) {
    $ytpb_class = ' ytpb-row';
}else {
    $ytpb_class = '';

}

// text color class
if ( !empty($vsc_text_color) ) {
    $color_class = ' inherit-color';

}else {
    $color_class = '';

}

$row_class = ' class="' . $base_css_classes . $layout_classes . $parallax_bg . $ytpb_class . $color_class . '"';


// Row style
//----------------------------------------------------------

// background image
$has_image = false;

if ( (int)$vsc_bg_image > 0 && ($image_url = wp_get_attachment_url( $vsc_bg_image, 'large' )) != false ) {
    $has_image = true;
}

if ( $has_image ) {
    $background_style = 'background-image: url(' . $image_url . ');';

    $background_style .= ' background-repeat: ' . $vsc_bg_repeat . ';';

    $background_style .= ' -webkit-background-size: ' . $vsc_bg_size . '; background-size: '.$vsc_bg_size.';';

    $background_style .= ' background-position: ' . $vsc_bg_position . ';';

    if ( $vsc_parallax ) $background_style .= ' background-attachment: fixed;';
}else {
    $background_style = '';
}

// text color
if ( !empty($vsc_text_color) ) {
    $text_style = ' color: ' . $vsc_text_color . ';';
}else {
    $text_style = '';
}

$row_style = ' style="' . $background_style . $text_style . '"';

if ( !empty($vsc_bg_gradient) ) {
    $searchReplaceArray = array(
        "<br />" => "",
        "<br/>" => "",
        "\n" => "",
        "\r" => ""
    );

    $vsc_bg_gradient = str_replace( array_keys($searchReplaceArray), array_values($searchReplaceArray), $vsc_bg_gradient );
    $vsc_custom_style = ".vsc_custom_" . $vsc_id . "_" . time() . "{" . $vsc_bg_gradient . "}";
}

// Output
//----------------------------------------------------------
$output .= '<div' . $output_id . $row_class . $yt_video_bg_attrs . $row_style . '  data-token="' . $token . '">';

if ( !empty($vsc_bg_color) || !empty($vsc_custom_style) ) {
    $output .= '<div class="row-overlay clearfix ' . vc_shortcode_custom_css_class( $vsc_custom_style, ' ' ) . '" ' . ( ( !empty($vsc_bg_color) ) ? ' style="background-color: ' . $vsc_bg_color . ';"' : '' ) . '></div>';
}

if ( $width == 'stretch_row' ) {
    $output .= '<div class="container">';
}

$output .= wpb_js_remove_wpautop($content);

if ( $width == 'stretch_row' ) {
    $output .= '</div>';
}

$output .= '</div>';

// vsc_text_color

if ( !empty($vsc_custom_style) ) {
    $output .= '<style type="text/css" scoped>' . $vsc_custom_style . '</style>';
}

$output .= $this->endBlockComment('row');

echo $output;
