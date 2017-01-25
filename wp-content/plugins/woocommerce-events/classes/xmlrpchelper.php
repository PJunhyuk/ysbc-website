<?php if ( ! defined( 'ABSPATH' ) ) exit; 

class XMLRPC_Helper {
    
    public $Config;
    
    public function __construct($Config) {
    
        $this->Config = $Config;
            
        $this->check_xmlrpc_enabled();
        
    }
    
    public function check_xmlrpc_enabled() {
        
        if(!$this->is_xmlrpc_enabled()) {
        
            $this->output_notices(array("XMLRPC is not enabled."));
            
        }
        
    }
    
    public function is_xmlrpc_enabled() {
        
        $returnBool = false; 
        $enabled = get_option('enable_xmlrpc');

        if($enabled) {
            $returnBool = true;
        }
        else {
            global $wp_version;
            if (version_compare($wp_version, '3.5', '>=')) {
                $returnBool = true; 
            }
            else {
                $returnBool = false;
            }  
        }
        return $returnBool;
    }
    
    
    private function output_notices($notices) {

        foreach ($notices as $notice) {

                echo "<div class='updated'><p>$notice</p></div>";

        }

    }

    
}

/**
 * Gets all events
 * 
 * @global object $wp_xmlrpc_server
 * @param array $args
 */
function woocommerce_events_get_list_of_events($args) {
    
    error_reporting(0);
    ini_set('display_errors', 0);
    
    global $wp_xmlrpc_server;
    $wp_xmlrpc_server->escape( $args );
    
    $username = $args[0];
    $password = $args[1];
    
    if ( ! $user = $wp_xmlrpc_server->login( $username, $password ) ) {
        
        return $wp_xmlrpc_server->error;
        exit();
        
    }

    if(!woocommerce_events_checkroles($user->roles)) {
        
        $output['message'] = false;
        echo json_encode($output);
        exit();
        
    }
    
    
    $args = array(
            'post_type'         => 'product',
            'order'             => 'ASC',
            'posts_per_page'    => -1,
            'meta_query' => array(
                    array(
                            'key'     => 'WooCommerceEventsEvent',
                            'value'   => 'Event',
                            'compare' => '=',
                    ),
            ),
    );
    $query = new WP_Query( $args );
    $events = $query->get_posts();
    $eventOutput = array();
    
    $x = 0;
    foreach ($events as &$event) {
        
        $eventOutput[$x]['WooCommerceEventsProductID']  = (string)$event->ID;
        $eventOutput[$x]['WooCommerceEventsName']       = (string)$event->post_title;
        $eventOutput[$x]['WooCommerceEventsDate']       = (string)get_post_meta($event->ID, 'WooCommerceEventsDate', true);
        $eventOutput[$x]['WooCommerceEventsHour']       = (string)get_post_meta($event->ID, 'WooCommerceEventsHour', true);
        $eventOutput[$x]['WooCommerceEventsMinutes']    = (string)get_post_meta($event->ID, 'WooCommerceEventsMinutes', true);
        $eventOutput[$x]['WooCommerceEventsTicketLogo'] = (string)get_post_meta($event->ID, 'WooCommerceEventsTicketLogo', true);
        
        $x++;
        
    }
    
    //exit();
    
    $eventOutput = json_encode($eventOutput);
    
    echo $eventOutput;
    
    exit();
    
}

/**
 * Gets an event
 * 
 * @global object $wp_xmlrpc_server
 * @param type $args
 * @return type
 */
