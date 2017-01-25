<?php if ( ! defined( 'ABSPATH' ) ) exit; 
/**
 * Plugin Name: WooCommerce Events
 * Description: Adds event and ticketing features to WooCommerce
 * Version: 1.1.11
 * Author: Grenade
 * Author URI: http://grenadeco.com/
 * Developer: Grenade
 * Developer URI: http://grenadeco.com/
 * Text Domain: woocommerce-events
 *
 * Copyright: © 2009-2015 WooThemes.
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

//include config
require(WP_PLUGIN_DIR.'/woocommerce-events/config.php');

class WooCommerce_Events {

    private $WooHelper;
    private $ICSHelper;
    private $Config;
    private $XMLRPCHelper;
    private $CommsHelper;
    private $CheckoutHelper;
    private $Salt;

    public function __construct() {

        $plugin = plugin_basename(__FILE__); 
        
        add_action( 'init', array( $this, 'plugin_init' ) );
        add_action( 'admin_init', array( $this, 'register_scripts' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'register_scripts_frontend' ) );
        add_action( 'admin_init', array( $this, 'register_styles' ) );
        add_action( 'admin_init', array( $this, 'activation_redirect' ) );
        add_action( 'admin_menu', array( $this, 'add_woocommerce_submenu' ) );
        add_action( 'woocommerce_settings_tabs_settings_woocommerce_events', array( $this, 'add_settings_tab_settings' ) );
        add_action( 'woocommerce_update_options_settings_woocommerce_events', array( $this, 'update_settings_tab_settings' ) );
        add_action( 'wp_ajax_woocommerce_events_ics', array( $this, 'woocommerce_events_ics' ) );
        add_action( 'wp_ajax_nopriv_woocommerce_events_ics', array( $this, 'woocommerce_events_ics' ) );
        add_action( 'plugins_loaded', array( $this, 'load_text_domain' ) );

        add_action( 'wp_ajax_woocommerce_events_cancel', array( $this, 'woocommerce_events_cancel' ) );
        add_action( 'wp_ajax_nopriv_woocommerce_events_cancel', array( $this, 'woocommerce_events_cancel' ) );
        
        add_filter( 'woocommerce_settings_tabs_array', array( $this, 'add_settings_tab' ) );
        add_filter( 'plugin_action_links_'.$plugin, array( $this, 'add_plugin_links' ) );
        add_filter( 'add_to_cart_text', array( $this, 'woo_custom_cart_button_text' ) );
        add_filter( 'woocommerce_product_single_add_to_cart_text', array( $this, 'woo_custom_cart_button_text' ) );
        add_filter( 'woocommerce_product_add_to_cart_text', array( $this, 'woo_custom_cart_button_text' ) );
        add_filter( 'wp_footer', array( $this, 'thickbox' ) );

    }

    /**
     *  Initialize events plugin and helpers.
     * 
     */
    public function plugin_init() {

        //Main config
        $this->Config = new WooCommerce_Events_Config();

        //WooHelper
        require_once($this->Config->classPath.'woohelper.php');
        $this->WooHelper = new Woo_Helper($this->Config);
        
        //ICSHelper
        require_once($this->Config->classPath.'icshelper.php');
        $this->ICSHelper = new ICS_Helper($this->Config);
        
        //CommsHelper
        require_once($this->Config->classPath.'commshelper.php');
        $this->CommsHelper = new Comms_Helper($this->Config);
        
        //XMLRPCHelper
        require_once($this->Config->classPath.'xmlrpchelper.php');
        $this->XMLRPCHelper = new XMLRPC_Helper($this->Config);
        
        //AttendeeHelper
        require_once($this->Config->classPath.'checkouthelper.php');
        $this->CheckoutHelper = new Checkout_Helper($this->Config);
        
        $this->Salt = $this->Config->salt;
        
        if(empty($this->Salt)) {
            
            $salt = rand(111111,999999); 
            add_option('woocommerce_events_do_salt', $salt);
            $this->Salt = $salt;
            $this->Config->salt = $salt;
        }
        
    }
    
    /**
     * Register plugin scripts.
     * 
     */
    public function register_scripts() {

        wp_enqueue_script( 'woocommerce-events-admin-script',  $this->Config->scriptsPath . 'events-admin.js', array(), '1.0.0', true  );
        wp_enqueue_script('jquery-ui-datepicker');
        wp_enqueue_script( 'wp-color-picker');

    }
    
    public function register_scripts_frontend() {

        wp_enqueue_script( 'woocommerce-events-front-script',  $this->Config->scriptsPath . 'events-frontend.js', array(), '1.0.0', true  );
        
    }
    
    /**
     * Register plugin styles.
     * 
     */
    public function register_styles() {

        wp_enqueue_style( 'woocommerce-events-admin-script',  $this->Config->stylesPath . 'events-admin.css', array(), '1.0.0', true  );
        wp_enqueue_style('jquery-style', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');
        wp_enqueue_style( 'wp-color-picker');

    }

    /**
     * Outputs notices to screen.
     * 
     * @param array $notices
     */
    private function output_notices($notices) {

        foreach ($notices as $notice) {

                echo "<div class='updated'><p>$notice</p></div>";

        }

    }

    /**
     * Adds option to for redirect.
     * 
     */
    static function activate_plugin() {
        
        $salt = rand(111111,999999); 
        add_option('woocommerce_events_do_salt', $salt);
        add_option('woocommerce_events_do_redirect', true);

    }

    /**
     * Redirects plugin on activation
     * 
     */
    public function activation_redirect() {

        if (get_option('woocommerce_events_do_redirect', false)) {

            delete_option('woocommerce_events_do_redirect');
            wp_redirect('admin.php?page=woocommerce-events-help'); exit;

        }

    }
    
    /**
     * Adds a settings tab to WooCommerce
     * 
     * @param array $settings_tabs
     */
    public function add_settings_tab($settings_tabs) {

        $settings_tabs['settings_woocommerce_events'] = __( 'Events', 'woocommerce-settings-woocommerce-events' );
        return $settings_tabs;


    }

    /**
     * Adds the WooCommerce tab settings
     * 
     */
    public function add_settings_tab_settings() {

        woocommerce_admin_fields( $this->get_tab_settings() );

    }

    /**
     * Gets the WooCommerce tab settings
     * 
     * @return array $settings
     */
    public function get_tab_settings() {
        
        $settings = array(
            'section_title' => array(
                'name'      => __( 'Event Settings', 'woocommerce-events' ),
                'type'      => 'title',
                'desc'      => '',
                'id'        => 'wc_settings_woocommerce_events_section_title'
            ),
            'globalWooCommerceEventsTicketBackgroundColor' => array(
                'name'      => __( 'Global ticket border', 'woocommerce-events' ),
                'type'      => 'text',
                'id'        => 'globalWooCommerceEventsTicketBackgroundColor',
                'class'     => 'color-field'
            ),
            'globalWooCommerceEventsTicketButtonColor' => array(
                'name'      => __( 'Global ticket button', 'woocommerce-events' ),
                'type'      => 'text',
                'id'        => 'globalWooCommerceEventsTicketButtonColor',
                'class'     => 'color-field'
            ),
            'globalWooCommerceEventsTicketTextColor' => array(
                'name'      => __( 'Global ticket button text', 'woocommerce-events' ),
                'type'      => 'text',
                'id'        => 'globalWooCommerceEventsTicketTextColor',
                'class'     => 'color-field'
            ),
            'globalWooCommerceEventsTicketLogo' => array(
                'name'      => __( 'Global ticket logo', 'woocommerce-events' ),
                'type'      => 'text',
                'id'        => 'globalWooCommerceEventsTicketLogo',
                'desc'      => __( 'URL to ticket logo file', 'woocommerce-events' ),
                'class' => 'text uploadfield'
            ),
            'globalWooCommerceEventsChangeAddToCart' => array(
                'name'  => __( 'Change add to cart text', 'woocommerce-events' ),
                'type'  => 'checkbox',
                'id'    => 'globalWooCommerceEventsChangeAddToCart',
                'desc'  => __( 'Changes "Add to cart" to "Book ticket" for event products', 'woocommerce-events' ),
                'class' => 'text uploadfield'
            ),
            'globalWooCommerceHideEventDetailsTab' => array(
                'name'      => __( 'Hide event details tab', 'woocommerce-events' ),
                'type'      => 'checkbox',
                'id'        => 'globalWooCommerceHideEventDetailsTab',
                'desc'      => __( 'Hides the event details tab on the product page', 'woocommerce-events' ),
                'class'     => 'text uploadfield' 
           ),
           'globalWooCommerceHideUnpaidTicketsApp' => array(
                'name'      => __( 'Hide unpaid tickets in app', 'woocommerce-events' ),
                'type'      => 'checkbox',
                'id'        => 'globalWooCommerceHideUnpaidTicketsApp',
                'desc'      => __( 'Hides the unpaid tickets in the iOS and Android apps', 'woocommerce-events' ),
                'class'     => 'text uploadfield' 
           ), 
            'globalWooCommerceEventsEmailAttendees' => array(
                'name'  => __( 'Email tickets to attendees', 'woocommerce-events' ),
                'type'  => 'checkbox',
                'id'    => 'globalWooCommerceEventsEmailAttendees',
                'desc'  => __( 'Sends tickets to attendees rather than the purchaser. Make sure Capture individual attendee details? is enabled in your products', 'woocommerce-events' )
            )
        );
        
        if($this->Config->clientMode === true) {
            $settings['globalWooCommerceEventsChangeCanceledMessage'] = array(
                'name'  => __( 'Canceled ticket message', 'woocommerce-events' ),
                'type'  => 'textarea',
                'id'    => 'globalWooCommerceEventsCanceledTicketMessage',
                'default' => __( 'Your ticket has been canceled.', 'woocommerce-events' ),
                'class' => 'text uploadfield'
            );
        }
        
        $settings['section_end'] = array(
            'type' => 'sectionend',
            'id' => 'wc_settings_tab_demo_section_end'
        );
        
        return $settings;
    }

    /**
     * Saves the WooCommerce tab settings
     * 
     */
    public function update_settings_tab_settings() {

        woocommerce_update_options( $this->get_tab_settings() );

    }
    
    /**
     * Adds the WooCommerce sub menu
     * 
     */
    public function add_woocommerce_submenu() {

        add_submenu_page( 'null',__( 'WooCommerce Events Introduction', 'woocommerce-events' ), __( 'WooCommerce Events Introduction', 'woocommerce-events' ), 'manage_options', 'woocommerce-events-help', array($this, 'add_woocommerce_submenu_page') ); 

    }
    
    /**
     * Adds the WooCommerce sub menu page
     * 
     */
    public function add_woocommerce_submenu_page() {
        
        require($this->Config->templatePath.'pluginintroduction.php');

    }
    
    /**
     * Adds plugin links to the plugins page
     * 
     * @param array $links
     * @return array $links
     */
    public function add_plugin_links($links) {
        
        $linkSettings = '<a href="admin.php?page=wc-settings&tab=settings_woocommerce_events">'.__( 'Settings', 'woocommerce-events' ).'</a>'; 
        array_unshift($links, $linkSettings); 
        
        $linkIntroduction = '<a href="admin.php?page=woocommerce-events-help">'.__( 'Introduction', 'woocommerce-events' ).'</a>'; 
        array_unshift($links, $linkIntroduction); 
        
        return $links;
        
    }
    
    /**
     * Builds the calendar ICS file
     * 
     */
    public function woocommerce_events_ics() {
        
        $event = (int)$_GET['event'];
        
        $post = get_post($event);
        
        $WooCommerceEventsEvent         = get_post_meta($event, 'WooCommerceEventsEvent', true);
        $WooCommerceEventsDate          = get_post_meta($event, 'WooCommerceEventsDate', true);
        $WooCommerceEventsHour          = get_post_meta($event, 'WooCommerceEventsHour', true);
        $WooCommerceEventsMinutes       = get_post_meta($event, 'WooCommerceEventsMinutes', true);
        $WooCommerceEventsHourEnd       = get_post_meta($event, 'WooCommerceEventsHourEnd', true);
        $WooCommerceEventsMinutesEnd    = get_post_meta($event, 'WooCommerceEventsMinutesEnd', true);
        $WooCommerceEventsLocation      = get_post_meta($event, 'WooCommerceEventsLocation', true);
        
        $newDate = date("Y-m-d", strtotime($WooCommerceEventsDate));
        
        $this->ICSHelper->build_ICS($newDate." ".$WooCommerceEventsHour.":".$WooCommerceEventsMinutes."",$newDate." ".$WooCommerceEventsHourEnd.":".$WooCommerceEventsMinutesEnd."",$post->post_title,get_bloginfo('name'),"GU1 1AA");
        $this->ICSHelper->show();
        
        exit();
    }
    
    public function woocommerce_events_cancel() {
        
        $ticketID   = (int)$_GET['id'];
        $timestamp  = (int)$_GET['t'];
        $key        = $_GET['k'];
        
        $serial = md5($ticketID + $timestamp + $this->Salt);

        //echo $serial;
        //echo '-->'.$this->Salt; exit();
        
        if($serial != $key) {

            echo "Error!";
            exit();
            
        } else {
            
            $tickets = new WP_Query( array('post_type' => array('event_magic_tickets'), 'meta_query' => array( array( 'key' => 'WooCommerceEventsTicketID', 'value' => $ticketID ) )) );
            $tickets = $tickets->get_posts();
            
            /*echo "<pre>";
                print_r($tickets);
            echo "</pre>";*/
            
            $ticket = $tickets[0];
            
            update_post_meta($ticket->ID, 'WooCommerceEventsStatus', 'Canceled');
            wp_redirect( home_url().'?wc_events=canceled' ); exit();
            
        }
        
        exit();
    }
    
    /**
     * Changes the WooCommerce 'Add to cart' text
     * 
     */
    public function woo_custom_cart_button_text($text) {
        
        global $post;
        
        $WooCommerceEventsEvent                         = get_post_meta($post->ID, 'WooCommerceEventsEvent', true);
        $globalWooCommerceEventsChangeAddToCart         = get_option('globalWooCommerceEventsChangeAddToCart', true);
        
        if($WooCommerceEventsEvent == 'Event' && $globalWooCommerceEventsChangeAddToCart === 'yes') {
        
            return __( 'Book ticket', 'woocommerce-events' );
        
        } else {
            
            return $text;
            
        }
        
    }
    
    public function load_text_domain() {
        


        $path = dirname( plugin_basename( __FILE__ ) ) . '/languages/';
        $loaded = load_plugin_textdomain( 'woocommerce-events', false, $path);
        
        /*if ( ! $loaded )
        {
            print "File not found: $path"; 
            exit;
        }*/
        
    }
    
    /**
     * Adds thickbox for notifications
     * 
     */
    public function thickbox() {

        
        /*if(!empty($_GET['wc_events'])) {
            
            if($_GET['wc_events'] == 'canceled') {
                
                $globalWooCommerceEventsCanceledTicketMessage = get_option('globalWooCommerceEventsCanceledTicketMessage', true);
                
                if(empty($globalWooCommerceEventsCanceledTicketMessage) || $globalWooCommerceEventsCanceledTicketMessage == '1') {
                    
                    $globalWooCommerceEventsCanceledTicketMessage = "Your ticket has been canceled.";
                    
                }
                
                add_thickbox();
                echo '
                    <div id="woocommerce-events-cancel-box" style="display:none;">
                        <p>
                             '.$globalWooCommerceEventsCanceledTicketMessage.'
                        </p>
                    </div>
                ';

            }
        }*/
        
    }

}

