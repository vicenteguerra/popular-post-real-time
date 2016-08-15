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
require_once( 'includes/class-google-analytics-api.php' );
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

function mylog($msg){
	$logfile = plugin_dir_path( __FILE__ ) . '/log.txt';
	$actual = file_get_contents($logfile);
	$actual .= $msg . "\n";
	file_put_contents($logfile, $actual);
}

function PopularPostRealTime () {
	$instance = PopularPostRealTime::instance( __FILE__, '1.0.0' );

	if ( is_null( $instance->settings ) ) {
		$instance->settings = PopularPostRealTime_Settings::instance( $instance );
	}

	return $instance;
}

PopularPostRealTime();

function add_cs_cron_fn( $schedules ) {
	$period = 10*60;
	return array('10minutes' => array( 'interval' => $period, 'display' => 'Every 10 minutes' ));
} // end add_cs_cron_fn()

add_filter('cron_schedules', 'add_cs_cron_fn' );

register_activation_hook( __FILE__, 'run_on_activate' );

function run_on_activate() {
	// for notifications
	if( !wp_next_scheduled( 'scheduler_say_hello' ) ) {
		wp_schedule_event( time(), '10minutes', 'scheduler_say_hello' );
	} // for expirations
	if( !wp_next_scheduled( 'scheduler_c_popular_rt' ) ) {
		wp_schedule_event( time(), '10minutes', 'scheduler_c_popular_rt' );
	}
} // end run_on_activate()

// add an action hook for expiration check and notification check
add_action ('scheduler_say_hello', 'say_hello' );
add_action ('scheduler_c_popular_rt',  'c_popular_rt' );

function say_hello(){
	mylog("Yes!, Hello :)");
}

function c_popular_rt() {
	mylog("entro a popular rt");
	$base = 'pprt_';

	$ga = new GoogleAnalyticsAPI('service');

	$client_id = get_option($base . "client_id"); // From the APIs console
	$email = get_option($base . "email");
	$account_id = get_option($base . "account_id");
	$private_key =  get_option($base . "path_private_key");

	if(!$client_id){
		mylog("No existe client id");
		return 0;
	}
	if(!$email){
		mylog("No hay email");
		return 0;
	}
	if(!$account_id){
		mylog("No hay Account id");
		return 0;
	}
	if(!file_exists($private_key)){
		mylog("No carga private key");
		return 0;
	}

	$ga->auth->setClientId($client_id);
	$ga->auth->setEmail($email); // From the APIs console
	$private_key =  get_option($base . "path_private_key");

	$ga->auth->setPrivateKey($private_key); // Path to the .p12 file
	$auth = $ga->auth->getAccessToken();

	// Try to get the AccessToken
	if ($auth['http_code'] == 200) {
			$status = "HTTP OK";
			$accessToken = $auth['access_token'];
			$tokenExpires = $auth['expires_in'];
			$tokenCreated = time();

			// Ejecutar para saber el Account ID
			//getAccountId($ga,$accessToken);die;
			$ga->setAccessToken($accessToken);
			$ga->setAccountId($account_id); // Replace with real Account ID (Use getAccountId function)

			$params = array(
					'metrics' => 'rt:activeUsers',
					'dimensions' => 'rt:pagePath',
					'sort' => '-rt:activeUsers',
					'max-results' => 10
			);
			$popular_posts = $ga->query($params);
			$status = json_encode($popular_posts);

			if(isset($popular_posts['rows'])){
				clearCategory();
				foreach ($popular_posts['rows'] as $popular ) {
					$page_path = $popular[0];
					$active_users = $popular[1];
					setPopularPost($page_path, $active_users);
				}
			}else{
				$status = "Error using Google Analytics";
			}

	} else {
			$status = "Error with Google Analytics API AccessToken";
			mylog("error");
	}
	mylog($status);
}


function clearCategory(){
	mylog("Clear");
	$cat = get_category_by_slug( $slug_popular_rt_cat );
	if($cat){
		$id_popular_rt_cat = $cat->cat_ID;
		$args = array( 'category' => $id_popular_rt_cat  );
		$popular_posts = get_posts( $args );
		foreach ($popular_posts as $current_post) {
			$post_id = $current_post->ID;
			$post_categories = wp_get_post_categories( $post_id );
			if(($key = array_search($id_popular_rt_cat, $post_categories)) !== false) {
				unset($post_categories[$key]);
			}
			wp_set_post_categories( $post_id, $post_categories, false );
			mylog("Cleaned");
		}
	} // end if
}

function setPopularPost($page_path, $active_users){
	mylog("Set Popular");
	$slug_popular_rt_cat = "popular_real_time_cat";
	$category_name = "Popular RT";
	$description = "Category used for displat Popular Post (Google Analytics Real Time)";

	if("/" == $page_path){
		return 0;
	}
	$post_id = url_to_postid($page_path);
	if($post_id){
		$meta_key = "active_users";
		update_post_meta($post_id, $meta_key, $active_users);

		$cat = get_category_by_slug( $slug_popular_rt_cat );
		if(!$cat){
			wp_insert_term($category_name, 'category', array(
				'description'=>$description,
				'slug'=>sanitize_title($slug_popular_rt_cat),
				'parent'=> 0
			));
			$cat = get_category_by_slug( $slug_popular_rt_cat );
		}

		$id_popular_rt_cat = $cat->cat_ID;
		wp_set_post_categories( $post_id, $id_popular_rt_cat, true ); // Append Popular RT Category to Post
	} // end if
}

register_deactivation_hook( __FILE__, 'run_on_deactivate' );
