<?php

// Include this here so that other plugins can extend it if they like.
require_once( dirname(__FILE__) . '/omnisearch-posts.php' );

class WP_Omnisearch {
	static $instance;
	static $num_results = 5;

	function __construct() {
		self::$instance = $this;
		add_action( 'wp_loaded',          array( $this, 'wp_loaded' ) );
		add_action( 'admin_init',         array( $this, 'add_providers' ) );
		add_action( 'admin_menu',         array( $this, 'admin_menu' ), 20 );
		if( ! wp_is_mobile() ) {
			add_action( 'admin_bar_menu', array( $this, 'admin_bar_search' ), 4 );
		}
		add_filter( 'omnisearch_num_results', array( $this, 'omnisearch_num_results' ) );
	}

	static function add_providers() {
		// omnisearch-posts.php is included above, so that other plugins can more easily extend it.
		new WP_Omnisearch_Posts;
		new WP_Omnisearch_Posts( 'page' );

		require_once( dirname(__FILE__) . '/omnisearch-comments.php' );
		new WP_Omnisearch_Comments;

		if ( current_user_can( 'upload_files' ) ) {
			require_once( dirname(__FILE__) . '/omnisearch-media.php' );
			new WP_Omnisearch_Media;
		}

		if ( current_user_can( 'install_plugins' ) ) {
			require_once( dirname(__FILE__) . '/omnisearch-plugins.php' );
			new WP_Omnisearch_Plugins;
		}

		do_action( 'omnisearch_add_providers' );
	}

	static function omnisearch_num_results( $num ) {
		return self::$num_results;
	}

	function wp_loaded() {
		$deps = null;
		if ( wp_style_is( 'genericons', 'registered' ) ) {
			$deps = array( 'genericons' );
		}
		wp_register_style( 'omnisearch-admin', plugins_url( 'omnisearch.css', __FILE__ ), $deps );
	}

	function admin_menu() {
		$this->slug = add_dashboard_page( __('Omnisearch', '__textdomain__'), __('Omnisearch', '__textdomain__'), 'edit_posts', 'omnisearch', array( $this, 'omnisearch_page' ) );
		add_action( "admin_print_styles-{$this->slug}", array( $this, 'admin_print_styles' ) );
	}

	function admin_print_styles() {
		wp_enqueue_style( 'omnisearch-admin' );
	}

	function omnisearch_page() {
		$results = array();
		$s = isset( $_GET['s'] ) ? $_GET['s'] : '';
		if( $s ) {
			$results = apply_filters( 'omnisearch_results', $results, $s );
		}
		?>
		<div class="wrap">
			<h2 class="page-title"><?php esc_html_e('Omnisearch', '__textdomain__'); ?> <small><?php esc_html_e('search everything', '__textdomain__'); ?></small></h2>
			<br class="clear" />
			<?php echo self::get_omnisearch_form( array(
							'form_class'         => 'omnisearch-form',
							'search_class'       => 'omnisearch',
							'search_placeholder' => '',
							'submit_class'       => 'omnisearch-submit',
							'alternate_submit'   => true,
						) ); ?>
			<?php if( ! empty( $results ) ): ?>
				<h3 id="results-title"><?php esc_html_e('Results:', '__textdomain__'); ?></h3>
				<div class="jump-to"><strong><?php esc_html_e('Jump to:', '__textdomain__'); ?></strong>
					<?php foreach( $results as $label => $result ) : ?>
						<a href="#result-<?php echo sanitize_title( $label ); ?>"><?php echo esc_html( $label ); ?></a>
					<?php endforeach; ?>
				</div>
				<br class="clear" />
				<script>var search_term = '<?php echo esc_js( $s ); ?>', num_results = <?php echo intval( apply_filters( 'omnisearch_num_results', 5 ) ); ?>;</script>
				<ul class="omnisearch-results">
					<?php foreach( $results as $label => $result ) : ?>
						<li id="result-<?php echo sanitize_title( $label ); ?>" data-label="<?php echo esc_attr( $label ); ?>">
							<?php echo $result; ?>
							<a class="back-to-top" href="#results-title"><?php esc_html_e('Back to Top &uarr;', '__textdomain__'); ?></a>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div><!-- /wrap -->
		<?php
	}

	function admin_bar_search( $wp_admin_bar ) {
		if( ! is_admin() || ! current_user_can( 'edit_posts' ) )
			return;

		$form = self::get_omnisearch_form( array(
			'form_id'      => 'adminbarsearch',
			'search_id'    => 'adminbar-search',
			'search_class' => 'adminbar-input',
			'submit_class' => 'adminbar-button',
		) );

		$form .= "<style>
				#adminbar-search::-webkit-input-placeholder,
				#adminbar-search:-moz-placeholder,
				#adminbar-search::-moz-placeholder,
				#adminbar-search:-ms-input-placeholder {
					text-shadow: none;
				}
			</style>";

		$wp_admin_bar->add_menu( array(
			'parent' => 'top-secondary',
			'id'     => 'search',
			'title'  => $form,
			'meta'   => array(
				'class'    => 'admin-bar-search',
				'tabindex' => -1,
			)
		) );
	}

	static function get_omnisearch_form( $args = array() ) {
		$defaults = array(
			'form_id'            => null,
			'form_class'         => null,
			'search_class'       => null,
			'search_id'          => null,
			'search_value'       => isset( $_REQUEST['s'] ) ? $_REQUEST['s'] : null,
			'search_placeholder' => __( 'Search Everything', '__textdomain__' ),
			'submit_class'       => 'button',
			'submit_value'       => __( 'Search', '__textdomain__' ),
			'alternate_submit'   => false,
		);
		extract( array_map( 'esc_attr', wp_parse_args( $args, $defaults ) ) );

		$rand = rand();
		if( empty( $form_id ) )
			$form_id = "omnisearch_form_$rand";
		if( empty( $search_id ) )
			$search_id = "omnisearch_search_$rand";

		ob_start();
		?>

		<form action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>" method="get" class="<?php echo $form_class; ?>" id="<?php echo $form_id; ?>">
			<input type="hidden" name="page" value="omnisearch" />
			<input name="s" type="search" class="<?php echo $search_class; ?>" id="<?php echo $search_id; ?>" value="<?php echo $search_value; ?>" placeholder="<?php echo $search_placeholder; ?>" />
			<?php if ( $alternate_submit ) : ?>
				<button type="submit" class="<?php echo $submit_class; ?>"><span><?php echo $submit_value; ?></span></button>
			<?php else : ?>
				<input type="submit" class="<?php echo $submit_class; ?>" value="<?php echo $submit_value; ?>" />
			<?php endif; ?>
		</form>

		<?php
		return apply_filters( 'get_omnisearch_form', ob_get_clean(), $args, $defaults );
	}

}
new WP_Omnisearch;

