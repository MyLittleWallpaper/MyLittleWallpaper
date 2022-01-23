<?php

declare(strict_types=1);

global $db, $user, $image;
$imageFound = false;

if (!empty($image)) {
    $file = $db->getRecord('wallpaper_submit', ['field' => 'file', 'value' => $image]);
    if (!empty($file['id'])) {
        if (file_exists(ROOT_DIR . $_ENV['FILE_FOLDER'] . 'moderate/' . $file['file'])) {
            $res_w   = 960;
            $res_h   = 540;
            $imgdata = @getimagesize(ROOT_DIR . $_ENV['FILE_FOLDER'] . 'moderate/' . $file['file']);
            if ($imgdata) {
                [, , $imgtype] = $imgdata;
                switch ($imgtype) {
                    case IMAGETYPE_GIF:
                        $image = imagecreatefromgif(ROOT_DIR . $_ENV['FILE_FOLDER'] . 'moderate/' . $file['file']);
                        break;
                    case IMAGETYPE_JPEG:
                        $image = imagecreatefromjpeg(ROOT_DIR . $_ENV['FILE_FOLDER'] . 'moderate/' . $file['file']);
                        break;
                    case IMAGETYPE_PNG:
                        $image = imagecreatefrompng(ROOT_DIR . $_ENV['FILE_FOLDER'] . 'moderate/' . $file['file']);
                        break;
                    default:
                        $image = false;
                }
            }
            if ($image) {
                $max_y = $res_h;
                $max_x = $res_w;
                $w     = @imagesx($image);
                $h     = @imagesy($image);

                if (($max_x > $w || empty($max_x)) && ($max_y > $h || empty($max_y))) {
                    header('Content-Type: ' . $file['mime']);
                    echo file_get_contents(ROOT_DIR . $_ENV['FILE_FOLDER'] . 'moderate/' . $file['fileid']);
                    imagedestroy($image);
                } else {
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
                    if (!$dimg = @imagecreatetruecolor($nw, $nh)) {
                        imagedestroy($image);
                    } else {
                        $white = @imagecolorallocate($dimg, 255, 255, 255);
                        if (!@imagefill($dimg, 0, 0, $white)) {
                            imagedestroy($dimg);
                            imagedestroy($image);
                        }
                        if (!@imagecopyresampled($dimg, $image, 0, 0, 0, 0, $nw, $nh, $w, $h)) {
                            imagedestroy($dimg);
                            imagedestroy($image);
                        }
                    }
                }
                if (!empty($dimg)) {
                    $imageFound = true;
                    header('Content-Type: image/jpeg');
                    imagejpeg($dimg, null, 90);
                    imagedestroy($dimg);
                    imagedestroy($image);
                }
            }
        }
    }
}

if (!$imageFound) {
    require_once(ROOT_DIR . 'pages/errors/404.php');
}
