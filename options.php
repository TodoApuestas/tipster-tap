<?php

class Tipster_TAP_Options{
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
        add_action('optionsframework_custom_scripts', array($this, 'optionsframework_custom_scripts'));
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
     * A unique identifier is defined to store the options in the database and reference them from the theme.
     * By default it uses the theme name, in lowercase and without spaces, but this can be changed if needed.
     * If the identifier changes, it'll appear as if the options have been reset.
     */
    function optionsframework_option_name() {

        // This gets the theme name from the stylesheet
        $plugin_name = Options_Framework::get_instance()->get_plugin_slug();

        $optionsframework_settings = get_option( 'options_framework' );
        $optionsframework_settings['id'] = $plugin_name;
        update_option( 'options_framework', $optionsframework_settings );
    }

    /**
     * Defines an array of options that will be used to generate the settings page and be saved in the database.
     * When creating the 'id' fields, make sure to use all lowercase and no spaces.
     *
     * If you are making your theme translatable, you should replace 'options_framework_theme'
     * with the actual text domain for your theme.  Read more:
     * http://codex.wordpress.org/Function_Reference/load_theme_textdomain
     */
    function optionsframework_options() {

        $options = array();

        $options[] = array(
            'name' => __('Datos Iniciales', 'options_framework'),
            'type' => 'heading');

        $options[] = array(
            'name' => __('Incluir datos iniciales', 'options_framework'),
            'desc' => __('Seleccionar si se utilizaran o no valores iniciales para realizar los calculos.', 'options_framework_theme'),
            'id' => 'tipster_tap_incluir_datos_iniciales',
            'std' => '0',
            'type' => 'radio',
            'options' => array(
                '0' => __('NO', 'options_framework'),
                '1' => __('SI', 'options_framework')
            ));

        $options[] = array(
            'name' => __('Aciertos', 'options_framework'),
            'desc' => __('Escribir la cantidad inicial de aciertos.', 'options_framework'),
            'id' => 'tipster_tap_inicial_aciertos',
            'std' => '0',
            'class' => 'mini',
            'type' => 'text');

        $options[] = array(
            'name' => __('Fallos', 'options_framework'),
            'desc' => __('Escribir la cantidad inicial de fallos.', 'options_framework'),
            'id' => 'tipster_tap_inicial_fallos',
            'std' => '0',
            'class' => 'mini',
            'type' => 'text');

        $options[] = array(
            'name' => __('Nulos', 'options_framework'),
            'desc' => __('Escribir la cantidad inicial de valores nulos.', 'options_framework'),
            'id' => 'tipster_tap_inicial_nulos',
            'std' => '0',
            'class' => 'mini',
            'type' => 'text');

        $options[] = array(
            'name' => __('Unidades jugadas', 'options_framework'),
            'desc' => __('Escribir la cantidad inicial de unidades jugadas.', 'options_framework'),
            'id' => 'tipster_tap_inicial_unidades_jugadas',
            'std' => '0',
            'class' => 'mini',
            'type' => 'text');

        $options[] = array(
            'name' => __('Unidades ganadas', 'options_framework'),
            'desc' => __('Escribir la cantidad inicial de unidades ganadas.', 'options_framework'),
            'id' => 'tipster_tap_inicial_unidades_ganadas',
            'std' => '0',
            'class' => 'mini',
            'type' => 'text');

        $options[] = array(
            'name' => __('Unidades perdidas', 'options_framework'),
            'desc' => __('Escribir la cantidad inicial de unidades perdidas.', 'options_framework'),
            'id' => 'tipster_tap_inicial_unidades_perdidas',
            'std' => '0',
            'class' => 'mini',
            'type' => 'text');

        return $options;
    }

    /**
     * Custom scripts.
     */
    function optionsframework_custom_scripts() { ?>

        <script type="text/javascript">
            jQuery(document).ready(function($) {

                function check_incluir_datos_iniciales(){
                    var datos_iniciales = $('input.of-radio:checked').val();
                    if(parseInt(datos_iniciales)){
                        jQuery("#section-tipster_tap_inicial_aciertos, #section-tipster_tap_inicial_fallos, #section-tipster_tap_inicial_nulos, #section-tipster_tap_inicial_unidades_jugadas, #section-tipster_tap_inicial_unidades_ganadas, #section-tipster_tap_inicial_unidades_perdidas").show();
                    }else{
                        jQuery("#section-tipster_tap_inicial_aciertos, #section-tipster_tap_inicial_fallos, #section-tipster_tap_inicial_nulos, #section-tipster_tap_inicial_unidades_jugadas, #section-tipster_tap_inicial_unidades_ganadas, #section-tipster_tap_inicial_unidades_perdidas").hide();
                    }
                }

                jQuery('input.of-radio').on('click', function() {
                    check_incluir_datos_iniciales();
                });

                check_incluir_datos_iniciales();

            });
        </script>

    <?php
    }
}

