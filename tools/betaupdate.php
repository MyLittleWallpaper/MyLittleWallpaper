#!/usr/bin/php
<?php

declare(strict_types=1);

use MyLittleWallpaper\classes\Database;

if (PHP_SAPI !== 'cli') {
    exit();
}

require_once('../vendor/autoload.php');
require_once('../config.php');
require_once('../lib/db.inc.php');
$db = Database::getInstance();

$db->query("TRUNCATE TABLE tag_artist");
$db->query("TRUNCATE TABLE wallpaper_tag_artist");
$db->query("TRUNCATE TABLE wallpaper_tag_aspect");
$result  = $db->query("SELECT * FROM wallpaper");
$artists = [];
$aspects = [
    '1' => '16:9',
    '2' => '16:10',
    '3' => '4:3',
];
while ($wallpaper = $result->fetch(PDO::FETCH_ASSOC)) {
    $a = $wallpaper['width'];
    $b = $wallpaper['height'];

    while ($b != 0) {
        $remainder = $a % $b;
        $a         = $b;
        $b         = $remainder;
    }
    $gcd = abs($a);

    $a     = $a / $gcd;
    $b     = $b / $gcd;
    $ratio = $a . ":" . $b;

    if ($ratio != '4:3' && $ratio != '16:9' && $ratio != '16:10') {
        $difference_a = abs((4 / 3) - ($a / $b));
        $difference_b = abs((16 / 9) - ($a / $b));
        $difference_c = abs((16 / 10) - ($a / $b));

        if ($difference_a < $difference_b && $difference_a < $difference_c) {
            $ratio = '4:3';
        } elseif ($difference_b < $difference_a && $difference_b < $difference_c) {
            $ratio = '16:9';
        } else {
            $ratio = '16:10';
        }
    }
    $ratio_id = array_search($ratio, $aspects);
    $db->query(
        "INSERT INTO wallpaper_tag_aspect (tag_aspect_id, wallpaper_id) VALUES (?, ?)",
        [$ratio_id, $wallpaper['id']]
    );

    if (!in_array($wallpaper['author'], $artists)) {
        $db->query("INSERT INTO tag_artist (name) VALUES (?)", [$wallpaper['author']]);
        $res = $db->query("SELECT LAST_INSERT_ID() id");
        while ($lastid = $res->fetch(PDO::FETCH_ASSOC)) {
            $artists[$lastid['id']] = $wallpaper['author'];
        }
        $res->closeCursor();
    }

    $artist_id = array_search($wallpaper['author'], $artists);
    $db->query(
        "INSERT INTO wallpaper_tag_artist (tag_artist_id, wallpaper_id) VALUES (?, ?)",
        [$artist_id, $wallpaper['id']]
    );

    echo $wallpaper['id'] . ' - ';
    echo $ratio . ' - ';
    echo $artist_id . ' - ';

    echo $wallpaper['name'];
    echo "\n";
}
$result->closeCursor();
