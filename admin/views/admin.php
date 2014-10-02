<?php
/**
 * Represents the view for the administration dashboard.
 *
 * This includes the header, options, and other information that should provide
 * The User Interface to the end user.
 *
 * @package   Tipster_TAP
 * @author    Alain Sanchez <asanchezg@inetzwerk.com>
 * @license   GPL-2.0+
 * @link      http://www.inetzwerk.com
 * @copyright 2014 Alain Sanchez
 */
?>

<div class="wrap">

	<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>

    <p><?php _e('Debes rellenar aqui las estadisticas de tus picks. Una vez lo hayas rellenado deberas elegir si permitir que a partir de ahora el calculo sea automatico (incluyendo los campos que indicamos mas abajo al crear el Pick) o manual, cambiando cada vez que lo necesites el valor de estadisticas.', Tipster_TAP::get_instance()->get_plugin_slug()) ?></p>
    <p><?php _e('Por tanto para tener las estadisticas de los Tipster actualizadas puedes', Tipster_TAP::get_instance()->get_plugin_slug()); ?>:</p>
    <ul>
        <li>- <?php _e('Al crear un nuevo Pick, en el post que creais incluir en los campos que aparecen en la sección Pick (un poco mas abajo de la zona donde incluis el contenido/explicación), rellenar los campos: Categoria, Tipster, Cuota, Stake, Resultado', Tipster_TAP::get_instance()->get_plugin_slug())?></li>
        <li>- <?php printf( __( 'Acceder a la pagina de <a href="%s">Opciones</a> y cambiar los datos, empezando por la seccion <em>Incluir datos iniciales</em> seleccionando la opcion <em>Si</em>.', Tipster_TAP::get_instance()->get_plugin_slug()), admin_url( 'admin.php?page='.Tipster_TAP::get_instance()->get_plugin_slug().'/update-picks-information' )  ); ?></li>
    </ul>

</div>
