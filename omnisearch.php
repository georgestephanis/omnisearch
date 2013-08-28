<?php
/*
Plugin Name: Omnisearch
Plugin URI: https://github.com/georgestephanis/omnisearch
Description: Enables an Omnisearch in WordPress. Feature pitch for Core in 3.8.
Author: George Stephanis
Version: 1.0
Author URI: http://stephanis.info/
*/

if ( is_admin() ) {
	require_once( dirname(__FILE__) . '/omnisearch-core.php' );
}