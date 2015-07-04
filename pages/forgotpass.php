<?php
// Check that correct entry point was used
if (!defined('INDEX')) {
	exit();
}
global $user, $db;

require_once(ROOT_DIR . 'classes/output/BasicPage.php');

DEFINE('ACTIVE_PAGE', 'forgotpass');
$ban = $db->getrecord('ban', Array('field' => 'ip', 'value' => USER_IP));
if (!empty($ban['ip']) && $ban['ip'] == USER_IP) {
	$banned = TRUE;
} else {
	$banned = FALSE;
}
$redirect = FALSE;
$error = FALSE;

if (!$user->getIsAnonymous()) {
	header('Location: ' . PUB_PATH_CAT);
} else {
	$resetPasswordPage = new BasicPage();
	$resetPasswordPage->setPageTitleAddition('Reset password');

	$db->query("DELETE FROM user_forgotpass WHERE time < ?", Array(gmdate('Y-m-d H:i:s', strtotime("-2 days"))));

	$resetPasswordPage->setJavascript('var RecaptchaOptions = {
		lang : \'en\',
		theme : \'clean\'
	};');
	$pageContents = '<div id="content"><div>';
	$pageContents .= '<h1>Reset password</h1>';

	if (!$banned) {
		if (isset($_GET['req']) && !empty($_GET['key'])) {
			if (isset($_POST['password'])) {
				$resp = recaptcha_check_answer(RECAPTCHA_PRIVATE, $_SERVER["REMOTE_ADDR"], $_POST["recaptcha_challenge_field"], $_POST["recaptcha_response_field"]);
				if (!$resp->is_valid) {
					$error = 'Invalid CAPTCHA.';
				} elseif (mb_strlen($_POST['password'], 'utf-8') < 6) {
					$error = 'Password is too short.';
				} elseif (strcmp($_POST['password'], $_POST['password_confirm']) !== 0) {
					$error = 'Password and its confirmation don\'t match.';
				}
				if (!$error) {
					$reset_rec = $db->getrecord('user_forgotpass', Array('field' => 'id', 'value' => $_GET['req']));
					if (!empty($reset_rec) && strcmp(Format::passwordHash($_GET['key'], $_GET['req']), $reset_rec['keyhash']) === 0) {
						$userinf = $db->getrecord('user', Array('field' => 'id', 'value' => $reset_rec['user_id']));
						if (!empty($userinf)) {
							$data = Array(
								'password' => Format::passwordHash($_POST['password'], trim($userinf['username'])),
							);
							$db->saveArray('user', $data, $userinf['id']);
							$db->query("DELETE FROM user_forgotpass WHERE user_id = ?", Array($reset_rec['user_id']));
							$redirect = TRUE;
							$_SESSION['success'] = TRUE;
							header('Location: ' . PUB_PATH_CAT . 'forgotpass?req=1&key=1');
						} else $error = 'Invalid request.';
					} else $error = 'Invalid request.';
				}
			}
			if (!empty($_SESSION['success'])) {
				$pageContents .= '<p><div class="success">Password changed, you can now <a href="' . PUB_PATH_CAT . 'login">log in</a>.</div></p>';
			} else {
				$pageContents .= '<p>Please give a new password for your account.</p>';
				$pageContents .= '<form class="uploadform" style="padding:5px 0 0 0;" action="' . PUB_PATH_CAT . 'forgotpass?req=' . urlencode($_GET['req']) . '&key=' . urlencode($_GET['key']) . '" method="post" accept-charset="utf-8">';
				if ($error)
					$pageContents .= '<div class="error">' . $error . '</div>';
				$pageContents .= '<div><label style="float:left;padding-top:2px;">Password:<br /><span style="font-size:11px;">At least 6 characters</span></label><input type="password" name="password" style="width:300px;" /><div style="clear:both;"></div></div>';
				$pageContents .= '<div><label>Confirm Password:</label><input type="password" name="password_confirm" style="width:300px;" /></div>';

				$pageContents .= recaptcha_get_html(RECAPTCHA_PUBLIC);

				$pageContents .= '<br /><input type="submit" value="Submit" />';
				$pageContents .= '</form>';
			}
		} else {
			if (isset($_POST['email'])) {
				$resp = recaptcha_check_answer(RECAPTCHA_PRIVATE, $_SERVER["REMOTE_ADDR"], $_POST["recaptcha_challenge_field"], $_POST["recaptcha_response_field"]);
				if (!$resp->is_valid) {
					$error = 'Invalid CAPTCHA.';
				} else {
					$reset_user = $db->getrecord('user', Array('field' => 'email', 'value' => $_POST['email']));
					if (!empty($reset_user)) {
						$db->query("DELETE FROM user_forgotpass WHERE user_id = ?", Array($reset_user['id']));
						$thekey = generate_password();
						$id = uid();
						$data = Array(
							'id' => $id,
							'user_id' => $reset_user['id'],
							'keyhash' => Format::passwordHash($thekey, trim($id)),
							'time' => gmdate('Y-m-d H:i:s'),
						);
						$db->saveArray('user_forgotpass', $data);
						$phpMailer = new PHPMailer();
						$phpMailer->From = 'noreply@mylittlewallpaper.com';
						$phpMailer->FromName = 'My Little Wallpaper';
						$phpMailer->Body = utf8_decode(
							'Password reset was requested for your account. To complete this request, go to the following URL: ' . PROTOCOL . SITE_DOMAIN . '/c/all/forgotpass?req=' . urlencode($id) . '&key=' . urlencode($thekey) . "\n\n" .
							'The URL above will expire in 48 hours.' . "\n\n" .
							'If you didn\'t request for password reset yourself, you can just ignore this message.' . "\n\n" .
							'Best regards,' . "\n" .
							'My Little Wallpaper Team');
						$phpMailer->Subject = 'My Little Wallpaper account password reset';
						$phpMailer->Encoding = 'quoted-printable';
						$phpMailer->AddAddress($_POST['email']);
						$phpMailer->Send();
						$_SESSION['success'] = TRUE;
						$redirect = TRUE;
						header('Location: ' . PUB_PATH_CAT . 'forgotpass');
					} else $error = 'Invalid email.';
				}
			}
			$pageContents .= '<p>Please give the email address of your account.</p>';
			if (!empty($_SESSION['success'])) {
				$pageContents .= '<div class="success">An email sent with instructions.</div>';
			}
			$pageContents .= '<form class="labelForm" style="padding:5px 0 0 0;" action="' . PUB_PATH_CAT . 'forgotpass" method="post" accept-charset="utf-8">';
			if ($error)
				$pageContents .= '<div class="error">' . $error . '</div>';
			$pageContents .= '<div><label>Email:</label><input type="text" autocomplete="off" name="email" style="width:300px;" value="" /></div>';

			$pageContents .= recaptcha_get_html(RECAPTCHA_PUBLIC);

			$pageContents .= '<br /><input type="submit" value="Submit" />';
			$pageContents .= '</form>';
		}
	} else {
		$pageContents .= '<p>Your IP is on the blacklist.</p>';
	}
	$pageContents .= '</div></div>';
	if (!$redirect && isset($_SESSION['success']))
		unset($_SESSION['success']);

	$resetPasswordPage->setHtml($pageContents);

	$response = new Response($resetPasswordPage);
	$response->output();
}