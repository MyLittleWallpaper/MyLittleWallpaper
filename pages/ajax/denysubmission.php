<?php
// Check that correct entry point was used
if (!defined('INDEX')) exit();
global $user;

require_once(ROOT_DIR . 'classes/output/BasicJSON.php');

if ($user->getIsAdmin()) {
	$return = Array('result' => 'Not found');
	if (!empty($_GET['id']) && $_GET['reason']) {
		$res = $db->query("SELECT * FROM `wallpaper_submit` WHERE id = ? LIMIT 1", Array($_GET['id']));
		while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
			$return = Array('result' => 'OK');
			$savedata = Array('user_id' => $row['user_id'], 'name' => $row['name'], 'url' => $row['url'], 'width' => $row['width'], 'height' => $row['height'], 'time' => time());
			if ($_GET['reason'] == 'quality') $savedata['reason'] = 'Wallpaper quality wasn\'t good enough.';
			elseif ($_GET['reason'] == 'duplicate') $savedata['reason'] = 'Wallpaper is already on the list.';
			elseif ($_GET['reason'] == 'size') $savedata['reason'] = 'Wallpaper size doesn\'t meet the requirements (1366x768).';
			elseif ($_GET['reason'] == 'unknown') $savedata['reason'] = 'Unknown source / no author.';
			elseif ($_GET['reason'] == 'vector') $savedata['reason'] = 'No permission for vector.';
			$db->saveArray('wallpaper_submit_rejected', $savedata);
			$db->query("DELETE FROM `wallpaper_submit` WHERE id = ?", Array($_GET['id']));
		}
	}
} else {
	$return = Array('result' => 'Permission denied');
}

$denySubmissionResult = new BasicJSON($return);

$response = new Response($denySubmissionResult);
$response->output();