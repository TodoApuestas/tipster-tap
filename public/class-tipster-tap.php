<?php
/**
 * Tipster Tap.
 *
 * @package   Tipster_TAP
 * @author    Alain Sanchez <luka.ghost@gmail.com>
 * @license   GPL-2.0+
 * @link      http://www.linkedin.com/in/mrbrazzi/
 * @copyright 2014 Alain Sanchez
 */

namespace TipsterTAP\Frontend;

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
	const VERSION = '2.6';

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

		add_action( 'customize_register', array( $this, 'customizations' ) );

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
            'url_sync_link_bookies' => 'http://www.todoapuestas.org/tdapuestas/web/api/blocks-bookies/%s/%s/listado-bonos-bookies.json/?access_token=%s&_=%s',
            'url_sync_link_deportes' => 'http://www.todoapuestas.org/tdapuestas/web/api/deporte/listado-visible-blogs.json/?access_token=%s&_=%s',
            'url_sync_link_competiciones' => 'http://www.todoapuestas.org/tdapuestas/web/api/competicion/listado.json/?access_token=%s&_=%s',
	        'url_check_ip' => 'http://www.todoapuestas.org/tdapuestas/web/api/geoip/country-by-ip.json/%s/?access_token=%s&_=%s',
            'tracked_web_category' => 'apuestas',
            'tracker' => $_SERVER['HTTP_HOST']
        );

        /* Define custom functionality.
         * Refer To http://codex.wordpress.org/Plugin_API#Hooks.2C_Actions_and_Filters
         *
         * add_action ( 'hook_name', 'your_function_name', [priority], [accepted_args] );
         *
         * add_filter ( 'hook_name', 'your_filter', [priority], [accepted_args] );
         */
		add_action( 'wp' , array( $this, 'active_remote_sync'));
		add_action( 'tipster_tap_hourly_remote_sync', array( $this, 'remote_sync' ) );

		add_filter( 'tipster_tap_get_tipster_picks', array( $this, 'get_tipster_picks' ), 10, 4 );

		add_filter( 'tipster_tap_default_avatar', array( $this, 'default_avatar' ), 10, 3 );
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
		remove_action( 'tipster_tap_hourly_remote_sync', array( self::$instance, 'remote_sync' ) );
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

	public function customizations(\WP_Customize_Manager $wp_customize)
	{
		$domain = $this->plugin_slug;
		$wp_customize->add_section( 'tipster_tap', array(
			'title' 	=> __( 'Tipsters', $domain ),
			'description' => '',
			'priority' 	=> 2,
		) );

		// Avatar por defecto para tipsters
//		$wp_customize->add_setting('tipster_tap_default_avatar', array(
//			'default'           => plugin_dir_url(__FILE__).'../assets/img/tipster.png',
//			'default'           => null,
//			'capability'        => 'edit_theme_options',
//			'sanitize_callback' => 'sanitize_text_field'
//		));
		$wp_customize->add_setting( 'tipster_tap_default_avatar', array( 'sanitize_callback' => 'sanitize_text_field' ) );

		$wp_customize->add_control( new \WP_Customize_Image_Control( $wp_customize, 'tipster_avatar', array(
			'label'         => __( 'Avatar por defecto', $domain ),
			'description'   => __( 'Seleccionar la imagen a utilizar como avatar por defecto. La dimensiones minimas deben ser de 200x200.', $domain ),
			'section'       => 'tipster_tap',
			'settings'      => 'tipster_tap_default_avatar',
		) ) );
		// Default avatar

		// Limite de picks
		$wp_customize->add_setting( 'tipster_tap_limit_total_picks', array(
			'default'           => 5000,
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'sanitize_text_field',
		) );

		$wp_customize->add_control( 'tipster_tap_limit_total_picks', array(
			'type'        => 'number',
			'label'       => __( 'Total de picks', $domain ),
			'description' => __( 'Escribir la cantidad maxima de registros no pendientes (Aciertos, Fallos, Nulos) a procesar para calculos de Yield, Beneficio, Estadisticas, etc.', $domain ),
			'section'     => 'tipster_tap',
			'settings'    => 'tipster_tap_limit_total_picks',
		) );

		// Racha
		$wp_customize->add_setting( 'tipster_tap_limit_racha_picks_no_pendientes', array(
			'default'           => 10,
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'sanitize_text_field',
		) );

		$wp_customize->add_control( 'tipster_tap_limit_racha_picks_no_pendientes', array(
			'type'        => 'number',
			'label'       => __( 'Racha', $domain ),
			'description' => __( 'Escribir la cantidad maxima de registros no pendientes (Aciertos, Fallos, Nulos) a visualizar como racha', $domain ),
			'section'     => 'tipster_tap',
			'settings'    => 'tipster_tap_limit_racha_picks_no_pendientes',
		) );

		// Estadisticas
		$wp_customize->add_setting( 'tipster_tap_limit_statistics', array(
			'default'           => 6,
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'sanitize_text_field',
		) );

		$wp_customize->add_control( 'tipster_tap_limit_statistics', array(
			'type'        => 'number',
			'label'       => __( 'Estadisticas', $domain ),
			'description' => __( 'Escribir la cantidad de meses a obtener registros para graficar en las estadisticas. Por defecto se asumen 6 meses.', $domain ),
			'section'     => 'tipster_tap',
			'settings'    => 'tipster_tap_limit_statistics',
		) );
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
        if ( !wp_next_scheduled( 'tipster_tap_hourly_remote_sync' ) ) {
            wp_schedule_event(time(), 'hourly', 'tipster_tap_hourly_remote_sync');
        }
    }

    /**
     * Execute synchronizations from todoapuestas.org server
     *
     * @since   1.0
     * @updated 2.1.1
     * @return void
     * @throws \Exception
     */
    public function remote_sync() {
        $option = get_option('tipster_tap_remote_info', $this->default_options);

        $oauthAccessToken = $this->get_oauth_access_token();

	    $timestamp = new \DateTime("now");

        $apiUrl = esc_url(sprintf($option['url_sync_link_bookies'], $option['tracked_web_category'], $option['tracker'], $oauthAccessToken, $timestamp->getTimestamp()));
        $list_bookies = $this->get_result_from_api($apiUrl);
        if(!empty($list_bookies)){
            update_option('tipster_tap_bookies', $list_bookies);
        }

        $apiUrl = esc_url(sprintf($option['url_sync_link_deportes'], $oauthAccessToken, $timestamp->getTimestamp()));
        $list_deportes = $this->get_result_from_api($apiUrl);
        if(!empty($list_deportes['deporte'])){
            update_option('tipster_tap_deportes', $list_deportes['deporte']);
        }

        $apiUrl = esc_url(sprintf($option['url_sync_link_competiciones'], $oauthAccessToken, $timestamp->getTimestamp()));
        $list_competiciones = $this->get_result_from_api($apiUrl);
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

        $query_create_table_statistics = "CREATE TABLE IF NOT EXISTS statistics (".
        "  id int(11) NOT NULL AUTO_INCREMENT,".
        "  corrects int(11) NOT NULL,".
        "  wrongs int(11) NOT NULL,".
        "  voids int(11) NOT NULL,".
        "  total_units float NOT NULL,".
        "  win_units float NOT NULL,".
        "  lost_units float NOT NULL,".
        "  yield float NOT NULL,".
        "  last_stat timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,".
        "  last_stat_date date DEFAULT NULL,".
        "  user_id int(11) NOT NULL,".
        "  PRIMARY KEY (id),".
        "  KEY user_id (user_id),".
        "  KEY user_group_units (user_id,total_units),".
        "  KEY total_units (total_units),".
        "  KEY last_stat_date (last_stat_date),".
        "  KEY last_stat_date_total_units (total_units,last_stat_date),".
        "  KEY user_date_units (user_id,last_stat_date,total_units)".
        ") ENGINE=MyISAM DEFAULT CHARSET=latin1;";

        $wpdb->query($query_create_table_statistics);
    }

	/**
	 * @since 2.3.0
	 * @return array|null
	 */
    private function get_oauth_access_token()
    {
        $session_id = session_id();
        if(empty($session_id) && !headers_sent()) @session_start();
        if(isset($_SESSION['TAP_OAUTH_CLIENT'])){
            $now = new \DateTime('now');
            if($now->getTimestamp() <= intval($_SESSION['TAP_OAUTH_CLIENT']['expires_in'])){
                $oauthAccessToken = $_SESSION['TAP_OAUTH_CLIENT']['access_token'];
                return $oauthAccessToken;
            }
            unset($_SESSION['TAP_OAUTH_CLIENT']);
        }

        $oauthUrl = get_option('TAP_OAUTH_CLIENT_CREDENTIALS_URL');
        $publicId = get_option('TAP_PUBLIC_ID');
        $secretKey = get_option('TAP_SECRET_KEY');
        if(empty($publicId) || empty($secretKey)){
            $_SESSION['TIPSTER_TAP_ERRORS'][] = 'No public or secret key given';
	        return null;
        }

        $oauthUrl = sprintf($oauthUrl, $publicId, $secretKey);
        $oauthResponse = wp_remote_get($oauthUrl);
        if($oauthResponse instanceof \WP_Error || strcmp($oauthResponse['response']['code'], '200') !== 0){
            $_SESSION['TIPSTER_TAP_ERRORS'][] = 'Invalid OAuth response';
	        return null;
        }

        $oauthResponseBody = json_decode($oauthResponse['body']);
        $oauthAccessToken = null;
        if($oauthResponseBody instanceof \WP_Error || !is_object($oauthResponseBody)){
            $_SESSION['TIPSTER_TAP_ERRORS'][] = 'Invalid OAuth access token';
	        return null;
        }
        $oauthAccessToken = $oauthResponseBody->access_token;

        if(!isset($_SESSION['TAP_OAUTH_CLIENT'])){
            $now = new \DateTime('now');
            $_SESSION['TAP_OAUTH_CLIENT'] = array(
                'access_token' => $oauthAccessToken,
                'expires_in' => $now->getTimestamp() + intval($oauthResponseBody->expires_in)
            );
        }

        return $oauthAccessToken;
    }

    private function get_result_from_api($url)
    {
        $apiResponse = wp_remote_get($url);
        if(strcmp($apiResponse['response']['code'], '200') != 0){
            throw new \Exception('Invalid API response');
        }
        $result = json_decode($apiResponse['body'], true);

        return $result;
    }

	/**
	 * @param $title
	 * @param $class
	 * @param $style
	 *
	 * @return string
	 */
    public function default_avatar($title, $class, $style)
    {
    	$image = sprintf('<img src="%1$s" class="%2$s" alt="%3$s" title="%3$s" style="%4$s">', get_theme_mod('tipster_tap_default_avatar'), $class, $title, $style);

	    return $image;
    }

	public function get_tipster_picks($tipster, $limit = -1, $start = 1, $pendientes = false)
	{
		$tipster_picks = array();
		$total = 0;

		// Obtener apuestas acertadas, falladas y nulas pertenecientes al tipster asociado al post
		$meta_query = array(
			'relation' => 'AND'
		);

		$meta_query[] = array(
			'key'     => '_post_tipo_publicacion',
			'value'   => 'pick',
			'compare' => '=',
		);

		$meta_query[] = array(
			'key'     => '_pick_tipster',
			'value'   => $tipster,
			'compare' => '=',
		);

		if($pendientes){
			$meta_query[] = array(
				'key'     => '_pick_resultado',
				'value'   => 'pendiente',
				'compare' => '=',
			);
		}else{
			$meta_query[] = array(
				'relation' => 'OR',
				array(
					'key'     => '_pick_resultado',
					'value'   => 'acierto',
					'compare' => '=',
				),
				array(
					'key'     => '_pick_resultado',
					'value'   => 'fallo',
					'compare' => '=',
				),
				array(
					'key'     => '_pick_resultado',
					'value'   => 'nulo',
					'compare' => '=',
				),
			);
		}

		$query = array(
			'post_type'              => 'post',
			'post_status'            => 'publish',
			'posts_per_page'         => $limit,
			'paged'                  => $start,
			'order'                  => 'DESC',
			'meta_query'             => $meta_query,
			'cache_results'          => false,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'ignore_sticky_posts'    => true
		);

		$query_result = new \WP_Query($query);
		if($query_result->have_posts()){
			$tipster_picks = $query_result->get_posts();
			$total = $query_result->post_count;
		}

		if(!$pendientes && $limit == -1){
			update_post_meta($tipster, '_tipster_total_picks_finalizados', $total);
		}

		if($pendientes && $limit == -1){
			update_post_meta($tipster, '_tipster_total_picks_pendientes', $total);
		}

		return $tipster_picks;
	}
}
