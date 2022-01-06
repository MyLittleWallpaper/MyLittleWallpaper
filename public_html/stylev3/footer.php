<?php

declare(strict_types=1);

global $user, $db, $response;

echo "\n" . '		<footer>';
echo sprintf(
    "			<div class=\"info\">&copy; 2012-%s %s, all wallpapers &copy; to their respective artists.</div>\n",
    date('Y'),
    'My Little Wallpaper'
);
echo sprintf(
    "			<div class=\"contact\">Running version <strong>%s</strong></div>\n",
    file_get_contents(ROOT_DIR . 'VERSION')
);
echo sprintf(
    '			<div class="contact">If you have any questions about the site, send an email to %s</div>' . "\n",
    '<a href="mailto:sharkmachine(at)ecxol(dot)net">sharkmachine(at)ecxol(dot)net</a>'
);

$time = microtime(true) - TIME_START;

if (
    $_SERVER['REQUEST_URI'] !== '/upload' &&
    !empty($_SERVER['HTTP_USER_AGENT']) &&
    isBot($_SERVER['HTTP_USER_AGENT']) === 0
) {
    $db->saveArray(
        'page_loadtime',
        [
            'id' => uid(),
            'load_time' => round($time, 4),
            'time' => gmdate('Y-m-d H:i:s'),
            'url' => $_SERVER['REQUEST_URI']
        ]
    );
}

echo sprintf(
    "			<div style=\"padding-top:20px;font-size:11px;font-style:italic;\">Page created in %s seconds",
    round($time, 4)
);
echo '</div>' . "\n";

echo '		</footer>' . "\n";
echo '	</body>' . "\n";
echo '</html>';
