<?php
// Check that correct entry point was used
if (!defined('INDEX')) exit();
global $image, $resize, $original;

if (!empty($image)) {
	$last_modified = filemtime(ROOT_DIR . FILE_FOLDER . $image);
	if (empty($_GET['download']) && ctype_alnum(str_replace('.', '', $image)) && file_exists(ROOT_DIR . FILE_FOLDER . $image) && (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) || isset($_SERVER['HTTP_IF_NONE_MATCH']))) {
		if ($last_modified <= strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE'] . ' UTC')) {
			session_cache_limiter('private');
			session_cache_expire(60 * 24 * 7);
			session_start();

			header('HTTP/1.1 304 Not Modified');
			exit();
		}
	}

	$file = $db->getRecord('wallpaper', Array('field' => 'file', 'value' => $image));
	if (!empty($file['id']) && $file['deleted'] == '0') {
		if (file_exists(ROOT_DIR . FILE_FOLDER . $file['file'])) {
			session_cache_limiter('private');
			session_cache_expire(60 * 24 * 7);
			session_start();

			if ($original && ($file['direct_with_link'] == '1')) {
				header("Last-Modified: " . gmdate('D, d M Y H:i:s', $last_modified));
				header('Content-Type: ' . $file['mime']);
				if (!empty($_GET['download'])) {
					header('Content-Disposition: attachment; filename="' . $file['filename'] . '"');
				} else {
					header('Content-Disposition: inline; filename="' . $file['filename'] . '"');
				}
				readfile(ROOT_DIR . FILE_FOLDER . $file['file']);
			} else {
				if (file_exists(ROOT_DIR . FILE_FOLDER . 'thumb/thumb1_' . $file['file'])) {
					header('Content-Type: image/jpeg');
					if ($resize == '2') {
						readfile(ROOT_DIR . FILE_FOLDER . 'thumb/thumb2_' . $file['file']);
					} elseif ($resize == '3') {
						readfile(ROOT_DIR . FILE_FOLDER . 'thumb/thumb3_' . $file['file']);
					} else {
						readfile(ROOT_DIR . FILE_FOLDER . 'thumb/thumb1_' . $file['file']);
					}
				} else {
					$res_w = 200;
					$res_h = 150;
					exec("convert " . ROOT_DIR . FILE_FOLDER . $file['file'] . " -resize " . $res_w . "x" . $res_h . "\\> -quality 90% " . ROOT_DIR . FILE_FOLDER . "cache/" . $file['file'] . "r1.jpg");
					if ($file['height'] > 700) {
						$res_w = 640;
						$res_h = 480;
						exec("convert " . ROOT_DIR . FILE_FOLDER . $file['file'] . " -resize " . $res_w . "x" . $res_h . "\\> -quality 90% " . ROOT_DIR . FILE_FOLDER . "cache/" . $file['file'] . "r2.jpg");
						$res_w = 457;
						$res_h = 342;
						exec("convert " . ROOT_DIR . FILE_FOLDER . $file['file'] . " -resize " . $res_w . "x" . $res_h . "\\> -quality 90% " . ROOT_DIR . FILE_FOLDER . "cache/" . $file['file'] . "r3.jpg");
					} else {
						$res_w = 400;
						$res_h = 300;
						exec("convert " . ROOT_DIR . FILE_FOLDER . $file['file'] . " -resize " . $res_w . "x" . $res_h . "\\> -quality 90% " . ROOT_DIR . FILE_FOLDER . "cache/" . $file['file'] . "r2.jpg");
						exec("convert " . ROOT_DIR . FILE_FOLDER . $file['file'] . " -resize " . $res_w . "x" . $res_h . "\\> -quality 90% " . ROOT_DIR . FILE_FOLDER . "cache/" . $file['file'] . "r3.jpg");
					}

					rename(ROOT_DIR . FILE_FOLDER . "cache/" . $file['file'] . "r1.jpg", ROOT_DIR . FILE_FOLDER . "thumb/thumb1_" . $file['file']);
					rename(ROOT_DIR . FILE_FOLDER . "cache/" . $file['file'] . "r2.jpg", ROOT_DIR . FILE_FOLDER . "thumb/thumb2_" . $file['file']);
					rename(ROOT_DIR . FILE_FOLDER . "cache/" . $file['file'] . "r3.jpg", ROOT_DIR . FILE_FOLDER . "thumb/thumb3_" . $file['file']);

					header("Last-Modified: " . gmdate('D, d M Y H:i:s', $last_modified) . ' GMT');
					header('Content-Type: image/jpeg');

					if ($resize == '2') {
						readfile(ROOT_DIR . FILE_FOLDER . 'thumb/thumb2_' . $file['file']);
					} elseif ($resize == '3') {
						readfile(ROOT_DIR . FILE_FOLDER . 'thumb/thumb3_' . $file['file']);
					} else {
						readfile(ROOT_DIR . FILE_FOLDER . 'thumb/thumb1_' . $file['file']);
					}
				}
			}
		} else {
			session_start();
			require_once(ROOT_DIR . 'pages/errors/404.php');
		}
	} else {
		session_start();
		require_once(ROOT_DIR . 'pages/errors/404.php');
	}
} else {
	session_start();
	require_once(ROOT_DIR . 'pages/errors/404.php');
}