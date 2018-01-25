<?php
/**
 * Tipster TAP.
 *
 * @package   TipsterTapAdmin
 * @author    Alain Sanchez <luka.ghost@gmail.com>
 * @license   GPL-2.0+
 * @link      http://www.linkedin.com/in/mrbrazzi/
 * @copyright 2014 Alain Sanchez
 */

namespace TipsterTAP\Backend;

use TipsterTAP\Frontend\TipsterTap;

/**
 * Plugin class. This class should ideally be used to work with the
 * administrative side of the WordPress site.
 *
 * If you're interested in introducing public-facing
 * functionality, then refer to `class-tipster-tap.php`
 *
 *
 * @package TipsterTapAdmin
 * @author  Alain Sanchez <luka.ghost@gmail.com>
 */
class TipsterTapAdmin {

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
		$plugin = TipsterTap::get_instance();
		$this->plugin_slug = $plugin->get_plugin_slug();

		// Load admin style sheet and JavaScript.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

		// Add the options page and menu item.
		add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );

		// Add an action link pointing to the options page.
		$plugin_basename = plugin_basename( plugin_dir_path( realpath( __DIR__ ) ) . $this->plugin_slug . '.php' );
		add_filter( 'plugin_action_links_' . $plugin_basename, array( $this, 'add_action_links' ) );

		add_filter( 'manage_edit-post_sortable_columns', array( $this, 'manage_posts_pick_manage_sortable_columns' ) );
        add_action( 'manage_posts_custom_column', array( $this, 'manage_wp_posts_pick_manage_posts_custom_column' ), 10, 2 );
        add_action( 'pre_get_posts', array( $this, 'manage_wp_posts_pick_pre_get_posts' ), 1 );
        add_action( 'bulk_edit_custom_box', array( $this, 'quick_edit_custom_box_pick_result' ), 10, 2 );
        add_action( 'quick_edit_custom_box', array( $this, 'quick_edit_custom_box_pick_result' ), 10, 2 );
        add_action( 'admin_print_scripts-edit.php', array( $this, 'manage_wp_posts_pick_enqueue_admin_scripts' ) );
        add_action( 'wp_ajax_manage_wp_posts_pick_using_bulk_quick_save_bulk_edit', array( $this, 'manage_wp_posts_pick_using_bulk_quick_save_bulk_edit' ) );
        add_action( 'save_post', array( $this, 'save_pick_result_meta' ), 9999, 3 );
		add_action( 'wp_insert_post', array( $this, 'save_pick' ), 9998, 3 );
