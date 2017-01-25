<?php

defined('ABSPATH') or die('No direct access');

// Require Site_Logo class
require_once "site-logo.class.php";

// Check if LESS Debug is on, to instantly regenerate styles from LESS on each page reload
if ( VENTCAMP_LESS_COMPILE_DEBUG == true ){
    add_action( 'after_setup_theme', 'ventcamp_less_compile' );
} else {
    add_action( 'customize_save_after', 'ventcamp_less_compile', 99 );
}

if( !function_exists('ventcamp_enqueue_styles') ) {
    /**
     * Enqueue Ventcamp styles
     */
    function ventcamp_enqueue_styles() {

        wp_enqueue_style('bootstrap',       THEME_URI . '/css/lib/bootstrap.min.css');
        wp_enqueue_style('font-awesome',    THEME_URI . '/css/lib/font-awesome.min.css');
        wp_enqueue_style('font-lineicons',  THEME_URI . '/css/lib/font-lineicons.css');
        wp_enqueue_style('toastr',          THEME_URI . '/css/lib/toastr.min.css');
        wp_enqueue_style('ventcamp-style',  get_stylesheet_directory_uri() . '/style.css');

        //wp_enqueue_style( 'ventcamp-less-styles', get_template_directory_uri() . '/css/less/style.less');
    }

    add_action('wp_enqueue_scripts', 'ventcamp_enqueue_styles', 50);
}

if( !function_exists('ventcamp_check_css_cache') ) {
    /**
     * Check if needed CSS files exist, create them if not
     */
    function ventcamp_check_css_cache(){
        // If one of the files does not exist
        if( !file_exists( get_template_directory() . '/cache/theme-style.css' ) ||
            !file_exists( get_template_directory() . '/cache/admin-style.css' ) ||
            !file_exists( get_template_directory() . '/cache/ventcamp-fonts.css' ) ) {
            ventcamp_less_compile(); //re-create new LESS files
        }
    }
}
add_action( 'wp_enqueue_scripts', 'ventcamp_check_css_cache', 100, 0);

if( !function_exists('ventcamp_append_head_styles') ) {
    /**
     * Include Custom CSS to header
     */
    function ventcamp_append_head_styles(){
        $global_css = ventcamp_option('customcode_global_css');
        $tablet_css = ventcamp_option('customcode_tablet_css');
        $phone_css  = ventcamp_option('customcode_phone_css');

        // If no custom code is defined, return
        if ( empty( $global_css ) && empty( $tablet_css ) && empty( $phone_css ) ) {
            return;
        }

        ?>
            <style type='text/css'>
                <?php
                    if( !empty($global_css) ) {
                        echo $global_css;
                    }

                    if( !empty($tablet_css) ) : ?>
                        @media (max-width: 992px) {
                            <?php echo $tablet_css; ?>
                        }
                    <?php endif;

                    if( !empty($phone_css) ) : ?>
                        @media (max-width: 767px) {
                            <?php echo $phone_css; ?>
                        }
                    <?php endif;
                ?>
            </style>
        <?php
    }
}
add_action( 'wp_head', 'ventcamp_append_head_styles', 100, 0);

if( !function_exists('ventcamp_hero_block') ) {
    /**
     * Show hero block before or after menu
     *
     * @param string $position Position of the hero block - before or after menu
     */
    function ventcamp_hero_block( $position = 'after' ){
        // Default hero_display option
        $default = '1';

        // Is hero block enabled in settings?
        $show_hero = (intval(ventcamp_option('hero_display', $default)) == 1 && is_front_page()) ||
                      intval(ventcamp_option('hero_display', $default)) == 2;

	    if( $show_hero && ventcamp_option('hero_menu_position', 'after') == $position ) {
	        get_template_part( 'content', 'hero' );
	    }
    }
}

if ( !function_exists('ventcamp_link_to_menu_editor') ) {
    /**
     * Menu fallback. Link to the menu editor if that is useful.
     *
     * @param array $args Array of arguments
     * @see wp-includes/nav-menu-template.php for available arguments
     *
     * @return string|bool Link to the editor
     */
    function ventcamp_link_to_menu_editor ( $args ) {
        // Only privileged users can see 'add a menu' link
        if ( !current_user_can( 'manage_options' ) ) {
            return false;
        }

        // Extract all arguments into variables
        extract( $args );

        // Link to menu editor
        $link = $link_before . '<a href="' . admin_url( 'nav-menus.php' ) . '">' . $before . __( 'Add a menu', 'ventcamp' ) . $after . '</a>' . $link_after;
        // Wrap link in <li> tag
        $link = "<li>$link</li>";

        // Make a formatted string, wrapper in specified tag with menu id and menu class
        $output = sprintf( $items_wrap, $menu_id, $menu_class, $link );

        // Only if container is not empty
        if ( !empty( $container ) ) {
            $output  = "<$container class='$container_class' id='$container_id'>$output</$container>";
        }

        // If echo flag is set
        if ( $args['echo'] ) {
            echo $output;
        }

        return $output;
    }
}

if ( !function_exists('ventcamp_header_menu') ) {
    /**
     * Show menu in header, if menu is enabled in settings
     */
    function ventcamp_header_menu () {
        // Default value for header_format_style
        $default = 'logo_menu_button';
        $header_format = explode("_", ventcamp_option( 'header_format_style', $default ));

        if( in_array('menu', $header_format) ) {
            wp_nav_menu( array(
                'theme_location' => 'primary',
                'container' => false,
                'menu_class' => 'navigation-list pull-left',
                'echo' => true,
                'items_wrap' => '<ul id="%1$s" class="%2$s">%3$s</ul>',
                'depth' => 2,
                'fallback_cb' => 'ventcamp_link_to_menu_editor',
            ));
        }
    }
}

if ( !function_exists('ventcamp_disable_sticky_header') ) {
    /**
     * Disable sticky header, if it is disabled in settings
     */
    function ventcamp_disable_sticky_header( $classes ) {
        if ( ventcamp_option('header_disable_fixed') ) {
            $classes[] = 'sticky-disabled';
        }
        return $classes;
    }
}
add_filter( 'body_class', 'ventcamp_disable_sticky_header' );

if ( !function_exists('ventcamp_header_logo') ) {
    /**
     * Check type of the logotype (text or image) and if a custom image is set, use it
     */
    function ventcamp_header_logo () {
        $logo = new Site_Logo();
        $logo->render();
    }
}

if ( !function_exists( 'ventcamp_header_button' ) ) {
    function ventcamp_header_button () {
        $header_format = explode( "_", ventcamp_option( 'header_format_style', 'logo_menu_button' ) );
        $button_text   = ventcamp_option( 'header_button_text', 'Buy Tickets' );
        $button_link   = ventcamp_option( 'header_button_link', '#' );

        if( in_array('button', $header_format) ): ?>
            <a href="<?php echo $button_link; ?>" class="pull-right btn-alt btn-sm buy-btn"><?php echo $button_text; ?></a>
        <?php endif;
    }
}

if ( !function_exists('ventcamp_enqueue_admin_scripts') ) {
    /**
     * Load theme Admin scripts
     */
    function ventcamp_enqueue_admin_scripts() {
        // Load Theme admin scripts
        wp_enqueue_style( 'ventcamp-admin-style',        THEME_URI . '/cache/admin-style.css');
        wp_enqueue_style( 'ventcamp-admin-woocommerce',  THEME_URI . '/css/ventcamp-admin.css');
    }
}

add_action('admin_enqueue_scripts', 'ventcamp_enqueue_admin_scripts');