<?php

declare(strict_types=1);

use MyLittleWallpaper\classes\output\WallpaperList;
use MyLittleWallpaper\classes\Response;

global $user;

if ($user->getIsAnonymous()) {
    require_once ROOT_DIR . 'pages/errors/403.php';
    return;
}

const ACTIVE_PAGE = 'favourites';

$wallpaper_list = new WallpaperList();
$wallpaper_list->setWallpapersPerPage(25);
$wallpaper_list->setSearchFavouritesUserId($user->getId());
$wallpaper_list->loadWallpapers();
$wallpaper_list->setLargeWallpaperThumbs(true);
$wallpaper_list->setCustomTemplate('wallpaper_list_featured.php');
$wallpaper_list->setAjaxLoadMorePage('favourites');
$wallpaper_list->setPageTitleAddition('Favourites');

$response = new Response($wallpaper_list);
$response->output();
