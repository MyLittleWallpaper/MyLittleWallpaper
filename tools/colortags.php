#!/usr/bin/php
<?php

declare(strict_types=1);

use MyLittleWallpaper\classes\GetCommonColours;

if (PHP_SAPI !== 'cli') {
    exit();
}

const DOC_DIR  = __DIR__ . '/../public_html/';
const ROOT_DIR = __DIR__ . '/../';
const PUB_PATH = '/';
$_SERVER['SERVER_PORT'] = '';
$_SERVER['SERVER_NAME'] = '';
$_SERVER['REMOTE_ADDR'] = '';

require_once(ROOT_DIR . 'inc/init.php');

$db->query("TRUNCATE TABLE wallpaper_tag_colour");

$res = $db->query("SELECT * FROM wallpaper WHERE deleted = 0 ORDER BY id DESC");

$clrs = new GetCommonColours();
while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
    $result = $clrs->getColours('../files/' . $row['file']);
    echo $row['id'] . ' ' . $row['name'] . "\n";
    foreach ($result as $cl) {
        $colours  = array_keys($cl['colours']);
        $col      = $colours[0];
        $amnt     = $cl['percent'];
        $tag_r    = base_convert(substr($col, 0, 2), 16, 10);
        $tag_g    = base_convert(substr($col, 2, 2), 16, 10);
        $tag_b    = base_convert(substr($col, 4, 2), 16, 10);
        $savedata = [
            'wallpaper_id' => $row['id'],
            'tag_r'        => $tag_r,
            'tag_g'        => $tag_g,
            'tag_b'        => $tag_b,
            'tag_colour'   => $col,
            'amount'       => round($amnt, 2),
        ];
        $db->saveArray('wallpaper_tag_colour', $savedata);
    }
}
