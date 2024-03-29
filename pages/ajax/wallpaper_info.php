<?php

declare(strict_types=1);

use MyLittleWallpaper\classes\Database;
use MyLittleWallpaper\classes\output\BasicJSON;
use MyLittleWallpaper\classes\Response;

global $user;

$return = [];
$db     = Database::getInstance();

if (!empty($_GET['id'])) {
    $sql    = "SELECT id, name, url, no_resolution, direct_with_link FROM wallpaper WHERE id = ? LIMIT 1";
    $result = $db->query($sql, [$_GET['id']]);
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        $return['id']   = $row['id'];
        $return['name'] = $row['name'];
        $return['tags'] = '';
        $sql            = <<<SQL
            SELECT t.name FROM tag t JOIN wallpaper_tag wt ON (t.id = wt.tag_id)
            WHERE wt.wallpaper_id = ? ORDER BY t.name
        SQL;

        $res            = $db->query($sql, [$row['id']]);
        while ($tag = $res->fetch(PDO::FETCH_ASSOC)) {
            $return['tags'] .= $tag['name'];
            $return['tags'] .= ', ';
        }
        $return['author'] = '';
        $sql              = <<<SQL
            SELECT t.name FROM tag_artist t JOIN wallpaper_tag_artist wt ON (t.id = wt.tag_artist_id)
            WHERE wt.wallpaper_id = ? ORDER BY t.name
        SQL;

        $res              = $db->query($sql, [$row['id']]);
        while ($tag = $res->fetch(PDO::FETCH_ASSOC)) {
            $return['author'] .= $tag['name'];
            $return['author'] .= ', ';
        }
        $return['platform'] = '';
        $sql                = <<<SQL
            SELECT t.name FROM tag_platform t JOIN wallpaper_tag_platform wt ON (t.id = wt.tag_platform_id)
            WHERE wt.wallpaper_id = ? ORDER BY t.name
        SQL;

        $res                = $db->query($sql, [$row['id']]);
        while ($tag = $res->fetch(PDO::FETCH_ASSOC)) {
            $return['platform'] .= $tag['name'];
            $return['platform'] .= ', ';
        }
        $return['url'] = $row['url'];
        if ($user->getIsAdmin()) {
            $return['no_resolution']    = $row['no_resolution'];
            $return['direct_with_link'] = $row['direct_with_link'];
        }
    }
}

$wallpaperInfoResult = new BasicJSON($return);

$response = new Response($wallpaperInfoResult);
$response->output();