$WooCommerce_Events = new WooCommerce_Events();
register_activation_hook( __FILE__, array( 'WooCommerce_Events', 'activate_plugin' ) );

//TODO: move this function into WooHelper
function displayEventTab() {
    
    global $post;
    $Config = new WooCommerce_Events_Config();
    
    $WooCommerceEventsEvent             = get_post_meta($post->ID, 'WooCommerceEventsEvent', true);
    $WooCommerceEventsDate              = get_post_meta($post->ID, 'WooCommerceEventsDate', true);
    $WooCommerceEventsHour              = get_post_meta($post->ID, 'WooCommerceEventsHour', true);
    $WooCommerceEventsMinutes           = get_post_meta($post->ID, 'WooCommerceEventsMinutes', true);
    $WooCommerceEventsHourEnd           = get_post_meta($post->ID, 'WooCommerceEventsHourEnd', true);
    $WooCommerceEventsMinutesEnd        = get_post_meta($post->ID, 'WooCommerceEventsMinutesEnd', true);
    $WooCommerceEventsLocation          = get_post_meta($post->ID, 'WooCommerceEventsLocation', true);
    $WooCommerceEventsTicketLogo        = get_post_meta($post->ID, 'WooCommerceEventsTicketLogo', true);
    $WooCommerceEventsSupportContact    = get_post_meta($post->ID, 'WooCommerceEventsSupportContact', true);
    $WooCommerceEventsGPS               = get_post_meta($post->ID, 'WooCommerceEventsGPS', true);
    $WooCommerceEventsDirections        = get_post_meta($post->ID, 'WooCommerceEventsDirections', true);
    $WooCommerceEventsEmail             = get_post_meta($post->ID, 'WooCommerceEventsEmail', true);
    
    if(file_exists($Config->emailTemplatePathTheme.'eventtab.php') ) {
        
        require($Config->templatePath.'eventtab.php');
    
    } else {
        
        require($Config->templatePath.'eventtab.php');
        
    }
    
}

function displayEventTabMap() {
    
    global $post;
    $Config = new WooCommerce_Events_Config();
    
    $WooCommerceEventsGoogleMaps                    = get_post_meta($post->ID, 'WooCommerceEventsGoogleMaps', true);
    
    $eventContent = $post->post_content;
    
    if(file_exists($Config->emailTemplatePathTheme.'eventtabmap.php') ) {
    
        require($Config->emailTemplatePathTheme.'eventtabmap.php');
        
    } else {
        
        require($Config->templatePath.'eventtabmap.php');
        
    }
    
}

function woocommerce_events_ics() {
    
    $Config = new WooCommerce_Events_Config();
    
    
}

if ( class_exists( 'WooCommerce_Events_PDFs' ) ) {

    echo "Hello";

} 
