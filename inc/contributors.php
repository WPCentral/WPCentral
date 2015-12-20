<?php

if ( ! defined('ABSPATH') ) {
	die();
}

class WP_Central_Contributor {

	public function __construct() {
		add_action( 'init', array( $this, 'register_post_type' ) );
		add_filter( 'post_updated_messages', array( $this, 'codex_book_updated_messages' ) );

		add_shortcode( 'contributors', array( $this, 'contributors_search' ) );
	}


	public static function create( $username ) {
		$data = WP_Central_Data_Colector::get_user_info_from_profile( $username );

		if ( ! $data ) {
			return false;
		}

		$args = array(
			'post_name'   => $username,
			'post_title'  => $username,
			'post_type'   => 'contributor',
			'post_status' => 'publish'
		);

		$post_id = wp_insert_post( $args );
		$post    = get_post( $post_id );

		WP_Central_Data_Colector::get_wp_user_data( $post, $username );

		return $post;
	}


	/**
	 * Register a book post type.
	 *
	 * @link http://codex.wordpress.org/Function_Reference/register_post_type
	 */
	public function register_post_type() {
		$labels = array(
			'name'               => _x( 'Contributors', 'post type general name', 'wpcentral' ),
			'singular_name'      => _x( 'Contributor', 'post type singular name', 'wpcentral' ),
			'menu_name'          => _x( 'Contributors', 'admin menu', 'wpcentral' ),
			'name_admin_bar'     => _x( 'Contributor', 'add new on admin bar', 'wpcentral' ),
			'add_new'            => _x( 'Add New', 'book', 'wpcentral' ),
			'add_new_item'       => __( 'Add New Contributor', 'wpcentral' ),
			'new_item'           => __( 'New Contributor', 'wpcentral' ),
			'edit_item'          => __( 'Edit Contributor', 'wpcentral' ),
			'view_item'          => __( 'View Contributor', 'wpcentral' ),
			'all_items'          => __( 'All Contributors', 'wpcentral' ),
			'search_items'       => __( 'Search Contributors', 'wpcentral' ),
			'parent_item_colon'  => __( 'Parent Contributors:', 'wpcentral' ),
			'not_found'          => __( 'No contributors found.', 'wpcentral' ),
			'not_found_in_trash' => __( 'No contributors found in Trash.', 'wpcentral' )
		);

		$args = array(
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'contributors' ),
			'capabilities' => array(
				'edit_post'          => 'update_core',
				'read_post'          => 'update_core',
				'delete_post'        => 'update_core',
				'edit_posts'         => 'update_core',
				'edit_others_posts'  => 'update_core',
				'publish_posts'      => 'update_core',
				'read_private_posts' => 'update_core'
			),
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => array( 'title', 'editor', 'thumbnail' )
		);

		register_post_type( 'contributor', $args );
	}


	/**
	 * Book update messages.
	 *
	 * See /wp-admin/edit-form-advanced.php
	 *
	 * @param array $messages Existing post update messages.
	 *
	 * @return array Amended post update messages with new CPT update messages.
	 */
	public function codex_book_updated_messages( $messages ) {
		$post = get_post();

		$post_type_object = get_post_type_object( 'contributor' );

		$messages['contributor'] = array(
			0  => '', // Unused. Messages start at index 1.
			1  => __( 'Contributor updated.', 'wpcentral' ),
			2  => __( 'Custom field updated.', 'wpcentral' ),
			3  => __( 'Custom field deleted.', 'wpcentral' ),
			4  => __( 'Contributor updated.', 'wpcentral' ),
			/* translators: %s: date and time of the revision */
			5  => isset( $_GET['revision'] ) ? sprintf( __( 'Book restored to revision from %s', 'wpcentral' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6  => __( 'Contributor published.', 'wpcentral' ),
			7  => __( 'Contributor saved.', 'wpcentral' ),
			8  => __( 'Contributor submitted.', 'wpcentral' ),
			9  => sprintf(
				__( 'Contributor scheduled for: <strong>%1$s</strong>.', 'wpcentral' ),
				// translators: Publish box date format, see http://php.net/date
				date_i18n( __( 'M j, Y @ G:i', 'wpcentral' ), strtotime( $post->post_date ) )
			),
			10 => __( 'Contributor draft updated.', 'wpcentral' )
		);

		if ( $post_type_object->publicly_queryable ) {
			$permalink = get_permalink( $post->ID );

			$view_link = sprintf( ' <a href="%s">%s</a>', esc_url( $permalink ), __( 'View contributor', 'wpcentral' ) );
			$messages['contributor'][1] .= $view_link;
			$messages['contributor'][6] .= $view_link;
			$messages['contributor'][9] .= $view_link;

			$preview_permalink = add_query_arg( 'preview', 'true', $permalink );
			$preview_link = sprintf( ' <a target="_blank" href="%s">%s</a>', esc_url( $preview_permalink ), __( 'Preview contributor', 'wpcentral' ) );
			$messages['contributor'][8]  .= $preview_link;
			$messages['contributor'][10] .= $preview_link;
		}

		return $messages;
	}



	public static function contributors_search() {
		wp_enqueue_script( 'contributors-search', plugins_url( 'js/contributors-search.js', dirname( __FILE__ ) ), array( 'jquery' ), WP_Central::version );

		$html = '
		<form action="" method="post" class="searchform">
			<div class="input-group">
				<input type="text" id="searchform-contributor" class="form-control input-lg" placeholder="' . __( 'Find a WordPress contributor.', 'wpcentral' ) . '">

				<span class="input-group-btn">
					<button class="btn btn-primary btn-lg" type="button">' . __( 'Search ', 'wpcentral' ) . '</button>
				</span>
			</div>
		</form>';

		$html .= '<div id="contributors"></div>';

		return $html;
	}

}