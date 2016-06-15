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

        add_action( 'wp_insert_post', array( $this, 'save_post' ), 20, 3 );
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

            // Obtener número de apuestas acertadas, falladas y nulas pertenecientes al tipster asociado al post
	        $query_tipster_post = array(
		        'post_type' => 'post',
		        'post_status' => 'publish',
		        'post_per_page' => -1,
		        'nopaging' => true,
		        'order' => 'DESC',
		        'meta_query' => array(
			        'relation' => 'AND',
			        array(
				        'key' => '_post_tipo_publicacion',
				        'value' => 'pick',
				        'compare' => '=',
			        ),
			        array(
				        'key' => '_pick_tipster',
				        'value' => $tipster_id,
				        'compare' => '=',
			        ),
			        array(
				        'relation' => 'OR',
				        array(
					        'key' => '_pick_resultado',
					        'value' => 'acierto',
					        'compare' => '=',
				        ),
				        array(
					        'key' => '_pick_resultado',
					        'value' => 'fallo',
					        'compare' => '=',
				        ),
				        array(
					        'key' => '_pick_resultado',
					        'value' => 'nulo',
					        'compare' => '=',
				        ),
			        ),
		        )
	        );
	        $query_result = new \WP_Query($query_tipster_post);
	        $query_tipster_post_result = $query_result->get_posts();

	        $aAcertadas = 0; // apuestas acertadas
            $aFalladas = 0;  // apuestas falladas
            $aNulas = 0;     // apuestas nulas
            $totalCuotasAcertadas = 0;
            $unidadesAcertadas = 0; // Total stake apostado en el blog actual.
            $unidadesGanadas =  0; // Unidades ganadas = Ganado - Apostado. Datos actual.
            $unidadesFalladas = 0; // Las unidades falladas y las unidade sperdidas son lo mismo. Solo en el blog actual.
            $unidadesNulas = 0; // Solo en el blog actual
	        $unidadesTotales = $uiJugadas;

            foreach ($query_tipster_post_result as $tipster_post) {
                $resultado = get_post_meta($tipster_post->ID, '_pick_resultado', true);

                $stake = (float)str_replace(',', '.', get_post_meta($tipster_post->ID, '_pick_stake', true));
//                if(!is_float((float)$stake))
//                    $stake = 0;
//                else
//                    $stake = (float)$stake;

	            $unidadesTotales = $unidadesTotales + $stake;

                switch($resultado){
                    case "fallo":
                        $aFalladas += 1;

                        // Unidades falladas
                        $unidadesFalladas = $unidadesFalladas - $stake;

                        break;
                    case "nulo":
                        $aNulas += 1;

                        // Unidades Nulas
//                        $unidadesNulas = $unidadesNulas + $stake;

                        break;
                    default: // "acierto"
                        $aAcertadas += 1;

                        $cuota_x_acierto = (float)str_replace(',', '.', get_post_meta($tipster_post->ID, '_pick_cuota', true));
//                        if(!is_float((float)$cuota_x_acierto))
//                            $cuota_x_acierto = 0;
//                        else
//                            $cuota_x_acierto = (float)$cuota_x_acierto;
                        $totalCuotasAcertadas = $totalCuotasAcertadas + $cuota_x_acierto;

                        // Jugadas que ha resultado en acierto
//                        $unidadesAcertadas = $unidadesAcertadas + $stake;
                        $unidadesGanadas = $unidadesGanadas + ( ( $cuota_x_acierto * $stake ) - $stake );
                        break;
                }
            }

            // Obtener cuota media acertada
//            $average_cuota_acertada = $aAcertadas > 0 ? $totalCuotasAcertadas/$aAcertadas : 0;

            // Total apostado por el tipster = (StakeAcertado + StakeFallado + StakeNulo + total unidades iniciales jugadas).

            // Obtener yield
            // Yield = ( Beneficios / TotalApostado ) x 100
            $yield = 0;

            //se verifica si unidades totales esta vacia
            $yield = $unidadesTotales <> 0 ? ( (($unidadesGanadas+$uiGanadas)+($unidadesFalladas+$uiPerdidas))/$unidadesTotales ) * 100 : $yield;

            //modificar la base de datos con las nuevas estadisticas
            $insert_array =  array( 'corrects' => ($aAcertadas+$aiAcertadas), 'wrongs' => ($aFalladas+$aiFalladas), 'voids' =>($aNulas+$aiNulas), 'total_units' => $unidadesTotales, 'win_units' => ($unidadesGanadas+$uiGanadas), 'lost_units' => ($unidadesFalladas+$uiPerdidas), 'yield' => $yield, 'user_id' => $tipster_id);
            $wpdb->insert('statistics', $insert_array);

	        $total_picks = $this->tipster_total_picks($tipster_id, 5000);
	        $total_picks = number_format($total_picks,0,'.','');
	        update_post_meta($tipster_id, '_tipster_tips', $total_picks);
	        $rating = (floatval($total_picks) * floatval($yield)) / 100;
	        $rating = number_format($rating,2,'.','');
	        update_post_meta($tipster_id, '_tipster_rating', $rating);
	        $yield = number_format($yield,2,'.',',');
	        update_post_meta($tipster_id, '_tipster_yield', $yield);
	        $beneficio = number_format($unidadesGanadas+$uiGanadas,2,'.','');
	        update_post_meta($tipster_id, '_tipster_beneficio', $beneficio);
        }
    }

	function tipster_total_picks($tipster_id, $limit = -1){
		$count_picks = 0;
		$query = array(
			'post_type' => 'post',
			'posts_per_page' => $limit,
			'meta_query' => array(
				'relation' => 'AND',
				array(
					'key' => '_post_tipo_publicacion',
					'value' => 'pick',
					'compare' => '=',
				),
				array(
					'key' => '_pick_tipster',
					'value' => $tipster_id,
					'compare' => '=',
				)
			)
		);
		$query_result = new \WP_Query($query);
		if($query_result->have_posts()){
			$count_picks = $query_result->post_count;
		}
		wp_reset_query();

		return $count_picks;
	}
}
