<?php
/*
Plugin Name: Multisite Site SSL Indicator
Plugin URI: https://www.nostromo.nl/wordpress-plugins/
Description: Indicate if sites in a multisite subdomain network are SSL/HTTPS enabled.
Author: Marcel Bootsman
Version: 1.0
Author URI: https://www.nostromo.nl
Text Domain: ms-site-ssl-indicator
Domain Path: /languages/

*/

// Pre-activation checks
// Plugin is not really needed on subdir networks
register_activation_hook( __FILE__, 'nstrm_plugin_activate' );
function nstrm_plugin_activate() {

	if ( defined( 'SUBDOMAIN_INSTALL' ) && ( false === SUBDOMAIN_INSTALL ) ) {
		deactivate_plugins( basename( __FILE__ ) ); // Deactivate myself
		wp_die( __( 'Sorry, but you can\'t run this plugin on a subdirectory network. Well actually you can, but it\'s not really smart is it?', 'ms-site-ssl-indicator' ) );
	}
}

// hide plugin from sites plugin list, except for super admin
add_filter( 'all_plugins', 'nstrm_hide_plugin_from_plugin_list' );
function nstrm_hide_plugin_from_plugin_list( $plugins ) {

	if ( ! is_super_admin() ) {
		// Hide me
		if ( in_array( __FILE__, array_keys( $plugins ) ) ) {
			unset( $plugins[ __FILE__ ] );
		}
	}

	return $plugins;
}

// Add column
add_filter( 'wpmu_blogs_columns', 'nstrm_add_ms_site_ssl_indicator_column' );
function nstrm_add_ms_site_ssl_indicator_column( $sites_columns ) {
	// Split the array, we want to add a column on the second row

	// Get first column
	$first_column = array_slice( $sites_columns, 0, 1 );

	// Get rest of the columns
	$rest_columns = array_slice( $sites_columns, 1 );

	// Combine them, and add our own column
	$sites_columns = $first_column + array( 'blog_https' => 'HTTPS' ) + $rest_columns;

	return $sites_columns;
}

// Fill column
add_action( 'manage_sites_custom_column', 'nstrm_add_ms_site_ssl_indicator_content_in_column', 10, 2 );
function nstrm_add_ms_site_ssl_indicator_content_in_column( $column_name, $blog_id ) {
	if ( $column_name == 'blog_https' ) {
		echo strstr( get_home_url( $blog_id ), 'https://' ) ? '<span class="dashicons dashicons-lock"></span>' : '<span class="dashicons dashicons-unlock"></span>';
	}
}

// Style the column
add_action( 'admin_head', 'nstrm_add_ms_site_ssl_indicator_css' );
function nstrm_add_ms_site_ssl_indicator_css() {
	echo '<style>
    #blog_https {
    	width: 3em;
    }
  </style>';
}