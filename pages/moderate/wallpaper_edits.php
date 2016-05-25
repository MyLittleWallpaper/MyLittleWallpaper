<?php
// Check that correct entry point was used
if (!defined('INDEX')) exit();
global $user;

// @todo Rewrite
/*$time_start = microtime(true);
define('INDEX', true);

// We want all possible errors, but not to show them
error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', '0');
ini_set('log_errors', true);

// Conficuration and initialization
require_once('../config.php');
require_once('../lib/db.inc.php');
$db = new Database(DBUSER, DBPASS, DBNAME, DBHOST);
require_once('../lib/functions.php');
require_once('../lib/colors.inc.php');

$active = 'mod_wallpaperedits';
$thetitle = 'Wallpaper edits | ';
$tags_add_meta = '';
$rss = '';
$pubpath = str_replace('moderate/', '', $pubpath);

if ($user->getIsAdmin()) {
	$sql = "SELECT * FROM wallpaper_edit WHERE discarded = ? ORDER BY id LIMIT 1";
	$data = Array(0);
	$res = $db->query($sql, $data);
	$wallpaper_data = [];
	while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
		$wallpaper_data = $row;
	}
	if (!empty($wallpaper_data)) {
		$sql = "SELECT * FROM wallpaper WHERE id = ? ORDER BY id LIMIT 1";
		$data = Array($wallpaper_data['wallpaper_id']);
		$res = $db->query($sql, $data);
		$wallpaper_info = [];
		while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
			$wallpaper_info = $row;
		}
	}
	
	$pubnouli = str_replace('ulipito/', '', $pubpath);
	
	if (isset($_POST['name'])) {
		if (!empty($_POST['name']) && !empty($_POST['author']) && !empty($_POST['url'])) {
			$saveauthor = '';
			$authorlist = explode(',', $_POST['author']);
			$author_array = [];
			foreach($authorlist as $tag) {
				$tag = trim($tag);
				if (str_replace(' ', '', $tag) != '') {
					$res = $db->query("SELECT id, name FROM tag_artist WHERE name = ?", Array($tag));
					while($row = $res->fetch(PDO::FETCH_ASSOC)) {
						$author_array[] = $row['id'];
						if ($saveauthor == '') $saveauthor = $tag;
					}
				}
			}
	
			$data = Array(
				'name' => $_POST['name'],
				'url' => $_POST['url'],
				'no_resolution' => (!empty($_POST['no_resolution']) && $_POST['no_resolution'] == '1' ? 1 : 0),
			);
				
			$db->query("DELETE FROM wallpaper_tag WHERE wallpaper_id = ?", Array($wallpaper_data['wallpaper_id']));
			$db->query("DELETE FROM wallpaper_tag_artist WHERE wallpaper_id = ?", Array($wallpaper_data['wallpaper_id']));
			$db->query("DELETE FROM wallpaper_tag_platform WHERE wallpaper_id = ?", Array($wallpaper_data['wallpaper_id']));
			$db->query("DELETE FROM wallpaper_tag_aspect WHERE wallpaper_id = ?", Array($wallpaper_data['wallpaper_id']));
				
			$imageid = $db->saveArray('wallpaper', $data, $wallpaper_data['wallpaper_id']);
			foreach($author_array as $auth) {
				$data = Array(
					'tag_artist_id' => $auth,
					'wallpaper_id' => $imageid,
				);
				$db->saveArray('wallpaper_tag_artist', $data);
			}
			$taglist = explode(',', $_POST['tags']);
			foreach($taglist as $tag) {
				$tag = trim($tag);
				if (str_replace(' ', '', $tag) != '') {
					$res = $db->query("SELECT id, name FROM tag WHERE name = ?", Array($tag));
					while($row = $res->fetch(PDO::FETCH_ASSOC)) {
						$data = Array(
							'tag_id' => $row['id'],
							'wallpaper_id' => $imageid,
						);
						$db->saveArray('wallpaper_tag', $data);
					}
				}
			}
	
			$fields = Array(Array('table' => 'tag', 'field' => 'id'));
			$join = Array(
				Array(
					'table' => 'wallpaper_tag',
					'condition' => Array(
						Array(
							Array(
								'table' => 'wallpaper_tag',
								'field' => 'tag_id',
							),
							Array(
								'table' => 'tag',
								'field' => 'id',
							),
						),
					),
				),
			);
			$conditions = [];
			$conditions[] = Array(
				'table' => 'wallpaper_tag',
				'field' => 'wallpaper_id',
				'value' => $imageid,
				'operator' => '=',
			);
			$conditions[] = Array(
				'table' => 'tag',
				'field' => 'type',
				'value' => 'character',
				'operator' => '=',
			);
			$order = Array(Array('table' => 'tag', 'field' => 'name'));
			$taglist = $db->getlist('tag', $fields, $conditions, $order, NULL, $join);
			$chartags = '';
			$ct_count = 0;
			foreach($taglist as $tag) {
				if ($chartags != '') $chartags .= ',';
				$chartags .= $tag['id'];
				$ct_count ++;
			}
			if ($ct_count < 16) {
				$savedata = Array('chartags' => $chartags);
				$db->saveArray('wallpaper', $savedata, $imageid);
			}
	
			$noaspect = false;
			$platformlist = explode(',', $_POST['platform']);
			foreach($platformlist as $tag) {
				$tag = trim($tag);
				if (str_replace(' ', '', $tag) != '') {
					$res = $db->query("SELECT id, name FROM tag_platform WHERE name = ?", Array($tag));
					while($row = $res->fetch(PDO::FETCH_ASSOC)) {
						if ($row['name'] == 'Mobile') {
							$db->saveArray('wallpaper', Array('no_aspect' => 1), $imageid);
							$noaspect = true;
						}
						$data = Array(
							'tag_platform_id' => $row['id'],
							'wallpaper_id' => $imageid,
						);
						$db->saveArray('wallpaper_tag_platform', $data);
					}
				}
			}
			if (!$noaspect) {
				$aspect = aspect($wallpaper_info['width'], $wallpaper_info['height']);
				$res = $db->query("SELECT id, name FROM tag_aspect WHERE name = ?", Array($aspect));
				while($row = $res->fetch(PDO::FETCH_ASSOC)) {
					$data = Array(
						'tag_aspect_id' => $row['id'],
						'wallpaper_id' => $imageid,
					);
					$db->saveArray('wallpaper_tag_aspect', $data);
				}
			}
			$db->query("DELETE FROM wallpaper_edit WHERE id = ?", Array($wallpaper_data['id']));
			header('Location: edits.php');
		}
	}
}
require_once('../lib/header.php');
echo '<div id="content"><div>';
echo '<h1>Wallpaper edits</h1>';
	
if ($user->getIsAdmin()) {
	if (!empty($wallpaper_data)) {
		$authorlist = explode(',', $wallpaper_data['author']);
		$author_array = [];
		$new_author_array = [];
		foreach($authorlist as $tag) {
			$tag = trim($tag);
			if (str_replace(' ', '', $tag) != '') {
				$res = $db->query("SELECT id, name FROM tag_artist WHERE name = ?", Array($tag));
				$found = false;
				while($row = $res->fetch(PDO::FETCH_ASSOC)) {
					$found = true;
					$author_array[] = $row['name'];
				}
				if (!$found) $new_author_array[] = $tag;
			}
		}
	
		$taglist = explode(',', $wallpaper_data['tags']);
		$tag_array = [];
		$new_tag_array = [];
		foreach($taglist as $tag) {
			$tag = trim($tag);
			if (str_replace(' ', '', $tag) != '') {
				$res = $db->query("SELECT id, name FROM tag WHERE name = ?", Array($tag));
				$found = false;
				while($row = $res->fetch(PDO::FETCH_ASSOC)) {
					$found = true;
					$tag_array[] = $row['name'];
				}
				if (!$found) $new_tag_array[] = $tag;
			}
		}
		
		echo '<form class="uploadform" method="post" action="edits.php" accept-charset="utf-8" style="margin-top:20px;padding-left:0;">';
		echo '<div><label>Name:</label><input type="text" autocomplete="off" name="name" style="width:300px;" value="'.Format::htmlEntities($wallpaper_data['name']).'"/></div>';
		echo '<div><label>Author:</label><input type="text" autocomplete="off" name="author" id="author" style="width:300px;" value="'.Format::htmlEntities(implode(', ', $author_array)).', " /></div>';
		echo '<div><label>New author:</label>'.Format::htmlEntities(implode(', ', $new_author_array)).'</div>';
		echo '<div><label>Tags:</label><input type="text" autocomplete="off" name="tags" id="tags" style="width:300px;" value="'.Format::htmlEntities(implode(', ', $tag_array)).', " /></div>';
		echo '<div><label>New tags:</label>'.htmlentities(implode(', ', $new_tag_array)).'</div>';
		echo '<div><label>Platform:</label><input type="text" autocomplete="off" name="platform" id="platform" style="width:300px;" value="Desktop, " /></div>';
		echo '<div><label>No reslotion:</label><input type="checkbox" value="1" name="no_resolution" /></div>';
		echo '<div><label>URL:</label><input type="text" autocomplete="off" name="url" style="width:300px;" value="'.Format::htmlEntities($wallpaper_data['url']).'" /></div>';
		echo '<div><label>Size:</label>'.$wallpaper_info['width'].'x'.$wallpaper_info['height'].'</div>';
	
		echo '<br /><input type="submit" value="Accept" />';
		echo '</form><br /><br />';
		
		echo '<img src="'.$pubnouli.'image.php?image='.$wallpaper_info['file'].'&amp;resize=2" alt="'.Format::htmlEntities($wallpaper_info['name']).'" />';
	} else {
		echo '<h1>No wallpaper edits to moderate</h1>';
	}
	
} else {
	echo '<p>You do not have permission to access this page!</p>';
}
	
echo '</div></div>';
echo '<div id="footer">My Little Pony: Friendship is Magic is © Hasbro, all wallpapers © to their respective artists.';
echo 'Header Rarity vector by FoxTail8000 [<a href="http://foxtail8000.deviantart.com/art/It-s-Sweet-297643309">link</a>]';
echo '<br /><br />My Little Wallpaper is not affiliated to Hasbro, The Hub or its associates.';
echo '<div class="info">The purpose of this website is to list My Little Pony: Friendship is Magic wallpapers. Only thumbnail (200x150), preview image (640x480) and image information is shown.<br />';
echo 'Download link directs to the source of the wallpaper or with permission from the artist, the image is hosted here.</div>';
echo '<div class="contact">If you have any inquiries, send me an e-mail to <a href="mailto:sharkmachine(at)ecxol(dot)net">sharkmachine(at)ecxol(dot)net</a></div>';
echo '</div></body></html>';
*/