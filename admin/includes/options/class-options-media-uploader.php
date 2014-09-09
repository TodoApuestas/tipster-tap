<?php

class Options_Media_Uploader{
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
     * Media Uploader Using the WordPress Media Library.
     *
     * Parameters:
     * - string $_id - A token to identify this field (the name).
     * - string $_value - The value of the field, if present.
     * - string $_desc - An optional description of the field.
     */
    public function optionsframework_uploader( $_id, $_value, $_desc = '', $_name = '' ) {

        $optionsframework_settings = get_option( 'options_framework' );

        // Gets the unique option id
        $option_name = $optionsframework_settings['id'];

        $output = '';
        $id = '';
        $class = '';
        $int = '';
        $value = '';
        $name = '';

        $id = strip_tags( strtolower( $_id ) );

        // If a value is passed and we don't have a stored value, use the value that's passed through.
        if ( $_value != '' && $value == '' ) {
            $value = $_value;
        }

        if ($_name != '') {
            $name = $_name;
        }
        else {
            $name = $option_name.'['.$id.']';
        }

        if ( $value ) {
            $class = ' has-file';
        }
        $output .= '<input id="' . $id . '" class="upload' . $class . '" type="text" name="'.$name.'" value="' . $value . '" placeholder="' . __('No file chosen', 'options_framework') .'" />' . "\n";
        if ( $value == '' ) {
            $output .= '<input id="upload-' . $id . '" class="upload-button button" type="button" value="' . __( 'Upload', 'options_framework' ) . '" />' . "\n";
        } else {
            $output .= '<input id="remove-' . $id . '" class="remove-file button" type="button" value="' . __( 'Remove', 'options_framework' ) . '" />' . "\n";
        }

        if ( $_desc != '' ) {
            $output .= '<span class="of-metabox-desc">' . $_desc . '</span>' . "\n";
        }

        $output .= '<div class="screenshot" id="' . $id . '-image">' . "\n";

        if ( $value != '' ) {
            $remove = '<a class="remove-image">Remove</a>';
            $image = preg_match( '/(^.*\.jpg|jpeg|png|gif|ico*)/i', $value );
            if ( $image ) {
                $output .= '<img src="' . $value . '" alt="" />' . $remove;
            } else {
                $parts = explode( "/", $value );
                for( $i = 0; $i < sizeof( $parts ); ++$i ) {
                    $title = $parts[$i];
                }

                // No output preview if it's not an image.
                $output .= '';

                // Standard generic output if it's not an image.
                $title = __( 'View File', 'options_framework' );
                $output .= '<div class="no-image"><span class="file_link"><a href="' . $value . '" target="_blank" rel="external">'.$title.'</a></span></div>';
            }
        }
        $output .= '</div>' . "\n";
        return $output;
    }

    /**
     * Enqueue scripts for file uploader
     */
    public function optionsframework_media_scripts(){
        wp_enqueue_media();
        wp_register_script( 'of-media-uploader', TIPSTER_TAP_OPTIONS_FRAMEWORK_DIRECTORY .'js/media-uploader.js', array( 'jquery' ) );
        wp_enqueue_script( 'of-media-uploader' );
        wp_localize_script( 'of-media-uploader', 'optionsframework_l10n', array(
                'upload' => __( 'Upload', 'options_framework' ),
                'remove' => __( 'Remove', 'options_framework' )
            ) );
    }

}