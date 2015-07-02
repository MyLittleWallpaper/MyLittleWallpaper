<?php
// Check that correct entry point was used
if (!defined('INDEX')) exit();

require_once(ROOT_DIR . 'classes/output/WallpaperList.php');

DEFINE('ACTIVE_PAGE', 'index');

/*if (!empty($_COOKIE['pageless']) && $_COOKIE['pageless'] == 'true') {
	$pageless = TRUE;
} else $pageless = TRUE;*/

$wallpaper_list = new WallpaperList();
$wallpaper_list->loadSearchFromRequest();
if (CATEGORY_ID > 0) {
	$wallpaper_list->setCategory(CATEGORY_ID);
}
$wallpaper_list->loadWallpapers();

$response = new Response($wallpaper_list);
$response->output();
