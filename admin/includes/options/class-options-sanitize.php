<?php
/**
 * Created by PhpStorm.
 * User: brazzi
 * Date: 8/09/14
 * Time: 0:03
 */

class Options_Sanitize {
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
        /* Text */
        add_filter( 'of_sanitize_text', 'sanitize_text_field' );

        /* Password */
        add_filter( 'of_sanitize_password', 'sanitize_text_field' );

        /* Textarea */
        add_filter( 'of_sanitize_textarea', array($this, 'of_sanitize_textarea') );

        /* Select */
        add_filter( 'of_sanitize_select', array($this, 'of_sanitize_enum'), 10, 2);

        /* Radio */
        add_filter( 'of_sanitize_radio', array($this, 'of_sanitize_enum'), 10, 2);

        /* Images */
        add_filter( 'of_sanitize_images', array($this, 'of_sanitize_enum'), 10, 2);

        /* Checkbox */
        add_filter( 'of_sanitize_checkbox', array($this, 'of_sanitize_checkbox') );

        /* Multicheck */
        add_filter( 'of_sanitize_multicheck', array($this, 'of_sanitize_multicheck'), 10, 2 );

        /* Color Picker */
        add_filter( 'of_sanitize_color', array($this, 'of_sanitize_hex') );

        /* Uploader */
        add_filter( 'of_sanitize_upload', array($this, 'of_sanitize_upload') );

        /* Editor */
        add_filter( 'of_sanitize_editor', array($this, 'of_sanitize_editor') );

        /* Allowed Post Tags */
        add_filter( 'of_sanitize_info', array($this, 'of_sanitize_allowedposttags') );

        /* Background */
        add_filter( 'of_sanitize_background', array($this, 'of_sanitize_background') );
        add_filter( 'of_background_repeat', array($this, 'of_sanitize_background_repeat') );
        add_filter( 'of_background_position', array($this, 'of_sanitize_background_position') );
        add_filter( 'of_background_attachment', array($this, 'of_sanitize_background_attachment') );

        /* Typography */
        add_filter( 'of_sanitize_typography', array($this, 'of_sanitize_typography'), 10, 2 );
        add_filter( 'of_font_size', array($this, 'of_sanitize_font_size') );
        add_filter( 'of_font_style', array($this, 'of_sanitize_font_style') );
        add_filter( 'of_font_face', array($this, 'of_sanitize_font_face') );
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

    /* Textarea */
    function of_sanitize_textarea($input) {
        global $allowedposttags;
        $output = wp_kses( $input, $allowedposttags);
        return $output;
    }

    /* Checkbox */
    public function of_sanitize_checkbox( $input ) {
        if ( $input ) {
            $output = '1';
        } else {
            $output = false;
        }
        return $output;
    }

    /* Multicheck */
    function of_sanitize_multicheck( $input, $option ) {
        $output = '';
        if ( is_array( $input ) ) {
            foreach( $option['options'] as $key => $value ) {
                $output[$key] = "0";
            }
            foreach( $input as $key => $value ) {
                if ( array_key_exists( $key, $option['options'] ) && $value ) {
                    $output[$key] = "1";
                }
            }
        }
        return $output;
    }

    /**
     * Uploader
     *
     * @param $input
     * @return string
     */
    function of_sanitize_upload( $input ) {
        $output = '';
        $filetype = wp_check_filetype($input);
        if ( $filetype["ext"] ) {
            $output = $input;
        }
        return $output;
    }

    /**
     * Editor
     *
     * @param $input
     * @return string
     */
    function of_sanitize_editor($input) {
        if ( current_user_can( 'unfiltered_html' ) ) {
            $output = $input;
        }
        else {
            global $allowedtags;
            $output = wpautop(wp_kses( $input, $allowedtags));
        }
        return $output;
    }

    /**
     * Allowed Tags
     *
     * @param $input
     * @return string
     */
    function of_sanitize_allowedtags($input) {
        global $allowedtags;
        $output = wpautop(wp_kses( $input, $allowedtags));
        return $output;
    }

    /**
     * Allowed Post Tags
     *
     * @param $input
     * @return string
     */
    function of_sanitize_allowedposttags($input) {
        global $allowedposttags;
        $output = wpautop(wp_kses( $input, $allowedposttags));
        return $output;
    }

