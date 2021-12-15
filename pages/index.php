<?php

use MyLittleWallpaper\classes\output\WallpaperList;
use MyLittleWallpaper\classes\Response;

define('ACTIVE_PAGE', 'index');

/*if (!empty($_COOKIE['pageless']) && $_COOKIE['pageless'] == 'true') {
	$pageless = true;
} else $pageless = true;*/

$wallpaper_list = new WallpaperList();
$wallpaper_list->loadSearchFromRequest();
if (CATEGORY_ID > 0) {
    $wallpaper_list->setCategory(CATEGORY_ID);
}
$wallpaper_list->loadWallpapers();

$response = new Response($wallpaper_list);
$response->output();
