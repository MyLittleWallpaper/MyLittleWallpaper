<?php
// Check that correct entry point was used
if (!defined('INDEX')) {
	exit();
}
global $session, $user, $db;

require_once(ROOT_DIR . 'classes/output/BasicPage.php');

define('ACTIVE_PAGE', 'login');
$loginPage = new BasicPage();
$loginPage->setPageTitleAddition('Login');

$ban = $db->getRecord('ban', Array('field' => 'ip', 'value' => USER_IP));
if (!empty($ban['ip']) && $ban['ip'] == USER_IP)
	$banned = true; else $banned = false;

$ipCount = 0;
$captchaError = false;

$db->query("DELETE FROM login_attempt WHERE time < ?", Array(strtotime("-1 hour")));
$result = $db->query("SELECT COUNT(*) cnt FROM login_attempt WHERE ip = ?", [USER_IP]);
while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
	$ipCount = $row['cnt'];
}

if (!$user->getIsAnonymous()) {
	header('Location: ' . PUB_PATH_CAT);
} else {
	$login_failed = false;
	if (isset($_POST['username']) && !$banned) {
		if ($ipCount > 4) {
			// @todo Prevent login
		}
		if (!$login_failed) {
			$userRepository = new UserRepository();
			$loginUser = $userRepository->getUserByUsernameAndPassword($_POST['username'], $_POST['password']);
			if ($loginUser !== null) {
				if (!$loginUser->getIsBanned()) {
					$session->logUserIn($loginUser->getId());
					header('Location: ' . PUB_PATH_CAT);
				} else $login_failed = true;
			} else $login_failed = true;
		}

		if ($login_failed) {
			$db->saveArray('login_attempt', Array('id' => uid(), 'username' => $_POST['username'], 'ip' => USER_IP, 'time' => time()));
			$ipCount++;
		}
	}

	$loginPage->setPageTitleAddition('Log in');

	$pageContents = '<div id="content"><div>';
	$pageContents .= '<h1>Log in</h1>';
	if ($banned) {
		$pageContents .= '<p>Your IP is on the blacklist.</p>';
	} else {
		$pageContents .= '<p>No account? You can register one <a href="' . PUB_PATH_CAT . 'register">here</a>.</p>';
		$pageContents .= '<p>Have you forgotten your password? Reset your password <a href="' . PUB_PATH_CAT . 'forgotpass">here</a>.</p>';
		$pageContents .= '<form class="labelForm" style="padding:5px 0 0 0;" action="' . PUB_PATH_CAT . 'login" method="post" accept-charset="utf-8">';
		if ($login_failed) {
			if ($ipCount > 4 && $captchaError) {
				$pageContents .= '<div class="error">Incorrect CAPTCHA.</div>';
			} else {
				$pageContents .= '<div class="error">Incorrect username or password.</div>';
			}
		}
		$pageContents .= '<div><label>Username:</label><input type="text" autocomplete="off" name="username" style="width:300px;" value="' . (!empty($_POST['username']) ? Format::htmlEntities($_POST['username']) : '') . '" /></div>';
		$pageContents .= '<div><label>Password:</label><input type="password" name="password" style="width:300px;" /></div>';

		$pageContents .= '<input type="submit" value="Log in" />';
		$pageContents .= '</form>';
	}
	$pageContents .= '</div></div>';

	$loginPage->setHtml($pageContents);

	$response = new Response($loginPage);
	$response->output();
}