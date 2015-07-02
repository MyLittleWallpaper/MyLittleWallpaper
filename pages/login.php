<?php
// Check that correct entry point was used
if (!defined('INDEX')) {
	exit();
}
global $session, $user, $db;

require_once(ROOT_DIR . 'classes/output/BasicPage.php');

DEFINE('ACTIVE_PAGE', 'login');
$loginPage = new BasicPage();
$loginPage->setPageTitleAddition('Login');

$ban = $db->getrecord('ban', Array('field' => 'ip', 'value' => USER_IP));
if (!empty($ban['ip']) && $ban['ip'] == USER_IP)
	$banned = TRUE; else $banned = FALSE;

$ipCount = 0;
$captchaError = FALSE;

$db->query("DELETE FROM login_attempt WHERE time < ?", Array(strtotime("-1 hour")));
$result = $db->query("SELECT COUNT(*) cnt FROM login_attempt WHERE ip = ?", Array(USER_IP));
while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
	$ipCount = $row['cnt'];
}

if (!$user->getIsAnonymous()) {
	header('Location: ' . PUB_PATH_CAT);
} else {
	$login_failed = FALSE;
	if (isset($_POST['username']) && !$banned) {
		if ($ipCount > 4) {
			$resp = recaptcha_check_answer(RECAPTCHA_PRIVATE, $_SERVER["REMOTE_ADDR"], $_POST["recaptcha_challenge_field"], $_POST["recaptcha_response_field"]);
			if (!$resp->is_valid) {
				$login_failed = TRUE;
				$captchaError = TRUE;
			}
		}
		if (!$login_failed) {
			$userRepository = new UserRepository();
			$loginUser = $userRepository->getUserByUsernameAndPassword($_POST['username'], $_POST['password']);
			if ($loginUser !== null) {
				if (!$loginUser->getIsBanned()) {
					$session->logUserIn($loginUser->getId());
					header('Location: ' . PUB_PATH_CAT);
				} else $login_failed = TRUE;
			} else $login_failed = TRUE;
		}

		if ($login_failed) {
			$db->saveArray('login_attempt', Array('id' => uid(), 'username' => $_POST['username'], 'ip' => USER_IP, 'time' => time()));
			$ipCount++;
		}
	}

	$loginPage->setJavascript('var RecaptchaOptions = {
		lang : \'en\',
		theme : \'clean\'
	};');
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

		if ($ipCount > 4) {
			$pageContents .= '<p>Too many failed login attempts, please fill in the CAPTCHA below.</p>';
			$pageContents .= recaptcha_get_html(RECAPTCHA_PUBLIC);
		}
		$pageContents .= '<input type="submit" value="Log in" />';
		$pageContents .= '</form>';
	}
	$pageContents .= '</div></div>';

	$loginPage->setHtml($pageContents);

	$response = new Response($loginPage);
	$response->output();
}