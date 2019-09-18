<?php

/**
 * Plugin name: Continent Filter
 * Description: Filters pages depending on which continent the request came from.
 * Author: Felföldi László
 * Version: 0.0.1
 */

class KDContinentFilter {

	private static $instance;
	public static function getInstance() {
		if ( !( self::$instance instanceof KDContinentFilter ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private $continent_codes = [
		'af' => 'Afrika',
		'an' => 'Antarktika',
		'as' => 'Ázsia',
		'eu' => 'Európa',
		'na' => 'Észak-Amerika',
		'oc' => 'Óceánia',
		'sa' => 'Dél-Amerika'
	];

	public function __construct() {

		add_action( 'init', [ $this, 'init' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue' ] );
		add_action( 'pre_get_posts', [ $this, 'pre_get_posts' ] );

		add_action( 'kd_continents_add_form_fields', [ $this, 'add_form_fields' ] );
		add_action( 'kd_continents_edit_form_fields', [ $this, 'edit_form_fields' ] );
		add_action( 'edited_kd_continents', [ $this, 'edited_term' ] );

		add_filter( 'manage_page_posts_columns', [ $this, 'columns' ] );
		add_action( 'manage_page_posts_custom_column', [ $this, 'custom_column' ] );
		add_action( 'restrict_manage_posts', [ $this, 'filter' ] );
		add_filter( 'display_post_states', [ $this, 'states' ], 10, 2 );

		add_filter( 'wp_nav_menu_args', [ $this, 'menu' ] );

	}

	public function init() {

		if ( !defined( 'KD_CONTINENT_CODE' ) ) define( 'KD_CONTINENT_CODE', $_SESSION['continent'] ?: $this->get_continent_by_ip() );

		register_taxonomy( 'kd_continents', 'page', [
			'labels' => [
				'name' => 'Kontinensek'
			],
			'hierarchical' => true
		] );

		$terms = get_terms([
			'taxonomy' => 'kd_continents',
			'hide_empty' => false
		]);

		$home_term = get_post_meta( get_option( 'page_on_front' ), 'kd_continent_homepage_of', true );

		$continent_menus = [];
		foreach( get_registered_nav_menus() as $location => $description ) {

			foreach( $terms as $term ) {

				if ( $term->term_id != $home_term ) {
					$continent_menus[ $location . '-' . $term->slug ] = $description . ' - ' . $term->name;
				}
			}

		}

		register_nav_menus( $continent_menus );

	}

	public function admin_enqueue() {

		global $pagenow;
		if ( ( $pagenow == 'edit-tags.php' && $_GET['taxonomy'] == 'kd_continents' ) || ( $pagenow == 'nav-menus.php' && ( !isset( $_GET['menu'] ) || $_GET['menu'] > 0 ) ) ) {

			wp_register_script( 'kd_continent_filter_admin_script', plugins_url( 'js/admin.js', __FILE__ ), [ 'jquery' ] );
			wp_localize_script( 'kd_continent_filter_admin_script', 'KDContinentFilter', [
				'continent_codes' => $this->continent_codes
			] );
			wp_enqueue_script( 'kd_continent_filter_admin_script' );

		}

	}

	public function pre_get_posts( $query ) {

		$object = get_queried_object();

		if ( in_array( KD_CONTINENT_CODE, get_terms([ 'taxonomy' => 'kd_continent', 'fields' => 'slugs' ]) ) && !is_admin() && $query->is_main_query() && is_page() && $object ) {

			$post_terms = wp_get_post_terms( $object->ID, 'kd_continents', [ 'fields' => 'ids' ] );
			$continent_term = get_term_by( 'slug', KD_CONTINENT_CODE, 'kd_continents', OBJECT );

			if ( !in_array( $continent_term->term_id , $post_terms ) ) {

				if ( $object->ID == get_option( 'page_on_front' ) ) {

					$post = get_posts([ 
						'post_type' => 'page',
						'kd_continents' => KD_CONTINENT_CODE,
						'meta_query' => [
							'key' => 'kd_continent_homepage_of',
							'value' => $continent_term->term_id
						]
					]);

					if ( empty( $post ) ) $query->set_404();
					else $query->set( 'page_id', $post[0]->ID );

				} else {

					$query->set_404();

				} 

			}

		}

	}

	public function add_form_fields() {

		$posts = get_posts([
			'post_type' => 'page',
			'language' => 'all',
			'posts_per_page' => -1
		]);

		include( __DIR__ . '/views/add_form.php' );

	}

	public function edit_form_fields( $term ) {

		$posts = get_posts([
			'post_type' => 'page',
			'language' => 'all',
			'posts_per_page' => -1
		]);

		include( __DIR__ . '/views/edit_form.php' );

	}

	public function columns( $columns ) {

		$this->kd_add_assoc( $columns, [ 'continent' => 'Kontinens' ], 3 );
		return $columns;

	}

	public function custom_column( $column ) {

		switch( $column ) {
			case 'continent' : {

				$terms = wp_get_post_terms( get_the_ID(), 'kd_continents', [ 'fields' => 'names' ] );

				if ( !empty( $terms ) ) { 
					echo implode( ', ', $terms );
				} else echo '—';

			} break;
		}

	}

	public function filter( $post_type ) {

		if ( 'page' != $post_type ) return;

		wp_dropdown_categories([
			'show_option_all' => 'Összes kontinens',
			'orderby' => 'name',
			'order' => 'ASC',
			'value_field' => 'slug',
			'name' => 'kd_continents',
			'taxonomy' => 'kd_continents',
			'selected' => $_GET['kd_continents']
		]);

	}

	public function edited_term( $term_id ) {

		global $wpdb;

		if ( isset( $_POST['_inline_edit'] ) ) return;

		$post = get_post( $_POST['kd_continent_homepage'] );
		if ( empty( $post ) ) return;

		$wpdb->query( $wpdb->prepare( "DELETE FROM " . $wpdb->postmeta . " WHERE meta_key = 'kd_continent_homepage_of' AND meta_value=%d;", $term_id ) );

		update_post_meta( $post->ID, 'kd_continent_homepage_of', $term_id );
		wp_set_post_terms( $post->ID, $term_id, 'kd_continents', false );

	}

	public function states( $post_states, $post ) {

		$continent = get_post_meta( $post->ID, 'kd_continent_homepage_of', true );
		if ( 'page' == get_post_type() && !empty( $continent ) && $post->ID != get_option( 'page_on_front' ) ) {

			$post_states[] = 'Felhasználói oldal (Front Page)';

		}

		return $post_states;

	}

	public function menu( $args ) {

		$home_term = get_term( get_post_meta( pll_get_post( get_option( 'page_on_front' ), pll_default_language() ), 'kd_continent_homepage_of', true ) )->slug;

		if ( in_array( KD_CONTINENT_CODE, get_terms([ 'taxonomy' => 'kd_continent', 'fields' => 'slugs' ]) ) && $home_term != KD_CONTINENT_CODE ) {
			$args['theme_location'] .= '-' . KD_CONTINENT_CODE;
		}
		
		return $args;

	}

	private function get_continent_by_ip( $ip = false ) {
	    $code = false;

	    if (!$ip) {
	        $client = @$_SERVER['HTTP_CLIENT_IP'];
	        $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
	        $remote = @$_SERVER['REMOTE_ADDR'];

	        if (filter_var($client, FILTER_VALIDATE_IP)) {
	            $ip = $client;
	        } elseif (filter_var($forward, FILTER_VALIDATE_IP)) {
	            $ip = $forward;
	        } else {
	            $ip = $remote;
	        }
	    }

	    $response = @json_decode(file_get_contents("http://www.geoplugin.net/json.gp?ip={$ip}"));    

	    if ($response && isset($response->geoplugin_continentCode)) {
	        $code = strtolower( $response->geoplugin_continentCode );
	    }

	    $_SESSION['continent'] = $code;

	    return $code;
	}

	private function kd_add_assoc( &$columns, $column, $position = false ) {

		if ( $position === false ) {
			$columns += $column;
		} else if ( $position === 0 ) {
			$columns = $column + $columns;
		} else {

			$end = array_slice( $columns, $position );
			array_splice( $columns, $position );
			$columns += $column + $end;

		}

	}

}

KDContinentFilter::getInstance();