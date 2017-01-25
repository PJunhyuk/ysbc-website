<?php if ( ! defined( 'ABSPATH' ) ) exit; 

class Checkout_Helper {
  
    private $Config;


    public function __construct($Config) {
        
        $this->Config = $Config;
        
        add_action('woocommerce_after_order_notes', array( $this, 'attendee_checkout'));
        add_action('woocommerce_checkout_process', array( $this, 'attendee_checkout_process'));
        add_action('woocommerce_checkout_update_order_meta', array( $this, 'woocommerce_events_process'));
        
    }
    
    /**
     * Displays attendee checkout forms on the checkout page
     * 
     */
    public function attendee_checkout($checkout) {
        
        global $woocommerce;

        $events = $this->get_order_events($woocommerce);

        foreach($events as $event => $tickets) {
            
            $captureAttendees = $this->check_tickets_for_capture_attendees($tickets);
            
            if($captureAttendees) {
            
                echo '<h2>' . __($event) . '</h2>';

                $x = 1;
                foreach($tickets as $ticket) {

                    $ticketType = '';
                    if(!empty($ticket['attribute_ticket-type'])) {

                        $ticketType = ' - '.$ticket['attribute_ticket-type'];

                    }

                    $WooCommerceEventsCaptureAttendeeDetails    = get_post_meta($ticket['product_id'], 'WooCommerceEventsCaptureAttendeeDetails', true);

                    if($WooCommerceEventsCaptureAttendeeDetails === 'on') {

                        woocommerce_form_field( $ticket['product_id'].'_attendee_'.$x, array(
                        'type'          => 'text',
                        'class'         => array('attendee-class form-row-wide'),
                        'label'         => sprintf(__('Attendee %d name %s', 'woocommerce-events'), $x, $ticketType),
                        'placeholder'   => '',
                        'required'      => true,    
                        ), $checkout->get_value( $ticket['product_id'].'_attendee_'.$x ));

                        woocommerce_form_field( $ticket['product_id'].'_attendeeemail_'.$x, array(
                        'type'          => 'text',
                        'class'         => array('attendee-class form-row-wide'),
                        'label'         => sprintf(__('Attendee %d email', 'woocommerce-events'), $x),
                        'placeholder'   => '',
                        'required'      => true, 
                        ), $checkout->get_value( $ticket['product_id'].'_attendeeemail_'.$x ));

                        $x++;

                    }
                }
                
            }
        }

        
    }
    
    /**
     * Check if attendee details should be captured
     * 
     * @param array $tickets
     * 
     */
    public function check_tickets_for_capture_attendees($tickets) {
        
        foreach($tickets as $ticket) {
            
            $WooCommerceEventsCaptureAttendeeDetails    = get_post_meta($ticket['product_id'], 'WooCommerceEventsCaptureAttendeeDetails', true);
            
            if($WooCommerceEventsCaptureAttendeeDetails === 'on') {
                
                return true;
                
            }
            
        }
        
        return false;
        
    }
    
    /**
     * Processes the attendee details on Checkout
     * 
     * 
     */
    public function attendee_checkout_process() {
        
        global $woocommerce;
        
        $events = $this->get_order_events($woocommerce);

        foreach($events as $event => $tickets) {
            
            $x = 1;
            foreach($tickets as $ticket) {
                
                $WooCommerceEventsCaptureAttendeeDetails    = get_post_meta($ticket['product_id'], 'WooCommerceEventsCaptureAttendeeDetails', true);
                
                if($WooCommerceEventsCaptureAttendeeDetails === 'on') {
                
                    if ( ! $_POST[$ticket['product_id'].'_attendee_'.$x] ) {
                        
                        $notice = sprintf(__( 'Name is required for %s attendee %d', 'woocommerce-events' ), $event, $x );
                        wc_add_notice( $notice, 'error' );

                    }  

                    if ( ! $_POST[$ticket['product_id'].'_attendeeemail_'.$x] ) {
                        
                        $notice = sprintf(__( 'Email is required for %s attendee %d', 'woocommerce-events' ), $event, $x);
                        wc_add_notice( $notice, 'error' );

                    }
                    
                    if (!$this->is_email_valid($_POST[$ticket['product_id'].'_attendeeemail_'.$x])) {
                        
                        $notice = sprintf(__( 'Email is not valid for %s attendee %d', 'woocommerce-events' ), $event, $x);
                        wc_add_notice( $notice, 'error' );
                        
                    }
                    
                }
                
                $x++;
                
            }
            
        }

    }
    
