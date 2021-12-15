<?php
/**
 * Featured wallpaper list template.
 * @author Petri Haikonen <sharkmachine@ecxol.net>
 * @package MyLittleWallpaper
 * @subpackage DefaultTemplate
 */
// Check that correct entry point was used
if (!defined('INDEX')) exit();
global $response, $user;

echo "\n".'		<!-- Wallpaper edit dialog -->'."\n";
echo '		<div id="wallpaper_edit" style="display:none;" title="Edit wallpaper information">'."\n";
echo '			<form class="labelForm" id="wallpaper_edit_form" method="post" accept-charset="utf-8" action="'.PUB_PATH_CAT.'ajax/wallpaper_edit" style="padding-top:10px;">'."\n";
echo '				<p style="padding-bottom:10px;">Please read the submission <a href="'.PUB_PATH_CAT.'upload" target="_blank">instructions</a> before editing.</p>'."\n";
echo '				<div><label>Name:</label><input type="hidden" name="id" id="wallid" value="" /><input type="text" autocomplete="off" name="name" id="name" style="width:300px;" value=""/></div>'."\n";
echo '				<div><label>Author(s):</label><input type="text" autocomplete="off" name="author" id="author" style="width:300px;" value="" /></div>'."\n";
echo '				<div><label>Tags:</label><input type="text" autocomplete="off" name="tags" id="tags" style="width:300px;" value="" /></div>'."\n";
if ($user->getIsAdmin()) {
	echo '				<div><label>Don\'t show resolution</label><input type="checkbox" value="1" name="no_resolution" id="no_resolution" /></div>'."\n";
}
echo '				<div><label>Platform:</label><input type="text" autocomplete="off" name="platform" id="platform" style="width:300px;" value="" /></div>'."\n";
echo '				<div><label>Source URL:</label><input type="text" autocomplete="off" name="url" id="url" style="width:300px;" value="" /></div>'."\n";
echo '				<div><label style="float:left;padding-top:6px;">Edit reason:<br /><span style="font-size:11px;">Not required, but<br />please provide one</span></label><textarea id="reason" name="reason" style="width:300px;height:80px;"></textarea><br /></div>'."\n";

echo '			</form>'."\n";
echo '		</div>'."\n";

echo "\n".'		<!-- Error message dialog -->'."\n";
echo '		<div id="dialog-message" title="Error" style="display:none;">'."\n";
echo '			<p style="padding:5px 15px;font-size:13px;"></p>'."\n";
echo '		</div>'."\n\n";

echo '		<!-- Wallpapers -->'."\n";
echo '		<div class="imagelistcontainer"><div id="galleryimages" style="margin:20px;">'."\n";
if (ACTIVE_PAGE == 'featured') {
	echo '			<h1>Featured</h1>' . "\n";
	echo '			<p>This section contains wallpapers picked by the staff. All wallpapers here are at least in 1920x1080 resolution.</p>';
	echo '			<div class="wallpapercount wallpapercount_featured">' . $response->responseVariables->wallpaper_count . ' wallpaper' . ($response->responseVariables->wallpaper_count != 1 ? 's' : '') . ' found.</div>';
} elseif (ACTIVE_PAGE == 'favourites') {
	echo '			<h1>My Favourites</h1>'."\n";
} else {
	echo '			<h1>Randoms</h1>'."\n";
}

include(DOC_DIR.THEME.'/wallpaper_list_wallpapers.php');

echo '			<div style="clear:both;" id="cleardiv"></div>';
echo '		</div></div>'."\n";