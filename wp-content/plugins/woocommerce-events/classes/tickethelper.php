<?php if ( ! defined( 'ABSPATH' ) ) exit; 

class Ticket_Helper {
    
    public $Config;
    private $BarcodeHelper;
    public $MailHelper;
    
    public function __construct($config) {
        
        $this->Config = $config;
        $this->register_ticket_post_type();
        
        //BarcodeHelper
        require_once($this->Config->classPath.'barcodehelper.php');
        $this->BarcodeHelper = new Barcode_Helper($this->Config);
        
        //MailHelper
        require_once($this->Config->classPath.'mailhelper.php');
        $this->MailHelper = new Mail_Helper($this->Config);
        
        add_action('admin_menu', array(&$this, 'hide_ticket_add_new'), 1, 2);
        add_action('manage_edit-event_magic_tickets_columns', array(&$this, 'add_admin_columns'), 10, 1);
        add_action('manage_event_magic_tickets_posts_custom_column', array(&$this, 'add_admin_column_content'), 10, 1);
        add_action('add_meta_boxes', array(&$this, 'add_tickets_meta_boxes'), 1, 2);
        add_action('save_post', array(&$this, 'save_ticket_meta_boxes'), 1, 2);
        add_action( 'template_redirect', array( $this, 'redirect_ticket' ) );
        add_action( 'post_row_actions', array( $this, 'remove_ticket_view' ) );
        add_action( 'parse_query', array( $this, 'filter_unpaid_tickets' ) );
        
        add_filter('pre_get_posts', array(&$this, 'tickets_where'), 10, 1);
        
    }
    
    /**
     * Registers the ticket post type.
     * 
     */
    private function register_ticket_post_type() {
        

        $labels = array(
		'name'               => __( 'Ticket', 'woocommerce-events' ),
		'singular_name'      => __( 'Ticket', 'woocommerce-events' ),
		'add_new'            => __( 'Add New', 'woocommerce-events' ),
		'add_new_item'       => __( 'Add New Ticket', 'woocommerce-events' ),
		'edit_item'          => __( 'Edit Ticket', 'woocommerce-events' ),
		'new_item'           => __( 'New Ticket', 'woocommerce-events' ),
		'all_items'          => __( 'All Tickets', 'woocommerce-events' ),
		'view_item'          => __( 'View Ticket', 'woocommerce-events' ),
		'search_items'       => __( 'Search Tickets', 'woocommerce-events' ),
		'not_found'          => __( 'No tickets found', 'woocommerce-events' ),
		'not_found_in_trash' => __( 'No tickets found in the Trash', 'woocommerce-events' ), 
		'parent_item_colon'  => '',
		'menu_name'          => __( 'Tickets', 'woocommerce-events' ));
        
        $args = array(
		'labels'        => $labels,
		'description'   => __( 'Event Tickets', 'woocommerce-events' ),
		'public'        => true,
		'exclude_from_search' => true,
		'menu_position' => 5,
		'supports'      => array('custom-fields'),
		'has_archive'   => true,
                'capabilities'  => array( 'create_posts' => true ),       
                'map_meta_cap'  => true,
                'menu_icon'     => 'dashicons-tickets-alt'
	);
        
        register_post_type( 'event_magic_tickets', $args );	
        
    }

    /**
     * Adds admin columns to the event ticket custom post type.
     * 
     * @param array $columns
     * @return array $columns
     */
    public function add_admin_columns($columns) {
        
        $columns = array(
            'cb'                => __('Select', 'woocommerce-events'),
            'title'             => __('Title', 'woocommerce-events'),
            'Event'             => __('Event', 'woocommerce-events'),
            'Purchaser'         => __('Purchaser', 'woocommerce-events'),
            'Attendee'          => __('Attendee', 'woocommerce-events'),
            'PurchaseDate'      => __('Purchase Date', 'woocommerce-events'),
            'Status'            => __('Status', 'woocommerce-events')
        );
        
        return $columns;
    }
    
