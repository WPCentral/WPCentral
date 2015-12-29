<?php

if ( ! defined('ABSPATH') ) {
	die();
}

class WP_Central_Data_Colector {

	public static function update_contributors_for_version( $wp_version, $create_users ) {

	}

	public static function get_wp_user_data( & $post, $username, $meta = 'all' ) {
		$current = current_time( 'timestamp' );
		$synced  = mysql2date( 'U', $post->post_modified ) + DAY_IN_SECONDS;
		$force   = $synced < $current && 'all' == $meta;

		$options = array(
			'core_contributed_to'      => array( $post, 'core_contributed_to', $username, array( 'WP_Central_Data_Colector', 'get_contributions_of_user' ), $force ),
			'core_contributions'       => array( $post, 'core_contributions', $username, array( 'WP_Central_WordPress_Api', 'get_changeset_items' ), $force  ),
			'core_contributions_count' => array( $post, 'core_contributions_count', $username, array( 'WP_Central_WordPress_Api', 'get_changeset_count' ), $force  ),
			'codex_items'              => array( $post, 'codex_items', $username, array( 'WP_Central_WordPress_Api', 'get_codex_items' ), $force  ),
			'codex_items_count'        => array( $post, 'codex_items_count', $username, array( 'WP_Central_WordPress_Api', 'get_codex_count' ), $force  ),
			'plugins'                  => array( $post, 'plugins', $username, array( 'WP_Central_WordPress_Api', 'get_plugins' ), $force  ),
			'themes'                   => array( $post, 'themes', $username, array( 'WP_Central_WordPress_Api', 'get_themes' ), $force  ),
		);

		if ( 'all' != $meta ) {
			if ( ! isset( $options[ $meta ] ) ) {
				return false;
			}

			return call_user_func_array( array( __CLASS__, 'get_user_value' ), $options[ $meta ] );
		}

		$data = array();

		foreach ( $options as $meta_key => $option ) {
			$data[ $meta_key ] = call_user_func_array( array( __CLASS__, 'get_user_value' ), $option );
		}


		if ( $force || $post->post_modified == $post->post_date ) {
			$user_info = WP_Central_Data_Colector::get_user_info_from_profile( $username );

			$update_args = array(
				'ID'           => $post->ID,
				'post_title'   => $user_info['name'],
				'post_content' => $user_info['description'],

				'post_modified'     => current_time( 'mysql' ),
				'post_modified_gmt' => current_time( 'mysql', 1 )
			);
			wp_update_post( $update_args );

			update_post_meta( $post->ID, 'avatar', $user_info['avatar'] );
			update_post_meta( $post->ID, 'location', $user_info['location'] );
			update_post_meta( $post->ID, 'website', $user_info['website'] );
			update_post_meta( $post->ID, 'company', $user_info['company'] );
			update_post_meta( $post->ID, 'socials', $user_info['socials'] );
			update_post_meta( $post->ID, 'badges', $user_info['badges'] );

			$post = get_post( $post->ID );
		}

		return $data;
	}

	public static function get_contributions_of_user( $username ) {
		$version = number_format( self::get_latest_wp_version('major'), 1, '.', '' );

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
							$role = __( 'Core Contributor', 'wpcentral' );
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
		$badges   = $finder->query('//ul[@id="user-badges"]/li/div');

		$data = array(
			'name'         => trim( $name->item(0)->nodeValue ),
			'description'  => '',
			'avatar'       => strtok( $avatar->item(0)->getAttribute('src'), '?' ),
			'location'     => trim( $location->item(0)->nodeValue ),
			'company'      => '',
			'website'      => '',
			'socials'      => array(),
			'badges'       => array(),
		);

		if ( $company->length ) {
			$data['company'] = trim( preg_replace( '/\t+/', '', $company->item(0)->nodeValue ) );
		}

		if ( $website->length ) {
			$data['website'] = trim( $website->item(0)->getAttribute('href') );
		}

		foreach ( $badges as $badge ) {
			preg_match( '/(?<!\w)badge-(?:[^_\W]|-)+/', $badge->getAttribute('class'), $matches );
			$data['badges'][ $matches[0] ] = $badge->getAttribute('title');
		}


		if ( $data['avatar'] ) {
			$hash    = str_replace( '/avatar/', '', parse_url( $data['avatar'] )['path'] );
			$request = wp_remote_get( 'https://en.gravatar.com/' . $hash . '.json' );
			$code    = wp_remote_retrieve_response_code( $request );

			if ( 200 === $code ) {
				$body  = json_decode( wp_remote_retrieve_body( $request ) );
				$entry = $body->entry[0];

				$data['description'] = $entry->aboutMe;

				if ( isset( $entry->accounts ) ) {
					foreach ( $body->entry[0]->accounts as $item ) {
						$data['socials'][ $item->shortname ] = $item->url;
					}
				}
			}
		}


		return $data;
	}




	private static function get_user_value( $post, $field, $username = false, $fallback = false, $force = false ) {
		$data = '';

		if ( ( $post->$field || $post->$field !== '' ) && ! $force ) {
			$data = $post->$field;
		}
		else if ( $username && $fallback ) {
			$data = call_user_func( $fallback, $username );

			// Cache the data
			update_post_meta( $post->ID, $field, $data );
		}

		return $data;
	}


	public static function get_latest_wp_version( $type ) {
		global $wp_version;
		
		// Current version for this installation.
		$version = $wp_version;

		// If there is an update then use that version.
		include ABSPATH . 'wp-admin/includes/update.php';
		$cur = get_preferred_from_update_core();
		if ( isset( $cur->response ) && $cur->response == 'upgrade' ) {
			$version = $cur->current;
		}

		// Only return the first part of a version when the major versin is being requested.
		if ( 'major' == $type ) {
			$version = substr( $version, 0, 3 );
		}

		return $version;
	}

}