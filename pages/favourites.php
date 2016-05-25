<?php
// Check that correct entry point was used
if (!defined('INDEX')) exit();
global $user;
if ($user->getIsAnonymous()) {
	require_once(ROOT_DIR . 'pages/errors/403.php');
} else {
	require_once(ROOT_DIR . 'classes/output/WallpaperList.php');

	define('ACTIVE_PAGE', 'favourites');

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
}