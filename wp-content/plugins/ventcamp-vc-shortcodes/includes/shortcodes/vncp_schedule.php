<?php

vc_map( array(
    'name' => __( 'Ventcamp Schedule', 'ventcamp' ),
    'base' => 'vc_schedule',
    'icon' => 'icon-schedule',
    'category' => __( 'Ventcamp', 'ventcamp' ),
    'description' => __( 'Schedule tabbed block', 'ventcamp' ),
    'params' => array(
        array(
            'type' => 'textfield',
            'heading' => __( 'Extra class name', 'ventcamp' ),
            'param_name' => 'el_class',
            'description' => __( 'If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.', 'ventcamp' )
        )
    )
) );

// Only if "Advanced Custom Fields" plugin is enabled
if ( class_exists( 'acf' ) ) {

    if ( !class_exists( 'VC_schedule_shortcode' ) ) {
        /**
         * Class for VC_schedule shortcode
         */
        class VC_schedule_shortcode {
            // Shortcode tag
            public $shortcode_tag = 'vc_schedule';
            // Holds shortcode attributes
            public $attr = array();
            // Shortcode content
            public $content;

            // Type of the schedule shortcode
            protected $type;
            // A default set of icons
            protected static $icons = array(
                'clock'      => '<i class="icon icon-office-24"></i>',
                'arrow-down' => '<i class="icon icon-arrows-06"></i>'
            );

            /**
             * VC_schedule_shortcode constructor.
             *
             * @param array $args Shortcode arguments
             */
            function __construct( $args = array() ) {
                // Add shortcode
                add_shortcode( $this->shortcode_tag, array( $this, 'shortcode_handler' ) );
            }

            /**
             * Handling of attributes
             *
             * @param array $attributes Array of attributes passed to shortcode
             * @param string $content Shortcode content
             *
             * @return string Result
             */
            function shortcode_handler ( $attributes , $content = null ) {
                /*
                 * Get shortcode attributes
                 */
                $this->attr = shortcode_atts(
                    array(
                        'el_class' => '' // Custom element class
                    ),
                    $attributes
                );

                // Assign shortcode content
                $this->content = $content;
                // Get the type of the event from settings
                $this->type = get_field( 'vncp_event_type', 'option' );

                // Generate schedule
                $result = $this->schedule();

                return $result;
            }

            /**
             * Generate schedule with required type
             */
            function schedule () {
                $result = '<div class="schedule">';

                // Check event type and call the appropriate method
                switch ( $this->type ) {
                    // Only one day without threads
                    case 'one_day_no_threads' :
                        $result .= $this->one_day_without_threads();
                        break;

                    // Multi-day event with threads
                    case 'multi_day_with_threads' :
                        $result .= $this->multi_days_with_threads();
                        break;

                    // Multi-day event without threads
                    case 'multi_day_no_threads' :
                        $result .= $this->multi_days_without_threads();
                        break;

                    default :
                        $result .= __( 'No schedule found, please add or import one', 'ventcamp' );
                        break;
                }

                $result .= '</div>';

                return $result;
            }

            /**
             * Template for one-day event without threads
             */
            protected function one_day_without_threads () {
                // Get the schedule from settings
                $schedule = get_field( 'vncp_one_day_schedule', 'option' );

                // Get only the first day
                $day = $schedule[0];

                $output  = '<div class="schedule-simple">';
                $output .=     $this->simple_timeline( $day['lectures'] );
                $output .= '</div>';

                return $output;
            }

            /**
             * Just a simple list of lectures without tabs.
             *
             * @param array $timeline Array of lectures for one day
             *
             * @return string A resulted timeline.
             */
            protected function simple_timeline( $timeline ) {
                $output = "";

                // If timeline is empty, show a "no schedule yet" message
                if ( empty( $timeline ) ) {
                    return '<div class="schedule-item">' . __('No schedule yet.', 'ventcamp') . '</div>';
                }

                // Loop through the all timeline and process each lecture
                foreach ( $timeline as $lecture ) {
                    $output .= '<div class="schedule-simple-item">';

                    // Only if time is set
                    if ( !empty( $lecture['time'] ) ) {
                        $output .= '<div class="schedule-simple-time">';
                        $output .=     VC_schedule_shortcode::$icons['clock'] . $lecture['time'];
                        $output .= '</div>';
                    }

                    // Lecture content (info, location, title and description)
                    $output .= '<article>';

                        // Only if extra info is set
                        if ( !empty( $lecture['extra_info'] ) ) {
                            $output .= '<div class="schedule-simple-extra-info">';
                            $output .=     $lecture['extra_info'];
                            $output .= '</div>';
                        }

                        // Only if location is set
                        if ( !empty( $lecture['location'] ) ) {
                            $output .= '<div class="schedule-simple-location">';
                            $output .=     $lecture['location'];
                            $output .= '</div>';
                        }

                        // Lecture title
                        $output .= !empty( $lecture['title'] ) ? "<h4>${lecture['title']}</h4>" : "";
                        // Lecture description
                        $output .= !empty( $lecture['description'] ) ? '<p class="description">' . nl2br($lecture['description']) . '</p>' : '';

                    $output .= '</article>';

                    $output .= '</div>';
                }

                return $output;
            }

            /**
             * Template for multi-day event with threads
             */
            protected function multi_days_with_threads () {
                // Get the schedule from settings
                $schedule = get_field( 'vncp_md_w_thrds', 'option' );

                // Make a clickable tabs for each day
                $output = $this->make_schedule_tabs( $schedule, 'day' );

                // Wrapper for the schedule content
                $output .= '<div class="tab-content">';
                // Loop through the all days and make the content
                foreach ( $schedule as $index => $day ) {
                    // Is it the first day?
                    $first_day_class = ( $index == 0 ) ? 'active' : '';
                    // Make a unique day ID
                    $day_id = 'day' . $index;

                    // A tab pane for threads
                    $output .= "<div id='${day_id}' class='tab-pane fade in ${first_day_class}'>";
                    $output .=     $this->threads( $day['threads'], $day_id );
                    $output .= "</div>";
                }
                $output .= "</div>";

                return $output;
            }

            /**
             * Make a panel with clickable tabs with all threads for specified day.
             *
             * @param  array  $threads An array of threads
             * @param  string $parent_id ID of the parent day
             *
             * @return string A panel with all threads and lectures for specified day
             */
            protected function threads ( $threads, $parent_id = 'noday' ) {
                // Make a clickable tabs for each day
                $output = $this->make_schedule_tabs(
                    $threads, // Array of threads with titles and date
                    $parent_id . '_auditorium', // ID for the tab
                    true // It's a subtabs, make a heading just a little bit smaller
                );

                // Wrapper for the threads content
                $output .= '<div class="tab-content tab-content-schedule">';
                // Wrapper for th
                foreach ( $threads as $index => $thread ) {
                    // Is it the first thread?
                    $first_thread_class = ( $index == 0 ) ? 'active' : '';
                    // Make a unique thread ID
                    $thread_id = $parent_id . '_auditorium' . $index;

                    $output .= "<div id='${thread_id}' class='tab-pane fade in ${first_thread_class}'>";
                    $output .=     "<div class='panel-group' id='${thread_id}_timeline'>";
                    $output .=         $this->timeline( $thread['lectures'], $thread_id . '_timeline' );
                    $output .=     "</div>";
                    $output .= "</div>";
                }
                $output .= '</div>';

                return $output;
            }

            /**
             * Template for multi-day event without threads
             */
            protected function multi_days_without_threads () {
                // Get the schedule from settings
                $schedule = get_field( 'vncp_md_wo_thrds', 'option' );

                // Make a clickable tabs for each day
                $output = $this->make_schedule_tabs( $schedule, 'day' );

                // Wrapper for the schedule content
                $output .= '<div class="tab-content tab-content-schedule">';

                // Loop through the all days and make the content
                foreach ( $schedule as $index => $day ) {
                    // Is it the first day?
                    $first_day_class = ( $index == 0 ) ? 'active' : '';
                    // Unique day ID
                    $day_id = "day" . $index;

                    $output .= "<div id='${day_id}' class='tab-pane fade in ${first_day_class}'>";
                    $output .=     $this->timeline( $day['lectures'], 'day' . $index );
                    $output .= "</div>";
                }

                $output .= '</div>';

                return $output;
            }

            /**
             * Make the tabs for each day of the event.
             *
             * @param array  $schedule Array of days/threads with date and title
             * @param string $tab_id   ID for the tab's links
             * @param bool   $subtabs  Is it a subtabs or not
             *
             * @return string The resulted schedule tabs
             */
            protected function make_schedule_tabs ( array $schedule, $tab_id, $subtabs = false ) {
                // Set an additional class if schedule has only one day
                $one_day_class = count( $schedule ) == 1 ? 'one-child-nav' : '';
                // The wrapper for schedule list
                $output = '<ul class="nav nav-schedule ' . $one_day_class .'">';

                // Loop through the all days and make a list
                foreach ( $schedule as $index => $day ) {
                    // Is it the first day?
                    $first_day_class = ( $index == 0 ) ? ' class="active"' : '';

                    // Make the tab
                    $output .= "<li ${first_day_class}>";
                    $output .=     "<a href='#${tab_id}${index}' data-toggle='tab'>";

                    // If it's a one-day event with no threads or subtabs
                    if ( $this->type == 'one_day_no_threads' || $subtabs == true ) {
                        // Just a title without a date
                        $output .= $day['title'];
                    } else {
                        // Add a title with date
                        $output .= $this->tab_title_with_date( $day['title'], $day['date'] );
                    }

                    $output .=     "</a>";
                    $output .= "</li>";
                }

                $output .= "</ul>";

                return $output;
            }

            /**
             * Helper function to output the tab title with date.
             * The title will be a bit smaller for schedules without threads.
             *
             * @param string $title  Title of the tab
             * @param string $date   Date for the tab
             *
             * @return string Resulted title with date
             */
            protected function tab_title_with_date ( $title, $date ) {
                // If it's a schedule without threads, make the title smaller
                $tag = ( $this->type == 'multi_day_no_threads' ) ? 'h5' : 'h4';

                // Only if title is set
                $output  = !empty( $title ) ? "<${tag} class='highlight'>${title}</${tag}>" : "";
                // Only if date is set
                $output .= !empty( $date ) ? "<p class='text-alt'>${date}</p>" : "";

                return $output;
            }

            /**
             * Timeline with list of lectures for specific thread.
             *
             * @param array  $timeline  Timeline for the specific thread
             * @param string $parent_id ID of the thread
             *
             * @return string The resulting timeline.
             */
            protected function timeline ( $timeline, $parent_id = 'noparent' ) {
                $output = "";

                // If timeline is empty, show the "no schedule yet" message
                if ( empty( $timeline ) ) {
                    return '<div class="schedule-item">' . __('No schedule yet.', 'ventcamp') . '</div>';
                }

                // Loop through the all lectures on the timeline
                foreach ( $timeline as $index => $lecture ) {
                    $output .= '<div class="panel schedule-item">';

                    // Add an lecture icon
                    $output .= $this->lecture_icon( $lecture['icon'] );

                    /*
                     * Collapse item title
                     */

                    // Collapse all the lectures, except the first one
                    $first_lecture_class = ( $index == 0 ) ? '' : 'collapsed';
                    // Unique lecture ID
                    $lecture_id = $parent_id . '_time' . $index;

                    // Make a link to the parent and the panel content
                    $output .= "<a data-toggle='collapse'
                                   data-parent='#${parent_id}'
                                   href='#${lecture_id}'
                                   class='schedule-item-toggle ${first_lecture_class}'>";

                    // Only if time of the lecture is set
                    if ( !empty( $lecture['time'] ) ) {
                        $output .= '<strong class="time highlight">';
                        $output .=     VC_schedule_shortcode::$icons['clock'] . $lecture['time'];
                        $output .= '</strong>';
                    }

                    // Only if lecture title is set
                    if ( !empty( $lecture['title'] ) ) {
                        $output .= '<h6 class="title">';
                        $output .=     $lecture['title'] . VC_schedule_shortcode::$icons['arrow-down'];
                        $output .= '</h6>';
                    }

                    $output .= '</a>';

                    /*
                     * Collapse panel with lecture description
                     */

                    // Description of the first lecture should be open
                    $first_panel_class = $index != 0 ? '' : 'in';

                    $output .= "<div id='${lecture_id}' class='panel-collapse collapse schedule-item-body ${first_panel_class}'>";
                    $output .=     "<article>";
                    $output .=         "<p class='description'>${lecture['description']}</p>";
                    $output .=         "<strong class='highlight speaker-name'>${lecture['lecturer']}</strong>";
                    $output .=     "</article>";
                    $output .= "</div>";

                    $output .= '</div>';
                }

                return $output;
            }

            /**
             * If icon is set, make a tag with icon background image.
             * If not, output the default icon.
             *
             * @param string $icon Icon image
             *
             * @return string The resulted icon.
             */
            protected function lecture_icon ( $icon ) {
                // If the custom icon is set
                if ( isset( $icon['sizes']['thumbnail'] ) && !empty( $icon['sizes']['thumbnail'] ) ) {
                    // Set a custom icon image
                    $icon_image = "background-image: url('" . $icon['sizes']['thumbnail'] . "')";
                    // Make an icon tag
                    return '<div class="lecture-icon-wrapper" style="'.$icon_image.'"></div>';
                } else {
                    // Output the default icon
                    return '<div class="lecture-icon-wrapper default-icon"></div>';
                }
            }
        }

        // Init our class
        new VC_schedule_shortcode();
    }
}
