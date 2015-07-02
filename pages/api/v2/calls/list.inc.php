<?php
// Check that correct entry point was used
if (!defined('INDEX')) exit();

require_once(ROOT_DIR . 'classes/output/WallpaperList.php');

if (isset($_GET['debug'])) {
	$returnData = Array('generateTime' => 0, 'searchTags' => array(), 'amount' => 0, 'offset' => 0, 'searchTotal' => 0, 'result' => Array());
} else {
	$returnData = Array('searchTags' => array(), 'amount' => 0, 'offset' => 0, 'searchTotal' => 0, 'result' => Array());
}

$wallpaperList = new WallpaperList();
$wallpaperList->loadSearchFromRequest();
if (!empty($_GET['limit']) && filter_var($_GET['limit'], FILTER_VALIDATE_INT) !== FALSE && $_GET['limit'] >= 1) {
	if ($_GET['limit'] > 100) $limit = 100; else $limit = (int) $_GET['limit'];
} else $limit = 10;
$wallpaperList->setWallpapersPerPage($limit);
if (!empty($_GET['offset']) && filter_var($_GET['offset'], FILTER_VALIDATE_INT) !== FALSE && $_GET['offset'] >= 0) {
	$wallpaperList->setOffset($_GET['offset']);
}
if (CATEGORY_ID > 0) {
	$wallpaperList->setCategory(CATEGORY_ID);
}

$wallpaperList->loadWallpapers();
$wallpapers = $wallpaperList->getWallpapers();

$returnData['searchTotal'] = $wallpaperList->getWallpaperCount();
$returnData['offset'] = $wallpaperList->getOffset();

$amount = 0;
foreach($wallpapers as $wallpaper) {
	$amount ++;
	$wallpaperData = Array();
	$wallpaperData['title'] = $wallpaper->getName();
	$wallpaperData['imageId'] = $wallpaper->getFileId();
	$wallpaperData['downloadURL'] = $wallpaper->getDirectDownloadLink();
	$wallpaperData['fullImageURL'] = $wallpaper->getImageLink();
	if ($wallpaper->getHasResolution()) {
		$wallpaperData['dimensions'] = Array('width' => $wallpaper->getWidth(), 'height' => $wallpaper->getHeight());
	}
	$wallpaperData['authors'] = Array();
	$tagList = $wallpaper->getAuthorTags();
	foreach($tagList as $tag) {
		$wallpaperData['authors'][] = $tag->getName();
	}
	$wallpaperData['clicks'] = $wallpaper->getClicks();
	$wallpaperData['favourites'] = $wallpaper->getFavourites();
	$returnData['result'][] = $wallpaperData;
}
$returnData['amount'] = (int) $amount;

return $returnData;