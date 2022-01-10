<?php

declare(strict_types=1);

use MyLittleWallpaper\classes\output\BasicJSON;
use MyLittleWallpaper\classes\Response;

global $outputData;

$apiResult = new BasicJSON($outputData);

$response = new Response($apiResult);
$response->output();
