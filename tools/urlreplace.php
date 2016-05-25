#!/usr/bin/php
<?php
if (PHP_SAPI == 'cli') {
        $time_start = microtime(true);
        define('INDEX', true);

        require_once('../config.php');
        require_once('../lib/db.inc.php');
        $db = new Database(DBUSER, DBPASS, DBNAME, DBHOST);

        $res = $db->query("SELECT * FROM `wallpaper` WHERE url like 'http://speedymclight%';");

	while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
		$savedata = ['url' => str_replace('http://speedymclight.', 'http://evoraflux.', $row['url'])];
		print_r($savedata);
		$db->saveArray('wallpaper', $savedata, $row['id']);
	}
}