<?php
/*
 * Plugin Name: Popular Post Real Time
 * Version: 1.0
 * Plugin URI: https://github.com/vicenteguerra/popular-post-real-time.git
 * Description: Wordpress plugin Popular Post (Based in Google Analytics Real Time)
 * Author: Vicente Guerra
 * Author URI: http://vicenteguerra.github.io
 * Requires at least: 4.0
 * Tested up to: 4.0
 *
 * Text Domain: popularpostrealtime
 * Domain Path: /lang/
 *
 * @package WordPress
 * @author Vicente Guerra
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// Load plugin class files
require_once(ABSPATH . 'wp-admin/includes/file.php');
require_once( 'includes/class-popularpostrealtime.php' );
require_once( 'includes/class-popularpostrealtime-settings.php' );

// Load plugin libraries
require_once( 'includes/lib/class-popularpostrealtime-admin-api.php' );
//require_once( 'includes/lib/class-popularpostrealtime-post-type.php' );
//require_once( 'includes/lib/class-popularpostrealtime-taxonomy.php' );

/**
 * Returns the main instance of PopularPostRealTime to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return object PopularPostRealTime
 */
function PopularPostRealTime () {
	$instance = PopularPostRealTime::instance( __FILE__, '1.0.0' );

	if ( is_null( $instance->settings ) ) {
		$instance->settings = PopularPostRealTime_Settings::instance( $instance );
	}

	return $instance;
}

PopularPostRealTime();
