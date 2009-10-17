<?php

/*
Plugin Name: Twitter2Press
Plugin URI: http://projets.lesniak.fr/twitter2press
Description: Twitter2Press is the Wordpress Plug-In that allow you to post your Twitter Images to your WordPress by using Tweetie client
Version: 1.0.3
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


if (!defined('UPLOAD_DIRECTORY')) {
	define('UPLOAD_DIRECTORY',ABSPATH . 'wp-content/');
}

if (!defined('GALLERYPATH')) {
	define('GALLERYPATH','/wp-content/twitter2press');
}

if ( !defined('T2P_CHMOD_DIR') ) {
	define('T2P_CHMOD_DIR', 0755 );
}

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

	if ( $t2p_settings['setup'] == 0 ) {
		$t2p_settings['setup'] = 1;
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
	
	
		if(is_writable(UPLOAD_DIRECTORY)) {
			if(!is_dir(UPLOAD_DIRECTORY . 'twitter2press')) {
				mkdir(UPLOAD_DIRECTORY . 'twitter2press', T2P_CHMOD_DIR);
			}
			
			if(!is_dir(UPLOAD_DIRECTORY . 'twitter2press/tn')) {
				mkdir(UPLOAD_DIRECTORY . 'twitter2press/tn', T2P_CHMOD_DIR);
			}
		}
		
		$pages = get_pages(); 
		
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
		echo '<h3>Manage pictures</h3>';
		echo 'Click a picture to delete it : <br/>';
		$table = $wpdb->prefix . "twitter2press";

		if ( isset($_GET['delete']) && $_GET['delete'] != '' ) {
			$wpdb->query($wpdb->prepare("DELETE FROM $table WHERE id='%d'", $_GET['delete']));
		}

		$nb_records = $wpdb->get_var("SELECT COUNT(*) FROM $table");
		$nb_pages 	= ceil($nb_records / T2P_ADMIN_PER_PAGE);
		$page_offset = ($offset / T2P_ADMIN_PER_PAGE) + 1;

		$images = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table ORDER BY id DESC LIMIT $offset,%d", T2P_ADMIN_PER_PAGE));
		foreach ( $images as $image ) {
			echo '<a href="'.admin_url( 'options-general.php?page=twitter2press/twitter2press.php&offset='.$offset.'&delete='.$image->id ).'" onclick="return do_confirm();"><img src="'.get_option ( 'siteurl' ).GALLERYPATH.'/tn/'.$image->name.'" style="padding: 5px"/></a>';
		}

		echo '	<div style="text-align:center">Displaying page '.$page_offset.' on '.$nb_pages;
		echo '	<br/>';
		if ( $offset > 0 ) {
			echo '	<a href="'.admin_url( 'options-general.php?page=twitter2press/twitter2press.php&offset='.($offset-T2P_ADMIN_PER_PAGE) ).'">&laquo; previous page</a>';
		}
		if ( ($offset + T2P_ADMIN_PER_PAGE) <= $nb_records ) {
			echo '	<a href="'.admin_url( 'options-general.php?page=twitter2press/twitter2press.php&offset='.($offset+T2P_ADMIN_PER_PAGE) ).'">next page &raquo;</a>';
		}
		echo '	</div>';
		echo '<hr/>';
		echo '<div style="text-align:center">Follow <a href="http://twitter.com/mathieulesniak">@mathieulesniak on Twitter</a></div>';
}


add_action('plugins_loaded', 'twitter2press_upload', -1);



/** 
	Rendering 
**/

	/* CSS */
function twitter2press_css() {
	$stylesheet_url = get_option ( 'siteurl' ) . '/wp-content/plugins/twitter2press/twitter2press.css';
	echo '<link rel="stylesheet" href="' . $stylesheet_url . '" type="text/css" />';
}
add_action( 'wp_head', 'twitter2press_css' );

	/* page loader */
function load_gallery() {
	Global $t2p_settings, $wp_query;
	

	if ( $wp_query->post->ID == $t2p_settings['page_id']	) {
		add_filter('the_content','load_gallery_content');
	}
}

	/* gallery rendering */
