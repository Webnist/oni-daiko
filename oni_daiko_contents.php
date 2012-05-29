<?php
function oni_daiko_short_contents($get_contents, $characters) {
	global $shortcode_tags;
	if (empty($characters)) {
		$get_characters = 100;
	} else {
		$get_characters	= $characters;
	}
	$get_content = strip_tags($get_contents);
	if ( !empty($shortcode_tags) && is_array($shortcode_tags) ) {
		$tagnames = array_keys($shortcode_tags);
		$tagregexp = join( '|', array_map('preg_quote', $tagnames) );
		$get_content = preg_replace('/\[(' . $tagregexp . ')\\b.*?\\/?\\]/s', '', $get_content);
		$get_content = preg_replace('/\[\/(' . $tagregexp . ')\\b.*?\\/?\\]/s', '', $get_content);
	}
	if (strlen($get_content) == mb_strlen($get_content, 'UTF-8')) {
		$content_short = substr($get_content, 0, $get_characters);
	} else {
		$content_short = mb_substr($get_content, 0, $get_characters + 1, 'UTF-8');
	}
	$output = $content_short."\n";
	return $output;
}
function get_feed_url() {
	if ( get_option( 'permalink_structure' ) ) {
		$output = home_url( '/oni-feed' );
	} else {
		$output = home_url( '/wp-content/plugins/oni-daiko/oni_daiko_feed.php' );
	}
	return $output;
}
function feed_url() {
	if ( get_option( 'permalink_structure' ) ) {
		$output = home_url( '/oni-feed' );
	} else {
		$output = home_url( '/wp-content/plugins/oni-daiko/oni_daiko_feed.php' );
	}
	echo $output;
}
/*
function oni_daiko_template_tag( $case = 'page' ) {
	$output = get_oni_daiko_post('title=' . oni_daiko_title() . '&limit=' . oni_daiko_limit() . '&contents=' .oni_daiko_contents(). '&characters=' . oni_daiko_text_limit() . '&case=' . $case);
	return $output;
}
*/
function get_last_updated_time() {
	global $wpdb;
	return $wpdb->get_results( $wpdb->prepare("SELECT last_updated FROM $wpdb->blogs WHERE public = '1' AND last_updated != '0000-00-00 00:00:00' ORDER BY last_updated DESC limit 1", $wpdb->siteid, $start, $quantity ) , OBJECT );
}
function get_oni_daiko_posts( $args = '' ) {
	global $wpdb;
    $defaults = array(
        'title' => __('New Post','oni_daiko'),
        'limit' => '10',
        'contents' => '0',
        'characters' => '100',
        'case' => 'page',
    );
	$r = wp_parse_args($args, $defaults);
    $get_title = $r['title'];
    $limit = $r['limit'];
    $get_contents = $r['contents'];
    $characters = $r['characters'];
    $case = $r['case'];
	$date_format = get_option('date_format');
	$query = "SELECT * FROM {$wpdb->blogs} WHERE blog_id != '1' AND site_id = '{$wpdb->siteid}' AND public = '1' ";
	$set_blog_list = $wpdb->get_results( $query, ARRAY_A );
	$sql = '';
	$count = '';
	$blog_count = count( $set_blog_list );
	foreach ($set_blog_list as $oni_daiko_updated) {
		$count++;
		$blog_list = $oni_daiko_updated['blog_id'];
		switch_to_blog($oni_daiko_updated['blog_id']);
		$sql .= ("SELECT *, $blog_list as blog_id FROM $wpdb->posts WHERE post_type = 'post' AND post_status = 'publish'");
		if ( $count != $blog_count ) {
			$sql .= 'UNION' . "\n";
		}
		restore_current_blog();
	}
	$sql .= " ORDER BY post_date DESC LIMIT $limit";
	$posts = $wpdb->get_results( $sql, OBJECT );
	return $posts;
}
function get_oni_daiko_post( $args = '' ) {
	global $wpdb;
    $defaults = array(
        'title' => __('New Post','oni_daiko'),
        'limit' => '10',
        'contents' => '0',
        'characters' => '100',
        'case' => 'page',
    );
	$r = wp_parse_args($args, $defaults);
    $get_title = $r['title'];
    $limit = $r['limit'];
    $get_contents = $r['contents'];
    $characters = $r['characters'];
    $case = $r['case'];
	$date_format = get_option('date_format');
	$feed_url = '/oni-feed';
	$query = "SELECT * FROM {$wpdb->blogs} WHERE site_id = '{$wpdb->siteid}' ";
	$set_blog_list = $wpdb->get_results( $query, ARRAY_A );
	foreach ($set_blog_list as $oni_daiko_updated) {
		$blog_list = $oni_daiko_updated['blog_id'];
		switch_to_blog($blog_list);
		if ($blog_list != 1) {
			$posts = get_posts('numberposts='.$limit);
			$blog_id = $blog_list;
			foreach($posts as $post) {
				setup_postdata($post);
				$post_id = $post->ID;
				$get_post_time = $post->post_date;
				$unix_time = strtotime($get_post_time);
				$post_time = date($date_format,$unix_time);
				$post_author = get_the_author();
				$post_title = $post->post_title;
				$post_content = get_the_content();
				$post_content = apply_filters('the_content', $post_content);
				if ( get_post_thumbnail_id( $post_id ) )
					$post_thumb = wp_get_attachment_image( get_post_thumbnail_id( $post_id ), 'thumbnail' );
				$post_short_content = oni_daiko_short_contents($post_content, $characters);
				$oni_daiko_array = array("blog_id" => $blog_id, "ID" => $post_id, "unix_time" => $unix_time, "post_time" => $post_time, "post_author" => $post_author, "post_title" => $post_title, "post_excerpt" => $post_short_content, "post_thumb" => $post_thumb);
				$post_arr[$unix_time] = $oni_daiko_array;
			}
		}
		restore_current_blog();
	}
	krsort($post_arr);
	$post_items = array_slice($post_arr, 0, $limit);
	switch ($case) {
			case 'page':
				$output = '<div class="oni_daiko_post">'."\n";
				$output .= '<h2>'.$get_title.'<span><a href="'.$feed_url.'">'.__('[RSS]','oni_daiko').'</a></span></h2>'."\n";
				foreach ($post_items as $post_item) {
					add_filter('excerpt_mblength', 'oni_daiko_excerpt');
					$blog_name = get_blog_option($post_item['blog_id'],'blogname');
					$blog_link = get_blog_option($post_item['blog_id'],'siteurl');
					$get_permalink = get_blog_permalink($post_item['blog_id'], $post_item['ID']);
					$content = strip_tags($post_item['post_content']);
					$post_excerpt = $post_item['post_excerpt'];
					$output .= '<h3><a href="'.$get_permalink.'" title="'.sprintf(__('Permanent Link to %s','oni_daiko'), $post_item['post_title']).'">'.$post_item['post_title'].'</a></h3>'."\n";
					$output .= '<p class="data">'.$post_item['post_time'].'</p>'."\n";
					$output .= '<p class="data">'.$post_item['post_thumb'].'</p>'."\n";
					if($get_contents == 0) {
						if($post_item['post_excerpt']){
							$output .= '<p>'."\n";
							$output .= $post_item['post_excerpt'];
							$output .= '</p>'."\n";
							$output .= '<p class="go_more">'."\n";
							$output .= '<a href="'.$get_permalink.'" title="'.sprintf(__('Permanent Link to %s','oni_daiko'), $post_item['post_title']).'">';
							$output .= __('Read the rest of this entry &nbsp;&raquo;', 'oni_daiko');
							$output .= '</a>';
							$output .= '</p>'."\n";
						} else {
							if($content > $short_content){
								$output .= '<p>'."\n";
								$output .= $short_content.'...'."\n";
								$output .= '</p>'."\n";
								$output .= '<p class="go_more">'."\n";
								$output .= '<a href="'.$get_permalink.'" title="'.sprintf(__('Permanent Link to %s','oni_daiko'), $post_item['post_title']).'">';
								$output .= __('Read the rest of this entry &nbsp;&raquo;', 'oni_daiko');
								$output .= '</a>';
								$output .= '</p>'."\n";
							} else {
								$output .= '<p>'."\n";
								$output .= $short_content."\n";
								$output .= '</p>'."\n";
							}
						}
					if ($post_item['post_thumb']) 
						$output .= '<p class="thumb"><a href="'.$get_permalink.'" title="'.sprintf(__('Permanent Link to %s','oni_daiko'), $post_item['post_title']).'">'.$post_item['post_thumb'].'</a></p>'."\n";
					}
					$output .= '<p class="blog_name">(<a href="'.$blog_link.'" title="'.sprintf(__('Permanent Link to %s','oni_daiko'), $blog_name).'">'.$blog_name.'</a>)</p>'."\n";
				}
				$output .= '</div>'."\n";
				break;
			case 'feed':
				foreach ($post_items as $post_item) {
					$get_permalink = get_blog_permalink($post_item['blog_id'], $post_item['ID']);
					$output .= '<item>'."\n";
					$output .= "\t\t\t".'<title>'.$post_item['post_title'].'</title>'."\n";
					$output .= "\t\t\t".'<link>'.$get_permalink.'</link>'."\n";
					$output .= "\t\t\t".'<comments>'.$post_item['post_permalink'].'#comments</comments>'."\n";
					$output .= "\t\t\t".'<pubDate>'.$post_item['post_time'].'</pubDate>'."\n";
					$output .= "\t\t\t".'<dc:creator>'.$post_item['post_author'].'</dc:creator>'."\n";
					$output .= "\t\t\t".'<guid isPermaLink="false">'.$post_item['post_guid'].'</guid>'."\n";
					$output .= "\t\t\t".'<description><![CDATA['.$post_item['post_content'].']]></description>'."\n";
					$output .= "\t\t\t".'<content:encoded><![CDATA['.$post_item['post_content'].']]></content:encoded>'."\n";
					$output .= "\t\t\t".'<wfw:commentRss>'.$post_item['post_permalink'].'feed/</wfw:commentRss>'."\n";
					$output .= "\t\t\t".'<slash:comments>'.$post_item['post_comment_count'].'</slash:comments>'."\n";
					$output .= "\t\t\t".'</item>'."\n";
				}
				break;
	}
	return $output;
}