    /**
     * Adds column content to the event ticket custom post type.
     * 
     * @param string $column
     * @param int $post_id
     * @global object $post
     * 
     */
    public function add_admin_column_content($column) {
        
        global $post;
        global $woocommerce;
        
        $order_id = get_post_meta($post->ID, 'WooCommerceEventsOrderID', true);
        $customer_id = get_post_meta($post->ID, 'WooCommerceEventsCustomerID', true);
        $order = new WC_Order( $order_id );

        switch( $column ) {
            case 'Event' :
                
                $WooCommerceEventsProductID = get_post_meta($post->ID, 'WooCommerceEventsProductID', true);
                
                echo '<a href="'.get_site_url().'/wp-admin/post.php?post='.$WooCommerceEventsProductID.'&action=edit">'.get_the_title($WooCommerceEventsProductID).'</a>';
                
                break;
            case 'Purchaser' :
                
                if(!empty($customer_id)) {
                    
                    $purchaser = get_user_meta($customer_id);
                    echo '<a href="'.get_site_url().'/wp-admin/user-edit.php?user_id='.$customer_id.'">'.$order->billing_first_name.' '.$order->billing_last_name.' - ( '.$order->billing_email.' )</a>';
                    
                } else {
                    
                    //guest account
                    echo $order->billing_first_name.' '.$order->billing_last_name.' - ( '.$order->billing_email.' )';
                
                }
                
                break;
                
            case 'Attendee' : 
                
                $WooCommerceEventsAttendeeName = get_post_meta($post->ID, 'WooCommerceEventsAttendeeName', true);
                $WooCommerceEventsAttendeeEmail = get_post_meta($post->ID, 'WooCommerceEventsAttendeeEmail', true);
                echo $WooCommerceEventsAttendeeName.' - '.$WooCommerceEventsAttendeeEmail;
                
                break;
            
            case 'PurchaseDate' :
                
                echo $post->post_date;
                
                break;
            case 'Status' :
                
                $WooCommerceEventsStatus = get_post_meta($post->ID, 'WooCommerceEventsStatus', true);
                echo $WooCommerceEventsStatus;
                
                break;
            case 'Options' :
                
                
                break;
        }
        
    }
    
    /**
     * Adds meta boxes to the tickets custom post type page.
     * 
     * 
     */
    public function add_tickets_meta_boxes() {
        
        $screens = array('event_magic_tickets');
        
        foreach ( $screens as $screen ) {
            
            if(isset($_GET['post'])) {
            
                add_meta_box(
                            'woocommerce_events_ticket_details',
                            __( 'Ticket Details', 'woocommerce-events' ),
                             array(&$this, 'add_tickets_meta_ticket_details'),
                            $screen, 'normal', 'high'
                    );

                add_meta_box(
                            'woocommerce_events_ticket_status',
                            __( 'Ticket Status', 'woocommerce-events' ),
                             array(&$this, 'add_tickets_meta_ticket_status'),
                            $screen, 'side', 'default'
                    );

                add_meta_box(
                            'woocommerce_events_ticket_resend_ticket',
                            __( 'Resend Ticket', 'woocommerce-events' ),
                             array(&$this, 'add_tickets_meta_ticket_resend_tickets'),
                            $screen, 'side', 'low'
                    );
            
            }
            
            if(!isset($_GET['post'])) {
                
                add_meta_box(
                            'woocommerce_events_ticket_add_event',
                            __( 'Event', 'woocommerce-events' ),
                             array(&$this, 'woocommerce_events_ticket_add_event'),
                            $screen, 'normal', 'high'
                );
                
                
            }
            
        }
        
    }
    
