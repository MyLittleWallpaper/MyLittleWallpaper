#!/usr/bin/php
<?php

declare(strict_types=1);

use MyLittleWallpaper\classes\Database;

if (PHP_SAPI !== 'cli') {
    exit();
}

require_once('../config.php');
require_once('../lib/db.inc.php');
$db = Database::getInstance();

$res = $db->query("SELECT * FROM `wallpaper` WHERE url like 'http://speedymclight%';");

while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
    $savedata = ['url' => str_replace('http://speedymclight.', 'http://evoraflux.', $row['url'])];
    $db->saveArray('wallpaper', $savedata, $row['id']);
}
