<?php

declare(strict_types=1);

use MyLittleWallpaper\classes\Format;
use MyLittleWallpaper\classes\Navigation\Navigation;

global $response, $user, $db, $redirectPageUrl;

$version = file_get_contents(ROOT_DIR . 'VERSION');

$navigation = new Navigation();

$javaScriptIncludes = '';
if (!empty($response->getResponseVariables()->javaScriptFiles)) {
    foreach ($response->getResponseVariables()->javaScriptFiles as $javaScriptFile) {
        $javaScriptIncludes .= "\n" . '		<script type="text/javascript" src="' . PUB_PATH . 'js/' .
            $javaScriptFile . '?v=' . urlencode($version) . '"></script>';
    }
}

$category_list = $response->getResponseVariables()->category_list;

$allCategories  = '';
foreach ($category_list as $category) {
    if ($allCategories !== '') {
        $allCategories .= ', ';
    }
    $allCategories .= Format::htmlEntities($category->getName());
}

// @todo fix
// phpcs:disable Generic.Files.LineLength.TooLong
$menu          = '			<div id="menu">' . "\n";
$menu          .= '				<nav>' . "\n";
$menu          .= '					<ul>' . "\n";
$activeSubMenu = [];
foreach ($navigation->getNavigationElements() as $key => $menuElement) {
    $active       = false;
    $subMenuItems = $menuElement->getSubMenuItems();
    if (defined('ACTIVE_PAGE')) {
        if ($key == ACTIVE_PAGE) {
            $active = true;
        } elseif (!empty($subMenuItems)) {
            foreach ($subMenuItems as $subMenuKey => $subMenuItem) {
                if ($subMenuKey == ACTIVE_PAGE) {
                    $active = true;
                }
            }
        }
    }
    if ($active && !empty($subMenuItems)) {
        $activeSubMenu = $subMenuItems;
    }
    $menu .= '						<li' . ($active ? ' class="active"' : '') . '>' . $menuElement . '</li>' . "\n";
}
$menu .= '					</ul>' . "\n";
$menu .= '				</nav>' . "\n";
$menu .= '			</div>' . "\n";
if (!empty($activeSubMenu)) {
    $menu .= '			<div id="subMenu">' . "\n";
    $menu .= '				<nav>' . "\n";
    $menu .= '					<ul>' . "\n";
    foreach ($activeSubMenu as $key => $menuElement) {
        $active = false;
        if (defined('ACTIVE_PAGE') && $key == ACTIVE_PAGE) {
            $active = true;
        }
        $menu .= '						<li' . ($active ? ' class="active"' : '') . '>' . $menuElement . '</li>' .
            "\n";
    }
    $menu .= '					</ul>' . "\n";
    $menu .= '				</nav>' . "\n";
    $menu .= '			</div>' . "\n";
}

$titleAddition = '';
if (defined('ACTIVE_PAGE') && in_array(ACTIVE_PAGE, ['index', 'featured', 'random', 'download'])) {
    $titleAddition .= ' wallpapers';
}

