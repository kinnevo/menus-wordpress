<?php
/*
Plugin Name: WP Bookbuilder
Plugin URI: http://www.kinnevo.com/wpbookbuilder
Description: Create and manage custom reading paths
Author: Jorge Zavala
Version: 1.2.1
Author URI: http://twitter.com/jzavala
License: GPLv3

////////// WP Bookbuilder (menus) //////////////
*/

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

////////////////////////////////////////////////////////////////////////////////////////////////////
define('CPTURL1',    plugins_url('', __FILE__));

global $nw_db_version;
$nw_db_version = '1.0';

function nw_install()
{
    global $wpdb;
    global $nw_db_version;
 
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    $charset_collate = $wpdb->get_charset_collate();
    add_option( 'nw_db_version', $nw_db_version );

    $table_name = $wpdb->prefix . 'book_navigator_active_users';

    $sql = "CREATE TABLE IF NOT EXISTS  $table_name (
        user_id bigint(20) NOT NULL,
        book mediumint(6) NOT NULL,
        home_page mediumint(6) NOT NULL,
        account_manager_id bigint(20) NOT NULL DEFAULT 1,
        member_since datetime NOT NULL,
        last_login datetime NOT NULL,
        last_sheet_visited mediumint(6) NOT NULL,
        welcome_message text,
        UNIQUE  KEY  user_id (user_id)
    ) $charset_collate;";
    dbDelta( $sql );

	$table_name =  $wpdb->prefix . 'book_navigator_lineal';
	$sql = "CREATE TABLE IF NOT EXISTS $table_name (
		id int(11) NOT NULL AUTO_INCREMENT,
		user_id int(11) NOT NULL default '0',
		book_id int(11) NOT NULL default '0',
		version int(11) NOT NULL default '0',
		page_id int(11) NOT NULL default '0',
		order_id int(11) NOT NULL default '0',
		category_id int(11) NOT NULL default '0',
		visits int(11) NOT NULL default '0',
		liked int(1) NOT NULL default '0',
		dontcare int(1) NOT NULL default '0',
		more_info int(1) NOT NULL default '0',
		ask_info int(11) default '0',
		UNIQUE KEY  id (id)
	) $charset_collate;";
	dbDelta( $sql );

	$table_name =  $wpdb->prefix . 'book';
	$sql = "CREATE TABLE IF NOT EXISTS $table_name (
		id int(11) NOT NULL AUTO_INCREMENT,
		title text,
		description longtext,
		book_type varchar(20),
		category int(11),
		current_version int(11),
		UNIQUE KEY  id (id)
	) $charset_collate;";
	dbDelta( $sql );


	$table_name =  $wpdb->prefix . 'book_content';
	$sql = "CREATE TABLE IF NOT EXISTS $table_name (
		id int(11) NOT NULL AUTO_INCREMENT,
		book_id int(11) NOT NULL default '0',
		version int(11) NOT NULL default '0',
		page_id int(11) NOT NULL default '0',
		order_id int(11) NOT NULL default '0',
		category_id int(11) NOT NULL default '0',
		UNIQUE KEY  id (id)
	) $charset_collate;";
	dbDelta( $sql );


	$table_name =  $wpdb->prefix . 'more_info';
	$sql = "CREATE TABLE IF NOT EXISTS $table_name (
		id int(11) NOT NULL AUTO_INCREMENT,
		description longtext,
		last_request datetime NOT NULL,
		UNIQUE KEY  id (id)
	) $charset_collate;";
	dbDelta( $sql );

}

// WP Bookbuilder relies on the functionality of another plugin for setting taxonomy sort order
// there are two alternatives to test for a valid plugin,
//		check if Category Order and Taxonomy Terms Order plugin is installed or 
//		validate that the wp_terms.term_order column is available ( not implemented )
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

if ( !is_plugin_active('taxonomy-terms-order/taxonomy-terms-order.php')){
	// Deactivate the plugin
	deactivate_plugins(__FILE__);
// Throw an error in the wordpress admin console
    $error_message = __('This plugin requires the <a href="https://wordpress.org/plugins/taxonomy-terms-order/" target="_blank">Category Order and Taxonomy Terms Order</a> plugin to be active!');
    die($error_message);
}

register_activation_hook( __FILE__, 'nw_install' );

// Enable shortcodes in text widgets; we need this enabled in order to display our nav and other tools to the user
add_filter('widget_text','do_shortcode');



// enqueue styles and scripts
add_action( 'init', 'wpbookbuilder_enqueuer' );

function wpbookbuilder_enqueuer() {
	wp_enqueue_style( 'nav_wheel', CPTURL1 . '/css/nav_wheel.css' );
	wp_enqueue_style( 'dragula', CPTURL1 . '/css/dragula.css' );
	wp_enqueue_style( 'wpbookbuilder', CPTURL1 . '/css/wpbookbuilder.css' );
   
    wp_enqueue_script('jquery');
    wp_enqueue_script('jquery-ui');

    wp_register_script('dragula', CPTURL1 . '/js/dragula.js', array( 'jquery' ), '1', true );
    wp_enqueue_script( 'dragula' );

    wp_register_script('example', CPTURL1 . '/js/example.js', array( 'jquery' ), '1', true );
    wp_enqueue_script( 'example' );

}


// enqueue admin styles
function load_wpbookbuilder_wp_admin_style($hook) {
	wp_enqueue_style( 'wpbookbuilderadmin', CPTURL1 . '/css/xbk5-admin.css' );
}
add_action( 'admin_enqueue_scripts', 'load_wpbookbuilder_wp_admin_style' );


require 'build_book.php';
require 'build_new_book.php';

require 'build_chapter.php';
require 'build_a_new_chapter.php';

require 'manage_users.php';
require 'stats.php';
require 'conversations.php';