    public function woocommerce_events_ticket_add_event() {
        
        $events = new WP_Query( array('post_type' => array('product'), 'posts_per_page' => -1, 'meta_query' => array( array( 'key' => 'WooCommerceEventsEvent', 'value' => 'Event' ) )) );
        $events = $events->get_posts();
        
        
        require($this->Config->templatePath.'addeventmeta.php');
    }
    
    
    /**
     * Add ticket details meta box
     * 
     * @global object $post
     */
    public function add_tickets_meta_ticket_details() {
        
        global $post;
        global $woocommerce;
        
        $order_id = get_post_meta($post->ID, 'WooCommerceEventsOrderID', true);
        $customer_id = get_post_meta($post->ID, 'WooCommerceEventsCustomerID', true);
        $order = new WC_Order( $order_id );
        
        $purchaserDetails = array();
        $purchaser['customerFirstName']     = $order->billing_first_name;
        $purchaser['customerLastName']      = $order->billing_last_name;
        $purchaser['customerEmail']         = $order->billing_email;
        $purchaser['customerPhone']         = $order->billing_phone;
        
        
        if(!empty($customer_id)) {

            $purchaser['customerID'] = $customer_id;
        
        } else {
            
            $purchaser['customerID']            = 0;
            
        }
        
        $WooCommerceEventsProductID             = get_post_meta($post->ID, 'WooCommerceEventsProductID', true);
        $WooCommerceEventsTicketID              = get_post_meta($post->ID, 'WooCommerceEventsTicketID', true);
        $WooCommerceEventsTicketType            = get_post_meta($post->ID, 'WooCommerceEventsTicketType', true);
        $WooCommerceEventsAttendeeName          = get_post_meta($post->ID, 'WooCommerceEventsAttendeeName', true);
        $WooCommerceEventsAttendeeEmail         = get_post_meta($post->ID, 'WooCommerceEventsAttendeeEmail', true);
        $WooCommerceEventsVariations            = json_decode(get_post_meta($post->ID, 'WooCommerceEventsVariations', true));
        
        $WooCommerceEventsEvent                 = get_post_meta($WooCommerceEventsProductID, 'WooCommerceEventsEvent', true);
        $WooCommerceEventsDate                  = get_post_meta($WooCommerceEventsProductID, 'WooCommerceEventsDate', true);
        $WooCommerceEventsHour                  = get_post_meta($WooCommerceEventsProductID, 'WooCommerceEventsHour', true);
        $WooCommerceEventsMinutes               = get_post_meta($WooCommerceEventsProductID, 'WooCommerceEventsMinutes', true);
        $WooCommerceEventsHourEnd               = get_post_meta($WooCommerceEventsProductID, 'WooCommerceEventsHourEnd', true);
        $WooCommerceEventsMinutesEnd            = get_post_meta($WooCommerceEventsProductID, 'WooCommerceEventsMinutesEnd', true);
        $WooCommerceEventsLocation              = get_post_meta($WooCommerceEventsProductID, 'WooCommerceEventsLocation', true);
        $WooCommerceEventsTicketLogo            = get_post_meta($WooCommerceEventsProductID, 'WooCommerceEventsTicketLogo', true);
        $WooCommerceEventsSupportContact        = get_post_meta($WooCommerceEventsProductID, 'WooCommerceEventsSupportContact', true);
        $WooCommerceEventsGPS                   = get_post_meta($WooCommerceEventsProductID, 'WooCommerceEventsGPS', true);
        $WooCommerceEventsDirections            = get_post_meta($WooCommerceEventsProductID, 'WooCommerceEventsDirections', true);
        $WooCommerceEventsEmail                 = get_post_meta($WooCommerceEventsProductID, 'WooCommerceEventsEmail', true);
        $WooCommerceEventsTicketText            = get_post_meta($WooCommerceEventsProductID, 'WooCommerceEventsTicketText', true);
        $WooCommerceEventsTicketDisplayPrice    = get_post_meta($WooCommerceEventsProductID, 'WooCommerceEventsTicketDisplayPrice', true);
        
        
        $WooCommerceEventsTitle             = get_the_title($WooCommerceEventsProductID);
        
        $WooCommerceEventsStatus = get_post_meta($post->ID, 'WooCommerceEventsStatus', true);
        
        $barcodeURL =  $this->Config->pluginURL.'/barcodes/';
        
        if (!file_exists($this->Config->barcodePath.$WooCommerceEventsTicketID.'.png')) {
            
            $this->BarcodeHelper->generate_barcode($WooCommerceEventsTicketID);
            
        }
        
        $this->BarcodeHelper->generate_barcode($WooCommerceEventsTicketID);
        
        require($this->Config->templatePath.'ticketdetailmeta.php');
        
    }
    
    /**
     * Add ticket status meta box.
     * 
     * @global object $post
     */
    public function add_tickets_meta_ticket_status() {
        
        global $post;
        
        $WooCommerceEventsStatus = get_post_meta($post->ID, 'WooCommerceEventsStatus', true);
        
        require($this->Config->templatePath.'ticketstatusmeta.php');
        
    }
    
    /**
     * Add resend ticket box
     * 
     * @global object $post
     */
    public function add_tickets_meta_ticket_resend_tickets() {
        
        global $post;
        global $woocommerce;
        
        $order_id = get_post_meta($post->ID, 'WooCommerceEventsOrderID', true);
        $customer_id = get_post_meta($post->ID, 'WooCommerceEventsCustomerID', true);
        $order = new WC_Order( $order_id );
        
        $purchaserDetails = array();
        $purchaser['customerEmail'] = $order->billing_email;
        
        require($this->Config->templatePath.'ticketresendticketmeta.php');
        
    }
    
