<?php

class WP_Central_WordPress_Api {

	public static function get_plugins( $username, $args = array() ) {
		if ( ! $username ) {
			return false;
		}

		require_once ABSPATH . 'wp-admin/includes/plugin-install.php';

		$defaults = array(
			'author'   => $username,
			'per_page' => 30,
			'fields'   => array( 'description' => false, 'compatibility' => false )
		);

		$data = plugins_api( 'query_plugins', wp_parse_args( $args, $defaults ) );

		if ( $data && isset( $data->plugins ) ) {
			return $data->plugins;
		}

		return false;
	}

	public static function get_themes( $username, $args = array() ) {
		if ( ! $username ) {
			return false;
		}

		$defaults = array(
			'author'   => $username,
			'per_page' => 30,
			'fields'   => array()
		);

		$data = themes_api( 'query_themes', wp_parse_args( $args, $defaults ) );

		if ( $data && isset( $data->themes ) ) {
			return $data->themes;
		}

		return false;
	}

}