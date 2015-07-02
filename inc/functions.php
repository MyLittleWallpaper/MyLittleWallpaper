<?php
// Check that correct entry point was used
if (!defined('INDEX')) exit();

/**
 * @todo Return boolean instead of integer
 * @param $user_agent
 * @return int returns 1 if the user agent is a bot
 */
function is_bot($user_agent) {
	// If no user agent is supplied then assume it's a bot
	if ($user_agent == "")
		return 1;

	// Array of bot strings to check for
	$bot_strings = Array("google", "bot",
		"yahoo", "spider",
		"archiver", "curl",
		"python", "nambu",
		"twitt", "perl",
		"sphere", "PEAR",
		"java", "wordpress",
		"radian", "crawl",
		"yandex", "eventbox",
		"monitor", "mechanize",
		"facebookexternal"
	);
	foreach ($bot_strings as $bot) {
		if (strpos($user_agent, $bot) !== false) {
			return 1;
		}
	}
	return 0;
}

/**
 * Returns client IP address
 * @return string
 */
function getRealIpAddr() {
	if (!empty($_SERVER['HTTP_CLIENT_IP']))   //check ip from share internet
	{
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))   //to check ip is pass from proxy
	{
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	} else {
		$ip = $_SERVER['REMOTE_ADDR'];
	}
	return $ip;
}

/**
 * @param int $a
 * @param int $b
 * @return number
 */
function GCD($a, $b) {
	while ($b != 0) {
		$remainder = $a % $b;
		$a = $b;
		$b = $remainder;
	}
	return abs($a);
}

/**
 * @param int $a
 * @param int $b
 * @return string
 */
function aspect($a, $b) {
	$gcd = GCD($a, $b);
	$a = $a / $gcd;
	$b = $b / $gcd;
	$ratio = $a . ":" . $b;

	if ($ratio != '4:3' && $ratio != '16:9' && $ratio != '16:10') {
		$difference_a = abs((4 / 3) - ($a / $b));
		$difference_b = abs((16 / 9) - ($a / $b));
		$difference_c = abs((16 / 10) - ($a / $b));

		if ($difference_a < $difference_b && $difference_a < $difference_c) {
			$ratio = '4:3';
		} elseif ($difference_b < $difference_a && $difference_b < $difference_c) {
			$ratio = '16:9';
		} else {
			$ratio = '16:10';
		}
	}
	return $ratio;
}

/**
 * Formats the filesize.
 * For example if the file size is over 1024 bytes, the result is returned in kilobytes.
 *
 * Example:
 * <code>
 * // Returns '1,00 kB'
 * FILESIZE_FORMAT(1024);
 * </code>
 * @param int $bytes File size in bytes
 * @param string $decimal Separator for the decimal point.
 * @return string The formatted filesize
 */
function FILESIZE_FORMAT($bytes, $decimal = ',') {
	if ($bytes < 1000) {
		$result = $bytes . " B";
	} elseif ($bytes / 1024 < 1000) {
		$result = number_format(round($bytes / 1024, 2), 2, $decimal, ' ') . " kB";
	} elseif (bcdiv($bytes, '1048576', 2) < 1000) {
		$result = number_format(bcdiv($bytes, '1048576', 2), 2, $decimal, ' ') . " MB";
	} else {
		$result = number_format(bcdiv($bytes, '1073741824', 2), 2, $decimal, ' ') . " GB";
	}
	return $result;
}

/**
 * Returns a formatted filesize in bytes.
 * Works with PHP ini values (like 1M) and also filesizes formatted by {@link FILESIZE_FORMAT} -function.
 * @param string $original File size presentation, for example '1M' or '14 kB'.
 * @return int The file size in bytes
 */
function FILESIZE_BYTES($original) {
	$val = preg_replace("/[^0-9\\.,kKmMgG]/", '', trim($original));
	$num = (float) str_replace(',', '.', preg_replace("/[^0-9\\.,]/", '', $val));
	$last = strtolower($val[strlen($val) - 1]);
	switch ($last) {
		case 'g':
			$num *= 1024 * 1024 * 1024;
			break;
		case 'm':
			$num *= 1024 * 1024;
			break;
		case 'k':
			$num *= 1024;
			break;
	}
	return $num;
}

/**
 * @return string
 */
function uid() {
	$php_uniq = uniqid('', true);
	$uuid = sprintf('%04x%04x-%02x', mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xff))
		. substr($php_uniq, 0, 2) . '-' . substr($php_uniq, 2, 4) . '-' . substr($php_uniq, 6, 4) . '-' . str_replace('.', '', substr($php_uniq, 10));
	return $uuid;
}

/**
 * @param string $ip
 * @param string $email
 * @return bool
 */
function check_forumspam($ip, $email = '') {
	$url = 'http://www.stopforumspam.com/api?ip=' . urlencode($ip) . (!empty($email) ? '&email=' . urlencode($email) : '') . '&f=serial';
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$data = curl_exec($ch);
	$return = unserialize($data);

	if ($return['success']) {
		if ($return['ip']['frequency'] >= 2)
			return FALSE;
	} else return FALSE;

	if (!empty($email)) {
		if ($return['email']['frequency'] >= 4)
			return FALSE;
	}
	//trigger_error($data);
	return TRUE;
}

/**
 * @param int $w
 * @param int $h
 * @param int $max_x
 * @param int $max_y
 * @return int[]
 */
function calc_thumb_size($w, $h, $max_x, $max_y) {
	if (empty($max_x)) {
		$nw = round($w / ($h / $max_y));
		$nh = $max_y;
	} elseif (empty($max_y)) {
		$nw = $max_x;
		$nh = round($h / ($w / $max_x));
	} elseif ($w / $max_x > $h / $max_y) {
		$nw = $max_x;
		$nh = round($h / ($w / $max_x));
	} else {
		$nw = round($w / ($h / $max_y));
		$nh = $max_y;
	}
	return Array($nw, $nh);
}

/**
 * @return string
 */
function generate_password() {
	$validchars[0] = "abcdfghjkmnpqrstvwxyz";
	$validchars[1] = "ABCDEFGHJKLMNPQRSTUVWXYZ";
	$validchars[2] = "23456789";

	$used = Array(FALSE, FALSE, FALSE);

	$done = FALSE;
	$generated_pass = '';
	while (!$done) {
		$which = rand(0, 2);
		$used[$which] = TRUE;
		$generated_pass .= substr($validchars[$which], rand(0, strlen($validchars[$which]) - 1), 1);
		if (strlen($generated_pass) == 8) {
			if ($used[0] && $used[1] && $used[2])
				$done = TRUE;
			else
				$generated_pass = '';
		}
	}
	return $generated_pass;
}