<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

class WP_Central_WordPress_Release {

	private static $api = 'http://10.133.166.181/stats-service';

	private $version;

	private $data;

	public function __construct( $version ) {
		$this->version = $version;

		$this->load_release_data();
	}


	//
	// Public facing methods
	//

	public function get_title() {
		if ( isset( $this->data->name ) && $this->data->name ) {
			$title = sprintf( __( 'WordPress %s “%s”' ), $this->version, $this->data->name );
		}
		else {
			$title = sprintf( __( 'WordPress %s' ), $this->version );
		}

		return $title;
	}

	public function get_releases() {
		if ( false === ( $releases = get_transient( 'wordpress_releases_' . $this->version ) ) ) {
			$request  = wp_remote_get( self::$api . '/releases/' . $this->version );
			$releases = json_decode( wp_remote_retrieve_body( $request ) );

			set_transient( 'wordpress_releases_' . $this->version, $releases, DAY_IN_SECONDS );
		}

		if ( ! $releases ) {
			$releases = array();
		}

		return $releases;
	}

	public function get_download_count() {
		if ( false === ( $count = get_transient( 'wordpress_downloads_' . $this->version ) ) ) {
			$request = wp_remote_get( self::$api . '/count/' . $this->version );
			$data    = json_decode( wp_remote_retrieve_body( $request ) );

			if ( $data ) {
				$count = number_format( $data->count );
			}
			else {
				$count = 0;
			}

			set_transient( 'wordpress_downloads_' . $this->version, $count, 60 - date('s') );
		}

		return $count;
	}

	public function downloads_per_day() {
		if ( false === ( $data = get_transient( 'wordpress_downloads_day_' . $this->version ) ) ) {
			$request = wp_remote_get( self::$api . '/count-history/' . $this->version );
			$data    = json_decode( wp_remote_retrieve_body( $request ) );

			set_transient( 'wordpress_downloads_day_' . $this->version, $data, 600 );
		}

		return $data;
	}

	public function downloads_last7days() {
		global $wp_locale;

		if ( false === ( $count = get_transient( 'downloads_last7days_' . $this->version ) ) ) {
			$request = wp_remote_get( self::$api . '/last-7days/' . $this->version );
			$data    = json_decode( wp_remote_retrieve_body( $request ) );

			$count = array();

			if ( $data ) {
				foreach ( $data as $row ) {
					$weekday = ( $row->weekday == 6 ) ? 0 : $row->weekday + 1;

					$count[] = array( 'label' => $weekday, 'value' => absint( $row->downloads ) );
				}
			}

			set_transient( 'downloads_last7days_' . $this->version, $count, 600 );
		}


		for ( $i = 0; $i < count( $count ); ++$i ) {
			$count[ $i ]['label'] = $wp_locale->get_weekday( $count[ $i ]['label'] );
		}

		return $count;
	}

	public function counts_per_hour() {
		$data  = self::get_counts_data( 'hours' );
		$hours = array();

		foreach ( $data as $hour => $value ) {
			$hours[] = array( 'label' => $hour, 'value' => $value );
		}

		return $hours;
	}

	public function counts_per_day() {
		global $wp_locale;

		$data = self::get_counts_data( 'days' );
		$days = array();

		foreach ( $data as $day => $value ) {
			$days[] = array( 'label' => $wp_locale->get_weekday( $day ), 'value' => $value );
		}

		return $days;
	}


	//
	// Internal methods
	//

	private function load_release_data() {
		if ( $this->data ) {
			return;
		}

		if ( false === ( $data = get_transient( 'wordpress_versions_' . $this->version ) ) ) {
			$request = wp_remote_get( self::$api . '/versions/' . $this->version );
			$data    = json_decode( wp_remote_retrieve_body( $request ) );

			if ( $data ) {
				set_transient( 'wordpress_versions_' . $this->version, $data, DAY_IN_SECONDS );
			}
			else {
				$data = new stdClass;
			}
		}

		$this->data = $data;
	}

	private function get_counts_data( $type ) {
		if ( 'hours' != $type && 'days' != $type ) {
			return array();
		}

		if ( false === ( $data = get_transient( 'wordpress_counts_' . $this->version . '_' . $type ) ) ) {
			$request = wp_remote_get( self::$api . '/count-stats/' . $this->version );
			$counts  = json_decode( wp_remote_retrieve_body( $request ) );

			if ( $counts ) {
				set_transient( 'wordpress_counts_' . $this->version . '_days', $counts->days, 600 );
				set_transient( 'wordpress_counts_' . $this->version . '_hours', $counts->hours, 600 );

				$data = $counts->$type;
			}
			else {
				$data = array();
			}
		}

		return $data;
	}

}