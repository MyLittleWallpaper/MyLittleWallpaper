#!/usr/bin/php
<?php
if (PHP_SAPI == 'cli') {
    define('DOC_DIR', __DIR__ . '/../public_html/');
    define('ROOT_DIR', __DIR__ . '/../');
    define('PUB_PATH', '/');
    $_SERVER['SERVER_PORT'] = '';
    $_SERVER['SERVER_NAME'] = '';
    $_SERVER['REMOTE_ADDR'] = '';

    $time_start = microtime(true);
    define('INDEX', true);
    require_once(ROOT_DIR . 'classes/Format.php');
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

    $beforestamp = gmmktime(gmdate('H'), gmdate('i'), 0, gmdate('n'), gmdate('j'), gmdate('Y'));
    $before      = gmdate('Y-m-d H:i:s', $beforestamp);
    $afterstamp  = $beforestamp - 300;
    $after       = gmdate('Y-m-d H:i:s', $afterstamp);
    if (
        $beforestamp == gmmktime(gmdate('H'), 0, 0, gmdate('n'), gmdate('j'), gmdate('Y')) ||
        $beforestamp == gmmktime(gmdate('H'), 30, 0, gmdate('n'), gmdate('j'), gmdate('Y'))
    ) {
        $sql         = "SELECT AVG(load_time) load_time, MAX(load_time) damax FROM page_loadtime WHERE `time` < ? AND `time` >= ?";
        $res         = $db->query($sql, [$before, gmdate('Y-m-d H:i:s', $beforestamp - 1800)]);
        $loadtime    = 0;
        $loadtimemax = 0;
        while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
            $loadtime    = $row['load_time'];
            $loadtimemax = $row['damax'];
        }
        //$db->query("DELETE FROM page_loadtime WHERE `time` < ?", Array($before));
        $savedata = ['time' => $before, 'load_time' => round($loadtime, 4), 'load_time_max' => round($loadtimemax, 4)];
        $db->saveArray('page_loadtime_avg', $savedata);
    }
    if ($beforestamp == gmmktime(gmdate('H'), 0, 0, gmdate('n'), gmdate('j'), gmdate('Y'))) {
        $sql       = "SELECT count(*) cnt FROM page_loadtime WHERE `time` < ? AND `time` >= ?";
        $res       = $db->query($sql, [$before, gmdate('Y-m-d H:i:s', $beforestamp - 3600)]);
        $pageviews = 0;
        while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
            $pageviews = $row['cnt'];
        }
        $savedata = ['time' => $before, 'views' => $pageviews];
        $db->saveArray('pageview_stats', $savedata);
    }
}