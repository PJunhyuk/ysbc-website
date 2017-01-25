<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

// Require core Tag_Generator class
require_once THEME_DIR . "/extensions/core/tag_generator.class.php";

// Require Video_Background class
require_once "video-background.class.php";

// Don't duplicate this class
if ( !class_exists( 'Hero' ) ) :
    /**
     * Base hero class, all styles inherit this class
     */
    class Hero extends Tag_Generator {
        // Type of the background: color, image or video
        public $bg_type;
        // Stores section data attributes
        protected $section_attr = array();

        /**
         * Return section attributes
         *
         * @param string $classes Additional classes to be used
         *
         * @return string Section attributes
         */
        public function get_section_attr( $classes = '' ) {
            // Set base section class
            $this->section_attr['class'][] = 'hero-section';
            // Center the hero
            $this->section_attr['class'][] = 'align-center';
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
         * Depending on background type, set background color, background image or background video
         */
        public function set_background () {
            // Get background type from settings (solid color by default)
            $this->bg_type = ventcamp_option( 'hero_background_type', 'color' );

            // Background type: solid color
            if( $this->bg_type == 'color'){
                $this->section_attr['style']['background-color'] = ventcamp_option( 'hero_background_color', '#252323' );
            // Background type: image
            } else if( $this->bg_type == 'image' ) {
                // Set cover class for background
                $this->section_attr['class'][] = 'bg-cover';
                $this->section_attr['style']['background-image'] = "url(" . ventcamp_option( 'hero_background_image' ) . ")";
            // Background type: video
            } else if( $this->bg_type == 'video' ) {
                $this->set_background_video();
            }
        }

        /**
         * Set background video
         */
        protected function set_background_video () {
            $video = new Video_Background();
            // Merge both arrays: array with section attributes and with video data tags
            $this->section_attr = array_merge( $video->bg_attributes, $this->section_attr );
            $this->section_attr['class'][] = "ytpb-row";
        }

        /**
         * Check if overlay is enabled in settings and display on the page.
         * Overlay could be with solid color and with gradient background
         */
        public function overlay () {
            $type = ventcamp_option( 'hero_overlay_type' );

            // Declare array with overlay style properties
            $overlay['class'] = 'hero-overlay';

            // Check overlay type
            if( $type == 'color' ){
                // If it's a color
                $overlay['style']['background-color'] = ventcamp_option( 'hero_overlay_color' );
            } elseif ( $type == 'gradient' ) {
                // If it's a gradient
                $overlay['style'][] = ventcamp_option( 'hero_overlay_gradient' );
            } ?>

            <div <?php echo $this->generate_tag_attributes( $overlay ); ?>></div>

            <?php
        }

        /**
         * Show heading top with date/location of the conference
         */
        public function heading_top () {
            $date     = ventcamp_option( 'hero_heading_upper_text_date', '28.NOV' );
            $location = ventcamp_option( 'hero_heading_upper_text_location', 'NEW YORK, NY' );
            ?>
            <span class="hero-heading-top">
                <?php if ( !empty( $date ) ) : ?>
                    <span class="fa fa-calendar-o base-clr-txt"></span>
                    <?php esc_attr_e( $date ); ?>
                <?php endif; ?>

                <?php if ( !empty( $location ) ) : ?>
                    <span class="fa fa-map-marker base-clr-txt" style="margin-left: 14px;"></span>
                    <?php esc_attr_e( $location ); ?>
                <?php endif; ?>
            </span>
            <?php
        }

        /**
         * Check hero top line type (social links or text) and call the appropriate function
         */
        public function top_line () {
            // Get top line type
            $type = ventcamp_option( 'hero_top_line_type', 'social' );

            if ( $type == 'social' ) {
                // Display social links
                $this->social_links();
            } elseif ( $type == 'text' ) {
                // Display text
                $this->top_line_text();
            }
        }

        /**
         * Show top line text
         */
        public function top_line_text () {
            $text = ventcamp_option( 'hero_top_line_text' ); ?>
            <div class='top-line'>
                <?php esc_html_e( $text ); ?>
            </div>
            <?php
        }

        /**
         * Add social links.
         */
        protected function social_links () {
            // Default links, used as fallback
            $default =  array(
                array( 'type' => 'facebook',  'url' => 'http://facebook.com' ),
                array( 'type' => 'twitter',   'url' => 'http://twitter.com' ),
                array( 'type' => 'google',    'url' => 'http://google.com' ),
                array( 'type' => 'instagram', 'url' => 'http://instagram.com' )
            );

            // Get the list of social links
            $social_links = (array) ventcamp_option( 'hero_social', $default );

            // Only if at least one link is set
            if ( isset( $social_links ) && !empty( $social_links ) ) : ?>
                <!-- begin of socials block -->
                <ul class="socials-nav">
                    <?php foreach ( $social_links as $link ) : ?>
                        <li class="socials-nav-item">
                            <a href="<?php echo esc_url( $link[ 'url' ] ); ?>" target="_blank">
                                <span class="fa fa-<?php esc_attr_e( $link[ 'type' ] ); ?>"></span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <!-- end of socials -->
            <?php endif;
        }

        /**
         * Show heading depending on settings, display text wrapped in h1 tag or image
         */
        public function heading () {
            $type = ventcamp_option( 'hero_heading_type', 'text' );

            // If heading is a text
            if ( $type == 'text') : ?>
                <h1 class="hero-heading-main">
                    <?php echo esc_html( ventcamp_option( 'hero_heading_text', 'VENTCAMP' ) ); ?>
                </h1>
            <?php elseif( $type == 'image') : ?>
                <img src="<?php esc_attr_e( ventcamp_option( 'hero_heading_image' ) ); ?>" alt="">
            <?php endif;
        }

        /**
         * Show subtitle
         */
        public function subheading () {
            $subheading = ventcamp_option( 'hero_heading_lower_text', 'Massive conference about web development' );

            // Only if subheading is set
            if ( !empty( $subheading ) ) : ?>
                <span class="hero-heading-bottom">
                    <?php echo esc_html( $subheading ); ?>
                </span>
            <?php endif;
        }
    }
endif;