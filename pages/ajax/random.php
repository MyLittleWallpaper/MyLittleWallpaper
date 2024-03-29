<?php

declare(strict_types=1);

use MyLittleWallpaper\classes\output\WallpaperList;
use MyLittleWallpaper\classes\Response;

$wallpaper_list = new WallpaperList();
$wallpaper_list->setWallpapersPerPage(15);
$wallpaper_list->setDisplayOrder(WallpaperList::ORDER_RANDOM);
if (CATEGORY_ID > 0) {
    $wallpaper_list->setCategory(CATEGORY_ID);
}
$wallpaper_list->loadWallpapers();
$wallpaper_list->setRenderWallpapersOnly(true);
$wallpaper_list->setLargeWallpaperThumbs(true);

$response = new Response($wallpaper_list);
$response->setDisableHeaderAndFooter();
$response->output();
