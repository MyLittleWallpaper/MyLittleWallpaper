<?php

declare(strict_types=1);

use MyLittleWallpaper\classes\Format;

const DOC_DIR  = __DIR__ . '/';
const ROOT_DIR = __DIR__ . '/../';
define('PUB_PATH', str_replace($_SERVER['DOCUMENT_ROOT'], '', DOC_DIR));

// Get the request URI
$originalRequestUri = $_SERVER['REQUEST_URI'];
$requestUri         = $originalRequestUri;

// If trying to access index.php, redirect to /c/all/
if ($requestUri === '/index.php') {
    header('Location: /c/all/');
    exit();
}

$apiVersion      = '';
$apiOutputFormat = '';
$pageType        = '';
$page            = '';
$category        = '';
$redirectPageUrl = '';
$startSession    = true;
$redirectOk      = false;

$requestUri = str_replace('\\', '', $requestUri);

// Forbidden if request URI contains two consecutive dots
if (strpos($requestUri, '..') !== false || strpos($requestUri, './') !== false || strpos($requestUri, '/.') !== false) {
    $pageType = 'errors';
    $page     = '403';
} else {
    $qMarkPos = strpos($requestUri, '?');
    if ($qMarkPos !== false) {
        $requestUri = substr($requestUri, 0, $qMarkPos);
    }

    // Parse request URI
    if ($requestUri === '/') {
        $page = 'index';
    } else {
        $uriParts = explode('/', $requestUri);
        $offset   = 0;
        if (strcmp($uriParts[1], 'c') === 0) {
            $offset   = 2;
            $category = $uriParts[2];
        }
        if (preg_match('/^\/c\/([0-9a-z_-]*)\/$/', $requestUri)) {
            $page = 'index';
        } elseif (!empty($uriParts[1 + $offset])) {
            if (strcmp($uriParts[1 + $offset], 'errors') === 0) {
                $pageType = 'errors';
                $page     = '404';
                if (empty($uriParts[2 + $offset])) {
                    $page = '404';
                } elseif (file_exists(ROOT_DIR . 'pages/errors/' . $uriParts[2 + $offset] . '.php')) {
                    $page = $uriParts[2 + $offset];
                }
            } elseif (strcmp($uriParts[1 + $offset], 'ajax') === 0) {
                $pageType = 'errors';
                $page     = '404';
                if (empty($uriParts[2 + $offset])) {
                    $page = '404';
                } elseif (file_exists(ROOT_DIR . 'pages/ajax/' . $uriParts[2 + $offset] . '.php')) {
                    $pageType = 'ajax';
                    $page     = $uriParts[2 + $offset];
                }
            } elseif (strcmp($uriParts[1 + $offset], 'feed') === 0) {
                $pageType = 'errors';
                $page     = '404';
                if (empty($uriParts[2 + $offset])) {
                    $pageType = 'feed';
                    $page     = 'index';
                } elseif (file_exists(ROOT_DIR . 'pages/feed/' . $uriParts[2 + $offset] . '.php')) {
                    $pageType = 'feed';
                    $page     = $uriParts[2 + $offset];
                }
            } elseif (strcmp($uriParts[1 + $offset], 'api') === 0) {
                $pageType = 'errors';
                $page     = '404';
                if (!empty($uriParts[2 + $offset]) && !empty($uriParts[3 + $offset])) {
                    if (
                        preg_match('/^[0-9a-zA-Z]*\.[0-9a-zA-Z]*$/', $uriParts[3 + $offset])
                        && file_exists(
                            ROOT_DIR . 'pages/api/' . $uriParts[2 + $offset] . '/calls/' .
                            Format::fileWithoutExtension($uriParts[3 + $offset]) . '.inc.php'
                        )
                        && file_exists(
                            ROOT_DIR . 'pages/api/' . $uriParts[2 + $offset] . '/output/' .
                            Format::fileExtension($uriParts[3 + $offset]) . '.inc.php'
                        )
                    ) {
                        $apiVersion      = $uriParts[2 + $offset];
                        $pageType        = 'api';
                        $page            = Format::fileWithoutExtension($uriParts[3 + $offset]);
                        $apiOutputFormat = Format::fileExtension($uriParts[3 + $offset]);
                    }
                }
            } elseif (strcmp($uriParts[1 + $offset], 'image') === 0 || strcmp($uriParts[1 + $offset], 'images') === 0) {
                $pageType = 'errors';
                $page     = '404';
                if (!empty($uriParts[2 + $offset])) {
                    $pageType = 'images';
                    $page     = $uriParts[2 + $offset];
                }
            } elseif (strcmp($uriParts[1 + $offset], 'link') === 0) {
                $pageType = 'errors';
                $page     = '404';
                if (
                    !empty($uriParts[2 + $offset]) &&
                    preg_match('/^[0-9a-z]{14}\.[0-9a-z]{8}$/', $uriParts[2 + $offset])
                ) {
                    $pageType = 'link';
                    $page     = $uriParts[2 + $offset];
                }
            } elseif (strcmp($uriParts[1 + $offset], 'download') === 0) {
                $pageType = 'errors';
                $page     = '404';
                if (
                    !empty($uriParts[2 + $offset]) &&
                    preg_match('/^[0-9a-z]{14}\.[0-9a-z]{8}$/', $uriParts[2 + $offset])
                ) {
                    $pageType = 'download';
                    $page     = $uriParts[2 + $offset];
                }
            } elseif (strcmp($uriParts[1 + $offset], 'moderate') === 0) {
                $pageType = 'errors';
                $page     = '404';
                if (
                    !empty($uriParts[2 + $offset]) && strcmp($uriParts[2 + $offset], 'queue-image') === 0 &&
                    !empty($uriParts[3 + $offset])
                ) {
                    $image    = substr($uriParts[3 + $offset], 0, 23);
                    $page     = 'queue-image';
                    $pageType = 'moderate';
                } elseif (
                    !empty($uriParts[2 + $offset]) &&
                    file_exists(ROOT_DIR . 'pages/moderate/' . $uriParts[2 + $offset] . '.php')
                ) {
                    $pageType = 'moderate';
                    $page     = $uriParts[2 + $offset];
                }
            } else {
                if (file_exists(ROOT_DIR . 'pages/' . $uriParts[1 + $offset] . '.php')) {
                    $page = $uriParts[1 + $offset];
                } else {
                    $pageType = 'errors';
                    $page     = '404';
                }
            }
        } else {
            $pageType = 'errors';
            $page     = '404';
        }
    }
}

