<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

class WP_Central_Versions {

	public function __construct() {
		add_action( 'init', array( $this, 'add_rewrite_rule' ) );
		add_filter( 'query_vars', array( $this, 'query_vars' ) );
	}

	public function add_rewrite_rule() {
		add_rewrite_rule('^version/([0-9.]+)/?$', 'index.php?wp_version_selector=$matches[1]', 'top');
	}

	public function query_vars( $query_vars ) {
		$query_vars[] = 'wp_version_selector';

		return $query_vars;
	}

}