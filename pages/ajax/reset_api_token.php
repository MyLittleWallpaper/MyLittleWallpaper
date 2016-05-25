<?php
// Check that correct entry point was used
if (!defined('INDEX')) exit();
global $response, $user;

require_once(ROOT_DIR . 'classes/output/BasicJSON.php');

$return = ['token' => null];
if (!$user->getIsAnonymous()) {
	$user->setToken(uid());
	$db->saveArray('user', ['token' => $user->getToken()], $user->getId());
	$return['token'] = $user->getToken();
}

$tokenChangeResult = new BasicJSON($return);

$response = new Response($tokenChangeResult);
$response->output();