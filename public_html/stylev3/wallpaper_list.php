<?php

use MyLittleWallpaper\classes\Format;
use MyLittleWallpaper\classes\output\WallpaperList;

global $response, $user;

echo "\n" . '		<!-- Wallpaper edit dialog -->' . "\n";
echo '		<div id="wallpaper_edit" style="display:none;" title="Edit wallpaper information">' . "\n";
echo '			<form class="labelForm" id="wallpaper_edit_form" method="post" accept-charset="utf-8" action="' . PUB_PATH_CAT . 'ajax/wallpaper_edit" style="padding-top:10px;">' . "\n";
echo '				<p style="padding-bottom:10px;">Please read the submission <a href="' . PUB_PATH_CAT . 'upload" target="_blank">instructions</a> before editing.</p>' . "\n";
echo '				<div><label>Name:</label><input type="hidden" name="id" id="wallid" value="" /><input type="text" autocomplete="off" name="name" id="name" style="width:300px;" value=""/></div>' . "\n";
echo '				<div><label>Author(s):</label><input type="text" autocomplete="off" name="author" id="author" style="width:300px;" value="" /></div>' . "\n";
echo '				<div><label>Tags:</label><input type="text" autocomplete="off" name="tags" id="tags" style="width:300px;" value="" /></div>' . "\n";
if ($user->getIsAdmin()) {
	echo '				<div><label>Don\'t show resolution</label><input type="checkbox" value="1" name="no_resolution" id="no_resolution" /></div>' . "\n";
}
echo '				<div><label>Platform:</label><input type="text" autocomplete="off" name="platform" id="platform" style="width:300px;" value="" /></div>' . "\n";
echo '				<div><label>Source URL:</label><input type="text" autocomplete="off" name="url" id="url" style="width:300px;" value="" /></div>' . "\n";
echo '				<div><label style="float:left;padding-top:6px;">Edit reason:<br /><span style="font-size:11px;">Not required, but<br />please provide one</span></label><textarea id="reason" name="reason" style="width:300px;height:80px;"></textarea><br /></div>' . "\n";

echo '			</form>' . "\n";
echo '		</div>' . "\n";

echo "\n" . '		<!-- Error message dialog -->' . "\n";
echo '		<div id="dialog-message" title="Error" style="display:none;">' . "\n";
echo '			<p style="padding:5px 15px;font-size:13px;"></p>' . "\n";
echo '		</div>' . "\n\n";

echo '		<!-- Search form -->' . "\n";
echo '		<div class="imagelistcontainer"><div id="search_container">' . "\n";
echo '			<h2>Search for wallpapers</h2>' . "\n";
echo '			<form method="get" action="' . PUB_PATH_CAT . '" accept-charset="utf-8">' . "\n";
echo '				<div id="basicSearch">' . "\n";
echo '					<div id="sort">' . "\n";
echo '						<label>Sort by: </label><br />' . "\n";
echo '						<select name="sort">' . "\n";
$sorts = array(WallpaperList::ORDER_DATE_ADDED, WallpaperList::ORDER_POPULARITY);
foreach ($sorts as $sort) {
	echo '							<option value="' . $sort . '"' . (isset($_GET['sort']) && $_GET['sort'] == $sort ? ' selected="selected"' : '') . '>' . Format::htmlEntities(WallpaperList::GET_ORDER_TITLE($sort)) . '</option>' . "\n";
}
echo '						</select>' . "\n";
echo '					</div>' . "\n";
echo '					<div id="res">' . "\n";
echo '						<label>Resolution: </label><br />' . "\n";
echo '						<select name="size">' . "\n";
echo '							<option value="0">All sizes</option>' . "\n";
$resolutions = array(WallpaperList::RESOLUTION_3840X2160, WallpaperList::RESOLUTION_2560X1600, WallpaperList::RESOLUTION_2560X1440, WallpaperList::RESOLUTION_1920X1200, WallpaperList::RESOLUTION_1920X1080, WallpaperList::RESOLUTION_1680X1050, WallpaperList::RESOLUTION_1366X768);
foreach ($resolutions as $resolution) {
	echo '							<option value="' . $resolution . '"' . (isset($_GET['size']) && $_GET['size'] == $resolution ? ' selected="selected"' : '') . '>' . WallpaperList::GET_RESOLUTION_TITLE($resolution) . '</option>' . "\n";
}
echo '						</select>' . "\n";
echo '					</div>' . "\n";
echo '					<div id="keywords">' . "\n";
echo '						<label>Tags: </label><br />' . "\n";
echo '						<input type="text" id="search" name="search" style="width:260px;" value="' . (!empty($_GET['search']) ? Format::htmlEntities(rtrim($_GET['search'], "\t\n\r\0\x0B ,") . ', ') : '') . '" />' . "\n";
echo '						<div id="taglist_link">' . "\n";
echo '							<a class="taglist" href="' . PUB_PATH_CAT . 'colours" onclick="return open_taglist(\'' . PUB_PATH_CAT . 'colours\');">Colours</a> &nbsp; <a class="taglist" href="' . PUB_PATH_CAT . 'authorlist" onclick="return open_taglist(\'' . PUB_PATH_CAT . 'authorlist\');">Author list</a> &nbsp; <a class="taglist" href="' . PUB_PATH_CAT . 'taglist" onclick="return open_taglist(\'' . PUB_PATH_CAT . 'taglist\');">Taglist</a>' . "\n";
echo '						</div>' . "\n";
echo '					</div>' . "\n";
if (!empty($_GET['searchAny']) || !empty($_GET['searchExclude'])) {
	$advancedSearch = true;
} else {
	$advancedSearch = false;
}

