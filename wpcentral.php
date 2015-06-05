<?php
/*
	Plugin Name: WP Central
	Plugin URI:  http://wpcentral.io
	Description: The code behind WP Central
	Version:     1.0
	Author:      markoheijnen
	Author URI:  http://markoheijnen.com
	License:     GPL

	Text Domain: wpcentral
*/


if ( ! defined('ABSPATH') ) {
	die();
}

include 'inc/contributors.php';
include 'inc/data-collector.php';
include 'inc/graph.php';
include 'inc/json-api.php';
include 'inc/stats.php';
include 'inc/stats-collector.php';
include 'inc/wordpress-api.php';

// Graphs
include 'inc/graphs/morris.php';
include 'inc/graphs/chartjs.php';

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	include 'wpcli/load.php';
}

class WP_Central {

	const version = '1.1';

	private $stats;

	public function __construct() {
		new WP_Central_Contributor;
		new WP_Central_Graph;
		new WP_Central_JSON_API;
		$this->stats = new WP_Central_Stats;

		register_activation_hook( __FILE__, array( $this, 'install' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
		add_filter( 'cron_schedules', array( $this, 'cron_schedules' ) );
	}


	/**
	 * Calls the install methods of the features
	 *
	 * @since 1.1.0
	 */
	public function install() {
		$this->stats->install();
	}

	/**
	 * Calls the deactivate methods of the features
	 *
	 * @since 1.1.0
	 */
	public function deactivate() {
		$this->stats->deactivate();
	}


	/**
	 * Load plugin textdomain.
	 *
	 * @since 1.0.0
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'wpcentral', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' ); 
	}

	/**
	 * Adds a minutly cron
	 *
	 * @since 1.1.0
	 */
	public function cron_schedules( $schedules ) {
		$schedules['minutly'] = array( 'interval' => MINUTE_IN_SECONDS, 'display' => __( 'Every minute', 'wordpress-stats' ) );

		return $schedules;
	}

}

$wp_central = new WP_Central;