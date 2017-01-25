<?php if ( ! defined( 'ABSPATH' ) ) exit; 

class Woo_Helper {
	
	public  $Config;
        public  $TicketHelper;
        private $BarcodeHelper;
        public  $MailHelper;
	
	public function __construct($config) {

            $this->check_woocommerce_exists();
            $this->Config = $config;
            
            //TicketHelper
            require_once($this->Config->classPath.'tickethelper.php');
            $this->TicketHelper = new Ticket_Helper($this->Config);
            
            //BarcodeHelper
            require_once($this->Config->classPath.'barcodehelper.php');
            $this->BarcodeHelper = new Barcode_Helper($this->Config);
            
            //MailHelper
            require_once($this->Config->classPath.'mailhelper.php');
            $this->MailHelper = new Mail_Helper($this->Config);
            
            add_action('woocommerce_product_tabs', array(&$this, 'add_front_end_tab'), 10, 2);
            add_action('woocommerce_order_status_completed', array(&$this, 'send_ticket_email'), 10, 1);
            add_action( 'woocommerce_product_write_panel_tabs', array( $this, 'add_product_options_tab' ) );
            add_action( 'woocommerce_product_write_panels', array( $this, 'add_product_options_tab_options' ) );
            add_action( 'woocommerce_process_product_meta', array( $this, 'process_meta_box' ) );
            add_action( 'wp_ajax_woocommerce_events_csv', array( $this, 'woocommerce_events_csv' ) );
            add_action( 'wp_ajax_nopriv_woocommerce_events_csv', array( $this, 'woocommerce_events_csv' ) );
            add_action( 'wp_ajax_nopriv_woocommerce_events_csv', array( $this, 'woocommerce_events_csv' ) );

	}
        
        /**
         * Checks if the WooCommerce plugin exists
         * 
         */
	public function check_woocommerce_exists() {
	
            if ( !class_exists( 'WooCommerce' ) ) {

                    $this->output_notices(array(__( 'WooCommerce is not active. Please install and activate it.', 'woocommerce-events' )));

            } 
	
	}
        
        /**
         * Initializes the WooCommerce meta box
         * 
         */
        public function add_product_options_tab() {

            echo '<li class="custom_tab"><a href="#woocommerce_events_data">'.__( 'Event', 'woocommerce-events' ).'</a></li>';

        }
        
        
        /**
         * Displays the event form 
         * 
         * @param object $post
         */
        public function add_product_options_tab_options() {
            
            global $post;
            
            $WooCommerceEventsEvent                     = get_post_meta($post->ID, 'WooCommerceEventsEvent', true);
            $WooCommerceEventsDate                      = get_post_meta($post->ID, 'WooCommerceEventsDate', true);
            $WooCommerceEventsHour                      = get_post_meta($post->ID, 'WooCommerceEventsHour', true);
            $WooCommerceEventsMinutes                   = get_post_meta($post->ID, 'WooCommerceEventsMinutes', true);
            $WooCommerceEventsHourEnd                   = get_post_meta($post->ID, 'WooCommerceEventsHourEnd', true);
            $WooCommerceEventsMinutesEnd                = get_post_meta($post->ID, 'WooCommerceEventsMinutesEnd', true);
            $WooCommerceEventsLocation                  = get_post_meta($post->ID, 'WooCommerceEventsLocation', true);
            $WooCommerceEventsTicketLogo                = get_post_meta($post->ID, 'WooCommerceEventsTicketLogo', true);
            $WooCommerceEventsSupportContact            = get_post_meta($post->ID, 'WooCommerceEventsSupportContact', true);
            $WooCommerceEventsGPS                       = get_post_meta($post->ID, 'WooCommerceEventsGPS', true);
            $WooCommerceEventsGoogleMaps                = get_post_meta($post->ID, 'WooCommerceEventsGoogleMaps', true);
            $WooCommerceEventsDirections                = get_post_meta($post->ID, 'WooCommerceEventsDirections', true);
            $WooCommerceEventsEmail                     = get_post_meta($post->ID, 'WooCommerceEventsEmail', true);
            $WooCommerceEventsTicketBackgroundColor     = get_post_meta($post->ID, 'WooCommerceEventsTicketBackgroundColor', true);
            $WooCommerceEventsTicketButtonColor         = get_post_meta($post->ID, 'WooCommerceEventsTicketButtonColor', true);
            $WooCommerceEventsTicketTextColor           = get_post_meta($post->ID, 'WooCommerceEventsTicketTextColor', true);
            $WooCommerceEventsTicketPurchaserDetails    = get_post_meta($post->ID, 'WooCommerceEventsTicketPurchaserDetails', true);
            $WooCommerceEventsTicketAddCalendar         = get_post_meta($post->ID, 'WooCommerceEventsTicketAddCalendar', true);
            $WooCommerceEventsTicketDisplayDateTime     = get_post_meta($post->ID, 'WooCommerceEventsTicketDisplayDateTime', true);
            $WooCommerceEventsTicketDisplayBarcode      = get_post_meta($post->ID, 'WooCommerceEventsTicketDisplayBarcode', true);
            $WooCommerceEventsTicketDisplayPrice        = get_post_meta($post->ID, 'WooCommerceEventsTicketDisplayPrice', true);
            $WooCommerceEventsTicketText                = get_post_meta($post->ID, 'WooCommerceEventsTicketText', true);
            $WooCommerceEventsCaptureAttendeeDetails    = get_post_meta($post->ID, 'WooCommerceEventsCaptureAttendeeDetails', true);
            $WooCommerceEventsSendEmailTickets          = get_post_meta($post->ID, 'WooCommerceEventsSendEmailTickets', true);
            
            $globalWooCommerceEventsTicketBackgroundColor   = get_option('globalWooCommerceEventsTicketBackgroundColor', true);
            $globalWooCommerceEventsTicketButtonColor       = get_option('globalWooCommerceEventsTicketButtonColor', true);
            $globalWooCommerceEventsTicketLogo              = get_option('globalWooCommerceEventsTicketLogo', true);

            
            require($this->Config->templatePath.'eventmetaoptions.php');

        }
	
