<?php

use MyLittleWallpaper\classes\output\BasicJSON;
use MyLittleWallpaper\classes\Response;

global $output_data;

$apiResult = new BasicJSON($output_data);

$response = new Response($apiResult);
$response->output();