    /**
     * Check that the key value sent is valid
     *
     * @param $input
     * @param $option
     * @return string
     */
    function of_sanitize_enum( $input, $option ) {
        $output = '';
        if ( array_key_exists( $input, $option['options'] ) ) {
            $output = $input;
        }
        return $output;
    }

    /**
     * Background
     *
     * @param $input
     * @return array
     */
    function of_sanitize_background( $input ) {
        $output = wp_parse_args( $input, array(
                'color' => '',
                'image'  => '',
                'repeat'  => 'repeat',
                'position' => 'top center',
                'attachment' => 'scroll'
            ) );

        $output['color'] = apply_filters( 'of_sanitize_hex', $input['color'] );
        $output['image'] = apply_filters( 'of_sanitize_upload', $input['image'] );
        $output['repeat'] = apply_filters( 'of_background_repeat', $input['repeat'] );
        $output['position'] = apply_filters( 'of_background_position', $input['position'] );
        $output['attachment'] = apply_filters( 'of_background_attachment', $input['attachment'] );

        return $output;
    }

    /**
     *
     *
     * @param $value
     * @return mixed|void
     */
    function of_sanitize_background_repeat( $value ) {
        $recognized = of_recognized_background_repeat();
        if ( array_key_exists( $value, $recognized ) ) {
            return $value;
        }
        return apply_filters( 'of_default_background_repeat', current( $recognized ) );
    }

    /**
     * @param $value
     * @return mixed|void
     */
    function of_sanitize_background_position( $value ) {
        $recognized = of_recognized_background_position();
        if ( array_key_exists( $value, $recognized ) ) {
            return $value;
        }
        return apply_filters( 'of_default_background_position', current( $recognized ) );
    }

    /**
     * @param $value
     * @return mixed|void
     */
    function of_sanitize_background_attachment( $value ) {
        $recognized = of_recognized_background_attachment();
        if ( array_key_exists( $value, $recognized ) ) {
            return $value;
        }
        return apply_filters( 'of_default_background_attachment', current( $recognized ) );
    }

    /**
     * Typography
     *
     * @param $input
     * @param $option
     * @return array
     */
    function of_sanitize_typography( $input, $option ) {

        $output = wp_parse_args( $input, array(
                'size'  => '',
                'face'  => '',
                'style' => '',
                'color' => ''
            ) );

        if ( isset( $option['options']['faces'] ) && isset( $input['face'] ) ) {
            if ( !( array_key_exists( $input['face'], $option['options']['faces'] ) ) ) {
                $output['face'] = '';
            }
        }
        else {
            $output['face']  = apply_filters( 'of_font_face', $output['face'] );
        }

        $output['size']  = apply_filters( 'of_font_size', $output['size'] );
        $output['style'] = apply_filters( 'of_font_style', $output['style'] );
        $output['color'] = apply_filters( 'of_sanitize_color', $output['color'] );
        return $output;
    }

    /**
     *
     *
     * @param $value
     * @return mixed|void
     */
    function of_sanitize_font_size( $value ) {
        $recognized = of_recognized_font_sizes();
        $value_check = preg_replace('/px/','', $value);
        if ( in_array( (int) $value_check, $recognized ) ) {
            return $value;
        }
        return apply_filters( 'of_default_font_size', $recognized );
    }

    /**
     *
     *
     * @param $value
     * @return mixed|void
     */
    function of_sanitize_font_style( $value ) {
        $recognized = of_recognized_font_styles();
        if ( array_key_exists( $value, $recognized ) ) {
            return $value;
        }
        return apply_filters( 'of_default_font_style', current( $recognized ) );
    }

    /**
     *
     *
     * @param $value
     * @return mixed|void
     */
    function of_sanitize_font_face( $value ) {
        $recognized = of_recognized_font_faces();
        if ( array_key_exists( $value, $recognized ) ) {
            return $value;
        }
        return apply_filters( 'of_default_font_face', current( $recognized ) );
    }

    /**
     * Get recognized background repeat settings
     *
     * @return   array
     *
     */
    function of_recognized_background_repeat() {
        $default = array(
            'no-repeat' => __('No Repeat', 'options_framework'),
            'repeat-x'  => __('Repeat Horizontally', 'options_framework'),
            'repeat-y'  => __('Repeat Vertically', 'options_framework'),
            'repeat'    => __('Repeat All', 'options_framework'),
        );
        return apply_filters( 'of_recognized_background_repeat', $default );
    }

