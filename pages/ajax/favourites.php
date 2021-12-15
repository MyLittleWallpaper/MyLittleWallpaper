<?php

use MyLittleWallpaper\classes\output\WallpaperList;
use MyLittleWallpaper\classes\Response;

global $user;

if ($user->getIsAnonymous()) {
    require_once(ROOT_DIR . 'pages/errors/403.php');
} else {
    $wallpaper_list = new WallpaperList();
    $wallpaper_list->setWallpapersPerPage(25);
    $wallpaper_list->setSearchFavouritesUserId($user->getId());
    if (isset($_GET['page']) && filter_var($_GET['page'], FILTER_VALIDATE_INT) !== false && $_GET['page'] > 1) {
        $wallpaper_list->setPageNumber((int)$_GET['page']);
    }
    $wallpaper_list->loadWallpapers();
    $wallpaper_list->setRenderWallpapersOnly(true);
    $wallpaper_list->setLargeWallpaperThumbs(true);

    $response = new Response($wallpaper_list);
    $response->setDisableHeaderAndFooter();
    $response->output();
}
