<?php

if ( ! defined('ABSPATH') ) {
	die();
}

class WP_Central_Data_Colector {

	public static function get_wp_user_data( $user, $username ) {
		$data = array(
			'core_contributed_to'      => self::get_user_value( $user, 'core_contributed_to', $username, array( 'WP_Central_Data_Colector', 'get_contributions_of_user' ) ),
			'core_contributions'       => self::get_user_value( $user, 'core_contributions', $username, array( 'WP_Central_WordPress_Api', 'get_changeset_items' ) ),
			'core_contributions_count' => self::get_user_value( $user, 'core_contributions_count', $username, array( 'WP_Central_WordPress_Api', 'get_changeset_count' ) ),
			'codex_items'              => self::get_user_value( $user, 'codex_items', $username, array( 'WP_Central_WordPress_Api', 'get_codex_items' ) ),
			'codex_items_count'        => self::get_user_value( $user, 'codex_items_count', $username, array( 'WP_Central_WordPress_Api', 'get_codex_items_count' ) ),
			'plugins'                  => self::get_user_value( $user, 'plugins', $username, array( 'WP_Central_WordPress_Api', 'get_plugins' ) ),
			'themes'                   => self::get_user_value( $user, 'themes', $username, array( 'WP_Central_WordPress_Api', 'get_themes' ) ),
		);

		return $data;
	}

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


	private static function get_user_value( $user, $field, $username = false, $fallback = false ) {
		$data = '';

		if ( $user->has_prop( $field ) ) {
			$data = $user->get( $field );
		} else if( $username && $fallback ) {
			$data = call_user_func( $fallback, $username );

			// Cache the data
			update_user_meta( $user->ID, $field, $data );
		}

		return $data;
	}

}