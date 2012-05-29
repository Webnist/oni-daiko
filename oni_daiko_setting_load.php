<?php
function oni_daiko_title() {
	if(get_option('oni_daiko_title')) {
		$value = apply_filters('oni_daiko_title', get_option('oni_daiko_title'));
	} else {
		$value = __('New Post','oni_daiko');
	}
	return $value;
}
function oni_daiko_limit() {
	if(get_option('oni_daiko_limit')) {
		$value = apply_filters('oni_daiko_limit', get_option('oni_daiko_limit'));
	} else {
		$value = 10;
	}
	return $value;
}
function oni_daiko_contents() {
	if(get_option('oni_daiko_contents')) {
		$value = apply_filters('oni_daiko_contents', get_option('oni_daiko_contents'));
	} else {
		$value = 0;
	}
	return $value;
}
function oni_daiko_text_limit() {
	if(get_option('oni_daiko_text_limit')) {
		$value = apply_filters('oni_daiko_text_limit', get_option('oni_daiko_text_limit'));
	} else {
		$value = 100;
	}
	return $value;
} ?>