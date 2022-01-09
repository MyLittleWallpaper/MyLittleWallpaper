<?php

declare(strict_types=1);

use MyLittleWallpaper\classes\CSRF;

global $session;

$session->logUserOut();
CSRF::clearTokens();
header('Location: ' . PUB_PATH_CAT);
