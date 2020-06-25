<?php

namespace TipsterTAP\Common;

/**
 * Tipster post type.
 *
 * @package WordPress
 * @subpackage Theme
 */

class TipsterPostType{
    /**
     * Instance of this class.
     *
     * @since    1.0.0
     *
     * @var      object
     */
    protected static $instance;

    /**
     * Initialize the plugin by loading admin scripts & styles and adding a
     * settings page and menu.
     *
     * @since     1.0.0
     */
    private function __construct() {
        add_action( 'init', array( $this, 'post_type_tipster') );
        add_action( 'init', array( $this, 'post_type_tipster_taxonomies' ), 0 );
//        add_action( 'contextual_help', array( $this, 'post_type_tipster_contextual_help' ), 10, 3 );

        add_filter( 'post_updated_messages', array( $this, 'post_type_tipster_updated_messages' ) );
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
         * - Uncomment following lines if the admin class should only be available for super admins
         */
        /* if( ! is_super_admin() ) {
            return;
        } */

        // If the single instance hasn't been set, set it now.
        if ( null === self::$instance ) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Register a tipster post type
     */
    public function post_type_tipster() {
        $labels = array(
            'name'                => _x( 'Tipsters', 'post type general name', 'epic' ),
            'singular_name'       => _x( 'Tipster', 'post type singular name', 'epic' ),
            'menu_name'           => _x( 'Tipster', 'admin menu', 'epic' ),
            'name_admin_bar'      => _x( 'Tipster', 'add new on admin bar', 'epic' ),
            'parent_item_colon'   => __( 'Tipster padre:', 'epic' ),
            'all_items'           => __( 'Todos los tipster', 'epic' ),
            'view_item'           => __( 'Ver tipster', 'epic' ),
            'add_new_item'        => __( 'Agregar tipster', 'epic' ),
            'add_new'             => _x( 'Agregar nuevo', 'tipster', 'epic' ),
            'edit_item'           => __( 'Editar tipster', 'epic' ),
            'update_item'         => __( 'Actualizar tipster', 'epic' ),
            'search_items'        => __( 'Buscar tipster', 'epic' ),
            'not_found'           => __( 'No se encontraron tipster', 'epic' ),
            'not_found_in_trash'  => __( 'No se encontraron tipster en la papelera', 'epic' ),
        );
        $args = array(
            'label'               => __( 'tipster', 'epic' ),
            'description'         => __( 'Gestiona los tipsters y la informacion de los tipsters', 'epic' ),
            'labels'              => $labels,
            'supports'            => array( 'title', 'editor', 'author', 'thumbnail', ),
            'hierarchical'        => false,
            'public'              => true,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'show_in_nav_menus'   => false,
            'show_in_admin_bar'   => true,
            'menu_position'       => 5,
            'menu_icon'           => 'dashicons-admin-generic',
            'can_export'          => true,
            'has_archive'         => true,
            'exclude_from_search' => false,
            'publicly_queryable'  => true,
            'capability_type'     => 'page',
        );
        register_post_type( 'tipster', $args );
    }

    /**
     * Register a tipster post type taxonomies
     */
    public function post_type_tipster_taxonomies() {
        $labels = array(
            'name'              => _x( 'Categorias de Tipsters', 'taxonomy general name', 'epic' ),
            'singular_name'     => _x( 'Categoria de Tipster', 'taxonomy singular name', 'epic' ),
            'search_items'      => __( 'Buscar categorias', 'epic' ),
            'all_items'         => __( 'Todas las categorias', 'epic' ),
            'parent_item'       => __( 'Categoria padre', 'epic' ),
            'parent_item_colon' => __( 'Categoria padre:', 'epic' ),
            'edit_item'         => __( 'Editar categoria', 'epic' ),
            'update_item'       => __( 'Actualizar categoria', 'epic' ),
            'add_new_item'      => __( 'Agregar nueva', 'epic' ),
            'new_item_name'     => __( 'Agregar categoria', 'epic' ),
            'menu_name'         => __( 'Categorias de Tipsters', 'epic' ),
        );
        $args = array(
            'labels' => $labels,
            'hierarchical' => true,
        );
        register_taxonomy( 'tipsters', 'tipster', $args );
    }

    /**
     * Display contextual help for Tipster
     *
     * @param $contextual_help
     * @param $screen_id
     * @param $screen
     * @return string
     */
    public function post_type_tipster_contextual_help( $contextual_help, $screen_id, $screen ) {
        if ( 'edit-tipster' === $screen->id ) {

            $contextual_help = '<h2>Tipster</h2>
        <p>Se muestran los detalles de los elementos que se muestran en la pagina de detalles de los tipsters. Usted puede ver la lista de esos elementos en esta pagina y ordenarlos cronologicamente - el ultimo agregado es el primero.</p>
        <p>Usted puede ver/editar los detalles de cada tipster haciendo clic en su nombre, o puede aplicar acciones usando el menu de opciones y seleccionar multiples elementos.</p>';

        } elseif ( 'tipster' === $screen->id ) {

            $contextual_help = '<h2>Editar tipster</h2>
        <p>Esta pagina le permite ver/modificar los detalles de un tipster. Por favor asegurece de llenar los campos de las cajas disponibles (nombre, imagen, enlace).</p>';

        }
        return $contextual_help;
    }

    /**
     * Tipster update messages.
     *
     * @param $messages array Existing post update messages.
     * @return array Amended post update messages with new CPT update messages.
     */
    public function post_type_tipster_updated_messages( $messages ) {
        $post             = get_post();
        $post_type        = get_post_type( $post );
//        $post_type_object = get_post_type_object( $post_type );

        $messages['tipster'] = array(
            0  => '', // Unused. Messages start at index 1.
            1  => sprintf( __('Tipster actualizado. <a href="%s">Ver tipster</a>', 'epic'), esc_url( get_permalink($post->ID) ) ),
            2  => __( 'Campo personalizado actualizado.', 'epic' ),
            3  => __( 'Campo personalizado eliminado.', 'epic' ),
            4  => __( 'Tipster actualizado.', 'epic' ),
            5  => isset($_GET['revision']) ? sprintf( __('Tipster restaurado desde la revision from %s', 'epic'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
            6  => sprintf( __('Tipster publicado. <a href="%s">Ver tipster</a>', 'epic'), esc_url( get_permalink($post->ID) ) ),
            7  => __( 'Tipster guardado.', 'epic' ),
            8  => sprintf( __('Tipster enviado. <a target="_blank" href="%s">Ver tipster</a>', 'epic'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post->ID) ) ) ),
            9  => sprintf( __('Tipster planificado para: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Ver tipster</a>', 'epic'), date_i18n( __( 'M j, Y @ G:i', 'epic' ), strtotime( $post->post_date ) ), esc_url( get_permalink($post->ID) ) ),
            10 => sprintf( __('Borrador del tipster actualizado. <a target="_blank" href="%s">Ver tipster</a>', 'epic'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post->ID) ) ) ),
        );

//        if ( $post_type_object->publicly_queryable ) {
//            $permalink = get_permalink( $post->ID );
//
//            $view_link = sprintf( ' <a href="%s">%s</a>', esc_url( $permalink ), __( 'View tipster', 'epic' ) );
//            $messages[ $post_type ][1] .= $view_link;
//            $messages[ $post_type ][6] .= $view_link;
//            $messages[ $post_type ][9] .= $view_link;
//
//            $preview_permalink = add_query_arg( 'preview', 'true', $permalink );
//            $preview_link = sprintf( ' <a target="_blank" href="%s">%s</a>', esc_url( $preview_permalink ), __( 'Preview tipster', 'epic' ) );
//            $messages[ $post_type ][8]  .= $preview_link;
//            $messages[ $post_type ][10] .= $preview_link;
//        }

        return $messages;
    }
}