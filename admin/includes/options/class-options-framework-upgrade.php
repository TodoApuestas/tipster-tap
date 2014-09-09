<?php

class OptionsFramework_Upgrade{

    /**
     * Instance of this class.
     *
     * @since    1.0.0
     *
     * @var      object
     */
    protected static $instance = null;

    /**
     * Initialize the plugin by loading admin scripts & styles and adding a
     * settings page and menu.
     *
     * @since     1.0.0
     */
    private function __construct() {

    }

    /**
     * Return an instance of this class.
     *
     * @since     1.0.0
     *
     * @return    object    A single instance of this class.
     */
    public static function get_instance() {

        /*
         * @TODO :
         *
         * - Uncomment following lines if the admin class should only be available for super admins
         */
        /* if( ! is_super_admin() ) {
            return;
        } */

        // If the single instance hasn't been set, set it now.
        if ( null == self::$instance ) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Updates Options Framework Data
     *
     * @package     Options Framework
     * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
     * @since       1.5
     */
    public function optionsframework_upgrade_routine() {
        $this->optionsframework_update_to_version_1_5();
        $this->optionsframework_update_version();
    }

    /**
     * Media uploader code changed in Options Framework 1.5
     * and no longer uses a custom post type.
     *
     * Function removes the post type 'options_framework'
     * Media attached to the post type remains in the media library
     *
     * @access      public
     * @since       1.5
     * @return      void
     */
    function optionsframework_update_to_version_1_5() {
        register_post_type( 'options_framework', array(
                'labels' => array(
                    'name' => __( 'Plugin Options Media', 'options_framework' ),
                ),
                'show_ui' => false,
                'rewrite' => false,
                'show_in_nav_menus' => false,
                'public' => false
            ) );

        // Get all the optionsframework post type
        $query = new WP_Query( array(
            'post_type' => 'options_framework',
            'numberposts' => -1,
        ) );

        while ( $query->have_posts() ) :
            $query->the_post();
            echo the_ID();
            $attachments = get_children( array(
                    'post_parent' => the_ID(),
                    'post_type' => 'attachment'
                )
            );
            if ( !empty( $attachments ) ) {
                // Unassign each of the attachments from the post
                foreach ( $attachments as $attachment ) {
                    wp_update_post( array(
                            'ID' => $attachment->ID,
                            'post_parent' => 0
                        )
                    );
                }
            }
            wp_delete_post( the_ID(), true);
        endwhile;

        wp_reset_postdata();
    }

    /**
     * Updates Options Framework version in the database
     *
     * @access      public
     * @since       1.5
     * @return      void
     */
    function optionsframework_update_version() {
        $optionsframework_settings = get_option( 'options_framework' );
        $optionsframework_settings['version'] = '1.5';
        update_option( 'options_framework', $optionsframework_settings );
    }
}
