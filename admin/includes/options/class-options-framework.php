<?php

// Include the required files
require_once dirname( __FILE__ ) . '/class-options-sanitize.php';
require_once dirname( __FILE__ ) . '/class-options-interface.php';
require_once dirname( __FILE__ ) . '/class-options-media-uploader.php';
require_once dirname( __FILE__ ) . '/class-options-framework-upgrade.php';
// Loads the options array from the theme
if ( file_exists(plugin_dir_path(dirname(dirname(dirname(__FILE__)))) .'options.php') ) {
    require_once plugin_dir_path(dirname(dirname(dirname(__FILE__)))) .'options.php';
}
else if (file_exists( dirname( __FILE__ ) . '/options.php' ) ) {
    require_once dirname( __FILE__ ) . '/options.php';
}

/**
 * @package Options_Framework
 *
 */
class Options_Framework {
    private $options;
    private $options_sanitize;
    private $options_interface;
    private $options_medida_uploader;
    private $options_framework_upgrade;
    private $plugin_slug;

    /**
     * Slug of the plugin screen.
     *
     * @since    1.0.0
     *
     * @var      string
     */
    protected $of_screen_hook_suffix = null;

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
        $plugin = Tipster_TAP::get_instance();
        $this->plugin_slug = $plugin->get_plugin_slug();

        add_action('init', array( $this, 'optionsframework_rolescheck' ) );
        add_action( 'init', array( $this, 'optionsframework_load_sanitization' ) );
//        add_action( 'optionsframework_after_validate', array( $this, 'optionsframework_save_options_notice' ) );
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

    public function get_plugin_slug()
    {
        return $this->plugin_slug;
    }

    /**
     * If the user can't edit theme options, no use running this plugin
     *
     */
    function optionsframework_rolescheck () {
        if ( current_user_can( 'manage_options' ) ) {
            // If the user can edit options, let the fun begin!
            add_action( 'admin_init', array( $this, 'optionsframework_init' ) );
            add_action( 'admin_menu', array( $this, 'optionsframework_add_page' ) );
            add_action( 'wp_before_admin_bar_render', array( $this, 'optionsframework_adminbar' ) );
        }
    }

    /**
     * Loads the file for option sanitization
     *
     */
    function optionsframework_load_sanitization() {
//        require_once dirname( __FILE__ ) . '/options-sanitize.php';

        $this->options_sanitize = Options_Sanitize::get_instance();
    }

    /**
     * Creates the settings in the database by looping through the array
     * we supplied in options.php.  This is a neat way to do it since
     * we won't have to save settings for headers, descriptions, or arguments.
     *
     * Read more about the Settings API in the WordPress codex:
     * http://codex.wordpress.org/Settings_API
     *
     */
    function optionsframework_init() {

        // Include the required files
        $this->options_interface = Options_Interface::get_instance();
        $this->options_medida_uploader = Options_Media_Uploader::get_instance();
        $this->options = Tipster_TAP_Options::get_instance();

        // Load settings
        $optionsframework_settings = get_option('options_framework' );

        // Update routine
        // This code can be removed if you're starting a new project
        // and don't have legacy users to support
        if ( $optionsframework_settings && !isset($optionsframework_settings['version']) ) {
            $this->options_framework_upgrade = OptionsFramework_Upgrade::get_instance();
            $this->options_framework_upgrade->optionsframework_upgrade_routine();
        }

        // Updates the unique option id in the database if it has changed
        $this->options->optionsframework_option_name();

        // Gets the unique id, returning a default if it isn't defined
        if ( isset( $optionsframework_settings['id'] ) ) {
            $option_name = $optionsframework_settings['id'];
        }
        else {
            $option_name = 'options_framework';
        }

        // If the option has no saved data, load the defaults
        if ( ! get_option($option_name) ) {
            $this->optionsframework_setdefaults();
        }

        // Registers the settings fields and callback
        register_setting( 'options_framework', $option_name, array($this, 'optionsframework_validate') );

        // Change the capability required to save the 'options_framework' options group.
        add_filter( 'option_page_capability_optionsframework', array($this, 'optionsframework_page_capability') );
    }

