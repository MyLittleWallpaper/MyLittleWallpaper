<?php
// Check that correct entry point was used
if (!defined('INDEX')) exit();
global $user;

require_once(ROOT_DIR . 'classes/output/BasicJSON.php');

$return = Array();

if (!empty($_GET['id'])) {
	$sql = "SELECT id, name, url, no_resolution, direct_with_link FROM wallpaper WHERE id = ? LIMIT 1";
	$result = $db->query($sql, Array($_GET['id']));
	while($row = $result->fetch(PDO::FETCH_ASSOC)) {
		$return['id'] = $row['id'];
		$return['name'] = $row['name'];
		$return['tags'] = '';
		$sql = "SELECT t.name FROM tag t JOIN wallpaper_tag wt ON (t.id = wt.tag_id) WHERE wt.wallpaper_id = ? ORDER BY t.name";
		$res = $db->query($sql, Array($row['id']));
		while($tag = $res->fetch(PDO::FETCH_ASSOC)) {
			$return['tags'] .= $tag['name'];
			$return['tags'] .= ', ';
		}
		$return['author'] = '';
		$sql = "SELECT t.name FROM tag_artist t JOIN wallpaper_tag_artist wt ON (t.id = wt.tag_artist_id) WHERE wt.wallpaper_id = ? ORDER BY t.name";
		$res = $db->query($sql, Array($row['id']));
		while($tag = $res->fetch(PDO::FETCH_ASSOC)) {
			$return['author'] .= $tag['name'];
			$return['author'] .= ', ';
		}
		$return['platform'] = '';
		$sql = "SELECT t.name FROM tag_platform t JOIN wallpaper_tag_platform wt ON (t.id = wt.tag_platform_id) WHERE wt.wallpaper_id = ? ORDER BY t.name";
		$res = $db->query($sql, Array($row['id']));
		while($tag = $res->fetch(PDO::FETCH_ASSOC)) {
			$return['platform'] .= $tag['name'];
			$return['platform'] .= ', ';
		}
		$return['url'] = $row['url'];
		if ($user->getIsAdmin()) {
			$return['no_resolution'] = $row['no_resolution'];
			$return['direct_with_link'] = $row['direct_with_link'];
		}
	}
}

$wallpaperInfoResult = new BasicJSON($return);

$response = new Response($wallpaperInfoResult);
$response->output();