<?php
/*
Plugin Name: Jetpack Omnisearch
Plugin URI: http://wordpress.org/extend/plugins/omnisearch/
Description: Enables an omnisearch (search ALL THE THINGS) in WordPress.
Author: George Stephanis
Version: 1.0
Author URI: http://stephanis.info/
*/

require_once( dirname(__FILE__) . '/omnisearch_posts.php' );
require_once( dirname(__FILE__) . '/omnisearch_plugins.php' );

class Jetpack_Omnisearch {
	static $instance;

	function __construct() {
		self::$instance = $this;
		add_action( 'init', array( $this, 'init' ) );
	}

	function init() {
		add_action(	'admin_menu', array( $this, 'admin_menu' ) );
	}

	function admin_menu() {
		$this->page_slug = add_menu_page( __('Omnisearch'), __('Omnisearch'), 'manage_options', 'omnisearch', array( $this, 'omnisearch_page' ) );
	}

	function omnisearch_page() {
		$results = array();
		$s = isset( $_GET['s'] ) ? $_GET['s'] : '';
		if( $s ) {
			$results = apply_filters( 'omnisearch_results', $results, $s );
		}
		?>
		<div class="wrap">
			<div id="icon-tools" class="icon32">
				<br />
			</div>
			<h2><?php _e('Jetpack Omnisearch Prototype'); ?></h2>
			<br class="clear" />
			<form action="<?php echo admin_url( 'admin.php' ); ?>" method="get">
				<input type="hidden" name="page" value="omnisearch" />
				<input type="search" name="s" class="omnisearch" placeholder="Search ALL THE THINGS!!!!!" style="font-size:2.2em; padding:.25em .5em; border-radius:.25em; width:100%;" value="<?php echo esc_attr( $s ); ?>" />
			</form>
			<?php if( ! empty( $results ) ): ?>
				<h3><?php _e('Results:'); ?></h3>
				<?php echo implode( '<hr />', $results ); ?>
			<?php endif; ?>
		</div><!-- /wrap -->
		<?php
	}

}
new Jetpack_Omnisearch;
