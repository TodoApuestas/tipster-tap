<?php
/**
 * Tipster TAP.
 *
 * @package   Tipster_TAP_Admin
 * @author    Alain Sanchez <luka.ghost@gmail.com>
 * @license   GPL-2.0+
 * @link      http://www.linkedin.com/in/mrbrazzi/
 * @copyright 2014 Alain Sanchez
 */

namespace TipsterTAP\Backend;

use TipsterTAP\Frontend\Tipster_TAP;

/**
 * Plugin class. This class should ideally be used to work with the
 * administrative side of the WordPress site.
 *
 * If you're interested in introducing public-facing
 * functionality, then refer to `class-tipster-tap.php`
 *
 *
 * @package Tipster_TAP_Admin
 * @author  Your Name <email@example.com>
 */
class Tipster_TAP_Admin {

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Slug of the plugin screen.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_screen_hook_suffix = array();

	/**
	 * Initialize the plugin by loading admin scripts & styles and adding a
	 * settings page and menu.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {

		/*
		 * - Uncomment following lines if the admin class should only be available for super admins
		 */
		/* if( ! is_super_admin() ) {
			return;
		} */

		/*
		 * Call $plugin_slug from public plugin class.
		 *
		 */
		$plugin = Tipster_TAP::get_instance();
		$this->plugin_slug = $plugin->get_plugin_slug();

		// Load admin style sheet and JavaScript.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

		// Add the options page and menu item.
		add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );
