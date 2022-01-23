<?php

declare(strict_types=1);

global $db, $image, $resize, $original;

use Gumlet\ImageResize;
use MyLittleWallpaper\classes\Session;

if (!empty($image)) {
    $last_modified = filemtime(ROOT_DIR . $_ENV['FILE_FOLDER'] . $image);
    if (
        empty($_GET['download']) && ctype_alnum(str_replace('.', '', $image)) &&
        file_exists(ROOT_DIR . $_ENV['FILE_FOLDER'] . $image) &&
        (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) || isset($_SERVER['HTTP_IF_NONE_MATCH']))
    ) {
        if ($last_modified <= strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE'] . ' UTC')) {
            Session::setCacheLimiter('private');
            Session::setCacheExpire(60 * 24 * 7);
            Session::startSession();

            header('HTTP/1.1 304 Not Modified');
            exit();
        }
    }

    $file = $db->getRecord('wallpaper', ['field' => 'file', 'value' => $image]);
    if (!empty($file['id']) && $file['deleted'] == '0') {
        if (file_exists(ROOT_DIR . $_ENV['FILE_FOLDER'] . $file['file'])) {
            Session::setCacheLimiter('private');
            Session::setCacheExpire(60 * 24 * 7);
            Session::startSession();

            if ($original && ($file['direct_with_link'] == '1')) {
                header("Last-Modified: " . gmdate('D, d M Y H:i:s', $last_modified));
                header('Content-Type: ' . $file['mime']);
                if (!empty($_GET['download'])) {
                    header('Content-Disposition: attachment; filename="' . $file['filename'] . '"');
                } else {
                    header('Content-Disposition: inline; filename="' . $file['filename'] . '"');
                }
                readfile(ROOT_DIR . $_ENV['FILE_FOLDER'] . $file['file']);
            } elseif (file_exists(ROOT_DIR . $_ENV['FILE_FOLDER'] . 'thumb/thumb1_' . $file['file'])) {
                header('Content-Type: image/jpeg');
                if ($resize == '2') {
                    readfile(ROOT_DIR . $_ENV['FILE_FOLDER'] . 'thumb/thumb2_' . $file['file']);
                } elseif ($resize == '3') {
                    readfile(ROOT_DIR . $_ENV['FILE_FOLDER'] . 'thumb/thumb3_' . $file['file']);
                } else {
                    readfile(ROOT_DIR . $_ENV['FILE_FOLDER'] . 'thumb/thumb1_' . $file['file']);
                }
            } else {
                $image = new ImageResize(ROOT_DIR . $_ENV['FILE_FOLDER'] . $file['file']);
                $image->resizeToBestFit(200, 150);
                $image->save(ROOT_DIR . $_ENV['FILE_FOLDER'] . 'cache/' . $file['file'] . 'r1.jpg', IMAGETYPE_JPEG, 90);
                if ($file['height'] > 700) {
                    $image = new ImageResize(ROOT_DIR . $_ENV['FILE_FOLDER'] . $file['file']);
                    $image->resizeToBestFit(640, 480);
                    $image->save(
                        ROOT_DIR . $_ENV['FILE_FOLDER'] . 'cache/' . $file['file'] . 'r2.jpg',
                        IMAGETYPE_JPEG,
                        90
                    );

                    $image = new ImageResize(ROOT_DIR . $_ENV['FILE_FOLDER'] . $file['file']);
                    $image->resizeToBestFit(457, 342);
                    $image->save(
                        ROOT_DIR . $_ENV['FILE_FOLDER'] . 'cache/' . $file['file'] . 'r3.jpg',
                        IMAGETYPE_JPEG,
                        90
                    );
                } else {
                    $image = new ImageResize(ROOT_DIR . $_ENV['FILE_FOLDER'] . $file['file']);
                    $image->resizeToBestFit(400, 300);
                    $image->save(
                        ROOT_DIR . $_ENV['FILE_FOLDER'] . 'cache/' . $file['file'] . 'r2.jpg',
                        IMAGETYPE_JPEG,
                        90
                    );
                    $image->save(
                        ROOT_DIR . $_ENV['FILE_FOLDER'] . 'cache/' . $file['file'] . 'r3.jpg',
                        IMAGETYPE_JPEG,
                        90
                    );
                }

                rename(
                    ROOT_DIR . $_ENV['FILE_FOLDER'] . "cache/" . $file['file'] . "r1.jpg",
                    ROOT_DIR . $_ENV['FILE_FOLDER'] . "thumb/thumb1_" . $file['file']
                );
                rename(
                    ROOT_DIR . $_ENV['FILE_FOLDER'] . "cache/" . $file['file'] . "r2.jpg",
                    ROOT_DIR . $_ENV['FILE_FOLDER'] . "thumb/thumb2_" . $file['file']
                );
                rename(
                    ROOT_DIR . $_ENV['FILE_FOLDER'] . "cache/" . $file['file'] . "r3.jpg",
                    ROOT_DIR . $_ENV['FILE_FOLDER'] . "thumb/thumb3_" . $file['file']
                );

                header("Last-Modified: " . gmdate('D, d M Y H:i:s', $last_modified) . ' GMT');
                header('Content-Type: image/jpeg');

                if ($resize == '2') {
                    readfile(ROOT_DIR . $_ENV['FILE_FOLDER'] . 'thumb/thumb2_' . $file['file']);
                } elseif ($resize == '3') {
                    readfile(ROOT_DIR . $_ENV['FILE_FOLDER'] . 'thumb/thumb3_' . $file['file']);
                } else {
                    readfile(ROOT_DIR . $_ENV['FILE_FOLDER'] . 'thumb/thumb1_' . $file['file']);
                }
            }
        } else {
            Session::startSession();
            require_once(ROOT_DIR . 'pages/errors/404.php');
        }
    } else {
        Session::startSession();
        require_once(ROOT_DIR . 'pages/errors/404.php');
    }
} else {
    Session::startSession();
    require_once(ROOT_DIR . 'pages/errors/404.php');
}
