<?php
/*
Plugin Name: Oni Daiko
Plugin URI: http://plugins.webnist.net/
Description: Shows a list of the latest posts from each blogs on a multisaite blog.
Version: 0.7.1.0
Author: Webnist
Author URI: http://www.webnist.net
License: GPLv2 or later
*/

if ( !defined( 'ONI_DAIKO_DIR' ) )
	define( 'ONI_DAIKO_DIR', WP_PLUGIN_DIR . '/oni-daiko' );

if ( !defined( 'ONI_DAIKO_URL' ) )
	define( 'ONI_DAIKO_URL', WP_PLUGIN_URL . '/oni-daiko' );

if ( !class_exists('OniDaikoAdmin') )
	require_once(dirname(__FILE__).'/includes/class-admin-menu.php');

if ( !class_exists('OniDaikoWidget') )
	require_once(dirname(__FILE__).'/includes/class-widget.php');

load_plugin_textdomain(OniDaiko::TEXT_DOMAIN, false, dirname(plugin_basename(__FILE__)) . '/languages/');

class OniDaiko {
	const TEXT_DOMAIN = 'oni-daiko';
	const ONIDAIKO_SLUG = 'oni-daiko';

	private $current_blog_id;
	private $plugin_basename;
	private $plugin_dir_path;
	private $plugin_dir_url;

	public function __construct() {
		$this->current_blog_id = get_current_blog_id();
		$this->plugin_basename = self::plugin_basename();
		$this->plugin_dir_path = self::plugin_dir_path();
		$this->plugin_dir_url = self::plugin_dir_url();

		$this->main_include = get_option( 'oni-daiko-main-include', 1 );
		$this->slug = get_option( 'oni-daiko-slug', 'oni-daiko' );

		if ( $this->current_blog_id == 1 ) {
			register_activation_hook( __FILE__, array( &$this, 'flush_rewrite_rules', 'add_option' ) );
			register_deactivation_hook( __FILE__, array( &$this, 'flush_rewrite_rules', 'delete_option' ) );
	
			add_filter( 'init', array( &$this, 'add_rewrite_rule' ) );

			add_filter( 'query_vars',  array( &$this, 'add_query_var' ) );
			
			add_filter( 'posts_request', array( &$this, 'add_posts_request' ), 10, 2 );
			
			add_action( 'pre_get_posts', array( &$this, 'add_pre_get_posts' ) );

			remove_filter('do_feed_rdf', 'do_feed_rdf', 10);
			remove_filter('do_feed_rss', 'do_feed_rss', 10);
			remove_filter('do_feed_rss2', 'do_feed_rss2', 10);
			remove_filter('do_feed_atom', 'do_feed_atom', 10);
			add_action('do_feed_rdf', array( &$this, 'custom_feed_rdf' ), 10, 1);
			add_action('do_feed_rss', array( &$this, 'custom_feed_rss' ), 10, 1);
			add_action('do_feed_rss2', array( &$this, 'custom_feed_rss2' ), 10, 1);
			add_action('do_feed_atom', array( &$this, 'custom_feed_atom' ), 10, 1);

		}
		register_uninstall_hook( __FILE__, array( &$this, 'flush_rewrite_rules', 'delete_option' ) );
	}

	static public function plugin_basename() {
		return plugin_basename(__FILE__);
	}

	static public function plugin_dir_path() {
		return plugin_dir_path( self::plugin_basename() );
	}
	
	static public function plugin_dir_url() {
		return plugin_dir_url( self::plugin_basename() );
	}

	public function flush_rewrite_rules( $hard = false ) {
		global $wp_rewrite;
		$wp_rewrite->flush_rules( $hard );
	}

	public function add_option() {
		if ( !get_option( 'oni-daiko-slug' ) ) {
			update_option( 'oni-daiko-slug', 'oni-daiko' );
		}
	}
	
	public function delete_option() {
		if ( get_option( 'oni-daiko-slug' ) ) {
			delete_option( 'oni-daiko-slug' );
		}
	}

	public function add_rewrite_rule() {
	
		global $wp_rewrite;
		$wp_rewrite->add_rewrite_tag( '%' . $this->slug . '%', '(' . $this->slug . ')', $this->slug . '=' );
		$wp_rewrite->add_permastruct( $this->slug, '%' . $this->slug . '%', false );

	}

	public function add_query_var( $vars ){
		array_push( $vars, $this->slug );
		return $vars;
	}

	public function add_pre_get_posts( $query ) {
		global $wp_query;
		if ( !is_admin() && $query->is_main_query() && get_query_var( $this->slug ) && $this->current_blog_id == 1 ) {
			$query->is_home = false;
		}
	}
	
