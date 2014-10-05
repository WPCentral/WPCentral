<?php

if ( ! defined('ABSPATH') ) {
	die();
}

class WP_Central_Data_Colector {

	public static function update_contributors_for_version( $wp_version, $create_users ) {

	}

	public static function get_wp_user_data( $post, $username, $meta = 'all' ) {
		$options = array(
			'core_contributed_to'      => array( $post, 'core_contributed_to', $username, array( 'WP_Central_Data_Colector', 'get_contributions_of_user' ) ),
			'core_contributions'       => array( $post, 'core_contributions', $username, array( 'WP_Central_WordPress_Api', 'get_changeset_items' ) ),
			'core_contributions_count' => array( $post, 'core_contributions_count', $username, array( 'WP_Central_WordPress_Api', 'get_changeset_count' ) ),
			'codex_items'              => array( $post, 'codex_items', $username, array( 'WP_Central_WordPress_Api', 'get_codex_items' ) ),
			'codex_items_count'        => array( $post, 'codex_items_count', $username, array( 'WP_Central_WordPress_Api', 'get_codex_count' ) ),
			'plugins'                  => array( $post, 'plugins', $username, array( 'WP_Central_WordPress_Api', 'get_plugins' ) ),
			'themes'                   => array( $post, 'themes', $username, array( 'WP_Central_WordPress_Api', 'get_themes' ) ),
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

		$version = number_format( $wp_version, 1, '.', '' );

		$contributions = array();
		while ( $version ) {
			$version = number_format( $version, 1, '.', '' );

			$role = self::loop_wp_version( $version, $username );

			if ( false !== $role ) {
				if ( $role ) {
					$contributions[ $version ] = $role;
				}

				$version -= 0.1;
			}
			else {
				$version = false;
			}
		}

		return $contributions;
	}



	private static function loop_wp_version( $version, $username = false ) {
		$credits  = WP_Central_WordPress_Api::get_credits( $version );

		if ( $credits ) {

			foreach ( $credits['groups'] as $group_slug => $group_data ) {
				if ( 'libraries' == $group_data['type'] ) {
					continue;
				}

				foreach ( $group_data['data'] as $person_username => $person_data ) {
					if ( strtolower( $person_username ) == $username ) {
						$role = '';

						if ( 'titles' == $group_data['type'] ) {
							if ( $person_data[3] ) {
								$role = $person_data[3];
							}
							else if ( $group_data['name'] ) {
								$role = $group_data['name'];
							}
							else {
								$role = ucfirst( str_replace( '-', ' ', $group_slug ) );
							}

							$role = rtrim( $role, 's' );
						}
						else {
							$role = __( 'Core Contributor', 'wpcentral-api' );
						}

						return $role;
					}	
				}
			}

			return null;
		}

		return false;
	}



	public static function get_user_info_from_profile( $username ) {
		$url = 'https://profiles.wordpress.org/' . $username;

		$request = wp_remote_get( $url, array( 'redirection' => 0 ) );
		$code    = wp_remote_retrieve_response_code( $request );

		if ( 200 !== $code ) {
			return false;
		}

		$body = wp_remote_retrieve_body( $request );

		$dom = new DOMDocument();
		@$dom->loadHTML( $body ); // Error supressing due to the fact that special characters haven't been converted to HTML.
		$finder = new DomXPath( $dom );

		$name     = $finder->query('//h2[@class="fn"]');
		$avatar   = $finder->query('//div[@id="meta-status-badge-container"]/a/img');
		$location = $finder->query('//li[@id="user-location"]');
		$website  = $finder->query('//li[@id="user-website"]/a');
		$company  = $finder->query('//li[@id="user-company"]');
		$socials  = $finder->query('//ul[@id="user-social-media-accounts"]/li/a');
		$badges   = $finder->query('//ul[@id="user-badges"]/li/div');

		$data = array(
			'name'     => trim( $name->item(0)->nodeValue ),
			'avatar'   => strtok( $avatar->item(0)->getAttribute('src'), '?' ),
			'location' => trim( $location->item(0)->nodeValue ),
			'company'  => '',
			'website'  => '',
			'socials'  => array(),
			'badges'   => array(),
		);

		if ( $company->length ) {
			$data['company'] = trim( preg_replace( '/\t+/', '', $company->item(0)->nodeValue ) );
		}

		if ( $website->length ) {
			$data['website'] = trim( $website->item(0)->getAttribute('href') );
		}

		foreach ( $socials as $item ) {
			$icon = $item->getElementsByTagName("div");

			$data['socials'][ $icon->item(0)->getAttribute('title') ] = $item->getAttribute('href');
		}

		foreach ( $badges as $badge ) {
			$data['badges'][] = $badge->getAttribute('title');
		}

		return $data;
	}




	private static function get_user_value( $post, $field, $username = false, $fallback = false ) {
		$data = '';

		if ( $post->$field ) {
			$data = $post->$field;
		}
		else if( $username && $fallback ) {
			$data = call_user_func( $fallback, $username );

			// Cache the data
			update_post_meta( $post->ID, $field, $data );
		}

		return $data;
	}

}