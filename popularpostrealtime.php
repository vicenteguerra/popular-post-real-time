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
function PopularPostRealTime () {
	$instance = PopularPostRealTime::instance( __FILE__, '1.0.0' );

	if ( is_null( $instance->settings ) ) {
		$instance->settings = PopularPostRealTime_Settings::instance( $instance );
	}

	return $instance;
}

PopularPostRealTime();

/**
 * Schedules
 *
 * @param array $schedules
 *
 * @return array
 */
function db_crontest_schedules( $schedules ) {
	$schedules['10_minutes'] = array(
		'interval' => 10*60,
		'display'  => 'Once Every 10 Minutes',
	);
	return $schedules;
}
add_filter( 'cron_schedules', 'db_crontest_schedules', 10, 1 );
/**
 * Activate
 */
function db_crontest_activate() {
	if ( ! wp_next_scheduled( 'db_crontest' ) ) {
		wp_schedule_event( time(), '10_minutes', 'db_crontest' );
	}
}
register_activation_hook( __FILE__, 'db_crontest_activate' );
/**
 * Deactivate
 */
function db_crontest_deactivate() {
	wp_unschedule_event( wp_next_scheduled( 'db_crontest' ), 'db_crontest' );
}
register_deactivation_hook( __FILE__, 'db_crontest_deactivate' );
/**
 * Crontest
 */
function db_crontest() {
	$ga = new GoogleAnalyticsAPI('service');

	$client_id = get_option($this->base . "client_id"); // From the APIs console
	$email = get_option($this->base . "email");
	$account_id = get_option($this->base . "account_id");
	$private_key =  get_option($this->base . "path_private_key");

	if( file_exists($private_key) && $client_id && $email && $account_id){

		$ga->auth->setClientId($client_id);
		$ga->auth->setEmail($email); // From the APIs console
		$private_key =  get_option($this->base . "path_private_key");

		$ga->auth->setPrivateKey($private_key); // Path to the .p12 file
		$auth = $ga->auth->getAccessToken();

		// Try to get the AccessToken
		if ($auth['http_code'] == 200) {
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
					$this->clearCategory();
					foreach ($popular_posts['rows'] as $popular ) {
						$page_path = $popular[0];
						$active_users = $popular[1];
						$this->setPopularPost($page_path, $active_users);
					}
				}else{
					$status = "Error using Google Analytics";
				}

		} else {
				$status = "Error with Google Analytics API AccessToken";
				//error_log("Problema al verificar el AccessToken de Google Analytics API" . PHP_EOL, 3, get_template_directory() ."/theme_log/error.log");
		}
	}else{
		$status = "First config your Google API credentials";
	}
}


function clearCategory(){
	$cat = get_category_by_slug( $this->slug_popular_rt_cat );
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
		}
	} // end if
}

function setPopularPost($page_path, $active_users){
	if("/" == $page_path){
		return 0;
	}
	$post_id = url_to_postid($page_path);
	if($post_id){
		$meta_key = "active_users";
		update_post_meta($post_id, $meta_key, $active_users);

		$cat = get_category_by_slug( $this->slug_popular_rt_cat );
		if(!$cat){
			wp_insert_term($this->category_name, 'category', array(
				'description'=>$this->description,
				'slug'=>sanitize_title($this->slug_popular_rt_cat),
				'parent'=> 0
			));
			$cat = get_category_by_slug( $this->slug_popular_rt_cat );
		}

		$id_popular_rt_cat = $cat->cat_ID;
		wp_set_post_categories( $post_id, $id_popular_rt_cat, true ); // Append Popular RT Category to Post
	} // end if
}

add_action( 'db_crontest', 'db_crontest' );
