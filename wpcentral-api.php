<?php
/*
	Plugin Name: WP Central API
	Plugin URI:  http://wpcentral.io
	Description: The API for WP Central
	Version:     1.0
	Author:      markoheijnen
	Author URI:  http://markoheijnen.com
	License:     GPL

	Text Domain: wpcentral-api
*/


if ( ! defined('ABSPATH') ) {
	die();
}

include 'inc/contributors.php';
include 'inc/data-collector.php';
include 'inc/json-api.php';
include 'inc/wordpress-api.php';

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	include 'wpcli/load.php';
}

class WP_Central_API {

	const version = '1.0';

	/**
	 * Base route name
	 */
	protected $base = '/contributors';


	public function __construct() {
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );

		new WP_Central_Contributor;
		new WP_Central_JSON_API;
	}


	/**
	 * Load plugin textdomain.
	 *
	 * @since 1.0.0
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'wpcentral-api', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' ); 
	}

}

$wp_central_api = new WP_Central_API;