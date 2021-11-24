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

$transient_option = get_transient($transient);
$transient_timeout = '_transient_timeout_' . $transient;
$timeout = get_option($transient_timeout);
?>
<div class="">
    <h3><?php _e('Cache information', TipsterTap::get_instance()->get_plugin_slug()) ?></h3>
    <table class="wp-list-table widefat table-view-list">
        <tbody>
        <tr>
            <th style="vertical-align: middle;">Expires at</th>
            <td style="vertical-align: middle;"><?php
                if ( false !== $timeout && $timeout < time() ) {
                    _e('EXPIRED', TipsterTap::get_instance()->get_plugin_slug());
                } else {
                    echo date('d-m-Y H:m', $timeout);
                }
                ?></td>
        </tr>
        <tr>
            <th style="vertical-align: top;">Details</th>
            <td style="vertical-align: top;"><pre><?php echo json_encode($transient_option, JSON_PRETTY_PRINT);?></pre></td>
        </tr>
        </tbody>
    </table>
</div>