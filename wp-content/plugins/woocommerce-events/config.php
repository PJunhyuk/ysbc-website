<?php if ( ! defined( 'ABSPATH' ) ) exit; 

class WooCommerce_Events_Config {
	
        public $pluginDirectory;
	public $path;
	public $classPath;
	public $templatePath; 
        public $barcodePath;
	public $scriptsPath;
	public $stylesPath;
	public $emailTemplatePath;
        public $pluginURL;
        public $eventPluginURL;
        public $clientMode;
        public $salt;
	
        /**
         * Initialize configuration variables to be used as object.
         * 
         */
	public function __construct() {
                
                $this->pluginDirectory = 'woocommerce_events';
		$this->path = plugin_dir_path( __FILE__ );
		$this->pluginURL = plugin_dir_url(__FILE__);
		$this->classPath = plugin_dir_path( __FILE__ ).'classes/';
		$this->templatePath = plugin_dir_path( __FILE__ ).'templates/';
		$this->barcodePath = plugin_dir_path( __FILE__ ).'barcodes/';
		$this->emailTemplatePath = plugin_dir_path( __FILE__ ).'templates/email/';
		$this->emailTemplatePathThemeEmail = get_stylesheet_directory().'/'.$this->pluginDirectory.'/templates/email/';
		$this->emailTemplatePathTheme = get_stylesheet_directory().'/'.$this->pluginDirectory.'/templates/';
		$this->scriptsPath = plugin_dir_url(__FILE__) .'js/';
		$this->stylesPath = plugin_dir_url(__FILE__) .'css/';
                $this->eventPluginURL = plugins_url().'/'.$this->pluginDirectory.'/';
                $this->clientMode = false;
                $this->salt = get_option('woocommerce_events_do_salt');
	
	}

} 