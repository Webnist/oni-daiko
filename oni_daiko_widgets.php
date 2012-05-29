<?php
class Oni_daiko_Widget_NewPost extends WP_Widget {
	function widget($args, $instance) {		
		extract( $args );
		if(!$instance['title']) {
			$title = __('New Post','oni_daiko');
		} else {
				$title = esc_attr($instance['title']);
		}
		if(!$get_limit) {
			$limit = '10';
		} else {
			$limit = (int)$instance['limit'];
		}
		if(!$get_contents) {
			$contents = '0';
		} else {
			$contents = (int)$instance['contents'];
		}
		if(!$get_text_limit) {
			$text_limit = '100';
		} else {
			$text_limit = (int)$instance['text_limit'];
		}
		echo $before_widget;
		echo get_oni_daiko_post('title=' . $title . '&limit=' . $limit . '&contents=' . $contents . '&characters=' . $text_limit . '&case=page');
		echo $after_widget;
	}

	function update($new_instance, $old_instance) {				
		return $new_instance;
	}

	function form($instance) {				
		if(!$instance['title']) {
			$title = __('New Post','oni_daiko');
		} else {
				$title = esc_attr($instance['title']);
		}
		if(!$get_limit) {
			$limit = '10';
		} else {
			$limit = (int)$instance['limit'];
		}
		if(!$get_contents) {
			$contents = '0';
		} else {
			$contents = (int)$instance['contents'];
		}
		if(!$get_text_limit) {
			$text_limit = '100';
		} else {
			$text_limit = (int)$instance['text_limit'];
		} ?>
		<p>
            <label for="<?php echo $this->get_field_id('title'); ?>">
				<?php _e('Title','oni_daiko'); ?>
                <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
            </label>
		</p>
		<p>
            <label for="<?php echo $this->get_field_id('limit'); ?>">
				<?php _e('Number of posts to show:', 'oni_daiko'); ?>
                <select id="<?php echo $this->get_field_id('limit'); ?>" name="<?php echo $this->get_field_name('limit'); ?>">
                <?php
                for ( $i = 1; $i <= 20; ++$i )
                echo "<option value='$i' " . ( $limit == $i ? "selected='selected'" : '' ) . ">$i</option>";
                ?>
                </select>
            </label>
		</p>
		<p>
			<?php _e('Contents Display','oni_daiko'); ?>
            <br />
            <label for="<?php echo $this->get_field_id('contents_0'); ?>">
                <input name="<?php echo $this->get_field_name('contents'); ?>" type="radio" id="<?php echo $this->get_field_id('contents_0'); ?>" value="0"<?php if($contents == 0) { ?> checked="checked"<?php } ?> />
                <?php _e(':Display','oni_daiko'); ?>
            </label>
            <br />
            <label for="<?php echo $this->get_field_id('contents_1'); ?>">
                <input name="<?php echo $this->get_field_name('contents'); ?>" type="radio" id="<?php echo $this->get_field_id('contents_1'); ?>" value="1"<?php if($contents == 1) { ?> checked="checked"<?php } ?> />
                <?php _e(':Hidden','oni_daiko'); ?>
            </label>
		</p>
		<p>
            <label for="<?php echo $this->get_field_id('text_limit'); ?>">
				<?php _e( 'Text Limit:', 'oni_daiko' ); ?>
                <input class="widefat" id="<?php echo $this->get_field_id('text_limit'); ?>" name="<?php echo $this->get_field_name('text_limit'); ?>" type="text" value="<?php echo $text_limit; ?>" />
            </label>
		</p>
		<?php 
	}

	function Oni_daiko_Widget_NewPost() {
		$widget_ops = array('classname' => 'oni_daiko_post_list', 'description' => __( 'The most recent posts on your blog', 'oni_daiko' ) );
		$this->WP_Widget('oni_daiko_post_list', __( 'Oni Daiko Post List', 'oni_daiko' ), $widget_ops);
	}
}
add_action('widgets_init', create_function('', 'return register_widget("Oni_daiko_Widget_NewPost");'));
