<?php
/**
 * Represents the view for manage picks information.
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

$tipster_id = null;
if(isset($_POST['_tipstertap_manage_tipsters_metas_nonce']) && wp_verify_nonce($_POST['_tipstertap_manage_tipsters_metas_nonce'], 'form_manage_tipster_metas')){
    $tipster_id = $_POST['_tipstertap_manage_tipsters_tipster'];
    do_action('tipster_tap_update_tipster_metas', $tipster_id);
}

$args = array(
	'post_type'              => 'tipster',
	'post_status'            => 'publish',
	'posts_per_page'         => -1,
	'order'                  => 'ASC',
	'ignore_sticky_posts'    => true
);
$tipsters = get_posts($args)
?>
<div class="wrap">
	<h2><?php printf('%s &raquo; Manage Tipsters', esc_html( get_admin_page_title() )) ; ?></h2>
	<?php settings_errors('tipstertap-manage-tipsters-metas'); ?>
	<div class="card">
        <h3><?php _e('Tipster information', TipsterTap::get_instance()->get_plugin_slug()) ?></h3>
		<form id="form-manage-tipsters-metas" role="form" method="post" action="<?php echo admin_url( 'admin.php?page=' . TipsterTap::get_instance()->get_plugin_slug() . '/manage-tipsters' ) ?>">
			<table class="form-table">
				<tbody>
                    <tr>
                        <td colspan="2">
                            <p><?php _e('Select a tipster and click button to update and view tipster information', TipsterTap::get_instance()->get_plugin_slug()) ?></p>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label for="tipsters">Tipsters</label>
                        </td>
                        <td>
                            <select id="tipsters" name="_tipstertap_manage_tipsters_tipster" class="widefat">
                            <?php foreach ( $tipsters as $tipster ): ?>
                                <?php if($tipster instanceof WP_Post):?>
                                <option value="<?php echo $tipster->ID ?>"><?php echo get_the_title($tipster->ID);?></option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                </tbody>
                <tfoot>
					<tr>
						<td colspan="2">
							<?php wp_nonce_field( 'form_manage_tipster_metas', '_tipstertap_manage_tipsters_metas_nonce' ); ?>
							<button id="manage-tipsters-metas-btn" type="submit" class="button button-primary">
								<?php _e('Update information', TipsterTap::get_instance()->get_plugin_slug()) ?>
							</button>
						</td>
					</tr>
				</tfoot>
			</table>
		</form>
	</div>
	
    <?php if(null !== $tipster_id): ?>
    <div class="card widefat">
        <h3><?php echo get_the_title($tipster_id); ?></h3>
        <?php
        $total_picks = get_post_meta($tipster_id, '_tipster_total_picks', true);
        $total_picks_pendientes = get_post_meta($tipster_id, '_tipster_total_picks_pendientes', true);
        $total_picks_finalizados = get_post_meta($tipster_id, '_tipster_total_picks_finalizados', true);
        $statistics  = get_post_meta($tipster_id, '_tipster_statistics_last', true);
        $yield       = empty($statistics['acumulado']['yield']) ? 0 : (double)$statistics['acumulado']['yield'];
        $win_units   = empty($statistics['acumulado']['unidades_ganadas']) ? 0 : (double)$statistics['acumulado']['unidades_ganadas'];
        $lost_units  = empty($statistics['acumulado']['unidades_perdidas']) ? 0 : (double)$statistics['acumulado']['unidades_perdidas'];
        $beneficio   = empty($statistics['acumulado']['beneficio']) ? 0 : (double)$statistics['acumulado']['beneficio'];
        $rating = empty($statistics['acumulado']['rating']) ? 0 : (double)$statistics['acumulado']['rating'];
        ?>
        <h4>Resumen</h4>
        <table class="form-table">
            <tbody>
                <tr>
                    <td>Beneficio</td>
                    <td><?php echo number_format_i18n($beneficio,2);?></td>
                </tr>
                <tr>
                    <td>Yield</td>
                    <td><?php echo number_format_i18n($yield,2);?> &percnt;</td>
                </tr>
                <tr>
                    <td>Rating</td>
                    <td><?php echo number_format_i18n($rating);?></td>
                </tr>
            </tbody>
        </table>
        
        <h4>Apuestas</h4>
        <table class="form-table">
            <tbody>
                <tr>
                    <td>Pendientes</td>
                    <td><?php echo number_format_i18n($total_picks_pendientes); ?></td>
                </tr>
                <tr>
                    <td>Finalizadas</td>
                    <td><?php echo number_format_i18n($total_picks_finalizados); ?></td>
                </tr>
                <tr>
                    <td>Total</td>
                    <td><?php echo number_format_i18n($total_picks); ?></td>
                </tr>
            </tbody>
        </table>
        <?php
        $months = (integer)get_theme_mod( 'tipster_tap_limit_statistics');
        $months_tipster = (integer)get_post_meta($tipster_id, '_tipster_limit_statistics', true);
        if($months_tipster > 0){
	        $months = $months_tipster;
        }
        ?>
        <h4><?php printf('Estadisticas (ultimos %d meses)', $months); ?></h4>
        <?php $statistics = get_post_meta($tipster_id, '_tipster_statistics_monthly', true); ?>
	    <?php foreach ( $statistics as $year_month => $statistic ) : ?>
        <table class="form-table">
            <thead>
                <tr>
	                <?php $date = new \DateTime($year_month);?>
                    <th><?php echo ucfirst(date_i18n('F, Y', $date->getTimestamp())); ?></th>
                    <th>Mes</th>
                    <th>Acumulado</th>
                </tr>
            </thead>
            <tbody>
            
<!--                <tr>-->
<!--                    --><?php //$date = new \DateTime($year_month);?>
<!--                    <td colspan="3"><strong>--><?php //echo ucfirst(date_i18n('F, Y', $date->getTimestamp())); ?><!--</strong></td>-->
<!--                </tr>-->
                <tr>
                    <td>Aciertos</td>
                    <td><?php echo number_format_i18n($statistic['mes']['aciertos']); ?></td>
                    <td><?php echo number_format_i18n($statistic['acumulado']['aciertos']); ?></td>
                </tr>
                <tr>
                    <td>Fallos</td>
                    <td><?php echo number_format_i18n($statistic['mes']['fallos']); ?></td>
                    <td><?php echo number_format_i18n($statistic['acumulado']['fallos']); ?></td>
                </tr>
                <tr>
                    <td>Nulos</td>
                    <td><?php echo number_format_i18n($statistic['mes']['nulos']); ?></td>
                    <td><?php echo number_format_i18n($statistic['acumulado']['nulos']); ?></td>
                </tr>
                <tr>
                    <td>U. ganadas</td>
                    <td><?php echo number_format_i18n($statistic['mes']['unidades_ganadas']); ?></td>
                    <td><?php echo number_format_i18n($statistic['acumulado']['unidades_ganadas']); ?></td>
                </tr>
                <tr>
                    <td>U. perdidas</td>
                    <td><?php echo number_format_i18n($statistic['mes']['unidades_perdidas']); ?></td>
                    <td><?php echo number_format_i18n($statistic['acumulado']['unidades_perdidas']); ?></td>
                </tr>
                <tr>
                    <td>Beneficios</td>
                    <td><?php echo number_format_i18n($statistic['mes']['beneficio'], 2); ?></td>
                    <td><?php echo number_format_i18n($statistic['acumulado']['beneficio'], 2); ?></td>
                </tr>
                <tr>
                    <td>Yield</td>
                    <td><?php echo number_format_i18n($statistic['mes']['yield'], 2); ?> &percnt;</td>
                    <td><?php echo number_format_i18n($statistic['acumulado']['yield'], 2); ?> &percnt;</td>
                </tr>
                <tr>
                    <td>Rating</td>
                    <td><?php echo number_format_i18n($statistic['mes']['rating']); ?></td>
                    <td><?php echo number_format_i18n($statistic['acumulado']['rating']); ?></td>
                </tr>
            
            </tbody>
        </table>
	    <?php endforeach; ?>
	</div>
    <?php endif; ?>
</div>