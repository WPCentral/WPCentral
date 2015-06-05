<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

class WP_Central_Stats_Collector {
	private $version = '1.0';
	private $api_url = 'http://api.wordpress.org/stats/';

	private $request_time;

	public function __construct() {
		add_action( 'cron_wordpress_stats', array( $this, 'cronjob_fast' ) );
		add_action( 'cron_wordpress_stats_daily', array( $this, 'cronjob_daily' ) );
	}


	// Type = Downloads, MySQL, PHP, WordPress
	public function cronjob_fast() {
		$count = $this->get_current_wordpress_downloads();

		if ( false !== $count ) {
			$this->add_stat( 'downloads', WP_Central_Stats::wp_version(), $count );
		}
	}

	public function cronjob_daily() {
		$wordpress = $this->get_current_wordpress_usage();
		foreach ( $wordpress as $version => $percentage ) {
			$this->add_stat( 'wordpress', $version, $percentage );
		}

		$php = $this->get_current_php_usage();
		foreach ( $php as $version => $percentage ) {
			$this->add_stat( 'php', $version, $percentage );
		}

		$mysql = $this->get_current_mysql_usage();
		foreach ( $mysql as $version => $percentage ) {
			$this->add_stat( 'mysql', $version, $percentage );
		}

		// Delete the caches
		delete_transient('wordpress_versions');
		delete_transient('php_versions');
		delete_transient('mysql_versions');
	}



	public static function get_current_wordpress_downloads() {
		$response = wp_remote_get( 'http://wordpress.org/download/counter/?ajaxupdate=1&time=' . time() );

		if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) != 200 ) {
			self::report_error( $response );

			return false;
		}
		else {
			$count = wp_remote_retrieve_body( $response );

			$count = str_replace( '.', '', $count );
			$count = str_replace( ',', '', $count );
			
			return $count;
		}
	}

	public function get_current_wordpress_usage() {
		$response = wp_remote_get( $this->api_url . 'wordpress/1.0/' );

		if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) != 200 ) {
			$this->report_error( $response );

			return false;
		}
		else {
			$data = json_decode( wp_remote_retrieve_body( $response ) );

			return $data;
		}
	}

	public function get_current_php_usage() {
		$response = wp_remote_get( $this->api_url . 'php/1.0/' );

		if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) != 200 ) {
			$this->report_error( $response );

			return false;
		}
		else {
			$data = json_decode( wp_remote_retrieve_body( $response ) );

			return $data;
		}
	}

	public function get_current_mysql_usage() {
		$response = wp_remote_get( $this->api_url . 'mysql/1.0/' );

		if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) != 200 ) {
			$this->report_error( $response );

			return false;
		}
		else {
			$data = json_decode( wp_remote_retrieve_body( $response ) );

			return $data;
		}
	}



	public function add_stat( $type, $version, $count ) {
		global $wpdb;

		if ( false === $count ) {
			$this->report_error( 'The value of count was false:' . $type . ' - ' . $version );
		}

		if ( ! $this->request_time ) {
			$this->request_time = get_gmt_from_date( current_time( 'mysql' ) );
		}

		$data             = array();
		$data['type']     = $type;
		$data['version']  = $version;
		$data['count']    = $count;
		$data['date_gmt'] = $this->request_time;
		
		$wpdb->insert( WP_Central_Stats::db_table(), $data, array( '%s', '%s', '%f', '%s' ) );
	}


	private static function report_error( $response ) {
		
	}

}