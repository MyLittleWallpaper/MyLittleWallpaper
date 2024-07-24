<?php

declare(strict_types=1);

use MyLittleWallpaper\classes\output\BasicPage;
use MyLittleWallpaper\classes\Response;

const ACTIVE_PAGE = 'software';
$softwarePage = new BasicPage();
$softwarePage->setPageTitleAddition('Software');

$html = '<div id="content"><div>';
$html .= '<h1>Software</h1>';

$html .= '<h3 style="margin-top:40px;">Media RSS support</h3>';
$html .= '<p>Any software that supports Media RSS should work, just copy & paste the RSS link to the software.</p>';

$html .= '<h3 style="margin-top:40px;">Variety support</h3>';
$html .= '<p>My Little Wallpaper supports Variety through Media RSS. At the moment you can only get a RSS feed ' .
    'for search of your choice.</p>';
$html .= '<p style="font-size:20px;">You can find more information about Variety <b>' .
    '<a href="http://peterlevi.com/variety/" target="_blank">here</a></b>.</p>';

$html .= '<h3 style="margin-top:40px;">Python wallpaper changer for Linux</h3>';
$html .= '<p style="font-size:20px;"><b>' .
    '<a href="https://github.com/MyLittleWallpaper/WallpaperChanger" target="_blank">Download</a></b></p>';
$html .= '<p>A simple Python script for downloading a random wallpaper from My Little Wallpaper and making it ' .
    'the desktop background. Can be set to run periodically with crontab.</p>';
$html .= '<p><strong>Note that currently the only supported desktop environments are Unity, ' .
    'Gnome and Cinnamon!</strong></p>';

$html .= '</div></div>';

$meta = "\n" . '		<meta name="twitter:card" content="summary" />' . "\n";
$meta .= '		<meta name="twitter:description" content="My Little Wallpaper software" />' . "\n";

$softwarePage->setHtml($html);
$softwarePage->setMeta($meta);

$response = new Response($softwarePage);
$response->output();
