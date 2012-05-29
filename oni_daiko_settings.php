<?php function oni_daiko_setting_menu() {
global $plugin_page; ?>
<div id="icon-options-oni_daiko" class="icon32"><br /></div>
<h2><?php _e('Oni Daiko Settings', 'oni_daiko'); ?></h2>
<div id="oni_daiko_wrap">
	<form method="post" action="<?php echo esc_attr($_SERVER['REQUEST_URI']); ?>">
		<h3>
			<?php echo oni_daiko_text_limit(); ?><?php _e('Base Setting','oni_daiko'); ?>
		</h3>
		<p>
			<label for="oni_daiko_title">
			<?php _e('Title','oni_daiko'); ?>
			<br />
			<input name="oni_daiko_title" type="text" id="oni_daiko_title" value="<?php echo oni_daiko_title(); ?>" />
			</label>
		</p>
		<p>
			<label for="oni_daiko_limit">
			<?php _e('How many do you display it?','oni_daiko'); ?>
			<br />
			<select id="oni_daiko_limit" name="oni_daiko_limit">
				<?php for ( $i = 1; $i <= 20; ++$i ) 
				echo "<option value='$i' " . ( oni_daiko_limit() == $i ? "selected='selected'" : '' ) . ">$i</option>"; ?>
			</select>
			</label>
		</p>
		<p>
			<?php _e('Contents Display','oni_daiko'); ?>
			<br />
			<label for="oni_daiko_contents_0">
			<input name="oni_daiko_contents" type="radio" id="oni_daiko_contents_0" value="0"<?php if(oni_daiko_contents() == 0) { ?> checked="checked"<?php } ?> />
			<?php _e(':Display','oni_daiko'); ?>
			</label>
			<br />
			<label for="oni_daiko_contents_1">
			<input name="oni_daiko_contents" type="radio" id="oni_daiko_contents_1" value="1"<?php if(oni_daiko_contents() == 1) { ?> checked="checked"<?php } ?> />
			<?php _e(':Hidden','oni_daiko'); ?>
			</label>
		</p>
		<p>
			<label for="oni_daiko_text_limit">
			<?php _e('Text Limit','oni_daiko'); ?>
			<br />
			<input name="oni_daiko_text_limit" type="text" id="oni_daiko_text_limit" value="<?php echo oni_daiko_text_limit(); ?>" /> <?php _e('If there is no excerpt is cut off by the specified number of characters.','oni_daiko'); ?>
			</label>
		</p>
		<h3>
			<?php _e('Template Tag','oni_daiko'); ?>
		</h3>
		<p><?php _e('If you stuck to the theme, please copy and paste the following attached.','oni_daiko'); ?></p>
		<input type="text" id="oni_daiko_tag" onfocus="this.select();" value="&lt;?php echo oni_daiko_template_tag(); ?&gt;" readonly="readonly" />
		<p class="submit">
			<input type="submit" value="<?php _e('Save Changes &raquo;','oni_daiko'); ?>" />
		</p>
		<input type="hidden" name="action" value="save" />
	</form>
</div>
<?php }
add_action( 'init', 'update_mu_setting');