    /**
     * Saves tickets meta box settings
     * 
     * @param int $post_ID
     * @global object $post
     */
    public function save_ticket_meta_boxes($post_ID) {
        
        global $post;
        global $woocommerce;
       
        if (is_object($post) && isset( $_POST )) {
       
            if( $post->post_type == "event_magic_tickets" ) {

                if (isset( $_POST ) ) {

                    update_post_meta( $post_ID, 'WooCommerceEventsStatus', strip_tags( $_POST['WooCommerceEventsStatus'] ) ); 

                }

                if (!empty($_POST['WooCommerceEventsResendTicket']) && !empty($_POST['WooCommerceEventsResendTicketEmail'])) {
                    
                    set_time_limit(0);
                    
                    $WooCommerceEventsProductID = get_post_meta($post->ID, 'WooCommerceEventsProductID', true);
                    $WooCommerceEventsEvent = get_post_meta($WooCommerceEventsProductID, 'WooCommerceEventsEvent', true);
                    
                        
                        $order_id = get_post_meta($post->ID, 'WooCommerceEventsOrderID', true);
                        $customer_id = get_post_meta($post->ID, 'WooCommerceEventsCustomerID', true);
                        $order = new WC_Order( $order_id );

                        $purchaserDetails = array(); 

                        $customerDetails['customerFirstName'] = $order->billing_first_name;
                        $customerDetails['customerLastName'] = $order->billing_last_name;
                        $customerDetails['customerEmail'] = $order->billing_email;
                        $customerDetails['customerPhone'] = $order->billing_phone;

                        $body = '';
                        $header = $this->MailHelper->parse_email_template('header.php', $customerDetails); 
                        $footer = $this->MailHelper->parse_email_template('footer.php', $customerDetails); 
                        $ticketBody = '';
                         
                        $WooCommerceEventsCaptureAttendeeDetails            = get_post_meta($WooCommerceEventsProductID, 'WooCommerceEventsCaptureAttendeeDetails', true);
                        $ticket['WooCommerceEventsVariations']              = json_decode(get_post_meta($post->ID, 'WooCommerceEventsVariations', true)); 
                        $ticket['WooCommerceEventsEvent']                   = get_post_meta($WooCommerceEventsProductID, 'WooCommerceEventsEvent', true);
                        $ticket['WooCommerceEventsDate']                    = get_post_meta($WooCommerceEventsProductID, 'WooCommerceEventsDate', true);
                        $ticket['WooCommerceEventsHour']                    = get_post_meta($WooCommerceEventsProductID, 'WooCommerceEventsHour', true);
                        $ticket['WooCommerceEventsMinutes']                 = get_post_meta($WooCommerceEventsProductID, 'WooCommerceEventsMinutes', true);
                        $ticket['WooCommerceEventsHourEnd']                 = get_post_meta($WooCommerceEventsProductID, 'WooCommerceEventsHourEnd', true);
                        $ticket['WooCommerceEventsMinutesEnd']              = get_post_meta($WooCommerceEventsProductID, 'WooCommerceEventsMinutesEnd', true);
                        $ticket['WooCommerceEventsLocation']                = get_post_meta($WooCommerceEventsProductID, 'WooCommerceEventsLocation', true);
                        $ticket['WooCommerceEventsTicketLogo']              = get_post_meta($WooCommerceEventsProductID, 'WooCommerceEventsTicketLogo', true);
                        $ticket['WooCommerceEventsSupportContact']          = get_post_meta($WooCommerceEventsProductID, 'WooCommerceEventsSupportContact', true);
                        $ticket['WooCommerceEventsTicketID']                = get_post_meta($post->ID, 'WooCommerceEventsTicketID', true);
                        $ticket['WooCommerceEventsTicketType']              = get_post_meta($post_ID, 'WooCommerceEventsTicketType', true);
                        $ticket['WooCommerceEventsTicketBackgroundColor']   = get_post_meta($WooCommerceEventsProductID, 'WooCommerceEventsTicketBackgroundColor', true);
                        $ticket['WooCommerceEventsTicketButtonColor']       = get_post_meta($WooCommerceEventsProductID, 'WooCommerceEventsTicketButtonColor', true);
                        $ticket['WooCommerceEventsTicketDisplayPrice']      = get_post_meta($WooCommerceEventsProductID, 'WooCommerceEventsTicketDisplayPrice', true);
                        
                        $ticket['WooCommerceEventsTicketPurchaserDetails']  = get_post_meta($WooCommerceEventsProductID, 'WooCommerceEventsTicketPurchaserDetails', true);
                        $ticket['WooCommerceEventsTicketAddCalendar']       = get_post_meta($WooCommerceEventsProductID, 'WooCommerceEventsTicketAddCalendar', true);
                        $ticket['WooCommerceEventsTicketDisplayDateTime']   = get_post_meta($WooCommerceEventsProductID, 'WooCommerceEventsTicketDisplayDateTime', true);
                        $ticket['WooCommerceEventsTicketDisplayBarcode']    = get_post_meta($WooCommerceEventsProductID, 'WooCommerceEventsTicketDisplayBarcode', true);
                        $ticket['WooCommerceEventsTicketText']              = get_post_meta($WooCommerceEventsProductID, 'WooCommerceEventsTicketText', true);
                        $ticket['WooCommerceEventsProductID']               = $WooCommerceEventsProductID;
                        
                        $timestamp              = time();
                        $key                    = md5($ticket['WooCommerceEventsTicketID'] + $timestamp + $this->Config->salt);                              
                        $ticket['cancelLink']   = get_site_url().'/wp-admin/admin-ajax.php?action=woocommerce_events_cancel&id='.$ticket['WooCommerceEventsTicketID'].'&t='.$timestamp.'&k='.$key;
                        
                        if($WooCommerceEventsCaptureAttendeeDetails === 'on') {

                            $ticket['customerFirstName']        = get_post_meta($post->ID, 'WooCommerceEventsAttendeeName', true);
                            $ticket['customerLastName']         = '';

                        } else {

                            $ticket['customerFirstName']        = $customerDetails['customerFirstName']; 
                            $ticket['customerLastName']         = $customerDetails['customerLastName'];

                        }
                         
                         $ticketDetails = get_post($WooCommerceEventsProductID);

     
                         if(empty($ticket['name'])) {

                             $ticket['name'] = $ticketDetails->post_title;

                         } 

                         $ticketBody .= $this->MailHelper->parse_ticket_template($ticket);

                         $body       = $header.$ticketBody.$footer;
                         $subject    = __( 'Ticket', 'woocommerce-events' );
                         $fromEmail  = get_bloginfo('admin_email');  
                         $fromName   = get_bloginfo('name'); 
                         $from       = get_option( 'woocommerce_email_from_name' ).' <'.sanitize_email( get_option( 'woocommerce_email_from_address' ) ).'>';
                         $to         = $_POST['WooCommerceEventsResendTicketEmail'];

                         $headers = 'From: '.$from;

                         $this->MailHelper->send_ticket($to, $subject, $body, $headers);
     
                }

            }
       
        }
        
    }
    
