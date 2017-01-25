<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if(!defined('VENTCAMP_IMPORT_DIR')) {
    define('VENTCAMP_IMPORT_DIR', trailingslashit(dirname(__FILE__)));
}

require_once "reset.php";

// Don't duplicate this class
if ( !class_exists( 'Demo_Importer' ) ) :
    class Demo_Importer {

        // Holds a copy of object for easy access
        private static $instance;

        // Array of menus to assign
        private $menus = array();
        // Path to the demo files
        public $demo_files_path;
        // Path to the content demo file
        public $content_demo;
        // Path to the file with theme options
        public $theme_options;
        // Flag imported to prevent duplicates
        public $imported_demo = array();
        public $imported_parts = array(
            'content' => false,
            'menus'   => false,
            'widgets' => false,
            'options' => false
        );

        /**
         * Demo_Importer constructor.
         */
        public function __construct() {
            self::$instance = $this;

            $this->demo_files_path 		= apply_filters('ventcamp_importer_demo_files_path', VENTCAMP_IMPORT_DIR . 'demodata/default/');
            $this->content_demo 		= apply_filters('ventcamp_importer_content_demo_file', $this->demo_files_path . 'startuply_demo_all.xml');
            $this->theme_options 		= apply_filters('ventcamp_importer_theme_options_file', $this->demo_files_path . 'ventcamp-event-schedule.json');

            // Array of our menus
            $this->menus = array(
                // Main menu
                __( 'Theme main', 'ventcamp' ) => 'primary',
                // Footer menu
                __( 'Footer', 'ventcamp' ) => 'footer',
            );

            // Get saved variables
            $this->imported_demo = get_option( 'ventcamp_imported_demo' );
            // Add 'Demo Data' menu item to admin menu
            add_action( "admin_menu", array( $this, 'add_admin_page' ) );
            // Save variables after import
            add_action( 'ventcamp_import_end', array( $this, 'after_wp_import' ) );
        }

        /**
         * Text for demo data import form.
         */
        private function form_intro_text() {
            if( !empty($this->imported_demo) ) {
                $this->show_alert( __('Demo data already imported', 'ventcamp') );
            } ?>

            <h2><?php echo esc_html( get_admin_page_title() ); ?></h2>

            <p><strong>*NOTE: using this importer more then once can result in duplicate content! Works best on clean WP install*</strong></p>

            <p>
                <ul>
                    <li>- Before starting the import, you need to install all required theme plugins first</li>
                    <li>- Import process will take time needed to download all attachments from demo web site, it can take several minutes. Closing Browser will stop the import process</li>
                    <li>- Please make sure that your server able to do outbound request, we need to download some image that used on demo</li>
                </ul>
            </p>
            <?php
        }

        /**
         * Radio buttons for demo data import form.
         */
        private function form_radio_buttons() {
            ?>
            <div class="radio-group">
                <div class="radio">
                    <input type="radio" name="content_type" id="main_content" value="main_content" checked="checked">
                    <label for="main_content"><?php _e('All content - Pages, Posts, Menus, Forms, Event Schedule, WooCommerce Products', 'ventcamp'); ?></label>
                </div>

                <div class="radio">
                    <input type="radio" name="content_type" id="event_schedule" value="event_schedule">
                    <label for="event_schedule"><?php _e('Additional content - Event schedule', 'ventcamp'); ?></label>
                </div>

                <div class="radio">
                    <input type="radio" name="content_type" id="woo_events" value="woo_events">
                    <label for="woo_events"><?php _e('Additional content - WooCommerce Products', 'ventcamp'); ?></label>
                </div>

                <div class="radio">
                    <input type="radio" name="content_type" id="all_pages" value="all_pages">
                    <label for="all_pages"><?php _e('Pages only', 'ventcamp'); ?></label>
                </div>

                <div class="radio">
                    <input type="radio" name="content_type" id="all_posts" value="all_posts">
                    <label for="all_posts"><?php _e('Posts only', 'ventcamp'); ?></label>
                </div>

                <div class="radio">
                    <input type="radio" name="content_type" id="all_forms" value="all_forms">
                    <label for="all_forms"><?php _e('Forms only', 'ventcamp'); ?></label>
                </div>
            </div>
            <?php
        }

        /**
         * Display an html form with import and reset buttons on admin page.
         */
        public function import_form() {
            // Process submit action
            $this->handle_submit();
            $content_button = empty( $this->imported_demo ) && $this->imported_parts['content'] == false ?  __('Start import', 'ventcamp') : __('Import again', 'ventcamp');
            ?>

            <div id="ventcamp-wrapper" class="wrap">
                <?php $this->form_intro_text(); // Default introductory text ?>

                <form class="ventcamp-import" method="post">
                    <input type="hidden" name="import_nonce" value="<?php echo wp_create_nonce(basename(__FILE__)); ?>" />
                    <input type="hidden" name="action" value="demo-importer" />

                    <?php $this->form_radio_buttons(); ?>

                    <input type="submit" name="import" class="button button-primary" value="<?php echo $content_button; ?>" />
                    <input type="submit" name="reset" class="button" value="<?php _e('Reset current data', 'ventcamp'); ?>" />
                </form>

            </div>

            <?php
        }

        /**
         * Check if user submitted a form, then call an appropriate function.
         */
        private function handle_submit() {
            $import = isset($_POST['import']) && !empty($_POST['import']) ? true : false;
            $reset = isset($_POST['reset']) && !empty($_POST['reset']) ? true : false;
            $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

            if ( $this->check_nonce() && $action == 'demo-importer' ) {
                if ( $import ) {
                    $this->import();
                } elseif ( $reset ) {
                    $this->reset();
                }
            }
        }

        /**
         * Depending on user input, set demo content file.
         *
         * @param string $content_type Type of the content to import
         */
        private function set_demo_file( $content_type ) {
            try {
                switch ( $content_type ) {
                    // All content - Pages, Posts, Menus, Forms, Event Schedule, WooCommerce Products
                    case 'main_content' :
                        $file = 'ventcamp_demo_all_content.xml';
                        break;

                    // Additional content - WooCommerce Products
                    case 'woo_events' :
                        $file = 'ventcamp_demo_woo_products.xml';
                        break;

                    // Pages only
                    case 'all_pages' :
                        $file = 'ventcamp_demo_all_pages.xml';
                        break;

                    // Posts only
                    case 'all_posts' :
                        $file = 'ventcamp_demo_all_posts.xml';
                        break;

                    // Forms only
                    case 'all_forms' :
                        $file = 'ventcamp_demo_all_forms.xml';
                        break;

                    default :
                        throw new Exception( "Invalid content type: " . $content_type, 99 );
                }

                $this->content_demo = $this->demo_files_path . $file;

            } catch (Exception $e) {
                // Show an error with message
                $this->show_alert( $e->getMessage(), 'error' );
                // Stop executing php code
                wp_die();
            }
        }

        /**
         * Show an alert with required type, it can be an info message, error or warning.
         *
         * @param string $message Message to be showed
         * @param string $type Type of the alert: info, error or warning
         */
        private function show_alert( $message, $type = 'success' ) {
            // Allowed types of alert
            $alert_types = array( 'info', 'success', 'error', 'warning' );
            // Set alert class depending on alert type
            $alert_class = in_array( $type, $alert_types ) ? 'notice-' . $type : '';

            if( !empty( $message ) ) : ?>
                <div class="<?php echo $alert_class ?> notice is-dismissible">
                    <p><strong><?php echo $message; ?></strong></p>
                </div>
            <?php endif;
        }

        /**
         * Fix for menu location: get's an id of main menu and set appropriate location
         *
         * @param array $menus Array of menus in the following format: "Menu name" => "Location"
         */
        private function set_menu_locations( $menus = array() ) {
            // Check if user passed a custom array of menus
            $menus = empty( $menus ) ? $this->menus : $menus;
            // Get an array of locations
            $locations = get_nav_menu_locations();

            // Loop through the all menus and assign appropriate location
            foreach ($menus as $name => $location) {
                try {
                    // Get id of our primary menu by menu name
                    $term = get_term_by('name', $name, 'nav_menu');
                    // If nav menu does not exist, show warning
                    if ( $term != false ) {
                        // Get menu id
                        $menu_id = $term->term_id;
                        // Set menu location
                        $locations[ $location ] = $menu_id;
                    } else {
                        throw new Exception( "Nav menu with name \"" . $name . "\" does not exist", 98 );
                    }
                } catch (Exception $e) {
                    // Show warning with message
                    $this->show_alert( $e->getMessage(), 'warning' );
                }
            }

            set_theme_mod( 'nav_menu_locations', $locations );
            // Set flag that menus were configured
            $this->imported_parts['menus'] = true;
        }

        /**
         * Set blog and home pages
         */
        private function set_blog_and_home_pages() {
            // Get home page
            $home = get_page_by_title( 'Home page: theme default' );
            // Get blog page
            $blog = get_page_by_title( 'Blog' );

            update_option('show_on_front', 'page');

            try {
                // If home page exist, update page_on_front option
                if( isset( $home ) && $home->ID ) {
                    update_option('page_on_front', $home->ID); // Front Page
                } else {
                    throw new Exception( "Home page does not exist", 96 );
                }

                // If blog page exist, update page_for_posts option
                if( isset( $blog ) && $blog->ID ) {
                    update_option('page_for_posts', $blog->ID); // Blog Page
                } else {
                    throw new Exception( "Blog page does not exist", 97 );
                }
            } catch (Exception $e) {
                // Show warning
                $this->show_alert( $e->getMessage(), 'warning' );
            }
        }

        /**
         * Import demo data and adjust settings
         */
        public function import() {
            $this->check_libraries();
            // Get user's choice
            $content_type = isset($_POST['content_type']) ? $_POST['content_type'] : '';
            // Except for event schedule
            if ( $content_type != 'event_schedule' ) {
                $this->set_demo_file( $content_type );
                $this->process_import_file();
            }
            // If all content needs to be imported
            if ( $content_type == 'main_content' ) {
                $this->set_blog_and_home_pages();
                $this->set_menu_locations();
                $this->import_widgets();
            }
            // Additional content - event schedule
            if ( $content_type == 'event_schedule' || $content_type == 'main_content' ) {
                $this->process_theme_options_file();
            }

            // Import finished successfully, show an alert
            $this->show_alert( __('Demo data imported successfully. Have fun!', 'ventcamp') );

            // Save variables after import end
            do_action( 'ventcamp_import_end');
        }

        /**
         * Reset wordpress installation
         */
        public function reset() {
            // Init new DB Resetter
            $resetter = new DB_Resetter();
            // Reset DB
            $resetter->reset();
            // Show an alert
            $this->show_alert( __('All posts, pages and forms has been successfully reset', 'ventcamp') );

            // Reset all the flags
            $this->imported_parts['content'] = false;
            $this->imported_parts['widgets'] = false;
            $this->imported_parts['menus'] = false;
            $this->imported_parts['options'] = false;
            unset($this->imported_demo);
            $this->imported_demo = array();
        }

        /**
         * Check if nonce is correct
         *
         * @return boolean True is nonce verified, false otherwise
         */
        private function check_nonce() {
            if ( key_exists( 'import_nonce', $_POST ) ) {
                if ( wp_verify_nonce( $_POST['import_nonce'], basename(__FILE__) ) ) {
                    return true;
                }
            }

            return false;
        }

        /**
         * Check if all necessary libraries are loaded
         */
        public function check_libraries() {
            if ( !defined('WP_LOAD_IMPORTERS') ) {
                define('WP_LOAD_IMPORTERS', true);
            }

            try {
                /* Check presence of WP_Importer */
                if ( !class_exists( 'WP_Importer' ) ) {
                    $class_wp_importer = ABSPATH . 'wp-admin/includes/class-wp-importer.php';
                    if ( file_exists( $class_wp_importer ) ) {
                        require_once( $class_wp_importer );
                    } else {
                        throw new Exception( "Can't find WP_Importer file, path: " . $class_wp_importer, 100 );
                    }
                }

                /* Check presence of WP_Import */
                if ( !class_exists( 'WP_Import' ) ) {
                    $class_wp_import = dirname( __FILE__ ) .'/wordpress-importer.php';
                    if ( file_exists( $class_wp_import ) ) {
                        require_once( $class_wp_import );
                    } else {
                        throw new Exception( "Can't find WP_Import file, path: " . $class_wp_import, 101 );
                    }
                }

                /* Check presence of WP_Options_Importer */
                if ( !class_exists( 'WP_Options_Importer' ) ) {
                    $class_wp_options_importer = dirname( __FILE__ ) .'/wordpress-options-importer.php';
                    if ( file_exists( $class_wp_options_importer ) ) {
                        require_once( $class_wp_options_importer );
                    } else {
                        throw new Exception( "Can't find WP_Options_Importer file, path: " . $class_wp_import, 101 );
                    }
                }
            } catch (Exception $e) {
                // Show an error with message
                $this->show_alert( $e->getMessage(), 'error' );
                // Stop executing php code
                wp_die();
            }
        }

        /**
         * Import demo data from specified file
         *
         * @param string $file Path to file with demo data
         */
        public function process_import_file( $file = '' ) {
            // Check if user passed a custom file
            $file = empty( $file ) ? $this->content_demo : $file;

            try {
                if( !is_file( $file ) ) {
                    throw new Exception(__('File with demo data containing dummy data or could not be read, check file permissions', 'ventcamp'), 102);
                } elseif ( empty( $file ) ) {
                    throw new Exception(__('Demo data file is not specified', 'ventcamp'), 103);
                }

                $wp_import = new WP_Import();
                $wp_import->fetch_attachments = true;
                ob_start();
                $wp_import->import( $file );
                ob_end_clean();

                // Set flag that content was imported
                $this->imported_parts['content'] = true;
            } catch (Exception $e) {
                // File name is not specified or cannot be read, or containing dummy data
                $this->show_alert( $e->getMessage(), 'error' );
                // Stop executing php code
                wp_die();
            }
        }

        /**
         * Import theme options data from specified file
         *
         * @param string $file Path to file with theme options
         */
        public function process_theme_options_file( $file = '' ) {
            global $wp_filesystem;

            // Check if user passed a custom file
            $file = empty( $file ) ? $this->theme_options : $file;

            try {
                if( !is_file( $file ) ) {
                    throw new Exception(__('File with theme options containing dummy data or could not be read, check file permissions', 'ventcamp'), 102);
                } elseif ( empty( $file ) ) {
                    throw new Exception(__('Theme options file is not specified', 'ventcamp'), 103);
                }

                $wp_import = WP_Options_Importer::instance();
                $_POST['settings']['which_options'] = 'all';
                $file_contents = $wp_filesystem->get_contents( $file );
                $wp_import->import_data = json_decode( $file_contents, true );

                ob_start();
                $wp_import->import();
                ob_end_clean();

                // Set flag that options were imported
                $this->imported_parts['options'] = true;
            } catch (Exception $e) {
                // File name is not specified or cannot be read, or containing dummy data
                $this->show_alert( $e->getMessage(), 'error' );
                // Stop executing php code
                wp_die();
            }
        }

        /**
         * Import demo widgets
         */
        public function import_widgets() {
            $params = array(
                array(
                    'sidebar' => 'sidebar_footer_1',
                    'widgets' => array(
                        array(
                            'name' => 'about_sply_widget',
                            'widget_opt_name' => 'widget_about_sply_widget',
                            'args' => array(
                                'title' => '',
                                'description' => 'Ut enim ad minim veniam, quis nostrud exercitation ullamco. Qui officia deserunt mollit anim id est laborum. Ut enim ad minim veniam, quis nostrud exercitation ullamco.',
                                'author' => 'John Doeson, Founder.',
                                'bg_image' => '',
                            )
                        )
                    )
                ),
                array(
                    'sidebar' => 'sidebar_footer_2',
                    'widgets' => array(
                        array(
                            'name' => 'socials_sply_widget',
                            'widget_opt_name' => 'widget_socials_sply_widget',
                            'args' => array(
                                'title' => 'Social Networks',
                                'fb_opt' => 'http://your-social-link-here.com',
                                'twitter_opt' => 'http://your-social-link-here.com',
                                'google_opt' => 'http://your-social-link-here.com',
                                'linkedin_opt' => 'http://your-social-link-here.com',
                                'instagram_opt' => 'http://your-social-link-here.com',
                                'skype_opt' => 'http://your-social-link-here.com',
                                'pinterest_opt' => 'http://your-social-link-here.com',
                                'youtube_opt' => 'http://your-social-link-here.com',
                                'soundcloud_opt' => '',
                                'rss_opt' => '',
                            )
                        )
                    )
                ),
                array(
                    'sidebar' => 'sidebar_footer_3',
                    'widgets' => array(
                        array(
                            'name' => 'contacts_sply_widget',
                            'widget_opt_name' => 'widget_contacts_sply_widget',
                            'args' => array(
                                'title' => __( 'Our Contacts', 'ventcamp' ),
                                'our_email' => 'office@example.com',
                                'our_address' => '2901 Marmora road, Glassgow,<br> Seattle, WA 98122-1090',
                                'our_telephone' => '+9 500 750',
                            )
                        )
                    )
                ),
            );

            $active_widgets = get_option( 'sidebars_widgets' );

            $magick_id = 77;

            foreach ($params as $sidebar) {
                foreach ($sidebar['widgets'] as $widget) {
                    $active_widgets[$sidebar['sidebar']][] = $widget['name'].'-'.$magick_id;

                    $widget_options = get_option( $widget['widget_opt_name'] );
                    $widget_options[$magick_id] = $widget['args'];

                    update_option( $widget['widget_opt_name'], $widget_options );
                }
            }

            update_option( 'sidebars_widgets', $active_widgets );

            // Set flag that content was imported
            $this->imported_parts['widgets'] = true;
        }

        /**
         * Update option after import
         */
        public function after_wp_import() {
            update_option( 'ventcamp_imported_demo', $this->imported_parts );
        }

        /**
         * Add 'Demo Data' menu item to admin menu
         */
        public function add_admin_page() {
            // Save slug to use later
            $this->page_slug = add_menu_page (
                'Demo Data', // Page title
                'Demo Data', // Menu title
                'manage_options', // Capability
                'ventcamp_import', // Menu slug
                array( $this, 'import_form'), // Callback
                'dashicons-download', // Dashicons helper class to use a font icon
                120 // Menu position
            );

            add_action( "admin_print_styles-{$this->page_slug}", array( $this, 'admin_enqueue_assets' ) );
        }

        /**
         * Enqueue admin css stylesheets/js scripts
         */
        public function admin_enqueue_assets() {
            wp_enqueue_style( 'ventcamp-import-css', plugins_url( 'css/ventcamp-import.css', __FILE__ ), false, '', 'all');
            wp_enqueue_script( 'ventcamp-import-js', plugins_url( 'js/ventcamp-import.js', __FILE__ ), array('jquery'), '', true);
        }
    }

    // Init new Demo Importer
    $importer = new Demo_Importer();
endif;