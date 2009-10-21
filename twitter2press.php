<?php

/*
Plugin Name: Twitter2Press
Plugin URI: http://projets.lesniak.fr/twitter2press
Description: Twitter2Press is the Wordpress Plug-In that allow you to post your Twitter Images to your WordPress by using Tweetie client
Version: 1.0.5
Author: Mathieu LESNIAK
Author URI: http://www.lesniak.fr/

Copyright 2009  Mathieu LESNIAK  (email : maverick@eskuel.net)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/



if ( !defined('T2P_ADMIN_PER_PAGE') ) {
	define('T2P_ADMIN_PER_PAGE', 20 );
}


$data = array(
	'page_id'					=> 0,
	'setup'						=> 0,
	'twitter_login'				=> '',
	'twitter_password'			=> '',
	'url_shortener'				=> '',
	'url_shortener_login' 		=> '',
	'url_shortener_password' 	=> '',
	'url_shortener_endpoint'	=> '', 
	'nb_pic_per_page'			=> 5
	);

$shortening_services = array(
							 'trim' 	=> array('key' => 0, 'name' => 'Tr.im'),
							 'isgd'		=> array('key' => 0, 'name' => 'Is.gd'),
							 'tinyurl' 	=> array('key' => 0, 'name' => 'Tiny URL'),
							 'bitly' 	=> array('key' => 0, 'name' => 'Bit.ly'),
							 'jmp'		=> array('key' => 0, 'name' => 'j.mp'),
							 'yourls' 	=> array('key' => 1, 'name' => 'Yourls private URL shortener')
							);
add_option('t2p_settings',$data,'Twitter2Press Options');

$t2p_settings = get_option('t2p_settings');


/* admin menu */
add_action('admin_menu', 'twitter2press_menu');
function twitter2press_menu() {
	add_options_page('Twitter2Press Options', 'Twitter2Press', 8, __FILE__, 'twitter2press_options');
}

