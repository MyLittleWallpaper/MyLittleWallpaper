<?php
// Check that correct entry point was used
if (!defined('INDEX')) exit();

require_once(ROOT_DIR . 'classes/output/WallpaperList.php');

define('ACTIVE_PAGE', 'featured');

$wallpaper_list = new WallpaperList();
$wallpaper_list->searchAddTag('Featured');
$wallpaper_list->setWallpapersPerPage(25);
if (CATEGORY_ID > 0) {
	$wallpaper_list->setCategory(CATEGORY_ID);
}
$wallpaper_list->loadWallpapers();
$wallpaper_list->setLargeWallpaperThumbs(true);
$wallpaper_list->setCustomTemplate('wallpaper_list_featured.php');
$wallpaper_list->setAjaxLoadMorePage('featured');

$response = new Response($wallpaper_list);
$response->output();