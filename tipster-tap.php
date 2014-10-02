<?php
/**
 * The WordPress Plugin Boilerplate.
 *
 * A foundation off of which to build well-documented WordPress plugins that
 * also follow WordPress Coding Standards and PHP best practices.
 *
 * @package   Tipster_TAP
 * @author    Alain Sanchez <asanchezg@inetzwerk.com>
 * @license   GPL-2.0+
 * @link      http://www.inetzwerk.com
 * @copyright 2014 Alain Sanchez
 *
 * @wordpress-plugin
 * Plugin Name:       Tipster TAP
 * Plugin URI:       http://www.todoapuestas.org
 * Description:       Plugin para gestionar apuestas
 * Version:           1.1.2
 * Author:       Alain Sanchez
 * Author URI:       http://www.inetzwerk.com
 * Text Domain:       tipster-tap
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 * GitHub Plugin URI: https://github.com/<owner>/<repo>
 * WordPress-Plugin-Boilerplate: v2.6.1
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/*----------------------------------------------------------------------------*
 * Public-Facing Functionality
 *----------------------------------------------------------------------------*/

/*
 * @TODO:
 *
 * - replace `class-tipster-tap.php` with the name of the plugin's class file
 *
 */
require_once( plugin_dir_path( __FILE__ ) . 'public/class-tipster-tap.php' );

/*
 * Register hooks that are fired when the plugin is activated or deactivated.
 * When the plugin is deleted, the uninstall.php file is loaded.
 *
 * @TODO:
 *
 * - replace Tipster_TAP with the name of the class defined in
 *   `class-tipster-tap.php`
 */
register_activation_hook( __FILE__, array( 'Tipster_TAP', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Tipster_TAP', 'deactivate' ) );

/*
 * @TODO:
 *
 * - replace Tipster_TAP with the name of the class defined in
 *   `class-tipster-tap.php`
 */
add_action( 'plugins_loaded', array( 'Tipster_TAP', 'get_instance' ) );

/*----------------------------------------------------------------------------*
 * Dashboard and Administrative Functionality
 *----------------------------------------------------------------------------*/

/*
 * @TODO:
 *
 * - replace `class-tipster-tap-admin.php` with the name of the plugin's admin file
 * - replace Tipster_TAP_Admin with the name of the class defined in
 *   `class-tipster-tap-admin.php`
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
if ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {

    require_once( plugin_dir_path( __FILE__ ) . 'admin/class-tipster-tap-admin.php' );
    add_action( 'plugins_loaded', array( 'Tipster_TAP_Admin', 'get_instance' ) );

    if( !class_exists('Meta_Boxes_Post_Type')){
        require_once plugin_dir_path( __FILE__ ) . 'admin/includes/meta-boxes.php';
        add_action( 'plugins_loaded', array( 'Meta_Boxes_Post_Type', 'get_instance' ) );
    }
}

if( !class_exists('Tipster_Post_Type')){
    require_once plugin_dir_path( __FILE__ ) . 'includes/post-type-tipster.php';
    add_action( 'plugins_loaded', array( 'Tipster_Post_Type', 'get_instance' ) );
}