function load_gallery_content($content) {
	Global $wpdb, $t2p_settings;
	$table = $wpdb->prefix . "twitter2press";

	if ( isset($_GET['image_id']) ) {
		$img = $wpdb->get_row($wpdb->prepare("SELECT *, UNIX_TIMESTAMP(time) AS ts FROM $table WHERE id = %d", $_GET['image_id']));
	}
	else {
		$img = $wpdb->get_row($wpdb->prepare("SELECT *, UNIX_TIMESTAMP(time) AS ts FROM $table ORDER BY id DESC LIMIT 0,1"));
	}
	if ( $img->updated == 0 ) {
		$content_search = wp_remote_fopen('http://search.twitter.com/search.atom?q=from%3A'.$t2p_settings['twitter_login'].'%20'.$img->shortened);
		preg_match('|<content type="html">(.*)</content>|Umis', $content_search, $matches);
		if ( $matches[1] != '' ) {
			$from = array('&lt;', '&quot;', '&gt;');
			$to = array('<', '"', '>');
			$caption = str_replace($from, $to, $matches[1]);
			$caption = str_replace('<a href="'.$img->shortened.'"><b>'.$img->shortened.'</b></a>', '', $caption);
			$wpdb->query($wpdb->prepare("UPDATE $table SET updated=1, twitt='%s' WHERE id='%d'", $caption, $img->id));
		}
	}
	else {
		$caption = $img->twitt;
	}
	$caption = str_replace('&amp;apos;', "'", $caption);

	$per_page = ( $t2p_settings['nb_pic_per_page'] != '' ) ? $t2p_settings['nb_pic_per_page'] : 10;

	$output  = '<div id="twitter2press-gallery">'; 
	if ( $img != null ) {
		$output .= '	<div class="mainimg"><img src="'.get_option ( 'siteurl' ).GALLERYPATH.'/'.$img->name.'" /></div>';
		$output .= '	<div class="caption">'.(utf8_decode($caption)).'</div>'."\n";
		$output .= '	<div class="datetime">This image was twitted on '.date('l, F jS, Y @ H:i', $img->ts).'</div>';
		
		$old_images = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table WHERE id != %d ORDER BY id DESC LIMIT %d", $img->id, $per_page));
	}
	else {
		$old_images = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table ORDER BY id DESC LIMIT %d", $per_page));
	}


	
	$page = ( $t2p_settings['page_id'] != 0 ) ? '?page_id='.$t2p_settings['page_id'].'&' : '?';
	if ( $old_images != null ) {
		$output .= '<ul>'."\n";
		while ( list($key, $val) = each($old_images) ) {
			$output .= '	<li><a href="'.get_option('siteurl').$page.'image_id='.$val->id.'"><img src="'.get_option ( 'siteurl' ).GALLERYPATH.'/tn/'.$val->name.'" /></a></li>'."\n";
		}
		$output .= '</ul>'."\n";
	}
	
	$output .= '	<div class="breaker"></div>';
	$output .= '</div>';
	
	return $output.$content;
}

add_action('get_header','load_gallery');


/* Upload  */
function twitter2press_upload() {
	Global $t2p_settings, $_POST, $_FILES, $wpdb;
	

	

	// Only is a file is uploaded
	if ( isset($_FILES['media']) ) {

		// Password check
		if ( $t2p_settings['twitter_login'] == $_POST['username'] 
				&& $t2p_settings['twitter_password'] == $_POST['password'] ) {

				$f_name = $_FILES['media']['name'];
				$f_tmp	= $_FILES['media']['tmp_name'];
				$f_size = $_FILES['media']['size'];
				
				$f_infos = getimagesize($f_tmp);

				if ( $f_infos[2] == 1 || $f_infos[2] == 2 || $f_infos[2] == 3 ) {
					$ts = time();
					
					$dest_file = UPLOAD_DIRECTORY.'/twitter2press/'.$ts.basename($f_name);
					$dest_file_tn = UPLOAD_DIRECTORY.'/twitter2press/tn/'.$ts.basename($f_name);
					if ( move_uploaded_file($f_tmp, $dest_file) ) {
						if ( file_exists(ABSPATH. 'wp-admin/includes/image.php') ) {
							if( !function_exists('wp_crop_image') ) {
								include ABSPATH. 'wp-admin/includes/image.php';
							}
						}
						// Thumbnails work
						if ( function_exists('wp_crop_image') ) {
							wp_crop_image ($dest_file, 0, 0, 500, 500, 100, 100, false, $dest_file_tn);
						}
						else {
							copy($dest_file, $dest_file_tn);
						}

						$table = $wpdb->prefix . "twitter2press";

						$wpdb->query($wpdb->prepare("INSERT INTO $table (name, twitt) VALUES ('%s', NULL)", time().basename($f_name)));
						$id = $wpdb->insert_id;
						$final_url = get_option ( 'siteurl' ) . '?page_id=' . $t2p_settings['page_id'] . '&image_id=' . $id;


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
						
						$wpdb->query($wpdb->prepare("UPDATE  $table SET shortened='%s', updated=0 WHERE id=%d", $shortened, $id));
				
					}
					
					else {

					}
					
					

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

	if ( $t2p_settings['setup'] != 1 ) {
		echo '<div class="error"><p><b>'.('Twitter2Press is not configured. Please go to the <a href="'.admin_url( 'options-general.php?page=twitter2press/twitter2press.php' ).'">plugin admin page</a> to configure it. ' ) . '</b></p></div>';
	}
	
	if ( !is_writable(UPLOAD_DIRECTORY) ) {
		echo '<div class="error"><p><b>'.UPLOAD_DIRECTORY.' is not writable !</b></p></div>';
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
	
	// Creating directories
	if(is_writable(UPLOAD_DIRECTORY)) {
		if(!is_dir(UPLOAD_DIRECTORY . 'twitter2press')) {
			mkdir(UPLOAD_DIRECTORY . 'twitter2press', T2P_CHMOD_DIR);
		}
		
		if(!is_dir(UPLOAD_DIRECTORY . 'twitter2press/tn')) {
			mkdir(UPLOAD_DIRECTORY . 'twitter2press/tn', T2P_CHMOD_DIR);
		}
	}

}
register_activation_hook( __FILE__, 'twitter2press_activate' );
?>