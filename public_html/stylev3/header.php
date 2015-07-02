<?php
/**
 * Header template.
 * @author Petri Haikonen <sharkmachine@ecxol.net>
 * @package MyLittleWallpaper
 * @subpackage DefaultTemplate
 */
// Check that correct entry point was used
if (!defined('INDEX')) exit();
global $response, $user, $db, $redirectPageUrl;

$version = file_get_contents(ROOT_DIR . 'VERSION');

require_once(ROOT_DIR . 'classes/Navigation.php');
$navigation = new Navigation();

$javaScriptIncludes = '';
if (!empty($response->responseVariables->javaScriptFiles)) {
	foreach ($response->responseVariables->javaScriptFiles as $javaScriptFile) {
		$javaScriptIncludes .= "\n" . '		<script type="text/javascript" src="' . PUB_PATH . 'js/' . $javaScriptFile . '?v=' . urlencode($version) . '"></script>';
	}
}

/**
 * @var $category_list Category[]
 */
$category_list = $response->responseVariables->category_list;

$categoriesMeta = '';
$allCategories = '';
foreach ($category_list as $category) {
	if ($allCategories != '') {
		$allCategories .= ', ';
	}
	$allCategories .= Format::htmlEntities($category->getName());
}
if (CATEGORY_NAME == '') {
	$categoriesMeta = $allCategories;
} else {
	$categoriesMeta = CATEGORY_NAME;
}

$menu = '			<div id="menu">'."\n";
$menu .= '				<nav>'."\n";
$menu .= '					<ul>'."\n";
$activeSubMenu = array();
foreach($navigation->getNavigationElements() as $key => $menuElement) {
	$active = false;
	$subMenuItems = $menuElement->getSubMenuItems();
	if (defined('ACTIVE_PAGE')) {
		if ($key == ACTIVE_PAGE) {
			$active = true;
		} else {
			if (!empty($subMenuItems)) {
				foreach ($subMenuItems as $subMenuKey => $subMenuItem) {
					if ($subMenuKey == ACTIVE_PAGE) {
						$active = true;
					}
				}
			}
		}
	}
	if ($active && !empty($subMenuItems)) {
		$activeSubMenu = $subMenuItems;
	}
	$menu .= '						<li'.($active ? ' class="active"' : '').'>'.$menuElement.'</li>'."\n";
}
$menu .= '					</ul>'."\n";
$menu .= '				</nav>'."\n";
$menu .= '			</div>'."\n";
if (!empty($activeSubMenu)) {
	$menu .= '			<div id="subMenu">'."\n";
	$menu .= '				<nav>'."\n";
	$menu .= '					<ul>'."\n";
	foreach($activeSubMenu as $key => $menuElement) {
		$active = false;
		if (defined('ACTIVE_PAGE') && $key == ACTIVE_PAGE) {
			$active = true;
		}
		$menu .= '						<li'.($active ? ' class="active"' : '').'>'.$menuElement.'</li>'."\n";
	}
	$menu .= '					</ul>'."\n";
	$menu .= '				</nav>'."\n";
	$menu .= '			</div>'."\n";
}

