<?php
/**
 * Represents the view for upgrade picks information.
 *
 * This includes the header, options, and other information that should provide
 * The User Interface to the end user.
 *
 * @package   TipsterTap
 * @author    Alain Sanchez <luka.ghost@gmail.com>
 * @license   GPL-2.0+
 * @link      http://www.linkedin.com/in/mrbrazzi/
 * @copyright 2014 Alain Sanchez
 */
use TipsterTAP\Frontend\TipsterTap;

global $wpdb, $post;

$tap_bookies = get_option('TAP_BOOKIES');
$bookies     = array();
foreach ( $tap_bookies as $k => $value ) {
    $bookies[$k] = $value['nombre'];
}

$tap_deportes = get_option('TAP_DEPORTES');
$deportes     = array();
foreach ( $tap_deportes as $k => $value ) {
    $deportes[$k] = $value['nombre'];
}
$tap_competiciones = get_option('TAP_COMPETICIONES');
$competiciones     = array();
foreach ( $tap_competiciones as $value ) {
    $competiciones[$value['id']] = $value['nombre'];
}
$tipster_id = $casa_apuesta = $deporte = $competicion = null;

if(isset($_POST['update'])){
    $casa_apuesta = $_POST['bookie'];
    $deporte = $_POST['deporte'];
    $competicion = $_POST['competicion'];

    $tipster_query = "SELECT p.ID, p.post_author".
                     " FROM {$wpdb->posts} AS p".
                     " WHERE p.post_type = 'tipster';";
    $tipsters = $wpdb->get_results($tipster_query, OBJECT);
    foreach ( $tipsters as $tipster ) {
        $autor = $tipster->post_author;
        $tipster_id = $tipster->ID;

        $post_query = "SELECT p.ID".
                      " FROM {$wpdb->posts} AS p".
                      " INNER JOIN {$wpdb->postmeta} AS pm ON p.ID = pm.post_id".
                      " WHERE p.post_author = {$autor} AND ((pm.meta_key = 'resultado' AND pm.meta_value <> 'none') OR (pm.meta_key = 'evento' AND pm.meta_value <> ''))".
                      " GROUP BY p.ID;";
        $post_query_result = $wpdb->get_results($post_query, OBJECT);
        foreach($post_query_result as $p){
            update_post_meta($p->ID, '_post_tipo_publicacion', 'pick');

            $evento = get_post_meta($p->ID, 'evento', true);
            update_post_meta($p->ID, '_pick_evento', $evento);

            $fecha_evento = get_post_meta($p->ID, 'fecha_evento', true);
            update_post_meta($p->ID, '_pick_fecha_evento', $fecha_evento);

            $hora_evento = get_post_meta($p->ID, 'hora_evento', true);
            update_post_meta($p->ID, '_pick_hora_evento', $hora_evento);

            $pronostico = get_post_meta($p->ID, 'pronostico', true);
            update_post_meta($p->ID, '_pick_pronostico', $pronostico);

            $cuota = get_post_meta($p->ID, 'cuota', true);
            update_post_meta($p->ID, '_pick_cuota', $cuota);

            $casa = get_post_meta($p->ID, 'casa', true);
            $casa = !empty($casa) && array_key_exists(strtolower($casa), $bookies) ? $casa : $casa_apuesta;
            update_post_meta($p->ID, '_pick_casa_apuesta', $casa);

            $stake = get_post_meta($p->ID, 'stake', true);
            update_post_meta($p->ID, '_pick_stake', $stake);

            $tipo_apuesta = get_post_meta($p->ID, 'tipo_apuesta', true);
            update_post_meta($p->ID, '_pick_tipo_apuesta', strtolower($tipo_apuesta));

            update_post_meta($p->ID, '_pick_tipster', $tipster_id);

            $competencia = get_post_meta($p->ID, 'competencia', true);
            $competencia = !empty($competencia) && array_key_exists($competencia, $competiciones) ? $competencia : $competicion;
            update_post_meta($p->ID, '_pick_competicion', $competencia);

            update_post_meta($p->ID, '_pick_deporte', $deporte);

            $resultado = get_post_meta($p->ID, 'resultado', true);
            update_post_meta($p->ID, '_pick_resultado', strtolower($resultado));

            $wpdb->update('statistics', array('user_id' => $tipster_id), array('user_id' => $autor), '%d', '%d');
        }
    }

    add_settings_error('upgrade-picks-information', 'form-upgrade-picks-information', __('Actualizacion realizada satisfactoriamente', TipsterTap::get_instance()->get_plugin_slug()), 'updated');
} ?>

<div class="wrap">

    <h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
    <?php settings_errors('upgrade-picks-information', false, true); ?>

    <p><?php _e('Hacer clic en el boton para actualizar la informacion de los metadatos de las publicaciones de tipo Picks', TipsterTap::get_instance()->get_plugin_slug()) ?></p>

    <form id="form-update-picks-information" method="post" action="<?php echo admin_url( 'admin.php?page='.TipsterTap::get_instance()->get_plugin_slug()."/update-picks-information&settings-updated=1" ) ?>">
        <p>
            <label for="bookie"><?php _e('Bookies', TipsterTap::get_instance()->get_plugin_slug()) ?></label>
            <select id="bookie" name="bookie"><?php
            if($bookies){
                foreach($bookies as $k => $v){
                    $selected = ($k == $casa_apuesta) ? 'selected="selected"' : '';?>
                    <option value="<?php echo $k; ?>" <?php echo $selected; ?>><?php echo $v; ?></option><?php
                }
            } ?>
            </select>
        </p>
        <p>
            <label for="deporte"><?php _e('Deporte', TipsterTap::get_instance()->get_plugin_slug()) ?></label>
            <select id="deporte" name="deporte"><?php
            if($deportes){
                foreach($deportes as $k => $v){
                    $selected = ($k == $deporte) ? 'selected="selected"' : '';?>
                    <option value="<?php echo $k; ?>" <?php echo $selected; ?>><?php echo $v; ?></option><?php
                }
            } ?>
            </select>
        </p>
        <p>
            <label for="competicion"><?php _e('Competicion', TipsterTap::get_instance()->get_plugin_slug()) ?></label>
            <select id="competicion" name="competicion"><?php
            if(!empty($competiciones)){
                foreach($competiciones as $k => $v){
                    $selected = ($k == $competicion) ? 'selected="selected"' : '';?>
                    <option value="<?php echo $k; ?>" <?php echo $selected; ?>><?php echo $v; ?></option><?php
                }
            } ?>
            </select>
        </p>
        <input type="hidden" name="update" value="1">
        <p class="submit">
            <input type="submit" id="upgrade" value="<?php _e('Actualizar informacion',  TipsterTap::get_instance()->get_plugin_slug())?> &raquo;"/>
        </p>
    </form>

</div>