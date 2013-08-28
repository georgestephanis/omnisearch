<?php
/*
Plugin Name: Jetpack Omnisearch
Plugin URI: https://github.com/georgestephanis/omnisearch
Description: Enables an Omnisearch in WordPress.
Author: George Stephanis
Version: 1.0
Author URI: http://stephanis.info/
*/

if ( is_admin() ) {
	require_once( dirname(__FILE__) . '/omnisearch-core.php' );
}