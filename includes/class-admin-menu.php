<?php
class OniDaikoAdmin {
	const OPTION_PAGE = 'oni-daiko';
	const OPTION_GROUP = 'oni-daiko';

	private $current_blog_id;
	private $plugin_basename;
	private $plugin_dir_path;
	private $plugin_dir_url;
	
	public function __construct() {
		$this->current_blog_id = get_current_blog_id();
		$this->plugin_basename = OniDaiko::plugin_basename();
		$this->plugin_dir_path = OniDaiko::plugin_dir_path();
		$this->plugin_dir_url = OniDaiko::plugin_dir_url();
		$this->main_include = get_option( 'oni-daiko-main-include', 1 );
		$this->slug = get_option( 'oni-daiko-slug', 'oni-daiko' );

		if ( $this->current_blog_id == 1 ) {
			add_action( 'admin_menu', array( &$this, 'admin_menu' ) );
			add_action( 'admin_init', array( &$this, 'add_general_custom_fields' ) );
			add_filter( 'admin_init', array( &$this, 'add_custom_whitelist_options_fields' ) );
			add_action( 'admin_print_styles', array( &$this, 'admin_styles' ) );
			if ( isset( $_GET['page'], $_GET['settings-updated'] ) && $_GET['page'] == self::OPTION_PAGE && $_GET['settings-updated'] == 'true' )
				add_action( 'admin_init', array( $this, 'flush_rules' ) );
		}
	}
	public function admin_menu() {
		add_menu_page( __( 'Oni Daiko', OniDaiko::TEXT_DOMAIN ), __( 'Oni Daiko', OniDaiko::TEXT_DOMAIN ), 'manage_network', self::OPTION_PAGE, array( &$this, 'add_admin_edit_page' ), $this->plugin_dir_url . '/admin/images/menu-icon.gif' );
	}
	
	public function add_admin_edit_page() {
		$title = __( 'Set Oni Daiko', OniDaiko::TEXT_DOMAIN ); ?>
		<div class="wrap">
		<?php screen_icon(); ?>
		<h2><?php echo esc_html( $title ); ?></h2>
		<form method="post" action="options.php">
		<?php settings_fields( self::OPTION_GROUP ); ?>
		<?php do_settings_sections( self::OPTION_PAGE ); ?>
		<input type="hidden" name="refresh">
		<table class="form-table">
		<?php do_settings_fields( self::OPTION_PAGE, 'default' ); ?>
		</table>
		<?php submit_button(); ?>
		</form>
		</div>
	<?php }

	public function add_general_custom_fields() {
		global $add_settings_field;
		add_settings_field( 'oni-daiko-main-include', __( 'Main sites include list.', OniDaiko::TEXT_DOMAIN ), array( &$this, 'onid_check_box' ), self::OPTION_PAGE, 'default', array( 'name' => 'oni-daiko-main-include', 'value' => $this->main_include, 'note' => 'Enabling' ) );
		add_settings_field( 'oni-daiko-slug', __( 'Oni Daiko slug setting', OniDaiko::TEXT_DOMAIN ), array( &$this, 'onid_text_field' ), self::OPTION_PAGE, 'default', array( 'name' => 'oni-daiko-slug', 'value' => $this->slug ) );
	}

	public function onid_check_box( $args ) {
		extract( $args );
		$output = '<label><input type="checkbox" name="' . $args['name'] .'" id="' . $args['name'] .'" value="1"' . checked( 1, $args['value'], false ). ' />' . esc_html__( $args['note'], OniDaiko::TEXT_DOMAIN ) . '</label>' ."\n";
		echo $output;
	}

	public function onid_text_field( $args ) {
		extract( $args );
		$output = '<label><input type="text" name="' . $args['name'] .'" id="' . $args['name'] .'" value="' . $args['value'] .'" /></label>' ."\n";
		echo $output;
	}

	public function add_custom_whitelist_options_fields() {
		register_setting( self::OPTION_PAGE, 'oni-daiko-main-include', 'intval' );
		register_setting( self::OPTION_PAGE, 'oni-daiko-slug' );
	}

	public function admin_styles() {
		wp_enqueue_style( 'admin-oni-daiko-style', $this->plugin_dir_url . '/admin/css/style.css' );
	}
	public function flush_rules() {
		global $wp_rewrite;
		check_admin_referer();
		$wp_rewrite->flush_rules( false );
	}

}
