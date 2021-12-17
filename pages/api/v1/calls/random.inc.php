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

$returnData = ['search_tags' => $wallpaperList->getSearchTagsWithType(), 'amount' => 0];
$amount     = 0;
foreach ($wallpapers as $wallpaper) {
    $amount++;
    $wallpaperData                = [];
    $wallpaperData['title']       = $wallpaper->getName();
    $wallpaperData['imageid']     = $wallpaper->getFileId();
    $wallpaperData['downloadurl'] = $wallpaper->getDirectDownloadLink();
    if ($wallpaper->getHasResolution()) {
        $wallpaperData['dimensions'] = [
            'width'  => (string)$wallpaper->getWidth(),
            'height' => (string)$wallpaper->getHeight(),
        ];
    }
    $wallpaperData['authors'] = [];
    $tagList                  = $wallpaper->getAuthorTags();
    foreach ($tagList as $tag) {
        $wallpaperData['authors'][] = $tag->getName();
    }
    $wallpaperData['clicks'] = (string)$wallpaper->getClicks();
    $returnData['result'][]  = $wallpaperData;
}
$returnData['amount'] = (int)$amount;

return $returnData;