function twitter2press_options() {
	Global $t2p_settings, $shortening_services, $wpdb;
	
	echo '<script type="text/javascript">';
	echo '	var shortening_services = [];';
	while ( list($key, $val) = each($shortening_services) ) {
		echo 'shortening_services["'.$key.'"] = \''.$val['key'].'\';';
	}	

	echo '	function check_key(elt) {';
	echo '		if (shortening_services[elt.options[elt.selectedIndex].value] == 1 ) {';
	echo '			document.getElementById(\'shortencred\').style.display = \'block\';';
	echo '		}';
	echo '		else {';
	echo '			document.getElementById(\'shortencred\').style.display = \'none\';';
	echo '		}';
	echo '	}';
	echo '	function do_confirm() {';
	echo '		result = confirm(\'Are you sure you want to delete this picture ?\');';
	echo '		return result;';
	echo '	}';
	echo '</script>';
	
	echo '<form action="" method="post">';
	echo '<div class="wrap" id="t2p-options">';
	echo '	<h2>Twitter2Press Options page</h2>';
	
	
	
	
	
	// Migration
	if ( $t2p_settings['setup'] == 1 ) {
		echo '<h3>Migration from old Twitter2Press setup</h3>';
		$table = $wpdb->prefix . "twitter2press";
		$old_entries = $wpdb->get_results($wpdb->prepare("SELECT *, UNIX_TIMESTAMP(time) AS ts FROM $table"));
		foreach ( $old_entries as $old_entry ) {
			$new_id = t2p_migrate(ABSPATH . 'wp-content/twitter2press/'.$old_entry->name, $old_entry->twitt, $old_entry->time);
			if ( $new_id != -1 ) {
				$wpdb->query($wpdb->prepare("UPDATE $table SET name='%s' WHERE id='%d'", $new_id, $old_entry->id));
				
			}
		}
		echo '<u>Migration OK</u>';
		$t2p_settings['setup'] = 2;
		update_option('t2p_settings', $t2p_settings);
		
	}
	
	
	if ( $t2p_settings['setup'] == 0 ) {
		$t2p_settings['setup'] = 2;
		update_option('t2p_settings', $t2p_settings);
	}
	
	
	
	
	// Navigation offset
	if ( isset($_GET['offset']) && $_GET['offset'] != '' ) {
		$offset = $_GET['offset'];
	}
	else {
		$offset = 0;
	}
	
	if ( $t2p_settings['url_shortener'] == '' ) {
		$t2p_settings['url_shortener'] = 'tinyurl';
	}
	
	if ( $t2p_settings['nb_pic_per_page'] == '' ) {
		$t2p_settings['nb_pic_per_page'] = 5;
	}
	
	if ( isset($_POST['t2p_submit']) ) {
		$t2p_settings['twitter_login'] 			= $_POST['twitter_login'];
		$t2p_settings['twitter_password'] 		= $_POST['twitter_password'];
		$t2p_settings['page_id'] 				= $_POST['page_id'];
		$t2p_settings['url_shortener']			= $_POST['url_shortener'];
		$t2p_settings['url_shortener_login']	= $_POST['shortener_login'];
		$t2p_settings['url_shortener_password']	= $_POST['shortener_password'];
		$t2p_settings['url_shortener_endpoint']	= $_POST['shortener_endpoint'];
		$t2p_settings['nb_pic_per_page']		= $_POST['nb_pic_per_page'];
		update_option('t2p_settings', $t2p_settings);
	}
	
	
	
		
	$pages = get_pages(); 
		
	
	echo '	<h3>Settings</h3>';
	echo '	<table cellpadding="15" cellspacing="15">';
	echo '	<tr>';
	echo '		<td>Gallery page :</td>';
	echo '		<td>';
	echo '	<select name="page_id">
				<option value="0">Homepage</option>';
			foreach($pages as $page) {
				echo '<option value="'.$page->ID.'"';
				if($page->ID == $t2p_settings['page_id']) {
					echo ' selected="selected" ';
				}
				echo '>'.$page->post_title.'</option>';
			}	
	
	echo '				</select>';
	echo '		</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Number of thumbnails per page :</td>';
	echo '		<td><input type="text" name="nb_pic_per_page" value="'.$t2p_settings['nb_pic_per_page'].'" /></td>';
	echo '	</tr>';
	
	echo '	<tr>';
	echo '		<td><label for="twitter_login">Your twitter username :</label></td>';
	echo '		<td><input type="text" name="twitter_login" value="'.$t2p_settings['twitter_login'].'" /></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td><label for="twitter_password">Your twitter password :</label></td>';
	echo '		<td><input type="password" name="twitter_password" value="'.$t2p_settings['twitter_password'].'" /></td>';
	echo '	</tr>';
	echo ' <tr>';
	echo '		<td><label for="urlshortener">URL Shortener :</label></td>';
	echo '		<td><select name="url_shortener" onchange="check_key(this)">';
	reset($shortening_services);
	while ( list($key, $val) = each($shortening_services) ) {
		$selected = ($key == $t2p_settings['url_shortener']) ? 'selected' : '';
		echo '<option value="'.$key.'" '.$selected.'>'.$val['name'].'</option>';
	}
	echo '			</select></td>';
	echo '	</tr>';

	$style = $shortening_services[$t2p_settings['url_shortener']]['key'] ? 'style="display: block"' : 'style="display: none"';

	echo ' <tr style="padding-left: 50px;">';
	echo '		<td></td>';
	echo '		<td>';
	echo '			<table id="shortencred" '.$style.'>';
	echo '			<tr>';
	echo '				<td><label for="shortener_login">API key / login</label></td>';
	echo '				<td><input type="text" name="shortener_login" value="'.$t2p_settings['url_shortener_login'].'" /></td>';
	echo '			</tr>';
	echo '			<tr>';
	echo '				<td><label for="shortener_password">API password</label></td>';
	echo '				<td><input type="password" name="shortener_password" value="'.$t2p_settings['url_shortener_password'].'" /></td>';
	echo '			</tr>';
	echo '			<tr>';
	echo '				<td><label for="shortener_endpoint">API endpoint (http://yoursite/yourls-api.php)</label></td>';
	echo '				<td><input type="text" name="shortener_endpoint" value="'.$t2p_settings['url_shortener_endpoint'].'" /></td>';
	echo '			</tr>';
	echo '			</table>';
	echo '		</td>';
	echo ' </tr>';
	echo ' <tr>';
	echo '		<td colspan="2" align="center"><input type="submit" name="t2p_submit" value="Save settings" class="button-primary"/></td>';
	echo '	</tr>';
	
	echo '	</table>';
	echo '</form>';
	echo '<hr/>';
	
	
	
	// Thumbnails listing for edit / delete
	echo '<h3>Manage pictures</h3>';
	echo 'Click a picture to edit / delete it : <br/>';


	if ( isset($_GET['delete']) && $_GET['delete'] != '' ) {
		wp_delete_post($_GET['delete']);
	}

	$args = array(
				'post_type' 	=> 'attachment',
				'numberposts' 	=> -1,
				'post_status' 	=> null,
				'post_parent' 	=> $t2p_settings['page_id']
			
				); 
	$attachments = get_posts($args);
	$nb_records = count($attachments);
	$nb_pages 	= ceil($nb_records / T2P_ADMIN_PER_PAGE);
	$page_offset = ($offset / T2P_ADMIN_PER_PAGE) + 1;
	
	if ($attachments) {
		
		$attachments_split = array_slice($attachments, $offset, T2P_ADMIN_PER_PAGE);
		foreach ($attachments_split as $attachment) {
			echo '<div style="float:left; text-align:center; padding:5px"><a href="'.admin_url('media.php?action=edit&attachment_id='.$attachment->ID).'">'.wp_get_attachment_image($attachment->ID,  array(100, 100), false).'</a><br/><a href="'.admin_url('media.php?action=edit&attachment_id='.$attachment->ID).'">Edit</a> | <a href="'.admin_url( 'options-general.php?page=twitter2press/twitter2press.php&offset='.$offset.'&delete='.$attachment->ID ).'" onclick="return do_confirm();">Delete</a></div>'."\n";
		}
	}


	echo '	<div style="text-align:center; clear:both">Displaying page '.$page_offset.' on '.$nb_pages;
	echo '	<br/>';
	if ( $offset > 0 ) {
		echo '	<a href="'.admin_url( 'options-general.php?page=twitter2press/twitter2press.php&offset='.($offset-T2P_ADMIN_PER_PAGE) ).'">&laquo; previous page</a>';
	}
	if ( ($offset + T2P_ADMIN_PER_PAGE) < $nb_records ) {
		echo '	<a href="'.admin_url( 'options-general.php?page=twitter2press/twitter2press.php&offset='.($offset+T2P_ADMIN_PER_PAGE) ).'">next page &raquo;</a>';
	}
	echo '	</div>';
	echo '<hr/>';
	echo '<div style="text-align:center">Follow <a href="http://twitter.com/mathieulesniak">@mathieulesniak on Twitter</a></div>';
}


