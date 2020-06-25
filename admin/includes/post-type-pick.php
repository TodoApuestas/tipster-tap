<?php

namespace TipsterTAP\Backend\Common;

use TipsterTAP\Frontend\TipsterTap;

/**
 * Pick post type.
 *
 * @package TipsterTap
 */

class PickPostType{
	/**
	 * @since    3.0.0
	 */
	const TYPE_WINNER = 'ganador';
	const TYPE_LOSER = 'perdedor';
	const TYPE_UNDER = 'under';
	const TYPE_OVER = 'over';
	const TYPE_HANDICAP = 'handicap';
	const TYPE_RESULT = 'resultado';
	const TYPE_MIXED = 'combinada';
	const TYPE_FUNBET = 'funbet';
	const TYPE_CHALLENGE = 'reto';
	const TYPE_OTHER = 'otro';
	
	/**
     * Instance of this class.
     *
     * @since    1.0.0
     *
     * @var      object
     */
    protected static $instance;
	
	private $plugin_slug;
    
    /**
     * Initialize the plugin by loading admin scripts & styles and adding a
     * settings page and menu.
     *
     * @since     1.0.0
     */
    private function __construct() {
    	$this->plugin_slug = TipsterTap::get_instance()->get_plugin_slug();
        add_action( 'init', array( $this, 'post_type_pick') );
        add_action( 'init', array( $this, 'post_type_pick_taxonomies' ), 0 );
//        add_action( 'contextual_help', array( $this, 'post_type_pick_contextual_help' ), 10, 3 );

        add_filter( 'post_updated_messages', array( $this, 'post_type_pick_updated_messages' ) );
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
     * Register a pick post type
     */
    public function post_type_pick() {
        $labels = array(
            'name'                => _x( 'Picks', 'post type general name', $this->plugin_slug ),
            'singular_name'       => _x( 'Pick', 'post type singular name', $this->plugin_slug ),
            'menu_name'           => _x( 'Pick', 'admin menu', $this->plugin_slug ),
            'name_admin_bar'      => _x( 'Pick', 'add new on admin bar', $this->plugin_slug ),
            'parent_item_colon'   => __( 'Pick padre:', $this->plugin_slug ),
            'all_items'           => __( 'Todos los picks', $this->plugin_slug ),
            'view_item'           => __( 'Ver pick', $this->plugin_slug ),
            'add_new_item'        => __( 'Agregar pick', $this->plugin_slug ),
            'add_new'             => _x( 'Agregar nuevo', 'pick', $this->plugin_slug ),
            'edit_item'           => __( 'Editar pick', $this->plugin_slug ),
            'update_item'         => __( 'Actualizar pick', $this->plugin_slug ),
            'search_items'        => __( 'Buscar picks', $this->plugin_slug ),
            'not_found'           => __( 'No se encontraron picks', $this->plugin_slug ),
            'not_found_in_trash'  => __( 'No se encontraron picks en la papelera', $this->plugin_slug ),
        );
        $args = array(
            'label'               => __( 'pick', $this->plugin_slug ),
            'description'         => __( 'Gestiona los picks y la informacion de los picks', $this->plugin_slug ),
            'labels'              => $labels,
            'supports'            => array( 'title', 'editor', 'author', 'thumbnail', ),
            'hierarchical'        => true,
            'public'              => true,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'show_in_nav_menus'   => true,
            'show_in_admin_bar'   => true,
            'menu_position'       => 5,
            'menu_icon'           => 'dashicons-admin-generic',
            'can_export'          => true,
            'has_archive'         => true,
            'exclude_from_search' => true,
            'publicly_queryable'  => true,
            'capability_type'     => 'page',
        );
        register_post_type( 'pick', $args );
    }

    /**
     * Register a pick post type taxonomies
     */
    public function post_type_pick_taxonomies() {
        $labels = array(
            'name'              => _x( 'Categorias de Picks', 'taxonomy general name', $this->plugin_slug ),
            'singular_name'     => _x( 'Categoria de Pick', 'taxonomy singular name', $this->plugin_slug ),
            'search_items'      => __( 'Buscar categorias', $this->plugin_slug ),
            'all_items'         => __( 'Todas las categorias', $this->plugin_slug ),
            'parent_item'       => __( 'Categoria padre', $this->plugin_slug ),
            'parent_item_colon' => __( 'Categoria padre:', $this->plugin_slug ),
            'edit_item'         => __( 'Editar categoria', $this->plugin_slug ),
            'update_item'       => __( 'Actualizar categoria', $this->plugin_slug ),
            'add_new_item'      => __( 'Agregar nueva', $this->plugin_slug ),
            'new_item_name'     => __( 'Agregar categoria', $this->plugin_slug ),
            'menu_name'         => __( 'Categorias de Picks', $this->plugin_slug ),
        );
        $args = array(
            'labels' => $labels,
            'hierarchical' => true,
        );
        register_taxonomy( 'picks', 'pick', $args );
    }

    /**
     * Display contextual help for Pick
     *
     * @param $contextual_help
     * @param $screen_id
     * @param $screen
     * @return string
     */
    public function post_type_pick_contextual_help( $contextual_help, $screen_id, $screen ) {
        if ( 'edit-pick' === $screen->id ) {

            $contextual_help = '<h2>Pick</h2>
        <p>Se muestran los detalles de los elementos que se muestran en la pagina de detalles de los picks. Usted puede ver la lista de esos elementos en esta pagina y ordenarlos cronologicamente - el ultimo agregado es el primero.</p>
        <p>Usted puede ver/editar los detalles de cada pick haciendo clic en su nombre, o puede aplicar acciones usando el menu de opciones y seleccionar multiples elementos.</p>';

        } elseif ( 'pick' === $screen->id ) {

            $contextual_help = '<h2>Editing pick</h2>
        <p>Esta pagina le permite ver/modificar los detalles de un pick. Por favor asegurece de llenar los campos de las cajas disponibles (nombre, imagen, enlace).</p>';

        }
        return $contextual_help;
    }
	
	/**
	 * Pick update messages.
	 *
	 * @param array $messages Existing post update messages.
	 *
	 * @return array Existing Amended post update messages with new CPT update messages
	 */
    public function post_type_pick_updated_messages( array $messages ) {
        $post             = get_post();
        $post_type        = get_post_type( $post );
//        $post_type_object = get_post_type_object( $post_type );

        $messages['pick'] = array(
            0  => '', // Unused. Messages start at index 1.
            1  => sprintf( __('Pick actualizado. <a href="%s">Ver pick</a>', $this->plugin_slug), esc_url( get_permalink($post->ID) ) ),
            2  => __( 'Campo personalizado actualizado.', $this->plugin_slug ),
            3  => __( 'Campo personalizado eliminado.', $this->plugin_slug ),
            4  => __( 'Pick actualizado.', $this->plugin_slug ),
            5  => isset($_GET['revision']) ? sprintf( __('Pick restaurado desde la revision from %s', $this->plugin_slug), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
            6  => sprintf( __('Pick publicado. <a href="%s">Ver pick</a>', $this->plugin_slug), esc_url( get_permalink($post->ID) ) ),
            7  => __( 'Pick guardado.', $this->plugin_slug ),
            8  => sprintf( __('Pick enviado. <a target="_blank" href="%s">Ver pick</a>', $this->plugin_slug), esc_url( add_query_arg( 'preview', 'true', get_permalink($post->ID) ) ) ),
            9  => sprintf( __('Pick planificado para: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Ver pick</a>', $this->plugin_slug), date_i18n( __( 'M j, Y @ G:i', $this->plugin_slug ), strtotime( $post->post_date ) ), esc_url( get_permalink($post->ID) ) ),
            10 => sprintf( __('Borrador del pick actualizado. <a target="_blank" href="%s">Ver pick</a>', $this->plugin_slug), esc_url( add_query_arg( 'preview', 'true', get_permalink($post->ID) ) ) ),
        );

//        if ( $post_type_object->publicly_queryable ) {
//            $permalink = get_permalink( $post->ID );
//
//            $view_link = sprintf( ' <a href="%s">%s</a>', esc_url( $permalink ), __( 'View pick', $this->plugin_slug ) );
//            $messages[ $post_type ][1] .= $view_link;
//            $messages[ $post_type ][6] .= $view_link;
//            $messages[ $post_type ][9] .= $view_link;
//
//            $preview_permalink = add_query_arg( 'preview', 'true', $permalink );
//            $preview_link = sprintf( ' <a target="_blank" href="%s">%s</a>', esc_url( $preview_permalink ), __( 'Preview pick', $this->plugin_slug ) );
//            $messages[ $post_type ][8]  .= $preview_link;
//            $messages[ $post_type ][10] .= $preview_link;
//        }

        return $messages;
    }
    
    public function getPickTypesArray(){
    	return array(
		    self::TYPE_WINNER,
		    self::TYPE_LOSER,
		    self::TYPE_UNDER,
		    self::TYPE_OVER,
		    self::TYPE_HANDICAP,
		    self::TYPE_RESULT,
		    self::TYPE_MIXED,
		    self::TYPE_FUNBET,
		    self::TYPE_CHALLENGE,
		    self::TYPE_OTHER
	    );
    }
	
	/**
	 * @since 3.0.0
	 *
	 * @return array
	 */
	public function getPickTypes(){
		return array(
			self::TYPE_WINNER    => __( 'Ganador', $this->plugin_slug ),
			self::TYPE_LOSER     => __( 'Perdedor', $this->plugin_slug ),
			self::TYPE_UNDER     => __( 'Under', $this->plugin_slug ),
			self::TYPE_OVER      => __( 'Over', $this->plugin_slug ),
			self::TYPE_HANDICAP  => __( 'Handicap', $this->plugin_slug ),
			self::TYPE_RESULT    => __( 'Resultado concreto', $this->plugin_slug ),
			self::TYPE_MIXED     => __( 'Combinada', $this->plugin_slug ),
			self::TYPE_FUNBET    => __( 'Funbet', $this->plugin_slug ),
			self::TYPE_CHALLENGE => __( 'Reto', $this->plugin_slug ),
			self::TYPE_OTHER     => __( 'Otro', $this->plugin_slug )
		);
	}
	
	/**
	 * @param $type
	 *
	 * @return mixed
	 * @since 3.0.0
	 */
	public function getPickTypeText($type){
		$types = $this->getPickTypesArray();
		if(!in_array($type, $types, true)){
			return '';
		}
		$types = $this->getPickTypes();
		return $types[$type];
	}
}