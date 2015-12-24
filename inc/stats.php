<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

class WP_Central_Stats {

	private static $api = 'http://188.166.68.183/stats-service';

	public static function db_table() {
		global $wpdb;

		return $wpdb->prefix . 'wordpress_stats';
	}

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


	public static function downloads_per_day() {
		if ( false === ( $data = get_transient( 'wordpress_downloads_day' ) ) ) {
			$request = wp_remote_get( self::$api . '/count-history/' . self::wp_version() );
			$data    = json_decode( wp_remote_retrieve_body( $request ) );

			set_transient( 'wordpress_downloads_day', $data, 600 );
		}

		return $data;
	}

	public static function wordpress_downloads() {
		if ( false === ( $count = get_transient( 'wordpress_downloads' ) ) ) {
			$request = wp_remote_get( self::$api . '/count/' . self::wp_version() );
			$data    = json_decode( wp_remote_retrieve_body( $request ) );

			if ( $data ) {
				$count = number_format( $data->count );
			}
			else {
				$count = 0;
			}

			set_transient( 'wordpress_downloads', $count, 60 - date('s') );
		}

		return $count;
	}

	public static function downloads_last7days() {
		global $wp_locale;

		if ( false === ( $count = get_transient( 'downloads_last7days' ) ) ) {
			$request = wp_remote_get( self::$api . '/last-7days/' . self::wp_version() );
			$data    = json_decode( wp_remote_retrieve_body( $request ) );

			$count = array();

			foreach ( $data as $row ) {
				$weekday = ( $row->weekday == 6 ) ? 0 : $row->weekday + 1;

				$count[] = array( 'label' => $weekday, 'value' => absint( $row->downloads ) );
			}

			set_transient( 'downloads_last7days', $count, 600 );
		}


		for ( $i = 0; $i < count( $count ); ++$i ) {
			$count[ $i ]['label'] = $wp_locale->get_weekday( $count[ $i ]['label'] );
		}

		return $count;
	}

	public static function counts_per_hour() {
		$data  = self::get_counts_data( 'hours' );
		$hours = array();

		foreach ( $data as $hour => $value ) {
			$hours[] = array( 'label' => $hour, 'value' => $value );
		}

		return $hours;
	}

	public static function counts_per_day() {
		global $wp_locale;

		$data = self::get_counts_data( 'days' );
		$days = array();

		foreach ( $data as $day => $value ) {
			$days[] = array( 'label' => $wp_locale->get_weekday( $day ), 'value' => $value );
		}

		return $days;
	}

	private static function get_counts_data( $type ) {
		if ( 'hours' != $type && 'days' != $type ) {
			return array();
		}

		if ( false === ( $data = get_transient( 'wordpress_counts_' . $type ) ) ) {
			$request = wp_remote_get( self::$api . '/count-stats/' . self::wp_version() );
			$counts  = json_decode( wp_remote_retrieve_body( $request ) );

			set_transient( 'wordpress_counts_days', $counts->days, 600 );
			set_transient( 'wordpress_counts_hours', $counts->hours, 600 );

			$data = $counts->$type;
		}

		return $data;
	}


	public static function get_major_releases() {
		if ( false === ( $releases = get_transient( 'wordpress_releases' ) ) ) {
			$request = wp_remote_get( self::$api . '/versions' );
			$data    = json_decode( wp_remote_retrieve_body( $request ) );

			if ( $data ) {
				$releases = wp_list_pluck( $data, 'version' );
				set_transient( 'wordpress_releases', $releases, DAY_IN_SECONDS );
			}
		}

		return $releases;
	}

	public static function get_minor_releases( $major = null ) {
		if ( $major == null ) {
			$major = self::wp_version();
		}

		if ( false === ( $releases = get_transient( 'wordpress_releases_' . $major ) ) ) {
			$request  = wp_remote_get( self::$api . '/releases/' . $major );
			$releases = json_decode( wp_remote_retrieve_body( $request ) );

			set_transient( 'wordpress_releases_' . $major, $releases, DAY_IN_SECONDS );
		}

		if ( ! $releases ) {
			$releases = array();
		}

		return $releases;
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