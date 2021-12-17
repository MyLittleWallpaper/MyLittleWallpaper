<?php

declare(strict_types=1);

use MyLittleWallpaper\classes\output\BasicPage;
use MyLittleWallpaper\classes\Response;

$errorPage = new BasicPage();
$errorPage->setPageTitleAddition('404 - Not found');
$errorPage->setHtml(
    '<h1>404 - Not Found</h1><p>[Insert funny and stupid joke here].</p>' .
    '<p>Sadly, the page you were looking for does not exist. ' .
    'To browse wallpapers, please go back to <a href="/">index</a>.</p>'
);

$response = new Response($errorPage);
$response->setHttpCode(404);
$response->output();
