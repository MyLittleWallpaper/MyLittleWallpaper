<?php

declare(strict_types=1);

use MyLittleWallpaper\classes\Database;
use MyLittleWallpaper\classes\output\BasicJSON;
use MyLittleWallpaper\classes\Response;

global $user;

$db = Database::getInstance();

if ($user->getIsAdmin()) {
    $return = ['result' => 'Not found'];
    if (!empty($_GET['id']) && $_GET['reason']) {
        $res = $db->query("SELECT * FROM `wallpaper_submit` WHERE id = ? LIMIT 1", [$_GET['id']]);
        while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
            $return   = ['result' => 'OK'];
            $saveData = [
                'user_id' => $row['user_id'],
                'name'    => $row['name'],
                'url'     => $row['url'],
                'width'   => $row['width'],
                'height'  => $row['height'],
                'time'    => time(),
            ];
            if ($_GET['reason'] === 'quality') {
                $saveData['reason'] = 'Wallpaper quality wasn\'t good enough.';
            } elseif ($_GET['reason'] === 'duplicate') {
                $saveData['reason'] = 'Wallpaper is already on the list.';
            } elseif ($_GET['reason'] === 'size') {
                $saveData['reason'] = 'Wallpaper size doesn\'t meet the requirements (1366x768).';
            } elseif ($_GET['reason'] === 'unknown') {
                $saveData['reason'] = 'Unknown source / no author.';
            } elseif ($_GET['reason'] === 'vector') {
                $saveData['reason'] = 'No permission for vector.';
            }
            $db->saveArray('wallpaper_submit_rejected', $saveData);
            $db->query("DELETE FROM `wallpaper_submit` WHERE id = ?", [$_GET['id']]);
        }
    }
} else {
    $return = ['result' => 'Permission denied'];
}

$denySubmissionResult = new BasicJSON($return);

$response = new Response($denySubmissionResult);
$response->output();