function woocommerce_events_get_event($args) {
    
    error_reporting(0);
    ini_set('display_errors', 0);
    
    global $wp_xmlrpc_server;
    $wp_xmlrpc_server->escape( $args );
    
    $username   = $args[0];
    $password   = $args[1];
    $eventID    = $args[2];
    
    if ( ! $user = $wp_xmlrpc_server->login( $username, $password ) ) {
        
        return $wp_xmlrpc_server->error;
        exit();
        
    }
    
    if(!woocommerce_events_checkroles($user->roles)) {
        
        $output['message'] = false;
        echo json_encode($output);
        exit();
        
    }
    
    $event = get_post( $eventID );
    
    $eventOutput  = array();
    $x = 0;
    
    $eventOutput['WooCommerceEventsProductID']        = (string)$event->ID;
    $eventOutput['WooCommerceEventsName']             = (string)$event->post_title;
    $eventOutput['WooCommerceEventsDate']             = (string)get_post_meta($event->ID, 'WooCommerceEventsDate', true);
    $eventOutput['WooCommerceEventsHour']             = (string)get_post_meta($event->ID, 'WooCommerceEventsHour', true);
    $eventOutput['WooCommerceEventsHourEnd']          = (string)get_post_meta($event->ID, 'WooCommerceEventsHourEnd', true);
    $eventOutput['WooCommerceEventsMinutes']          = (string)get_post_meta($event->ID, 'WooCommerceEventsMinutes', true);
    $eventOutput['WooCommerceEventsMinutesEnd']       = (string)get_post_meta($event->ID, 'WooCommerceEventsMinutesEnd', true);
    $eventOutput['WooCommerceEventsLocation']         = (string)get_post_meta($event->ID, 'WooCommerceEventsLocation', true);
    $eventOutput['WooCommerceEventsTicketLogo']       = (string)get_post_meta($event->ID, 'WooCommerceEventsTicketLogo', true);
    $eventOutput['WooCommerceEventsSupportContact']   = (string)get_post_meta($event->ID, 'WooCommerceEventsSupportContact', true);
    $eventOutput['WooCommerceEventsEmail']            = (string)get_post_meta($event->ID, 'WooCommerceEventsEmail', true);
    $eventOutput['WooCommerceEventsGPS']              = (string)get_post_meta($event->ID, 'WooCommerceEventsGPS', true);
    $eventOutput['WooCommerceEventsGoogleMaps']       = (string)get_post_meta($event->ID, 'WooCommerceEventsGoogleMaps', true);
    $eventOutput['WooCommerceEventsDirections']       = (string)get_post_meta($event->ID, 'WooCommerceEventsDirections', true);
    
    $eventOutput = json_encode($eventOutput);
    
    echo $eventOutput;
    
    exit();
}

/**
 * Gets a list of tickets belonging to an event
 * 
 * @global object $wp_xmlrpc_server
 * @param array $args
 */
function woocommerce_events_get_tickets_in_event($args) {
    
    /*error_reporting(E_ALL);
    ini_set('display_errors', '1');*/
    
    error_reporting(0);
    ini_set('display_errors', 0);
    
    global $woocommerce;
    global $wp_xmlrpc_server;
    $wp_xmlrpc_server->escape( $args );
    
    $username   = $args[0];
    $password   = $args[1];
    $eventID    = $args[2];
    
    if ( ! $user = $wp_xmlrpc_server->login( $username, $password ) ) {
        
        return $wp_xmlrpc_server->error;
        exit();
        
    }
    
    if(!woocommerce_events_checkroles($user->roles)) {
        
        $output['message'] = false;
        echo json_encode($output);
        exit();
        
    }
    $ticketStatusOptions = array();
    $globalWooCommerceHideUnpaidTicketsApp   = get_option('globalWooCommerceHideUnpaidTicketsApp', true);
    
    if($globalWooCommerceHideUnpaidTicketsApp == 'yes') {
        
        $ticketStatusOptions = array( 'key' => 'WooCommerceEventsStatus', 'compare' => '!=', 'value' => 'Unpaid' );
        
    }
    
    $events_query = new WP_Query( array('post_type' => array('event_magic_tickets'), 'posts_per_page' => -1, 'meta_query' => array( array( 'key' => 'WooCommerceEventsProductID', 'value' => $eventID ), $ticketStatusOptions )) );
    $tickets = $events_query->get_posts();
    
    $event          = get_post( $eventID );

    $ticketsOutput  = array();
    $x = 0;
    foreach($tickets as &$ticket) {

        $order_id                                               = get_post_meta($ticket->ID, 'WooCommerceEventsOrderID', true);
        $order                                                  = new WC_Order( $order_id );
        
        $ticketsOutput[$x]['customerFirstName']                 = (string)$order->billing_first_name;
        $ticketsOutput[$x]['customerLastName']                  = (string)$order->billing_last_name;
        $ticketsOutput[$x]['WooCommerceEventsAttendeeName']     = (string)get_post_meta($ticket->ID, 'WooCommerceEventsAttendeeName', true);
        //$ticketsOutput[$x]['WooCommerceEventsAttendeeEmail']    = (string)get_post_meta($ticket->ID, 'WooCommerceEventsAttendeeEmail', true);
        $ticketsOutput[$x]['WooCommerceEventsTicketID']         = (string)get_post_meta($ticket->ID, 'WooCommerceEventsTicketID', true);
        $ticketsOutput[$x]['WooCommerceEventsStatus']           = (string)get_post_meta($ticket->ID, 'WooCommerceEventsStatus', true);
        
        
        $x++;
        
    }
    
    $ticketsOutput = json_encode($ticketsOutput);
    
    echo $ticketsOutput;
    
    exit();
    
    
}