        /**
         * Processes the meta box form once the plubish / update button is clicked.
         * 
         * @global object $woocommerce_errors
         * @param int $post_id
         * @param object $post
         */
        public function process_meta_box($post_id) {
            
            global $woocommerce_errors;

            if(isset($_POST['WooCommerceEventsEvent'])) {
                
                update_post_meta($post_id, 'WooCommerceEventsEvent', $_POST['WooCommerceEventsEvent']);
                
            }
            
            if(isset($_POST['WooCommerceEventsDate'])) {
                
                update_post_meta($post_id, 'WooCommerceEventsDate', $_POST['WooCommerceEventsDate']);
                
            }
            
            if(isset($_POST['WooCommerceEventsHour'])) {
                
                update_post_meta($post_id, 'WooCommerceEventsHour', $_POST['WooCommerceEventsHour']);
                
            }
            
            if(isset($_POST['WooCommerceEventsMinutes'])) {
                
                update_post_meta($post_id, 'WooCommerceEventsMinutes', $_POST['WooCommerceEventsMinutes']);
                
            }
            
            if(isset($_POST['WooCommerceEventsLocation'])) {
                
                $WooCommerceEventsLocation = htmlentities(stripslashes($_POST['WooCommerceEventsLocation']));
                
                update_post_meta($post_id, 'WooCommerceEventsLocation', $WooCommerceEventsLocation);
                
            }
            
            if(isset($_POST['WooCommerceEventsTicketLogo'])) {
                
                update_post_meta($post_id, 'WooCommerceEventsTicketLogo', $_POST['WooCommerceEventsTicketLogo']);
                
            }
            
            if(isset($_POST['WooCommerceEventsTicketText'])) {
                
                update_post_meta($post_id, 'WooCommerceEventsTicketText', $_POST['WooCommerceEventsTicketText']);
                
            }
            
            if(isset($_POST['WooCommerceEventsSupportContact'])) {
                
                $WooCommerceEventsSupportContact = htmlentities(stripslashes($_POST['WooCommerceEventsSupportContact']));
                
                update_post_meta($post_id, 'WooCommerceEventsSupportContact', $WooCommerceEventsSupportContact);
                
            }
            
            if(isset($_POST['WooCommerceEventsHourEnd'])) {
                
                update_post_meta($post_id, 'WooCommerceEventsHourEnd', $_POST['WooCommerceEventsHourEnd']);
                
            }
            
            if(isset($_POST['WooCommerceEventsMinutesEnd'])) {
                
                update_post_meta($post_id, 'WooCommerceEventsMinutesEnd', $_POST['WooCommerceEventsMinutesEnd']);
                
            }
            
            if(isset($_POST['WooCommerceEventsGPS'])) {
                
                $WooCommerceEventsGPS = htmlentities(stripslashes($_POST['WooCommerceEventsGPS']));
                
                update_post_meta($post_id, 'WooCommerceEventsGPS',  htmlentities(stripslashes($_POST['WooCommerceEventsGPS'])));
                
            }
            
            if(isset($_POST['WooCommerceEventsDirections'])) {
                
                $WooCommerceEventsDirections = htmlentities(stripslashes($_POST['WooCommerceEventsDirections']));
                
                update_post_meta($post_id, 'WooCommerceEventsDirections', $WooCommerceEventsDirections);
                
            }
            
            if(isset($_POST['WooCommerceEventsEmail'])) {
                
                $WooCommerceEventsEmail = esc_textarea($_POST['WooCommerceEventsEmail']);
                
                update_post_meta($post_id, 'WooCommerceEventsEmail', $WooCommerceEventsEmail);
                
            }
            
            if(isset($_POST['WooCommerceEventsTicketBackgroundColor'])) {
                
                update_post_meta($post_id, 'WooCommerceEventsTicketBackgroundColor', $_POST['WooCommerceEventsTicketBackgroundColor']);
                
            }
            
            if(isset($_POST['WooCommerceEventsTicketButtonColor'])) {
                
                update_post_meta($post_id, 'WooCommerceEventsTicketButtonColor', $_POST['WooCommerceEventsTicketButtonColor']);
                
            }
            
            if(isset($_POST['WooCommerceEventsTicketTextColor'])) {
                
                update_post_meta($post_id, 'WooCommerceEventsTicketTextColor', $_POST['WooCommerceEventsTicketTextColor']);
                
            }
            
            if(isset($_POST['WooCommerceEventsGoogleMaps'])) {
                
                update_post_meta($post_id, 'WooCommerceEventsGoogleMaps', $_POST['WooCommerceEventsGoogleMaps']);
                
            }
            
            if(isset($_POST['WooCommerceEventsTicketPurchaserDetails'])) {
                
                update_post_meta($post_id, 'WooCommerceEventsTicketPurchaserDetails', $_POST['WooCommerceEventsTicketPurchaserDetails']);
                
            } else {
                
                update_post_meta($post_id, 'WooCommerceEventsTicketPurchaserDetails', 'off');
                
            }
            
            if(isset($_POST['WooCommerceEventsTicketAddCalendar'])) {
                
                update_post_meta($post_id, 'WooCommerceEventsTicketAddCalendar', $_POST['WooCommerceEventsTicketAddCalendar']);
                
            } else {
                
                update_post_meta($post_id, 'WooCommerceEventsTicketAddCalendar', 'off');
                
            }

            if(isset($_POST['WooCommerceEventsTicketDisplayDateTime'])) {
                
                update_post_meta($post_id, 'WooCommerceEventsTicketDisplayDateTime', $_POST['WooCommerceEventsTicketDisplayDateTime']);
                
            } else {
                
                update_post_meta($post_id, 'WooCommerceEventsTicketDisplayDateTime', 'off');
                
            }
            
            if(isset($_POST['WooCommerceEventsTicketDisplayBarcode'])) {
                
                update_post_meta($post_id, 'WooCommerceEventsTicketDisplayBarcode', $_POST['WooCommerceEventsTicketDisplayBarcode']);
                
            } else {
                
                update_post_meta($post_id, 'WooCommerceEventsTicketDisplayBarcode', 'off');
                
            }
            
            if(isset($_POST['WooCommerceEventsTicketDisplayPrice'])) {
                
                update_post_meta($post_id, 'WooCommerceEventsTicketDisplayPrice', $_POST['WooCommerceEventsTicketDisplayPrice']);
                
            } else {
                
                update_post_meta($post_id, 'WooCommerceEventsTicketDisplayPrice', 'off');
                
            }
            
            if(isset($_POST['WooCommerceEventsCaptureAttendeeDetails'])) {
                
                update_post_meta($post_id, 'WooCommerceEventsCaptureAttendeeDetails', $_POST['WooCommerceEventsCaptureAttendeeDetails']);
                
            } else {
                
                update_post_meta($post_id, 'WooCommerceEventsCaptureAttendeeDetails', 'off');
                
            }
            
            if(isset($_POST['WooCommerceEventsSendEmailTickets'])) {
                
                update_post_meta($post_id, 'WooCommerceEventsSendEmailTickets', $_POST['WooCommerceEventsSendEmailTickets']);
                
            } else {
                
                update_post_meta($post_id, 'WooCommerceEventsSendEmailTickets', 'off');
                
            }

        }
        
