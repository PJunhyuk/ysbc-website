<?php

    /*-----------------------------------------------------------------------------------*/
    /*  Contact Form 7 Wrapper VC Mapping (Backend)
    /*-----------------------------------------------------------------------------------*/
    //if ( shortcode_exists( 'contact-form-7' ) ) {
    // add new own contact form 7
    // Update Contact forms, for number forms > 5
    $cf7 = get_posts( 'post_type="wpcf7_contact_form"&posts_per_page=-1&orderby=title&order=ASC' );
    $contact_forms = array();
    if ( $cf7 ) {
        foreach ( $cf7 as $cform ) {
            $contact_forms[ $cform->post_title ] = $cform->ID;
        }
    } else {
        $contact_forms[__('No contact forms found', 'ventcamp')] = 0;
    }

    $params = array(
        /*
        array(
            'type' => 'textfield',
            'heading' => __( 'Form title', 'ventcamp' ),
            'param_name' => 'title',
            'admin_label' => true,
            'description' => __( 'What text use as form title. Leave blank if no title is needed.', 'ventcamp' )
        ),*/

        array(
            'type' => 'dropdown',
            'heading' => __( 'Select contact form', 'ventcamp' ),
            'param_name' => 'id',
            'value' => $contact_forms,
            'description' => __( 'Choose previously created contact form from the drop down list.', 'ventcamp' )
        ),

        array(
            "type" => "checkbox",
            "heading" => __("Show/Hide", "ventcamp"),
            "param_name" => "_wpcf7_vsc_hide_after_send",
            "value" => array(
                __("Hide form on successful submit", "ventcamp") => "yes"
            )
        ),

        array(
            "type" => "checkbox",
            "heading" => __("Redirect/Idle", "ventcamp"),
            "param_name" => "_wpcf7_vsc_redirect_after_send",
            "value" => array(
                __("Redirect to another page on successful submit", "ventcamp") => "yes"
            )
        ),

        array(
            "type" => "textfield",
            "heading" => __("Redirect url", "ventcamp"),
            "param_name" => "_wpcf7_vsc_redirect_url",
            "admin_label" => true,
            "dependency" => array(
                "element" => "_wpcf7_vsc_redirect_after_send",
                "value" => "yes"
            ),
            "description" => __("Please enter full page url with http://", "ventcamp"),
        ),
    );

    // Check if mailchimp functionality is enabled in settings or not
    $api_key = get_option('ventcamp_mailchimp_api_key', '');

    if( !$api_key ) {
        // Show an alert that user must enable Mailchimp functionality in settings
        $params[] = array(
            'param_name' => 'custom_warning1', // all params must have a unique name
            'type' => 'custom_markup', // this param type
            'heading' => __( 'MailChimp Settings', 'ventcamp' ),
            "dependency" => array(
                "element" => "vsc_use_mailchimp",
                "value" => "yes"
            ),
            'value' => __( '<div class="alert alert-info">Please set "Mailchimp Api key" in Ventcamp options to use MailChimp shortcode functionality <a href="http://kb.mailchimp.com/accounts/management/about-api-keys" target="_blank">Where can i find my API key?</a></div>', 'ventcamp' ), // your custom markup
        );
    } else {
        $params[] = array(
            "type" => "checkbox",
            "heading" => __("Mailchimp API", "ventcamp"),
            "param_name" => "_wpcf7_vsc_use_mailchimp",
            "value" => array(
                __("Enable Mailchimp for this form", "ventcamp") => "yes"
            )
        );

        $params[] = array(
            "type" => "textfield",
            "heading" => __("MailChimp List ID", "ventcamp"),
            "param_name" => "_wpcf7_vsc_mailchimp_list_id",
            "admin_label" => true,
            "dependency" => array(
                "element" => "_wpcf7_vsc_use_mailchimp",
                "value" => "yes"
            ),
            "description" => __("Enter MailChimp List ID here. <a href=\"http://kb.mailchimp.com/lists/managing-subscribers/find-your-list-id\" target=\"_blank\">Where can i find my List ID?</a>", "ventcamp"),
        );

        $params[] = array(
            "type" => "checkbox",
            "heading" => __("Double opt-in", "ventcamp"),
            "param_name" => "_wpcf7_vsc_double_opt",
            "dependency" => array(
                "element" => "_wpcf7_vsc_use_mailchimp",
                "value" => "yes"
            ),
            "value" => array(
                __("Enable Mailchimp Double Opt-in", "ventcamp") => "yes"
            ),
            "description" => __("What is <a href=\"http://kb.mailchimp.com/lists/signup-forms/the-double-opt-in-process\" target=\"_blank\">Double Opt-in</a> used for?", "ventcamp"),
        );
    }

    vc_map( array(
        'base' => 'contact-form-7-wrapper',
        'name' => __( 'Form Manager', 'ventcamp' ),
        'icon' => 'icon-wpb-contactform7',
        'category' => __( 'Ventcamp', 'ventcamp' ),
        'description' => __( 'Contact 7 form controls', 'ventcamp' ),
        'params' => $params
    ) );

