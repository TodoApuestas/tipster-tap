<?php
/**
 * Plugin Name.
 *
 * @package   Tipster_TAP
 * @author    Alain Sanchez <asanchezg@inetzwerk.com>
 * @license   GPL-2.0+
 * @link      http://www.inetzwerk.com
 * @copyright 2014 Alain Sanchez
 */

/**
 * Plugin class. This class should ideally be used to work with the
 * public-facing side of the WordPress site.
 *
 * If you're interested in introducing administrative or dashboard
 * functionality, then refer to `class-tipster-tap-admin.php`
 *
 *
 * @package Tipster_TAP
 * @author  Your Name <email@example.com>
 */
class Tipster_TAP {

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since   1.0.0
	 *
	 * @var     string
	 */
	const VERSION = '1.0.0';

	/**
	 * Unique identifier for your plugin.
	 *
	 *
	 * The variable name is used as the text domain when internationalizing strings
	 * of text. Its value should match the Text Domain file header in the main
	 * plugin file.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_slug = 'tipster-tap';

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

    private $default_options;

	/**
	 * Initialize the plugin by setting localization and loading public scripts
	 * and styles.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {

		// Load plugin text domain
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

		// Activate plugin when new blog is added
		add_action( 'wpmu_new_blog', array( $this, 'activate_new_site' ) );

		// Load public-facing style sheet and JavaScript.
//		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
//		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

        /**
         * Define the default options
         *
         * @since     1.0
         * @updated   2.0.0
         */
        $this->default_options = array(
            'url_sync_link_bookies' => 'http://www.todoapuestas.org/tdapuestas/web/api/%s/bookie/listado-as-array.json/?_=%s',
            'url_sync_link_deportes' => 'http://www.todoapuestas.org/tdapuestas/web/api/%s/deporte/listado-visible-blogs.json/?_=%s',
            'url_sync_link_competiciones' => 'http://www.todoapuestas.org/tdapuestas/web/api/%s/competicion/listado.json/?_=%s',
	        'url_check_ip' => 'http://www.todoapuestas.org/tdapuestas/web/api/%s/geoip/country-by-ip.json/%s/?_=%s'
        );

        /* Define custom functionality.
         * Refer To http://codex.wordpress.org/Plugin_API#Hooks.2C_Actions_and_Filters
         *
         * add_action ( 'hook_name', 'your_function_name', [priority], [accepted_args] );
         *
         * add_filter ( 'hook_name', 'your_filter', [priority], [accepted_args] );
         */

