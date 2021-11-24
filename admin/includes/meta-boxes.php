<?php

namespace TipsterTAP\Backend\Common;

use TipsterTAP\Frontend\TipsterTap;

/**
 * Include and setup custom metaboxes and fields.
 *
 * @category TipsterTap
 * @package  Metaboxes
 * @license  http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 * @link     https://github.com/webdevstudios/Custom-Metaboxes-and-Fields-for-WordPress
 */
class MetaBoxesPostType {
    /**
     * Instance of this class.
     *
     * @since    1.0.0
     *
     * @var      object
     */
    protected static $instance = null;

    private $plugin_slug;

    /**
     * Initialize the plugin by loading admin scripts & styles and adding a
     * settings page and menu.
     *
     * @since     1.0.0
     */
    private function __construct() {
        $this->plugin_slug = TipsterTap::get_instance()->get_plugin_slug();
        add_action( 'cmb2_admin_init', array( $this, 'post_type_pick_metabox' ) );
        add_action( 'cmb2_admin_init', array( $this, 'post_type_tipster_metabox' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
    }

    /**
     * @param array $elements
     * @return array
     *
     * @since 1.0.0
     * @updated 4.2.2
     */
    private function get_options($elements) {
        $options = array();
        foreach($elements as $k => $v){
            try {
                if (is_array($v) && array_key_exists('nombre', $v) && !empty($v['nombre'])) {
                    if(array_key_exists('id', $v)) {
                        $options[$v['id']] = $v['nombre'];
                    } else {
                        $options[$k] = $v['nombre'];
                    }
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        return $options;
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
	 * Define the metabox and field configurations for post-type pick.
	 *
	 * @return void
	 */
    public function post_type_pick_metabox() {
        global $post;
        // Start with an underscore to hide fields from custom fields list
        $prefix = '_pick_';

        $tap_bookies = get_option('TAP_BOOKIES');
        $bookies = array();
        if($tap_bookies){
            $bookies = $this->get_options($tap_bookies);
        }

        $tap_deportes = get_option('TAP_DEPORTES');
        $deportes = array();
        if($tap_deportes){
            $deportes = $this->get_options($tap_deportes);
        }

        $tap_competiciones = get_option('TAP_COMPETICIONES');
        $competiciones = array();
        if($tap_competiciones){
            $competiciones = $this->get_options($tap_competiciones);
        }

        $tipsters = array();
        $tipster_query = array(
            'post_type' => 'tipster',
            'order' => 'ASC',
            'orderby' => 'name'
        );
        $tipster_query_result = new \WP_Query($tipster_query);
        if($tipster_query_result->have_posts()){
            while($tipster_query_result->have_posts()){
                $tipster_query_result->the_post();
                $tipster = $post;
                $tipsters[$tipster->ID] = $tipster->post_title;
            }
            wp_reset_query();
        }
	
	    $cmb_post_type = new_cmb2_box(
	    	array(
			    'id'            => 'post_type_metabox',
			    'title'         => __( 'Informacion adicional', $this->plugin_slug ),
			    'object_types'  => array( 'post', )
	        )
	    );
        
	    $cmb_post_type->add_field(
		    array(
			    'name'    => __('Tipo de publicacion', $this->plugin_slug),
			    'desc'    => __('Seleccionar el tipo de publicacion.<br>Si no es un <strong>PICK</strong> debes dejar el valor por defecto: <strong>POST</strong>', $this->plugin_slug),
			    'id'      => '_post_tipo_publicacion',
			    'type'    => 'select',
			    'options' => array(
				    'post' => __('Post', $this->plugin_slug),
				    'pick' => __('Pick', $this->plugin_slug)
			    ),
		    )
	    );
	    
	    $cmb_pick_informacion_general = new_cmb2_box(
		    array(
			    'id'            => 'pick_informacion_general',
			    'title'         => __( 'Tipo de publicacion', $this->plugin_slug ),
			    'object_types'  => array( 'post', )
		    )
	    );
	
	    $cmb_pick_informacion_general->add_field(
		    array(
			    'name' => __('Pronostico de pago', $this->plugin_slug),
			    'desc' => __( 'Seleccionar si es un pronostico de pago', $this->plugin_slug ),
			    'id'   => $prefix . 'pronostico_pago',
			    'type' => 'checkbox'
		    )
	    );
	    $cmb_pick_informacion_general->add_field(
		    array(
			    'name' => __('Live', $this->plugin_slug),
			    'desc' => __( 'Seleccionar si es live', $this->plugin_slug ),
			    'id'   => $prefix . 'live',
			    'type' => 'checkbox'
		    )
	    );
	    $cmb_pick_informacion_general->add_field(
		    array(
			    'name' => __('Evento', $this->plugin_slug),
			    'desc' => __( 'Escribir el nombre del evento deportivo, social o lo que sea que permita una apuesta', $this->plugin_slug ),
			    'id'   => $prefix . 'evento',
			    'type' => 'text'
		    )
	    );
	    $cmb_pick_informacion_general->add_field(
		    array(
			    'name'        => __( 'Fecha del Evento', $this->plugin_slug ),
			    'desc'        => __( '<br>Seleccionar la fecha en que ocurre el evento deportivo.<br>Indicar utilizando el formato yyyy-mm-dd', $this->plugin_slug ),
			    'id'          => $prefix . 'fecha_evento',
			    'type'        => 'text_date',
			    'date_format' => 'Y-m-d' // d/m/Y
		    )
	    );
	    $cmb_pick_informacion_general->add_field(
		    array(
			    'name'        => __( 'Hora del Evento', $this->plugin_slug ),
			    'desc'        => __( '<br>Seleccionar la hora en que ocurre el evento deportivo.<br>Indicar utilizando el formato hh:mm', $this->plugin_slug ),
			    'id'          => $prefix . 'hora_evento',
			    'type'        => 'text_time',
			    'time_format' => 'H:i'
		    )
	    );
	    $cmb_pick_informacion_general->add_field(
		    array(
			    'name' => __('Pronostico', $this->plugin_slug),
			    'desc' => __( 'Escribir que apuesta/pronostico vas a realizar', $this->plugin_slug ),
			    'id'   => $prefix . 'pronostico',
			    'type' => 'text'
		    )
	    );
	    $cmb_pick_informacion_general->add_field(
		    array(
			    'name' => __('Cuota', $this->plugin_slug),
			    'desc' => __( 'Escribir la cuota de la apuesta.<br>Como separador decimal debe utilizar el punto. Ejemplo incorrecto: 1,23 / correcto: 1.23', $this->plugin_slug ),
			    'id'   => $prefix . 'cuota',
			    'type' => 'text'
		    )
	    );
	    $cmb_pick_informacion_general->add_field(
		    array(
			    'name' => __('Stake', $this->plugin_slug),
			    'desc' => __( 'Escribir el nivel de confianza en la apuesta.<br>Como separador decimal debe utilizar el punto. Ejemplo incorrecto: 1,23 / correcto: 1.23', $this->plugin_slug ),
			    'id'   => $prefix . 'stake',
			    'type' => 'text'
		    )
	    );
	    $cmb_pick_informacion_general->add_field(
		    array(
			    'name'    => __('Casa de apuestas', $this->plugin_slug),
			    'desc'    => __('Seleccionar la casa de apuestas donde haz realizado la apuesta', $this->plugin_slug),
			    'id'      => $prefix . 'casa_apuesta',
			    'type'    => 'select',
			    'options' => $bookies
		    )
	    );
	    $cmb_pick_informacion_general->add_field(
		    array(
			    'name'    => __('Tipo de apuesta', $this->plugin_slug),
			    'desc'    => __('Seleccionar el tipo de apuesta hecha, ya sea un over, under, handicap...', $this->plugin_slug),
			    'id'      => $prefix . 'tipo_apuesta',
			    'type'    => 'select',
			    'options' =>  array( // TODO: evaluar y/o valorar la posibilidad de convertirlo a taxonomia
				    'ganador'   => __( 'Ganador', $this->plugin_slug ),
				    'perdedor'  => __( 'Perdedor', $this->plugin_slug ),
				    'under'     => __( 'Under', $this->plugin_slug ),
				    'over'      => __( 'Over', $this->plugin_slug ),
				    'handicap'  => __( 'Handicap', $this->plugin_slug ),
				    'resultado' => __( 'Resultado concreto', $this->plugin_slug ),
				    'combinada' => __( 'Combinada', $this->plugin_slug ),
				    'funbet'    => __( 'Funbet', $this->plugin_slug ),
				    'reto'      => __( 'Reto', $this->plugin_slug ),
				    'otro'      => __( 'Otro', $this->plugin_slug ),
			    ),
		    )
	    );
	    $cmb_pick_informacion_general->add_field(
		    array(
			    'name' => __('Tipster', $this->plugin_slug),
			    'desc' => __( 'Seleccionar el tipster que promueve la apuesta', $this->plugin_slug ),
			    'id'   => $prefix . 'tipster',
			    'type' => 'select',
			    'options' => $tipsters
		    )
	    );
	    $cmb_pick_informacion_general->add_field(
		    array(
			    'name' => __('Competicion', $this->plugin_slug),
			    'desc' => __( 'Seleccionar el nombre de la competencion asociada a la apuesta', $this->plugin_slug ),
			    'id'   => $prefix . 'competicion',
			    'type' => 'select',
			    'options' => $competiciones
		    )
	    );
	    $cmb_pick_informacion_general->add_field(
		    array(
			    'name' => __('Deporte', $this->plugin_slug),
			    'desc' => __( 'Seleccionar el deporte asociado a la apuesta', $this->plugin_slug ),
			    'id'   => $prefix . 'deporte',
			    'type' => 'select',
			    'options' => $deportes
		    )
	    );
	    $cmb_pick_informacion_general->add_field(
		    array(
			    'name'    => __('Resultado', $this->plugin_slug),
			    'desc'    => __('Seleccionar el resultado de la apuesta: pendiente, acierto, fallo o nulo.<br>Si el evento aún no se ha resuelto debes dejar el resultado <strong>PENDIENTE</strong> como seleccionado.<br>Cuando el evento se resuelva actualiza el resultado según sea <strong>ACIERTO</strong>, <strong>FALLO</strong> o <strong>NULO</strong>', $this->plugin_slug),
			    'id'      => $prefix . 'resultado',
			    'column'           => true,
			    'display_cb'       => array( $this, 'display_pick_result_column'),
			    'type'    => 'select',
			    'options' => array(
				    'pendiente' => __('Pendiente', $this->plugin_slug),
				    'acierto'   => __('Acierto', $this->plugin_slug),
				    'fallo'     => __('Fallo', $this->plugin_slug),
				    'nulo'      => __('Nulo', $this->plugin_slug),
			    ),
		    )
	    );
    }
    
    public function display_pick_result_column($field_args, $field){
	    global $post;
	    
	    $post_tipo_publicacion = get_post_meta($post->ID, '_post_tipo_publicacion', true);
	
	    $text = null;
	    $color = null;
	    
	    if(strcmp($post_tipo_publicacion, 'pick') === 0) {
		    $selected_value = $field->escaped_value();
		    switch ( $selected_value ) {
			    case 'acierto':
				    $text = __( 'Acierto', $this->plugin_slug );
					$color = '#6AA84F';
					break;
			    case 'fallo':
				    $text = __( 'Fallo', $this->plugin_slug );
				    $color = '#EF082C';
				    break;
			    case 'nulo':
				    $text = __( 'Nulo', $this->plugin_slug );
				    $color = '#519BF8';
				    break;
			    default: // pendiente
				    $text = __( 'Pendiente', $this->plugin_slug );
				    $color = '#727271';
				    break;
		    }
	    }else{
		    $text = '—';
		    $color = 'inherit';
	    }
	    printf('<span style="font-weight: 700; color: %s;">%s</span>', $color, $text);
    }
	
	/**
	 * Define the metabox and field configurations for post-type tipster.
	 *
	 * @return void
	 */
    public function post_type_tipster_metabox() {
        // Start with an underscore to hide fields from custom fields list
        $prefix = '_tipster_';
	
	    $cmb_tipster_extra_info = new_cmb2_box(
		    array(
			    'id'            => 'tipster_extra_info',
			    'title'         => __( 'Informacion adicional', $this->plugin_slug ),
			    'object_types'  => array( 'tipster', )
		    )
	    );
	
	    $cmb_tipster_extra_info->add_field(
		    array(
                'name'    => __('Datos iniciales', $this->plugin_slug),
			    'desc'    => __('Seleccionar si se utilizaran o no valores iniciales para realizar los calculos.', $this->plugin_slug),
			    'id'      => $prefix.'incluir_datos_iniciales',
			    'type'    => 'select',
			    'options' => array(
				    '0' => __('NO', $this->plugin_slug),
				    '1' => __('SI', $this->plugin_slug)
			    ),
		    )
	    );
	    $cmb_tipster_extra_info->add_field(
		    array(
			    'name' => __('Aciertos', $this->plugin_slug),
			    'desc' => __( '<br>Escribir la cantidad inicial de aciertos.', $this->plugin_slug ),
			    'id'   => $prefix . 'aciertos_iniciales',
			    'type' => 'text_small',
			    'default' => 0
		    )
	    );
	    $cmb_tipster_extra_info->add_field(
		    array(
			    'name' => __('Fallos', $this->plugin_slug),
			    'desc' => __( '<br>Escribir la cantidad inicial de fallos.', $this->plugin_slug ),
			    'id'   => $prefix . 'fallos_iniciales',
			    'type' => 'text_small',
			    'default' => 0
		    )
	    );
	    $cmb_tipster_extra_info->add_field(
		    array(
			    'name' => __('Nulos', $this->plugin_slug),
			    'desc' => __( '<br>Escribir la cantidad inicial de datos nulos.', $this->plugin_slug ),
			    'id'   => $prefix . 'nulos_iniciales',
			    'type' => 'text_small',
			    'default' => 0
		    )
	    );
	    $cmb_tipster_extra_info->add_field(
		    array(
			    'name' => __('Unidades jugadas', $this->plugin_slug),
			    'desc' => __( '<br>Escribir la cantidad inicial de unidades jugadas.<br>Como separador decimal debe utilizar el punto. Ejemplo incorrecto: 1,23 / correcto: 1.23', $this->plugin_slug ),
			    'id'   => $prefix . 'unidades_jugadas_iniciales',
			    'type' => 'text_small',
			    'default' => 0
		    )
	    );
	    $cmb_tipster_extra_info->add_field(
		    array(
			    'name' => __('Unidades ganadas', $this->plugin_slug),
			    'desc' => __( '<br>Escribir la cantidad inicial de unidades ganadas.<br>Como separador decimal debe utilizar el punto. Ejemplo incorrecto: 1,23 / correcto: 1.23', $this->plugin_slug ),
			    'id'   => $prefix . 'unidades_ganadas_iniciales',
			    'type' => 'text_small',
			    'default' => 0
		    )
	    );
	    $cmb_tipster_extra_info->add_field(
		    array(
			    'name' => __('Unidades perdidas', $this->plugin_slug),
			    'desc' => __( '<br>Escribir la cantidad inicial de unidades perdidas.<br>Como separador decimal debe utilizar el punto. Ejemplo incorrecto: 1,23 / correcto: 1.23', $this->plugin_slug ),
			    'id'   => $prefix . 'unidades_perdidas_iniciales',
			    'type' => 'text_small',
			    'default' => 0
		    )
	    );
	    $months = (integer)get_theme_mod( 'tipster_tap_limit_statistics');
	    $cmb_tipster_extra_info->add_field(
		    array(
			    'name' => __('Estadisticas', $this->plugin_slug),
			    'desc' => sprintf(__( '<br>Escribir la cantidad de meses a obtener registros para calcular y/o graficar las estadisticas.<br>Por defecto se asumen %s meses.', $this->plugin_slug ), $months),
			    'id'   => $prefix . 'limit_statistics',
			    'type' => 'text_small',
			    'default' => $months
		    )
	    );
	    $cmb_tipster_extra_info->add_field(
		    array(
			    'name'      => __( 'Google+', $this->plugin_slug ),
			    'desc'      => __( 'Escribir la url del profile en Google Plus', $this->plugin_slug ),
			    'id'        => $prefix . 'google_plus',
			    'type'      => 'text_url',
			    'protocols' => array( 'http', 'https' ),
		    )
	    );
    }

    public function enqueue_scripts(){
        $screen = get_current_screen();
        if(null !== $screen) {
	        switch ( $screen->id ) {
		        case 'post':
			        wp_enqueue_script( $this->plugin_slug . '-admin-metabox-script', plugins_url( 'assets/js/meta-boxes-post.min.js', __DIR__ ), array( 'jquery' ), TipsterTap::VERSION );
			        wp_enqueue_script( 'datepicker-es', plugins_url( 'assets/js/datepicker.es.min.js', __DIR__ ), array( 'jquery-ui-core', 'jquery-ui-datepicker', 'jquery-ui-datetimepicker' ), TipsterTap::VERSION );
			        wp_enqueue_script( 'jquery-validation', plugins_url( 'assets/js/jquery-validation/js/jquery.validate.min.js', __DIR__ ), array( 'jquery' ), TipsterTap::VERSION );
			        wp_enqueue_script( 'jquery-validation-es', plugins_url( 'assets/js/jquery-validation/i18n/messages_es.min.js', __DIR__ ), array( 'jquery-validation' ), TipsterTap::VERSION );
			        wp_enqueue_script( $this->plugin_slug . '-admin-pick-validation', plugins_url( 'assets/js/pick-validations.min.js', __DIR__ ), array( 'jquery', 'jquery-validation' ), TipsterTap::VERSION );
			        break;
		        case 'tipster':
			        wp_enqueue_script( $this->plugin_slug . '-admin-metabox-script', plugins_url( 'assets/js/meta-boxes-tipster.min.js', __DIR__ ), array( 'jquery' ), TipsterTap::VERSION );
			        wp_enqueue_script( 'jquery-validation', plugins_url( 'assets/js/jquery-validation/js/jquery.validate.min.js', __DIR__ ), array( 'jquery' ), TipsterTap::VERSION );
			        wp_enqueue_script( 'jquery-validation-es', plugins_url( 'assets/js/jquery-validation/i18n/messages_es.min.js', __DIR__ ), array( 'jquery-validation' ), TipsterTap::VERSION );
			        wp_enqueue_script( $this->plugin_slug . '-admin-tipster-validation', plugins_url( 'assets/js/tipster-validations.min.js', __DIR__ ), array( 'jquery', 'jquery-validation' ), TipsterTap::VERSION );
			        break;
		        default:
			        break;
	        }
        }
    }
}








