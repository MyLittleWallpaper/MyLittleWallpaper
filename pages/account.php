<?php
// Check that correct entry point was used
if (!defined('INDEX')) {
	exit();
}
global $user;

require_once(ROOT_DIR . 'classes/output/BasicPage.php');
DEFINE('ACTIVE_PAGE', 'account');

$redirect = FALSE;
$error = FALSE;

if ($user->getIsAnonymous()) {
	require_once(ROOT_DIR . 'pages/errors/403.php');
} else {

	$accountPage = new BasicPage();
	$accountPage->setPageTitleAddition('Account');

	if (isset($_POST['email'])) {
		if (strcmp(Format::passwordHash($_POST['old_password'], $user->getUsername()), $user->getPasswordHash()) !== 0) {
			$error = 'Old password incorrect.';
		}
		if (filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) === FALSE) {
			if ($error) $error .= '<br />Incorrect email.';
			else $error = 'Incorrect email.';
		}
		if ($_POST['password'] != '') {
			if (mb_strlen($_POST['password'], 'utf-8') < 6) {
				if ($error) $error .= '<br />Password is too short.';
				else $error = 'Password is too short.';
			} elseif (strcmp($_POST['password'], $_POST['password_confirm']) !== 0) {
				if ($error) $error .= '<br />Password and its confirmation don\'t match.';
				else $error = 'Password and its confirmation don\'t match.';
			}
		}
		if (!$error) {
			$email_exists = $db->getRecord('user', Array('field' => 'email', 'value' => $_POST['email']));
			if (!empty($email_exists) && $email_exists['id'] != $user->getId()) {
				$error = 'Given email is already in use.';
			}
			if (!$error) {
				$savedata = Array(
					'email' => $_POST['email'],
				);
				if ($_POST['password'] != '') {
					$savedata['password'] = Format::passwordHash($_POST['password'], $user->getUsername());
				}
				$db->saveArray('user', $savedata, $user->getId());
				$_SESSION['success'] = TRUE;
				$redirect = TRUE;
				header('Location: '.PUB_PATH.'account');
			}
		}
	}

	$pageContents = '<div id="content"><div>';
	$pageContents .= '<h1>Account</h1>';
	if ($user->getToken() === null) {
		$user->setToken(uid());
		$db->saveArray('user', array('token' => $user->getToken()), $user->getId());
	}
	$pageContents .= '<form class="labelForm" style="padding:5px 0 0 0;" action="'.PUB_PATH_CAT.'account" method="post" accept-charset="utf-8">';
	if ($error) $pageContents .= '<div class="error">'.$error.'</div>';
	if (!empty($_SESSION['success'])) $pageContents .= '<div class="success">Account information updated successfully.</div>';
	$pageContents .= '<p>Some API calls require a user token, the one below is your personal user token.</p>';
	$pageContents .= '<div><label>API token:</label><input type="text" readonly="readonly" style="width:300px;background:#f4f4f4;cursor:text;" value="' . $user->getToken() . '" /></div>';
	$pageContents .= '<p>Old password is required for changing your account information.</p>';
	$pageContents .= '<div><label>Email:</label><input type="text" autocomplete="off" name="email" style="width:300px;" value="'.(!empty($_POST['email']) ? Format::htmlEntities($_POST['email']) : Format::htmlEntities($user->getEmail())).'" /></div>';
	$pageContents .= '<div><label>Old password:<br /></label><input type="password" name="old_password" style="width:300px;" /></div>';
	$pageContents .= '<div><label style="float:left;padding-top:2px;">Password:<br /><span style="font-size:11px;">At least 6 characters.<br /><br />Leave empty if you<br />don\'t wish to change<br />your password.</span></label><input type="password" name="password" style="width:300px;" /><div style="clear:both;"></div></div>';
	$pageContents .= '<div><label>Confirm Password:</label><input type="password" name="password_confirm" style="width:300px;" /></div>';
		
	$pageContents .= '<input type="submit" value="Change" />';
	$pageContents .= '</form>';
	
	$pageContents .= '</div></div>';
	if (!$redirect && isset($_SESSION['success'])) unset($_SESSION['success']);

	$accountPage->setHtml($pageContents);

	$response = new Response($accountPage);
	$response->output();
}