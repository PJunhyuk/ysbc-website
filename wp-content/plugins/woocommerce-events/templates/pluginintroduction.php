<?php 
$Config = new WooCommerce_Events_Config();

?>
<div class='woocommerce-events-help'>
    <h1>WooCommerce Events Introduction</h1>

    <p>WooCommerce Events adds seamless event and ticketing functionality to WooCommerce. The plugin adds additional event specific fields and options to the existing WooCommerce products which allows you to create branded event tickets that can be sold on your site. The plugin provides a list of all tickets purchased for your events, a CSV export option, and event check-ins. You can also create different types of tickets using attributes and variations. </p>

    <h3>Installing WordPress</h3>
    <p>If you do not already have a working WordPress installation you will first need to install WordPress on your web server: <a href="https://codex.wordpress.org/Installing_WordPress">https://codex.wordpress.org/Installing_WordPress</a></p>

    <h3>Installing WooCommerce</h3>
    <p>WooCommerce is a WordPress plugin. Our Plugin, WooCommerce Events, is an extension that adds additional event and ticketing functionality to WooCommerce. Before installing WooCommerce Events you MUST first install WooCommerce: <a href="http://www.woothemes.com/woocommerce/"> If you are new to WooCommerce it is recommended that you go through the WooCommerce documentation: <a href="http://docs.woothemes.com/documentation/plugins/woocommerce/getting-started/">http://docs.woothemes.com/documentation/plugins/woocommerce/getting-started/</a>

    <h3>Installing WooCommerce Events</h3>
    <img src="<?php echo $Config->pluginURL ?>images/installation.png" alt="Installing WooCommerce" />
    <ol>
            <li>Ensure that WordPress and WooCommerce are installed (See above requirements)</li>
            <li>Download the WooCommerce Events plugin</li>
            <li>Upload the WooCommerce Events plugin to the following directory on your web server: /wp-content/plugins</li>
            <li>Login to your WordPress Admin Area</li>
            <li>Click on ‘Plugins’ in the main menu</li>
            <li>Find the WooCommerce Events plugin and activate it</li>
            <li>Congratulations! The plugin is now installed and ready to be configured for your event</li>
    </ol>  

    <h3>Setup and event type product</h3>
    <img src="<?php echo $Config->pluginURL ?>images/events-tab.png" alt="Setup and event type product" />

            <ol>
                    <li>Go to Products &gt; Add Product in the main menu</li>
                    <li>Complete the title, body, description, tags, categories, featured image and gallery as needed</li>
                    <li>Go to the Product Data tab set and select ‘Events’</li>
                    <li>To activate event functionality set the ‘Is this product an event?’ dropdown to ‘yes’. Doing so will reveal additional fields used to create your event</li>
                    <li>Complete the following fields:
                            <ol>
                                    <li>Date - The date that the event is scheduled to take place</li>
                                    <li>Start time - The time that the event is scheduled to start</li>
                                    <li>End time - The time that the event is scheduled to end</li>
                                    <li>Venue - The venue where the event will be held</li>
                                    <li>GPS Coordinates - The venue’s GPS coordinates</li>
                                    <li>Google Map Coordinates - The GPS coordinates used to determine the pin position on the Google map that is displayed on the event page. NB: Please ensure you use the following format:
                                    <ol>
                                            <li>Example: -26.137600, 28.008141</li>
                                            <li>If neccesary you can convert to this format using the following tool: <a href="http://www.gps-coordinates.net/gps-coordinates-converter">http://www.gps-coordinates.net/gps-coordinates-converter</a></li>
                                    </ol>
                                    </li>
                                            <li>Directions - Text directions explaining how to find the venue</li>
                                            <li>Phone - Event organizer’s landline or mobile phone number</li>
                                            <li>Email - Event organizer’s email address</li>
                                            <li>Ticket logo - The logo which will be displayed on the ticket in JPG or PNG format
                                    <ol>
                                            <li>Minimum width - 200px</li>
                                            <li>Minimum height - N/A</li>
                                    </ol>
                                    </li>
                                    <li>Ticket border color - The color of the ticket border</li>
                                    <li>Ticket buttons colour - The color of the ticket button</li>
                                    <li>Ticket button text colour - The color of the ticket button’s text</li>
                            </ol>
                    </li>
                    <li>NB: Once you have completed these fields please make sure that you save your post before proceeding!</li>
                    <li>You can set up various ticket types using WooCommerce attributes and variations. To do this please follow these instructions:
                    <ol>
                            <li>Go to the ‘Attributes’ tab in the Product Data panel</li>
                            <li>Create a new attribute called ‘Ticket Type’. It’s very important that the attribute is called this as this is the name that is used to reflect the ticket type on the actual ticket</li>
                            <li>Add the name of each ticket type under values and separate them with the pipe symbol ‘|’ e.g. VIP | General | Early Bird</li>
                            <li>Make sure that you select ‘Visible on the product page’ and ‘Used for variations’</li>
                            <li>Save the attributes</li>
                            <li>Click on the ‘Attributes’ tab in the Product Data panel</li>
                            <li>Add a variation for each ticket type and specify the relevant ticket criteria (price, in stock etc.)</li>
                            <li>Make sure that you select ‘Enabled’</li>
                            <li>We recommend that you select ‘Virtual’ if you don’t want the shipping information displayed on the checkout screen</li>
                            <li>Save/update the post once all variations have been added</li>
                            <li>The ticket type variations will now display as ticket options when purchasing a ticket</li>
                    </ol>
                    </li>
                    <li>Once your product is published it will appear in your WooCommerce store and users will be able to purchase tickets for your event</li>
            </ol>                         

    <h3>Managing Tickets</h3>
    <img src="<?php echo $Config->pluginURL ?>images/tickets.png" alt="Adding ticket types" /><br />
    Every ticket that is attached to a completed WooCommerce order will appear in the ‘Tickets’ admin menu
    <ol>
            <li>Go to ‘Tickets’ in the menu</li>
            <li>To resend a ticket:
                    <ul>
                            <li>Open the ticket and specify the email address that the ticket should be resent to in the resend option box</li>
                            <li>Click the resend button</li>
                    </ul>
    </li>
            <li>Tickets can have the following statuses:
                    <ul>
                            <li>“Not checked in”</li>
                            <li>“Checked in”</li>
                            <li>“Canceled”</li>
                    </ul>
    </li>
            <li>You can check someone in by changing their status in the Ticket Status box to “Checked in”</li>
            <li>The “Canceled” status can also be used to mark tickets that are no longer valid</li>
    </ol>

    <h3>Barcodes</h3>

    <p>Each ticket that is generated contains a unique barcode that is rendered using the barcode code 128 standard. If you own a scanner that can read ordinary barcodes then you have the option of scanning tickets to find them quickly. The separate WooCommerce Events mobile app (coming soon on iOS) can also be used to check-in people at events.</p>

    <h3>Global Settings</h3>
    <ol>
            <li>Go to WooCommerce -&gt; Settings</li>
            <li>Click on the “Events” tab</li>
            <li>Change the default event settings as required</li>
            <li>Save changes</li>
    </ol>

    <h3>Modifying theme templates</h3>

    <ol>
            <li>In your WordPress theme create the following directory structure: woocommerce_events/templates</li>
            <li>Copy the template files that you would like to modify from the wp-content/plugins/woocommerce_events/templates directory to the directory that you created in Step 1</li>
            <li>Modify the template files as required</li>
    </ol>

    <h3>Modifying the ticket template</h3>

    <ol>
            <li>In your WordPress theme create the following directory structure: woocommerce_events/templates/email</li>
            <li>Copy the template files in the wp-content/plugins/woocommerce_events/templates/email directory to the directory that you created in Step 1</li>
            <li>Modify the template files as required</li>
    </ol>
    
</div>