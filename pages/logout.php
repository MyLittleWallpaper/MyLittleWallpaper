<?php

declare(strict_types=1);
global $session;

$session->logUserOut();
header('Location: ' . PUB_PATH_CAT);
