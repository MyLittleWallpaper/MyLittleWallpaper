<?php

declare(strict_types=1);

use MyLittleWallpaper\classes\Database;
use MyLittleWallpaper\classes\Helpers;

if (PHP_SAPI !== 'cli') {
    exit();
}

const DOC_DIR  = __DIR__ . '/../public_html/';
const ROOT_DIR = __DIR__ . '/../';
const PUB_PATH = '/';
$_SERVER['SERVER_PORT'] = '';
$_SERVER['SERVER_NAME'] = '';
$_SERVER['REMOTE_ADDR'] = '';

require_once(ROOT_DIR . 'inc/init.php');

$db      = Database::getInstance();
$options = getopt('', ['dry-run']);
$dryRun  = array_key_exists('dry-run', $options);
if ($dryRun) {
    echo 'Dry-run, not updating anything!' . "\n\n";
}

$wallpapers = $db->query(<<<SQL
    SELECT w.id, w.name, w.width, w.height, ta.id tag_id, ta.name aspect
    FROM wallpaper w
    LEFT JOIN wallpaper_tag_aspect wta ON (wta.wallpaper_id = w.id)
    LEFT JOIN tag_aspect ta ON (ta.id = wta.tag_aspect_id)
    WHERE w.no_aspect = 0 AND w.no_resolution = 0 AND w.deleted = 0
SQL)->fetchAll(PDO::FETCH_ASSOC);

/**
 * @var array{id:int,name:string,width:int,height:int,tag_id:int,aspect:string} $wallpaper
 */
foreach ($wallpapers as $wallpaper) {
    $newAspectId = Helpers::getTagAspectId($wallpaper['width'], $wallpaper['height']);
    $newAspect = Helpers::getAspectRatio($wallpaper['width'], $wallpaper['height']);
    if ($newAspect === $wallpaper['aspect']) {
        continue;
    }
    echo sprintf("%s %s\n", str_pad('#' . $wallpaper['id'], 7, ' ', STR_PAD_LEFT), $wallpaper['name']);
    echo '  - ' . $wallpaper['aspect'];
    echo ' -> ' . $newAspect . "\n";
    if ($dryRun) {
        continue;
    }
    $db->query(
        'UPDATE wallpaper_tag_aspect SET tag_aspect_id = ? WHERE wallpaper_id = ?',
        [$newAspectId, $wallpaper['id']]
    );
}
