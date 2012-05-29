<?php
function update_mu_setting() {
	if (isset($_POST['oni_daiko_title'])) {
		$oni_daiko_title = trim(strip_tags(stripslashes($_POST['oni_daiko_title'])));
		update_option('oni_daiko_title', $oni_daiko_title);
	}
	if (isset($_POST['oni_daiko_limit'])) {
		$oni_daiko_limit = (int)$_POST['oni_daiko_limit'];
		update_option('oni_daiko_limit', $oni_daiko_limit);
	}
	if (isset($_POST['oni_daiko_contents'])) {
		$oni_daiko_contents = (int)$_POST['oni_daiko_contents'];
		update_option('oni_daiko_contents', $oni_daiko_contents);
	}
	if (isset($_POST['oni_daiko_text_limit'])) {
		$oni_daiko_text_limit = (int)$_POST['oni_daiko_text_limit'];
		update_option('oni_daiko_text_limit', $oni_daiko_text_limit);
	}
} ?>