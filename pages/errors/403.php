<?php

use MyLittleWallpaper\classes\output\BasicPage;
use MyLittleWallpaper\classes\Response;

$errorPage = new BasicPage();
$errorPage->setPageTitleAddition('403 - Forbidden');
$errorPage->setHtml(
    '<h1>403 - Forbidden</h1><p>Sadly, you do not have permissions to view this page. To browse wallpapers, please go back to <a href="/">index</a>.</p>'
);

$response = new Response($errorPage);
$response->setHttpCode(403);
$response->output();
