<?php

/**
 *
 *	Genesis Subtle Patterns admin page
 *	@author Shivanand Sharma	
 *
 *	Builds the admin page for the plugin that can include plugin options
 *
 */
 
/** Prevent direct access to this file. **/
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Sorry, you are not allowed to access this file directly.' );
}

class Gsp_Admin extends Genesis_Admin_Boxes {
	
	function __construct() {
		
		$page_id  = 'genesis-subtle-patterns';
		
		$menu_ops = array(
			'submenu' => array(
				'parent_slug' => __( 'genesis', 'genesis' ),
				'page_title' => __( 'Genesis Subtle Patterns', GSP_PLUGIN_DOMAIN ),
				'menu_title' => __( 'Subtle Patterns', GSP_PLUGIN_DOMAIN ) 
			) 
		);
		
		$page_ops = array(
			'save_button_text'  => __( 'Save Options', 'genesis' ),
			'reset_button_text' => __( 'Reset Options', 'genesis' ),
		);
		
		$settings_field = GSP_SETTINGS_FIELD;
		
		$default_settings = gsp_default_settings();
				
		$this->create( $page_id, $menu_ops, $page_ops, $settings_field, $default_settings );
		
		add_action( 'genesis_settings_sanitizer_init', array( $this, 'sanitizer_filters' ) );
		
		add_action( 'admin_print_styles', array( $this, 'styles' ) );
		
	}
	
	function metaboxes() {
		
		add_meta_box( 'gsp-main-panel', __( 'Genesis Subtle Patterns', GSP_PLUGIN_DOMAIN ), array( $this, 'gsp_main_panel' ), $this->pagehook, 'main' );
		
		add_action( $this->pagehook . '_settings_page_boxes', array( $this, 'gsp_intro_section' ), 5 );
	
	}
	
	function sanitizer_filters() {
		
		genesis_add_option_filter( 'one_zero', $this->settings_field, array(
			'gsp_frontend_preview',
		));
		
	}
	
	function styles() {
		
		wp_enqueue_style( 'gsp-panel-styles', GSP_PLUGIN_URL . 'css/gsp-admin-styles.css' );
		
	}
	
	function gsp_intro_section() {
		
		?>
		<div class="clearfix"></div>
		<div class="gsp-intro-box">
			<h3><?php _e( 'Say Hello to Genesis Subtle Patterns', GSP_PLUGIN_DOMAIN ); ?></h3>
			<p><?php _e( 'Genesis Subtle Patterns allows you to use beautiful and elegant subtle patterns on your Genesis website. It empowers the site admin to customize the site background right from pattern selector UI on the front-end.', GSP_PLUGIN_DOMAIN ); ?></p>
			
			<p><?php printf( __( 'You get the entire collection of %sSubtle Patterns%s (200+ and growing). You can preview the patterns and save the one that works for your site.', GSP_PLUGIN_DOMAIN ), '<a href="http://subtlepatterns.com/">', '</a>' ); ?></p>			
		</div>
		<?php
		
	}
	
	function gsp_main_panel() {
		
		?>
		<div class="gsp-panel-container">
			<table>
			<tr>
				<td colspan="2">
				<p><em><?php _e( 'Use this option to disable the UI that shows up on the site frontend.', GSP_PLUGIN_DOMAIN ); ?></em></p>
				</td>
			</tr>
			
			<tr>				
				<td class="field-label">
				<p><label for="<?php $this->field_id( 'gsp_frontend_preview' ); ?>"><?php _e( 'Enable pattern selector? ', GSP_PLUGIN_DOMAIN ); ?></label></p>
				</td>
				<td>
				<p><input type="checkbox" name="<?php $this->field_name( 'gsp_frontend_preview' ); ?>" id="<?php $this->field_id( 'gsp_frontend_preview' ); ?>" value="1"<?php checked( $this->get_field_value( 'gsp_frontend_preview' ) ); ?> /></p>
				</td>
			</tr>
			</table>
		</div>
		<?php
		
	}
	
}