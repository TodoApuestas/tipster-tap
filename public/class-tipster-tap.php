<?php
/**
 * Tipster Tap.
 *
 * @package   TipsterTap
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
 * @package TipsterTap
 * @author  Your Name <email@example.com>
 */
class TipsterTap {

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since   1.0.0
     * @updated 4.1
     * @updated 4.2
	 *
	 * @var     string
	 */
	const VERSION = TIPSTER_TAP_VERSION;

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
	protected static $instance;

    private $default_options;

	/**
	 * Initialize the plugin by setting localization and loading public scripts
	 * and styles.
	 *
	 * @since     1.0.0
	 * @updated   3.2
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

        /* Define custom functionality.
         * Refer To http://codex.wordpress.org/Plugin_API#Hooks.2C_Actions_and_Filters
         *
         * add_action ( 'hook_name', 'your_function_name', [priority], [accepted_args] );
         *
         * add_filter ( 'hook_name', 'your_filter', [priority], [accepted_args] );
         */
		add_action( 'wp' , array( $this, 'active_remote_sync'));
		add_action( 'tipster_tap_hourly_remote_sync', array( $this, 'remote_sync' ) );
		
		/**
		 * deprecated 3.0
		 */
		add_filter( 'tipster_tap_get_tipster_picks', array( $this, 'get_tipster_picks' ), 10, 4 );
		
		add_filter( 'tipster_tap_get_picks', array( $this, 'get_picks' ), 10, 2 );
		add_action( 'tipster_tap_get_total_picks', array( $this, 'get_total_picks' ) );
		
