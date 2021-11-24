<?php
/**
 * Represents the view for troubleshooting some information requested from TodoApuestas API.
 *
 * This includes the header, options, and other information that should provide
 * The User Interface to the end user.
 *
 * @package   TipsterTap
 * @author    Alain Sanchez <luka.ghost@gmail.com>
 * @license   GPL-2.0+
 * @link      http://www.linkedin.com/in/mrbrazzi/
 * @copyright 2021 Alain Sanchez
 * @since 4.2.2
 */

use TipsterTAP\Frontend\TipsterTap;

$updateCache = false;
$forceRequest = null;
if(isset($_POST['_tipstertap_troubleshoot_nonce']) && wp_verify_nonce($_POST['_tipstertap_troubleshoot_nonce'], 'form_troubleshoot')) {
    $forceRequest = sanitize_key($_POST['force_request']);
    do_action( 'qm/debug', $_POST );
    $updateCache = array_key_exists('update_cache', $_POST) && $_POST['update_cache'] === 'yes';
    if($updateCache) {
        switch ($forceRequest) {
            case 'rest_client_tap_request_bookies':
                delete_transient('tap_bookies');
                break;
            case 'rest_client_tap_request_sports':
                delete_transient('tap_deportes');
                break;
            case 'rest_client_tap_request_competitions':
                delete_transient('tap_competiciones');
                break;
            default:
                break;
        }
    }
    if(in_array($forceRequest, array('rest_client_tap_request_bookies', 'rest_client_tap_request_sports', 'rest_client_tap_request_competitions'))) {
        do_action($forceRequest);
    }
}
?>
<div class="wrap">
    <h2><?php printf('%s &raquo; Troubleshoot', esc_html( get_admin_page_title() )) ; ?></h2>
    <?php settings_errors('tap-troubleshoot'); ?>
    <div class="card" style="max-width: 80%;">
        <h3><?php _e('Troubleshooting', TipsterTap::get_instance()->get_plugin_slug()) ?></h3>
        <form id="troubleshoot-form" role="form" method="post" action="<?php echo admin_url( 'admin.php?page=' . TipsterTap::get_instance()->get_plugin_slug() . '/troubleshoot' ) ?>">
            <fieldset>
                <table class="form-table">
                    <tbody>
                        <tr>
                            <td>
                                <input type="checkbox" id="update-cache" name="update_cache" value="yes" <?php checked(true, $updateCache) ?> >
                                <label for="update-cache"><?php _e('Update cache', TipsterTap::get_instance()->get_plugin_slug()); ?>. </label>
                                <label for="force-request"><?php _e('Force request for', TipsterTap::get_instance()->get_plugin_slug()); ?></label>
                                <select id="force-request" name="force_request" required>
                                    <option value="rest_client_tap_request_bookies" <?php selected('rest_client_tap_request_bookies', $forceRequest); ?> >
                                        <?php _e('Bookies', TipsterTap::get_instance()->get_plugin_slug()); ?>
                                    </option>
                                    <option value="rest_client_tap_request_sports" <?php selected('rest_client_tap_request_sports', $forceRequest); ?> >
                                        <?php _e('Sports', TipsterTap::get_instance()->get_plugin_slug()); ?>
                                    </option>
                                    <option value="rest_client_tap_request_competitions" <?php selected('rest_client_tap_request_competitions', $forceRequest); ?> >
                                        <?php _e('Competitions', TipsterTap::get_instance()->get_plugin_slug()); ?>
                                    </option>
                                </select>
                                <?php wp_nonce_field( 'form_troubleshoot', '_tipstertap_troubleshoot_nonce' ); ?>
                                <button id="btnSubmit" type="submit" class="button button-primary">
                                    <?php _e('Execute', TipsterTap::get_instance()->get_plugin_slug()) ?>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </fieldset>
        </form>
    </div>
</div>
<div class="wrap">
<?php
if ($forceRequest) {
    switch ($forceRequest) {
        case 'rest_client_tap_request_bookies':
            include_once __DIR__ . '/troubleshoot_request_bookies.php';
            break;
        case 'rest_client_tap_request_sports':
            include_once __DIR__ . '/troubleshoot_request_sports.php';
            break;
        case 'rest_client_tap_request_competitions':
            include_once __DIR__ . '/troubleshoot_request_competitions.php';
            break;
        default:
            break;
    }
}
?>
</div>
