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
include 'inc/json-api.php';
include 'inc/wordpress-api.php';

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	include 'wpcli/load.php';
}

class WP_Central {

	const version = '1.0';

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
		load_plugin_textdomain( 'wpcentral', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' ); 
	}

}

$wp_central = new WP_Central;