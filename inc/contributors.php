<?php

if ( ! defined('ABSPATH') ) {
	die();
}

class WP_Central_Contributor {

	public function __construct() {
		add_action( 'init', array( $this, 'register_post_type' ) );
		add_filter( 'post_updated_messages', array( $this, 'codex_book_updated_messages' ) );
	}

	/**
	 * Register a book post type.
	 *
	 * @link http://codex.wordpress.org/Function_Reference/register_post_type
	 */
	function register_post_type() {
		$labels = array(
			'name'               => _x( 'Contributors', 'post type general name', 'wpcentral-api' ),
			'singular_name'      => _x( 'Contributor', 'post type singular name', 'wpcentral-api' ),
			'menu_name'          => _x( 'Contributors', 'admin menu', 'wpcentral-api' ),
			'name_admin_bar'     => _x( 'Contributor', 'add new on admin bar', 'wpcentral-api' ),
			'add_new'            => _x( 'Add New', 'book', 'wpcentral-api' ),
			'add_new_item'       => __( 'Add New Contributor', 'wpcentral-api' ),
			'new_item'           => __( 'New Contributor', 'wpcentral-api' ),
			'edit_item'          => __( 'Edit Contributor', 'wpcentral-api' ),
			'view_item'          => __( 'View Contributor', 'wpcentral-api' ),
			'all_items'          => __( 'All Contributors', 'wpcentral-api' ),
			'search_items'       => __( 'Search Contributors', 'wpcentral-api' ),
			'parent_item_colon'  => __( 'Parent Contributors:', 'wpcentral-api' ),
			'not_found'          => __( 'No contributors found.', 'wpcentral-api' ),
			'not_found_in_trash' => __( 'No contributors found in Trash.', 'wpcentral-api' )
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
	function codex_book_updated_messages( $messages ) {
		$post = get_post();

		$post_type_object = get_post_type_object( 'contributor' );

		$messages['contributor'] = array(
			0  => '', // Unused. Messages start at index 1.
			1  => __( 'Contributor updated.', 'wpcentral-api' ),
			2  => __( 'Custom field updated.', 'wpcentral-api' ),
			3  => __( 'Custom field deleted.', 'wpcentral-api' ),
			4  => __( 'Contributor updated.', 'wpcentral-api' ),
			/* translators: %s: date and time of the revision */
			5  => isset( $_GET['revision'] ) ? sprintf( __( 'Book restored to revision from %s', 'wpcentral-api' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6  => __( 'Contributor published.', 'wpcentral-api' ),
			7  => __( 'Contributor saved.', 'wpcentral-api' ),
			8  => __( 'Contributor submitted.', 'wpcentral-api' ),
			9  => sprintf(
				__( 'Contributor scheduled for: <strong>%1$s</strong>.', 'wpcentral-api' ),
				// translators: Publish box date format, see http://php.net/date
				date_i18n( __( 'M j, Y @ G:i', 'wpcentral-api' ), strtotime( $post->post_date ) )
			),
			10 => __( 'Contributor draft updated.', 'wpcentral-api' )
		);

		if ( $post_type_object->publicly_queryable ) {
			$permalink = get_permalink( $post->ID );

			$view_link = sprintf( ' <a href="%s">%s</a>', esc_url( $permalink ), __( 'View contributor', 'wpcentral-api' ) );
			$messages['contributor'][1] .= $view_link;
			$messages['contributor'][6] .= $view_link;
			$messages['contributor'][9] .= $view_link;

			$preview_permalink = add_query_arg( 'preview', 'true', $permalink );
			$preview_link = sprintf( ' <a target="_blank" href="%s">%s</a>', esc_url( $preview_permalink ), __( 'Preview contributor', 'wpcentral-api' ) );
			$messages['contributor'][8]  .= $preview_link;
			$messages['contributor'][10] .= $preview_link;
		}

		return $messages;
	}

}