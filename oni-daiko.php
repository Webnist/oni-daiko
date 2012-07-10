<?php
/*
Plugin Name: Oni Daiko
Plugin URI: http://plugins.webnist.net/
Description: Shows a list of the latest posts from each blogs on a multisaite blog.
Version: 0.7.1.0
Author: Webnist
Author URI: http://www.webnist.net
Network: true
License: GPLv2 or later
*/
define( 'ONIDAIKO_VERSION', '0.7.1.0' );

if ( ! defined( 'ONI_DAIKO_DIR' ) )
	define( 'ONI_DAIKO_DIR', WP_PLUGIN_DIR . '/oni-daiko' );

if ( ! defined( 'ONI_DAIKO_URL' ) )
	define( 'ONI_DAIKO_URL', WP_PLUGIN_URL . '/oni-daiko' );

if ( ! defined( 'ONI_DAIKO_IMG_DIR' ) )
	define( 'ONI_DAIKO_IMG_DIR', ONI_DAIKO_DIR . '/images' );

if ( ! defined( 'ONI_DAIKO_IMG_URL' ) )
	define( 'ONI_DAIKO_IMG_URL', ONI_DAIKO_URL . '/images' );

load_plugin_textdomain( 'onidaiko', false, '/oni-daiko/languages/' );

add_action( 'admin_menu', 'oni_daiko_menu' );
function oni_daiko_menu() {
	add_menu_page( __( 'Oni Daiko', 'onidaiko' ), __( 'Oni Daiko', 'onidaiko' ), 'level_10', 'oni-daiko.php', 'oni_daiko_setting_menu', ONI_DAIKO_IMG_URL . '/admin_side.gif' );
}

add_filter( 'rewrite_rules_array', 'onidaiko_rewrite_rules' );
function onidaiko_rewrite_rules( $rules ) {
	$newrules = array();
	$newrules['oni-daiko/?$'] = 'index.php?oni-daiko=post';
	$newrules['oni-daiko-feed/?$'] = 'index.php?oni-daiko=feed';
	return $newrules + $rules;
}

add_filter( 'query_vars', 'onidaiko_query_var' );
function onidaiko_query_var( $vars ){
	array_push( $vars, 'oni-daiko' );
	array_push( $vars, 'oni-daiko-feed' );
	return $vars;
}

add_filter( 'init', 'onidaiko_flushRules' );
function onidaiko_flushRules(){
	global $wp_rewrite;
	$wp_rewrite->flush_rules();
}

function get_onidaiko_blogs(){
	global $wpdb;
	$query = "SELECT * FROM {$wpdb->blogs} WHERE public = '1' ";
	$set_blog_list = $wpdb->get_results( $query );
	return $set_blog_list;
}

add_filter( 'posts_request', 'get_onidaiko_posts' );
function get_onidaiko_posts( $input ) {
	global $current_blog;
	if ( get_query_var( 'oni-daiko' ) && $current_blog->blog_id == 1 ) {
		global $wpdb;
		$limit = get_query_var( 'posts_per_page' );
		$where = get_query_var( 'posts_where_request' );
				/*
				$where		= apply_filters_ref_array( 'posts_where_request' );
				$groupby	= apply_filters_ref_array( 'posts_groupby_request' );
				$join		= apply_filters_ref_array( 'posts_join_request' );
				$orderby	= apply_filters_ref_array( 'posts_orderby_request' );
				$distinct	= apply_filters_ref_array( 'posts_distinct_request' );
				$fields		= apply_filters_ref_array( 'posts_fields_request' );
				$limits		= apply_filters_ref_array( 'post_limits_request' );
				*/
		$set_blog_list = get_onidaiko_blogs();
		$sql = '';
		$count = '';
		$blog_count = count( $set_blog_list );
		foreach ( $set_blog_list as $blogs ) {
			$count++;
			$blog_list = $blogs->blog_id;
			switch_to_blog( $blogs->blog_id );
			$sql .= ("SELECT *, $blog_list as blog_id FROM $wpdb->posts WHERE post_type = 'post' AND post_status = 'publish'");
			if ( $count != $blog_count ) {
				$sql .= 'UNION' . "\n";
			}
			restore_current_blog();
		}
		$sql .= " ORDER BY post_date DESC LIMIT $limit";
		$posts = $wpdb->get_results( $sql, OBJECT );
		//$this->request = " SELECT $found_rows $distinct $fields FROM $wpdb->posts $join WHERE 1=1 $where $groupby $orderby $limits";
		return $sql;
	} else {
		return $input;
	}
}

add_action( 'pre_get_posts', 'onidaiko_post' );
function onidaiko_post( $query ) {
	global $current_blog;
	if ( ! is_admin() && get_query_var( 'oni-daiko' ) && $current_blog->blog_id == 1 ) {
		//set_query_var( 'posts_per_page', 1 );
		$query->is_home = false;
		$query->is_archive = true;
	} elseif ( ! is_admin()  && get_option( 'oni_home' ) == true ) {
	}
}
