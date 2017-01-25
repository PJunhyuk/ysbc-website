<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

// Require Video_Background class
require_once THEME_DIR . "/extensions/hero.class.php";

// Don't duplicate this class
if ( !class_exists( 'Hero_Style_2' ) ) :
    /**
     * Core functionality of second hero style.
     *
     * Custom form on the right and text on the left.
     */
    class Hero_Style_2 extends Hero {
        // Holds an instance of contact form
        protected $form_instance;
        // Holds a form title for our filter callback
        protected $form_title;

        /**
         * Override parent's method and remove centering of the content
         *
         * @param string $classes Additional classes to be used
         *
         * @return string Section attributes
         */
        public function get_section_attr( $classes = '' ) {
            // Set base section class
            $this->section_attr['class'][] = 'hero-section';
            $this->section_attr['class'][] = $classes;

            // Get params from settings
            $fullheight = ventcamp_option( 'hero_fullheight', true );
            $centering  = ventcamp_option( 'hero_verticalcentring', true );

            // Full height is set?
            if ( $fullheight ) {
                $this->section_attr['class'][] = "window-height";
            }

            // Vertical centering is set?
            if ( $centering ) {
                $this->section_attr['class'][] = "center-content";
            }

            // Set hero block background
            $this->set_background();

            return $this->generate_tag_attributes( $this->section_attr );
        }

        /**
         * Get form id, title and form background from settings, and echo the form
         */
        public function form () {
            // Check if Contact Form 7 plugin is enabled
            if ( class_exists( 'WPCF7_ContactForm' ) ) {
                // Get form ID
                $id = intval( ventcamp_option( 'hero_style_2_form_select', '' ) );

                // Check if form with specified ID exist
                if ( !( $this->form_instance = wpcf7_contact_form( $id ) ) ) {
                    // Get form by title
                    $this->form_instance = wpcf7_get_contact_form_by_title( "Event Registration" );

                    // Something goes wrong, no forms found, return false
                    if ( !$this->form_instance ) {
                        return;
                    }
                }

                // Check if title was created or not
                if ( !empty( $this->form_title = $this->form_title() ) ) {
                    // Do custom shortcuts in a contact form
                    add_filter( 'wpcf7_form_elements', array( $this, 'title_inject' ) );
                }

                // Echo the resulted HTML code
                echo $this->form_instance->form_html();
            }
        }

        /**
         * Get the title from settings and return it
         *
         * @return string Form title
         */
        protected function form_title () {
            // Is form title enabled in settings?
            $title_enabled = intval( ventcamp_option( 'hero_style_2_enable_form_header', 1 ) );

            // Only if title is enable
            if ( $title_enabled ) {
                // Use a custom title or get title from contact form 7 settings
                $use_contactform7_title = ventcamp_option( 'hero_style_2_use_contactform7_header', 1 );

                // Use contact form 7 title?
                if ( $use_contactform7_title ) {
                    $title = $this->form_instance->title();
                } else {
                    // Get custom form title from settings
                    $title = ventcamp_option( 'hero_style_2_form_header_text', __( 'Event Registration', 'ventcamp' ) );
                }

                // Return the title
                return '<h5 class="form-heading">' . $title . '</h5>';
            }

            return false;
        }

        /**
         * Injects our custom form title into Contact Form 7 form
         *
         * @param string $form Form code
         *
         * @return string Modified form with injected title
         */
        public function title_inject ( $form ) {
            $form = do_shortcode( $form );

            return '<div class="form-content">' . $this->form_title . $form . '</div>';
        }
    }
endif;