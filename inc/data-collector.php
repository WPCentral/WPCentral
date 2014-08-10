<?php

if ( ! defined('ABSPATH') ) {
	die();
}

class WP_Central_Data_Colector {

	public static function get_contributions_of_user( $username ) {
		global $wp_version;

		$version = $wp_version - 0;

		$contributions = array();

		while ( $version ) {
			$credits = WP_Central_WordPress_Api::get_credits( $version );

			if ( $credits ) {
				foreach ( $credits['groups'] as $group_slug => $group_data ) {
					if ( 'libraries' == $group_data['type'] ) {
						continue;
					}

					foreach ( $group_data['data'] as $person_username => $person_data ) {
						if ( $person_username == $username ) {
							$contributions[] = $version;
							continue 2;
						}	
					}
				}

				$version -= 0.1;
			}
			else {
				$version = false;
			}
		}

		return $contributions;
	}

}