        /**
         * Displays the event details on the front end template. Before WooCommerce Displays content.
         * 
         * @param array $tabs
         * @global object $post
         * @return array $tabs
         */
        public function add_front_end_tab($tabs) {
            
            global $post;
            
            $WooCommerceEventsEvent = get_post_meta($post->ID, 'WooCommerceEventsEvent', true);
            
            $WooCommerceEventsGoogleMaps = get_post_meta($post->ID, 'WooCommerceEventsGoogleMaps', true);
            
            $globalWooCommerceHideEventDetailsTab   = get_option('globalWooCommerceHideEventDetailsTab', true);
            
            if($WooCommerceEventsEvent == 'Event') {
                
                if($globalWooCommerceHideEventDetailsTab != 'yes') {
                
                    $tabs['woocommerce_events'] = array(
                        'title'     => __('Event Details', 'woocommerce-events'),
                        'priority'  => 30,
                        'callback'  => 'displayEventTab'
                    );

                }
                
                if(!empty($WooCommerceEventsGoogleMaps)) {
                    
                    $tabs['description'] = array(
                        'title'     => __('Description', 'woocommerce-events'),
                        'priority' => 1,
                        'callback'  => 'displayEventTabMap'
                    );
                    
                }
                
            }
            return $tabs;
            
        }
        