if ( !function_exists('contact_form_7_wrapper') ) {
    /**
     * Contact Form 7 Wrapper VC Render (Front-end)
     *
     * @param $atts
     * @param null $content
     *
     * @return mixed
     */
    function contact_form_7_wrapper($atts, $content = null) {
        extract(shortcode_atts(array(
            'title' => '',
            'id' => '',
            '_wpcf7_vsc_use_mailchimp' => 'false',
            '_wpcf7_vsc_mailchimp_list_id' => '',
            '_wpcf7_vsc_double_opt' => 'false',
            '_wpcf7_vsc_redirect_after_send' => 'false',
            '_wpcf7_vsc_redirect_url' => '',
            '_wpcf7_vsc_hide_after_send' => 'false'
        ), $atts));

        $_wpcf7_vsc_use_mailchimp = $_wpcf7_vsc_use_mailchimp === 'yes';
        $_wpcf7_vsc_double_opt = $_wpcf7_vsc_double_opt === 'yes';
        $_wpcf7_vsc_redirect_after_send = $_wpcf7_vsc_redirect_after_send === 'yes';
        $_wpcf7_vsc_hide_after_send = $_wpcf7_vsc_hide_after_send === 'yes';

        $token = wp_generate_password(5, false, false);

        wp_enqueue_script( 'ventcamp-cf7-custom' , get_template_directory_uri().'/js/lib/jquery.countTo.js', array('jquery'), false, true);
        wp_localize_script( 'ventcamp-cf7-custom', 'vsc_custom_contact_form_7_' . $token,
            array(
                '_wpcf7_vsc_use_mailchimp' => $_wpcf7_vsc_use_mailchimp,
                '_wpcf7_vsc_mailchimp_list_id' => $_wpcf7_vsc_mailchimp_list_id,
                '_wpcf7_vsc_double_opt' => $_wpcf7_vsc_double_opt,
                '_wpcf7_vsc_redirect_after_send' => $_wpcf7_vsc_redirect_after_send,
                '_wpcf7_vsc_redirect_url' => empty($_wpcf7_vsc_redirect_url) ? $_wpcf7_vsc_redirect_url : base64_encode($_wpcf7_vsc_redirect_url),
                '_wpcf7_vsc_hide_after_send' => $_wpcf7_vsc_hide_after_send
            ) );

        $output = "<div class=\"contact-form-7-data\" data-token=\"{$token}\">[contact-form-7 id=\"{$id}\" title=\"{$title}\"]</div>";

        return do_shortcode($output); // redirect to default contact-form-7 shortcode
    }

    add_shortcode("contact-form-7-wrapper", "contact_form_7_wrapper");
}