    /**
     * Creates tickets and assigns attendees
     * 
     */
    public function woocommerce_events_process($order_id) {
        
        set_time_limit(0);
        
        global $woocommerce;
        
        $events = $this->get_order_events($woocommerce);
        
        /*echo "<pre>";
            print_r($events);
        echo "</pre>";
        echo "<pre>";
            print_r($_POST);
        echo "</pre>";
        
        exit();*/
        
        $totalTickets = array();
        foreach($events as $event => $tickets) {
            
            $x = 1;
            foreach($tickets as $ticket) {
                
                $WooCommerceEventsCaptureAttendeeDetails    = get_post_meta($ticket['product_id'], 'WooCommerceEventsCaptureAttendeeDetails', true);
                
                $customer = get_post_meta($order_id, '_customer_user', true);
                
                $customerDetails = array(
                            'customerID' => $customer
                        );
                
                if(empty($customerDetails['customerID'])) {

                    $customerDetails['customerID'] = 0;

                }
                
                if(empty($ticket['variations'])) {
                    
                    $ticket['variations'] = '';
                    
                }
                
                if($WooCommerceEventsCaptureAttendeeDetails === 'on') {
                    
                    $attendeeName   = $_POST[$ticket['product_id'].'_attendee_'.$x];
                    $attendeeEmail  = $_POST[$ticket['product_id'].'_attendeeemail_'.$x];
                    
                    //create ticket
                    $ticket['WooCommerceEventsTicketID'] = $this->create_ticket($customerDetails['customerID'], $ticket['product_id'], $order_id, $ticket['attribute_ticket-type'], $ticket['variations'], $attendeeName, $attendeeEmail);
                    
                } else {
                    
                    $ticket['WooCommerceEventsTicketID'] = $this->create_ticket($customerDetails['customerID'], $ticket['product_id'], $order_id, $ticket['attribute_ticket-type'], $ticket['variations']);
                    
                }
                
                $x++;
                //$totalTickets++;
                
                if(empty($ticket['product_id'])) {
                    
                    $totalTickets[$ticket['product_id']] = 1;
                    
                } else {
                    
                    if(isset($totalTickets[$ticket['product_id']])) {
                        
                        $totalTickets[$ticket['product_id']]++;
                        
                    } else {
                        
                        $totalTickets[$ticket['product_id']] = 1;
                        
                    }
                    
                }
                
            }
            
            
            
        }

        update_post_meta($order_id, 'WooCommerceEventsTicketsPurchased', json_encode($totalTickets));
        //exit();
        
    }
    
    /**
     * Checks a string for valid email address
     * 
     * @param string $email
     * @return bool
     */
    private function is_email_valid($email) {
        
        return filter_var($email, FILTER_VALIDATE_EMAIL) 
            && preg_match('/@.+\./', $email);
        
    }
    
    private function get_order_events($woocommerce) {
        
        $products = $woocommerce->cart->get_cart();

        $events = array();
        foreach($products as $cart_item_key => $product) {

            for($x = 0; $x < $product['quantity']; $x++) {
                
                $WooCommerceEventsEvent = get_post_meta($product['product_id'], 'WooCommerceEventsEvent', true);
                
                if($WooCommerceEventsEvent == 'Event') {
                    
                    $ticket = array();
                    $ticket['product_id']               = $product['product_id'];
                    $ticket['attribute_ticket-type']    = '';
                    $ticket['event_name']               = $product['data']->post->post_title;

                    if(!empty($product['variation']['attribute_ticket-type'])) {

                        $ticket['attribute_ticket-type'] = $product['variation']['attribute_ticket-type'];
                        unset($product['variation']['attribute_ticket-type']);

                    }



                    if(!empty($product['variation'])) {

                        $ticket['variations'] = $product['variation'];

                    }

                    $events[$product['data']->post->post_title][] = $ticket;
                
                }
                
            }
            
        }
        

        return $events;
        
    }
    
     /**
     * Creates a new ticket
     * 
     */
    private function create_ticket($customerID, $product_id, $order_id, $ticketType, $variations, $attendeeName = '', $attendeeEmail = '') {
        
        $order = new WC_Order( $order_id );
        
        $rand = rand(111111,999999);
        
        $post = array(
            
                'post_author' => $customerID,
                'post_content' => "Ticket",
                'post_status' => "publish",
                'post_title' => 'Assigned Ticket',
                'post_type' => "event_magic_tickets"
            
        );

        $post['ID'] = wp_insert_post( $post );
        $ticketID = $post['ID'].$rand;
        $post['post_title'] = '#'.$ticketID;
        $postID = wp_update_post( $post );

        $variations = json_encode($variations);
        
        update_post_meta($postID, 'WooCommerceEventsTicketID', $ticketID);
        update_post_meta($postID, 'WooCommerceEventsProductID', $product_id);
        update_post_meta($postID, 'WooCommerceEventsOrderID', $order_id);
        update_post_meta($postID, 'WooCommerceEventsTicketType', $ticketType);
        update_post_meta($postID, 'WooCommerceEventsStatus', 'Unpaid');
        update_post_meta($postID, 'WooCommerceEventsCustomerID', $customerID);
        update_post_meta($postID, 'WooCommerceEventsAttendeeName', $attendeeName);
        update_post_meta($postID, 'WooCommerceEventsAttendeeEmail', $attendeeEmail);
        update_post_meta($postID, 'WooCommerceEventsVariations', $variations);
        update_post_meta($postID, 'WooCommerceEventsPurchaserFirstName', $order->billing_first_name);
        update_post_meta($postID, 'WooCommerceEventsPurchaserLastName', $order->billing_last_name);
        update_post_meta($postID, 'WooCommerceEventsPurchaserEmail', $order->billing_email);
        
        return $ticketID;
        
    }
    
}


?>