add_action('init', 'twitter2press_upload', -1);



/** 
	Rendering 
**/

	/* CSS */
function twitter2press_css() {

	if ( is_file(get_template_directory().'/twitter2press.css') ) {
		$stylesheet_url = get_template_directory_uri().'/twitter2press.css';
	}
	else {
		$stylesheet_url = get_option ( 'siteurl' ) . '/wp-content/plugins/twitter2press/twitter2press.css';
	}
	echo '<link rel="stylesheet" href="' . $stylesheet_url . '" type="text/css" />';
}
add_action( 'wp_head', 'twitter2press_css' );

	/* page loader */
function load_gallery() {
	Global $t2p_settings, $wp_query;


	if ( $wp_query->post->ID == $t2p_settings['page_id'] || $wp_query->post->post_parent == $t2p_settings['page_id'] ) {
		$wp_query->is_attachment = 0;
		$wp_query->is_single = 0;
		$wp_query->is_page = 1;

		remove_filter('the_content', 'prepend_attachment');

		add_filter('the_content','load_gallery_content');

	}
}

	/* gallery rendering */
function load_gallery_content($content) {
	Global $wpdb, $t2p_settings, $post, $wp_query;

	if ( $post->post_type == 'attachment' ) {
		$is_attachment = 1;
		$current_image = $post->ID;
		if ( isset($_GET['navig']) ) {
			$offset = $_GET['navig'];
		}
		else {
			$offset = 0;
		}
		$main_img = $post->guid;
		$post_id = $post->ID;
		$caption = $post->post_content;
		$do_search = true;
	}
	else {
		$is_attachment = 0;
		$args = array(
			'post_type' => 'attachment',
			'numberposts' => -1,
			'post_status' => null,
			'post_parent' => $post->ID
			); 
		$attachments = get_posts($args);

		$main_img = $attachments[0]->guid;
		$post_id = $attachments[0]->ID;
		$caption = $attachments[0]->post_content;
		$do_search = count($attachments) > 0;
		if ( $do_search == 0 ) {
			return $content;
		}
	}


	

	
	
	
	$table = $wpdb->prefix . "twitter2press";


	$cache = $wpdb->get_row($wpdb->prepare("SELECT *, UNIX_TIMESTAMP(time) AS ts FROM $table WHERE name = '%s'", $post_id));
	if ( $cache->updated == 0  && $do_search ) {
		$content_search = wp_remote_fopen('http://search.twitter.com/search.atom?q=from%3A'.$t2p_settings['twitter_login'].'%20'.$cache->shortened);
		preg_match('|<content type="html">(.*)</content>|Umis', $content_search, $matches);
		
		if ( $matches[1] != '' ) {

			$from = array('&lt;', '&quot;', '&gt;');
			$to = array('<', '"', '>');
			$caption = str_replace($from, $to, $matches[1]);
			$caption = str_replace('<a href="'.$cache->shortened.'"><b>'.$cache->shortened.'</b></a>', '', $caption);
			$caption = str_replace('&amp;apos;', "'", $caption);
			$caption = str_replace('&amp;', '&', $caption);
			
			$wpdb->query($wpdb->prepare("UPDATE $table SET updated=1 WHERE name='%s'", $post_id));

	
			
			$update_args = array( 'ID' => $post_id, 'post_content' => $caption );
			wp_update_post($update_args);
		}
	}
	$caption = str_replace('&amp;apos;', "'", $caption);
	$caption = str_replace('&amp;', '&', $caption);

	$per_page = ( $t2p_settings['nb_pic_per_page'] != '' ) ? $t2p_settings['nb_pic_per_page'] : 10;



	$img_sizes = @getimagesize($main_img);
	if ( is_array($img_sizes) ) {
		$max_width = 'style="max-width: '.$img_sizes[0].'px"';
	}
	else {
		$max_width = '';
	}
	$output  .= '<div id="twitter2press-gallery">'; 
	$output .= '	<div class="mainimg"><img src="'.$main_img.'" '.$max_width.'/></div>';
	$output .= '	<div class="caption">'.(utf8_decode($caption)).'</div>'."\n";
	$output .= '	<div class="datetime">This image was twitted on '.date('l, F jS, Y @ H:i', $cache->ts).'</div>';
		
	// Displaying other thumbs
	if ( $is_attachment ) {
		$args = array(
			'post_type' => 'attachment',
			'numberposts' => $per_page,
			'post_status' => null,
			'post_parent' => $post->post_parent, 
			'exclude' => $current_image,
			'offset' => $offset
			);		
		$output .= '<div class="retweet"><a href="http://twitter.com/home?status='.urlencode('RT @'.$t2p_settings['twitter_login'].':'.$caption.' '.$cache->shortened).'">Retweet this image</a></div>';
	}
	else {
		$args = array(
			'post_type' => 'attachment',
			'numberposts' => $per_page,
			'post_status' => null,
			'post_parent' => $post->ID, 
			'exclude' => $current_image,
			'offset' => $offset
		); 
	}

	$attachments = get_posts($args);


	$output .= '	<ul>'."\n";
	if ($attachments) {
		foreach ($attachments as $attachment) {
//			$output .= '		<li><a href="'.get_attachment_link($attachment->ID).'">'.wp_get_attachment_image($attachment->ID,  array(100, 100), false).'</a></li>'."\n";;
			$output .= '		<li>'.get_the_attachment_link($attachment->ID, false, null, true).'</li>';
		}
	}
	$output .= '	</ul>';
	
	
	
	$output .= '	<div class="breaker"></div>';
	$output .= '</div>';
	
	if ( $is_attachment ) {
		$parent_post = get_post($post->post_parent);

		return $output . $parent_post->post_content;
	} else {
		return $output.$content;
	}

}