if (strcmp($pageType, 'images') === 0) {
    $startSession = false;
    require_once(ROOT_DIR . 'inc/init.php');
    if (preg_match('/^o_[0-9a-z]{14}\.[0-9a-z]{8}\.(jpg|jpeg|png|gif)$/', $page)) {
        $original = true;
        $resize   = 0;
        $image    = substr($page, 2, 23);
        require_once(ROOT_DIR . 'pages/image.php');
    } elseif (preg_match('/^r[1-3]{1}_[0-9a-z]{14}\.[0-9a-z]{8}\.jpg$/', $page)) {
        $original = false;
        $resize   = substr($page, 1, 1);
        $image    = substr($page, 3, 23);
        require_once(ROOT_DIR . 'pages/image.php');
    } else {
        session_start();
        require_once(ROOT_DIR . 'pages/errors/404.php');
    }
} else {
    if (preg_match('/^\/c\/[0-9a-z_-]*\/.*$/', $requestUri)) {
        $redirectPageUrl = preg_replace('/^\/c\/[0-9a-z_-]*\/(.*)$/', '$1', $originalRequestUri);
    } else {
        $redirectPageUrl = substr($originalRequestUri, 1);
    }

    if (
        empty($pageType) || strcmp($pageType, 'feed') === 0 || strcmp($pageType, 'link') === 0 ||
        strcmp($pageType, 'download') === 0 || strcmp($pageType, 'api') === 0
    ) {
        // Redirect logic
        $redirectOk = true;
    }
    require_once(ROOT_DIR . 'inc/init.php');
    global $user;

    if (empty($pageType)) {
        require_once(ROOT_DIR . 'pages/' . $page . '.php');
    } elseif (strcmp($pageType, 'ajax') === 0) {
        require_once(ROOT_DIR . 'pages/ajax/' . $page . '.php');
    } elseif (strcmp($pageType, 'feed') === 0) {
        require_once(ROOT_DIR . 'pages/feed/' . $page . '.php');
    } elseif (strcmp($pageType, 'api') === 0) {
        $output_data = [];
        $output_data = require_once(ROOT_DIR . 'pages/api/' . $apiVersion . '/calls/' . $page . '.inc.php');
        if (isset($_GET['debug'])) {
            $time_end                     = microtime(true);
            $time                         = $time_end - $time_start;
            $output_data['generate_time'] = round($time, 4);
        }
        require_once(ROOT_DIR . 'pages/api/' . $apiVersion . '/output/' . $apiOutputFormat . '.inc.php');
    } elseif (strcmp($pageType, 'link') === 0) {
        require_once(ROOT_DIR . 'pages/link.php');
    } elseif (strcmp($pageType, 'download') === 0) {
        require_once(ROOT_DIR . 'pages/download.php');
    } elseif (strcmp($pageType, 'moderate') === 0) {
        if (!$user->getIsAdmin()) {
            require_once(ROOT_DIR . 'pages/errors/403.php');
        } else {
            require_once(ROOT_DIR . 'pages/moderate/' . $page . '.php');
        }
    } else {
        require_once(ROOT_DIR . 'pages/errors/' . $page . '.php');
    }
}
