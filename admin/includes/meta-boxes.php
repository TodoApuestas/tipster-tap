<?php


/**
 * Include and setup custom metaboxes and fields.
 *
 * @category Tipster_TAP
 * @package  Metaboxes
 * @license  http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 * @link     https://github.com/webdevstudios/Custom-Metaboxes-and-Fields-for-WordPress
 */
class Meta_Boxes_Post_Type {
    /**
     * Instance of this class.
     *
     * @since    1.0.0
     *
     * @var      object
     */
    protected static $instance = null;

    /**
     * Initialize the plugin by loading admin scripts & styles and adding a
     * settings page and menu.
     *
     * @since     1.0.0
     */
    private function __construct() {
        add_filter( 'cmb_meta_boxes', array( $this, 'post_type_pick_metabox' ) );
        add_action( 'init', array( $this, 'cmb_initialize_cmb_meta_boxes' ), 9999 );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
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
         * @TODO :
         *
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
     * Define the metabox and field configurations for post-type pick.
     *
     * @param  array $meta_boxes
     * @return array
     */
    function post_type_pick_metabox( array $meta_boxes ) {
        global $post;
        // Start with an underscore to hide fields from custom fields list
        $prefix = '_pick_';

        $bookies = get_option('tipster_tap_bookies');
        $tipsters = array();
        $tipster_query = array(
            'post_type' => 'tipster',
            'order' => 'ASC',
            'orderby' => 'name'
        );
        $tipster_query_result = new WP_Query($tipster_query);
        if($tipster_query_result->have_posts()){
            while($tipster_query_result->have_posts()){
                $tipster_query_result->the_post();
                $tipster = $post;
                $tipsters[$tipster->ID] = $tipster->post_title;
            }
        }

        $meta_boxes['post_type'] = array(
            'id'         => 'post_type',
            'title'      => __( 'Tipo publicacion', 'epic' ),
            'pages'      => array( 'post' ), // Tells CMB to use user_meta vs post_meta
            'show_names' => true,
            'cmb_styles' => true, // Show cmb bundled styles.. not needed on user profile page
            'fields'     => array(
                array(
                    //'name'    => __('Tipo de apuesta', 'epic'),
                    'desc'    => __('Seleccionar el tipo de publicacion.<br>Si no es un pick debes dejar el valor por defecto: <strong>POST</strong>', 'epic'),
                    'id'      => '_post_tipo_publicacion',
                    'type'    => 'select',
                    'options' => array(
                        'post' => __('Post', 'epic'),
                        'pick' => __('Pick', 'epic')
                    ),
                )
            )
        );

        $meta_boxes['pick_informacion_general'] = array(
            'id'         => 'pick_informacion_general',
            'title'      => __( 'Picks', 'epic' ),
            'pages'      => array( 'post' ), // Tells CMB to use user_meta vs post_meta
            'show_names' => true,
            'cmb_styles' => true, // Show cmb bundled styles.. not needed on user profile page
            'fields'     => array(
                array(
                    'name' => __('Evento', 'epic'),
                    'desc' => __( 'Escribir el nombre del evento deportivo, social o lo que sea que permita una apuesta', 'epic' ),
                    'id'   => $prefix . 'evento',
                    'type' => 'text'
                ),
                array(
                    'name' => __('Fecha del Evento', 'epic'),
                    'desc' => __('Seleccionar/escribir la fecha en que ocurre el evento deportivo.<br>Indicar utilizando el formato dd/mm/yyyy', 'epic'),
                    'id'   => $prefix . 'fecha_evento',
                    'type' => 'text_date'
                ),
                array(
                    'name' => __('Hora del Evento', 'epic'),
                    'desc' => __('Seleccionar/escribir la hora en que ocurre el evento deportivo.<br>Indicar utilizando el formato hh:mm', 'epic'),
                    'id'   => $prefix . 'hora_evento',
                    'type' => 'text_time'
                ),
                array(
                    'name' => __('Pronostico', 'epic'),
                    'desc' => __( 'Escribir que apuesta/pronostico vas a realizar', 'epic' ),
                    'id'   => $prefix . 'pronostico',
                    'type' => 'text'
                ),
                array(
                    'name' => __('Cuota', 'epic'),
                    'desc' => __( 'Escribir la cuota de la apuesta', 'epic' ),
                    'id'   => $prefix . 'cuota',
                    'type' => 'text'
                ),
                array(
                    'name'    => __('Casa de apuestas', 'epic'),
                    'desc'    => __('Seleccionar la casa de apuestas donde haz realizado la apuesta', 'epic'),
                    'id'      => $prefix . 'casa_apuesta',
                    'type'    => 'select',
                    'options' => $bookies
                ),
                array(
                    'name' => __('Stake', 'epic'),
                    'desc' => __( 'Escribir el nivel de confianza en la apuesta', 'epic' ),
                    'id'   => $prefix . 'stake',
                    'type' => 'text'
                ),
                array(
                    'name'    => __('Tipo de apuesta', 'epic'),
                    'desc'    => __('Seleccionar el tipo de apuesta hecha, ya sea un over, under, handicap...', 'epic'),
                    'id'      => $prefix . 'tipo_apuesta',
                    'type'    => 'select',
                    'options' => array(
                        'ganador'   => __('Ganador', 'epic'),
                        'perdedor'  => __('Perdedor', 'epic'),
                        'under'     => __('Under', 'epic'),
                        'over'      => __('Over', 'epic'),
                        'handicap'  => __('Handicap', 'epic'),
                        'resultado' => __('Resultado concreto', 'epic'),
                        'otro'      => __('Otro', 'epic'),
                    ),
                ),
                array(
                    'name' => __('Tipster', 'epic'),
                    'desc' => __( 'Seleccionar el tipster que promueve la apuesta', 'epic' ),
                    'id'   => $prefix . 'tipster',
                    'type' => 'select',
                    'options' => $tipsters
                ),
                array(
                    'name' => __('Competicion', 'epic'),
                    'desc' => __( 'Escribir el nombre de la competencion asociada a la apuesta', 'epic' ),
                    'id'   => $prefix . 'competencicion',
                    'type' => 'text'
                ),
                array(
                    'name' => __('Deporte', 'epic'),
                    'desc' => __( 'Seleccionar el deporte asociado a la apuesta', 'epic' ),
                    'id'   => $prefix . 'deporte',
                    'type' => 'select',
                    'options' => array(
                        'futbol'     => __('Futbol', 'epic'),
                        'tenis'      => __('Tenis', 'epic'),
                        'baloncesto' => __('Baloncesto', 'epic'),
                        'balonmano'  => __('Balonmano', 'epic'),
                        'formula1'   => __('Formula 1', 'epic'),
                        'motos'      => __('Motos', 'epic')
                    ),
                ),
                array(
                    'name'    => __('Resultado', 'epic'),
                    'desc'    => __('Resultado de la apuesta: pendiente, acierto, fallo o nulo.<br>Si el evento aún no se ha resuelto debes dejar el resultado <strong>PENDIENTE</strong>.<br>Cuando el evento se resuelva actualiza el resultado según sea <strong>ACIERTO</strong>, <strong>FALLO</strong> o <strong>NULO</strong>', 'epic'),
                    'id'      => $prefix . 'resultado',
                    'type'    => 'select',
                    'options' => array(
                        'pendiente' => __('Pendiente', 'epic'),
                        'acierto'   => __('Acierto', 'epic'),
                        'fallo'     => __('Fallo', 'epic'),
                        'nulo'      => __('Nulo', 'epic'),
                    ),
                ),
                array(
                    'name' => __('Autor', 'epic'),
                    'desc' => __( 'Escribir la url del profile en Google Plus del autor', 'epic' ),
                    'id'   => $prefix . 'autor',
                    'type' => 'text_url'
                ),
            )
        );

        return $meta_boxes;
    }

    /**
     * Initialize the metabox class.
     */
    function cmb_initialize_cmb_meta_boxes() {

        if ( ! class_exists( 'cmb_Meta_Box' ) )
            require_once dirname(__FILE__). '/cmb/init.php';

    }

    function enqueue_scripts(){
        $screen = get_current_screen();
        if ( "post" == $screen->id ) {
            wp_enqueue_script( $this->plugin_slug . '-admin-metabox-script', plugins_url( 'assets/js/meta-boxes.js', dirname(__FILE__) ), array( 'jquery' ), Tipster_TAP::VERSION );
        }
    }
}








