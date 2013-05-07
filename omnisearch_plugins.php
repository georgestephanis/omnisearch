<?php

class Jetpack_Omnisearch_Plugins {
	static $instance;

	function __construct() {
		self::$instance = $this;
		add_filter( 'omnisearch_results', array( $this, 'search'), 10, 2 );
	}

	function search( $results, $search_term ) {
		require_once( ABSPATH . 'wp-admin/includes/plugin-install.php' );
		$html = '<h2>' . __('Plugins:') . '</h2>';

		$api = plugins_api( 'query_plugins', array( 'search' => $search_term ) );

		if ( is_wp_error( $api ) ) {
			$html .= '<p>' . $api->get_error_message() . '</p>';
		} elseif( empty( $api->plugins ) ) {
			$html .= '<p>' . __('No results found.') . '</p>';
		} else {
			$html .= '<ul>';
			foreach( $api->plugins as $plugin ) {
				$html .= '<li>'
						.'<p><strong><a href="' . $plugin->homepage . '">' . $plugin->name . '</a></strong> '
						.'<small>Rating: ' . $plugin->rating . '/100</small></p>'
						.wpautop( $plugin->short_description )
						.'</li>';
			}
			$html .= '</ul>';
		}

		$results[ __CLASS__ ] = $html;
		return $results;
	}

}

new Jetpack_Omnisearch_Plugins;
