<?php
/**
 * Plugin Name:       Roomworksmedia Forcast
 * Description:       An interactive block with the Interactivity API.
 * Version:           0.1.0
 * Requires at least: 6.6
 * Requires PHP:      7.2
 * Author:            The WordPress Contributors
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       roomworksmedia-forcast
 *
 * @package           roomworksmedia-block
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Registers the block using the metadata loaded from the `block.json` file.
 * Behind the scenes, it registers also all assets so they can be enqueued
 * through the block editor in the corresponding context.
 *
 * @see https://developer.wordpress.org/reference/functions/register_block_type/
 */
function create_block_roomworksmedia_forcast_block_init() {
	register_block_type_from_metadata( __DIR__ . '/build' );
}
add_action( 'init', 'create_block_roomworksmedia_forcast_block_init' );


/**
 * Settings
 */
require_once plugin_dir_path( __FILE__ ) . 'inc/settings_meta.php';


/**
 * weather_app_enqueue_block_assets function
 *
 * @return void
 */
function weather_app_enqueue_block_assets() {
	$weather_api_key = get_option( 'weather_app_api_key' );

	wp_enqueue_script(
		'weather-app-block',
		plugins_url( 'src/edit.js', __FILE__ ),
		array( 'wp-blocks', 'wp-element', 'wp-editor' ),
		file_exists( plugin_dir_path( __FILE__ ) . 'src/edit.js' ) ? filemtime( plugin_dir_path( __FILE__ ) . 'src/edit.js' ) : false
	);

	wp_localize_script(
		'weather-app-block',
		'weatherAppData',
		array(
			'apiKey' => $weather_api_key,
		)
	);
}
add_action( 'enqueue_block_editor_assets', 'weather_app_enqueue_block_assets' );

add_action(
	'rest_api_init',
	function () {
		register_rest_route(
			'weather-app/v1',
			'/data',
			array(
				'methods'             => 'GET',
				'callback'            => 'fetch_weather_data',
				'permission_callback' => '__return_true', // For testing only; consider more secure options in production.
			)
		);
	}
);

function fetch_weather_data( WP_REST_Request $request ) {
	$api_key  = get_option( 'weather_app_api_key' );
	$location = sanitize_text_field( $request->get_param( 'location' ) );
	$api_key  = $api_key; // Replace with your actual Weather API key

	$response = wp_remote_get( "https://api.weatherapi.com/v1/forecast.json?key={$api_key}&q={$location}&aqi=no" );

	if ( is_wp_error( $response ) ) {
		return new WP_Error( 'api_error', 'Failed to fetch weather data', array( 'status' => 500 ) );
	}

	$body = wp_remote_retrieve_body( $response );
	return json_decode( $body );
}
