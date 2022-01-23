<?php

declare(strict_types=1);

use MyLittleWallpaper\classes\output\WallpaperList;
use MyLittleWallpaper\classes\Response;

$wallpaper_list = new WallpaperList();
$wallpaper_list->loadSearchFromRequest();
if (CATEGORY_ID > 0) {
    $wallpaper_list->setCategory(CATEGORY_ID);
}
$wallpaper_list->loadWallpapers();
$wallpaper_list->setRenderWallpapersOnly(true);

$response = new Response($wallpaper_list);
$response->setDisableHeaderAndFooter();
$response->output();
