<?php
/*
Plugin Name: Tvorog
Plugin URI: http://jorhult.se
Description: 
Version: 0.1
Author: Mikael Jorhult
Author URI: http://jorhult.se
License: MIT (http://mikaeljorhult.mit-license.org)
*/

class Jorhult_Tvorog {
	static $instance;
	private $plugin_name = 'jorhult-tvorog';
	private $keys_file = 'twitter-keys.php';
	private $consumer_key = '';
	private $consumer_secret = '';
	private $access_token = '';
	private $last_request = '';
	private $last_response = '';
	private $settings = array();
	
	/**
	 * The constructor is executed when the class is instantiated and the plugin gets loaded.
	 * @return void
	 */
	function __construct() {
		self::$instance = $this;
		// Require key file if present.
		if ( file_exists( dirname( __FILE__ ) . '/' . $this->keys_file ) ) {
			require_once( $this->keys_file );
		}
		
		$this->load_settings();
		
		// Check for defined Twitter application keys.
		if ( defined( TVOROG_CONSUMER_KEY ) && defined( TVOROG_CONSUMER_SECRET ) && TVOROG_CONSUMER_KEY != '' || TVOROG_CONSUMER_SECRET != '' ) {
			$this->consumer_key = TVOROG_CONSUMER_KEY;
			$this->consumer_secret = TVOROG_CONSUMER_SECRET;
		} else {
			// Get keys from database.
			if ( is_array( $this->settings ) ) {
				if ( isset( $this->settings[ 'consumer-key' ] ) && !empty( $this->settings[ 'consumer-key' ] ) ) {
					$this->consumer_key = $this->settings[ 'consumer-key' ];
				}
				
				if ( isset( $this->settings[ 'consumer-secret' ] ) && !empty( $this->settings[ 'consumer-secret' ] ) ) {
					$this->consumer_secret = $this->settings[ 'consumer-secret' ];
				}
			}
		}
		
		if ( is_array( $this->settings ) && isset( $this->settings[ 'access-token' ] ) ) {
			$this->access_token = $this->settings[ 'access-token' ];
		}
		
		// Initiate
		if ( !defined( TVOROG_LITE ) ) {
			add_action( 'plugins_loaded', array( $this, 'init_localization' ), 20 );
			add_action( 'wp_enqueue_scripts', array( $this, 'init_scripts' ), 20 );
			add_action( 'wp_enqueue_scripts', array( $this, 'init_styles' ), 20 );
			add_action( 'init', array( $this, 'init_shortcodes' ), 20 );
			add_action( 'admin_menu', array( $this, 'init_admin_menu' ) );
			add_action( 'admin_init', array( $this, 'init_admin_init' ) );
		}
	}
	
	function load_settings() {
		$this->settings = (array) get_option( $this->plugin_name . '-settings' );
	}
	
	function save_settings() {
		update_option( $this->plugin_name . '-settings', $this->settings );
	}
	
	/**
	 * Loading the gettext textdomain first from the WP languages directory, 
	 * and if that fails try the subfolder /apps/languages/ in the plugin directory. 
	 * @return void
	 */
	function init_localization() {
		$locale = apply_filters( 'plugin_locale', get_locale(), $this->plugin_name );
		load_textdomain( $this->plugin_name, WP_LANG_DIR . '/' . $this->plugin_name . '-' . $locale . '.mo' );
		load_plugin_textdomain( $this->plugin_name, false, dirname( plugin_basename( __FILE__ ) ) . '/apps/languages/' );
	}
	
	function init_scripts() {
		
	}
	
	function init_styles() {
		
	}
	
	function init_shortcodes() {
		
	}
	
	function init_admin_init() {
		register_setting( $this->plugin_name . '-settings-group', $this->plugin_name . '-settings');
	}
	
	/**
	* Add menu item in options menu.
	* @return void
	*/
	function init_admin_menu() {
		add_options_page( 'Tvorog', 'Tvorog', 'manage_options', $this->plugin_name, array( $this, 'menu_page' )  );
	}
	
	/**
	* This function will be executed when the admin sub page is to be loaded.
	* @return void
	*/
	function menu_page() {
		require( 'apps/administration.php' );
	}
	
	function get_token() {
		// Bail if access token already present.
		if ( !empty( $this->access_token ) ) {
			return;
		}
		
		// Request token.
		$response = $this->request( 'https://api.twitter.com/oauth2/token/', array( 'method' => 'POST' ) );
		
		// Save access token if one is returned.
		if ( is_array( $response ) && isset( $response[ 'token_type' ] ) && $response[ 'token_type' ] == 'bearer' ) {
			$this->access_token = $response[ 'access_token' ];
		}
	}
	
	function request( $url, $args = array() ) {
		// Get bearer access token if not already present.
		if ( !stristr( $url, '/oauth2/token/' ) != false ) {
			$this->get_token();
		}
		
		// Default values for requests.
		$defaults = array(
			'headers' => array(
				'Authorization' => 'Basic ' . base64_encode( $this->consumer_key . ':' . $this->consumer_secret ),
				'Content-Type' => 'application/x-www-form-urlencoded;charset=UTF-8'
			),
			'method' => 'GET'
		);
		
		if ( !empty( $this->access_token ) ) {
			$defaults[ 'headers' ][ 'Authorization' ] = 'Bearer ' . $this->access_token;
		} else {
			$defaults[ 'body' ] = array(
				'grant_type' => 'client_credentials'
			);
		}
		
		$args = wp_parse_args( $args, $defaults );
		
		// Make request.
		$response = wp_remote_request( $url, $args );
		$this->last_request = $args;
		$this->last_response = $response;
		
		// Parse and return JSON if valid response.
		if ( $response[ 'response' ][ 'code' ] == 200 ) {
			return json_decode( $response[ 'body' ], true );
		} else {
			return false;	
		}
	}
}

/**
 * Register the plugin.
 */
new Jorhult_Tvorog;