<?php

if ( ! defined('ABSPATH') ) {
	die();
}

class WP_Central_Data_Colector {

	public static function get_wp_user_data( $user, $username, $meta = 'all' ) {
		$options = array(
			'core_contributed_to'      => array( $user, 'core_contributed_to', $username, array( 'WP_Central_Data_Colector', 'get_contributions_of_user' ) ),
			'core_contributions'       => array( $user, 'core_contributions', $username, array( 'WP_Central_WordPress_Api', 'get_changeset_items' ) ),
			'core_contributions_count' => array( $user, 'core_contributions_count', $username, array( 'WP_Central_WordPress_Api', 'get_changeset_count' ) ),
			'codex_items'              => array( $user, 'codex_items', $username, array( 'WP_Central_WordPress_Api', 'get_codex_items' ) ),
			'codex_items_count'        => array( $user, 'codex_items_count', $username, array( 'WP_Central_WordPress_Api', 'get_codex_items_count' ) ),
			'plugins'                  => array( $user, 'plugins', $username, array( 'WP_Central_WordPress_Api', 'get_plugins' ) ),
			'themes'                   => array( $user, 'themes', $username, array( 'WP_Central_WordPress_Api', 'get_themes' ) ),
		);

		if ( 'all' != $meta ) {
			if ( ! isset( $options[ $meta ] ) ) {
				return false;
			}

			return call_user_func_array( array( 'WP_Central_Data_Colector', 'get_user_value' ), $options[ $meta ] );
		}

		$data = array();

		foreach ( $options as $meta_key => $option ) {
			$data[ $meta_key ] = call_user_func_array( array( 'WP_Central_Data_Colector', 'get_user_value' ), $option );
		}

		return $data;
	}

	public static function get_contributions_of_user( $username ) {
		global $wp_version;

		$version = $wp_version - 0;

		$contributions = array();

		while ( $version ) {
			$_version = (string) $version;
			$credits  = WP_Central_WordPress_Api::get_credits( $_version );

			if ( $credits ) {
				foreach ( $credits['groups'] as $group_slug => $group_data ) {
					if ( 'libraries' == $group_data['type'] ) {
						continue;
					}

					foreach ( $group_data['data'] as $person_username => $person_data ) {
						if ( strtolower( $person_username ) == $username ) {
							if ( 'titles' == $group_data['type'] ) {
								if ( $person_data[3] ) {
									$contributions[ $_version ] = $person_data[3];
								}
								else if ( $group_data['name'] ) {
									$contributions[ $_version ] = $group_data['name'];
								}
								else {
									$contributions[ $_version ] = ucfirst( str_replace( '-', ' ', $group_slug ) );
								}

								$contributions[ $_version ] = rtrim( $contributions[ $_version ], 's' );
							}
							else {
								$contributions[ $_version ] = __( 'Core Contributor', 'wpcentral-api' );
							}

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