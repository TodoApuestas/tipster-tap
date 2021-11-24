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

$tap_competiciones = get_option('TAP_COMPETICIONES');
$competiciones = array();
if($tap_competiciones){
    foreach($tap_competiciones as $v){
        try {
            if (is_array($v) && !empty($v['nombre'])) {
                $competiciones[$v['id']] = $v['nombre'];
            }
        } catch (\Exception $e) {
            continue;
        }
    }
} ?>
<div id="col-container" class="wp-clearfix">
    <div id="col-left">
        <div class="col-wrap">
            <div>
                <h3><?php _e('Competitions', TipsterTap::get_instance()->get_plugin_slug()) ?></h3>
                <table class="wp-list-table widefat striped table-view-list">
                    <thead>
                        <tr>
                            <th style="text-align: left;"><?php _e('ID', TipsterTap::get_instance()->get_plugin_slug()) ?></th>
                            <th style="text-align: left;"><?php _e('Name', TipsterTap::get_instance()->get_plugin_slug()) ?></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach($competiciones as $k => $v): ?>
                        <tr>
                            <td><?php echo $k?></td>
                            <td><?php echo $v?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div id="col-right">
        <div class="col-wrap">
            <?php
            $transient = 'tap_competiciones';
            include_once __DIR__ . '/troubleshoot_cache_information.php';
            ?>
        </div>
    </div>
</div>