//        add_action( 'wp_insert_post', array( $this, 'save_pick_statistics' ), 9999, 3 );
		add_action( 'before_delete_post', array( $this, 'delete_pick' ), 9999, 1 );
		
		$session_id = session_id();
		if(empty($session_id) && !headers_sent()) @session_start();
		if(!empty($_SESSION) && array_key_exists('TIPSTER_TAP_ERRORS', $_SESSION)){
			add_action( 'admin_notices', array( $this, 'display_errors' ));
		}
		
		add_action( 'tipster_tap_update_statistics', array( $this, 'update_statistics' ), 10, 2 );
		add_action( 'tipster_tap_update_statistics_by_month', array( $this, 'update_statistics_by_month' ) );
		add_action( 'tipster_tap_update_yield_history', array( $this, 'update_yield_history' ) );
		add_action( 'tipster_tap_update_graphic_statistics', array( $this, 'update_graphic_statistics' ) );
		
		add_action( 'tipster_tap_execute_pick_migration', array( $this, 'execute_pick_migration' ) );
		add_action( 'tipster_tap_update_tipster_metas', array( $this, 'update_picks_date' ) );
		add_action( 'tipster_tap_update_tipster_metas', array( $this, 'update_tipster_metas' ) );
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
		if ( null === self::$instance ) {
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
		if ( $this->plugin_screen_hook_suffix['root'] === $screen->id ) {
			wp_enqueue_style( $this->plugin_slug .'-admin-styles', plugins_url( 'assets/css/admin.css', __FILE__ ), array(), TipsterTap::VERSION );
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
		if ( $this->plugin_screen_hook_suffix['root'] === $screen->id ) {
//			wp_enqueue_script( $this->plugin_slug . '-admin-script', plugins_url( 'assets/js/admin.js', __FILE__ ), array( 'jquery' ), TipsterTap::VERSION, true );
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

        $this->plugin_screen_hook_suffix['manage_tipsters'] = add_submenu_page(
			$this->plugin_slug,
			__('Tipster TAP', $this->plugin_slug),
			__('Manage Tipsters', $this->plugin_slug),
			'manage_options',
			$this->plugin_slug . '/manage-tipsters',
			array( $this, 'manage_tipsters_page' )
		);
		
		$this->plugin_screen_hook_suffix['manage_picks'] = add_submenu_page(
			$this->plugin_slug,
			__('Tipster TAP', $this->plugin_slug),
			__('Manage Picks', $this->plugin_slug),
			'manage_options',
			$this->plugin_slug . '/manage-picks',
			array( $this, 'manage_picks_page' )
		);
	}

    /**
	 * Render the settings page for this plugin.
	 *
	 * @since    1.0.0
	 */
	public function display_plugin_admin_page() {
		include_once 'views/admin.php';
	}

    /**
     * Render the manage picks page
     *
     * @since    3.0
     */
    public function manage_picks_page(){
        include_once 'views/manage-picks.php';
    }
	
	/**
	 * Render the manage tipster's meta page
	 *
	 * @since    3.0
	 */
	public function manage_tipsters_page(){
		include_once 'views/manage-tipsters.php';
	}

	/**
	 * Add settings action link to the plugins page.
	 *
	 * @since    1.0.0
	 */
	public function add_action_links( $links ) {

		return array_merge(
			array(
				'settings' => '<a href="' . admin_url( 'admin.php?page=' . $this->plugin_slug ) . '">' . __( 'Information', $this->plugin_slug ) . '</a>'
			),
			$links
		);

	}

    public function manage_posts_pick_manage_sortable_columns( $sortable_columns ){
        $sortable_columns[ '_pick_resultado' ] = '_pick_resultado';
        return $sortable_columns;
    }
    
    public function manage_wp_posts_pick_manage_posts_custom_column( $column_name, $post_id ){
        $tipo_publicacion = get_post_meta( $post_id, '_post_tipo_publicacion', true );

        if ( strcmp( $tipo_publicacion, 'pick' ) === 0 ) {
            echo '<div id="pick_resultado-' . $post_id . '" style="display:none;">' . get_post_meta( $post_id, $column_name, true ) . '</div>';
        }
    }

    public function manage_wp_posts_pick_pre_get_posts( $query ){
        if ( $query->is_main_query() && ( $orderby = $query->get( 'orderby' ) ) ) {
            switch( $orderby ) {
                case '_pick_resultado':
                    $query->set( 'meta_key', '_pick_resultado' );
                    $query->set( 'orderby', 'meta_value' );
                    break;
            }
        }
    }
    
    /**
     * Add _pick_resultado meta field in quick edit box
     *
     * @param $column_name
     * @param $post_type
     *
     * @since    2.6
     */
	public function quick_edit_custom_box_pick_result($column_name, $post_type){
		if(strcmp($post_type, 'post') === 0 && strcmp($column_name, '_pick_resultado') === 0 ) {
			static $printNonce = true;
			if ( $printNonce ) {
				$printNonce = false;
				wp_nonce_field( plugin_basename( __FILE__ ), $this->plugin_slug.'_edit'.$column_name.'_nonce' );
			} ?>
			<fieldset class="inline-edit-col-right">
				<div class="inline-edit-col column-<?php echo $column_name; ?>">
					<label class="inline-edit-pick-result alignleft">
						<span class="title"><?php _e('Resultado', $this->plugin_slug);?></span>
                        <span class="input-text-wrap">
                            <select name="<?php echo $column_name;?>">
                                <option value="pendiente">
                                    <?php _e('Pendiente', $this->plugin_slug);?>
                                </option>
                                <option value="acierto">
                                    <?php _e('Acierto', $this->plugin_slug);?>
                                </option>
                                <option value="fallo">
                                    <?php _e('Fallo', $this->plugin_slug);?>
                                </option>
                                <option value="nulo">
                                    <?php _e('Nulo', $this->plugin_slug);?>
                                </option>
                            </select>
                        </span>
					</label>
				</div>
			</fieldset>
			<?php
		}
	}

	public function manage_wp_posts_pick_enqueue_admin_scripts(){
        wp_enqueue_script( 'manage-wp-posts-using-bulk-quick-edit', trailingslashit( plugin_dir_url( __FILE__ ) ) . 'assets/js/bulk_quick_edit.js', array( 'jquery', 'inline-edit-post' ), TipsterTap::VERSION, true );
    }
    
    public function manage_wp_posts_pick_using_bulk_quick_save_bulk_edit(){
        // we need the post IDs
        $post_ids = ( isset( $_POST[ 'post_ids' ] ) && !empty( $_POST[ 'post_ids' ] ) ) ? $_POST[ 'post_ids' ] : NULL;

        // if we have post IDs
        if ( ! empty( $post_ids ) && is_array( $post_ids ) ) {

            if ( isset( $_POST[ '_pick_resultado' ] ) && !empty( $_POST[ '_pick_resultado' ] ) ) {

                foreach ( $post_ids as $post_id ) {
                    $tipo_publicacion = get_post_meta( $post_id, '_post_tipo_publicacion', true );
                    
                    if ( strcmp( $tipo_publicacion, 'pick' ) !== 0 ) {
                        continue;
                    }
	                update_post_meta( $post_id, '_pick_resultado', $_POST[ '_pick_resultado' ] );
	                $this->save_pick($post_id);
                }
                
            }
            
        }
        
    }
	
	/**
	 * Save _pick_resultado meta from quick edit box
	 *
	 * @param $post_ID
	 * @param $post
	 * @param $update
	 *
	 * @return mixed
	 *
	 * @since    2.6
     * @updated  3.0
	 */
	public function save_pick_result_meta($post_ID, $post, $update){
        // pointless if $_POST is empty (this happens on bulk edit)
        if ( empty( $_POST ) )
            return $post_ID;

        // verify quick edit nonce
        if ( isset( $_POST[ '_inline_edit' ] ) && ! wp_verify_nonce( $_POST[ '_inline_edit' ], 'inlineeditnonce' ) )
            return $post_ID;

        // don't save for autosave
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
            return $post_ID;

        // dont save for revisions
        if ( isset( $post->post_type ) && $post->post_type === 'revision' )
            return $post_ID;

        if ( !current_user_can( 'edit_post', $post_ID ) ) {
            return $post_ID;
        }
        
        $tipo_publicacion = get_post_meta($post_ID, '_post_tipo_publicacion', true);
        if(strcmp($post->post_type, 'post') !== 0 || strcmp($tipo_publicacion, 'pick') !== 0 ) {
			return $post_ID;
		}
		
		$_POST += array("{$this->plugin_slug}_edit_pick_resultado_nonce" => '');
		if ( !wp_verify_nonce( $_POST["{$this->plugin_slug}_edit_pick_resultado_nonce"], plugin_basename( __FILE__ ) ) ) {
			return $post_ID;
		}
		
		if ( true === $update && isset( $_REQUEST['_pick_resultado'] ) ) {
		    $pick_resultado = $_REQUEST['_pick_resultado'];
			update_post_meta( $post_ID, '_pick_resultado', $pick_resultado );
			$this->save_pick($post_ID, $post, $update);
		}
		
		return $post_ID;
	}
	
	/**
	 * @param $post_ID
	 * @param null $post
	 * @param bool $update
	 *
     * @return mixed
     *
	 * @since    3.0
	 */
	public function save_pick($post_ID, $post = null, $update = true){
		global $wpdb;
	    try {
		    $tipo_publicacion = get_post_meta($post_ID, '_post_tipo_publicacion', true);
		    
			// se comprueba si el tipo de publicacion no es un "pick"
            if(false === wp_is_post_revision($post) && strcmp($post->post_type, 'post') === 0
               && strcmp($post->post_status, 'publish') === 0 && strcmp($tipo_publicacion, 'post') === 0) {
				delete_post_meta($post_ID, '_pick_pronostico_pago');
				delete_post_meta($post_ID, '_pick_live');
				delete_post_meta($post_ID, '_pick_evento');
				delete_post_meta($post_ID, '_pick_fecha_evento');
				delete_post_meta($post_ID, '_pick_hora_evento');
				delete_post_meta($post_ID, '_pick_pronostico');
				delete_post_meta($post_ID, '_pick_cuota');
				delete_post_meta($post_ID, '_pick_casa_apuesta');
				delete_post_meta($post_ID, '_pick_tipo_apuesta');
				delete_post_meta($post_ID, '_pick_tipster');
				delete_post_meta($post_ID, '_pick_competicion');
				delete_post_meta($post_ID, '_pick_deporte');
				delete_post_meta($post_ID, '_pick_resultado');
	
	            // si el tipo de publicación es pick, entonces se busca si existe en la tabla wp_picks algún registro con pick_id = post_ID y se elimina
				$pick_exist = $wpdb->get_var($wpdb->prepare('SELECT COUNT(*) FROM '.$wpdb->base_prefix.'picks WHERE pick_id = %d', array($post_ID)));
				if( null !== $pick_exist && true === (boolean)$pick_exist ) {
					$record = $wpdb->get_row($wpdb->prepare('SELECT * FROM '.$wpdb->base_prefix.'picks WHERE pick_id = %d', array($post_ID)), ARRAY_A);
				 
					$wpdb->delete( $wpdb->base_prefix . 'picks', array( 'pick_id' => $post_ID ) );
					
					// se actualizan las estadisticas pertenecientes al tipster asociado al pick
					do_action( 'tipster_tap_get_total_picks', $record['tipster_id'] );
                    do_action( 'tipster_tap_update_statistics_by_month', $record['tipster_id'] );
				}
				
				return $post_ID;
			}
		
		    $tipster_ID = get_post_meta( $post_ID, '_pick_tipster', true );
			$resultado = get_post_meta( $post_ID, '_pick_resultado', true );
			
			$pick_date     = get_post_meta( $post_ID, '_pick_fecha_evento', true );
			$pick_time     = get_post_meta( $post_ID, '_pick_hora_evento', true );
			$pick_datetime = new \DateTime( sprintf( '%s %s', $pick_date, $pick_time ) );
			
			$args = array(
				'tipster_id'     => (integer) get_post_meta( $post_ID, '_pick_tipster', true ),
				'pick_id'        => $post_ID,
				'bookie_id'      => get_post_meta( $post_ID, '_pick_casa_apuesta', true ),
				'sport_id'       => (integer) get_post_meta( $post_ID, '_pick_deporte', true ),
				'competition_id' => (integer) get_post_meta( $post_ID, '_pick_competicion', true ),
				'pick_datetime'  => $pick_datetime instanceof \DateTime ? $pick_datetime->getTimestamp() : null,
				'pick_cuote'     => (double) get_post_meta( $post_ID, '_pick_cuota', true ),
				'pick_stake'     => (double) get_post_meta( $post_ID, '_pick_stake', true ),
				'pick_type'      => get_post_meta( $post_ID, '_pick_tipo_apuesta', true ),
				'pick_result'    => $resultado,
			);
			
			$pick_exist = $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . $wpdb->base_prefix . 'picks WHERE pick_id = %d', array( $post_ID ) ) );
			if ( null !== $pick_exist && true === (boolean)$pick_exist ) {
				$where = array( 'pick_id' => $post_ID );
				$this->pick_insert_or_update( $args, $where );
			} else {
				$this->pick_insert_or_update( $args );
			}
		
			// solo se ejecuta actualizacion de estadisticas si el resultado del pick es acierto, fallo o nulo
		    if(false === wp_is_post_revision($post) && strcmp($post->post_type, 'post') === 0 && strcmp($tipo_publicacion, 'pick') === 0
		       && (strcmp($resultado, 'acierto') === 0 || strcmp($resultado, 'fallo') === 0 || strcmp($resultado, 'nulo') === 0 )) {
			    do_action( 'tipster_tap_get_total_picks', $tipster_ID );
			    do_action( 'tipster_tap_update_statistics_by_month', $tipster_ID );
		    }
		}catch (\Exception $e){
			$_SESSION['TIPSTER_TAP_ERRORS'][] = $e->getMessage();
        }
		
		return $post_ID;
    }
	
	/**
     * Delete a pick when the associated post is going to be deleted
     *
	 * @param $post_ID
	 *
	 * @since 3.0
	 */
	public function delete_pick($post_ID){
		global $wpdb;
		$post = get_post($post_ID);
		$tipo_publicacion = get_post_meta($post_ID, '_post_tipo_publicacion', true);
		if(strcmp($post->post_type, 'post') === 0 && strcmp($tipo_publicacion, 'pick') === 0) {
			$pick_exist = $wpdb->get_var($wpdb->prepare('SELECT COUNT(*) FROM '.$wpdb->base_prefix.'picks WHERE pick_id = %d', array($post_ID)));
			if( null !== $pick_exist && true === (boolean)$pick_exist ) {
				$record = $wpdb->get_row($wpdb->prepare('SELECT * FROM '.$wpdb->base_prefix.'picks WHERE pick_id = %d', array($post_ID)), ARRAY_A);
				
				$wpdb->delete( $wpdb->base_prefix . 'picks', array( 'pick_id' => $post_ID ) );
				
				do_action( 'tipster_tap_get_total_picks', $record['tipster_id'] );
				do_action( 'tipster_tap_update_statistics_by_month', $record['tipster_id'] );
			}
		}
	}
	
	/**
	 * Calculate and save full tipster's statistics
	 *
	 * @param $tipster_id
	 * @param array $args
	 *
	 * @since 3.0
	 *
	 */
    public function update_statistics($tipster_id, $args){
	    ini_set('max_execution_time', 0);
	    ini_set('max_input_time', -1);
	
	    // ai apuestas iniciales - entendiendo apuestas como el numero de veces que ha apostado.
	    // ui unidades iniciales - entendiendo unidades como el valor en stake apostado.
	    $aiAcertadas = 0;
	    $aiFalladas = 0;
	    $aiNulas = 0;
	    $uiJugadas = 0;
	    $uiGanadas = 0;
	    $uiPerdidas = 0;
	
	    $datos_iniciales = (boolean)get_post_meta($tipster_id, '_tipster_incluir_datos_iniciales', true);
	
	    // Si han sido habilitados los datos iniciales los usamos.
	    if(true === $datos_iniciales){
		    //Veces que ha apostado y el resultado ha sido acierto.
		    $aiAcertadas = (int)get_post_meta($tipster_id, '_tipster_aciertos_iniciales', true);
		    if(!is_int($aiAcertadas)) {
			    $aiAcertadas = 0;
		    }
		
		    //Veces que ha apostado y el resultado ha sido fallo.
		    $aiFalladas =  (int)get_post_meta($tipster_id, '_tipster_fallos_iniciales', true);
		    if(!is_int($aiFalladas)) {
			    $aiFalladas = 0;
		    }
		
		    //Veces que ha apostado y el resultado ha sido nulo.
		    $aiNulas =  (int)get_post_meta($tipster_id, '_tipster_nulos_iniciales', true);
		    if(!is_int($aiNulas)) {
			    $aiNulas = 0;
		    }
		
		    //Unidades totales que ha jugado
		    $uiJugadas =  (double)get_post_meta($tipster_id, '_tipster_unidades_jugadas_iniciales', true);
		    if(!is_float($uiJugadas)) {
			    $uiJugadas = 0;
		    }
		
		    //Unidades totales que ha ganado
		    $uiGanadas =  (double)get_post_meta($tipster_id, '_tipster_unidades_ganadas_iniciales', true);
		    if(!is_float($uiGanadas)) {
			    $uiGanadas = 0;
		    }
		
		    //Unidades totales que ha perdido
		    $uiPerdidas =  (double)get_post_meta($tipster_id, '_tipster_unidades_perdidas_iniciales', true);
		    if(!is_float($uiPerdidas)) {
			    $uiPerdidas = 0;
		    }
	    }
	    
	    $tipster_picks = apply_filters('tipster_tap_get_picks', $tipster_id, $args);
	
	    $aAcertadas = 0; // apuestas acertadas
	    $aFalladas = 0;  // apuestas falladas
	    $aNulas = 0;     // apuestas nulas
	    $unidadesGanadas =  0; // Unidades ganadas = Ganado - Apostado.
	    $unidadesPerdidas = 0; // Las unidades falladas o unidades perdidas.
	    $unidadesApostadas = $uiJugadas;
	
	    foreach ($tipster_picks as $pick) {
		    $resultado = $pick['pick_result'];
		    $stake = (double)$pick['pick_stake'];
		    
		    $unidadesApostadas += $stake;
		
		    switch($resultado){
			    case "fallo":
				    $aFalladas++;
				    $unidadesPerdidas -= $stake;
				    break;
			    case "nulo":
				    $aNulas++;
				    break;
			    default: // "acierto"
				    $aAcertadas++;
				    $cuota_x_acierto = (double)$pick['pick_cuote'];
				    $unidadesGanadas += ( ( $cuota_x_acierto * $stake ) - $stake );
				    break;
		    }
	    }
	
	    $ganancias = ( $unidadesGanadas + $uiGanadas ) + ( $unidadesPerdidas + $uiPerdidas );
	
	    // Obtener yield
	    // Yield = ( Beneficios / TotalApostado ) x 100
	    // se verifica si unidades totales esta vacia
	    $yield = $unidadesApostadas !== 0 ? ( $ganancias / $unidadesApostadas ) * 100 : 0;
	
	    // datos de totales de estadistica
	    $aciertos = $aAcertadas + $aiAcertadas;
	    $fallos = $aFalladas + $aiFalladas;
	    $nulos = $aNulas + $aiNulas;
	    $unidadesGanadas += $uiGanadas;
	    $unidadesPerdidas += $uiPerdidas;
	
	    $total_picks = count($tipster_picks);
	    // se calcula el rating
	    $rating = ($total_picks * (double)$yield) / 100;
	    
	    $statistics = array(
		    'picks'     => $total_picks,
		    'yield'     => $yield,
		    'beneficio' => $ganancias,
		    'rating'    => $rating,
		    'aciertos'  => $aciertos,
		    'fallos'    => $fallos,
		    'nulos'     => $nulos,
		    'unidades_apostadas' => $unidadesApostadas,
		    'unidades_ganadas'   => $unidadesGanadas,
		    'unidades_perdidas'  => $unidadesPerdidas
	    );
	    update_post_meta($tipster_id, '_tipster_statistics', $statistics);
	
	    ini_restore('max_input_time');
	    ini_restore('max_execution_time');
    }
	
	/**
     * Calculate and save tipster's statistics for a period
     *
	 * @param $tipster_id
	 *
     * @since 3.0
     *
	 * @throws \Exception
	 */
	public function update_statistics_by_month($tipster_id){
		ini_set('max_execution_time', 0);
		ini_set('max_input_time', -1);
		
		$months = (integer)get_theme_mod( 'tipster_tap_limit_statistics');
		$months_tipster = (integer)get_post_meta($tipster_id, '_tipster_limit_statistics', true);
		if($months_tipster > 0){
		    $months = $months_tipster;
        }
		$now = new \DateTime('now');
		$date_start = new \DateTime($now->format('Y-m'));
		$date_start->sub(new \DateInterval('P'.($months-1).'M')); // se resta 1 mes para tener en cuenta las estadisticas del mes en curso
		$date_end = new \DateTime($date_start->format('Y-m'));
		$date_end->add(new \DateInterval('P1M'));
		
		$date_range = array(
			'start' => $date_start->getTimestamp(),
			'end' => false,
			'op' => '<='
		);
		$args = array('finished', $date_range, false, false, false);
		do_action( 'tipster_tap_update_statistics', $tipster_id, $args);
		$statistic_before = get_post_meta($tipster_id, '_tipster_statistics', true);
		
		$i = 1;
		$aciertosAcumulado = (integer)$statistic_before['aciertos']; // apuestas acertadas
		$fallosAcumulado = (integer)$statistic_before['fallos'];  // apuestas falladas
		$nulosAcumulado = (integer)$statistic_before['nulos'];     // apuestas nulas
		$unidadesGanadasAcumulado =  (double)$statistic_before['unidades_ganadas']; // Unidades ganadas = Ganado - Apostado.
		$unidadesPerdidasAcumulado = (double)$statistic_before['unidades_perdidas']; // Las unidades falladas o unidades perdidas.
		$unidadesApostadasAcumulado = (double)$statistic_before['unidades_apostadas'];
		$gananciasAcumulado = (double)$statistic_before['beneficio']; // beneficio total durante el periodo
		$totalPicksAcumulado = (integer)$statistic_before['picks'];
        $statistics_by_month = array();
		
		while ($i <= $months){
			$year_month = $date_start->format('Y-m');
			
			$aciertosMes = 0;
			$fallosMes = 0;
			$nulosMes = 0;
			$unidadesApostadasMes = 0;
			$unidadesGanadasMes = 0;
			$unidadesPerdidasMes = 0;
			
			$date_range = array(
                'start' => $date_start->getTimestamp(),
                'end' => $date_end->getTimestamp()
            );
			
			$args = array('finished', $date_range, false, false, false);
			$tipster_picks = apply_filters('tipster_tap_get_picks', $tipster_id, $args);
			$totalPicksMes = count($tipster_picks);
			$totalPicksAcumulado += $totalPicksMes;
			
			foreach ($tipster_picks as $pick) {
				$resultado = $pick['pick_result'];
				$stake = (double)$pick['pick_stake'];
				
				$unidadesApostadasAcumulado += $stake;
				$unidadesApostadasMes += $stake;
				
				switch($resultado){
					case 'fallo':
						$fallosAcumulado++;
						$unidadesPerdidasAcumulado -= $stake;
						
						$fallosMes++;
						$unidadesPerdidasMes -= $stake;
						break;
					case 'nulo':
						$nulosAcumulado++;
						$nulosMes++;
						break;
					default: //  'acierto'
						$aciertosAcumulado++;
						$aciertosMes++;
						
						$cuota_x_acierto = (double)$pick['pick_cuote'];
						$unidadesGanadasAcumulado += ( ( $cuota_x_acierto * $stake ) - $stake );
						$unidadesGanadasMes += ( ( $cuota_x_acierto * $stake ) - $stake );
						break;
				}
			}
			
			$gananciasMes = $unidadesGanadasMes + $unidadesPerdidasMes;
			if( $totalPicksMes > 0 && ( $aciertosMes > 0 || $fallosMes > 0 ) ) {
				$gananciasAcumulado += $unidadesGanadasAcumulado + $unidadesPerdidasAcumulado;
			}
			
			// Obtener yield
			// Yield = ( Beneficios / TotalApostado ) x 100
			// se verifica si unidades totales esta vacia
			$yieldMes = $unidadesApostadasMes !== 0 ? ( $gananciasMes / $unidadesApostadasMes ) * 100 : 0;
			$yieldAcumulado = $unidadesApostadasAcumulado !== 0 ? ( $gananciasAcumulado / $unidadesApostadasAcumulado ) * 100 : 0;
			
			// se calcula el rating
			$ratingMes = ($totalPicksMes * $yieldMes) / 100;
			$ratingAcumulado = ($totalPicksAcumulado * $yieldAcumulado) / 100;
			
			$statistics_by_month[$year_month] = array(
				'mes' => array(
					'picks'     => $totalPicksMes,
					'yield'     => $yieldMes,
					'beneficio' => $gananciasMes,
					'rating'    => $ratingMes,
					'aciertos'  => $aciertosMes,
					'fallos'    => $fallosMes,
					'nulos'     => $nulosMes,
					'unidades_apostadas' => $unidadesApostadasMes,
					'unidades_ganadas'   => $unidadesGanadasMes,
					'unidades_perdidas'  => $unidadesPerdidasMes,
				),
				'acumulado' => array(
					'picks'     => $totalPicksAcumulado,
					'yield'     => $yieldAcumulado,
					'beneficio' => $gananciasAcumulado,
					'rating'    => $ratingAcumulado,
					'aciertos'  => $aciertosAcumulado,
					'fallos'    => $fallosAcumulado,
					'nulos'     => $nulosAcumulado,
					'unidades_apostadas' => $unidadesApostadasAcumulado,
					'unidades_ganadas'   => $unidadesGanadasAcumulado,
					'unidades_perdidas'  => $unidadesPerdidasAcumulado
				)
            );
			$date_start->add(new \DateInterval('P1M'));
			$date_end->add(new \DateInterval('P1M'));
			$i++;
		}
		
		update_post_meta($tipster_id, '_tipster_statistics_monthly', $statistics_by_month);
		
		$statistics_by_month_keys = array_keys($statistics_by_month);
		$statistics_last = $statistics_by_month[ $statistics_by_month_keys[ count($statistics_by_month_keys) - 1 ] ];
		update_post_meta($tipster_id, '_tipster_statistics_last', $statistics_last);
		
		ini_restore('max_input_time');
		ini_restore('max_execution_time');
		
		do_action( 'tipster_tap_update_yield_history', $tipster_id );
		do_action( 'tipster_tap_update_graphic_statistics', $tipster_id );
	}
	
	/**
     * Update tipster's yield history
     *
	 * @param $tipster_id
     *
     * @since 3.0
	 */
	public function update_yield_history($tipster_id){
		$yield_by_tipster = array();
	 
		/**
		 * @var array
		 */
	    $statistics = get_post_meta($tipster_id, '_tipster_statistics_monthly', true);
		
	    foreach ( $statistics as $year_month => $statistic ) {
		    $fecha = new \DateTime($year_month);
	        $yield_by_tipster[$year_month] = array(
			    'fecha' => $fecha->getTimestamp(),
			    'yield_mes' => number_format_i18n($statistic['mes']['yield'], 2),
                'yield_acumulado' => number_format_i18n($statistic['acumulado']['yield'], 2)
		    );
	    }
		
		update_post_meta($tipster_id, '_tipster_yield_history', $yield_by_tipster);
    }
	
	/**
     * Update tipster's graphics statistics
     *
	 * @param $tipster_id
     *
     * @since 3.0
	 */
    public function update_graphic_statistics($tipster_id){
	    $aciertos   = array();
	    $fallos     = array();
	    $nulos      = array();
	    $beneficios = array();
	    $yields     = array();
	
	    /**
	     * @var array
	     */
	    $statistics = get_post_meta($tipster_id, '_tipster_statistics_monthly', true);
	
	    foreach ( $statistics as $year_month => $statistic ) {
		    $acierto = (integer)$statistic['mes']['aciertos'];
		    $fallo = (integer)$statistic['mes']['fallos'];
		    $nulo = (integer)$statistic['mes']['nulos'];
		    $beneficio = (double)$statistic['mes']['beneficio'];
		    $yield = (double)$statistic['mes']['yield'];
		
		    $aciertos[] = array( $year_month, $acierto );
		    $fallos[] = array( $year_month, $fallo );
		    $nulos[] = array( $year_month, $nulo );
		    $beneficios[] = array( $year_month, number_format( $beneficio, '.', '' ) );
		    $yields[] = array( $year_month, number_format( $yield, 2, '.', '' ) );
	    }
	
	    $serie_aciertos   = array( 'data' => $aciertos, 'label' => __( 'Aciertos', $this->plugin_slug ), 'lines' => array( 'lineWidth' => 1 ), 'shadowSize' => 0, 'color' => 0 );
	    $serie_fallos     = array( 'data' => $fallos, 'label' => __( 'Fallos', $this->plugin_slug ), 'lines' => array( 'lineWidth' => 1 ), 'shadowSize' => 0, 'color' => 1 );
	    $serie_nulos      = array( 'data' => $nulos, 'label' => __( 'Nulos', $this->plugin_slug ), 'lines' => array( 'lineWidth' => 1 ), 'shadowSize' => 0, 'color' => 2 );
	    $serie_beneficios = array( 'data' => $beneficios, 'label' => __( 'Beneficios', $this->plugin_slug ), 'lines' => array( 'lineWidth' => 1 ), 'shadowSize' => 0, 'color' => 3 );
	    $serie_yields     = array( 'data' => $yields, 'label' => __( 'Yield', $this->plugin_slug ), 'lines' => array( 'lineWidth' => 1 ), 'shadowSize' => 0, 'color' => 4 );
	    $response         = array( 'aciertos' => $serie_aciertos, 'fallos' => $serie_fallos, 'nulos' => $serie_nulos, 'beneficios' => $serie_beneficios, 'yields' => $serie_yields );
	
	    update_post_meta($tipster_id, '_tipster_statistics_graphic', $response);
    }
	
	/**
	 * Calculates and save the statistics values
	 *
	 * @param $post_ID
	 * @param null $post
	 * @param bool $update
	 *
	 * @since    1.1.3
	 * @updated  2.6
     * @deprecated 3.0
     *
	 * @throws \Exception
	 */
    public function save_pick_statistics($post_ID, $post = null, $update = true){
        do_action('deprecated_function_run', 'save_pick_statistics', 'save_pick', '3.0');
        
        global $wpdb;
	    ini_set('max_execution_time', 0);
	    ini_set('max_input_time', -1);

	    $tipo_publicacion = get_post_meta($post_ID, '_post_tipo_publicacion', true);

        if(false === wp_is_post_revision($post) && strcmp($post->post_type, 'post') === 0 && strcmp($post->post_status, 'publish') === 0 && strcmp($tipo_publicacion, 'post') === 0)
        {
            delete_post_meta($post_ID, '_pick_pronostico_pago');
            delete_post_meta($post_ID, '_pick_live');
            delete_post_meta($post_ID, '_pick_evento');
            delete_post_meta($post_ID, '_pick_fecha_evento');
            delete_post_meta($post_ID, '_pick_hora_evento');
            delete_post_meta($post_ID, '_pick_pronostico');
            delete_post_meta($post_ID, '_pick_cuota');
            delete_post_meta($post_ID, '_pick_casa_apuesta');
            delete_post_meta($post_ID, '_pick_tipo_apuesta');
            delete_post_meta($post_ID, '_pick_tipster');
            delete_post_meta($post_ID, '_pick_competicion');
            delete_post_meta($post_ID, '_pick_deporte');
            delete_post_meta($post_ID, '_pick_resultado');
	
	        $pick_exist = $wpdb->get_col($wpdb->prepare('SELECT COUNT(*) FROM '.$wpdb->base_prefix.'picks WHERE pick_id = %d', array($post_ID)));
	        if(null !== $pick_exist && isset($pick_exist[0]) && true === (boolean)$pick_exist[0]) {
	            $wpdb->delete( $wpdb->base_prefix . 'picks', array( 'pick_id' => $post_ID ) );
            }
            return;
        }

        $picks_limit = (int)get_theme_mod('tipster_tap_limit_total_picks');
        $resultado = get_post_meta($post_ID, '_pick_resultado', true);
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

            $tipster_id = get_post_meta($post_ID, '_pick_tipster', true);

            $datos_iniciales = (boolean)get_post_meta($tipster_id, '_tipster_incluir_datos_iniciales', true);

            // Si han sido habilitados los datos iniciales los usamos.
            if(true === $datos_iniciales){
                //Veces que ha apostado saliendo ganador.
                $aiAcertadas = (int)get_post_meta($tipster_id, '_tipster_aciertos_iniciales', true);
                if(!is_int($aiAcertadas)) {
	                $aiAcertadas = 0;
                }
                
                //Veces que ha apostado y ha perdido.
                $aiFalladas =  (int)get_post_meta($tipster_id, '_tipster_fallos_iniciales', true);
                if(!is_int($aiFalladas)) {
	                $aiFalladas = 0;
                }
                
                //Veces que ha apostado y el resultado ha sido nulo.
                $aiNulas =  (int)get_post_meta($tipster_id, '_tipster_nulos_iniciales', true);
                if(!is_int($aiNulas)) {
	                $aiNulas = 0;
                }
                
                //Unidades totales que ha jugado
                $uiJugadas =  (float)str_replace(',', '.', get_post_meta($tipster_id, '_tipster_unidades_jugadas_iniciales', true));
                if(!is_float($uiJugadas)) {
	                $uiJugadas = 0;
                }

                //Unidades totales que ha gando
                $uiGanadas =  (float)str_replace(',', '.', get_post_meta($tipster_id, '_tipster_unidades_ganadas_iniciales', true));
                if(!is_float($uiGanadas)) {
	                $uiGanadas = 0;
                }

                //Unidades totales que ha perdido
                $uiPerdidas =  (float)str_replace(',', '.', get_post_meta($tipster_id, '_tipster_unidades_perdidas_iniciales', true));
                if(!is_float($uiPerdidas)) {
	                $uiPerdidas = 0;
                }
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

	            $unidadesTotales += $stake;

                switch($resultado){
                    case "fallo":
                        $aFalladas += 1;
                        $unidadesFalladas -= $stake;
                        break;
                    case "nulo":
                        $aNulas += 1;
                        break;
                    default: // "acierto"
                        $aAcertadas += 1;
                        $cuota_x_acierto = (float)str_replace(',', '.', get_post_meta($tipster_post->ID, '_pick_cuota', true));
                        $unidadesGanadas += ( ( $cuota_x_acierto * $stake ) - $stake );
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
	        $rating = ((float)$total_picks * (float)$yield) / 100;
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
	
	/**
	 * @param $tipster
	 * @param int $limit
	 *
	 * @return int
     *
     * @deprecated 3.0
	 */
	public function count_total_tipster_picks($tipster, $limit = -1)
	{
		do_action('deprecated_function_run', 'count_total_tipster_picks', 'hook tipster_tap_get_total_picks', '3.0');
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
	
	/**
	 * @param $tipster
	 *
	 * @return array|null|object|void
     *
     * @deprecated 3.0
	 */
	public function tipster_last_statistics($tipster){
		do_action('deprecated_function_run', 'tipster_last_statistics', 'update_statistics', '3.0');
		global $wpdb;

		$query_statistics = 'SELECT * FROM statistics where user_id = ' . $tipster . ' ORDER BY  last_stat DESC LIMIT 1';
		$statistics = $wpdb->get_row($query_statistics, OBJECT);

		update_post_meta($tipster, '_tipster_last_statistics', $statistics);
		return $statistics;
	}
	
	/**
	 * @param $tipster
	 *
	 * @throws \Exception
     *
     * @deprecated 3.0
	 */
	public function tipster_graphic_statistics($tipster){
		do_action('deprecated_function_run', 'tipster_graphic_statistics', 'update_graphic_statistics', '3.0');
		global $wpdb;

		$months = get_theme_mod( 'tipster_tap_limit_statistics', 6 );
		$period = new \DateInterval('P'.$months.'M1D');
		$date_end = new \DateTime();
		$dateStr = $date_end->format('Y-m-d');
		$date_start = new \DateTime($dateStr);
		$date_start->sub($period);

		$statistics_query  = sprintf("SELECT * FROM statistics WHERE user_id = '%s' AND last_stat BETWEEN '%s' AND '%s' ORDER BY last_stat DESC;", $tipster, $date_start->format('Y-m-d H:i:s'), $date_end->format('Y-m-d H:i:s'));
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
			$fecha            = $date_time->format( 'Y-m-d' );
			$acierto          = number_format_i18n((integer)$statistic[ 'corrects' ]);
			$fallo            = number_format_i18n((integer)$statistic[ 'wrongs' ]);
			$nulo             = number_format_i18n((integer)$statistic[ 'voids' ]);
			$unidades_ganadas = number_format_i18n((float)$statistic[ 'win_units' ], 2);
			$unidades_perdidas = number_format_i18n((float)$statistic[ 'lost_units' ], 2);
			$ganancia = $unidades_ganadas + $unidades_perdidas;
			$ganancia = number_format_i18n($ganancia, 2);
			$yield = number_format_i18n((float)$statistic[ 'yield' ], 2);
			$aciertos[] = array( $fecha, $acierto );
			$fallos[] = array( $fecha, $fallo );
			$nulos[] = array( $fecha, $nulo );
			$ganancias[] = array( $fecha, number_format_i18n( $ganancia, 2 ) );
			$yields[] = array( $fecha, $yield);
		}
		$serie_aciertos  = array( 'data' => $aciertos, 'label' => __( 'Aciertos', $this->plugin_slug ), 'lines' => array( 'lineWidth' => 1 ), 'shadowSize' => 0, 'color' => 0 );
		$serie_fallos    = array( 'data' => $fallos, 'label' => __( 'Fallos', $this->plugin_slug ), 'lines' => array( 'lineWidth' => 1 ), 'shadowSize' => 0, 'color' => 1 );
		$serie_nulos     = array( 'data' => $nulos, 'label' => __( 'Voids', $this->plugin_slug ), 'lines' => array( 'lineWidth' => 1 ), 'shadowSize' => 0, 'color' => 2 );
		$serie_ganancias = array( 'data' => $ganancias, 'label' => __( 'U. ganadas', $this->plugin_slug ), 'lines' => array( 'lineWidth' => 1 ), 'shadowSize' => 0, 'color' => 3 );
		$serie_yields    = array( 'data' => $yields, 'label' => __( 'Yield', $this->plugin_slug ), 'lines' => array( 'lineWidth' => 1 ), 'shadowSize' => 0, 'color' => 4 );
		$response        = array( 'aciertos' => $serie_aciertos, 'fallos' => $serie_fallos, 'nulos' => $serie_nulos, 'ganancias' => $serie_ganancias, 'yields' => $serie_yields );

		update_post_meta($tipster, '_tipster_statistics_graphic', $response);
	}
	
	/**
	 * @param $tipster
	 *
	 * @deprecated 3.0
     *
	 * @throws \Exception
	 */
	public function tipster_yield_history($tipster){
		do_action('deprecated_function_run', 'tipster_yield_history', 'update_yield_history', '3.0');
		global $wpdb;

		$months = (integer)get_theme_mod( 'tipster_tap_limit_statistics');
		$now = new \DateTime();
		$dateStr = $now->format('Y-m');
		$date_end = new \DateTime($dateStr);
		$date_end->add(new \DateInterval('P1M'));
		$dateStr = $date_end->format('Y-m');
		$date_start = new \DateTime($dateStr);
		$date_start->sub(new \DateInterval('P'.$months.'M'));

		$statistics_query = sprintf("SELECT last_stat, yield AS yield FROM statistics WHERE user_id = '%s' AND last_stat BETWEEN '%s' AND '%s' GROUP BY yield ORDER BY last_stat DESC;", $tipster, $date_start->format('Y-m-d H:i:s'), $date_end->format('Y-m-d H:i:s'));
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
					'yield_global' => $yield
				);
				continue;
			}

			if(array_key_exists($fecha_formated, $yield_by_tipster) && $fecha > $yield_by_tipster[$fecha_formated]['fecha']){
				$yield_by_tipster[$fecha_formated]['fecha'] = $fecha;
				$yield_by_tipster[$fecha_formated]['yield_global'] = $yield;
			}
		}

		update_post_meta($tipster, '_tipster_yield_history', $yield_by_tipster);
	}
	
	/**
     * Execute migration from wp_post to wp_picks to any post with post_meta _post_tipo_publicacion = pick
     *
	 * @since 3.0
	 */
    public function execute_pick_migration(){
        global $wpdb;
        
	    $option = (float)get_option('tipster_tap_version');
	    if( $option >= 3.1 ) {
		    $message = __( 'Migration is not necessary.', $this->plugin_slug );
		    add_settings_error('tipstertap-manage-picks-migration', 'tipstertap-manage-picks-migration', $message);
	        return;
	    }
	
	    $tipsters_args = array(
		    'post_type'              => 'tipster',
		    'post_status'            => 'publish',
		    'posts_per_page'         => -1,
		    'order'                  => 'ASC',
		    'cache_results'          => false,
		    'update_post_meta_cache' => false,
		    'update_post_term_cache' => false,
		    'ignore_sticky_posts'    => true
	    );
        $tipsters = get_posts($tipsters_args);
        foreach ( $tipsters as $tipster ) {
            if($tipster instanceof \WP_Post) {
                $meta_query_post = array(
                    'relation' => 'AND'
                );
        
                $meta_query_post[] = array(
                    'key'     => '_post_tipo_publicacion',
                    'value'   => 'pick',
                    'compare' => '=',
                );
        
                $meta_query_post[] = array(
                    'key'     => '_pick_tipster',
                    'value'   => $tipster->ID,
                    'compare' => '=',
                );
        
                $query_post = array(
                    'post_type'              => 'post',
                    'post_status'            => 'publish',
                    'posts_per_page'         => -1,
                    'order'                  => 'ASC',
                    'meta_query'             => $meta_query_post,
                    'cache_results'          => false,
                    'update_post_meta_cache' => false,
                    'update_post_term_cache' => false,
                    'ignore_sticky_posts'    => true
                );
                $tipster_posts = get_posts($query_post);
                foreach ( $tipster_posts as $post ) {
                    if($post instanceof \WP_Post) {
                        try {
                            $pick_date     = get_post_meta( $post->ID, '_pick_fecha_evento', true );
                            $pick_time     = get_post_meta( $post->ID, '_pick_hora_evento', true );
                            $pick_datetime = \DateTime::createFromFormat( 'd/m/Y H:i', sprintf( '%s %s', $pick_date, $pick_time ) );
                            if( false === ($pick_datetime instanceof \DateTime) ){
                                $pick_datetime = new \DateTime($post->post_date);
                            }
                            $tipsters_args = array(
                                'tipster_id'     => (integer) get_post_meta( $post->ID, '_pick_tipster', true ),
                                'pick_id'        => $post->ID,
                                'bookie_id'      => get_post_meta( $post->ID, '_pick_casa_apuesta', true ),
                                'sport_id'       => (integer) get_post_meta( $post->ID, '_pick_deporte', true ),
                                'competition_id' => (integer) get_post_meta( $post->ID, '_pick_competicion', true ),
                                'pick_datetime'  => $pick_datetime instanceof \DateTime ? $pick_datetime->getTimestamp() : null,
                                'pick_cuote'     => (double) get_post_meta( $post->ID, '_pick_cuota', true ),
                                'pick_stake'     => (integer) get_post_meta( $post->ID, '_pick_stake', true ),
                                'pick_type'      => get_post_meta( $post->ID, '_pick_tipo_apuesta', true ),
                                'pick_result'    => get_post_meta( $post->ID, '_pick_resultado', true )
                            );
                
                            $pick_exist = $wpdb->get_col($wpdb->prepare('SELECT COUNT(*) FROM '.$wpdb->base_prefix.'picks WHERE pick_id = %d', array($post->ID)));
                            if(null !== $pick_exist && isset($pick_exist[0]) && true === (boolean)$pick_exist[0]) {
                                $where = array('pick_id' => $post->ID);
                                $this->pick_insert_or_update( $tipsters_args, $where );
                            }else{
                                $this->pick_insert_or_update( $tipsters_args );
                            }
                        } catch ( \Exception $e ) {
                            add_settings_error( 'tipstertap-manage-picks-migration', 'tipstertap-manage-picks-migration', $e->getMessage() );
                        }
                    }
                }
            }
        }
		
        add_settings_error( 'tipstertap-manage-picks-migration', 'tipstertap-manage-picks-migration', __( 'Migration successfully.', $this->plugin_slug ), 'updated' );
	    
        update_option('tipster_tap_version', '3.1');
	    //TODO: For test propose uncomment next line, comment again when test finish
        //delete_option('tipster_tap_version');
    }
	
	/**
	 * @param $tipster_id
     *
     * @since 3.0
	 */
    public function update_tipster_metas($tipster_id){
	    do_action( 'tipster_tap_get_total_picks', $tipster_id );
	    do_action( 'tipster_tap_update_statistics_by_month', $tipster_id );
    }
	
	/**
	 * @param $tipster_id
     *
     * @since 3.3
	 */
    public function update_picks_date($tipster_id) {
        global $wpdb;
	    try {
		    $tipster_picks = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . $wpdb->base_prefix . "picks WHERE tipster_id = '%s' AND pick_datetime IS NULL;", $tipster_id ), ARRAY_A );
		    foreach ( $tipster_picks as $pick ) {
			    $post = get_post( $pick['pick_id'] );
			    if ( $post instanceof \WP_Post ) {
				    $pick_datetime = new \DateTime( $post->post_date );
				    $args = array(
					    'pick_datetime' => $pick_datetime->getTimestamp()
				    );
				    $where = array( 'pick_id' => $post->ID );
				    $this->pick_insert_or_update( $args, $where );
			    }
		    }
	    }catch (\Exception $e){
		    $_SESSION['TIPSTER_TAP_ERRORS'][] = $e->getMessage();
		    add_settings_error( 'tipstertap-manage-tipsters-metas', 'tipstertap-manage-tipsters-metas', $e->getMessage() );
	    }
    }
	
	/**
	 * @param array $args
	 * @param bool|array $where
	 *
     * @since 3.0
     *
	 * @throws \Exception
	 */
    public function pick_insert_or_update($args, $where = false){
	    global $wpdb;
	    $update_at = new \DateTime('now');
	    $args['updated_at'] = $update_at->format('Y-m-d H:i');
	    try {
		    if ( false === $where ) {
			    $wpdb->insert( $wpdb->base_prefix . 'picks', $args );
		    } else {
			    $wpdb->update( $wpdb->base_prefix . 'picks', $args, $where );
		    }
	    }catch (\Exception $e){
	        $operation = false === $where ? 'insert' : 'update';
	        $args = print_r($args, true);
		    $message = sprintf( __( '%s. Invalid data to %s: %s', $this->plugin_slug ), $e->getMessage(), $operation, $args );
		    throw new \ErrorException($message);
        }
    }
	
	/**
	 * @since 2.6
	 */
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
