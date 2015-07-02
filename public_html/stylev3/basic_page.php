<?php
/**
 * Basic page template.
 * @author Petri Haikonen <sharkmachine@ecxol.net>
 * @package MyLittleWallpaper
 * @subpackage DefaultTemplate
 */
// Check that correct entry point was used
if (!defined('INDEX')) exit();
global $response;

echo '<div class="basic_page_container">';
echo $response->responseVariables->html;
echo '</div>';