add_action('wp','load_gallery');


/* Upload  */
function twitter2press_upload() {
	Global $t2p_settings, $_POST, $_FILES, $wpdb;
	

	
	// Only is a file is uploaded
	if ( isset($_FILES['media']) ) {
		// Loading all required libs from admin  media upload
		include ABSPATH. 'wp-admin/includes/import.php';
		include ABSPATH. 'wp-admin/includes/file.php';	
		include ABSPATH. 'wp-admin/includes/image.php';	

		include ABSPATH. 'wp-admin/import/wordpress.php';
		include ABSPATH. 'wp-admin/includes/media.php';
		
		
		// Password check
		if ( $t2p_settings['twitter_login'] == $_POST['username'] 
				&& $t2p_settings['twitter_password'] == $_POST['password'] ) {
				
			$parent_post_data = get_post($t2p_settings['page_id']);

			
			
			$f_name = $_FILES['media']['name'];
			$f_tmp	= $_FILES['media']['tmp_name'];
			$f_size = $_FILES['media']['size'];
				
			$f_infos = getimagesize($f_tmp);
				
			// testing mime-type
			if ( $f_infos[2] == 1 || $f_infos[2] == 2 || $f_infos[2] == 3 ) {
				$overrides = array('test_form'=>false);
				$time = current_time('mysql');
				$name = $_FILES['media']['name'];
				$file = wp_handle_upload($_FILES['media'], $overrides, $time);

				$name_parts = pathinfo($name);
				$name = trim( substr( $name, 0, -(1 + strlen($name_parts['extension'])) ) );

				$url = $file['url'];
				$type = $file['type'];
				$file = $file['file'];
				$title = $name;
				$content = '';

				$attachment = array(
									'post_mime_type' => $type,
									'guid' => $url,
									'post_parent' => $t2p_settings['page_id'],
									'post_title' => 'Twitter picture',
									'post_content' => $content,
									'post_author' => $parent_post_data->post_author
									);
				
				// Save the attachment

				$id = wp_insert_attachment($attachment, $file, $t2p_settings['page_id']);
				if ( !is_wp_error($id) ) {
					wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $file ) );
				}


					
				// Syncing twitter2press table
				$table = $wpdb->prefix . "twitter2press";
				$wpdb->query($wpdb->prepare("INSERT INTO $table (name, twitt) VALUES ('%s', NULL)", $id));

		
				// Building short url
				$final_url = get_option ( 'siteurl' ) . '?page_id=' . $id;


				if ( $t2p_settings['url_shortener'] == 'isgd' ) {
					$shortened = wp_remote_fopen('http://is.gd/api.php?longurl='.urlencode($final_url));							
				}
				else if ( $t2p_settings['url_shortener'] == 'trim' ) {
					$xml_shortened = wp_remote_fopen('http://api.tr.im/api/trim_url.xml?url='.urlencode($final_url));
					preg_match('|<url>(.*)</url>|Umis', $xml_shortened, $matches);
							
					$shortened = $matches[1];
				}
				// Bit.ly | j.mp
				else if ( $t2p_settings['url_shortener'] == 'bitly'  ) {
					$xml_shortened = wp_remote_fopen('http://api.bit.ly/shorten?version=2.0.1&longUrl='.urlencode($final_url).'&login=bitlyapidemo&apiKey=R_0da49e0a9118ff35f52f629d2d71bf07&format=xml');
					preg_match('|<shortUrl>(.*)</shortUrl>|Umis', $xml_shortened, $matches);
							
					$shortened = $matches[1];
				}
				else if ( $t2p_settings['url_shortener'] == 'jmp') {
					$xml_shortened = wp_remote_fopen('http://api.j.mp/shorten?version=2.0.1&longUrl='.urlencode($final_url).'&login=bitlyapidemo&apiKey=R_0da49e0a9118ff35f52f629d2d71bf07&format=xml');
					preg_match('|<shortUrl>(.*)</shortUrl>|Umis', $xml_shortened, $matches);
							
					$shortened = $matches[1];
							
				}
				else if ( $t2p_settings['url_shortener'] == 'yourls' ) {
					$args['body']['url'] = $final_url;
					$args['body']['keyword'] = '';
					$args['body']['format'] = 'simple';
					$args['body']['action'] = 'shorturl';
					$args['body']['username'] = $t2p_settings['url_shortener_login'];
					$args['body']['password'] = $t2p_settings['url_shortener_password'];
						
					$xml_shortened = wp_remote_post($t2p_settings['url_shortener_endpoint'], $args);
							
						
					$shortened = $xml_shortened['body'];				
				}
				// Default case : tinyurl
				else {
					$shortened = wp_remote_fopen('http://tinyurl.com/api-create.php?url='.urlencode($final_url));
				}
						
				echo '<mediaurl>'.$shortened.'</mediaurl>'."\n";
		
				$wpdb->query($wpdb->prepare("UPDATE  $table SET shortened='%s', updated=0 WHERE name=%d", $shortened, $id));		
			}			
		else {
						// Not a valid image
		}
				
	}

	exit;
}


}

