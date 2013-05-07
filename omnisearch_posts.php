<?php

class Jetpack_Omnisearch_Posts {
	static $instance;

	function __construct() {
		self::$instance = $this;
		add_filter( 'omnisearch_results', array( $this, 'search'), 10, 2 );
	}

	function search( $results, $search_term ) {
		$html = '<h2>' . __('Posts:') . '</h2>';

		$query = new WP_Query( array( 's' => $search_term ) );
		if( $query->have_posts() ) {
			$html .= '<ul>';
			while( $query->have_posts() ) {
				$query->the_post();
				$html .= '<li>' . get_the_title() . ' <a href="' . get_edit_post_link() . '">' . __('edit') . '</a></li>';
			}
			$html .= '</ul>';
		} else {
			$html .= '<p>' . __('No results found.') . '</p>';
		}

		$results[ __CLASS__ ] = $html;
		return $results;
	}

}

new Jetpack_Omnisearch_Posts;