function woocommerce_events_get_ticket($args) {
    
    /*error_reporting(E_ALL);
    ini_set('display_errors', '1');*/
    
    error_reporting(0);
    ini_set('display_errors', 0);
    
    global $woocommerce;
    global $wp_xmlrpc_server;
    $wp_xmlrpc_server->escape( $args );
    
    $username    = $args[0];
    $password    = $args[1];
    $ticketID    = $args[2];
    
    if ( ! $user = $wp_xmlrpc_server->login( $username, $password ) ) {
        
        return $wp_xmlrpc_server->error;
        exit();
        
    }
    
    if(!woocommerce_events_checkroles($user->roles)) {
        
        $output['message'] = false;
        echo json_encode($output);
        exit();
        
    }
    
    $events_query = new WP_Query( array('post_type' => array('event_magic_tickets'), 'meta_query' => array( array( 'key' => 'WooCommerceEventsTicketID', 'value' => $ticketID ) )) );
    $ticket = $events_query->get_posts();
    $ticket = $ticket[0];
    
    $ticketOutput = array();
    
    $order_id                              = get_post_meta($ticket->ID, 'WooCommerceEventsOrderID', true);
    $order                                 = new WC_Order( $order_id );
    
    $ticketOutput['customerFirstName']                 = (string)$order->billing_first_name;
    $ticketOutput['customerLastName']                  = (string)$order->billing_last_name;
    $ticketOutput['customerEmail']                     = (string)$order->billing_email;
    $ticketOutput['customerPhone']                     = (string)$order->billing_phone;
    $ticketOutput['WooCommerceEventsAttendeeName']     = (string)get_post_meta($ticket->ID, 'WooCommerceEventsAttendeeName', true);
    $ticketOutput['WooCommerceEventsAttendeeEmail']    = (string)get_post_meta($ticket->ID, 'WooCommerceEventsAttendeeEmail', true);
    $ticketOutput['WooCommerceEventsTicketID']         = (string)get_post_meta($ticket->ID, 'WooCommerceEventsTicketID', true);
    $ticketOutput['WooCommerceEventsTicketType']       = (string)get_post_meta($ticket->ID, 'WooCommerceEventsTicketType', true);
    $ticketOutput['WooCommerceEventsStatus']           = (string)get_post_meta($ticket->ID, 'WooCommerceEventsStatus', true);
    $WooCommerceEventsVariations                       = json_decode(get_post_meta($ticket->ID, 'WooCommerceEventsVariations', true), true);
    
    $WooCommerceEventsVariationsOutput = array();
    
    if(!empty($WooCommerceEventsVariations)) {
        foreach($WooCommerceEventsVariations as $variationName => $variationValue) {


            $variationNameOutput = str_replace('attribute_', '', $variationName);
            $variationNameOutput = str_replace('pa_', '', $variationNameOutput);
            $variationNameOutput = str_replace('_', ' ', $variationNameOutput);
            $variationNameOutput = str_replace('-', ' ', $variationNameOutput);
            $variationNameOutput = str_replace('Pa_', '', $variationNameOutput);
            $variationNameOutput = ucwords($variationNameOutput);

            $variationValueOutput = str_replace('_', ' ', $variationValue);
            $variationValueOutput = str_replace('-', ' ', $variationValueOutput);
            $variationValueOutput = ucwords($variationValueOutput);
            
            $WooCommerceEventsVariationsOutput[$variationNameOutput] = (string)$variationValueOutput;

        }
    }
    
    $ticketOutput['WooCommerceEventsVariations'] = $WooCommerceEventsVariationsOutput;

    $ticketOutput = json_encode($ticketOutput);
    
    echo $ticketOutput;
    
    exit();
}

/**
 * Gets the owner of a ticket
 * 
 * @global object $wp_xmlrpc_server
 * @param array $args
 */
