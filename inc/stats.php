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
			$data    = array_filter( $data, array( __CLASS__, 'make_value_number' ) );

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
		return array(
			'3.3',
			'3.4',
			'3.5',
			'3.6',
			'3.7',
			'3.8',
			'3.9',
			'4.0',
			'4.1',
			'4.2',
		);
	}

	public static function get_minor_releases( $major = null ) {
		if( $major == null ) {
			$major = self::wp_version();
		}

		$releases = array(
			'4.2' => array(
				'4.2.4' => array(
					'title' => 'WordPress 4.2.4 Security and Maintenance Release',
					'link'  => 'https://wordpress.org/news/2015/08/wordpress-4-2-4-security-and-maintenance-release/'
				),
				'4.2.3' => array(
					'title' => 'WordPress 4.2.3 Security and Maintenance Release',
					'link'  => 'https://wordpress.org/news/2015/07/wordpress-4-2-3/'
				),
				'4.2.2' => array(
					'title' => 'WordPress 4.2.2 Security and Maintenance Release',
					'link'  => 'https://wordpress.org/news/2015/05/wordpress-4-2-2/'
				),
				'4.2.1' => array(
					'title' => 'WordPress 4.2.1 Security Release',
					'link'  => 'https://wordpress.org/news/2015/04/wordpress-4-2-1/'
				),
				'4.2' => array(
					'title' => 'WordPress 4.2 “Powell”',
					'link'  => 'https://wordpress.org/news/2015/04/powell/'
				)
			),
			'4.1' => array(
				'4.1.2' => array(
					'title' => 'WordPress 4.1.2 Security Release',
					'link'  => 'https://wordpress.org/news/2015/04/wordpress-4-1-2/'
				),
				'4.1.1' => array(
					'title' => 'WordPress 4.1.1 Maintenance Release',
					'link'  => 'https://wordpress.org/news/2015/02/wordpress-4-1-1/'
				),
				'4.1' => array(
					'title' => 'WordPress 4.1 “Dinah”',
					'link'  => 'https://wordpress.org/news/2014/12/dinah/'
				)
			),
			'4.0' => array(
				'4.0.1' => array(
					'title' => 'WordPress 4.0.1',
					'link'  => 'https://wordpress.org/news/2014/11/wordpress-4-0-1/'
				),
				'4.0' => array(
					'title' => 'WordPress 4.0 “Benny”',
					'link'  => 'https://wordpress.org/news/2014/09/benny/'
				)
			),
			'3.9' => array(
				'3.9.2' => array(
					'title' => 'WordPress 3.9.2 Security Release',
					'link'  => 'https://wordpress.org/news/2014/08/wordpress-3-9-2/'
				),
				'3.9.1' => array(
					'title' => 'WordPress 3.9.1 Maintenance Release',
					'link'  => 'https://wordpress.org/news/2014/05/wordpress-3-9-1/'
				),
				'3.9' => array(
					'title' => 'WordPress 3.9 “Smith”',
					'link'  => 'https://wordpress.org/news/2014/04/smith/'
				)
			),
			'3.8' => array(
				'3.8.3' => array(
					'title' => 'WordPress 3.8.3 Maintenance Release',
					'link'  => 'https://wordpress.org/news/2014/04/wordpress-3-8-3/'
				),
				'3.8.2' => array(
					'title' => 'WordPress 3.8.2 Security Release',
					'link'  => 'https://wordpress.org/news/2014/04/wordpress-3-8-2/'
				),
				'3.8.1' => array(
					'title' => 'WordPress 3.8.1 Maintenance Release',
					'link'  => 'https://wordpress.org/news/2014/01/wordpress-3-8-1/'
				),
				'3.8' => array(
					'title' => 'WordPress 3.8 “Parker”',
					'link'  => 'https://wordpress.org/news/2013/12/parker/'
				)
			),
			'3.7' => array(
				'3.7.1' => array(
					'title' => 'WordPress 3.7.3 Security Release',
					'link'  => 'https://wordpress.org/news/2014/04/wordpress-3-8-2/'
				),
				'3.7.1' => array(
					'title' => 'WordPress 3.7.1 Maintenance Release',
					'link'  => 'https://wordpress.org/news/2013/10/wordpress-3-7-1/'
				),
				'3.7' => array(
					'title' => 'WordPress 3.7 “Basie”',
					'link'  => 'https://wordpress.org/news/2013/10/basie/'
				)
			),
			'3.6' => array(
				'3.6.1' => array(
					'title' => 'WordPress 3.6.1 Maintenance and Security Release',
					'link'  => 'https://wordpress.org/news/2013/09/wordpress-3-6-1/'
				),
				'3.6' => array(
					'title' => 'WordPress 3.6 “Oscar”',
					'link'  => 'https://wordpress.org/news/2013/08/oscar/'
				)
			),
			'3.5' => array(
				'3.5.2' => array(
					'title' => 'WordPress 3.5.2 Maintenance and Security Release',
					'link'  => 'https://wordpress.org/news/2013/06/wordpress-3-5-2/'
				),
				'3.5.1' => array(
					'title' => 'WordPress 3.5.1 Maintenance and Security Release',
					'link'  => 'https://wordpress.org/news/2013/01/wordpress-3-5-1/'
				),
				'3.5' => array(
					'title' => 'WordPress 3.5 “Elvin”',
					'link'  => 'https://wordpress.org/news/2012/12/elvin/'
				)
			),
			'3.4' => array(
				'3.4.2' => array(
					'title' => 'WordPress 3.4.2 Maintenance and Security Release',
					'link'  => 'https://wordpress.org/news/2012/09/wordpress-3-4-2/'
				),
				'3.4.1' => array(
					'title' => 'WordPress 3.4.1 Maintenance and Security Release',
					'link'  => 'https://wordpress.org/news/2012/06/wordpress-3-4-1/'
				),
				'3.4' => array(
					'title' => 'WordPress 3.4 “Green”',
					'link'  => 'https://wordpress.org/news/2012/06/green/'
				)
			),
			'3.3' => array(
				'3.3.1' => array(
					'title' => 'WordPress 3.3.1 Security and Maintenance Release',
					'link'  => 'https://wordpress.org/news/2012/01/wordpress-3-3-1/'
				),
				'3.3' => array(
					'title' => 'WordPress 3.3 “Sonny”',
					'link'  => 'https://wordpress.org/news/2011/12/sonny/'
				)
			),
		);

		if( isset( $releases[ $major ] ) ) {
			return $releases[ $major ];
		}

		return array();
	}


	public static function wordpress_version() {
		if ( false === ( $data = get_transient( 'wordpress_versions' ) ) ) {
			global $wpdb;

			$table = self::db_table();
			$query = "SELECT s1.count as value, s1.version as label FROM {$table} as s1 LEFT JOIN {$table} s2 ON (s1.type = s2.type AND s1.date_gmt < s2.date_gmt) WHERE s1.type='wordpress' AND s2.type IS NULL AND s1.date_gmt > DATE_SUB(CURDATE(), INTERVAL 25 HOUR)";
			$data = $wpdb->get_results( $query );

			$data = array_values( array_filter( $data, array( __CLASS__, 'make_value_number' ) ) );

			set_transient( 'wordpress_versions', $data, HOUR_IN_SECONDS );
		}

		return $data;
	}

	public static function php_version() {
		if ( false === ( $data = get_transient( 'php_versions' ) ) ) {
			global $wpdb;

			$table = self::db_table();
			$query = "SELECT s1.count as value, s1.version as label FROM {$table} as s1 LEFT JOIN {$table} s2 ON (s1.type = s2.type AND s1.date_gmt < s2.date_gmt) WHERE s1.type='php' AND s2.type IS NULL AND s1.date_gmt > DATE_SUB(CURDATE(), INTERVAL 25 HOUR)";
			$data = $wpdb->get_results( $query );

			$data = array_values( array_filter( $data, array( __CLASS__, 'make_value_number' ) ) );

			set_transient( 'php_versions', $data, HOUR_IN_SECONDS );
		}

		return $data;
	}

	public static function mysql_version() {
		if ( false === ( $data = get_transient( 'mysql_versions' ) ) ) {
			global $wpdb;

			$table = self::db_table();
			$query = "SELECT s1.count as value, s1.version as label FROM {$table} as s1 LEFT JOIN {$table} s2 ON (s1.type = s2.type AND s1.date_gmt < s2.date_gmt) WHERE s1.type='mysql' AND s2.type IS NULL AND s1.date_gmt > DATE_SUB(CURDATE(), INTERVAL 25 HOUR)";
			$data = $wpdb->get_results( $query );

			$data = array_values( array_filter( $data, array( __CLASS__, 'make_value_number' ) ) );

			set_transient( 'mysql_versions', $data, HOUR_IN_SECONDS );
		}

		return $data;
	}


	/**
	 * @return array
	 */
	public static function wordpress_version_by_day() {
		global $wpdb;

		//$query = "SELECT version, GROUP_CONCAT(count) as mycount FROM ".self::db_table()." WHERE type='php' GROUP BY version";
		$table = self::db_table();
		$query = "SELECT DATE_FORMAT(date_gmt,'%X W%V') AS date, version, AVG(count) AS count
		FROM {$table}
		WHERE TYPE='wordpress' AND VERSION NOT IN ('2.7', '2.8', '2.9')
		GROUP BY DATE_FORMAT(date_gmt,'%X W%V'), version";

		$results = $wpdb->get_results( $query );
		$data    = array();

		foreach ( $results as $item ) {
			$data[ $item->date ]['date']           = $item->date;
			$data[ $item->date ][ $item->version ] = round( $item->count, 2 );
		}

		$data = array_values( $data );

		return $data;
	}

	/**
	 * @return array
	 */
	public static function php_version_by_day() {
		global $wpdb;

		$table = self::db_table();
		$query = "SELECT DATE_FORMAT(date_gmt,'%X W%V') AS date, version, AVG(count) AS count
		FROM {$table}
		WHERE type='php' AND version NOT IN ('4.3', '4.4', '5.0', '5.7')
		GROUP BY DATE_FORMAT(date_gmt,'%X W%V'), version";

		$results = $wpdb->get_results( $query );
		$data    = array();

		foreach ( $results as $item ) {
			$data[ $item->date ]['date']           = $item->date;
			$data[ $item->date ][ $item->version ] = round( $item->count, 2 );
		}

		$data = array_values( $data );

		return $data;
	}

	/**
	 * @return array
	 */
	public static function mysql_version_by_day() {
		global $wpdb;

		$table = self::db_table();
		$query = "SELECT DATE_FORMAT(date_gmt,'%X W%V') AS date, version, AVG(count) AS count
		FROM {$table}
		WHERE type='mysql' AND version NOT IN ('3.23', '4.0', '4.1', '5.', '5.13', '5.2', '5.3', '5.4', '5.7')
		GROUP BY DATE_FORMAT(date_gmt,'%X W%V'), version";

		$results = $wpdb->get_results( $query );
		$data    = array();

		foreach ( $results as $item ) {
			$data[ $item->date ]['date']           = $item->date;
			$data[ $item->date ][ $item->version ] = round( $item->count, 2 );
		}

		$data = array_values( $data );

		return $data;
	}


	public static function make_value_number( $item ) {
		if ( isset( $item->value ) ) {
			$item->value = (float) $item->value;

			if ( $item->value ) {
				return $item;
			}
		}
		else if ( isset( $item->count ) ) {
			$item->count = (float) $item->count;

			return $item;
		}

		return false;
	}

}