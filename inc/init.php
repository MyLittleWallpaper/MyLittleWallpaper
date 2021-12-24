<?php

declare(strict_types=1);

global $category, $redirectOk, $startSession, $pageType, $page;

use MyLittleWallpaper\classes\Category\Category;
use MyLittleWallpaper\classes\Category\CategoryRepository;
use MyLittleWallpaper\classes\Database;
use MyLittleWallpaper\classes\Session;

const THEME = 'stylev3';

// @todo fix
// phpcs:disable SlevomatCodingStandard.Variables.UnusedVariable.UnusedVariable
// phpcs:disable PSR1.Files.SideEffects
// Server protocol
$protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
    $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
define('PROTOCOL', $protocol);

// Site domain (for example www.mylittlewallpaper.com)
define('SITE_DOMAIN', $_SERVER['SERVER_NAME']);

// We want all possible errors, but not to show them
error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

require_once(ROOT_DIR . 'vendor/autoload.php');

// Start session
if ($startSession) {
    session_start();
}

// Conficuration and initialization
require_once(ROOT_DIR . 'inc/config.php');

$db       = Database::getInstance();
$memcache = new Memcache();
$memcache->connect('localhost', 11211);

require_once(ROOT_DIR . 'inc/functions.php');

define("USER_IP", getRealIpAddr());

$session = new Session($db, $memcache);
$user    = $session->loadUser();

$category_repository = new CategoryRepository($db);
if (!empty($category)) {
    if ($category == 'all') {
        $category      = 'all';
        $category_name = '';
        $category_id   = 0;
    } else {
        $selected_category = $category_repository->getCategoryByUrlName($category);
        if ($selected_category instanceof Category) {
            $category      = $selected_category->getUrlName();
            $category_name = $selected_category->getName();
            $category_id   = $selected_category->getId();
        } else {
            $pageType = 'errors';
            $page     = '404';
        }
    }
} elseif (!empty($_COOKIE['category_id'])) {
    $selected_category = $category_repository->getCategoryById((int)$_COOKIE['category_id']);
    if ($selected_category instanceof Category) {
        $category      = $selected_category->getUrlName();
        $category_name = $selected_category->getName();
        $category_id   = $selected_category->getId();
        if ($redirectOk) {
            header('Location: /c/' . $category . '/' . $redirectPageUrl);
            exit();
        }
    } elseif ($_COOKIE['category_id'] == 0) {
        $category      = 'all';
        $category_name = '';
        $category_id   = 0;
        if ($redirectOk) {
            header('Location: /c/all/' . $redirectPageUrl);
            exit();
        }
    } else {
        $pageType = 'errors';
        $page     = '404';
    }
} else {
    $category      = 'all';
    $category_name = '';
    $category_id   = 0;
    if ($redirectOk) {
        header('Location: /c/all/' . $redirectPageUrl);
        exit();
    }
}
define('CATEGORY', $category);
define('CATEGORY_NAME', $category_name);
define('CATEGORY_ID', $category_id);

setcookie('category_id', (string)CATEGORY_ID, time() + (3600 * 24 * 60));
const PUB_PATH_CAT = PUB_PATH . (CATEGORY != '' ? 'c/' . CATEGORY . '/' : '');

$visits = $db->getRecord('visits', ['field' => 'id', 'value' => 1]);

if (!empty($_SERVER['HTTP_USER_AGENT']) && isBot($_SERVER['HTTP_USER_AGENT']) === 0) {
    $data = [
        'count' => $visits['count'] + 1,
    ];
    $db->saveArray('visits', $data, 1);
    $data = [
        'ip'         => USER_IP,
        'url'        => $_SERVER['REQUEST_URI'],
        'time'       => gmdate('Y-m-d H:i:s'),
        'user_agent' => $_SERVER['HTTP_USER_AGENT'],
    ];
    $db->saveArray('visit_log', $data);
}
