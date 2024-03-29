<?php

declare(strict_types=1);

global $db;

if (
    !empty($_GET['after']) && preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}:[0-9]{2}$/', $_GET['after']) &&
    strtotime($_GET['after'])
) {
    $sql           = "SELECT COUNT(*) cnt FROM wallpaper WHERE timeadded >= ?";
    $result        = $db->query($sql, [strtotime($_GET['after'] . 'Z')]);
    $newWallpapers = 0;
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        $newWallpapers = (int)$row['cnt'];
    }
    return ['newWallpapers' => $newWallpapers];
}

return ['newWallpapers' => 0];
