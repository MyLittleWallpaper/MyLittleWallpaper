#!/usr/bin/php
<?php
if (PHP_SAPI == 'cli') {
	$time_start = microtime(true);
	define('INDEX', true);
	
	require_once('../config.php');
	require_once('../lib/db.inc.php');
	$db = new Database(DBUSER, DBPASS, DBNAME, DBHOST);
	
	$res = $db->query("SELECT * FROM wallpaper WHERE deleted = 0 ORDER BY id");
	while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
		$fields = Array(Array('table' => 'tag', 'field' => 'id'));
		$join = Array(
			Array(
				'table' => 'wallpaper_tag',
				'condition' => Array(
					Array(
						Array(
							'table' => 'wallpaper_tag',
							'field' => 'tag_id',
						),
						Array(
							'table' => 'tag',
							'field' => 'id',
						),
					),
				),
			),
		);
		$conditions = [];
		$conditions[] = Array(
			'table' => 'wallpaper_tag',
			'field' => 'wallpaper_id',
			'value' => $row['id'],
			'operator' => '=',
		);
		$conditions[] = Array(
			'table' => 'tag',
			'field' => 'type',
			'value' => 'character',
			'operator' => '=',
		);
		$order = Array(Array('table' => 'tag', 'field' => 'name'));
		$taglist = $db->getList('tag', $fields, $conditions, $order, NULL, $join);
		$chartags = '';
		$count = 0;
		foreach($taglist as $tag) {
			if ($chartags != '') $chartags .= ',';
			$chartags .= $tag['id'];
			$count ++;
		}
		if ($count < 16) {
			$savedata = Array('chartags' => $chartags);
			$db->saveArray('wallpaper', $savedata, $row['id']);
			echo $row['id'].' - '.$chartags."\n";
		} else {
			echo $row['id'].' - None'."\n";
		}
		
	}
}