<?php

declare(strict_types=1);

use MyLittleWallpaper\classes\Format;
use MyLittleWallpaper\classes\output\BasicJSON;
use MyLittleWallpaper\classes\Response;

$return = [];

$sql          = <<<SQL
    SELECT name, oldname FROM tag_artist WHERE (name LIKE ? OR oldname LIKE ?) AND deleted = 0 ORDER BY name LIMIT 50
SQL;

$searchString = (!empty($_GET['term']) ? "%" . $_GET['term'] . "%" : '');
$result       = $db->query($sql, [$searchString, $searchString]);
while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
    if (!empty($row['oldname'])) {
        $desc = 'Formerly known as <b>' . Format::htmlEntities($row['oldname']) . '</b>';
    } else {
        $desc = '';
    }

    $return[] = [
        'id'    => $row['name'],
        'label' => $row['name'],
        'value' => $row['name'],
        'desc'  => $desc,
    ];
}

$searchResult = new BasicJSON($return);

$response = new Response($searchResult);
$response->output();