		add_filter( 'tipster_tap_default_avatar', array( $this, 'default_avatar' ), 10, 3 );
	}

	/**
	 * Return the plugin slug.
	 *
	 * @since    1.0.0
	 *
	 * @return    string  Plugin slug variable.
	 */
	public function get_plugin_slug() {
		return $this->plugin_slug;
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    TipsterTap    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null === self::$instance ) {
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
		$sql = "SELECT blog_id FROM $wpdb->blogs WHERE archived = '0' AND spam = '0' AND deleted = '0'";

		return $wpdb->get_col( $sql );

	}

	/**
	 * Fired for each blog when the plugin is activated.
	 *
	 * @since    1.0.0
	 * @updated   3.2
	 */
	private static function single_activate() {
        //execute db update table
        self::get_instance()->db_update();
        // execute initial synchronization
        self::get_instance()->remote_sync();
	}

	/**
	 * Fired for each blog when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 * @updated   3.2
	 */
	private static function single_deactivate() {
		remove_action( 'tipster_tap_hourly_remote_sync', array( self::$instance, 'remote_sync' ) );
        remove_action( 'wp' , array( self::$instance, 'active_remote_sync'));
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 * @updated   3.2
	 */
	public function load_plugin_textdomain() {

		$domain = $this->plugin_slug;
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

		load_textdomain( $domain, trailingslashit( WP_LANG_DIR ) . $domain . '/' . $domain . '-' . $locale . '.mo' );
		load_plugin_textdomain( $domain, FALSE, basename( plugin_dir_path( __DIR__ ) ) . '/languages/' );

	}
	
	/**
	 * @param \WP_Customize_Manager $wp_customize
	 *
	 * @since 2.0
	 * @updated   2.6
	 * @updated   3.0
	 * @updated   3.2
	 */
	public function customizations(\WP_Customize_Manager $wp_customize)
	{
		$domain = $this->plugin_slug;
		$wp_customize->add_section( 'tipster_tap', array(
			'title' 	=> __( 'Tipsters', $domain ),
			'description' => '',
			'priority' 	=> 2,
		) );

		// Avatar por defecto para tipsters
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
			'description' => __( 'Escribir la cantidad de meses a obtener registros para calcular y/o graficar las estadisticas. Por defecto se asumen 6 meses.', $domain ),
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
	 * Execute synchronizations from todoapuestas.com server
	 *
	 * @since   1.0
	 * @updated 2.1.1
     * @updated 3.2
     *
	 * @return void
	 */
    public function remote_sync() {
        do_action('rest_client_tap_request_bookies');
        do_action('rest_client_tap_request_sports');
        do_action('rest_client_tap_request_competitions');
    }

	/**
	 * @since     3.0
	 */
    public function db_update()
    {
	    global $wpdb;
	    
	    $option = (float)get_option('tipster_tap_version');
    	
	    if( $option < 1.0 ) {
		    $query_create_table_statistics = 'CREATE TABLE IF NOT EXISTS statistics (' .
				'  id INT(11) NOT NULL AUTO_INCREMENT,' .
				'  corrects INT(11) NOT NULL,' .
				'  wrongs INT(11) NOT NULL,' .
				'  voids INT(11) NOT NULL,' .
				'  total_units FLOAT NOT NULL,' .
				'  win_units FLOAT NOT NULL,' .
				'  lost_units FLOAT NOT NULL,' .
				'  yield FLOAT NOT NULL,' .
				'  last_stat TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,' .
				'  user_id INT(11) NOT NULL,' .
				'  PRIMARY KEY (id),' .
				'  KEY user_id (user_id),' .
				'  KEY user_group_units (user_id,total_units),' .
				'  KEY total_units (total_units),' .
				'  KEY last_stat_date (last_stat),' .
				'  KEY last_stat_date_total_units (total_units,last_stat),' .
				'  KEY user_date_units (user_id,last_stat,total_units)' .
				') ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;'
		    ;
		    $wpdb->query( $query_create_table_statistics );
	    }
	    
	    if( $option < 2.7 ) {
		    $query_update = 'ALTER TABLE statistics DEFAULT CHARACTER SET=utf8mb4;';
		    $wpdb->query( $query_update );
		
		    $query_update = 'CREATE INDEX user_id ON statistics (user_id);';
		    $wpdb->query( $query_update );
		
		    $query_update = 'CREATE INDEX user_group_units ON statistics (user_id, total_units);';
		    $wpdb->query( $query_update );
		
		    $query_update = 'CREATE INDEX total_units ON statistics (total_units);';
		    $wpdb->query( $query_update );
		
		    $query_update = 'CREATE INDEX last_stat_date ON statistics (last_stat);';
		    $wpdb->query( $query_update );
		
		    $query_update = 'CREATE INDEX last_stat_total_units ON statistics (total_units, last_stat);';
		    $wpdb->query( $query_update );
		
		    $query_update = 'CREATE INDEX user_date_units ON statistics (user_id, last_stat, total_units);';
		    $wpdb->query( $query_update );
	    }
	
	    if( $option < 3.0 ) {
		    $query_create_table_picks = 'CREATE TABLE IF NOT EXISTS '.$wpdb->base_prefix.'picks ('.
				'  id INT(11) NOT NULL AUTO_INCREMENT,' .
				'  tipster_id INT(11) NOT NULL,' .
				'  pick_id INT(11) NOT NULL,' .
				'  bookie_id VARCHAR(255) NOT NULL,' .
				'  sport_id INT(11) NOT NULL,' .
				'  competition_id INT(11) NOT NULL,' .
				'  pick_datetime BIGINT DEFAULT NULL,' .
				'  pick_cuote DOUBLE DEFAULT NULL,' .
				'  pick_stake DOUBLE DEFAULT NULL,' .
				'  pick_type VARCHAR(15) NOT NULL,' .
				'  pick_result VARCHAR(15) NOT NULL,' .
				'  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,' .
				'  PRIMARY KEY (id),' .
				'  KEY '.$wpdb->base_prefix.'picks_tipster_id (tipster_id),' .
				'  KEY '.$wpdb->base_prefix.'picks_pick_id (pick_id),' .
				'  KEY '.$wpdb->base_prefix.'picks_bookie_id (bookie_id),' .
				'  KEY '.$wpdb->base_prefix.'picks_tipster_id_datetime (tipster_id, pick_datetime)' .
				') ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;';
		    $wpdb->query($query_create_table_picks);
	    }
	    
	    update_option('tipster_tap_version', self::VERSION);
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
    	$image = sprintf('<img src="%1$s" class="%2$s" alt="%3$s" title="%3$s" style="%4$s" loading="lazy">', get_theme_mod('tipster_tap_default_avatar'), $class, $title, $style);

	    return $image;
    }
	
	/**
	 * @param integer $tipster Tipster id
	 * @param array $args An array with values for $condition, $date_range, $limit, $start in that order
	 *
	 * @return array|object
	 *
	 * @since 3.0
	 */
	public function get_picks($tipster, $args){
		global $wpdb;
		list($condition, $date_range, $limit, $start, $order_type) = $args;
    	
    	$where = '';
    	switch ($condition){
    		case 'finished':
    			$where = " AND ( pick_result = 'acierto' OR pick_result = 'fallo' OR pick_result = 'nulo' )";
    			break;
		    case 'standby':
		    	$where = " AND pick_result = 'pendiente'";
		    	break;
		    default: // 'all'
		    	break;
	    }
		
		if(false !== $date_range){
    		if(false !== $date_range['start'] && false === $date_range['end']) {
			    $where .= sprintf( ' AND ( pick_datetime %s %s )', $date_range['op'], $date_range['start'] );
		    }elseif (false === $date_range['start'] && false !== $date_range['end']){
			    $where .= sprintf( ' AND ( pick_datetime %s %s )', $date_range['op'], $date_range['end'] );
		    }else{
			    $where .= sprintf( ' AND ( pick_datetime BETWEEN %s AND %s )', $date_range['start'], $date_range['end'] );
		    }
	    }
	    
	    $limits = '';
	    if( false !== $limit ){
    		$limits = sprintf(' LIMIT %d', $limit);
	    }
	    if( false !== $start ){
	    	$limits = sprintf(' LIMIT %d,%d', $start, $limit);
	    }
	    
	    $order = ' ORDER BY pick_datetime ASC';
	    if(false !== $order_type){
	    	$order = sprintf(' ORDER BY pick_datetime %s', $order_type);
	    }
		
		$tipster_picks = $wpdb->get_results($wpdb->prepare('SELECT * FROM ' . $wpdb->base_prefix . "picks WHERE pick_result <> '' AND tipster_id = '%s'" . $where . $order . $limits . ';', $tipster), ARRAY_A);
		
		return $tipster_picks;
	}
	
	/**
	 * @param $tipster
	 *
	 * @since 3.0
	 */
	public function get_total_picks($tipster){
		global $wpdb;
		$total = array();
		
		$total['finalizados'] = $wpdb->get_var($wpdb->prepare( 'SELECT COUNT(*) FROM ' . $wpdb->base_prefix . "picks WHERE tipster_id = '%s' AND (pick_result = 'acierto' OR pick_result = 'fallo' OR pick_result = 'nulo') AND pick_datetime IS NOT NULL;", $tipster));
		$total['pendientes'] = $wpdb->get_var($wpdb->prepare( 'SELECT COUNT(*) FROM ' . $wpdb->base_prefix . "picks WHERE tipster_id = '%s' AND pick_result = 'pendiente' AND pick_datetime IS NOT NULL;", $tipster));
		$total_picks = (integer)$total['finalizados'] + (integer)$total['pendientes'];
		
		update_post_meta($tipster, '_tipster_total_picks_finalizados', $total['finalizados']);
		update_post_meta($tipster, '_tipster_total_picks_pendientes', $total['pendientes']);
		update_post_meta($tipster, '_tipster_total_picks', $total_picks);
	}
}
