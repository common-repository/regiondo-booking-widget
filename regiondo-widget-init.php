<?php
/*
Plugin Name: Regiondo Booking Widget
Plugin URI: https://pro.regiondo.com
Description: Add Regiondo Booking widget to your page, post or widget.
Version: 2.0
Author: regiondo
Author URI: https://pro.regiondo.com/
License: GPL2
*/
if ( ! defined( 'ABSPATH' ) ) {
	die( 'Invalid request.' );
}
// Plugin init
function regiondo_widget_plugin_init() {
	add_action('widgets_init', 'regiondo_widget_load_widgets');
}
add_action('plugins_loaded', 'regiondo_widget_plugin_init');

function regiondo_widget_load_textdomain() {
	load_plugin_textdomain( 'regiondo-booking-widget', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action('plugins_loaded', 'regiondo_widget_load_textdomain');

function regiondo_widget_load_widgets() {
	require_once('regiondo-widget.php');
	register_widget('regiondo_widget');
}

// Metabox
if ( is_admin() ) {
	require_once('meta-box.php');
}
