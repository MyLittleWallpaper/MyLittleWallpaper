<?php

declare(strict_types=1);

use MyLittleWallpaper\classes\output\WallpaperList;
use MyLittleWallpaper\classes\Response;

const ACTIVE_PAGE = 'featured';

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
