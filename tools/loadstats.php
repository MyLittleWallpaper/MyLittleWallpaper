#!/usr/bin/php
<?php

declare(strict_types=1);

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

$data        = [strtotime('-5 minutes')];
$sql         = "select count(1) cnt from (select distinct ip from user_session WHERE time > ?) a";
$res         = $db->query($sql, $data);
$usersonline = 0;
while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
    $usersonline = $row['cnt'];
}
$loadavg  = sys_getloadavg();
$savedata = [
    'time'         => gmdate('Y-m-d H:i:s'),
    'avg1'         => $loadavg[0],
    'avg5'         => $loadavg[1],
    'avg15'        => $loadavg[2],
    'users_online' => $usersonline,
];
$db->saveArray('serverloadstats', $savedata);

$beforestamp = gmmktime((int)gmdate('H'), (int)gmdate('i'), 0, (int)gmdate('n'), (int)gmdate('j'), (int)gmdate('Y'));
$before      = gmdate('Y-m-d H:i:s', $beforestamp);
if (
    $beforestamp == gmmktime((int)gmdate('H'), 0, 0, (int)gmdate('n'), (int)gmdate('j'), (int)gmdate('Y')) ||
    $beforestamp == gmmktime((int)gmdate('H'), 30, 0, (int)gmdate('n'), (int)gmdate('j'), (int)gmdate('Y'))
) {
    $sql         = <<<SQL
        SELECT AVG(load_time) load_time, MAX(load_time) damax FROM page_loadtime WHERE `time` < ? AND `time` >= ?
    SQL;

    $res         = $db->query($sql, [$before, gmdate('Y-m-d H:i:s', $beforestamp - 1800)]);
    $loadtime    = 0;
    $loadtimemax = 0;
    while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
        $loadtime    = $row['load_time'];
        $loadtimemax = $row['damax'];
    }
    $savedata = ['time' => $before, 'load_time' => round($loadtime, 4), 'load_time_max' => round($loadtimemax, 4)];
    $db->saveArray('page_loadtime_avg', $savedata);
}
if ($beforestamp == gmmktime((int)gmdate('H'), 0, 0, (int)gmdate('n'), (int)gmdate('j'), (int)gmdate('Y'))) {
    $sql       = "SELECT count(*) cnt FROM page_loadtime WHERE `time` < ? AND `time` >= ?";
    $res       = $db->query($sql, [$before, gmdate('Y-m-d H:i:s', $beforestamp - 3600)]);
    $pageviews = 0;
    while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
        $pageviews = $row['cnt'];
    }
    $savedata = ['time' => $before, 'views' => $pageviews];
    $db->saveArray('pageview_stats', $savedata);
}
