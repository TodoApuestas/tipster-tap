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
        // Start with an underscore to hide fields from custom fields list
        $prefix = '_pick_';

        $meta_boxes['tipster_informacion_general'] = array(
            'id'         => 'tipster_informacion_general',
            'title'      => __( 'Informacion General', 'epic' ),
            'pages'      => array( 'pick' ), // Tells CMB to use user_meta vs post_meta
            'show_names' => true,
            'cmb_styles' => true, // Show cmb bundled styles.. not needed on user profile page
            'fields'     => array(
                array(
                    'name' => __('Evento', 'epic'),
                    'desc' => __( 'Evento deportivo, social o lo que sea que permita una apuesta', 'epic' ),
                    'id'   => $prefix . 'evento',
                    'type' => 'text'
                ),
                array(
                    'name' => __('Fecha del Evento', 'epic'),
                    'desc' => __('En que fecha ocurre el evento deportivo. Indicar con dd/mm/yyyy', 'epic'),
                    'id'   => $prefix . 'fecha_evento',
                    'type' => 'text_date'
                ),
                array(
                    'name' => __('Hora del Evento', 'epic'),
                    'desc' => __('En que hora ocurre el evento deportivo. Indicar con hh:mm', 'epic'),
                    'id'   => $prefix . 'hora_evento',
                    'type' => 'text_time'
                ),
                array(
                    'name' => __('Pronostico', 'epic'),
                    'desc' => __( 'Que apuesta/pronóstico vas a realizar', 'epic' ),
                    'id'   => $prefix . 'pronostico',
                    'type' => 'text'
                ),
                array(
                    'name' => __('Cuota', 'epic'),
                    'desc' => __( 'Cuota de la apuesta', 'epic' ),
                    'id'   => $prefix . 'cuota',
                    'type' => 'text'
                ),
                array(
                    'name'    => __('Casa de apuestas', 'epic'),
                    'desc'    => __('Casa de apuestas donde has realizado la apuesta', 'epic'),
                    'id'      => $prefix . 'casa_apuesta',
                    'type'    => 'select',
                    'options' => array(
                        'standard' => __('Option One', 'epic'),
                        'custom'   => __('Option Two', 'epic'),
                        'none'     => __('Option Three', 'epic'),
                    ),
                ),
                array(
                    'name' => __('Stake', 'epic'),
                    'desc' => __( 'Stake, nivel de confianza en la apuesta', 'epic' ),
                    'id'   => $prefix . 'stake',
                    'type' => 'text'
                ),
                array(
                    'name'    => __('Tipo de apuesta', 'epic'),
                    'desc'    => __('Tipo de apuesta hecha, ya sea un over, under, handicap...', 'epic'),
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
                    'name' => __('Competencia', 'epic'),
                    'desc' => __( 'Competencia de la apuesta', 'epic' ),
                    'id'   => $prefix . 'competencia',
                    'type' => 'text'
                ),
                array(
                    'name'    => __('Resultado', 'epic'),
                    'desc'    => __('Resultado de la apuesta: pendiente, acierto, fallo o nulo.<br>Si el evento aún no se ha resuelto debes dejar el resultado <strong>PENDIENTE</strong>.<br>Cuando el evento se resuelva actualiza el resultado según sea <strong>ACIERTO</strong>, <strong>FALLO</strong> o <strong>NULO</strong>', 'epic'),
                    'id'      => $prefix . 'resultado',
                    'type'    => 'select',
                    'options' => array(
                        'pendiente'   => __('Pendiente', 'epic'),
                        'acierto'  => __('Acierto', 'epic'),
                        'fallo'     => __('Fallo', 'epic'),
                        'nulo'      => __('Nulo', 'epic'),
                    ),
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
}