    /**
     * Ensures that a user with the 'manage_options' capability can actually set the options
     * See: http://core.trac.wordpress.org/ticket/14365
     *
     * @param string $capability The capability used for the page, which is manage_options by default.
     * @return string The capability to actually use.
     */
    function optionsframework_page_capability( $capability ) {
        return 'manage_options';
    }

    /*
     * Adds default options to the database if they aren't already present.
     * May update this later to load only on plugin activation, or theme
     * activation since most people won't be editing the options.php
     * on a regular basis.
     *
     * http://codex.wordpress.org/Function_Reference/add_option
     *
     */
    function optionsframework_setdefaults() {

        $optionsframework_settings = get_option( 'options_framework' );

        // Gets the unique option id
        $option_name = $optionsframework_settings['id'];

        /*
         * Each theme will hopefully have a unique id, and all of its options saved
         * as a separate option set.  We need to track all of these option sets so
         * it can be easily deleted if someone wishes to remove the plugin and
         * its associated data.  No need to clutter the database.
         *
         */
        if ( isset( $optionsframework_settings['knownoptions'] ) ) {
            $knownoptions =  $optionsframework_settings['knownoptions'];
            if ( !in_array( $option_name, $knownoptions ) ) {
                array_push( $knownoptions, $option_name );
                $optionsframework_settings['knownoptions'] = $knownoptions;
                update_option( 'options_framework', $optionsframework_settings );
            }
        } else {
            $newoptionname = array( $option_name );
            $optionsframework_settings['knownoptions'] = $newoptionname;
            update_option( 'options_framework', $optionsframework_settings );
        }

        // Gets the default options data from the array in options.php
        $options = $this->options->optionsframework_options();

        // If the options haven't been added to the database yet, they are added now
        $values = $this->of_get_default_values();

        if ( isset( $values ) ) {
            add_option( $option_name, $values ); // Add option with default settings
        }
    }

    /**
     * Add a subpage called "Plugin Options" to the appearance menu.
     */
    function optionsframework_add_page() {
        $this->of_screen_hook_suffix = add_submenu_page(
            $this->plugin_slug,
            __('Tipster TAP :: Options', 'options_framework'),
            __('Options', 'options_framework'),
            $this->optionsframework_page_capability(null),
            $this->plugin_slug.'/options',
            array( $this, 'optionsframework_page' )
        );

        // Load the required CSS and javscript
        add_action( 'admin_enqueue_scripts', array($this, 'optionsframework_load_scripts') );
        add_action( 'admin_enqueue_scripts', array($this, 'optionsframework_media_scripts') );
        add_action( 'admin_print_styles-' . $this->of_screen_hook_suffix, array($this, 'optionsframework_load_styles') );
    }

    /**
     * Loads the CSS
     */
    function optionsframework_load_styles() {
        wp_enqueue_style( 'options_framework', TIPSTER_TAP_OPTIONS_FRAMEWORK_DIRECTORY.'css/optionsframework.css' );
        if ( !wp_style_is( 'wp-color-picker','registered' ) ) {
            wp_register_style( 'wp-color-picker', TIPSTER_TAP_OPTIONS_FRAMEWORK_DIRECTORY.'css/color-picker.min.css' );
        }
        wp_enqueue_style( 'wp-color-picker' );
    }

