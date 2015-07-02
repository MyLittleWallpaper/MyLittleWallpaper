<?php
// Check that correct entry point was used
if (!defined('INDEX')) exit();
global $category, $redirectOk, $startSession, $pageType, $page;

$time_start = microtime(true);
define('THEME', 'stylev3');

// Server protocol
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
define('PROTOCOL', $protocol);

// Site domain (for example www.mylittlewallpaper.com)
define('SITE_DOMAIN', $_SERVER['SERVER_NAME']);

// We want all possible errors, but not to show them
error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', '0');
ini_set('log_errors', TRUE);

require_once(ROOT_DIR . 'classes/Exceptions.php');
require_once(ROOT_DIR . 'classes/RecaptchaLib.php');
require_once(ROOT_DIR . 'classes/Response.php');
require_once(ROOT_DIR . 'classes/CategoryRepository.php');
require_once(ROOT_DIR . 'classes/Session.php');
require_once(ROOT_DIR . 'classes/UserRepository.php');
require_once(ROOT_DIR . 'classes/output/Output.php');
require_once(ROOT_DIR . 'vendor/autoload.php');

// Start session
if ($startSession) {
	session_start();
}

// Conficuration and initialization
require_once(ROOT_DIR . 'inc/config.php');

// Database class
require_once(ROOT_DIR . 'classes/Database.php');

$db = new Database(DBUSER, DBPASS, DBNAME, DBHOST);
$memcache = new Memcache();
$memcache->connect('localhost', 11211);

require_once(ROOT_DIR . 'inc/functions.php');

DEFINE("USER_IP", getRealIpAddr());

$session = new Session($db, $memcache);
$user = $session->loadUser();

$category_repository = new CategoryRepository($db);
if (!empty($category)) {
	if ($category == 'all') {
		$category = 'all';
		$category_name = '';
		$category_id = 0;
	} else {
		$selected_category = $category_repository->getCategoryByUrlName($category);
		if ($selected_category instanceof Category) {
			$category = $selected_category->getUrlName();
			$category_name = $selected_category->getName();
			$category_id = $selected_category->getId();
		} else {
			$pageType = 'errors';
			$page = '404';
		}
	}
} else {
	if (!empty($_COOKIE['category_id'])) {
		$selected_category = $category_repository->getCategoryById($_COOKIE['category_id']);
		if ($selected_category instanceof Category) {
			$category = $selected_category->getUrlName();
			$category_name = $selected_category->getName();
			$category_id = $selected_category->getId();
			if ($redirectOk) {
				header('Location: /c/'.$category.'/' . $redirectPageUrl);
				exit();
			}
		} elseif ($_COOKIE['category_id'] == 0) {
			$category = 'all';
			$category_name = '';
			$category_id = 0;
			if ($redirectOk) {
				header('Location: /c/all/' . $redirectPageUrl);
				exit();
			}
		} else {
			$pageType = 'errors';
			$page = '404';
		}
	} else {
		$category = 'all';
		$category_name = '';
		$category_id = 0;
		if ($redirectOk) {
			header('Location: /c/all/' . $redirectPageUrl);
			exit();
		}
	}
}
define('CATEGORY', $category);
define('CATEGORY_NAME', $category_name);
define('CATEGORY_ID', $category_id);

setcookie('category_id', CATEGORY_ID, time() + (3600 * 24 * 60), '/');
define('PUB_PATH_CAT', PUB_PATH . (CATEGORY != '' ? 'c/'.CATEGORY.'/' : ''));

$visits = $db->getRecord('visits', Array('field' => 'id', 'value' => 1));

if (!empty($_SERVER['HTTP_USER_AGENT']) && is_bot($_SERVER['HTTP_USER_AGENT']) === 0) {
	$data = Array(
		'count' => $visits['count'] + 1,
	);
	$db->saveArray('visits', $data, 1);
	$data = Array(
		'ip' => USER_IP,
		'url' => $_SERVER['REQUEST_URI'],
		'time' => gmdate('Y-m-d H:i:s'),
		'user_agent' => $_SERVER['HTTP_USER_AGENT'],
	);
	$db->saveArray('visit_log', $data);
}
