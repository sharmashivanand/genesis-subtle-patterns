<?php

/**
 *
 *	Core functions file gsp-throttle.php
 *	
 *	This class initiates the scripts, styles and the core functionality
 *
 */
 
/** Prevent direct access to this file. **/
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Sorry, you are not allowed to access this file directly.' );
}

class Gsp_Init_Main {
	
	/* Github URLs to fetch the patterns from */
	private $pattern_api_github_url = 'https://api.github.com/repos/subtlepatterns/SubtlePatterns/git/trees/master';
	private $pattern_raw_github_url = 'https://raw.github.com/subtlepatterns/SubtlePatterns/master/';
	
	private $github_response;
	
	/* Constructor class to initialize the functions used in the plugin */
	public function __construct() {

		add_action( 'admin_init', array( $this, 'gsp_admin_init' ) );

		// Check if user has disabled the GUI on the options page
		$enabled = gsb_fetch_option( 'gsp_frontend_preview' );
		if( $enabled ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'gsp_enqueue_scripts' ) );
		}

		add_action( 'wp_head', array( $this, 'gsp_wp_head_action' ) );

  	}
	
	
	public function gsp_admin_init() {
		
		add_action( 'wp_ajax_gsploader', array( $this, 'gsp_ajax_requests' ) );
		
	}
	
	/* Dynamically fetch the pattern URLs and display on the slider */
	public function gsp_ajax_requests() {
		
		global $current_user;

		if( !current_user_can( 'edit_themes' ) ) {
			print json_encode( array( 'error' => 'Invalid request!' ) );
			exit;
		}

		/* Get or set the background on frontend depending on the request */
		switch( $_POST['action_type'] ) {
			
			case "getPatterns":
				/* Fetch the patterns from the transient in the database */
				$response = json_encode( $this->gsp_decode_fetch_patterns() );
			break;

			case "setBackground":
				$response = $this->getSetBackground();
			break;

		}
		
		print $response;
		exit;
		
	}
	

	/* Caller function to fetch the patterns from Github */
	private function getPatterns() {
		
		$patterns = $this->getPatternsFromGithub();
		
		return $patterns;

	}

	
	/* Remotely fetch the patterns from the Subtle Patterns Github repo */
	private function getPatternsFromGithub() {

	    $response = wp_remote_get( $this->pattern_api_github_url );

	    if( $response['response']['code'] == 200 ) {
	    	$this->github_response = $response['body'];
	    	return $this->saveJson()->prepareJson();
	    }
		else {
	    	return json_encode( array('error' => 'Sorry, no connection to the background source. Please try again later!') );
	    }

	}

	
	/* Saves the response from the remote repo to a local json in the plugin directory */
	private function saveJson() {

		if( !is_dir( GSP_PLUGIN_PATH . 'json' ) ) {
			mkdir( GSP_PLUGIN_PATH . 'json' );
		}
		
		file_put_contents( GSP_PLUGIN_PATH . 'json/github_response.json', $this->github_response );

		return $this;
		
	}

	
	/* Prepare a clean json of pattern names and pattern URLs  */
	private function prepareJson() {

		if( !$json = json_decode( $this->github_response ) ) {
			return json_encode( array( 'error' => __( 'Some error occurred while trying to parse the fetched String. Please check the json logfile in the plugin directory and post it to the support forums!', GSP_PLUGIN_DOMAIN ) ) );
		}

		$files = $json->tree;

		if( count( ( array ) $files ) < 10 ) {
			return json_encode( array( 'error' => __( 'Some error occurred! There should have been sent more backgrounds! Please check the json logfile in the plugin directory and post it to the support forums!', GSP_PLUGIN_DOMAIN ) ) );
		}

		$response = array();

		foreach( $files as $k => $file ) {

			if( substr( $file->path, -3 ) != 'png' )
				continue;

			// Extract the response as patterns urls and the pattern names
			$response[] = array(
				'url' => $this->pattern_raw_github_url . $file->path,
				'name' => str_replace( array( '.png', '_' ), array( '', ' ' ), $file->path )
			);

		}

		return json_encode( $response );
		
	}

	
	/* Save the pattern chosen by the user as the background for the site. We will fetch the selected pattern from the repo and save it to the uploads folder in WordPress directory so that the Customizer is able to locate it as valid mime type and allow image customization options on it */
	public function getSetBackground() {

		if ( !current_user_can( 'edit_theme_options' ) || !isset( $_POST['url'] ) )
			exit;

		$url 		= $_POST['url'];
		$filename 	= basename( $url );

		$gh_pattern = strpos( $url, $this->pattern_raw_github_url );

		if( $gh_pattern === false ) {
			return json_encode( array( 'error' => __( 'Cheatin\' huh!', GSP_PLUGIN_DOMAIN ) ) );
			exit;
		}

		// Get Image
		$response 	= wp_remote_get( $url );
		$newfile 	= $response['body']; 
		$upload_dir = wp_upload_dir();
		$uploadPath = $upload_dir['path'] . '/' . $filename;
		file_put_contents( $uploadPath, $newfile );

		$wp_filetype = wp_check_filetype( $filename, null );

		$attachment = array(
			'post_mime_type' 	=> $wp_filetype['type'],
			'guid' 				=> $upload_dir['url'] . '/' . $filename,
			'post_title' 		=> 'subtlepattern_com - ' . str_replace(array('.png', '_'), array('', ' '), $filename),
		);

		$attachment_id = wp_insert_attachment( $attachment, $uploadPath );

		require_once( ABSPATH . 'wp-admin/includes/image.php' );
		$attach_data = wp_generate_attachment_metadata( $attachment_id, $uploadPath );
		wp_update_attachment_metadata( $attachment_id, $attach_data );

		update_post_meta( $attachment_id, '_wp_attachment_is_custom_background', get_option('stylesheet' ) );

		$url = wp_get_attachment_image_src( $attachment_id, 'full' );
		$thumbnail = wp_get_attachment_image_src( $attachment_id, 'thumbnail' );
		set_theme_mod( 'background_image', esc_url_raw( $url[0] ) );
		set_theme_mod( 'background_image_thumb', esc_url_raw( $thumbnail[0] ) );

		return json_encode( array( 'success' => true ) );
		
	}
	
	
	/* Enqueue the necessary js files that build the UI for the pattern selector */
	public function gsp_enqueue_scripts() {
		
		if( current_user_can( 'edit_theme_options' ) ) {
			wp_enqueue_script( 'gsp-pattern-slider', GSP_PLUGIN_URL . 'scripts/gsp-flexslider.js', array('jquery'), '', true );
			wp_enqueue_script( 'gsp-frontend-scripts', GSP_PLUGIN_URL . 'scripts/gsp-frontend-script.js', array('jquery'), '', true );
			wp_enqueue_style( 'gsp-frontend-css', GSP_PLUGIN_URL . 'css/gsp-frontend.css' );
	    }
		
	}
	
	public function gsp_wp_head_action() {

	    echo '<script type="text/javascript">';
	    echo 'var ajaxurl = "' . admin_url('admin-ajax.php') . '";';
	    echo '</script>';		
		
	}
	
	
	/* Set the patterns retrieved from the repo as a separate array and set a transient that stores them. This will help us reduce the loading time and increase the user experience (in terms of reduced waiting time) largely */
	public function gsp_fetch_patterns() {
		
		// Check if pattern transient already exists
		$if_transient_patterns = get_transient( 'gsp_patterns' );
		
		// If transient does not exist, set a transient with pattern names and urls
		if( !$if_transient_patterns ) {
			$patterns = json_decode( $this->getPatterns() );
			
			if( !$patterns )
				return false;
			
			foreach( $patterns as $pattern ) {
				$patterns_url[] = base64_encode( $pattern->url ); // Encode the urls with MIME base64
				$patterns_name[] = $pattern->name;
			}
			
			$available_patterns = array_combine( $patterns_name, $patterns_url );
			set_transient( 'gsp_patterns', $available_patterns, 60 * 60 * 24 );
			
			return $available_patterns;
		}
		else {
			return $if_transient_patterns;	// Fetch the pattern data from existing transient
		}
		
	}
	
	
	/* Decode the MIME base64 urls in the transient as they can be parsed as clean URLs */
	private function gsp_decode_fetch_patterns() {
		
		$patterns_data = $this->gsp_fetch_patterns();
		
		if( !$patterns_data )
			return;
		
		foreach( $patterns_data as $name => $url ) {
			$names[] = $name;
			$urls[] = base64_decode( $url );
		}
		
		$raw_pattern_data = array_combine( $names, $urls );
		
		return $raw_pattern_data;
		
	}

}