        /**
         * Sends a ticket email once an order is completed.
         * 
         * @param int $order_id
         * @global $woocommerce, $evotx;
         */
     public function send_ticket_email($order_id) {
            
            error_reporting(E_ALL);
            ini_set('display_errors', '1');
         
            set_time_limit(0);
         
            global $woocommerce;
            
            $order = new WC_Order( $order_id );
            $tickets = $order->get_items();
            
            $WooCommerceEventsTicketsPurchased = get_post_meta($order_id, 'WooCommerceEventsTicketsPurchased', true);
            $WooCommerceEventsTicketsPurchased = json_decode($WooCommerceEventsTicketsPurchased, true);

            $customer = get_post_meta($order_id, '_customer_user', true);
            $usermeta = get_user_meta($customer);

            $WooCommerceEventsSentTicket        =  get_post_meta($order_id, 'WooCommerceEventsSentTicket', true);
           
            
            $customerDetails = array(
                        'customerID'        => $customer
            );

            $customerDetails['customerFirstName']   = $order->billing_first_name;
            $customerDetails['customerLastName']    = $order->billing_last_name;
            $customerDetails['customerEmail']       = $order->billing_email;
            
            
            $tickets = new WP_Query( array('post_type' => array('event_magic_tickets'), 'posts_per_page' => -1, 'meta_query' => array( array( 'key' => 'WooCommerceEventsOrderID', 'value' => $order_id ) )) );
            $tickets = $tickets->get_posts();
            
            
            $body = '';
            $header = $this->MailHelper->parse_email_template('header.php', $customerDetails); 
            $footer = $this->MailHelper->parse_email_template('footer.php', $customerDetails); 
            $ticketBody = '';
            
            $globalWooCommerceEventsEmailAttendees = get_option('globalWooCommerceEventsEmailAttendees', true);
            
            foreach ($tickets as $ticketItem) {
                
                $WooCommerceEventsProductID                 = get_post_meta($ticketItem->ID, 'WooCommerceEventsProductID', true);
                $WooCommerceEventsTicketType                = get_post_meta($ticketItem->ID, 'WooCommerceEventsTicketType', true);
                $WooCommerceEventsTicketID                  = get_post_meta($ticketItem->ID, 'WooCommerceEventsTicketID', true);
                $WooCommerceEventsStatus                    = get_post_meta($ticketItem->ID, 'WooCommerceEventsStatus', true);
                $ticket['WooCommerceEventsVariations']      = json_decode(get_post_meta($ticketItem->ID, 'WooCommerceEventsVariations', true));
                $WooCommerceEventsEvent                     = get_post_meta($WooCommerceEventsProductID, 'WooCommerceEventsEvent', true);
                $WooCommerceEventsCaptureAttendeeDetails    = get_post_meta($WooCommerceEventsProductID, 'WooCommerceEventsCaptureAttendeeDetails', true);
                $WooCommerceEventsSendEmailTickets          = get_post_meta($WooCommerceEventsProductID, 'WooCommerceEventsSendEmailTickets', true);
                
                
                if($WooCommerceEventsTicketsPurchased[$WooCommerceEventsProductID] > 0) {
     
                    if($WooCommerceEventsEvent == 'Event') {

                        //update ticket as paid
                        if($WooCommerceEventsStatus == 'Unpaid') {

                            update_post_meta($ticketItem->ID, 'WooCommerceEventsStatus', 'Not Checked In');

                        }

                        //ticket details for email
                        $ticket['WooCommerceEventsEvent']                       = get_post_meta($WooCommerceEventsProductID, 'WooCommerceEventsEvent', true);
                        $ticket['WooCommerceEventsDate']                        = get_post_meta($WooCommerceEventsProductID, 'WooCommerceEventsDate', true);
                        $ticket['WooCommerceEventsHour']                        = get_post_meta($WooCommerceEventsProductID, 'WooCommerceEventsHour', true);
                        $ticket['WooCommerceEventsMinutes']                     = get_post_meta($WooCommerceEventsProductID, 'WooCommerceEventsMinutes', true);
                        $ticket['WooCommerceEventsHourEnd']                     = get_post_meta($WooCommerceEventsProductID, 'WooCommerceEventsHourEnd', true);
                        $ticket['WooCommerceEventsMinutesEnd']                  = get_post_meta($WooCommerceEventsProductID, 'WooCommerceEventsMinutesEnd', true);
                        $ticket['WooCommerceEventsLocation']                    = get_post_meta($WooCommerceEventsProductID, 'WooCommerceEventsLocation', true);
                        $ticket['WooCommerceEventsTicketLogo']                  = get_post_meta($WooCommerceEventsProductID, 'WooCommerceEventsTicketLogo', true);
                        $ticket['WooCommerceEventsSupportContact']              = get_post_meta($WooCommerceEventsProductID, 'WooCommerceEventsSupportContact', true);
                        $ticket['WooCommerceEventsTicketBackgroundColor']       = get_post_meta($WooCommerceEventsProductID, 'WooCommerceEventsTicketBackgroundColor', true);
                        $ticket['WooCommerceEventsTicketButtonColor']           = get_post_meta($WooCommerceEventsProductID, 'WooCommerceEventsTicketButtonColor', true);
                        $ticket['WooCommerceEventsTicketPurchaserDetails']      = get_post_meta($WooCommerceEventsProductID, 'WooCommerceEventsTicketPurchaserDetails', true);
                        $ticket['WooCommerceEventsTicketAddCalendar']           = get_post_meta($WooCommerceEventsProductID, 'WooCommerceEventsTicketAddCalendar', true);
                        $ticket['WooCommerceEventsTicketDisplayDateTime']       = get_post_meta($WooCommerceEventsProductID, 'WooCommerceEventsTicketDisplayDateTime', true);
                        $ticket['WooCommerceEventsTicketDisplayBarcode']        = get_post_meta($WooCommerceEventsProductID, 'WooCommerceEventsTicketDisplayBarcode', true);
                        $ticket['WooCommerceEventsTicketText']                  = get_post_meta($WooCommerceEventsProductID, 'WooCommerceEventsTicketText', true);
                        $ticket['WooCommerceEventsDirections']                  = get_post_meta($WooCommerceEventsProductID, 'WooCommerceEventsDirections', true);
                        $ticket['WooCommerceEventsTicketDisplayPrice']          = get_post_meta($WooCommerceEventsProductID, 'WooCommerceEventsTicketDisplayPrice', true);

                        $ticket['WooCommerceEventsTicketType']                  = $WooCommerceEventsTicketType;
                        $ticket['WooCommerceEventsProductID']                   = $WooCommerceEventsProductID;
                        $ticket['WooCommerceEventsTicketID']                    = $WooCommerceEventsTicketID;
                        $ticket['name']                                         = $product->post_title;

                        $timestamp                                              = time();
                        $key                                                    = md5($WooCommerceEventsTicketID + $timestamp + $this->Config->salt);                              
                        $ticket['cancelLink']                                   = get_site_url().'/wp-admin/admin-ajax.php?action=woocommerce_events_cancel&id='.$WooCommerceEventsTicketID.'&t='.$timestamp.'&k='.$key;

                        if($WooCommerceEventsCaptureAttendeeDetails === 'on') {

                            $ticket['customerFirstName']        = get_post_meta($ticketItem->ID, 'WooCommerceEventsAttendeeName', true);
                            $ticket['customerLastName']         = '';

                        } else {

                            $ticket['customerFirstName']        = $customerDetails['customerFirstName']; 
                            $ticket['customerLastName']         = $customerDetails['customerLastName'];

                        }

                        $WooCommerceEventsAttendeeEmail = get_post_meta($ticketItem->ID, 'WooCommerceEventsAttendeeEmail', true);

                        //generate barcode
                        if (!file_exists($this->Config->barcodePath.$ticket['WooCommerceEventsTicketID'].'.png')) {

                            $this->BarcodeHelper->generate_barcode($ticket['WooCommerceEventsTicketID']);

                        }
                        
                        if($WooCommerceEventsSentTicket != 'Yes' && $globalWooCommerceEventsEmailAttendees == 'yes') {

                            //email attendee
                            $ticketBody = $this->MailHelper->parse_ticket_template($ticket);
                            if(!empty($ticketBody)) {

                                $body       = $header.$ticketBody.$footer;
                                $subject    = __('Ticket', 'woocommerce-events');
                                $fromEmail  = get_bloginfo('admin_email');  
                                $fromName   = get_bloginfo('name'); 
                                $from       = get_option( 'woocommerce_email_from_name' ).' <'.sanitize_email( get_option( 'woocommerce_email_from_address' ) ).'>';
                                $to         = $WooCommerceEventsAttendeeEmail;

                                $headers = 'From: '.$from;
                                
                                if(!empty($ticketBody) && $WooCommerceEventsSendEmailTickets != 'off') {

                                    //$sendMail = wp_mail($to, $subject, $body, $headers);
                                    //if($this->MailHelper->send_ticket($to, $subject, $body, $headers)) {

                                    //}
                                    $this->MailHelper->send_ticket($to, $subject, $body, $headers);
                                }



                            }

                            $ticketBody = '';

                        } else {

                            //email tickets to purchaser later
                            $ticketBody .= $this->MailHelper->parse_ticket_template($ticket);

                        }

                        $WooCommerceEventsTicketsPurchased[$WooCommerceEventsProductID] = $WooCommerceEventsTicketsPurchased[$WooCommerceEventsProductID] - 1;

                    }
                }
                
            }
            
            if($WooCommerceEventsSentTicket != 'Yes' && $globalWooCommerceEventsEmailAttendees != 'yes') {
                
                //email purchaser

                $body       = $header.$ticketBody.$footer;
                $subject    = '[#'.$order_id.']'.__('Tickets', 'woocommerce-events');
                $fromEmail  = get_bloginfo('admin_email');  
                $fromName   = get_bloginfo('name'); 
                $from       = get_option( 'woocommerce_email_from_name' ).' <'.sanitize_email( get_option( 'woocommerce_email_from_address' ) ).'>';
                $to         = $customerDetails['customerEmail'];

                $headers = 'From: '.$from;
                
                if(!empty($ticketBody) && $WooCommerceEventsSendEmailTickets != 'off') {

                    //$sendMail = wp_mail($to, $subject, $body, $headers);
                    $this->MailHelper->send_ticket($to, $subject, $body, $headers);

                }


            }
            
            update_post_meta($order_id, 'WooCommerceEventsSentTicket', 'Yes');
            /*echo $body;
            echo 'Processed: '.time();
            exit();*/
         
            /*set_time_limit(0);
         
            global $woocommerce;
            
            $order = new WC_Order( $order_id );
            $tickets = $order->get_items();
            
            $WooCommerceEventsTicketsPurchased = get_post_meta($order_id, 'WooCommerceEventsTicketsPurchased', true);
            $WooCommerceEventsTicketsPurchased = json_decode($WooCommerceEventsTicketsPurchased, true);

            $customer = get_post_meta($order_id, '_customer_user', true);
            $usermeta = get_user_meta($customer);
            
            $WooCommerceEventsSentTicket        =  get_post_meta($order_id, 'WooCommerceEventsSentTicket', true);
            
            $tickets = new WP_Query( array('post_type' => array('event_magic_tickets'), 'posts_per_page' => -1, 'meta_query' => array( array( 'key' => 'WooCommerceEventsOrderID', 'value' => $order_id ) )) );
            $tickets = $tickets->get_posts();
            
            
            $body = '';
            $header = $this->MailHelper->parse_email_template('header.php', array()); 
            $footer = $this->MailHelper->parse_email_template('footer.php', array()); 
            $ticketBody = '';
            
            $globalWooCommerceEventsEmailAttendees = get_option('globalWooCommerceEventsEmailAttendees', true);
            
            foreach ($tickets as $ticketItem) {
                
                $WooCommerceEventsProductID                 = get_post_meta($ticketItem->ID, 'WooCommerceEventsProductID', true);
                $WooCommerceEventsTicketType                = get_post_meta($ticketItem->ID, 'WooCommerceEventsTicketType', true);
                $WooCommerceEventsTicketID                  = get_post_meta($ticketItem->ID, 'WooCommerceEventsTicketID', true);
                $WooCommerceEventsStatus                    = get_post_meta($ticketItem->ID, 'WooCommerceEventsStatus', true);
                $ticket['WooCommerceEventsVariations']      = json_decode(get_post_meta($ticketItem->ID, 'WooCommerceEventsVariations', true));
                $WooCommerceEventsEvent                     = get_post_meta($WooCommerceEventsProductID, 'WooCommerceEventsEvent', true);
                $WooCommerceEventsCaptureAttendeeDetails    = get_post_meta($WooCommerceEventsProductID, 'WooCommerceEventsCaptureAttendeeDetails', true);
                $WooCommerceEventsSendEmailTickets          = get_post_meta($WooCommerceEventsProductID, 'WooCommerceEventsSendEmailTickets', true);
                
                $product = get_post($WooCommerceEventsProductID); 
                
                if($WooCommerceEventsTicketsPurchased[$WooCommerceEventsProductID] > 0) {
                    
                    
                    if($WooCommerceEventsEvent == 'Event') {
                        
                        
                        //update ticket as paid
                        if($WooCommerceEventsStatus == 'Unpaid') {

                            update_post_meta($ticketItem->ID, 'WooCommerceEventsStatus', 'Not Checked In');

                        }
                        
                        //ticket details for email
                        $ticket['WooCommerceEventsEvent']                       = get_post_meta($WooCommerceEventsProductID, 'WooCommerceEventsEvent', true);
                        $ticket['WooCommerceEventsDate']                        = get_post_meta($WooCommerceEventsProductID, 'WooCommerceEventsDate', true);
                        $ticket['WooCommerceEventsHour']                        = get_post_meta($WooCommerceEventsProductID, 'WooCommerceEventsHour', true);
                        $ticket['WooCommerceEventsMinutes']                     = get_post_meta($WooCommerceEventsProductID, 'WooCommerceEventsMinutes', true);
                        $ticket['WooCommerceEventsHourEnd']                     = get_post_meta($WooCommerceEventsProductID, 'WooCommerceEventsHourEnd', true);
                        $ticket['WooCommerceEventsMinutesEnd']                  = get_post_meta($WooCommerceEventsProductID, 'WooCommerceEventsMinutesEnd', true);
                        $ticket['WooCommerceEventsLocation']                    = get_post_meta($WooCommerceEventsProductID, 'WooCommerceEventsLocation', true);
                        $ticket['WooCommerceEventsTicketLogo']                  = get_post_meta($WooCommerceEventsProductID, 'WooCommerceEventsTicketLogo', true);
                        $ticket['WooCommerceEventsSupportContact']              = get_post_meta($WooCommerceEventsProductID, 'WooCommerceEventsSupportContact', true);
                        $ticket['WooCommerceEventsTicketBackgroundColor']       = get_post_meta($WooCommerceEventsProductID, 'WooCommerceEventsTicketBackgroundColor', true);
                        $ticket['WooCommerceEventsTicketButtonColor']           = get_post_meta($WooCommerceEventsProductID, 'WooCommerceEventsTicketButtonColor', true);
                        $ticket['WooCommerceEventsTicketPurchaserDetails']      = get_post_meta($WooCommerceEventsProductID, 'WooCommerceEventsTicketPurchaserDetails', true);
                        $ticket['WooCommerceEventsTicketAddCalendar']           = get_post_meta($WooCommerceEventsProductID, 'WooCommerceEventsTicketAddCalendar', true);
                        $ticket['WooCommerceEventsTicketDisplayDateTime']       = get_post_meta($WooCommerceEventsProductID, 'WooCommerceEventsTicketDisplayDateTime', true);
                        $ticket['WooCommerceEventsTicketText']                  = get_post_meta($WooCommerceEventsProductID, 'WooCommerceEventsTicketText', true);
                        $ticket['WooCommerceEventsDirections']                  = get_post_meta($WooCommerceEventsProductID, 'WooCommerceEventsDirections', true);

                        $ticket['WooCommerceEventsTicketType']                  = $WooCommerceEventsTicketType;
                        $ticket['WooCommerceEventsProductID']                   = $WooCommerceEventsProductID;
                        $ticket['WooCommerceEventsTicketID']                    = $WooCommerceEventsTicketID;
                        $ticket['name']                                         = $product->post_title;

                        $timestamp                                              = time();
                        $key                                                    = md5($WooCommerceEventsTicketID + $timestamp + $this->Config->salt);                              
                        $ticket['cancelLink']                                   = get_site_url().'/wp-admin/admin-ajax.php?action=woocommerce_events_cancel&id='.$WooCommerceEventsTicketID.'&t='.$timestamp.'&k='.$key;
                        
                        
                        
                        
                    }
                    
                    
                }
                
            }
            
            echo "<br />End.";
            exit();*/
            
        }
        
