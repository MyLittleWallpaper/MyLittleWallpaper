<?php

declare(strict_types=1);

use MyLittleWallpaper\classes\output\BasicJSON;
use MyLittleWallpaper\classes\Response;

$return = ['result' => 'OK'];

if (isset($_GET['url'])) {
    $theurl = '';
    // Check if the URL is a deviantART URL
    $url = $_GET['url'];
    if (preg_match("/^http:\\/\\/[^.]*\\.deviantart\\.com\\/art\\/.*$/", $url)) {
        $theurl = $url;
    } elseif (
        preg_match("/^http:\\/\\/[^.]*\\.deviantart\\.com\\/.*\\/d.*$/", $url) ||
        preg_match("/^http:\\/\\/fav\\.me\\/.*$/", $url)
    ) {
        if (preg_match("/^http:\\/\\/[^.]*\\.deviantart\\.com\\/.*\\/d.*$/", $url)) {
            $url = preg_replace('/^http:\\/\\/[^.]*\\.deviantart\\.com\\/.*\\/(d.*$)/', 'http://fav.me/$1', $url);
        }
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        $header = "Location: ";
        $pos    = strpos($response, $header);
        if ($pos !== false) {
            $pos    += strlen($header);
            $theurl = substr($response, $pos, strpos($response, "\r\n", $pos) - $pos);
        }
    }

    // Check if found on the database
    if ($theurl == '') {
        if (!empty($_GET['url'])) {
            $sim_res = $db->query("SELECT * FROM `wallpaper` WHERE deleted = 0 AND url = ? LIMIT 1", [$_GET['url']]);
            $in_db   = false;
            while ($indbrow = $sim_res->fetch(PDO::FETCH_ASSOC)) {
                $in_db = true;
            }
            if ($in_db) {
                $return['result'] = 'Found';
            }
        }
    } else {
        $id      = preg_replace("/^http:\\/\\/[^.]*\\.deviantart\\.com\\/art\\/.*-([0-9]*?)$/", "$1", $theurl);
        $sim_res = $db->query(
            "SELECT * FROM `wallpaper` WHERE deleted = 0 AND url like ? LIMIT 1",
            ['http://%.deviantart.com/art/%-' . $id]
        );
        $in_db   = false;
        while ($indbrow = $sim_res->fetch(PDO::FETCH_ASSOC)) {
            $in_db = true;
        }
        if ($in_db) {
            $return['result'] = 'Found';
        }
    }

    // If not found on the database, check moderation queue
    if ($return['result'] == 'OK') {
        if ($theurl == '') {
            if (!empty($_GET['url'])) {
                $sim_res = $db->query("SELECT * FROM `wallpaper_submit` WHERE url = ? LIMIT 1", [$_GET['url']]);
                $in_db   = false;
                if ($sim_res->fetch(PDO::FETCH_ASSOC)) {
                    $in_db = true;
                }
                if ($in_db) {
                    $return['result'] = 'Queue';
                }
            }
        } else {
            $id      = preg_replace("/^http:\\/\\/[^.]*\\.deviantart\\.com\\/art\\/.*-([0-9]*?)$/", "$1", $theurl);
            $sim_res = $db->query(
                "SELECT * FROM `wallpaper_submit` WHERE url like ? LIMIT 1",
                ['http://%.deviantart.com/art/%-' . $id]
            );
            $in_db = false;
            if ($sim_res->fetch(PDO::FETCH_ASSOC)) {
                $in_db = true;
            }
            if ($in_db) {
                $return['result'] = 'Queue';
            }
        }
    }
}

$duplicateCheckResult = new BasicJSON($return);

$response = new Response($duplicateCheckResult);
$response->output();