function woocommerce_events_get_ticket_user($args) {
    
    /*error_reporting(E_ALL);
    ini_set('display_errors', '1');*/
    
    error_reporting(0);
    ini_set('display_errors', 0);
    
    global $wp_xmlrpc_server;
    $wp_xmlrpc_server->escape( $args );
    
    $username   = $args[0];
    $password   = $args[1];
    $userID    = $args[2];

    if ( ! $user = $wp_xmlrpc_server->login( $username, $password ) ) {
        
        return $wp_xmlrpc_server->error;
        exit();
        
    }
    
    if(!woocommerce_events_checkroles($user->roles)) {
        
        $output['message'] = false;
        echo json_encode($output);
        exit();
        
    }
    
    $user = get_user_meta($userID);
    
    $user['customerFirstName']  = $user['billing_first_name'][0];
    $user['customerLastName']   = $user['billing_last_name'][0];
    $user['customerEmail']      = $user['billing_email'][0];
    $user['customerPhone']      = $user['billing_phone'][0];
    
    $user = json_encode($user);
    echo $user;
    
    exit();
    
}

/**
 * Updates a tickets status
 * 
 */
function woocommerce_events_update_ticket_status($args) {
    
    /*error_reporting(E_ALL);
    ini_set('display_errors', '1');*/
    
    error_reporting(0);
    ini_set('display_errors', 0);
    
    global $wp_xmlrpc_server;
    $wp_xmlrpc_server->escape( $args );
    
    $username           = $args[0];
    $password           = $args[1];
    $ticketPostID       = $args[2];
    $status             = $args[3];
    
    if ( ! $user = $wp_xmlrpc_server->login( $username, $password ) ) {
        
        return $wp_xmlrpc_server->error;
        exit();
        
    }
    
    if(!woocommerce_events_checkroles($user->roles)) {
        
        $output['message'] = false;
        echo json_encode($output);
        exit();
        
    }
    
    $events_query = new WP_Query( array('post_type' => array('event_magic_tickets'), 'meta_query' => array( array( 'key' => 'WooCommerceEventsTicketID', 'value' => $ticketPostID ) )) );
    $ticket = $events_query->get_posts();
    $ticket = $ticket[0];
    
    $output = array();
    
    if(!empty($status)) {
        if(update_post_meta( $ticket->ID, 'WooCommerceEventsStatus', strip_tags( $status ))) {

            $output['message'] = 'Status updated';

        } 
    } else {
        
        $output['message'] = 'Status is required';
        
    }
    
    $output = json_encode($output);
    
    echo $output;
    exit();
    
}

/**
 * Checks a users login details
 * 
 * @global object $wp_xmlrpc_server
 * @param type $args
 */
function woocommerce_events_login_status($args) {
    
    /*error_reporting(E_ALL);
    ini_set('display_errors', '1');*/
    
    error_reporting(0);
    ini_set('display_errors', 0);
    
    global $wp_xmlrpc_server;
    $wp_xmlrpc_server->escape( $args );
    
    $username           = $args[0];
    $password           = $args[1];
    
    if ( ! $user = $wp_xmlrpc_server->login( $username, $password ) ) {
        
        $output['message'] = false;
        
    } else {
        
        $output['message'] = true;
        
    }
    
    if(!woocommerce_events_checkroles($user->roles)) {
        
        $output['message'] = false;
        echo json_encode($output);
        exit();
        
    }
    
    $output = json_encode($output);
    
    echo $output;
    exit();
    
}


function woocommerce_events_new_xmlrpc_methods( $methods ) {
    
    error_reporting(0);
    ini_set('display_errors', 0);
    
    $methods['woocommerce_events.get_list_of_events'] = 'woocommerce_events_get_list_of_events';
    $methods['woocommerce_events.get_event'] = 'woocommerce_events_get_event';
    $methods['woocommerce_events.get_ticket'] = 'woocommerce_events_get_ticket';
    $methods['woocommerce_events.get_tickets_in_event'] = 'woocommerce_events_get_tickets_in_event';
    $methods['woocommerce_events.get_ticket_user'] = 'woocommerce_events_get_ticket_user';
    $methods['woocommerce_events.update_ticket_status'] = 'woocommerce_events_update_ticket_status';
    $methods['woocommerce_events.login_status'] = 'woocommerce_events_login_status';
    
    return $methods;   
    
}
add_filter( 'xmlrpc_methods', 'woocommerce_events_new_xmlrpc_methods');

function woocommerce_events_checkroles($roles) {
    
    $acceptableRoles = array('administrator', 'editor', 'author', 'contributor');
    
    foreach($roles as $key => $role) {
        
        if(in_array($role, $acceptableRoles)) {
            
            return true;
            
        }
        
        
    }
    
    
}


?>
