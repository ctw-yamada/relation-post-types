<?php
/*
Plugin Name: Relation Post Types
Plugin URI: http://redmine.beapi.fr/projects/show/relations-post-types
Description: Allow to build relations between 2 custom types.
Author: Amaury Balmer
Author URI: http://www.beapi.fr
Version: 1.0.8
Text Domain: relations-post-types
Domain Path: /languages/
Network: false

----

Copyright 2010 Amaury Balmer (amaury@beapi.fr)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

---

Todo :
	Core

	Admin
	
	Extras
	
	Client
		A simple function for template for display related content !
*/

// Setup table name for relations
global $wpdb;
$wpdb->relations = $wpdb->prefix . 'posts_relations';

// Folder name
define ( 'RPT_VERSION', '1.0.8' );
define ( 'RPT_OPTION',  'relations-post-types' );

// Build constants URL.
function define_rpt_paths_plugin() {
	$current_plugins = get_option ( 'active_plugins' );
	foreach ( ( array ) $current_plugins as $plugin ) {
		if (strpos ( $plugin, basename ( __FILE__ ) ) !== false) {
			$path = substr ( str_replace ( 'relations-post-types.php', '', $plugin ), 0, - 1 );
			
			define ( 'RPT_FOLDER', $path );
		}
	}
	
	if (! defined ( 'RPT_FOLDER' )) {
		define ( 'RPT_FOLDER',  'relation-post-types' ); // Relation without S because WP.org don't have S on slug.
	}

	// mu-plugins or regular plugins ?
	if ( is_dir(WPMU_PLUGIN_DIR . DIRECTORY_SEPARATOR . RPT_FOLDER ) ) {
		define ( 'RPT_DIR', WPMU_PLUGIN_DIR . DIRECTORY_SEPARATOR . RPT_FOLDER );
		define ( 'RPT_URL', WPMU_PLUGIN_URL . '/' . RPT_FOLDER );
	} else {
		define ( 'RPT_DIR', WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . RPT_FOLDER );
		define ( 'RPT_URL', WP_PLUGIN_URL . '/' . RPT_FOLDER );
	}
}
define_rpt_paths_plugin();

// Library
require( RPT_DIR . '/inc/functions.inc.php' );
require( RPT_DIR . '/inc/functions.tpl.php' );

// Call client class and functions
require( RPT_DIR . '/inc/class.base.php' );
require( RPT_DIR . '/inc/class.walker.php' );
require( RPT_DIR . '/inc/class.client.php' );
require( RPT_DIR . '/inc/class.widget.php' );

if ( is_admin() ) { // Call admin class
	require( RPT_DIR . '/inc/class.admin.php' );
	require( RPT_DIR . '/inc/class.admin.post.php' );
}

// Activate/Desactive Relation Post Types
register_activation_hook  ( __FILE__, array('RelationsPostTypes_Base', 'activate') );
register_deactivation_hook( __FILE__, array('RelationsPostTypes_Base', 'deactivate') );

add_action( 'plugins_loaded', 'initRelationsPostTypes' );
function initRelationsPostTypes() {
	global $relations_post_types;
	
	// Load translations
	load_plugin_textdomain ( 'relations-post-types', false, RPT_FOLDER . 'languages' );
	
	// Client
	$relations_post_types['client-base']  = new RelationsPostTypes_Client();
	
	// Admin
	if ( is_admin() ) {
		$relations_post_types['admin-base'] = new RelationsPostTypes_Admin();
		$relations_post_types['admin-post'] = new RelationsPostTypes_Admin_Post();
	}
	
	// Widget
	add_action( 'widgets_init', create_function('', 'return register_widget("RelationsPostTypes_Widget");') );
}
?>