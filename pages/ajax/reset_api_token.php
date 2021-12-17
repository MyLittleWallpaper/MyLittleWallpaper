<?php

declare(strict_types=1);

global $response, $user;

use MyLittleWallpaper\classes\output\BasicJSON;
use MyLittleWallpaper\classes\Response;

$return = ['token' => null];
if (!$user->getIsAnonymous()) {
    $user->setToken(uid());
    $db->saveArray('user', ['token' => $user->getToken()], $user->getId());
    $return['token'] = $user->getToken();
}

$tokenChangeResult = new BasicJSON($return);

$response = new Response($tokenChangeResult);
$response->output();
