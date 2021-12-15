<?php

// Check that correct entry point was used
if (!defined('INDEX')) {
    exit();
}
global $user, $db;

require_once(ROOT_DIR . 'classes/Wallpaper.php');
require_once(ROOT_DIR . 'classes/output/BasicJSON.php');

$return = ['favCount' => '', 'favCountNumber' => 0, 'favButtonText' => ''];
if (!empty($_GET['wallpaperId']) && !$user->getIsAnonymous()) {
    $wallpaper = new Wallpaper($db->getRecord('wallpaper', ['field' => 'id', 'value' => $_GET['wallpaperId']]));
    if ($wallpaper->getId()) {
        if ($wallpaper->getIsFavourite($user->getId())) {
            $db->query(
                "DELETE FROM wallpaper_fav WHERE wallpaper_id = ? AND user_id = ?",
                [$wallpaper->getId(), $user->getId()]
            );
            $return['favButtonText'] = 'Add to favourites';
        } else {
            $db->saveArray('wallpaper_fav', ['wallpaper_id' => $wallpaper->getId(), 'user_id' => $user->getId()]);
            $return['favButtonText'] = 'Remove from favs';
        }
        $result   = $db->query("SELECT count(*) cnt FROM wallpaper_fav WHERE wallpaper_id = ?", [$wallpaper->getId()]);
        $favCount = 0;
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $favCount = (int)$row['cnt'];
        }
        $db->saveArray('wallpaper', ['favs' => $favCount], $wallpaper->getId());

        $return['favCount']       = $favCount . ' fav' . ($favCount != 1 ? 's' : '');
        $return['favCountNumber'] = (int)$favCount;
    }
}

$wallpaperFavResult = new BasicJSON($return);
$response           = new Response($wallpaperFavResult);
$response->output();