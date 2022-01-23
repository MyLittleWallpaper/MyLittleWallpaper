<?php

declare(strict_types=1);

use MyLittleWallpaper\classes\Database;
use MyLittleWallpaper\classes\output\BasicJSON;
use MyLittleWallpaper\classes\Response;

$return = [];
$db     = Database::getInstance();

$sql    = "SELECT name FROM tag_platform WHERE name LIKE ? ORDER BY name LIMIT 50";
$srch   = (!empty($_GET['term']) ? "%" . $_GET['term'] . "%" : '');
$result = $db->query($sql, [$srch]);
while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
    $return[] = [
        'id'    => $row['name'],
        'label' => $row['name'],
        'value' => $row['name'],
    ];
}

$searchResult = new BasicJSON($return);

$response = new Response($searchResult);
$response->output();
