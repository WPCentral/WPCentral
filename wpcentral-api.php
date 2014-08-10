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

include 'inc/data-collector.php';
include 'inc/wordpress-api.php';

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	include 'wpcli/load.php';
}

class WP_Central_API {

	/**
	 * Base route name
	 */
	protected $base = '/users';


	public function __construct() {
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );

		add_filter( 'json_url_prefix', array( $this, 'json_url_prefix' ) );
		add_filter( 'json_endpoints', array( $this, 'register_routes' ), 30 );
	}


	/**
	 * Load plugin textdomain.
	 *
	 * @since 1.0.0
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'wpcentral-api', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' ); 
	}


	public function json_url_prefix() {
		return 'api';
	}

	/**
	 * Register the routes for the post type
	 *
	 * @param array $routes Routes for the post type
	 * @return array Modified routes
	 */
	public function register_routes( $routes ) {
		$routes[ $this->base . '/(?P<username>\w+)'] = array(
			array( array( $this, 'get_user' ), WP_JSON_Server::READABLE ),
		);

		return $routes;
	}

	public function get_user( $username ) {
		if ( ! username_exists( $username ) ) {
			return false;
		}

		$current_user_id = get_current_user_id();

		$user = get_userdata( 1 );

		if ( empty( $user->ID ) ) {
			return new WP_Error( 'json_user_invalid_id', __( 'Invalid user ID.' ), array( 'status' => 400 ) );
		}

		return $this->prepare_user( $user );
	}


	/**
	 *
	 * Prepare a User entity from a WP_User instance.
	 *
	 * @return array
	 */
	protected function prepare_user( $user ) {
		$user_fields = array(
			'username'    => $user->user_login,
			'first_name'  => $user->first_name,
			'last_name'   => $user->last_name,
			'URL'         => $user->user_url,
			'avatar'      => json_get_avatar_url( $user->user_email ),
			'description' => $user->description,
		);

		$user_fields['meta'] = array(
			'links' => array(
				'self' => json_url( $this->base .'/' . $user->user_login ),
			),
		);

		return apply_filters( 'wpcentral_api_prepare_user', $user_fields, $user );
	}

}

$wp_central_api = new WP_Central_API;