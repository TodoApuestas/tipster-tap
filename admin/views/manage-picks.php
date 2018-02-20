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

if(isset($_POST['_tipstertap_manage_picks_migration_nonce']) && wp_verify_nonce($_POST['_tipstertap_manage_picks_migration_nonce'], 'form_manage_picks_migration')){
    do_action('tipster_tap_execute_pick_migration');
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
	<h2><?php printf('%s &raquo; Manage Picks', esc_html( get_admin_page_title() )) ; ?></h2>
	
	<div class="card widefat" style="max-width: 80%;">
        <h3><?php _e('Picks list', TipsterTap::get_instance()->get_plugin_slug()) ?></h3>
        <form id="picks-form-filter">
            <fieldset>
                <legend>Filtro de picks por Tipster y Mes</legend>
                <table class="form-table">
                    <tbody>
                        <tr>
                            <td>
                                <label for="tipsters">Tipsters
                                    <select id="tipsters" name="tipster" required>
                                        <?php foreach ( $tipsters as $tipster ): ?>
                                            <?php if($tipster instanceof WP_Post):?>
                                                <option value="<?php echo $tipster->ID ?>"><?php echo get_the_title($tipster->ID);?></option>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </select>
                                </label>
                                <label for="yearmonth">Fecha
                                    <input id="yearmonth" type="text" required>
                                </label>
                                <button id="btnFilter" type="button" class="button button-primary">
                                    <?php _e('Filter', TipsterTap::get_instance()->get_plugin_slug()) ?>
                                </button>
                                <label id="manage-picks-spinner">Cargando...</label>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </fieldset>
            
        </form>
        <table id="dt-picks" class="table table-striped table-hover table-bordered table-condensed dt-responsive nowrap" width="100%" cellspacing="0">
            <thead>
                <tr>
                    <th>&nbsp;</th>
                    <th>Fecha</th>
                    <th>Evento</th>
                    <th>Cuota</th>
                    <th>Stake</th>
                    <th>Ganancia</th>
                    <th>Resultado</th>
                    <th>&nbsp;</th>
                </tr>
            </thead>
        </table>
        
	</div>
	
	<?php settings_errors('tipstertap-manage-picks-migration'); ?>
    <div class="card">
        <h3><?php _e('Migrate picks', TipsterTap::get_instance()->get_plugin_slug()) ?></h3>
        <form id="form-manage-picks-migration" role="form" method="post" action="<?php echo admin_url( 'admin.php?page=' . TipsterTap::get_instance()->get_plugin_slug() . '/manage-picks' ) ?>">
            <table class="form-table">
                <tbody>
                    <tr>
                        <td>
                            <label for="manage-picks-migration-btn"><?php _e('Click button to execute migration', TipsterTap::get_instance()->get_plugin_slug()) ?></label>
                        </td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <td>
                            <?php wp_nonce_field( 'form_manage_picks_migration', '_tipstertap_manage_picks_migration_nonce' ); ?>
                            <button id="manage-picks-migration-btn" type="submit" class="button button-primary">
                                <?php _e('Migrate', TipsterTap::get_instance()->get_plugin_slug()) ?>
                            </button>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </form>
    </div>
</div>