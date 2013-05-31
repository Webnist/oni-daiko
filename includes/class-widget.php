<?php
add_action( 'widgets_init', 'wp_onidaiko_widgets_init', 1 );
function wp_onidaiko_widgets_init() {
	register_widget( 'WP_Widget_Onidaiko_Recent_Posts' );
}
$OniDaiko = new OniDaiko();
class WP_Widget_Onidaiko_Recent_Posts extends WP_Widget {

	function __construct() {
		$widget_ops = array('classname' => 'widget_oni_daiko_recent_entries', 'description' => __( 'Latest posts of a multisite', 'oni-daiko') );
		parent::__construct('oni-daiko-recent-posts', __('Multisite Recent Posts', 'oni-daiko'), $widget_ops);
		$this->alt_option_name = 'widget_oni_daiko_recent_entries';

		add_action( 'save_post', array($this, 'flush_widget_cache') );
		add_action( 'deleted_post', array($this, 'flush_widget_cache') );
		add_action( 'switch_theme', array($this, 'flush_widget_cache') );
	}

	function widget($args, $instance) {
		global $wpdb, $OniDaiko;
		$cache = wp_cache_get('widget_oni_daiko_recent_posts', 'widget');

		if ( !is_array($cache) )
			$cache = array();

		if ( ! isset( $args['widget_id'] ) )
			$args['widget_id'] = $this->id;

		if ( isset( $cache[ $args['widget_id'] ] ) ) {
			echo $cache[ $args['widget_id'] ];
			return;
		}

		ob_start();
		extract($args);

		$title = apply_filters('widget_title', empty($instance['title']) ? __('Multisite Recent Posts') : $instance['title'], $instance, $this->id_base);
		if ( empty( $instance['number'] ) || ! $number = absint( $instance['number'] ) )
 			$number = 10;
		$posts = $OniDaiko->get_oni_posts($number);
		if ( $posts ) {
			echo $before_widget;
			if ( $title )
				echo $before_title . $title . $after_title;

			echo '<ul>';
			foreach ( $posts as $post ) {
				setup_postdata( $post );
				$id = $post->ID;
				$blog_id = $post->blog_id;
				switch_to_blog( $post->blog_id );
				$link = get_permalink( $id );
				$title = get_the_title( $id );
				echo '<li>';
				echo '<a href="' . $link . '">' . $title . '</a>';
				echo '</li>';
				restore_current_blog();
			}
			echo '</ul>';
			echo $after_widget;
			// Reset the global $the_post as this query will have stomped on it
			wp_reset_postdata();
		}

		$cache[$args['widget_id']] = ob_get_flush();
		wp_cache_set('widget_oni_daiko_recent_posts', $cache, 'widget');
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['number'] = (int) $new_instance['number'];
		$this->flush_widget_cache();

		$alloptions = wp_cache_get( 'alloptions', 'options' );
		if ( isset($alloptions['widget_oni_daiko_recent_entries']) )
			delete_option('widget_oni_daiko_recent_entries');

		return $instance;
	}

	function flush_widget_cache() {
		wp_cache_delete('widget_oni_daiko_recent_posts', 'widget');
	}

	function form( $instance ) {
		$title     = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
		$number    = isset( $instance['number'] ) ? absint( $instance['number'] ) : 5;
?>
		<p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" /></p>

		<p><label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php _e( 'Number of posts to show:' ); ?></label>
		<input id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" type="text" value="<?php echo $number; ?>" size="3" /></p>

<?php
	}
}
