<?php
// Check that correct entry point was used
if (!defined('INDEX')) exit();
global $user;
if ($user->getIsAnonymous()) {
	require_once(ROOT_DIR . 'pages/errors/403.php');
} else {
	require_once(ROOT_DIR . 'classes/output/WallpaperList.php');

	$wallpaper_list = new WallpaperList();
	$wallpaper_list->setWallpapersPerPage(25);
	$wallpaper_list->setSearchFavouritesUserId($user->getId());
	if (isset($_GET['page']) && filter_var($_GET['page'], FILTER_VALIDATE_INT) !== FALSE && $_GET['page'] > 1) {
		$wallpaper_list->setPageNumber((int) $_GET['page']);
	}
	$wallpaper_list->loadWallpapers();
	$wallpaper_list->setRenderWallpapersOnly(true);
	$wallpaper_list->setLargeWallpaperThumbs(true);

	$response = new Response($wallpaper_list);
	$response->setDisableHeaderAndFooter();
	$response->output();
}