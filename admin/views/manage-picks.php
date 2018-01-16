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

?>
<div class="wrap">
	<h2><?php printf('%s &raquo; Manage Picks', esc_html( get_admin_page_title() )) ; ?></h2>
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
	<div class="widefat">
	
	</div>
</div>