//        add_action( 'wp_before_admin_bar_render', array( $this, 'add_plugin_adminbar' ) );

		// Add an action link pointing to the options page.
		$plugin_basename = plugin_basename( plugin_dir_path( realpath( dirname( __FILE__ ) ) ) . $this->plugin_slug . '.php' );
		add_filter( 'plugin_action_links_' . $plugin_basename, array( $this, 'add_action_links' ) );

        add_action( 'wp_insert_post', array( $this, 'save_post' ), 9999, 3 );

		$session_id = session_id();
		if(empty($session_id) && !headers_sent()) @session_start();
		if(!empty($_SESSION) && array_key_exists('TIPSTER_TAP_ERRORS', $_SESSION)){
			add_action('admin_notices', array( $this, 'display_errors' ));
		}
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		/*
		 * - Uncomment following lines if the admin class should only be available for super admins
		 */
		/* if( ! is_super_admin() ) {
			return;
		} */

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Register and enqueue admin-specific style sheet.
	 *
	 *
	 * @since     1.0.0
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_styles() {

		if ( ! isset( $this->plugin_screen_hook_suffix['root'] ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( $this->plugin_screen_hook_suffix['root'] == $screen->id ) {
			wp_enqueue_style( $this->plugin_slug .'-admin-styles', plugins_url( 'assets/css/admin.css', __FILE__ ), array(), Tipster_TAP::VERSION );
		}

	}

	/**
	 * Register and enqueue admin-specific JavaScript.
	 *
	 *
	 * @since     1.0.0
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_scripts() {

		if ( ! isset( $this->plugin_screen_hook_suffix['root'] ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( $this->plugin_screen_hook_suffix['root'] == $screen->id ) {
			wp_enqueue_script( $this->plugin_slug . '-admin-script', plugins_url( 'assets/js/admin.js', __FILE__ ), array( 'jquery' ), Tipster_TAP::VERSION );
		}

	}

	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * @since    1.0.0
	 */
	public function add_plugin_admin_menu() {

		/*
		 * Add a settings page for this plugin to the menu.
		 *
		 * NOTE:  Alternative menu locations are available via WordPress administration menu functions.
		 *
		 *        Administration Menus: http://codex.wordpress.org/Administration_Menus
		 */
        $this->plugin_screen_hook_suffix['root'] = add_menu_page(
            __( 'Tipster TAP', $this->plugin_slug ),
            __( 'Tipster TAP', $this->plugin_slug ),
            'manage_options',
            $this->plugin_slug,
            '',
            'dashicons-admin-generic'
        );

        $this->plugin_screen_hook_suffix['info'] = add_submenu_page(
            $this->plugin_slug,
            __('Tipster TAP', $this->plugin_slug),
            __('Informacion', $this->plugin_slug),
            'manage_options',
            $this->plugin_slug,
            array( $this, 'display_plugin_admin_page' )
        );

        $this->plugin_screen_hook_suffix['upgrade'] = add_submenu_page(
            $this->plugin_slug,
            __('Tipster TAP :: Update Picks', $this->plugin_slug),
            __('Update Picks', $this->plugin_slug),
            'manage_options',
            $this->plugin_slug.'/update-picks-information',
            array( $this, 'update_picks_info_page' )
        );
	}

    public function add_plugin_adminbar(){
        global $wp_admin_bar;

        $wp_admin_bar->add_menu(
            array(
                'parent' => null,
                'id' => 'tipster_tap_plugin',
                'title' => __( 'Tipster TAP', $this->plugin_slug ),
                'href' => admin_url( 'admin.php?page='.$this->plugin_slug )
            ),
            array(
                'parent' => $this->plugin_slug,
                'id' => 'tipster_tap_plugin_informacion',
                'title' => __( 'Informacion', $this->plugin_slug ),
                'href' => admin_url( 'admin.php?page='.$this->plugin_slug )
            )
        );
    }

	/**
	 * Render the settings page for this plugin.
	 *
	 * @since    1.0.0
	 */
	public function display_plugin_admin_page() {
		include_once( 'views/admin.php' );
	}

    /**
     * Render the upgrade picks information page
     *
     * @since    1.1.0
     */
    public function update_picks_info_page(){
        include_once ( 'views/update-picks-information.php');
    }

	/**
	 * Add settings action link to the plugins page.
	 *
	 * @since    1.0.0
	 */
	public function add_action_links( $links ) {

		return array_merge(
			array(
				'settings' => '<a href="' . admin_url( 'admin.php?page=' . $this->plugin_slug ) . '">' . __( 'Informacion', $this->plugin_slug ) . '</a>'
			),
			$links
		);

	}

    /**
     * Calculates the statistics values
     *
     * @since    1.1.3
     */
    public function save_post($post_id, $post = false, $update = false){
        global $wpdb;
	    ini_set('max_execution_time', 0);
	    ini_set('max_input_time', -1);

	    $tipo_publicacion = get_post_meta($post_id, '_post_tipo_publicacion', true);

        if(false === wp_is_post_revision($post) && strcmp($post->post_type, 'post') === 0 && strcmp($post->post_status, 'publish') === 0 && strcmp($tipo_publicacion, 'post') === 0)
        {
            delete_post_meta($post_id, '_pick_pronostico_pago');
            delete_post_meta($post_id, '_pick_live');
            delete_post_meta($post_id, '_pick_evento');
            delete_post_meta($post_id, '_pick_fecha_evento');
            delete_post_meta($post_id, '_pick_hora_evento');
            delete_post_meta($post_id, '_pick_pronostico');
            delete_post_meta($post_id, '_pick_cuota');
            delete_post_meta($post_id, '_pick_casa_apuesta');
            delete_post_meta($post_id, '_pick_tipo_apuesta');
            delete_post_meta($post_id, '_pick_tipster');
            delete_post_meta($post_id, '_pick_competicion');
            delete_post_meta($post_id, '_pick_deporte');
            delete_post_meta($post_id, '_pick_resultado');
            return;
        }

        $picks_limit = (int)get_theme_mod('tipster_tap_limit_total_picks');
        $resultado = get_post_meta($post_id, '_pick_resultado', true);
        if(false === wp_is_post_revision($post) && strcmp($post->post_type, 'post') === 0 && strcmp($tipo_publicacion, 'pick') === 0
           && (strcmp($resultado, 'acierto') === 0 || strcmp($resultado, 'fallo') === 0 || strcmp($resultado, 'nulo') === 0 )){
            // ai apuestas iniciales - entendiendo apuestas como el numero de veces que ha apostado.
            // ui unidades iniciales - entendiendo unidades como el valor en stake apostado.
            $aiAcertadas = 0;
            $aiFalladas = 0;
            $aiNulas = 0;
            $uiJugadas = 0;
            $uiGanadas = 0;
            $uiPerdidas = 0;

            $tipster_id = get_post_meta($post_id, '_pick_tipster', true);

            $datos_iniciales = (int)get_post_meta($tipster_id, '_tipster_incluir_datos_iniciales', true);

            // Si han sido habilitados los datos iniciales los usamos.
            if($datos_iniciales){
                //Veces que ha apostado saliendo ganador.
                $aiAcertadas = (int)get_post_meta($tipster_id, '_tipster_aciertos_iniciales', true);
                if(!is_int((int)$aiAcertadas))
                    $aiAcertadas = 0;
                else
                    $aiAcertadas = (int)$aiAcertadas;

                //Veces que ha apostado y ha perdido.
                $aiFalladas =  (int)get_post_meta($tipster_id, '_tipster_fallos_iniciales', true);
                if(!is_int((int)$aiFalladas))
                    $aiFalladas = 0;
                else
                    $aiFalladas = (int)$aiFalladas;

                //Veces que ha apostado y el resultado ha sido nulo.
                $aiNulas =  (int)get_post_meta($tipster_id, '_tipster_nulos_iniciales', true);
                if(!is_int((int)$aiNulas))
                    $aiNulas = 0;
                else
                    $aiNulas = (int)$aiNulas;

                //Unidades totales que ha jugado
                $uiJugadas =  (float)str_replace(',', '.', get_post_meta($tipster_id, '_tipster_unidades_jugadas_iniciales', true));
                if(!is_float((float)$uiJugadas))
                    $uiJugadas = 0;
                else
                    $uiJugadas = (float)$uiJugadas;

                //Unidades totales que ha gando
                $uiGanadas =  (float)str_replace(',', '.', get_post_meta($tipster_id, '_tipster_unidades_ganadas_iniciales', true));
                if(!is_float((float)$uiGanadas))
                    $uiGanadas = 0;
                else
                    $uiGanadas = (float)$uiGanadas;

                //Unidades totales que ha perdido
                $uiPerdidas =  (float)str_replace(',', '.', get_post_meta($tipster_id, '_tipster_unidades_perdidas_iniciales', true));
                if(!is_float((float)$uiPerdidas))
                    $uiPerdidas = 0;
                else
                    $uiPerdidas = (float)$uiPerdidas;
            }

            $tipster_post_result = apply_filters('tipster_tap_get_tipster_picks', $tipster_id, $picks_limit);

	        $aAcertadas = 0; // apuestas acertadas
            $aFalladas = 0;  // apuestas falladas
            $aNulas = 0;     // apuestas nulas
            $unidadesGanadas =  0; // Unidades ganadas = Ganado - Apostado. Datos actual.
            $unidadesFalladas = 0; // Las unidades falladas y las unidades perdidas son lo mismo. Solo en el blog actual.
            $unidadesTotales = $uiJugadas;

            foreach ($tipster_post_result as $tipster_post) {
                $resultado = get_post_meta($tipster_post->ID, '_pick_resultado', true);

                $stake = (float)str_replace(',', '.', get_post_meta($tipster_post->ID, '_pick_stake', true));

	            $unidadesTotales = $unidadesTotales + $stake;

                switch($resultado){
                    case "fallo":
                        $aFalladas += 1;
                        $unidadesFalladas = $unidadesFalladas - $stake;
                        break;
                    case "nulo":
                        $aNulas += 1;
                        break;
                    default: // "acierto"
                        $aAcertadas += 1;
                        $cuota_x_acierto = (float)str_replace(',', '.', get_post_meta($tipster_post->ID, '_pick_cuota', true));
                        $unidadesGanadas = $unidadesGanadas + ( ( $cuota_x_acierto * $stake ) - $stake );
                        break;
                }
            }

            $ganancias = ($unidadesGanadas+$uiGanadas)+($unidadesFalladas+$uiPerdidas);

            // Obtener yield
            // Yield = ( Beneficios / TotalApostado ) x 100
            $yield = 0;
            //se verifica si unidades totales esta vacia
            $yield = $unidadesTotales <> 0 ? ( $ganancias/$unidadesTotales ) * 100 : $yield;

            //modificar la base de datos con las nuevas estadisticas
	        $corrects = $aAcertadas + $aiAcertadas;
	        $wrongs = $aFalladas + $aiFalladas;
	        $voids = $aNulas + $aiNulas;
	        $win_units = $unidadesGanadas + $uiGanadas;
	        $lost_units = $unidadesFalladas + $uiPerdidas;
	        $insert_array =  array( 'corrects' => $corrects, 'wrongs' => $wrongs, 'voids' => $voids, 'total_units' => $unidadesTotales, 'win_units' => $win_units, 'lost_units' => $lost_units, 'yield' => $yield, 'user_id' => $tipster_id);
            $wpdb->insert('statistics', $insert_array);

	        $total_picks = get_post_meta($tipster_id, '_tipster_total_picks_finalizados', true);
	        $rating = (floatval($total_picks) * floatval($yield)) / 100;
	        $last_stats = array(
		        'yield'       => $yield,
		        'beneficio'   => $ganancias,
		        'rating'      => $rating,
		        'corrects'    => $corrects,
		        'wrongs'      => $wrongs,
		        'voids'       => $voids,
		        'total_units' => $unidadesTotales,
		        'win_units'   => $win_units,
		        'lost_units'  => $lost_units
	        );
	        update_post_meta($tipster_id, '_tipster_last_statistics', $last_stats);

	        $this->tipster_yield_history($tipster_id);
	        $this->tipster_graphic_statistics($tipster_id);
        }

	    ini_restore('max_input_time');
	    ini_restore('max_execution_time');
    }

	public function count_total_tipster_picks($tipster, $limit = -1)
	{
		$total_picks = 0;
		$query = array(
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'posts_per_page' => $limit,
			'order'          => 'DESC',
			'meta_query'     => array(
				'relation' => 'AND',
				array(
					'key'     => '_post_tipo_publicacion',
					'value'   => 'pick',
					'compare' => '=',
				),
				array(
					'key'     => '_pick_tipster',
					'value'   => $tipster,
					'compare' => '=',
				)
			),
			'cache_results'          => false,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		);

		$query_result = new \WP_Query($query);
		if($query_result->have_posts()){
			$total_picks = $query_result->post_count;
		}

		update_post_meta($tipster, '_tipster_total_picks', $total_picks);
		return $total_picks;
	}

	public function tipster_last_statistics($tipster){
		global $wpdb;

		$query_statistics = "SELECT * FROM statistics where user_id = ".$tipster." ORDER BY  last_stat DESC LIMIT 1";
		$statistics = $wpdb->get_row($query_statistics, OBJECT);

		update_post_meta($tipster, '_tipster_last_statistics', $statistics);
		return $statistics;
	}

	function tipster_graphic_statistics($tipster){
		global $wpdb;

		$months = get_theme_mod( 'tipster_tap_limit_statistics', 6 );
		$period = new \DateInterval('P'.$months.'M1D');
		$date_end = new \DateTime();
		$dateStr = $date_end->format('Y-m-d');
		$date_start = new \DateTime($dateStr);
		$date_start->sub($period);

		$statistics_query  = sprintf("SELECT * FROM statistics WHERE user_id = %s AND last_stat BETWEEN '%s' AND '%s' ORDER BY last_stat DESC;", $tipster, $date_start->format('Y-m-d H:i:s'), $date_end->format('Y-m-d H:i:s'));
		$statistics_result = $wpdb->get_results( $statistics_query, ARRAY_A );
		$statistics_result = array_reverse( $statistics_result );
//	$total_yields = count($statistics_result);
		$aciertos  = array();
		$fallos    = array();
		$nulos     = array();
		$ganancias = array();
		$yields    = array();
		foreach ( $statistics_result as $statistic ) {
			$date_time        = new \DateTime( $statistic[ 'last_stat' ] );
			$fecha            = $date_time->format( "Y-m-d" );
			$acierto          = intval( $statistic[ 'corrects' ] );
			$fallo            = intval( $statistic[ 'wrongs' ] );
			$nulo             = intval( $statistic[ 'voids' ] );
			$unidades_ganadas = floatval( $statistic[ 'win_units' ] );
			$unidades_perdidas = floatval( $statistic[ 'lost_units' ] );
			$ganancia = $unidades_ganadas + $unidades_perdidas;
			$yield            = floatval( $statistic[ 'yield' ] );
			$aciertos[] = array( $fecha, (int) $acierto );
			$fallos[] = array( $fecha, (int) $fallo );
			$nulos[] = array( $fecha, (int) $nulo );
			$ganancias[] = array( $fecha, number_format( $ganancia, 2, '.', '' ) );
			$yields[] = array( $fecha, number_format( $yield, 2, '.', '' ) );
		}
		$serie_aciertos  = array( "data" => $aciertos, "label" => __( 'Aciertos', 'epic' ), "lines" => array( "lineWidth" => 1 ), "shadowSize" => 0, "color" => 0 );
		$serie_fallos    = array( "data" => $fallos, "label" => __( 'Fallos', 'epic' ), "lines" => array( "lineWidth" => 1 ), "shadowSize" => 0, "color" => 1 );
		$serie_nulos     = array( "data" => $nulos, "label" => __( 'Voids', 'epic' ), "lines" => array( "lineWidth" => 1 ), "shadowSize" => 0, "color" => 2 );
		$serie_ganancias = array( "data" => $ganancias, "label" => __( 'U. ganadas', 'epic' ), "lines" => array( "lineWidth" => 1 ), "shadowSize" => 0, "color" => 3 );
		$serie_yields    = array( "data" => $yields, "label" => __( 'Yield', 'epic' ), "lines" => array( "lineWidth" => 1 ), "shadowSize" => 0, "color" => 4 );
		$response        = array( "aciertos" => $serie_aciertos, "fallos" => $serie_fallos, "nulos" => $serie_nulos, "ganancias" => $serie_ganancias, "yields" => $serie_yields );

		update_post_meta($tipster, '_tipster_statistics_graphic', $response);
	}

	public function tipster_yield_history($tipster){
		global $wpdb;

		$months = (int)get_theme_mod( 'tipster_tap_limit_statistics');
		$now = new \DateTime();
		$dateStr = $now->format('Y-m');
		$date_end = new \DateTime($dateStr);
		$date_end->add(new \DateInterval('P1M'));
		$dateStr = $date_end->format('Y-m');
		$date_start = new \DateTime($dateStr);
		$date_start->sub(new \DateInterval('P'.$months.'M'));

		$statistics_query = sprintf("SELECT last_stat, yield AS yield FROM statistics WHERE user_id = %s AND last_stat BETWEEN '%s' AND '%s' GROUP BY yield ORDER BY last_stat DESC;", $tipster, $date_start->format('Y-m-d H:i:s'), $date_end->format('Y-m-d H:i:s'));
		$statistics_result = $wpdb->get_results( $statistics_query, ARRAY_A );
		$yield_by_tipster = array();
		foreach ( $statistics_result as $statistic ) {
			$last_stat = $statistic['last_stat'];
			$yield = $statistic['yield'];
			$fecha = new \DateTime($last_stat);
			$fecha_formated = $fecha->format('Ym');

			if(!array_key_exists($fecha_formated, $yield_by_tipster)){
				$yield_by_tipster[$fecha_formated] = array(
					'fecha' => $fecha,
					'yield' => $yield
				);
				continue;
			}

			if(array_key_exists($fecha_formated, $yield_by_tipster) && $fecha > $yield_by_tipster[$fecha_formated]['fecha']){
				$yield_by_tipster[$fecha_formated]['fecha'] = $fecha;
				$yield_by_tipster[$fecha_formated]['yield'] = $yield;
			}
		}

		update_post_meta($tipster, '_tipster_yield_history', $yield_by_tipster);
	}

	public function display_errors(){
		$errors = $_SESSION['TIPSTER_TAP_ERRORS'];

		$error_notice = '<div class="notice notice-error"><ul>';
		foreach ( $errors as $error ) {
			$error_notice .= sprintf('<li>%s</li>', $error);
		}
		$error_notice .= '</ul></div>';

		unset($_SESSION['TIPSTER_TAP_ERRORS']);
		echo $error_notice;
	}
}
