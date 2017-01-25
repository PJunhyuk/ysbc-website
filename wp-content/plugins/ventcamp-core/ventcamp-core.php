<?php if ( ! defined( 'ABSPATH' ) ) exit;
/*
 * Plugin Name: Ventcamp core
 * Description: Main core and misc helpers for Ventcamp WP theme
 * Version: 1.4
 * Author: Vivaco.com
 * Author URI: http://vivaco.com/
 * Developer: Vivaco
 * Developer URI: http://vivaco.com/
 * Text Domain: ventcamp-core
 */

define('VENTCAMP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

//Init WP filesystem
require_once(ABSPATH . 'wp-admin/includes/file.php');
$access_type = get_filesystem_method();
if($access_type === 'direct')
{
	/* you can safely run request_filesystem_credentials() without any issues and don't need to worry about passing in a URL */
	$creds = request_filesystem_credentials(site_url() . '/wp-admin/', '', false, false, array());

	/* initialize the API */
	if ( ! WP_Filesystem($creds) ) {
		return false;
	}
	global $wp_filesystem;
}

// Add pagination class
require VENTCAMP_PLUGIN_DIR . '/includes/pagination.class.php';

// Add Contact 7 extender with Mailchimp
require VENTCAMP_PLUGIN_DIR . '/includes/ventcamp_contact7_handler.php';

// Add Customizer Kirki Class
require VENTCAMP_PLUGIN_DIR . '/includes/lib/kirki/kirki.php';
if( !function_exists('ventcamp_kirki_update_url') ) {
    function ventcamp_kirki_update_url( $config ) {
        $config['url_path'] = plugins_url() . '/ventcamp-core/includes/lib/kirki/';
        return $config;
    }
}
add_filter( 'kirki/config', 'ventcamp_kirki_update_url' );

// Add Ventcamp Admin Dashboard Menu functions
require VENTCAMP_PLUGIN_DIR . '/includes/ventcamp_dashboard_menus.php';

// WP Mailchimp Class
require VENTCAMP_PLUGIN_DIR . '/includes/lib/mailchimp/inc/MCAPI.class.php';

// WP LESS parser
require VENTCAMP_PLUGIN_DIR . '/includes/lib/Less/Autoloader.php';
Less_Autoloader::register();

// Add ventcamp demodata importer
require VENTCAMP_PLUGIN_DIR . '/includes/lib/ventcamp-importer/demo-importer.php';

// Iinit ACF Pro
if( !function_exists('vivaco_acf_path') ) {
	function vivaco_acf_path( $path ) {
		$path = VENTCAMP_PLUGIN_DIR . '/includes/lib/acf/';
		return $path;
	}
}
add_filter('acf/settings/path', 'vivaco_acf_path');

if( !function_exists('vivaco_acf_dir') ) {
	function vivaco_acf_dir( $dir ) {
		$dir = plugins_url() . '/ventcamp-core/includes/lib/acf/';
		return $dir;
	}
}
add_filter('acf/settings/dir', 'vivaco_acf_dir');
add_filter('acf/settings/show_admin', '__return_false');

// Include ACF Pro
require VENTCAMP_PLUGIN_DIR . '/includes/lib/acf/acf.php';

if ( !function_exists( 'write_permissions_error' ) ) {
    /**
     * Check if theme has enough permisssions to write /cache files
     */
    function write_permissions_error() {
        ?>
        <div class="notice error write_permissions_error is-dismissible" >
            <p><?php esc_html__( 'Error, files cache/theme-style.css, cache/admin-style.css or cache/ventcamp-fonts.css could not be written! Please make sure there are enough permissions for a theme to create these files in /cache directory ', 'ventcamp' ); ?><a href="#">How to fix it & details</a></p>
        </div>
        <?php
    }
}

if( !function_exists('ventcamp_less_compile') ) {
	/**
	 * Compile Ventcamp LESS files
	 *
	 * @return bool
	 */
	function ventcamp_less_compile(){
		// Start WP File operations
		require_once( ABSPATH . 'wp-admin/includes/file.php' );
		WP_Filesystem();
		global $wp_filesystem;

		// Start LESSPHP parser
		$parser = new Less_Parser('cache_dir', get_temp_dir());

		$parser->SetImportDirs( array(get_template_directory() . '/css/less/' => get_template_directory_uri() ) );

		$parser->parseFile( get_template_directory() . '/css/less/style.less', get_template_directory_uri() );

		// $fields_for_dump = array();
		$less_vars = array();
		$tmp_fonts_to_load = array();
		$fonts_weights_to_load = array();
		foreach (Kirki::$fields as $field_key => $field) {
			// $fields_for_dump[] = "// " . $field['label'] . " (" . $field['type'] .  ")\n@" . $field['settings']." = " . Kirki::get_option( 'ventcamp_theme_config' , $field['settings'] ) . ";" ;
			// echo $field['settings'] . "<br>\n";
			$field_value = Kirki::get_option( 'ventcamp_theme_config' , $field['settings'] );
			if(isset($field['less'])){
				if(is_string($field['less'])){

					$quote = ((is_string($field_value) || is_bool($field_value) || is_int($field_value))
						&& isset($field['less_quote']) && $field['less_quote'] == true);

					$append = isset($field['less_append']) ? $field['less_append'] : '';


					if(is_string($field_value) || is_bool($field_value)){
						$less_vars[$field['less']] = ($quote ? "'":"") . $field_value . $append . ($quote ? "'":"");
					}else if(is_int($field_value)){

						$less_vars[$field['less']] = ($quote ? "'":"") . $field_value . 'px' . $append . ($quote ? "'":"");
					}

					if($field['type'] == 'typography' && is_array($field_value)){
						if(isset($field_value['font-family'])){
							$tmp_fonts_to_load[] = $field_value['font-family'];
						}
						if(isset($field_value['font-weight'])){
							if(!in_array($field_value['font-weight'], $fonts_weights_to_load)){
								$fonts_weights_to_load[] = $field_value['font-weight'];
							}
						}

						foreach ($field_value as $key => $value) {
							if(is_string($value)){
								$less_vars[$field['less'] . "_" . str_replace('-', '_', $key)] = $value;
							}else if (is_bool($value)) {
								$less_vars[$field['less'] . "_" . str_replace('-', '_', $key)] = ( $value ? 1 : 0);
							}else if(is_array($value)){
								foreach ($value as $val) {
									$less_vars[$field['less'] . "_" . str_replace('-', '_', $key) . "_" . $val] = true;
								}
							}
						}
					}
				}

			}
		}

		$fonts_to_load = array_unique($tmp_fonts_to_load);
		$google_fonts_import = "@import url(" . Kirki_Fonts::get_google_font_uri($fonts_to_load, $fonts_weights_to_load, ventcamp_option('typography_fonts_subsets')) . ");";

		// Writing theme fonts
		if ( ! $wp_filesystem->put_contents( get_template_directory() . '/cache/ventcamp-fonts.css', $google_fonts_import, 0644) ) {
			add_action( 'admin_notices', 'write_permissions_error' );
			return false;
		}

		if(isset($_GET['show-less']) && $_GET['show-less'] == '1'){
			var_dump($less_vars); die;
		}

		$parser->ModifyVars($less_vars);
		// $parser->parseFile( STYLES_DIR . 'animations.less',  get_template_directory_uri() );
		$css = $parser->getCss();

		// Writing main theme styles
		if ( ! $wp_filesystem->put_contents( get_template_directory() . '/cache/theme-style.css', $css, 0644) ) {
			add_action( 'admin_notices', 'write_permissions_error' );
			return false;
		}

		$admin_parser = new Less_Parser('cache_dir', get_temp_dir());
		$admin_parser->SetImportDirs( array(get_template_directory() . '/css/less/' => get_template_directory_uri() ) );
		$admin_parser->parseFile( get_template_directory() . '/css/less/admin-style.less', get_template_directory_uri() );
		$admin_parser->ModifyVars($less_vars);
		$admin_css = $admin_parser->getCss();

		// Writing admin styles
		if ( ! $wp_filesystem->put_contents( get_template_directory() . '/cache/admin-style.css', $admin_css, 0644) ) {
			add_action( 'admin_notices', 'write_permissions_error' );
			return false;
		}
	}
}

// Compile Ventcamp JS variables
if( !function_exists('ventcamp_js_variables_compile') ) {
	function ventcamp_js_variables_compile(){
		$js_setting_object = array();

		foreach (Kirki::$fields as $field_key => $field) {
			if(isset($field['to_js'])){
				$field_value = Kirki::get_option( 'ventcamp_theme_config' , $field['settings'] );
				$js_setting_object[$field['to_js']] = $field_value;
			}
		}

		if (!(is_admin()) && !empty($js_setting_object)) {
			wp_deregister_script( 'ventcamp-main' );
			//wp_register_script( 'ventcamp-main', get_template_directory_uri() . "/js/ventcamp.js", array('jquery'), false, true );
			wp_localize_script( 'ventcamp-main', 'ventcampThemeOptions', $js_setting_object );
			wp_enqueue_script( 'ventcamp-main' );
		}
	}
}

if ( !function_exists('enqueue_less_styles') ) {
    /**
     * Load all LESS source files
     *
     * @param $tag Link tag to the stylesheet
     * @param $handle Handle of the stylesheet
     *
     * @return string Link to the less stylesheet
     */
	function enqueue_less_styles($tag, $handle) {
		global $wp_styles;
		$match_pattern = '/\.less$/U';

        // Get stylesheet registered with handle
        $stylesheet = $wp_styles->registered[$handle];

		if ( preg_match( $match_pattern, $stylesheet->src ) ) {
			$handle = $stylesheet->handle;
			$media = $stylesheet->args;
			$href = $stylesheet->src . '?ver=' . $stylesheet->ver;
			$rel = isset($stylesheet->extra['alt']) && $stylesheet->extra['alt'] ? 'alternate stylesheet' : 'stylesheet';
			$title = isset($stylesheet->extra['title']) ? "title='" . esc_attr( $stylesheet->extra['title'] ) . "'" : '';

			$tag = "<link rel='stylesheet/less' id='$handle' $title href='$href' type='text/css' media='$media' />";
		}

		return $tag;
	}
	add_filter( 'style_loader_tag', 'enqueue_less_styles', 99, 2);
}

//Add LESS scripts for Customizer instant preview
if ( !function_exists('ventcamp_enqueue_less_js') ) {
	function ventcamp_enqueue_less_js() {
		global $enqueue_less;
		$enqueue_less = true;
		wp_enqueue_script( 'ventcamp-less-script', get_template_directory_uri() . '/js/less.min.js');
	}
}

if ( !function_exists('ventcamp_enqueue_less_styles') ) {
	function ventcamp_enqueue_less_styles() {
		global $enqueue_less;
		if ( $enqueue_less ) {
			wp_enqueue_style( 'less-styles', get_template_directory_uri() . '/css/less/style.less' );
		}
	}
}

if ( !function_exists('ventcamp_disable_core_plugins') ) {
    add_action('switch_theme', 'ventcamp_disable_core_plugins');

    /**
     * To prevent conflicts with other themes, ventcamp-core and ventcamp-vc-shortcodes
     * should be deactivated after theme switch.
     */
    function ventcamp_disable_core_plugins () {
		require_once VENTCAMP_PLUGIN_DIR . '/includes/lib/ventcamp-importer/plugin-manager.php';

		// Deactivate plugins, but not if switched theme is the main theme/child themes
    	if ( ! ( defined( 'THEME_NAME' ) && THEME_NAME == 'ventcamp' ) ) {
			// Init new Plugin Manager
			$plugins = new Plugin_Manager();
			// Disable them
			$plugins->deactivate_core_plugins();
		}
    }
}

add_action( 'wp_enqueue_scripts', 'ventcamp_js_variables_compile' );
add_action( 'customize_preview_init', 'ventcamp_js_variables_compile' );
add_action( 'customize_preview_init', 'ventcamp_enqueue_less_js', 0 );
add_action( 'wp_enqueue_scripts', 'ventcamp_enqueue_less_styles', 99 );
