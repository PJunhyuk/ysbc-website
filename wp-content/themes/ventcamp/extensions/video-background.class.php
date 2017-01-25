<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

// Require core Tag_Generator class
require_once THEME_DIR . "/extensions/core/tag_generator.class.php";

// Don't duplicate this class
if ( !class_exists( 'Video_Background' ) ) :
    /**
     * Helper class, adds data-* properties to container and enqueue scripts to play video in background.
     */
    class Video_Background extends Tag_Generator {
        // URL to the video
        public $video_url;
        // Array of properties to pass to jquery.mb.ytplayer
        public $ytplayer_properties = array();
        // Array of background attributes, such as controls, autoplay, mute and so on
        public $bg_attributes = array();
        // Will video be muted or not
        public $mute;
        // Play video immediately when page is loaded
        public $autoplay;
        // Add controls to video
        public $controls;
        // Variable to store video settings
        protected $video_settings = array();

        /**
         * Video_Background constructor.
         */
        public function __construct() {
            // Default params for ytplayer, can be overridden later
            $this->ytplayer_properties = array(
                "containment"       => '#hero',
                "realfullscreen"    => true,
                "stopMovieOnBlur"   => false,
                "addRaster"         => true,
                "showControls"      => false, // Don't show controls
                "startAt"           => 0,     // Start video from the beginning
                "opacity"           => 1,     // No transparency at all
                "gaTrack"           => false
            );

            // Get video background setting
            $this->video_settings = (array) ventcamp_option(
                'hero_background_video_settings',       // Name of the option
                array( 'controls', 'mute', 'autoplay' ) // Default values (all enabled by default, used as fallback)
            );

            $this->set_data_tags();
        }

        /**
         * Check every option and set variables, according to user settings
         */
        public function set_main_properties () {
            $this->set_background_video_url();
            $this->set_mute();
            $this->set_controls();
            $this->set_autoplay();
        }

        /**
         * Get background video URL from settings
         */
        public function set_background_video_url () {
            // Get background video URL
            $this->video_url = ventcamp_option( 'hero_background_video' );
        }

        /**
         * If in video background settings checkbox 'Mute video by default' is set,
         * set mute to 'true' and 'false' otherwise
         */
        public function set_mute () {
            // If checkbox 'Mute video' is set
            if ( in_array( 'mute', $this->video_settings ) ) {
                $this->mute = true;
            } else {
                $this->mute = false;
            }
        }

        /**
         * If in video background settings checkbox 'Add video controls' is set,
         * set controls to 'true' and enqueue ui-slider, otherwise set 'false'
         */
        public function set_controls () {
            // If checkbox 'Add video controls' is set
            if ( in_array( 'controls', $this->video_settings ) ) {
                $this->controls = ventcamp_option(
                    'hero_background_video_controls', // Name of the option
                    'left'                            // Default value (used as fallback)
                );
                $this->enqueue_slider();
            } else {
                $this->controls = 'none';
            }
        }

        /**
         * If in video background settings checkbox 'Play video automatically' is set,
         * set autoplay to 'true' and 'false' otherwise
         */
        public function set_autoplay () {
            // If checkbox 'Play video automatically' is set
            if ( in_array( 'autoplay', $this->video_settings ) ) {
                $this->autoplay = true;
            } else {
                $this->autoplay = false;
            }
        }

        /**
         * Generate JSON-formatted list of properties that will be assigned to 'data-property' attribute
         * and used by YTPlayer
         */
        protected function generate_ytplayer_data_property () {
            // Assign values to ytplayer properties array
            $this->ytplayer_properties['videoURL'] = $this->video_url;
            $this->ytplayer_properties['mute']     = $this->mute;
            $this->ytplayer_properties['autoplay'] = $this->autoplay;

            /*
             * Generate JSON-formatted list of properties with unescaped slashes
             *
             * For example:
             * {
             *   videoURL: 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
             *   realfullscreen: true,
             *   showControls: false,
             * }
             *
             */
            $data = json_encode( $this->ytplayer_properties, JSON_UNESCAPED_SLASHES );

            // Assign generated data to bg_attributes array
            $this->bg_attributes['data-property'] = $data;
        }

        /**
         * Generate resulted data-* tags
         */
        protected function set_data_tags () {
            // Get video background settings and set all the variables
            $this->set_main_properties();

            // Only if video is set
            if ( !empty( $this->video_url ) ) {
                $this->generate_ytplayer_data_property();

                // Assign generated data to bg_attributes array
                $this->bg_attributes['data-video-url']  = $this->video_url;
                $this->bg_attributes['data-controls']   = $this->controls;
                $this->bg_attributes['data-mute']       = $this->mute;
                $this->bg_attributes['data-autoplay']   = $this->autoplay;

                // Enqueue ytplayer's script
                $this->enqueue_ytplayer();
                $this->enqueue_ventcamp_video_background();
            }
        }

        /**
         * Convert array of tags to string and return the result
         *
         * @return string Data tags in string format
         */
        public function get_data_tags () {
            return Tag_Generator::generate_tag_attributes( $this->bg_attributes );
        }

        /**
         * Enqueue YTPlayer script, an open source jQuery component to play youtube video in background.
         */
        protected function enqueue_ytplayer () {
            wp_enqueue_script( 'youtube-video-bg', SCRIPTSPATH_URI . '/lib/jquery.mb.YTPlayer.min.js', array('jquery'), false, true );
        }

        /**
         * Enqueue UI-slider script.
         */
        protected function enqueue_slider () {
            wp_enqueue_script( 'ui-slider', SCRIPTSPATH_URI . '/lib/jquery-ui-slider.min.js', array('jquery'), false, true);
        }

        /**
         * Enqueue our custom script with video settings.
         */
        protected function enqueue_ventcamp_video_background () {
            wp_enqueue_script( 'ventcamp-video-background', SCRIPTSPATH_URI . '/ventcamp-video-background.js', array( 'jquery' ), false, true);
        }
    }
endif;