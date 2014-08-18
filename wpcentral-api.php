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
include 'inc/wordpress-api.php';

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	include 'wpcli/load.php';
}

class WP_Central_API {

	/**
	 * Base route name
	 */
	protected $base = '/contributors';


	public function __construct() {
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );

		add_filter( 'json_url_prefix', array( $this, 'json_url_prefix' ) );
		add_filter( 'json_endpoints', array( $this, 'register_routes' ), 30 );

		new WP_Central_Contributor;
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

		$routes[ $this->base . '/(?P<username>\w+)/meta/(?P<key>\w+)'] = array(
			array( array( $this, 'get_user_meta' ), WP_JSON_Server::READABLE ),
		);

		return $routes;
	}

	public function get_user( $username ) {
		if ( ! $contributor = get_page_by_path( $username, OBJECT, 'contributor' ) ) {
			$created = WP_Central_Contributor::create( $username );

			if ( ! $created ) {
				return new WP_Error( 'json_user_invalid_id', __( "User doesn't exist." ), array( 'status' => 400 ) );
			}
		}

		return $this->prepare_contributor( $contributor );
	}

	public function get_user_meta( $username, $key ) {
		if ( ! $contributor = get_page_by_path( $username, OBJECT, 'contributor' ) ) {
			$created = WP_Central_Contributor::create( $username );

			if ( ! $created ) {
				return new WP_Error( 'json_user_invalid_id', __( "User doesn't exist." ), array( 'status' => 400 ) );
			}
		}

		$user_fields = array(
			'data' => WP_Central_Data_Colector::get_wp_user_data( $contributor, $contributor->post_name, $key )
		);

		if ( ! $user_fields['data'] ) {
			return new WP_Error( 'json_user_invalid_id', __( 'This meta key is not an option' ), array( 'status' => 400 ) );
		}

		$user_fields['meta'] = array(
			'links' => array(
				'self'    => json_url( $this->base .'/' . $contributor->post_name ) . '/meta/' . $key,
				'profile' => json_url( $this->base .'/' . $contributor->post_name ),
			),
		);

		return apply_filters( 'wpcentral_api_prepare_user', $user_fields, $contributor );
	}

	/**
	 *
	 * Prepare a User entity from a WP_User instance.
	 *
	 * @return array
	 */
	protected function prepare_contributor( $contributor ) {
		$user_fields = array(
			'username'    => $contributor->post_name,
			'name'        => $contributor->post_title,
			'avatar'      => $contributor->avatar,
			'location'    => $contributor->location,
			'company'     => $contributor->company,
			'website'     => $contributor->website,
			'socials'     => $contributor->socials,
			'badges'      => $contributor->badges,
		);

		$user_fields = wp_parse_args( WP_Central_Data_Colector::get_wp_user_data( $contributor, $contributor->post_name ), $user_fields );

		$user_fields['meta'] = array(
			'links' => array(
				'self' => json_url( $this->base .'/' . $contributor->post_name ),
			),
		);

		return apply_filters( 'wpcentral_api_prepare_user', $user_fields, $contributor );
	}

}

$wp_central_api = new WP_Central_API;