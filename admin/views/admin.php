<?php
/**
 * Represents the view for the administration dashboard.
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

?>

<div class="wrap">

	<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
    <div class="card">
        <h3><?php _e('Tipster information', TipsterTap::get_instance()->get_plugin_slug()) ?></h3>
        <p>
            <a href="<?php echo admin_url( 'admin.php?page=' . TipsterTap::get_instance()->get_plugin_slug() . '/manage-tipsters' ) ?>">
	            <?php _e('View more', TipsterTap::get_instance()->get_plugin_slug()) ?>
            </a>
        </p>
    </div>
    
    <div class="card">
        <h3><?php _e('Migrate Picks', TipsterTap::get_instance()->get_plugin_slug()) ?></h3>
        <p>
            <a href="<?php echo admin_url( 'admin.php?page=' . TipsterTap::get_instance()->get_plugin_slug() . '/manage-picks' ) ?>">
                <?php _e('View more', TipsterTap::get_instance()->get_plugin_slug()) ?>
            </a>
        </p>
    </div>

    <div class="card">
        <h3><?php _e('Troubleshoot', TipsterTap::get_instance()->get_plugin_slug()) ?></h3>
        <p>
            <a href="<?php echo admin_url( 'admin.php?page=' . TipsterTap::get_instance()->get_plugin_slug() . '/troubleshoot' ) ?>">
                <?php _e('View more', TipsterTap::get_instance()->get_plugin_slug()) ?>
            </a>
        </p>
    </div>
    
    <div class="card">
        <h3><?php _e('FAQs', TipsterTap::get_instance()->get_plugin_slug()) ?></h3>
        <p><?php _e('Under construction', TipsterTap::get_instance()->get_plugin_slug()) ?></p>
    </div>
    
</div>