    /**
     * Loads the javascript
     *
     * @param $hook
     */
    function optionsframework_load_scripts( $hook ) {

//        if ( 'appearance_page_options-framework' != $hook )
//            return;

        // Enqueue colorpicker scripts for versions below 3.5 for compatibility
        if ( !wp_script_is( 'wp-color-picker', 'registered' ) ) {
            wp_register_script( 'of-iris', TIPSTER_TAP_OPTIONS_FRAMEWORK_DIRECTORY . 'js/iris.min.js', array( 'jquery-ui-draggable', 'jquery-ui-slider', 'jquery-touch-punch' ), false, 1 );
            wp_register_script( 'of-wp-color-picker', TIPSTER_TAP_OPTIONS_FRAMEWORK_DIRECTORY . 'js/color-picker.min.js', array( 'jquery', 'iris' ) );
            $colorpicker_l10n = array(
                'clear' => __( 'Clear','options_framework' ),
                'defaultString' => __( 'Default', 'options_framework' ),
                'pick' => __( 'Select Color', 'options_framework' )
            );
            wp_localize_script( 'of-wp-color-picker', 'wpColorPickerL10n', $colorpicker_l10n );
        }

        // Enqueue custom option panel JS
        wp_enqueue_script( 'of-options-custom', TIPSTER_TAP_OPTIONS_FRAMEWORK_DIRECTORY . 'js/options-custom.js', array( 'jquery','wp-color-picker' ) );

        // Inline scripts from options-interface.php
        add_action( 'admin_head', array($this, 'of_admin_head') );
//        add_action('admin_head', array($this->options, 'optionsframework_custom_scripts') );
    }

    function optionsframework_media_scripts(){
        $this->options_medida_uploader->optionsframework_media_scripts();
    }

    function of_admin_head() {
        // Hook to add custom scripts
        do_action( 'optionsframework_custom_scripts' );
    }

    /**
     * Builds out the options panel.
     *
     * If we were using the Settings API as it was likely intended we would use
     * do_settings_sections here.  But as we don't want the settings wrapped in a table,
     * we'll call our own custom optionsframework_fields.  See options-interface.php
     * for specifics on how each individual field is generated.
     *
     * Nonces are provided using the settings_fields()
     *
     */
    function optionsframework_page() {
        settings_errors(); ?>
        <div class="wrap">
            <h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
        </div>
        <div id="optionsframework-wrap" class="wrap">
            <h2 class="nav-tab-wrapper">
                <?php echo $this->options_interface->optionsframework_tabs(); ?>
            </h2>

            <div id="optionsframework-metabox" class="metabox-holder">
                <div id="optionsframework" class="postbox">
                    <form action="options.php" method="post">
                        <?php settings_fields( 'options_framework' ); ?>
                        <?php $this->options_interface->optionsframework_fields(); /* Settings */ ?>
                        <div id="optionsframework-submit">
                            <input type="submit" class="button-primary" name="update" value="<?php esc_attr_e( 'Save Options', 'options_framework' ); ?>" />
                            <input type="submit" class="reset-button button-secondary" name="reset" value="<?php esc_attr_e( 'Restore Defaults', 'options_framework' ); ?>" onclick="return confirm( '<?php print esc_js( __( 'Click OK to reset. Any settings will be lost!', 'options_framework' ) ); ?>' );" />
                            <div class="clear"></div>
                        </div>
                    </form>
                </div> <!-- / #container -->
            </div>
            <?php do_action( 'optionsframework_after' ); ?>
        </div> <!-- / .wrap -->

    <?php
    }

    /**
     * Validate Options.
     *
     * This runs after the submit/reset button has been clicked and
     * validates the inputs.
     *
     * @uses $_POST['reset'] to restore default options
     */
    function optionsframework_validate( $input ) {

        /*
         * Restore Defaults.
         *
         * In the event that the user clicked the "Restore Defaults"
         * button, the options defined in the theme's options.php
         * file will be added to the option for the active theme.
         */

        if ( isset( $_POST['reset'] ) ) {
            add_settings_error( 'options-framework', 'restore_defaults', __( 'Default options restored.', 'options_framework' ), 'updated fade' );
            return of_get_default_values();
        }

        /*
         * Update Settings
         *
         * This used to check for $_POST['update'], but has been updated
         * to be compatible with the theme customizer introduced in WordPress 3.4
         */

        $clean = array();
        $options = $this->options->optionsframework_options();
        foreach ( $options as $option ) {

            if ( ! isset( $option['id'] ) ) {
                continue;
            }

            if ( ! isset( $option['type'] ) ) {
                continue;
            }

            $id = preg_replace( '/[^a-zA-Z0-9._\-]/', '', strtolower( $option['id'] ) );

            // Set checkbox to false if it wasn't sent in the $_POST
            if ( 'checkbox' == $option['type'] && ! isset( $input[$id] ) ) {
                $input[$id] = false;
            }

            // Set each item in the multicheck to false if it wasn't sent in the $_POST
            if ( 'multicheck' == $option['type'] && ! isset( $input[$id] ) ) {
                foreach ( $option['options'] as $key => $value ) {
                    $input[$id][$key] = false;
                }
            }

            // For a value to be submitted to database it must pass through a sanitization filter
            if ( has_filter( 'of_sanitize_' . $option['type'] ) ) {
                $clean[$id] = apply_filters( 'of_sanitize_' . $option['type'], $input[$id], $option );
            }
        }

        // Hook to run after validation
        do_action( 'optionsframework_after_validate', $clean );

        return $clean;
    }

