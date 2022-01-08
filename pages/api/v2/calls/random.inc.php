<?php

declare(strict_types=1);

use MyLittleWallpaper\classes\output\WallpaperList;

$wallpaperList = new WallpaperList();
$wallpaperList->loadSearchFromRequest();
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
if (CATEGORY_ID > 0) {
    $wallpaperList->setCategory(CATEGORY_ID);
}
$wallpaperList->setDisplayOrder(WallpaperList::ORDER_RANDOM);
$wallpaperList->loadWallpapers();
$wallpapers = $wallpaperList->getWallpapers();

$returnData = ['searchTags' => $wallpaperList->getSearchTagsWithType(), 'amount' => 0, 'result' => []];
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
