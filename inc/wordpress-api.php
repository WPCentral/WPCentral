<?php

if ( ! defined('ABSPATH') ) {
	die();
}

class WP_Central_WordPress_Api {

	/**
	 * Retrieve the contributor credits.
	 *
	 * @param string $wp_version The WordPress version.
	 *
	 * @return array|bool A list of all of the contributors, or false on error.
	*/
	public static function get_credits( $wp_version, $locale = '' ) {
		// We can't request data before this.
		if ( version_compare( $wp_version, '3.2', '<' ) ) {
			return false;
		}

		$response = wp_remote_get( 'https://api.wordpress.org/core/credits/1.1/?version=' . $wp_version . '&locale=' .$locale );

		if ( is_wp_error( $response ) || 200 != wp_remote_retrieve_response_code( $response ) ) {
			return false;
		}

		$results = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( ! is_array( $results ) || $results['data']['version'] != (string) $wp_version ) {
			return false;
		}

		return $results;
	}


	/**
	 * Retrieve language packs
	 *
	 * @param string $wp_version The WordPress version.
	 *
	 * @return array|bool A list of all locales with language packs, or false on error.
	*/
	public static function get_language_packs( $wp_version ) {
		// We can't request data before this.
		if ( version_compare( $wp_version, '4.0', '<' ) ) {
			return false;
		}

		$response = wp_remote_get( 'https://api.wordpress.org/translations/core/1.0/?version=' . $wp_version );

		if ( is_wp_error( $response ) || 200 != wp_remote_retrieve_response_code( $response ) ) {
			return false;
		}

		$results = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( ! is_array( $results ) ) {
			return false;
		}

		return $results;
	}


	public static function get_plugins( $username, $args = array() ) {
		if ( ! $username ) {
			return false;
		}

		require_once ABSPATH . 'wp-admin/includes/plugin-install.php';

		$defaults = array(
			'author'   => $username,
			'per_page' => 30,
			'fields'   => array( 'description' => false, 'compatibility' => false )
		);

		$data = plugins_api( 'query_plugins', wp_parse_args( $args, $defaults ) );

		if ( $data && isset( $data->plugins ) ) {
			return $data->plugins;
		}

		return false;
	}

	public static function get_themes( $username, $args = array() ) {
		if ( ! $username ) {
			return false;
		}

		include ABSPATH . 'wp-admin/includes/theme.php';

		$defaults = array(
			'author'   => $username,
			'per_page' => 30,
			'fields'   => array()
		);

		$data = themes_api( 'query_themes', wp_parse_args( $args, $defaults ) );

		if ( $data && isset( $data->themes ) ) {
			return $data->themes;
		}

		return false;
	}




	public static function get_changeset_items( $username ) {
		if ( ! $username ) {
			return false;
		}

		$items = array();

		$results_url = add_query_arg(
			array(
				'q'             => 'props+' . $username,
				'noquickjump'   => '1',
				'changeset'     => 'on'
			),
			'https://core.trac.wordpress.org/search'
		);
		$response = wp_remote_get( $results_url );

		if ( 200 == wp_remote_retrieve_response_code( $response ) ) {
			$results  = wp_remote_retrieve_body( $response );

			$results  = preg_replace( '/\s+/', ' ', $results );
			$results  = str_replace( PHP_EOL, '', $results );
			$pattern  = '/<dt><a href="(.*?)" class="searchable">\[(.*?)\]: ((?s).*?)<\/a><\/dt>\s*(<dd class="searchable">.*?. #(.*?) .*?.<\/dd>)/';

			preg_match_all( $pattern, $results, $matches, PREG_SET_ORDER );

			foreach ( $matches as $match ) {
				array_shift( $match );

				$new_match = array(
					'link'          => 'https://core.trac.wordpress.org' . $match[0],
					'changeset'     => intval($match[1]),
					'description'   => $match[2],
					'ticket'        => isset( $match[3] ) ? intval($match[4]) : '',
				);

				array_push( $items, $new_match );
			}

		}

		return $items;
	}

	public static function get_changeset_count( $username ) {
		if ( ! $username ) {
			return false;
		}

		$count = 0;

		$results_url = add_query_arg(
			array(
				'q'             => 'props+' . $username,
				'noquickjump'   => '1',
				'changeset'     => 'on'
			),
			'https://core.trac.wordpress.org/search'
		);
		$response = wp_remote_get( $results_url );

		if ( 200 == wp_remote_retrieve_response_code( $response ) ) {
			$results = wp_remote_retrieve_body( $response );
			$pattern = '/<meta name="totalResults" content="(\d*)" \/>/';

			preg_match( $pattern, $results, $matches );

			if ( isset( $matches[1] ) ) {
				$count = intval( $matches[1] );
			}
		}

		return $count;
	}

	public static function get_codex_items( $username, $limit = 10 ) {
		if ( ! $username ) {
			return false;
		}

		$items = array();

		$results_url = add_query_arg( array(
			'action'    => 'query',
			'list'      => 'usercontribs',
			'ucuser'    => $username,
			'uclimit'   => $limit,
			'ucdir'     => 'older',
			'format'    => 'json'
		), 'https://codex.wordpress.org/api.php' );
		$response = wp_remote_get( $results_url );

		if ( 200 == wp_remote_retrieve_response_code( $response ) ) {
			$results   = wp_remote_retrieve_body( $response );
			$raw       = json_decode( $results );

			foreach ( $raw->query->usercontribs as $item ) {
				$count = 0;
				$clean_title = preg_replace( '/^Function Reference\//', '', (string) $item->title, 1, $count );

				$new_item = array(
					'title'         => $clean_title,
					'description'   => (string) $item->comment,
					'revision'      => (int) $item->revid,
					'function_ref'  => (bool) $count
				);

				array_push( $items, $new_item );
			}
		}

		return $items;
	}

	public static function get_codex_count( $username ) {
		if ( ! $username ) {
			return false;
		}

		$count = 0;

		$results_url = add_query_arg(
			array(
				'action'    =>  'query',
				'list'      =>  'users',
				'ususers'   =>  $username,
				'usprop'    =>  'editcount',
				'format'    =>  'json'
			),
			'https://codex.wordpress.org/api.php'
		);
		$response = wp_remote_get( $results_url );

		if ( 200 == wp_remote_retrieve_response_code( $response ) ) {
			$results  = wp_remote_retrieve_body( $response );
			$raw      = json_decode( $results );

			if ( isset( $raw->query ) ) {
				$count = (int) $raw->query->users[0]->editcount;
			}
		}

		return $count;
	}

}