        add_action( 'sync_hourly_event', array( $this, 'remote_sync' ) );
        add_action( 'wp' , array( $this, 'active_remote_sync'));
	}

	/**
	 * Return the plugin slug.
	 *
	 * @since    1.0.0
	 *
	 * @return    Plugin slug variable.
	 */
	public function get_plugin_slug() {
		return $this->plugin_slug;
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Fired when the plugin is activated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses
	 *                                       "Network Activate" action, false if
	 *                                       WPMU is disabled or plugin is
	 *                                       activated on an individual blog.
	 */
	public static function activate( $network_wide ) {

		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			if ( $network_wide  ) {

				// Get all blog ids
				$blog_ids = self::get_blog_ids();

				foreach ( $blog_ids as $blog_id ) {

					switch_to_blog( $blog_id );
					self::single_activate();

					restore_current_blog();
				}

			} else {
				self::single_activate();
			}

		} else {
			self::single_activate();
		}

	}

	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses
	 *                                       "Network Deactivate" action, false if
	 *                                       WPMU is disabled or plugin is
	 *                                       deactivated on an individual blog.
	 */
	public static function deactivate( $network_wide ) {

		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			if ( $network_wide ) {

				// Get all blog ids
				$blog_ids = self::get_blog_ids();

				foreach ( $blog_ids as $blog_id ) {

					switch_to_blog( $blog_id );
					self::single_deactivate();

					restore_current_blog();

				}

			} else {
				self::single_deactivate();
			}

		} else {
			self::single_deactivate();
		}

	}

	/**
	 * Fired when a new site is activated with a WPMU environment.
	 *
	 * @since    1.0.0
	 *
	 * @param    int    $blog_id    ID of the new blog.
	 */
	public function activate_new_site( $blog_id ) {

		if ( 1 !== did_action( 'wpmu_new_blog' ) ) {
			return;
		}

		switch_to_blog( $blog_id );
		self::single_activate();
		restore_current_blog();

	}

	/**
	 * Get all blog ids of blogs in the current network that are:
	 * - not archived
	 * - not spam
	 * - not deleted
	 *
	 * @since    1.0.0
	 *
	 * @return   array|false    The blog ids, false if no matches.
	 */
	private static function get_blog_ids() {

		global $wpdb;

		// get an array of blog ids
		$sql = "SELECT blog_id FROM $wpdb->blogs
			WHERE archived = '0' AND spam = '0'
			AND deleted = '0'";

		return $wpdb->get_col( $sql );

	}

	/**
	 * Fired for each blog when the plugin is activated.
	 *
	 * @since    1.0.0
	 */
	private static function single_activate() {
        add_option('tipster_tap_remote_info', self::get_instance()->default_options);
        add_option('tipster_tap_bookies', array());
        add_option('tipster_tap_deportes', array());
        add_option('tipster_tap_competiciones', array());

        //execute create statistics table
        self::get_instance()->create_statistics_table();
        // execute initial synchronization
        self::get_instance()->remote_sync();
	}

	/**
	 * Fired for each blog when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 */
	private static function single_deactivate() {
        remove_action( 'sync_hourly_event', array( self::$instance, 'remote_sync' ) );
        remove_action( 'wp' , array( self::$instance, 'active_remote_sync'));

        delete_option('tipster_tap_remote_info');
        delete_option('tipster_tap_bookies');
        delete_option('tipster_tap_deportes');
        delete_option('tipster_tap_competiciones');
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		$domain = $this->plugin_slug;
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

		load_textdomain( $domain, trailingslashit( WP_LANG_DIR ) . $domain . '/' . $domain . '-' . $locale . '.mo' );
		load_plugin_textdomain( $domain, FALSE, basename( plugin_dir_path( dirname( __FILE__ ) ) ) . '/languages/' );

	}

	/**
	 * Register and enqueue public-facing style sheet.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_slug . '-plugin-styles', plugins_url( 'assets/css/public.css', __FILE__ ), array(), self::VERSION );
	}

	/**
	 * Register and enqueues public-facing JavaScript files.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_slug . '-plugin-script', plugins_url( 'assets/js/public.js', __FILE__ ), array( 'jquery' ), self::VERSION );
	}

    /**
     * Activate remote synchronization hourly
     *
     * @since   1.0
     */
    public function active_remote_sync() {
        if ( !wp_next_scheduled( 'sync_hourly_event' ) ) {
            wp_schedule_event(time(), 'hourly', 'sync_hourly_event');
        }
    }

    /**
     * Execute synchronizations from todoapuestas.org server
     *
     * @since   1.0
     * @return array|void
     */
    public function remote_sync() {
        $option = get_option('tipster_tap_remote_info', $this->default_options);
	    $apiKey = get_option('TAP_API_KEY');
	    $timestamp = new DateTime("now");

	    $url_sync_link_bookies = esc_url(sprintf($option['url_sync_link_bookies'], $apiKey, $timestamp->getTimestamp()));
        $bookies = trim(@file_get_contents($url_sync_link_bookies));
        $list_bookies = json_decode($bookies, true);

        if(!empty($list_bookies['bookies'])){
            update_option('tipster_tap_bookies', $list_bookies['bookies']);
        }

	    $url_sync_link_deportes = esc_url(sprintf($option['url_sync_link_deportes'], $apiKey, $timestamp->getTimestamp()));
        $deportes = trim(@file_get_contents($url_sync_link_deportes));
        $list_deportes = json_decode($deportes, true);

        if(!empty($list_deportes['deporte'])){
            update_option('tipster_tap_deportes', $list_deportes['deporte']);
        }

	    $url_sync_link_competiciones = esc_url(sprintf($option['url_sync_link_competiciones'], $apiKey, $timestamp->getTimestamp()));
        $competiciones = trim(@file_get_contents($url_sync_link_competiciones));
        $list_competiciones = json_decode($competiciones, true);

        if(!empty($list_competiciones['competicion'])){
            update_option('tipster_tap_competiciones', $list_competiciones['competicion']);
        }
    }

	/**
	 * @since     1.1.6
	 */
    public function create_statistics_table()
    {
        global $wpdb;

        $query_create_table_statistics = "CREATE TABLE IF NOT EXISTS `statistics` (".
        "  `id` int(11) NOT NULL AUTO_INCREMENT,".
        "  `corrects` int(11) NOT NULL,".
        "  `wrongs` int(11) NOT NULL,".
        "  `voids` int(11) NOT NULL,".
        "  `total_units` float NOT NULL,".
        "  `win_units` float NOT NULL,".
        "  `lost_units` float NOT NULL,".
        "  `yield` float NOT NULL,".
        "  `last_stat` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,".
        "  `last_stat_date` date DEFAULT NULL,".
        "  `user_id` int(11) NOT NULL,".
        "  PRIMARY KEY (`id`),".
        "  KEY `user_id` (`user_id`),".
        "  KEY `user_group_units` (`user_id`,`total_units`),".
        "  KEY `total_units` (`total_units`),".
        "  KEY `last_stat_date` (`last_stat_date`),".
        "  KEY `last_stat_date_total_units` (`total_units`,`last_stat_date`),".
        "  KEY `user_date_units` (`user_id`,`last_stat_date`,`total_units`)".
        ") ENGINE=MyISAM DEFAULT CHARSET=latin1;";

        $wpdb->query($query_create_table_statistics);
    }
}