    /**
     * Hides the add new menu item.
     * 
     * @global $submenu
     */
    public function hide_ticket_add_new() {
        
        /*global $submenu;
        
        unset($submenu['edit.php?post_type=event_magic_tickets'][10]);*/
        
    }
    
    /**
     * Redirects tickets custom most type
     * 
     */
    public function redirect_ticket() {
        
        $queried_post_type = get_query_var('post_type');
        if ( is_single() && 'event_magic_tickets' ==  $queried_post_type ) {
          wp_redirect( home_url(), 301 );
          exit;
        }
        
    }
    
    /**
     * Removes view link
     * 
     */
    public function remove_ticket_view($action) {
        
        unset ($action['view']);
        return $action;
        
    }
    
    /**
     * Removes unpaid tickets from the ticket list
     * 
     */
    public function filter_unpaid_tickets($query) {
        
        if( is_admin() AND $query->query['post_type'] == 'event_magic_tickets' ) {    

            /*$query->query_vars['meta_key']      = 'WooCommerceEventsStatus';
            $query->query_vars['meta_value']    = 'Unpaid'; 
            $query->query_vars['meta_compare']  = '!=';*/

        }

        return $query;
        
    }
    
    /**
     * Searches for post meta
     * 
     * @param object $query
     */
    public function tickets_where($query) {
        
        if(isset($_GET['post_type']) && $_GET['post_type'] == 'event_magic_tickets') {

            $custom_fields = array(
                "WooCommerceEventsAttendeeName",
                "WooCommerceEventsAttendeeEmail",
                "WooCommerceEventsCustomerID",
                "WooCommerceEventsVariations",
                "WooCommerceEventsPurchaserFirstName",
                "WooCommerceEventsPurchaserLastName",
                "WooCommerceEventsPurchaserEmail",
                "WooCommerceEventsStatus",
                "WooCommerceEventsTicketID"
            );

            $searchterm = $query->query_vars['s'];

            $query->query_vars['s'] = "";

            if ($searchterm != "") {
                $meta_query = array('relation' => 'OR');
                foreach($custom_fields as $cf) {
                    array_push($meta_query, array(
                        'key' => $cf,
                        'value' => $searchterm,
                        'compare' => 'LIKE'
                    ));
                }
                $query->set("meta_query", $meta_query);
            };
        
        }
        
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