/* admin func */

function twitter2press_admin_notice() {
	Global $t2p_settings;

	if ( $t2p_settings['setup'] == 0 ) {
		echo '<div class="error"><p><b>'.('Twitter2Press is not configured. Please go to the <a href="'.admin_url( 'options-general.php?page=twitter2press/twitter2press.php' ).'">plugin admin page</a> to configure it. ' ) . '</b></p></div>';
	}
	if ( $t2p_settings['setup'] == 1 ) {
		echo '<div class="error"><p><b>'.('Twitter2Press notice : Please go to the <a href="'.admin_url( 'options-general.php?page=twitter2press/twitter2press.php' ).'">plugin admin page</a> to finish the plugin upgrade. ' ) . '</b></p></div>';
	}
}
add_action( 'admin_notices', 'twitter2press_admin_notice' );

/* activation hook */
function twitter2press_activate() {
	Global $wpdb;

	$table = $wpdb->prefix.'twitter2press';
	$table_result = $wpdb->get_var("SHOW TABLES LIKE '$table'");

	// Table does not exists, creating
	if ( $table_result != $table ) {
		$query = "CREATE TABLE `$table`(
			`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
			`name` VARCHAR( 50 ) NOT NULL,
			`twitt`VARCHAR(140),
			`time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
			`shortened` VARCHAR(255),
			`updated` TINYINT(1)
			)";
	
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($query);
	}

}
register_activation_hook( __FILE__, 'twitter2press_activate' );


