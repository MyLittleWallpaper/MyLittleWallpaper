<?php

use MyLittleWallpaper\classes\output\BasicPage;
use MyLittleWallpaper\classes\Response;

define('ACTIVE_PAGE', 'software');
$softwarePage = new BasicPage();
$softwarePage->setPageTitleAddition('Software');

$html = '<div id="content"><div>';
$html .= '<h1>Software</h1>';
$html .= '<h3>Windows 7 wallpaper changer / downloader</h3>';
$html .= '<p style="font-size:20px;"><b><a href="http://mailspeise.at/MLW/" target="_blank">Download</a></b></p>';
$html .= '<p><b>Requires <a href="http://www.oracle.com/technetwork/java/javase/downloads/index.html" target="_blank">JRE 7</a> to run.</b></p>';
$html .= '<p>The program downloads a random wallpaper in given interval and changes the desktop background to downloaded wallpaper. The randoms <a href="' .
    PUB_PATH_CAT .
    'api/v1/random.json?limit=1&amp;search=major-colour%3Affffff" target="_blank">API</a> is used for wallpaper download.</p>';
$html .= '<p>This is a 3rd party program. For support or feedback, contact <a href="https://twitter.com/relgukxilef" target="_blank">@relgukxilef</a> at Twitter.</p>';

$html .= '<h3 style="margin-top:40px;">Python wallpaper changer for Linux</h3>';
$html .= '<p style="font-size:20px;"><b><a href="https://github.com/MyLittleWallpaper/WallpaperChanger" target="_blank">Download</a></b></p>';
$html .= '<p>A simple Python script for downloading a random wallpaper from My Little Wallpaper and making it the desktop background. Can be set to run periodically with crontab.</p>';
$html .= '<p><strong>Note that currently the only supported desktop environments are Unity, Gnome and Cinnamon!</strong></p>';

$html .= '<h3 style="margin-top:40px;">Variety support</h3>';
$html .= '<p>My Little Wallpaper supports Variety through Media RSS. At the moment you can only get a RSS feed for search of your choice. There is no RSS feed for favourites at the moment.</p>';
$html .= '<p style="font-size:20px;">You can find more information about Variety <b><a href="http://peterlevi.com/variety/" target="_blank">here</a></b>.</p>';

$html .= '</div></div>';

$meta = "\n" . '		<meta name="twitter:card" content="summary" />' . "\n";
$meta .= '		<meta name="twitter:description" content="My Little Wallpaper software" />' . "\n";

$softwarePage->setHtml($html);
$softwarePage->setMeta($meta);

$response = new Response($softwarePage);
$response->output();
