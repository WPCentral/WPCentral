<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

class WP_Central_Stats {

	private static $api = 'http://10.133.166.181/stats-service';

	public static function wp_version( $include_minor = false ) {
		if ( false === ( $version = get_transient( 'wordpress_version' ) ) ) {
			$request = wp_remote_get( 'http://api.wordpress.org/core/version-check/1.7/' );
			$data    = json_decode( wp_remote_retrieve_body( $request ) );

			if ( $data ) {
				$version = $data->offers[0]->current;

				set_transient( 'wordpress_version', $version, 3600 * 6 );
			}
		}

		if ( ! $include_minor ) {
			$version = explode( '.', $version );
			$version = $version[0] . '.' . $version[1];
		}

		return $version;
	}


	public static function get_major_releases() {
		if ( false === ( $releases = get_transient( 'wordpress_releases' ) ) ) {
			$request = wp_remote_get( self::$api . '/versions' );
			$data    = json_decode( wp_remote_retrieve_body( $request ) );

			if ( $data ) {
				$releases = wp_list_pluck( $data, 'version' );
				$releases = array_reverse( $releases );
				set_transient( 'wordpress_releases', $releases, DAY_IN_SECONDS );
			}
		}

		return $releases;
	}

	public static function get_release_data( $major ) {
		return new WP_Central_WordPress_Release( $major );
	}


	public static function wordpress_version() {
		if ( false === ( $data = get_transient( 'wordpress_versions' ) ) ) {
			$request = wp_remote_get( self::$api . '/stats/wordpress' );
			$data    = json_decode( wp_remote_retrieve_body( $request ) );

			set_transient( 'wordpress_versions', $data, DAY_IN_SECONDS );
		}

		return $data;
	}

	public static function php_version() {
		if ( false === ( $data = get_transient( 'php_versions' ) ) ) {
			$request = wp_remote_get( self::$api . '/stats/php' );
			$data    = json_decode( wp_remote_retrieve_body( $request ) );

			set_transient( 'php_versions', $data, DAY_IN_SECONDS );
		}

		return $data;
	}

	public static function mysql_version() {
		if ( false === ( $data = get_transient( 'mysql_versions' ) ) ) {
			$request = wp_remote_get( self::$api . '/stats/mysql' );
			$data    = json_decode( wp_remote_retrieve_body( $request ) );

			set_transient( 'mysql_versions', $data, DAY_IN_SECONDS );
		}

		return $data;
	}


	/**
	 * @return array
	 */
	public static function wordpress_version_by_day() {
		if ( false === ( $data = get_transient( 'wordpress_versions_by_day' ) ) ) {
			$request = wp_remote_get( self::$api . '/stats-history/wordpress' );
			$data    = json_decode( wp_remote_retrieve_body( $request ) );

			set_transient( 'wordpress_versions_by_day', $data, DAY_IN_SECONDS );
		}

		return $data;
	}

	/**
	 * @return array
	 */
	public static function php_version_by_day() {
		if ( false === ( $data = get_transient( 'php_versions_by_day' ) ) ) {
			$request = wp_remote_get( self::$api . '/stats-history/php' );
			$data    = json_decode( wp_remote_retrieve_body( $request ) );

			set_transient( 'php_versions_by_day', $data, HOUR_IN_SECONDS );
		}

		return $data;
	}

	/**
	 * @return array
	 */
	public static function mysql_version_by_day() {
		if ( false === ( $data = get_transient( 'mysql_versions_by_day' ) ) ) {
			$request = wp_remote_get( self::$api . '/stats-history/mysql' );
			$data    = json_decode( wp_remote_retrieve_body( $request ) );

			set_transient( 'mysql_versions_by_day', $data, HOUR_IN_SECONDS );
		}

		return $data;
	}

}