#!/usr/bin/php
<?php

use MyLittleWallpaper\classes\Database;

if (PHP_SAPI == 'cli') {
    $time_start = microtime(true);
    define('INDEX', true);

    require_once('../config.php');
    require_once('../lib/db.inc.php');
    $db = new Database(DBUSER, DBPASS, DBNAME, DBHOST);

    $res = $db->query("SELECT * FROM wallpaper WHERE deleted = 0 ORDER BY id");
    while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
        $fields       = [['table' => 'tag', 'field' => 'id']];
        $join         = [
            [
                'table'     => 'wallpaper_tag',
                'condition' => [
                    [
                        [
                            'table' => 'wallpaper_tag',
                            'field' => 'tag_id',
                        ],
                        [
                            'table' => 'tag',
                            'field' => 'id',
                        ],
                    ],
                ],
            ],
        ];
        $conditions   = [];
        $conditions[] = [
            'table'    => 'wallpaper_tag',
            'field'    => 'wallpaper_id',
            'value'    => $row['id'],
            'operator' => '=',
        ];
        $conditions[] = [
            'table'    => 'tag',
            'field'    => 'type',
            'value'    => 'character',
            'operator' => '=',
        ];
        $order        = [['table' => 'tag', 'field' => 'name']];
        $taglist      = $db->getList('tag', $fields, $conditions, $order, null, $join);
        $chartags     = '';
        $count        = 0;
        foreach ($taglist as $tag) {
            if ($chartags != '') {
                $chartags .= ',';
            }
            $chartags .= $tag['id'];
            $count++;
        }
        if ($count < 16) {
            $savedata = ['chartags' => $chartags];
            $db->saveArray('wallpaper', $savedata, $row['id']);
            echo $row['id'] . ' - ' . $chartags . "\n";
        } else {
            echo $row['id'] . ' - None' . "\n";
        }
    }
}