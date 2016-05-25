<?php
// Check that correct entry point was used
if (!defined('INDEX')) exit();

require_once(ROOT_DIR . 'classes/output/WallpaperList.php');

if (isset($_GET['debug'])) {
	$returnData = Array('generate_time' => 0, 'search_tags' => [], 'amount' => 0, 'offset' => 0, 'search_total' => 0, 'result' => []);
} else {
	$returnData = Array('search_tags' => [], 'amount' => 0, 'offset' => 0, 'search_total' => 0, 'result' => []);
}

$wallpaperList = new WallpaperList();
$wallpaperList->loadSearchFromRequest();
if (!empty($_GET['limit']) && filter_var($_GET['limit'], FILTER_VALIDATE_INT) !== false && $_GET['limit'] >= 1) {
	if ($_GET['limit'] > 100) $limit = 100; else $limit = (int) $_GET['limit'];
} else $limit = 10;
$wallpaperList->setWallpapersPerPage($limit);
if (!empty($_GET['offset']) && filter_var($_GET['offset'], FILTER_VALIDATE_INT) !== false && $_GET['offset'] >= 0) {
	$wallpaperList->setOffset($_GET['offset']);
}
if (CATEGORY_ID > 0) {
	$wallpaperList->setCategory(CATEGORY_ID);
}
$wallpaperList->loadWallpapers();
$wallpapers = $wallpaperList->getWallpapers();

$returnData['search_total'] = $wallpaperList->getWallpaperCount();
$returnData['offset'] = $wallpaperList->getOffset();

$amount = 0;
foreach($wallpapers as $wallpaper) {
	$amount ++;
	$wallpaperData = [];
	$wallpaperData['title'] = $wallpaper->getName();
	$wallpaperData['imageid'] = $wallpaper->getFileId();
	$wallpaperData['downloadurl'] = $wallpaper->getDirectDownloadLink();
	if ($wallpaper->getHasResolution()) {
		$wallpaperData['dimensions'] = Array('width' => (string) $wallpaper->getWidth(), 'height' => (string) $wallpaper->getHeight());
	}
	$wallpaperData['authors'] = [];
	$tagList = $wallpaper->getAuthorTags();
	foreach($tagList as $tag) {
		$wallpaperData['authors'][] = $tag->getName();
	}
	$wallpaperData['clicks'] = (string) $wallpaper->getClicks();
	$returnData['result'][] = $wallpaperData;
}
$returnData['amount'] = (int) $amount;

return $returnData;