    /**
     * Display message when options have been saved
     */
    function optionsframework_save_options_notice() {
        add_settings_error( 'options-framework', 'save_options', __( 'Options saved.', 'options_framework' ), 'updated fade' );
    }

    /**
     * Format Configuration Array.
     *
     * Get an array of all default values as set in
     * options.php. The 'id','std' and 'type' keys need
     * to be defined in the configuration array. In the
     * event that these keys are not present the option
     * will not be included in this function's output.
     *
     * @return    array     Rey-keyed options configuration array.
     *
     * @access    private
     */

    function of_get_default_values() {
        $output = array();
        $config = $this->options->optionsframework_options();
        foreach ( (array) $config as $option ) {
            if ( ! isset( $option['id'] ) ) {
                continue;
            }
            if ( ! isset( $option['std'] ) ) {
                continue;
            }
            if ( ! isset( $option['type'] ) ) {
                continue;
            }
            if ( has_filter( 'of_sanitize_' . $option['type'] ) ) {
                $output[$option['id']] = apply_filters( 'of_sanitize_' . $option['type'], $option['std'], $option );
            }
        }
        return $output;
    }

    /**
     * Add Options menu item to Admin Bar.
     */
    function optionsframework_adminbar() {

        global $wp_admin_bar;

        $wp_admin_bar->add_menu( array(
                'parent' => $this->plugin_slug,
                'id' => 'of_plugin_options',
                'title' => __( 'Options', 'options_framework' ),
                'href' => admin_url( 'admin.php?page='.$this->plugin_slug.'/options' )
            ));


    }

    /**
     * Get Option.
     *
     * Helper function to return the theme option value.
     * If no value has been saved, it returns $default.
     * Needed because options are saved as serialized strings.
     */

    function of_get_option( $name, $default = false ) {
        $config = get_option( 'options_framework' );

        if ( ! isset( $config['id'] ) ) {
            return $default;
        }

        $options = get_option( $config['id'] );

        if ( isset( $options[$name] ) ) {
            return $options[$name];
        }

        return $default;
    }

    /**
     * Wrapper for optionsframework_options()
     *
     * Allows for manipulating or setting options via 'of_options' filter
     * For example:
     *
     * <code>
     * add_filter('of_options', function($options) {
     *     $options[] = array(
     *         'name' => 'Input Text Mini',
     *         'desc' => 'A mini text input field.',
     *         'id' => 'example_text_mini',
     *         'std' => 'Default',
     *         'class' => 'mini',
     *         'type' => 'text'
     *     );
     *
     *     return $options;
     * });
     * </code>
     *
     * Also allows for setting options via a return statement in the
     * options.php file.  For example (in options.php):
     *
     * <code>
     * return array(...);
     * </code>
     *
     * @return array (by reference)
     */
    function &_optionsframework_options() {
        static $options = null;

        if ( !$options ) {
            // Load options from options.php file (if it exists)
            $location = apply_filters( 'options_framework_location', array('options.php') );
            if ( file_exists(plugin_dir_path(dirname(dirname(dirname(__FILE__)))) .'options.php') ) {
                $maybe_options = require_once plugin_dir_path(dirname(dirname(dirname(__FILE__)))) .'options.php';
                if (is_array($maybe_options)) {
                    $options = $maybe_options;
                } else {
                    $options = $this->options->optionsframework_options();
                }
            }

            // Allow setting/manipulating options via filters
            $options = apply_filters('of_options', $options);
        }

        return $options;
    }
}