// Twitter2Press migrate from pre-1.0.4 setups
function t2p_migrate($old_file, $content, $date) {
	Global $t2p_settings;
	

	$wp_filetype = wp_check_filetype( basename($old_file), false );
	extract( $wp_filetype );

		
	if ( !$ext )
		$ext = ltrim(strrchr($file['name'], '.'), '.');

	if ( !$type )
		$type = $file['type'];
	
	// not a regular file
	if ( !$type || !$ext ) {
		return -1;
	}

	// A writable uploads dir will pass this test. Again, there's no point overriding this one.
	if ( ! ( ( $uploads = wp_upload_dir($time) ) && false === $uploads['error'] ) )
		return $upload_error_handler( $file, $uploads['error'] );


	$filename = wp_unique_filename( $uploads['path'], basename($old_file), $unique_filename_callback );

	// Move the file to the uploads dir
	$new_file = $uploads['path'] . "/$filename";
	$url = $uploads['url'] . "/$filename";	


	
	copy($old_file, $new_file);
	

	// Set correct file permissions
	$stat = stat( dirname( $new_file ));
	$perms = $stat['mode'] & 0000666;
	@ chmod( $new_file, $perms );

	// Compute the URL
	$url = $uploads['url'] . "/$filename";	
	
	
	

	

	
	$attachment = array(
						'post_mime_type' => $type,
						'guid' => $url,
						'post_parent' => $t2p_settings['page_id'],
						'post_title' => 'Twitter picture',
						'post_content' => $content,
						'post_author' => 0,
						'post_date' => $date,
						'post_date_gmt' => $date,
						);
	
	// Save the attachment

	$id = wp_insert_attachment($attachment, $new_file, $t2p_settings['page_id']);
	if ( !is_wp_error($id) ) {
		wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $new_file ) );
	}
	
	return $id;
	
	
}


?>