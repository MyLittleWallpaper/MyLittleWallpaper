<?php
// Check that correct entry point was used
if (!defined('INDEX')) exit();

require_once(ROOT_DIR . 'classes/output/WallpaperList.php');

define('ACTIVE_PAGE', 'random');

$wallpaper_list = new WallpaperList();
$wallpaper_list->setWallpapersPerPage(15);
$wallpaper_list->setDisplayOrder(WallpaperList::ORDER_RANDOM);
if (CATEGORY_ID > 0) {
	$wallpaper_list->setCategory(CATEGORY_ID);
}
$wallpaper_list->loadWallpapers();
$wallpaper_list->setLargeWallpaperThumbs(true);
$wallpaper_list->setCustomTemplate('wallpaper_list_featured.php');
$wallpaper_list->setAjaxLoadMorePage('random');
$wallpaper_list->setPageTitleAddition('Randoms');

$response = new Response($wallpaper_list);
$response->output();