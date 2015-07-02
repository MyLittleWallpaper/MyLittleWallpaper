#!/usr/bin/php
<?php
if (PHP_SAPI == 'cli') {
	exit();
	$time_start = microtime(true);
	define('INDEX', TRUE);
	require_once(ROOT_DIR . 'inc/init.php');

	$res = $db->query("SELECT * FROM wallpaper WHERE deleted = 0 AND direct_with_link = 0 ORDER BY last_checked, id LIMIT 13");

	/**
	 * @param string $headers
	 * @param string $url
	 * @return string|bool|null
	 */
	function parseredirect($headers, $url) {
		$fields = explode("\r\n", preg_replace('/\x0D\x0A[\x09\x20]+/', ' ', $headers));
		foreach($fields as $field) {
			if (preg_match('/([^:]+): (.+)/m', $field, $match)) {
				$match[1] = preg_replace('/(?<=^|[\x09\x20\x2D])./e', 'strtoupper("\0")', strtolower(trim($match[1])));
				if( isset($retVal[$match[1]]) ) {
					$retVal[$match[1]] = array($retVal[$match[1]], $match[2]);
				} else {
					$retVal[$match[1]] = trim($match[2]);
				}
			}
		}
		if (isset($retVal['Location'])) {
			if (substr($retVal['Location'], 0, 7) === 'http://' || substr($retVal['Location'], 0, 8) === 'https://' || substr($retVal['Location'], 0, 6) === 'ftp://') {
				$tries = 0;
				$done = FALSE;
				while(!$done) {
					$http = curl_init($retVal['Location']);
					curl_setopt($http, CURLOPT_RETURNTRANSFER, TRUE);
					curl_setopt($http, CURLOPT_HEADER, TRUE);
					$result = curl_exec($http);
					$http_status = curl_getinfo($http, CURLINFO_HTTP_CODE);
					curl_close($http);
					if ($http_status == '500' || !$http_status) {
						$tries ++;
						if ($tries > 3) {
							$done = true;
						}
					} else {
						if ($http_status == '301') {
							return parseredirect($result, $retVal['Location']);
						} elseif ($http_status == '404') {
							return false;
						} else {
							return $retVal['Location'];
						}
					}
				}
			} else return $url;
		} else return $url;
		return null;
	}

	while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
		$tries = 0;
		$done = FALSE;
		while(!$done) {
			$http = curl_init($row['url']);
			curl_setopt($http, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($http, CURLOPT_HEADER, TRUE);
			$result = curl_exec($http);
			$http_status = curl_getinfo($http, CURLINFO_HTTP_CODE);
			curl_close($http);
			if ($http_status == '301' || $http_status == '404') {
				echo str_pad($row['id'], 5, " ", STR_PAD_LEFT).' '.$row['url'].' '.$http_status."\n";
			}
			if ($http_status && $http_status != '500') {
				if ($http_status == '301') {
					$new_location = parseredirect($result, $row['url']);
					if ($new_location === FALSE) {
						$savedata = Array('status_check' => '404', 'deleted' => 1, 'last_checked' => gmdate('Y-m-d H:i:s'));
						echo '      NEWLOC 404'."\n";
					} else {
						$savedata = Array('status_check' => '200', 'url' => $new_location, 'last_checked' => gmdate('Y-m-d H:i:s'));
						echo '      NEWLOC '.$new_location."\n";
					}
					$db->saveArray('wallpaper', $savedata, $row['id']);
					$done = TRUE;
				} elseif ($http_status == '404') {
					$savedata = Array('status_check' => $http_status, 'deleted' => 1, 'last_checked' => gmdate('Y-m-d H:i:s'));
					$db->saveArray('wallpaper', $savedata, $row['id']);
					$done = TRUE;
				} else {
					$savedata = Array('status_check' => $http_status, 'last_checked' => gmdate('Y-m-d H:i:s'));
					$db->saveArray('wallpaper', $savedata, $row['id']);
					$done = TRUE;
				}
			}
			$tries ++;
			if ($tries > 3) $done = TRUE;
		}
		sleep(1);
	}
}
