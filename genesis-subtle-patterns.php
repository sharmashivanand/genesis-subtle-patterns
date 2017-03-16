<?php

/**
 *
 * Plugin Name: Genesis Subtle Patterns
 * Plugin URI: https://www.binaryturf.com/wordpress/genesis
 * Description: Enables you to set elegant subtle patterns as the background for your Genesis site.
 * Version: 1.0
 * Author: Shivanand Sharma
 * Author URI: https://www.binaryturf.com
 * License: GPL-2.0+
 * License URI: http://www.opensource.org/licenses/gpl-license.php
 *
 * Credits: Subtle Background Patterns developed by Marcus Pohorely(http://www.clubdesign.at ) which is the inspiration behind Genesis Subtle Patterns
 *
 */
 

define( 'GSP_SETTINGS_FIELD','genesis-subtle-patterns' );
define( 'GSP_PLUGIN_URL', plugin_dir_url(__FILE__) );
define( 'GSP_PLUGIN_PATH', plugin_dir_path(__FILE__) );
define( 'GSP_PLUGIN_DOMAIN', 'gsp-domain' );

register_activation_hook( __FILE__, 'gsp_activation' );

function gsp_activation() {

	if ( 'genesis' != basename( TEMPLATEPATH ) )
		gsp_error_template();
	
	if( !defined( 'PARENT_THEME_VERSION' ) || !version_compare( PARENT_THEME_VERSION, '2.1.0', '>=' ) || !version_compare( get_bloginfo('version'), '4.0', '>=' ) )
		gsp_error_version( '2.1.0', '4.0' );
	
	if( version_compare( PHP_VERSION, '5.2.0', '<' ) ) { 
		
		deactivate_plugins( plugin_basename(__FILE__));
		wp_die( sprintf( __( 'Genesis Subtle Patterns requires PHP 5.2 or higher.', GSP_PLUGIN_DOMAIN ) ) ); 
	
	}

}

/**
 *	Check if the parent theme Genesis is installed and activated, else deactivate
 */
 
function gsp_error_template() {
	
	deactivate_plugins( plugin_basename( __FILE__ ) );
	wp_die( sprintf( __( '%sGenesis Subtle Patterns plugin requires %sGenesis Framework%s to be installed and activated. Please install Genesis as the parent theme to use Genesis Subtle Patterns.%sIf Genesis Framework / Genesis child theme is already installed, go to %sThemes page%s and activate it.%s%sReturn to Plugins page%s</p>', GSP_PLUGIN_DOMAIN ), '<p>', '<a title="Genesis Framework" href="http://www.binaryturf.com/genesis">', '</a>', '</p><p>', '<a title="Go to Themes page" href="' . self_admin_url('themes.php') . '">', '</a>', '</p><p>', '<a title="Go to Plugins page" href="' . self_admin_url( 'plugins.php' ) . '" target="_parent">', '</a>' ) );

}

/**
 *	Check the WordPress and Genesis version
 *	WordPress to be 4.0 and Genesis to be 2.1.0 to use the plugin, else deactivate
 */
 
function gsp_error_version( $genesis_version = '2.1.0', $wp_version = '4.0' ) {
	
	deactivate_plugins( plugin_basename( __FILE__ ) );
	wp_die( sprintf( __( '%sGenesis Subtle Patterns requires WordPress version %s and Genesis version %s or greater. Please update to the latest version and try again.%sReturn to Plugins page%s', GSP_PLUGIN_DOMAIN ), '<p>', '<strong>' . $wp_version . '</strong>', '<strong>' . $genesis_version . '</strong>', '</p><p><a title="Go to Plugins page" href="' . self_admin_url( 'plugins.php' ) . '" target="_parent">', '</a></p>' ) );

}


/**
 *	Adding the Support and Author links to the plugin in the admin area on the plugins page
 */
 
add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'gsp_add_action_links' );

function gsp_add_action_links ( $links ) {
	
	$link = array(
		'<a href="' . menu_page_url( GSP_SETTINGS_FIELD, 0 ) . '">Say Hello</a>',
		'<a href="https://www.binaryturf.com/forum/genesis-subtle-patterns">Support</a>'
	);
	
	return array_merge( $links, $link );

}

/**
 *	Initiate the plugin admin page and the core functions
 */

add_action( 'genesis_init', 'gsp_loader', 20 );

function gsp_loader() {
	
	if( is_admin() ) {
		require_once( GSP_PLUGIN_PATH . 'admin/gsp-admin.php' );
	}
	
	add_theme_support( 'custom-background' );
	
	require_once( GSP_PLUGIN_PATH . 'admin/gsp-throttle.php' );
	$throttle_gsp = new Gsp_Init_Main();
	
}

/**
 *	Add the page menu to Genesis menu
 */

add_action( 'genesis_admin_menu', 'gsp_settings_admin' );

function gsp_settings_admin() {
	
	$gsp_init_admin = new Gsp_Admin;


}

/**
 *	Build default optionset for the plugin
 */

function gsp_default_settings() {
	
	$defaults = array(
		'gsp_frontend_preview'	=> 1,
	);
	
	// Provide a filter to allow additional settings
	return apply_filters( 'gsp_default_settings', $defaults );
	
}

/**
 *	Helper function to retrieve option value from the database
 */
function gsb_fetch_option( $key ) {
	
	return genesis_get_option( $key, GSP_SETTINGS_FIELD );
	
}