	public function add_posts_request( $sql, $query ) {
		global $wpdb;
		if( $query->is_main_query() && $this->current_blog_id == 1 && get_query_var( $this->slug ) == $this->slug ) {
			if ( preg_match('/(SELECT(.*))(FROM)/', $sql, $matches) ) {
				$select = trim( $matches[1] );
			}
			if ( preg_match('/(FROM(.*))(WHERE)/', $sql, $matches) ) {
				$from = trim( $matches[1] );
			}
			if ( preg_match('/(WHERE(.*))(ORDER|GROUP)/', $sql, $matches) ) {
				$where = trim( $matches[1] );
				$where = str_replace( 'wp_posts.', '', $where );
			}
			if ( preg_match('/(ORDER|GROUP)(.*)/', $sql, $matches) ) {
				$orderby = trim( $matches[0] );
				$orderby = str_replace( 'wp_posts.', '', $orderby );
			}
			$sql = '';
			$count = 1;
			$set_blog_list = $this->get_blog_list();
			$blog_count = count( $set_blog_list );
			foreach ( $set_blog_list as $blogs ) {
				$blog_id = $blogs->blog_id;
				switch_to_blog( $blog_id );
				$sql .= ("SELECT *, $blog_id as blog_id FROM $wpdb->posts $where");
				//$sql .= ("SELECT *, $blog_id as blog_id FROM $wpdb->posts WHERE post_type = 'post' AND post_status = 'publish'");
				if ( $count != $blog_count ) {
					$sql .= ' UNION' . "\n";
				}
				restore_current_blog();
				$count++;
			}
			$sql = "$sql $orderby";
		}
			
		return $sql;
	}

	public function get_oni_posts( $number ) {
		global $wpdb, $wp_query;
		$number = $number ? $number : 5;
		$limits = 'LIMIT ' . $number;
		$blogs = $this->get_blog_list();
		$sql = '';
		$count = 1;
		$set_blog_list = $this->get_blog_list();
		$blog_count = count( $set_blog_list );
		foreach ( $set_blog_list as $blogs ) {
			$blog_id = $blogs->blog_id;
			switch_to_blog( $blog_id );
			$sql .= ("SELECT *, $blog_id as blog_id FROM $wpdb->posts WHERE post_type = 'post' AND post_status = 'publish'");
			if ( $count != $blog_count ) {
				$sql .= ' UNION' . "\n";
			}
			restore_current_blog();
			$count++;
		}
		$sql .= " ORDER BY post_date DESC $limits";
		$posts = $wpdb->get_results( $sql, OBJECT );
		return $posts;
	}

	public function get_blog_list(){
		global $wpdb;

		if ( $this->main_include != 1 )
			$query = "SELECT * FROM {$wpdb->blogs} WHERE public = '1' AND blog_id != 1 ";
		else
			$query = "SELECT * FROM {$wpdb->blogs} WHERE public = '1' ";

		$set_blog_list = $wpdb->get_results( $query );
		return $set_blog_list;
	}

	public function get_oni_daiko_search_form($echo = true) {
		do_action( 'get_oni_daiko_search_form' );

		$search_form_template = locate_template('oni-daiko-searchform.php');
		if ( '' != $search_form_template ) {
			require($search_form_template);
			return;
		}

		$form = '<form role="search" method="get" id="oni-daiko-searchform" action="' . esc_url( home_url( $this->slug . '/' ) ) . '" >
		<div><label class="screen-reader-text" for="s">' . __('Multisite Search for:', 'oni-daiko') . '</label>
		<input type="text" value="' . get_search_query() . '" name="s" id="s" />
		<input type="submit" id="oni-daiko-searchsubmit" value="'. esc_attr__('Search') .'" />
		</div>
		</form>';

		if ( $echo )
			echo apply_filters('get_oni_daiko_search_form', $form);
		else
			return apply_filters('get_oni_daiko_search_form', $form);
	}

	function custom_feed_rdf() {
		$template_file = '/feed-rdf.php';
		if( get_query_var( $this->slug ) && is_feed() ) {
			$template_file = ONI_DAIKO_DIR . '/template' . $template_file;
		} else {
			$template_file = ABSPATH . WPINC . $template_file;
		}
		load_template( $template_file );
	}
	
	function custom_feed_rss() {
		$template_file = '/feed-rss.php';
		if( get_query_var( $this->slug ) == $this->slug ) {
			$template_file = ONI_DAIKO_DIR . '/template' . $template_file;
		} else {
			$template_file = ABSPATH . WPINC . $template_file;
		}
		load_template( $template_file );
	}
	
	function custom_feed_rss2( $for_comments ) {
		$template_file = '/feed-rss2' . ( $for_comments ? '-comments' : '' ) . '.php';
		if( get_query_var( $this->slug ) == $this->slug ) {
			$template_file = ONI_DAIKO_DIR . '/template' . $template_file;
		} else {
			$template_file = ABSPATH . WPINC . $template_file;
		}
		load_template( $template_file );
	}

	function custom_feed_atom( $for_comments ) {
		$template_file = '/feed-atom' . ( $for_comments ? '-comments' : '' ) . '.php';
		if( get_query_var( $this->slug ) && is_feed() ) {
			$template_file = ONI_DAIKO_DIR . '/template' . $template_file;
		} else {
			$template_file = ABSPATH . WPINC . $template_file;
		}
		load_template( $template_file );
	}

} // end of class

new OniDaiko();
new OniDaikoAdmin();

