<?php
/*
Plugin Name: Oni Daiko
Plugin URI: http://plugins.webnist.net/
Description: Shows a list of the latest posts from each blogs on a multisaite blog.
Version: 0.7.1.0
Author: Webnist
Author URI: http://www.webnist.net
License: GPLv2 or later
Network: true
*/

if ( !defined( 'ONI_DAIKO_DIR' ) )
	define( 'ONI_DAIKO_DIR', WP_PLUGIN_DIR . '/oni-daiko' );

if ( !defined( 'ONI_DAIKO_URL' ) )
	define( 'ONI_DAIKO_URL', WP_PLUGIN_URL . '/oni-daiko' );

new OniDaiko();

class OniDaiko {

	private $version = '0.7.1.0';
	private $plugin_dir;
	private $plugin_url;
	private $lang_path;
	private $main_include = 1;
	private $domain = 'oni-daiko';
	private $slug = 'oni-daiko';
	private $current_blog_id;

	public function __construct() {
		$this->plugin_dir = WP_PLUGIN_DIR . '/oni-daiko';
		$this->plugin_url = WP_PLUGIN_URL . '/oni-daiko';
		$this->lang_path = $this->plugin_dir . '/languages';

		load_plugin_textdomain( $this->domain, false, $this->lang_path );
		$this->current_blog_id = get_current_blog_id();
		$this->main_include = get_option( 'oni-daiko-main-include', 1 );
		$this->slug = get_option( 'oni-daiko-slug', 'oni-daiko' );
		if ( $this->current_blog_id == 1 ) {
			register_activation_hook( __FILE__, array( &$this, 'flush_rewrite_rules', 'add_option' ) );
			register_deactivation_hook( __FILE__, array( &$this, 'flush_rewrite_rules', 'delete_option' ) );
			register_uninstall_hook( __FILE__, array( &$this, 'flush_rewrite_rules', 'delete_option' ) );
	
			add_filter( 'init', array( &$this, 'add_rewrite_rule' ) );

			add_filter( 'query_vars',  array( &$this, 'add_query_var' ) );
			
			add_filter( 'posts_request', array( &$this, 'add_posts_request' ), 10, 2 );
			
			add_action( 'pre_get_posts', array( &$this, 'add_pre_get_posts' ) );
	
			add_action( 'admin_menu', array( &$this, 'admin_menu' ) );
			add_action( 'admin_init', array( &$this, 'add_general_custom_fields' ) );
			add_filter( 'admin_init', array( &$this, 'add_custom_whitelist_options_fields' ) );
			add_action( 'admin_print_styles', array( &$this, 'admin_styles' ) );
		}
	}

	public function flush_rewrite_rules( $hard = true ) {
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

	public function get_blog_list(){
		global $wpdb;

		if ( $this->main_include != 1 )
			$query = "SELECT * FROM {$wpdb->blogs} WHERE public = '1' AND blog_id != 1 ";
		else
			$query = "SELECT * FROM {$wpdb->blogs} WHERE public = '1' ";

		$set_blog_list = $wpdb->get_results( $query );
		return $set_blog_list;
	}

	public function admin_menu() {
		add_menu_page( __( 'Oni Daiko', $this->domain ), __( 'Oni Daiko', $this->domain ), 'manage_network', $this->domain, array( &$this, 'add_admin_edit_page' ), $this->plugin_url . '/admin/images/menu-icon.gif' );
	}
	
	public function add_admin_edit_page() {
		$title = __( 'Set Oni Daiko', $this->domain ); ?>
		<div class="wrap">
		<?php screen_icon(); ?>
		<h2><?php echo esc_html( $title ); ?></h2>
		<form method="post" action="options.php">
		<?php settings_fields( $this->domain ); ?>
		<?php do_settings_sections( $this->domain ); ?>
		<input type="hidden" name="refresh">
		<table class="form-table">
		<?php do_settings_fields( $this->domain, 'default' ); ?>
		</table>
		<?php submit_button(); ?>
		</form>
		</div>
	<?php }


	public function add_general_custom_fields() {
		add_settings_field( 'oni-daiko-main-include', __( 'Main sites include list.', $this->domain ), array( &$this, 'onid_check_box' ), $this->domain, 'default', array( 'name' => 'oni-daiko-main-include', 'value' => $this->main_include, 'note' => 'Enabling' ) );
		add_settings_field( 'oni-daiko-slug', __( 'Oni Daiko slug setting', $this->domain ), array( &$this, 'onid_text_field' ), $this->domain, 'default', array( 'name' => 'oni-daiko-slug', 'value' => $this->slug ) );
	}

	public function onid_check_box( $args ) {
		extract( $args );
		$output = '<label><input type="checkbox" name="' . $args['name'] .'" id="' . $args['name'] .'" value="1"' . checked( 1, $args['value'], false ). ' />' . esc_html__( $args['note'], $this->domain ) . '</label>' ."\n";
		echo $output;
	}

	public function onid_text_field( $args ) {
		extract( $args );
		$output = '<label><input type="text" name="' . $args['name'] .'" id="' . $args['name'] .'" value="' . $args['value'] .'" /></label>' ."\n";
		echo $output;
	}

	public function add_custom_whitelist_options_fields() {
		register_setting( $this->domain, 'oni-daiko-main-include', 'intval' );
		register_setting( $this->domain, 'oni-daiko-slug' );
	}

	public function admin_styles() {
		wp_enqueue_style( 'admin-oni-daiko-style', $this->plugin_url . '/admin/css/style.css' );
	}

} // end of class
