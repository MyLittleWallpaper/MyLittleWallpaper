#!/usr/bin/php
<?php

/**
 * @global Database $db
 */

use MyLittleWallpaper\classes\Format;

if (PHP_SAPI === 'cli') {
    define('DOC_DIR', __DIR__ . '/../public_html/');
    define('ROOT_DIR', __DIR__ . '/../');
    define('PUB_PATH', '/');
    $_SERVER['SERVER_PORT'] = '';
    $_SERVER['SERVER_NAME'] = '';
    $_SERVER['REMOTE_ADDR'] = '';

    $time_start = microtime(true);
    define('INDEX', true);
    require_once(ROOT_DIR . 'inc/init.php');

    $beforestamp = gmmktime(gmdate('H'), 0, 0, gmdate('n'), gmdate('j'), gmdate('Y'));
    $res         = $db->query("SELECT url, time FROM visit_log ORDER BY time");
    $cnt         = 0;
    while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
        if (strpos($row['url'], 'search=') !== false && strpos($row['url'], 'page=') === false) {
            $searchstart  = strpos($row['url'], 'search=') + 7;
            $searchend    = strpos($row['url'], '&', $searchstart);
            $searchstring = '';
            if ($searchend === false) {
                $searchstring = urldecode(substr($row['url'], $searchstart));
            } elseif ($searchend - $searchstart > 1) {
                $searchstring = urldecode(substr($row['url'], $searchstart, $searchend - $searchstart));
            }
            if ($searchstring != '') {
                $cnt++;
                if (strpos($searchstring, ',') === false) {
                    $searchparts = [trim($searchstring)];
                } else {
                    $searchparts = explode(',', $searchstring);
                    foreach ($searchparts as $k => $v) {
                        $searchparts[$k] = trim($v);
                    }
                }
                foreach ($searchparts as $searchpart) {
                    if (substr($searchpart, 0, 9) == 'platform:') {
                        $prefix = 'platform:';
                        $tag    = substr($searchpart, 9);
                        $result = $db->query(
                            "SELECT id, name FROM tag_platform WHERE name = ?",
                            [substr($searchpart, 9)]
                        );
                    } elseif (substr($searchpart, 0, 7) == 'author:') {
                        $prefix = 'author:';
                        $tag    = substr($searchpart, 7);
                        $result = $db->query("SELECT id, name FROM tag_artist WHERE name = ?", [substr($searchpart, 7)]
                        );
                    } elseif (substr($searchpart, 0, 7) == 'aspect:') {
                        $prefix = 'aspect:';
                        $tag    = substr($searchpart, 7);
                        $result = $db->query("SELECT id, name FROM tag_aspect WHERE name = ?", [substr($searchpart, 7)]
                        );
                    } else {
                        $prefix = '';
                        $tag    = $searchpart;
                        $result = $db->query("SELECT id, name FROM tag WHERE name = ?", [$searchpart]);
                    }
                    while ($tag_db = $result->fetch(PDO::FETCH_ASSOC)) {
                        if ($prefix == '') {
                            $type = 'tag';
                        } else {
                            $type = substr($prefix, 0, -1);
                        }
                        $tag      = $prefix . $tag_db['name'];
                        $savedata = ['tag' => $tag, 'type' => $type, 'time' => $row['time']];
                        $db->saveArray('tag_searchstats', $savedata);
                    }
                }
            }
        }
    }
    $db->query("TRUNCATE TABLE visit_log");

    $str = '<div style="width:390px;display:inline-block;">';
    $str .= '<h3>Most searched tags</h3>';
    $str .= '<table style="border:0;border-spacing:0;">';

    $res   = $db->query("SELECT count(*) searches FROM `tag_searchstats` WHERE type = 'tag'");
    $total = 0;
    while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
        $total = $row['searches'];
    }

    $res = $db->query(
        "SELECT tag, count(*) searches FROM `tag_searchstats` WHERE type = 'tag' GROUP BY tag ORDER BY searches DESC LIMIT 10"
    );
    while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
        $percent = round($row['searches'] / $total * 100, 2);
        $str     .= '<tr>';
        $str     .= '<td style="width:200px;border-bottom:1px solid #ccc;padding:4px 0;font-weight:bold;">' .
            Format::htmlEntities($row['tag']) . '</td>';
        $str     .= '<td style="width:125px;border-bottom:1px solid #ccc;padding:4px 0;text-align:right;">' .
            number_format($percent, 2, '.', '') . ' % &nbsp; <small>(' . number_format($row['searches'], 0, ',', ' ') .
            ')</small></td>';
        $str     .= '</tr>';
    }
    $str .= '</table>';
    $str .= '</div>';

    $str .= '<div style="width:390px;display:inline-block;">';
    $str .= '<h3>Most searched tags in the last 24 hours</h3>';
    $str .= '<table style="border:0;border-spacing:0;">';

    $after24stamp = $beforestamp - (3600 * 24);
    $res          = $db->query(
        "SELECT count(*) searches FROM `tag_searchstats` WHERE type = 'tag' AND time >= ? AND time < ?",
        [gmdate('Y-m-d H:i:s', $after24stamp), gmdate('Y-m-d H:i:s', $beforestamp)]
    );
    $total        = 0;
    while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
        $total = $row['searches'];
    }

    $res = $db->query(
        "SELECT tag, count(*) searches FROM `tag_searchstats` WHERE type = 'tag' AND time >= ? AND time < ? GROUP BY tag ORDER BY searches DESC LIMIT 10",
        [gmdate('Y-m-d H:i:s', $after24stamp), gmdate('Y-m-d H:i:s', $beforestamp)]
    );
    while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
        $percent = round($row['searches'] / $total * 100, 2);
        $str     .= '<tr>';
        $str     .= '<td style="width:200px;border-bottom:1px solid #ccc;padding:4px 0;font-weight:bold;">' .
            Format::htmlEntities($row['tag']) . '</td>';
        $str     .= '<td style="width:125px;border-bottom:1px solid #ccc;padding:4px 0;text-align:right;">' .
            number_format($percent, 2, '.', '') . ' % &nbsp; <small>(' . number_format($row['searches'], 0, ',', ' ') .
            ')</small></td>';
        $str     .= '</tr>';
    }
    $str .= '</table>';
    $str .= '</div>';
    $str .= '<div style="clear:both;height:10px;"></div>';

    $str .= '<div style="width:390px;display:inline-block;">';
    $str .= '<h3>Most searched authors</h3>';
    $str .= '<table style="border:0;border-spacing:0;">';

    $res   = $db->query("SELECT count(*) searches FROM `tag_searchstats` WHERE type = 'author'");
    $total = 0;
    while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
        $total = $row['searches'];
    }

    $res = $db->query(
        "SELECT tag, count(*) searches FROM `tag_searchstats` WHERE type = 'author' GROUP BY tag ORDER BY searches DESC LIMIT 10"
    );
    while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
        $percent = round($row['searches'] / $total * 100, 2);
        $str     .= '<tr>';
        $str     .= '<td style="width:200px;border-bottom:1px solid #ccc;padding:4px 0;font-weight:bold;">' .
            Format::htmlEntities(substr($row['tag'], 7)) . '</td>';
        $str     .= '<td style="width:125px;border-bottom:1px solid #ccc;padding:4px 0;text-align:right;">' .
            number_format($percent, 2, '.', '') . ' % &nbsp; <small>(' . number_format($row['searches'], 0, ',', ' ') .
            ')</small></td>';
        $str     .= '</tr>';
    }
    $str .= '</table>';
    $str .= '</div>';

    $str .= '<div style="width:390px;display:inline-block;">';
    $str .= '<h3>Most searched authors in the last 24 hours</h3>';
    $str .= '<table style="border:0;border-spacing:0;">';

    $after24stamp = $beforestamp - (3600 * 24);
    $res          = $db->query(
        "SELECT count(*) searches FROM `tag_searchstats` WHERE type = 'author' AND time >= ? AND time < ?",
        [gmdate('Y-m-d H:i:s', $after24stamp), gmdate('Y-m-d H:i:s', $beforestamp)]
    );
    $total        = 0;
    while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
        $total = $row['searches'];
    }

    $res = $db->query(
        "SELECT tag, count(*) searches FROM `tag_searchstats` WHERE type = 'author' AND time >= ? AND time < ? GROUP BY tag ORDER BY searches DESC LIMIT 10",
        [gmdate('Y-m-d H:i:s', $after24stamp), gmdate('Y-m-d H:i:s', $beforestamp)]
    );
    while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
        $percent = round($row['searches'] / $total * 100, 2);
        $str     .= '<tr>';
        $str     .= '<td style="width:200px;border-bottom:1px solid #ccc;padding:4px 0;font-weight:bold;">' .
            Format::htmlEntities(substr($row['tag'], 7)) . '</td>';
        $str     .= '<td style="width:125px;border-bottom:1px solid #ccc;padding:4px 0;text-align:right;">' .
            number_format($percent, 2, '.', '') . ' % &nbsp; <small>(' . number_format($row['searches'], 0, ',', ' ') .
            ')</small></td>';
        $str     .= '</tr>';
    }
    $str .= '</table>';
    $str .= '</div>';
    $str .= '<div style="clear:both;height:30px;"></div>';

    file_put_contents(ROOT_DIR . 'stats', $str);
}