    /**
     * Get recognized background positions
     *
     * @return   array
     *
     */
    function of_recognized_background_position() {
        $default = array(
            'top left'      => __('Top Left', 'options_framework'),
            'top center'    => __('Top Center', 'options_framework'),
            'top right'     => __('Top Right', 'options_framework'),
            'center left'   => __('Middle Left', 'options_framework'),
            'center center' => __('Middle Center', 'options_framework'),
            'center right'  => __('Middle Right', 'options_framework'),
            'bottom left'   => __('Bottom Left', 'options_framework'),
            'bottom center' => __('Bottom Center', 'options_framework'),
            'bottom right'  => __('Bottom Right', 'options_framework')
        );
        return apply_filters( 'of_recognized_background_position', $default );
    }

    /**
     * Get recognized background attachment
     *
     * @return   array
     *
     */
    function of_recognized_background_attachment() {
        $default = array(
            'scroll' => __('Scroll Normally', 'options_framework'),
            'fixed'  => __('Fixed in Place', 'options_framework')
        );
        return apply_filters( 'of_recognized_background_attachment', $default );
    }

    /**
     * Sanitize a color represented in hexidecimal notation.
     *
     * @param    string    Color in hexidecimal notation. "#" may or may not be prepended to the string.
     * @param    string    The value that this function should return if it cannot be recognized as a color.
     * @return   string
     */
    function of_sanitize_hex( $hex, $default = '' ) {
        if ( of_validate_hex( $hex ) ) {
            return $hex;
        }
        return $default;
    }

    /**
     * Get recognized font sizes.
     *
     * Returns an indexed array of all recognized font sizes.
     * Values are integers and represent a range of sizes from
     * smallest to largest.
     *
     * @return   array
     */
    function of_recognized_font_sizes() {
        $sizes = range( 9, 71 );
        $sizes = apply_filters( 'of_recognized_font_sizes', $sizes );
        $sizes = array_map( 'absint', $sizes );
        return $sizes;
    }

    /**
     * Get recognized font faces.
     *
     * Returns an array of all recognized font faces.
     * Keys are intended to be stored in the database
     * while values are ready for display in in html.
     *
     * @return   array
     */
    function of_recognized_font_faces() {
        $default = array(
            'arial'     => 'Arial',
            'verdana'   => 'Verdana, Geneva',
            'trebuchet' => 'Trebuchet',
            'georgia'   => 'Georgia',
            'times'     => 'Times New Roman',
            'tahoma'    => 'Tahoma, Geneva',
            'palatino'  => 'Palatino',
            'helvetica' => 'Helvetica*'
        );
        return apply_filters( 'of_recognized_font_faces', $default );
    }

    /**
     * Get recognized font styles.
     *
     * Returns an array of all recognized font styles.
     * Keys are intended to be stored in the database
     * while values are ready for display in in html.
     *
     * @return   array
     */
    function of_recognized_font_styles() {
        $default = array(
            'normal'      => __( 'Normal', 'options_framework' ),
            'italic'      => __( 'Italic', 'options_framework' ),
            'bold'        => __( 'Bold', 'options_framework' ),
            'bold italic' => __( 'Bold Italic', 'options_framework' )
        );
        return apply_filters( 'of_recognized_font_styles', $default );
    }

    /**
     * Is a given string a color formatted in hexidecimal notation?
     *
     * @param    string    Color in hexidecimal notation. "#" may or may not be prepended to the string.
     * @return   bool
     */
    function of_validate_hex( $hex ) {
        $hex = trim( $hex );
        /* Strip recognized prefixes. */
        if ( 0 === strpos( $hex, '#' ) ) {
            $hex = substr( $hex, 1 );
        }
        elseif ( 0 === strpos( $hex, '%23' ) ) {
            $hex = substr( $hex, 3 );
        }
        /* Regex match. */
        if ( 0 === preg_match( '/^[0-9a-fA-F]{6}$/', $hex ) ) {
            return false;
        }
        else {
            return true;
        }
    }
}