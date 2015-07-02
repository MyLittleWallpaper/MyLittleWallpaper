#!/usr/bin/php
<?php
if (PHP_SAPI == 'cli') {
	function GCD($a, $b) {  
		while ($b != 0) {
			$remainder = $a % $b;  
			$a = $b;  
			$b = $remainder;  
		}  
		return abs ($a);  
	}

	define("DBNAME", 'my_little_wallpaper');
	define("DBUSER", 'mlw');
	define("DBPASS", 'Ads2fudali4');
	define("DBHOST", 'localhost');

	$mdb = mysql_connect(DBHOST, DBUSER, DBPASS);
	mysql_select_db(DBNAME);
	
	mysql_query("TRUNCATE TABLE tag_artist");
	mysql_query("TRUNCATE TABLE wallpaper_tag_artist");
	mysql_query("TRUNCATE TABLE wallpaper_tag_aspect");
	$result = mysql_query("SELECT * FROM wallpaper");
	$artists = Array();
	$aspects = Array(
		'1' => '16:9',
		'2' => '16:10',
		'3' => '4:3',
	);
	while($wallpaper = mysql_fetch_assoc($result)) {
		$a = $wallpaper['width'];
		$b = $wallpaper['height'];
		$gcd = GCD($a, $b);  
		$a = $a/$gcd;  
		$b = $b/$gcd;  
		$ratio = $a . ":" . $b;  
		
		if ($ratio != '4:3' && $ratio != '16:9' && $ratio != '16:10') {
			$difference_a = abs((4/3) - ($a/$b));
			$difference_b = abs((16/9) - ($a/$b));
			$difference_c = abs((16/10) - ($a/$b));
			
			if ($difference_a < $difference_b && $difference_a < $difference_c) {
				$ratio = '4:3';
			} elseif ($difference_b < $difference_a && $difference_b < $difference_c) {
				$ratio = '16:9';
			} else {
				$ratio = '16:10';
			}
		}
		$ratio_id = array_search($ratio, $aspects);
		mysql_query("INSERT INTO wallpaper_tag_aspect (tag_aspect_id, wallpaper_id) VALUES ('".mysql_real_escape_string($ratio_id)."', '".mysql_real_escape_string($wallpaper['id'])."')");
		
		if (!in_array($wallpaper['author'], $artists)) {
			mysql_query("INSERT INTO tag_artist (name) VALUES ('".mysql_real_escape_string($wallpaper['author'])."')");
			$res = mysql_query("SELECT LAST_INSERT_ID() id");
			while($lastid = mysql_fetch_assoc($res)) {
				$artists[$lastid['id']] = $wallpaper['author'];
			}
			mysql_free_result($res);
		}
		
		$artist_id = array_search($wallpaper['author'], $artists);
		mysql_query("INSERT INTO wallpaper_tag_artist (tag_artist_id, wallpaper_id) VALUES ('".mysql_real_escape_string($artist_id)."', '".mysql_real_escape_string($wallpaper['id'])."')");
		
		echo $wallpaper['id'].' - ';
		echo $ratio.' - ';
		echo $artist_id.' - ';
		
		echo $wallpaper['name'];
		echo "\n";
	}
	mysql_free_result($result);
	
	mysql_close();
}
?>
