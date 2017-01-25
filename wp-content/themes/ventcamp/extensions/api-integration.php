<?php

defined('ABSPATH') or die('No direct access');

if( !function_exists('register_ventcamp_api_page') ) {
    /**
     * Add API config page
     */
    function register_ventcamp_api_page(){
        // Add sub-menu page in the appearance menu
        add_theme_page(
            __('API Integrations', 'ventcamp'), // Page title
            __( 'API Integrations', 'ventcamp' ), // Menu title
            'manage_options', // Capability to manage options
            'edit_theme_options', // Menu slug
            'ventcamp_api_integrations' // Function hook
        );
    }
}
add_action( 'admin_menu', 'register_ventcamp_api_page' );

if( !function_exists('ventcamp_api_integrations') ) {
    /**
     * Add API Integrations page
     */
    function ventcamp_api_integrations(){
        // If user is trying to save options
        if('POST' === $_SERVER['REQUEST_METHOD']){
            if ( !wp_verify_nonce($_POST['_wpnonce'])) {
                wp_die( 'Nonce is invalid', 'Error');
            } else {
                update_option( 'ventcamp_mailchimp_api_key', $_POST['ventcamp_mailchimp_api_key'] );
                update_option( 'ventcamp_gmaps_api_key', $_POST['ventcamp_gmaps_api_key'] );
                ?>
                <div class="updated">
                    <p>
                        <strong><?php _e('Options saved.', 'ventcamp' ); ?></strong>
                    </p>
                </div>
                <?php
            }
        }

        // Get mailchimp api key
        $ventcamp_mailchimp_api_key = get_option('ventcamp_mailchimp_api_key');
        // Get google maps api key
        $ventcamp_gmaps_api_key = get_option('ventcamp_gmaps_api_key');
        ?>
        <div class='wrap'>
            <h2><?php esc_html_e( 'Ventcamp API Integrations', 'ventcamp' ); ?></h2>

            <form method="post" action="<?php echo str_replace('%7E', '~', esc_url($_SERVER['REQUEST_URI']));?>">
                <?php wp_nonce_field(); ?>

                <div class="form-block">
                    <div class="label-group">
                        <label for="ventcamp_mailchimp_api_key"><?php _e( 'Mailchimp API key', 'ventcamp' ) ?></label>
                        <p class="description">
                            <?php printf( __( 'This API key grant full access to your MailChimp account, read more about API keys in <a href="%s">MailChimp official documentation</a>.', 'ventcamp' ), 'http://kb.mailchimp.com/integrations/api-integrations/about-api-keys' ) ?>
                        </p>
                    </div>
                    <input type="text" name='ventcamp_mailchimp_api_key' id="ventcamp_mailchimp_api_key" value='<?php echo $ventcamp_mailchimp_api_key;?>' size='45'>
                </div>

                <div class="form-block">
                    <div class="label-group">
                        <label for="ventcamp_gmaps_api_key"><?php _e( 'Google Maps API key', 'ventcamp' ) ?></label>
                        <p class="description">
                            <?php printf( __( 'Google don\'t support keyless access, read more about how to obtain this key in <a href="%s">our documentation</a>.', 'ventcamp' ), 'http://ventcampwp.com/docs/#document-5' ) ?>
                        </p>
                    </div>
                    <input type="text" name='ventcamp_gmaps_api_key' id="ventcamp_gmaps_api_key" value='<?php echo $ventcamp_gmaps_api_key;?>' size='45'>
                </div>

                <input type="submit" class='button' value="<?php _e( 'Save options', 'ventcamp' ); ?>">
            </form>

        </div>
        <?php
    }
}
