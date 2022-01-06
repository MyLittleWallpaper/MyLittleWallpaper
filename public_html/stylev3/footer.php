<?php

declare(strict_types=1);

global $user, $db, $time_start, $response;

echo "\n" . '		<footer>';
echo '			<div class="info">&copy; 2012-' . date('Y') . ' My Little Wallpaper, all wallpapers &copy; to their respective artists.</div>' . "\n";
echo '			<div class="contact">Running version <strong>' . file_get_contents(ROOT_DIR . 'VERSION') . '</strong></div>' . "\n";
echo '			<div class="contact">If you have any questions about the site, send an email to <a href="mailto:sharkmachine(at)ecxol(dot)net">sharkmachine(at)ecxol(dot)net</a></div>' . "\n";

$time_end = microtime(true);
$time = $time_end - $time_start;

if ($_SERVER['REQUEST_URI'] !== '/upload' && !empty($_SERVER['HTTP_USER_AGENT']) && isBot($_SERVER['HTTP_USER_AGENT']) === 0) {
    $loadtime_savedata = ['id' => uid(), 'load_time' => round($time, 4), 'time' => gmdate('Y-m-d H:i:s'), 'url' => $_SERVER['REQUEST_URI']];
    $db->saveArray('page_loadtime', $loadtime_savedata);
}

echo '			<div style="padding-top:20px;font-size:11px;font-style:italic;">Page created in ' . round($time, 4) . ' seconds';
echo '</div>' . "\n";

echo '		</footer>' . "\n";
echo '	</body>' . "\n";
echo '</html>';