        public function woocommerce_events_csv() {
            
            /*error_reporting(E_ALL);
            ini_set('display_errors', '1');*/
            
            error_reporting(0);
            ini_set('display_errors', 0);
            
            global $woocommerce;

            $event = $_GET['event'];
            
            $events_query = new WP_Query( array('post_type' => array('event_magic_tickets'), 'posts_per_page' => -1, 'meta_query' => array( array( 'key' => 'WooCommerceEventsProductID', 'value' => $event ) )) );
            $events = $events_query->get_posts();
            header('Content-type: text/csv');
            header('Content-Disposition: attachment; filename="'.date("Ymdhis").'.csv"');
            
            $csvOutput = array();
            foreach($events as $eventItem) {
                
                $id = $eventItem->ID;
                
                $order_id                           = get_post_meta($id, 'WooCommerceEventsOrderID', true);
                $customer_id                        = get_post_meta($id, 'WooCommerceEventsCustomerID', true);
                $WooCommerceEventsStatus            = get_post_meta($id, 'WooCommerceEventsStatus', true);
                $WooCommerceEventsVariations        = json_decode(get_post_meta($id, 'WooCommerceEventsVariations', true));
                $order = new WC_Order( $order_id );
                
                $ticket = get_post($id);
                $ticketID = $ticket->post_title;
                $ticketType = get_post_meta($ticket->ID, 'WooCommerceEventsTicketType', true);
                
                if(empty($customer_id)) {
                    
                    $customer_id = $ticket->post_author;
                    
                }
                
                $purchaser = get_user_meta($customer_id);
                $orderID = get_post_meta($ticket->ID, 'WooCommerceEventsOrderID', true);
                
                $order = new WC_Order( $orderID );
                $fp = fopen('php://output', 'w');
                
                $csvListName = '';
                $WooCommerceEventsAttendeeName = get_post_meta($ticket->ID, 'WooCommerceEventsAttendeeName', true);
                
                if(empty($WooCommerceEventsAttendeeName)) {
                    
                    $csvListName = $order->billing_first_name.' '.$order->billing_last_name;
                    
                } else {
                    
                    $csvListName = $WooCommerceEventsAttendeeName;

                }    
                
                $csvListEmail = '';
                $WooCommerceEventsAttendeeEmail = get_post_meta($ticket->ID, 'WooCommerceEventsAttendeeEmail', true);
                
                if(empty($WooCommerceEventsAttendeeEmail)) {
                    
                    $csvListEmail = $order->billing_email;
                    
                } else {
                    
                    $csvListEmail = $WooCommerceEventsAttendeeEmail;

                } 
                
                if(!empty($order->post->post_status)) {
                
                    if($order->post->post_status == 'wc-completed' && $WooCommerceEventsStatus != 'Unpaid') {

                        $variationOutput = '';
                        $i = 0;
                        if(!empty($WooCommerceEventsVariations)) {
                            foreach($WooCommerceEventsVariations as $variationName => $variationValue) {

                                if($i > 0) {

                                    $variationOutput .= ' | ';

                                }
                                
                                $variationNameOutput = str_replace('attribute_', '', $variationName);
                                $variationNameOutput = str_replace('pa_', '', $variationNameOutput);
                                $variationNameOutput = str_replace('_', ' ', $variationNameOutput);
                                $variationNameOutput = str_replace('-', ' ', $variationNameOutput);
                                $variationNameOutput = str_replace('Pa_', '', $variationNameOutput);
                                $variationNameOutput = ucwords($variationNameOutput);
                                
                                $variationValueOutput = str_replace('_', ' ', $variationValue);
                                $variationValueOutput = str_replace('-', ' ', $variationValueOutput);
                                $variationValueOutput = ucwords($variationValueOutput);
                                
                                $variationOutput .= $variationNameOutput.': '.$variationValueOutput;

                                $i++;
                            }
                        }
                        $csvOutput[] = array($ticketID, $csvListName, $csvListEmail, $WooCommerceEventsStatus, $ticketType, $variationOutput);
                    }
                
                }
                
            }
            if(empty($csvOutput)) {

                $csvOutput[] = array(__('No tickets found.', 'woocommerce-events'));

            }

            foreach ($csvOutput as $fields) {

                fputcsv($fp, $fields);

            }

            fclose($fp);

            exit();
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

}


?>