echo '					<input id="toggleAdvanced" type="button" value="' . ($advancedSearch ? 'Hide' : 'Show') . ' advanced search" />';
echo '				</div>' . "\n";


echo '				<div id="advancedSearch"' . (!$advancedSearch ? ' style="display:none;"' : '') . '>' . "\n";
echo '					<div id="keywordsAny">' . "\n";
echo '						<label>Tags, match any (colour searches not supported): </label><br />' . "\n";
echo '						<input type="text" id="searchAny"' . ($advancedSearch ? ' name="searchAny"' : '') . ' style="width:260px;" value="' . (!empty($_GET['searchAny']) ? Format::htmlEntities(rtrim($_GET['searchAny'], "\t\n\r\0\x0B ,") . ', ') : '') . '" />' . "\n";
echo '					</div>' . "\n";
echo '					<div id="keywordsExclude">' . "\n";
echo '						<label>Tags to exclude (colour searches not supported): </label><br />' . "\n";
echo '						<input type="text" id="searchExclude"' . ($advancedSearch ? ' name="searchExclude"' : '') . ' style="width:260px;" value="' . (!empty($_GET['searchExclude']) ? Format::htmlEntities(rtrim($_GET['searchExclude'], "\t\n\r\0\x0B ,") . ', ') : '') . '" />' . "\n";
echo '					</div>' . "\n";
echo '				</div>' . "\n";
echo '				<div id="submit">&nbsp;<br /><input type="submit" id="searchsubmit" value="Search" /> &nbsp;<input type="button" id="searchclear" value="Clear" onclick="window.location.href=\'' . PUB_PATH_CAT . '\';" /></div>' . "\n";
echo '			</form>' . "\n";
echo '			<div style="position:absolute;bottom:16px;right:16px;font-size:11px;"><img src="' . PUB_PATH . THEME . '/images/fin.png" alt="Finland" /> &nbsp; Made in Finland</div>' . "\n";
echo '			<div style="position:absolute;top:16px;right:16px;"><label id="rss">RSS' . ($response->getResponseVariables()->rss_search != '' ? ' for this search' : '') . ':</label> <a href="' . PUB_PATH_CAT . 'feed/' . $response->getResponseVariables()->rss_search . '">link</a></div>' . "\n";
echo '			<p>Account registration has been fixed. You can register an account <a href="' . PUB_PATH_CAT . 'register">here</a></p>';
echo '			<p>Tasty PHP 8.0 & MySQL 8.0 with <a href="https://www.ssllabs.com/ssltest/analyze.html?d=www.mylittlewallpaper.com" target="_blank">TLS 1.3</a></p>';
echo '			<div style="clear:both;"></div>' . "\n";
echo '		</div>' . "\n";

//echo $pager;

echo '		<div style="clear:both;"></div>' . "\n\n";
echo '		<!-- Wallpapers -->' . "\n";
echo '		<div id="galleryimages">' . "\n";
if ($response->getResponseVariables()->maxJoinAmountExceeded) {
	echo '			<div class="warning">Search too complex, please remove tags from the search.</div>';
}
echo '			<div class="wallpapercount">' . $response->getResponseVariables()->wallpaper_count . ' wallpaper' . ($response->getResponseVariables()->wallpaper_count != 1 ? 's' : '') . ' found.</div>';

include(DOC_DIR . THEME . '/wallpaper_list_wallpapers.php');

echo '			<div style="clear:both;" id="cleardiv"></div>';
echo '		</div></div>' . "\n";
