<?php

if ( ! defined('ABSPATH') ) {
	die();
}

class WP_Central_JSON_API {

	/**
	 * Base route name
	 */
	protected $namespace = 'contributors';


	public function __construct() {
		add_filter( 'rest_url_prefix', array( $this, 'rest_url_prefix' ) );
		add_filter( 'rest_api_init', array( $this, 'register_routes' ), 30 );
	}

	public function rest_url_prefix() {
		return 'api';
	}

	/**
	 * Register the routes for the post type
	 *
	 * @param array $routes Routes for the post type
	 * @return array Modified routes
	 */
	public function register_routes() {
		$posts_args = array(
			'page'                  => array(
				'default'           => 1,
				'sanitize_callback' => 'absint',
			),
			'per_page'              => array(
				'default'           => 10,
				'sanitize_callback' => 'absint',
			),
			'filter'                => array(),
		);

		register_rest_route( $this->namespace, '/search', array(
			'callback' => array( $this, 'get_users' ),
			'methods'  => WP_REST_Server::READABLE,
			'args'     => $posts_args
		) );

		register_rest_route( $this->namespace, '/(?P<username>[a-z-]+)', array(
			'callback' => array( $this, 'get_user' ),
			'methods'  => WP_REST_Server::READABLE
		) );
	}


	public function get_users( WP_REST_Request $request ) {
		$query_args = array(
			'post_type'      => 'contributor',
			'posts_per_page' => $request['per_page'],
			'paged'          => $request['page'],
			's'              => sanitize_text_field( $request->get_param( 'search' ) ),
		);

		$posts_query = new WP_Query();
		$query_result = $posts_query->query( $query_args );

		$posts = array();
		foreach ( $query_result as $post ) {
			$data    = $this->prepare_item_for_response( $post, $request );
			$posts[] = $this->prepare_response_for_collection( $data );
		}

		$response = new WP_REST_Response( $posts );

		return $response;
	}

	public function get_user( WP_REST_Request $request ) {
		header('Access-Control-Allow-Origin: *');

		$username = $request['username'];

		if ( ! $contributor = get_page_by_path( $username, OBJECT, 'contributor' ) ) {
			$contributor = WP_Central_Contributor::create( $username );

			if ( ! $contributor ) {
				return new WP_Error( 'rest_user_invalid_id', __( "User doesn't exist." ), array( 'status' => 404 ) );
			}
		}

		if ( ! ( $contributor instanceof WP_Post ) ) {
			return new WP_Error( 'rest_user_invalid_id', __( "User doesn't exist." ), array( 'status' => 404 ) );
		}

		$data = $this->prepare_item_for_response( $contributor, $request );

		$response = new WP_REST_Response( $data );

		return $response;
	}


	/**
	 *
	 * Prepare a User entity from a WP_User instance.
	 *
	 * @return array
	 */
	protected function prepare_item_for_response( $post, $request ) {
		$user_data = WP_Central_Data_Colector::get_wp_user_data( $post, $post->post_name );

		$data = array(
			'username'    => $post->post_name,
			'name'        => $post->post_title,
			'description' => $post->post_content,
			'avatar'      => $post->avatar,
			'location'    => $post->location,
			'company'     => $post->company,
			'website'     => $post->website,
			'socials'     => $post->socials,
			'badges'      => $post->badges,

			'modified'     => $this->prepare_date_response( $post->post_modified_gmt, $post->post_modified ),
			'modified_gmt' => $this->prepare_date_response( $post->post_modified_gmt ),

			'guid'         => array(
				'rendered' => apply_filters( 'get_the_guid', $post->guid ),
				'raw'      => $post->guid,
			),
			'link'         => get_permalink( $post->ID ),
		);

		$data = wp_parse_args( $user_data, $data );

		// Wrap the data in a response object
		$data = rest_ensure_response( $data );

		$data->add_links( $this->prepare_links( $post ) );

		return apply_filters( 'wpcentral_api_prepare_contributor', $data, $post );
	}

	/**
	 * Prepare links for the request.
	 *
	 * @param WP_Post $post Post object.
	 * @return array Links for the given post.
	 */
	protected function prepare_links( $post ) {
		return array(
			'self' => array(
				'href' => rest_url( trailingslashit( $this->namespace ) . $post->post_name )
			),
			'collection' => array(
				'href' => rest_url( $this->namespace )
			)
		);
	}

	/**
	 * Check the post_date_gmt or modified_gmt and prepare any post or
	 * modified date for single post output.
	 *
	 * @param string       $date_gmt
	 * @param string|null  $date
	 * @return string|null ISO8601/RFC3339 formatted datetime.
	 */
	protected function prepare_date_response( $date_gmt, $date = null ) {
		if ( '0000-00-00 00:00:00' === $date_gmt ) {
			return null;
		}

		if ( isset( $date ) ) {
			return rest_mysql_to_rfc3339( $date );
		}

		return rest_mysql_to_rfc3339( $date_gmt );
	}

	/**
	 * Prepare a response for inserting into a collection.
	 *
	 * @param WP_REST_Response $response Response object.
	 * @return array Response data, ready for insertion into collection data.
	 */
	protected function prepare_response_for_collection( $response ) {
		if ( ! ( $response instanceof WP_REST_Response ) ) {
			return $response;
		}

		$data = (array) $response->get_data();
		$links = WP_REST_Server::get_response_links( $response );

		if ( ! empty( $links ) ) {
			$data['_links'] = $links;
		}

		return $data;
	}

}