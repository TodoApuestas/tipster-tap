<?php
namespace TipsterTAP\Rest;

class TipsterTapREST {
	/**
	 * Instance of this class.
	 *
	 * @since    3.8
	 *
	 * @var      object
	 */
	protected static $instance = null;
	
	private function __construct() {
		add_action( 'rest_api_init', array( $this, 'init_routes' ) );
	}
	
	/**
	 * Return an instance of this class.
	 *
	 * @since     3.8
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
	 * @return bool|void
	 *
	 * @since 3.8
	 */
	public function init_routes(){
		if ( ! function_exists( 'register_rest_route' ) ) {
			// The REST API wasn't integrated into core until 4.4, and we support 4.4+ (for now).
			return false;
		}
		
		register_rest_route('tipster-tap/v4', '/picks/(?P<tipster>\d+)/(?P<yearmonth>\d{4}-\d{2})', array(
				'methods' => 'GET',
				'callback' => array( $this, 'get_picks_by_tipster' ),
                'permission_callback' => function() {
                    return true;
                },
				'args' => array(
					'tipster' => array(
						'validate_callback' => function($param, $request, $key){
							return is_numeric($param);
						}
					),
					'yearmonth' => array(
						'validate_callback' => function($param, $request, $key){
							if(false !== preg_match('/\d{4}-\d{2}/', $param)){
								$date = new \DateTime($param);
								if($date instanceof \DateTime && strcmp($param, $date->format('Y-m')) === 0){
									return true;
								}
							}
							return false;
						}
					)
				)
			)
		);
	}
	
	/**
	 * @param $data
	 *
	 * @return array
	 *
	 * @throws \Exception
	 *
	 * @since 3.8
	 */
	public function get_picks_by_tipster($data){
		$date_start = new \DateTime($data['yearmonth']);
		$date_end = new \DateTime($data['yearmonth']);
		$date_end->add(new \DateInterval('P1M'));
		
		$date_range = array(
			'start' => $date_start->getTimestamp(),
			'end' => $date_end->getTimestamp()
		);
		
		$args = array(false, $date_range, false, false, false);
		$picks = apply_filters('tipster_tap_get_picks', $data['tipster'], $args);
		
//		$bookies = get_option('TAP_BOOKIES');
		
		$dtPicks = array();
		foreach ( $picks as $pick ) {
			$pick_id = $pick['pick_id'];
			$cuota = (double)$pick['pick_cuote']; //number_format_i18n($pick['pick_cuote'], 2);
			$stake = (double)$pick['pick_stake']; //number_format_i18n($pick['pick_stake'], 2);
			$evento = get_post_meta($pick_id, '_pick_evento', true);
			$pick_datetime = $pick['pick_datetime'];
			$fecha_hora = new \DateTime();
			$fecha_hora->setTimestamp($pick_datetime);
			$fecha_hora_str = '';
			if($fecha_hora instanceof \DateTime){
				$fecha_hora_str = $fecha_hora->format( 'd-m-Y H:i' );
			}
			$resultado = $pick['pick_result'];
			
			$ganancia = 0;
			switch ($resultado){
				case 'acierto':
					$ganancia = $stake * $cuota - $stake;
					break;
				case 'fallo':
					$ganancia = -$stake;
					break;
				case 'nulo':
				default:
					break;
			}
			
			$post = get_post( $pick_id );
			$post_type_object = get_post_type_object( $post->post_type );
			$link = admin_url( sprintf( $post_type_object->_edit_link . '&action=edit', $post->ID ) );
			
			$dtPicks[] = array(
				'id' => $pick_id,
				'evento' => array(
					'display' => sprintf('<a href="%1$s" title="%2$s" target="_blank">%3$s</a>', esc_url($link), get_the_title($post->ID), $evento),
					'sort' => strtolower($evento)
				),
				'fecha' => array(
					'display' => $fecha_hora_str,
					'sort' => $fecha_hora->getTimestamp()
				),
				'cuota' => number_format_i18n($cuota, 2),
				'stake' => number_format_i18n($stake, 2),
				'ganancia' => number_format_i18n($ganancia, 2),
				'resultado' => array(
					'display' => ucfirst($resultado),
					'sort' => $resultado
				),
				'accion' => sprintf('<a href="%s" target="_blank">Editar</a> | <a href="%s" target="_blank">Ver</a>', esc_url($link), esc_url(get_the_permalink($pick_id)))
			);
		}
		
		return array('data' => $dtPicks);
	}
}