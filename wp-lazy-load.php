<?php
/**
 * Plugin Name: Lazy Load
 * Plugin URI: https://github.com/lenivene/wp_lazy_load
 * Description: LazyLoad is a jQuery plugin that improves the loading of images.
 * Author: Lenivene Bezerra
 * Author URI: https://github.com/lenivene/
 * Version: 1.0.1
 * License: GPLv2 or later
 * Text Domain: wp-lazy-load
 * Domain Path: /languages/
 */
if( !defined( 'ABSPATH' ) ) { header( 'Location: /' ); exit; }

if ( ! class_exists( 'WP_Lazy_Load' ) ) :

class WP_Lazy_Load{
	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	const VERSION = '1.0.1';

	/**
	 * Instance of this class.
	 *
	 * @var object
	 */
	protected static $instance = null;

	/**
	 * Initialize the plugin actions.
	 */
	private function __construct() {
		// Load scrips
		add_action( 'wp_enqueue_scripts', array( $this, 'add_script_in_layout' ) );
		
		// Execute Lazy Load in the_content
		add_filter( 'the_content', array( $this, 'load' ), 99 );

		// Execute Lazy Load in the_excerpt
		add_filter( 'the_excerpt', array( $this, 'load' ), 99 );

		// Execute Lazy Load in the_content
		add_filter( 'post_thumbnail_html', array( $this, 'load' ), 11 );

		// Execute Lazy Load in avatar
		add_filter( 'get_avatar', array( $this, 'load' ), 11 );

		// Execute Lazy Load in widget_text
		add_filter( 'widget_text', array( $this, 'load' ), 11 );
	}

	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	/**
	 * Get url plugin path.
	 *
	 * @return string
	 */
	public static function get_plugin_dir_url(){
		return plugin_dir_url( __FILE__ );
	}

	/**
	 * Enqueue scripts in template
	 *
	 * @return script
	 */
	public function add_script_in_layout(){
		if( !wp_script_is( 'jquery' ) )
			wp_enqueue_script( 'jquery' );

		// Check if debug is true
		$debug = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

		wp_enqueue_script( 'jquery-sonar', esc_url( $this->get_plugin_dir_url() . "assets/js/jquery.sonar{$debug}.js" ), NULL, '1.8.3', true );
		wp_enqueue_script( 'lazyload', esc_url( $this->get_plugin_dir_url() . "assets/js/jquery.lazyload{$debug}.js" ),
		array( // Requirement
			'jquery',
			'jquery-sonar'
		),
		'1.12.0', true );
	}

	/**
	 * Check robots
	 *
	 * @return boolean
	 */
	public static function is_crawlers(){
		$user_agent = $_SERVER['HTTP_USER_AGENT'];
		$crawlers = array(
			'Search' => 'search',
			'Bot' => 'bot',
			'Crawler' => 'crawler',
			'Google' => 'google',
			'MSN' => 'msnbot',
			'Bing' => 'bingbot',
			'Yahoo' => 'yahoo',
			'Facebook' => 'facebookexternalhit',
			'Abacho' => 'abachobot',
			'Baidu' => 'baiduspider'
		);

		if( !isset( $user_agent ) || empty( $user_agent ) )
			return false;

		$crawlers = implode( '|', $crawlers );
		if ( strpos( $crawlers, strtolower( $user_agent ) ) !== false )
			return true;

		return false;
	}

	public static function load( $content ) {
		/**
		 * Don't lazyload for feeds, previews, robots and if
		 * the content has already been run through previously.
		 */
		if( is_feed() || is_preview() || self::is_crawlers() || false !== mb_strpos( $content, 'data-src' ) )
			return $content;

		// If no src attribute given use image
		$apply_filters = apply_filters( 'wp-lazy-load', 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABAQMAAAAl21bKAAAAA1BMVEXDw8PWKQJEAAAACklEQVQI12NgAAAAAgAB4iG8MwAAAABJRU5ErkJggg==' );

		// Regex that makes the end result loool
		$content = preg_replace( '#<img([^>]+?)src=[\'"]?([^\'"\s>]+)[\'"]?([^>]*)>#', sprintf( '<img${1}src="%s" data-src="${2}"${3}><noscript><img${1}src="${2}"${3}></noscript>', $apply_filters ), $content );

		return $content;
	}
}
/**
 * Initialize the plugin.
 */
add_action( 'plugins_loaded', array( 'WP_Lazy_Load', 'get_instance' ) );

endif;
