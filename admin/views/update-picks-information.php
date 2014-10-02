<?php
/**
 * Represents the view for upgrade picks information.
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

<?php
global $wpdb, $post;

$tipster_tap_bookies = get_option('tipster_tap_bookies');
$tipster_tap_deportes = get_option('tipster_tap_deportes');
$tipster_tap_competiciones = get_option('tipster_tap_competiciones');
$tipster_id = $casa_apuesta = $deporte = $competicion = null;

if(isset($_POST['update']) && isset($_POST['tipster'])){
    $tipster_id = $_POST['tipster'];
    $casa_apuesta = $_POST['bookie'];
    $deporte = $_POST['deporte'];
    $competicion = $_POST['competicion'];

    $post_query = "SELECT p.ID, p.post_author".
        " FROM ".$wpdb->posts." AS p".
        " INNER JOIN ".$wpdb->postmeta." AS pm ON p.ID = pm.post_id".
        " WHERE (pm.meta_key = 'resultado' AND pm.meta_value <> 'none') OR (pm.meta_key = 'evento' AND pm.meta_value <> '')".
        " GROUP BY p.ID;";
    $post_query_result = $wpdb->get_results($post_query, OBJECT);

    foreach($post_query_result as $p){
        update_post_meta($p->ID, '_post_tipo_publicacion', 'pick');

        $autor = $p->post_author;
        $tipster_query = "SELECT p.ID ".
            " FROM ".$wpdb->posts." AS p".
            " WHERE p.post_type = 'tipster' AND p.post_author = ".$autor.";";
        $tipster = $wpdb->get_row($tipster_query, OBJECT);
        if(!is_null($tipster)){
            $tipster_id = $tipster->ID;
        }

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
        $casa = !empty($casa) && array_key_exists($casa, $tipster_tap_bookies) ? $casa : $competicion;
        update_post_meta($p->ID, '_pick_casa_apuesta', $casa_apuesta);

        $stake = get_post_meta($p->ID, 'stake', true);
        update_post_meta($p->ID, '_pick_stake', $stake);

        $tipo_apuesta = get_post_meta($p->ID, 'tipo_apuesta', true);
        update_post_meta($p->ID, '_pick_tipo_apuesta', strtolower($tipo_apuesta));

        update_post_meta($p->ID, '_pick_tipster', $tipster_id);

        $competencia = get_post_meta($p->ID, 'competencia', true);
        $competencia = !empty($competencia) && array_key_exists($competencia, $tipster_tap_competiciones) ? $competencia : $competicion;
        update_post_meta($p->ID, '_pick_competicion', $competencia);

        update_post_meta($p->ID, '_pick_deporte', $deporte);

        $resultado = get_post_meta($p->ID, 'resultado', true);
        update_post_meta($p->ID, '_pick_resultado', strtolower($resultado));
    }

    add_settings_error('upgrade-picks-information', 'form-upgrade-picks-information', __('Actualizacion realizada satisfactoriamente', Tipster_TAP::get_instance()->get_plugin_slug()), 'updated');
} ?>

<div class="wrap">

    <h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
    <?php settings_errors('upgrade-picks-information', false, true); ?>

    <p><?php _e('Hacer clic en el boton para actualizar la informacion de los metadatos de las publicaciones de tipo Picks', Tipster_TAP::get_instance()->get_plugin_slug()) ?></p>

    <form id="form-update-picks-information" method="post" action="<?php echo admin_url( 'admin.php?page='.Tipster_TAP::get_instance()->get_plugin_slug()."/update-picks-information&settings-updated=1" ) ?>">
        <p>
            <label for="tipster"><?php _e('Tipster', Tipster_TAP::get_instance()->get_plugin_slug()) ?></label>
            <select id="tipster" name="tipster"><?php
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
                    $selected = ($tipster->ID == $tipster_id) ? 'selected="selected"' : '';?>
                    <option value="<?php echo $tipster->ID; ?>" <?php echo $selected; ?>><?php echo $tipster->post_title; ?></option><?php
                }
            }
            wp_reset_postdata(); ?>
            </select>
        </p>
        <p>
            <label for="bookie"><?php _e('Bookies', Tipster_TAP::get_instance()->get_plugin_slug()) ?></label>
            <select id="bookie" name="bookie"><?php
            if($tipster_tap_bookies){
                foreach($tipster_tap_bookies as $k => $v){
                    $selected = ($k == $casa_apuesta) ? 'selected="selected"' : '';?>
                    <option value="<?php echo $k; ?>" <?php echo $selected; ?>><?php echo $v['nombre']; ?></option><?php
                }
            } ?>
            </select>
        </p>
        <p>
            <label for="deporte"><?php _e('Deporte', Tipster_TAP::get_instance()->get_plugin_slug()) ?></label>
            <select id="deporte" name="deporte"><?php
            if($tipster_tap_deportes){
                foreach($tipster_tap_deportes as $k => $v){
                    $selected = ($k == $deporte) ? 'selected="selected"' : '';?>
                    <option value="<?php echo $k; ?>" <?php echo $selected; ?>><?php echo $v['nombre']; ?></option><?php
                }
            } ?>
            </select>
        </p>
        <p>
            <label for="competicion"><?php _e('Competicion', Tipster_TAP::get_instance()->get_plugin_slug()) ?></label>
            <select id="competicion" name="competicion"><?php
            if($tipster_tap_competiciones){
                foreach($tipster_tap_competiciones as $k => $v){
                    $selected = ($k == $competicion) ? 'selected="selected"' : '';?>
                    <option value="<?php echo $k; ?>" <?php echo $selected; ?>><?php echo $v['nombre']; ?></option><?php
                }
            } ?>
            </select>
        </p>
        <input type="hidden" name="update" value="1">
        <p class="submit">
            <input type="submit" id="upgrade" value="<?php _e('Actualizar informacion',  Tipster_TAP::get_instance()->get_plugin_slug())?> &raquo;"/>
        </p>
    </form>

</div>