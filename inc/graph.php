<?php

if ( ! defined('ABSPATH') ) {
	die();
}

class WP_Central_Graph {

	public function __construct() {
		add_action( 'init', array( $this, 'register_script' ) );

		$this->load_shortcodes();
	}


	public function register_script() {
		wp_register_script( 'raphael', plugins_url( '/js/raphael.min.js', dirname( __FILE__ ) ), array() );
	}


	public static function get( $type, $method, $data, $args = array() ) {
		$class_name = 'WP_Central_Graph_' . $type;

		if ( class_exists( $class_name ) ) {
			$graph = new $class_name( $data );

			if ( ! is_callable( array( $graph, $method ) ) ) {
				return false;
			}

			return call_user_func( array( $graph, $method ), $args );
		}

		return false;
	}


	private function load_shortcodes() {
		add_shortcode( 'versions_last_year_wordpress', array( $this, 'versions_last_year_wordpress' ) );
		add_shortcode( 'versions_last_year_php', array( $this, 'versions_last_year_php' ) );
		add_shortcode( 'versions_last_year_mysql', array( $this, 'versions_last_year_mysql' ) );

		add_shortcode( 'current_wordpress_versions', array( $this, 'current_wordpress_versions' ) );
		add_shortcode( 'current_php_versions', array( $this, 'current_php_versions' ) );
		add_shortcode( 'current_mysql_versions', array( $this, 'current_mysql_versions' ) );
	}


	public function versions_last_year_wordpress() {
		$data = WP_Central_Stats::wordpress_version_by_day();
		$keys = array_keys( (array) end( $data) );
		unset( $keys[0] );
		$keys = array_values( $keys );

		return $this->get( 'morris', 'area_chart', $data, array( 'x' => 'date', 'y' => $keys, 'label' => $keys, 'ymax' => 100 ) );
	}

	public function versions_last_year_php() {
		$data = WP_Central_Stats::php_version_by_day();
		$keys = array_keys( (array) end( $data) );
		unset( $keys[0] );
		$keys = array_values( $keys );

		return $this->get( 'morris', 'area_chart', $data, array( 'x' => 'date', 'y' => $keys, 'label' => $keys, 'ymax' => 100 ) );
	}

	public function versions_last_year_mysql() {
		$data = WP_Central_Stats::mysql_version_by_day();
		$keys = array_keys( (array) end( $data) );
		unset( $keys[0] );
		$keys = array_values( $keys );

		return $this->get( 'morris', 'area_chart', $data, array( 'x' => 'date', 'y' => $keys, 'label' => $keys, 'ymax' => 100 ) );
	}

	/**
	 * @return bool|string
	 */
	public function current_wordpress_versions() {
		return $this->get( 'chartjs', 'doughnut_chart', WP_Central_Stats::wordpress_version() );
	}

	/**
	 * @return bool|string
	 */
	public function current_php_versions() {
		return $this->get( 'chartjs', 'doughnut_chart', WP_Central_Stats::php_version() );
	}

	/**
	 * @return bool|string
	 */
	public function current_mysql_versions() {
		return $this->get( 'chartjs', 'doughnut_chart', WP_Central_Stats::mysql_version() );
	}

}


abstract class WP_Central_Graph_Abstract {
	protected $data = array();
	protected static $counter = 0;

	public function __construct( $data ) {
		self::$counter++;

		$this->data = $data;

		$this->register_script();
	}

	final protected function unique_id() {
		return 'graph' . self::$counter;
	}

	abstract public function line_chart( $args );
	abstract public function pie_chart( $args );
	abstract public function bar( $args );

}