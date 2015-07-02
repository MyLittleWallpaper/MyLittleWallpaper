<?php
// Check that correct entry point was used
if (!defined('INDEX')) exit();
global $output_data;

require_once(ROOT_DIR . 'classes/output/BasicJSON.php');

$apiResult = new BasicJSON($output_data);

$response = new Response($apiResult);
$response->output();