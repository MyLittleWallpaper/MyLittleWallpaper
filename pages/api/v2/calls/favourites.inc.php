<?php

declare(strict_types=1);

use MyLittleWallpaper\classes\output\WallpaperList;

global $db;

// @todo Centralise somewhere
$requestAllowed = false;
$userId         = null;
if (!empty($_GET['requestId']) && !empty($_GET['hash']) && !empty($_GET['userName'])) {
    $result = $db->query("SELECT id, token FROM `user` WHERE username = ?", [$_GET['userName']]);
    while ($tokenRow = $result->fetch(PDO::FETCH_ASSOC)) {
        if (
            strcasecmp(hash('sha256', $_GET['userName'] . $tokenRow['token'] . $_GET['requestId']), $_GET['hash']) === 0
        ) {
            $userId         = (int)$tokenRow['id'];
            $requestAllowed = true;
        }
    }

    if ($requestAllowed) {
        $result = $db->query(
            "SELECT id FROM user_api_requests WHERE userId = ? AND requestId = ?",
            [$userId, $_GET['requestId']]
        );
        if ($result->fetch(PDO::FETCH_ASSOC)) {
            $requestAllowed = false;
        }
    }
}

if (!$requestAllowed) {
    return ['error' => 'Unauthorised access', 'amount' => 0, 'result' => []];
}

$db->saveArray('user_api_requests', ['userId' => $userId, 'requestId' => $_GET['requestId']]);
$wallpaperList = new WallpaperList();
if (!empty($_GET['limit']) && filter_var($_GET['limit'], FILTER_VALIDATE_INT) !== false && $_GET['limit'] >= 1) {
    if ($_GET['limit'] > 20) {
        $limit = 20;
    } else {
        $limit = (int)$_GET['limit'];
    }
} else {
    $limit = 10;
}
$wallpaperList->setWallpapersPerPage($limit);
if (!empty($_GET['sort']) && $_GET['sort'] === 'popularity') {
    $wallpaperList->setDisplayOrder(WallpaperList::ORDER_POPULARITY);
    if (
        !empty($_GET['offset']) && filter_var($_GET['offset'], FILTER_VALIDATE_INT) !== false &&
        $_GET['offset'] >= 0
    ) {
        $wallpaperList->setOffset((int)$_GET['offset']);
    }
} elseif (!empty($_GET['sort']) && $_GET['sort'] === 'random') {
    $wallpaperList->setDisplayOrder(WallpaperList::ORDER_RANDOM);
} elseif (
    !empty($_GET['offset']) && filter_var($_GET['offset'], FILTER_VALIDATE_INT) !== false &&
    $_GET['offset'] >= 0
) {
    $wallpaperList->setOffset((int)$_GET['offset']);
}
$wallpaperList->setSearchFavouritesUserId($userId);
$wallpaperList->loadWallpapers();
$wallpapers = $wallpaperList->getWallpapers();

$returnData = ['amount' => 0, 'result' => []];
$amount     = 0;
foreach ($wallpapers as $wallpaper) {
    $amount++;
    $wallpaperData                 = [];
    $wallpaperData['title']        = $wallpaper->getName();
    $wallpaperData['imageId']      = $wallpaper->getFileId();
    $wallpaperData['downloadURL']  = $wallpaper->getDirectDownloadLink();
    $wallpaperData['fullImageURL'] = $wallpaper->getImageLink();
    if ($wallpaper->getHasResolution()) {
        $wallpaperData['dimensions'] = ['width' => $wallpaper->getWidth(), 'height' => $wallpaper->getHeight()];
    }
    $wallpaperData['authors'] = [];
    $tagList                  = $wallpaper->getAuthorTags();
    foreach ($tagList as $tag) {
        $wallpaperData['authors'][] = $tag->getName();
    }
    $wallpaperData['clicks']     = $wallpaper->getClicks();
    $wallpaperData['favourites'] = $wallpaper->getFavourites();
    $returnData['result'][]      = $wallpaperData;
}
$returnData['amount'] = $amount;

return $returnData;
