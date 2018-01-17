<?php
/**
 * @package   TipsterTap
 * @author    Alain Sanchez <luka.ghost@gmail.com>
 * @license   GPL-2.0+
 * @link      http://www.linkedin.com/in/mrbrazzi/
 * @copyright 2014 Alain Sanchez
 *
 * @wordpress-plugin
 * Plugin Name:       Tipster TAP
 * Plugin URI:       https://todoapuestas.com
 * Description:       Plugin para gestionar tipsters y picks
 * Version:           3.0
 * Author:       Alain Sanchez
 * Author URI:       http://www.linkedin.com/in/mrbrazzi/
 * Text Domain:       tipster-tap
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 * Bitbucket Plugin URI: https://bitbucket.org/tapnetwork/tipster-tap.git
 * Bitbucket Branch: master
 * WordPress-Plugin-Boilerplate: v2.6.1
 */

namespace TipsterTAP;

use TipsterTAP\Frontend\TipsterTap;
use TipsterTAP\Backend\TipsterTapAdmin;
use TipsterTAP\Backend\Common\MetaBoxesPostType;
use TipsterTAP\Common\TipsterPostType;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/*----------------------------------------------------------------------------*
 * Public-Facing Functionality
 *----------------------------------------------------------------------------*/

/*
 */
require_once plugin_dir_path( __FILE__ ) . 'includes/InvalidApiResponseException.php';
require_once plugin_dir_path( __FILE__ ) . 'public/class-tipster-tap.php';

/*
 * Register hooks that are fired when the plugin is activated or deactivated.
 * When the plugin is deleted, the uninstall.php file is loaded.
 */
register_activation_hook( __FILE__, array( 'TipsterTAP\Frontend\TipsterTap', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'TipsterTAP\Frontend\TipsterTap', 'deactivate' ) );

/*
 */
add_action( 'plugins_loaded', array( 'TipsterTAP\Frontend\TipsterTap', 'get_instance' ) );

/*----------------------------------------------------------------------------*
 * Dashboard and Administrative Functionality
 *----------------------------------------------------------------------------*/

/*
 *
 * If you want to include Ajax within the dashboard, change the following
 * conditional to:
 *
 * if ( is_admin() ) {
 *   ...
 * }
 *
 * The code below is intended to to give the lightest footprint possible.
 */
//if ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {
if ( is_admin() ) {

    require_once plugin_dir_path( __FILE__ ) . 'admin/class-tipster-tap-admin.php';
    add_action( 'plugins_loaded', array( 'TipsterTAP\Backend\TipsterTapAdmin', 'get_instance' ) );

    if( !class_exists( 'TipsterTAP\Backend\Common\MetaBoxesPostType' )){
        require_once plugin_dir_path( __FILE__ ) . 'admin/includes/meta-boxes.php';
        add_action( 'plugins_loaded', array( 'TipsterTAP\Backend\Common\MetaBoxesPostType', 'get_instance' ) );
    }
}

if( !class_exists( 'TipsterTAP\Common\TipsterPostType' )){
    require_once plugin_dir_path( __FILE__ ) . 'includes/post-type-tipster.php';
    add_action( 'plugins_loaded', array( 'TipsterTAP\Common\TipsterPostType', 'get_instance' ) );
}