echo '<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="description" content="Searchable listing of ' . $allCategories . ' wallpapers.">
		<link rel="shortcut icon" href="' . PUB_PATH . 'favicon.ico">
		<meta name="twitter:title" content="' . $response->getResponseVariables()->titleAddition .
    (CATEGORY_NAME !== '' ? Format::htmlEntities(CATEGORY_NAME) . $titleAddition . ' | ' : '') .
    'My Little Wallpaper - Wallpapers are Magic">
		<meta name="twitter:domain" content="' . $_SERVER['SERVER_NAME'] . '">
		<meta name="twitter:url" content="http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'] . '">' .
    $response->getResponseVariables()->meta . '
		' . $response->getResponseVariables()->rss . '
		<title>' . $response->getResponseVariables()->titleAddition .
    (CATEGORY_NAME !== '' ? Format::htmlEntities(CATEGORY_NAME) . $titleAddition . ' | ' : '') .
    'My Little Wallpaper - Wallpapers are Magic</title>
		<link type="text/css" rel="stylesheet" href="' . PUB_PATH . 'stylev3/style.css?v=' . urlencode($version) . '-3" media="all">
		<link type="text/css" rel="stylesheet" href="' . PUB_PATH . 'stylev3/jquery-ui.structure-1.13.0.min.css?v=' .
    urlencode($version) . '" media="all">
		<link type="text/css" rel="stylesheet" href="' . PUB_PATH . 'stylev3/jquery-ui.theme-1.13.0.css?v=' .
    urlencode($version) . '-2" media="all">
		<script type="text/javascript" src="' . PUB_PATH . 'js/jquery-3.5.1.min.js?v=' . urlencode($version) . '"></script>
		<script type="text/javascript" src="' . PUB_PATH . 'js/jquery-migrate-3.3.2.min.js?v=' . urlencode($version) . '"></script>
		<script type="text/javascript" src="' . PUB_PATH . 'js/jquery.tagsinput-1.3.3.js?v=' . urlencode($version) . '"></script>
		<script type="text/javascript" src="' . PUB_PATH . 'js/vex.combined-2.3.3.min.js?v=' . urlencode($version) . '"></script>
		<script type="text/javascript" src="' . PUB_PATH . 'js/jquery-ui-1.13.0.min.js?v=' . urlencode($version) . '"></script>
		<script type="text/javascript" src="' . PUB_PATH . 'js/header.js?v=' . urlencode($version) . '-2"></script>' .
    $javaScriptIncludes . '
		' . $response->getResponseVariables()->javaScript . '
	</head>
	<body lang="en"' . (!empty($activeSubMenu) ? ' class="additional-padding"' : '') . '>' . "\n";

echo '		<header>' . "\n";
echo '			<div id="categoryselect">' . "\n";
echo '				Select category: <select onchange="change_category(this, \'' . $redirectPageUrl . '\');">' . "\n";
echo '					<option value="0">All</option>';

foreach ($category_list as $category) {
    echo '					<option value="' . Format::htmlEntities($category->getUrlName()) . '"' .
        (CATEGORY == $category->getUrlName() ? ' selected="selected"' : '') . '>' .
        Format::htmlEntities($category->getName()) . '</option>' . "\n";
}
echo '				</select>' . "\n";
echo '			</div>' . "\n";
echo $menu;
echo '		</header>' . "\n";
echo '<div id="loggedIn"><div><div>';
if (!$user->getIsAnonymous()) {
    echo 'Logged in as: ' . Format::htmlEntities($user->getUsername());
    echo ' &nbsp; - &nbsp; <a href="' . PUB_PATH_CAT . 'account">Account settings</a>';
    if ($user->getIsAdmin()) {
        $result = $db->query("SELECT COUNT(*) cnt FROM wallpaper_edit WHERE discarded = 0");
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $edits = $row['cnt'];
        }
        $result = $db->query("SELECT COUNT(*) cnt FROM wallpaper_submit WHERE discarded = 0");
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $submits = $row['cnt'];
        }
        echo '<br />' . $submits . ' submitted wallpaper' . ($submits != '1' ? 's' : '') . ' waiting. [<a href="' .
            PUB_PATH_CAT . 'moderate/wallpaper-queue">check queue</a>]';
        echo '<br />' . $edits . ' wallpaper edit' . ($submits != '1' ? 's' : '') . ' waiting.';
    }
    echo '<br /><br />';
}
echo '<div class="links" style="border:2px solid #bbb;background:#ddd;padding:8px;"><strong>GitHub:</strong><br />
<a href="https://github.com/MyLittleWallpaper/MyLittleWallpaper" target="_blank">Issue tracker and source code</a><br /><br />
<strong>Discord:</strong><br />
<a href="https://discord.gg/GWVG7Bu">Discord server</a><br /><br />
<strong>Related wallpaper sites:</strong><br />
<a href="http://www.reddit.com/r/ponypapers/" target="_blank">PonyPapers @ Reddit</a><br />
<a href="http://www.reddit.com/r/TouhouWallpaper/" target="_blank">TouhouWallpaper @ Reddit</a><br />
</div>';
echo '</div></div></div>';