echo '<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="description" content="Searchable listing of '.$allCategories.' wallpapers.">
		<meta name="keywords" content="'.$categoriesMeta.', wallpaper, wallpapers, My Little Wallpaper'.$response->responseVariables->metaTags.'">
		<link rel="shortcut icon" href="'.PUB_PATH.'favicon.ico">
		<meta name="twitter:title" content="'.$response->responseVariables->titleAddition.(CATEGORY_NAME != '' ? Format::htmlEntities(CATEGORY_NAME).' | ' : '').'My Little Wallpaper - Wallpapers are Magic">
		<meta name="twitter:domain" content="'.$_SERVER['SERVER_NAME'].'">
		<meta name="twitter:site" content="@MLWallpaper">
		<meta name="twitter:url" content="http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'].'">'.$response->responseVariables->meta.'
		'.$response->responseVariables->rss.'
		<title>'.$response->responseVariables->titleAddition.(CATEGORY_NAME != '' ? Format::htmlEntities(CATEGORY_NAME).' | ' : '').'My Little Wallpaper - Wallpapers are Magic</title>
		<link type="text/css" rel="stylesheet" href="'.PUB_PATH.'stylev3/style.css?v='.urlencode($version).'" media="all">
		<link type="text/css" rel="stylesheet" href="'.PUB_PATH.'stylev3/jquery-ui-1.9.2.css?v='.urlencode($version).'" media="all">
		<script type="text/javascript" src="'.PUB_PATH.'js/jquery-1.11.1.min.js?v='.urlencode($version).'"></script>
		<script type="text/javascript" src="'.PUB_PATH.'js/jquery.lazyload-1.9.3.min.js?v='.urlencode($version).'"></script>
		<script type="text/javascript" src="'.PUB_PATH.'js/perfect-scrollbar-0.4.10.with-mousewheel.min.js?v='.urlencode($version).'"></script>
		<script type="text/javascript" src="'.PUB_PATH.'js/jquery.tagsinput-1.3.3.js?v='.urlencode($version).'"></script>
		<script type="text/javascript" src="'.PUB_PATH.'js/vex.combined-2.0.1.js?v='.urlencode($version).'"></script>
		<script type="text/javascript" src="'.PUB_PATH.'js/jquery-ui-1.9.2.custom.min.js?v='.urlencode($version).'"></script>
		<script type="text/javascript" src="'.PUB_PATH.'js/header.js?v='.urlencode($version).'"></script>'.$javaScriptIncludes.'
		'.$response->responseVariables->javaScript.'
	</head>
	<body lang="en"'.(!empty($activeSubMenu) ? ' class="additional-padding"' : '').'>'."\n";

echo '		<header>'."\n";
echo '			<div id="categoryselect">'."\n";
echo '				Select category: <select onchange="change_category(this, \'' . $redirectPageUrl . '\');">'."\n";
echo '					<option value="0">All</option>';

foreach ($category_list as $category) {
	echo '					<option value="'.Format::htmlEntities($category->getUrlName()).'"'.(CATEGORY == $category->getUrlName() ? ' selected="selected"' : '').'>'.Format::htmlEntities($category->getName()).'</option>'."\n";
}
echo '				</select>'."\n";
echo '			</div>'."\n";
echo $menu;
echo '		</header>'."\n";
echo '<div id="loggedIn"><div><div>';
if (!$user->getIsAnonymous()) {
	echo 'Logged in as: '.Format::htmlEntities($user->getUsername());
	echo ' &nbsp; - &nbsp; <a href="'.PUB_PATH_CAT.'account">Account settings</a>';
	if ($user->getIsAdmin()) {
		$result = $db->query("SELECT COUNT(*) cnt FROM wallpaper_edit WHERE discarded = 0");
		while($row = $result->fetch(PDO::FETCH_ASSOC)) {
			$edits = $row['cnt'];
		}
		$result = $db->query("SELECT COUNT(*) cnt FROM wallpaper_submit WHERE discarded = 0");
		while($row = $result->fetch(PDO::FETCH_ASSOC)) {
			$submits = $row['cnt'];
		}
		echo '<br />'.$submits.' submitted wallpaper'.($submits != '1' ? 's' : '').' waiting. [<a href="' . PUB_PATH_CAT . 'moderate/wallpaper-queue">check queue</a>]';
		echo '<br />'.$edits.' wallpaper edit'.($submits != '1' ? 's' : '').' waiting.';
	}
	echo '<br /><br />';
}
echo '<div class="links" style="border:2px solid #bbb;background:#ddd;padding:8px;"><strong>IRC:</strong><br />
<a href="irc://irc.freenode.net/mylittlewallpaper">#mylittlewallpaper @ Freenode</a><br /><br />
<strong>Related wallpaper sites:</strong><br />
<a href="http://www.reddit.com/r/ponypapers/" target="_blank">PonyPapers @ Reddit</a><br />
<a href="http://www.reddit.com/r/TouhouWallpaper/" target="_blank">TouhouWallpaper @ Reddit</a><br />
</div>';
echo '</div></div></div>';
