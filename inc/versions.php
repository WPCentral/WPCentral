<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

class WP_Central_Versions {

	public function __construct() {
		add_action( 'init', array( $this, 'add_rewrite_rule' ) );
		add_filter( 'query_vars', array( $this, 'query_vars' ) );
		add_action( 'wp', array( $this, 'set_404' ) );
	}

	public function add_rewrite_rule() {
		add_rewrite_rule('^version/([0-9.]+)/?$', 'index.php?wp_version_selector=$matches[1]', 'top');
	}

	public function query_vars( $query_vars ) {
		$query_vars[] = 'wp_version_selector';

		return $query_vars;
	}

	public function set_404( $wp ) {
		global $wp_query;

		if ( isset( $wp->query_vars['wp_version_selector'] ) ) {
			$releases = WP_Central_Stats::get_major_releases();

			if ( ! in_array( $wp->query_vars['wp_version_selector'], $releases ) ) {
				$wp_query->set_404();
				status_header( 404 );
				nocache_headers();
			}
		}
	}

}