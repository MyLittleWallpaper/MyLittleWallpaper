<?php

declare(strict_types=1);

use MyLittleWallpaper\classes\CSRF;
use MyLittleWallpaper\classes\Format;
use MyLittleWallpaper\classes\output\BasicPage;
use MyLittleWallpaper\classes\Response;
use MyLittleWallpaper\classes\User\UserRepository;

global $session, $user, $db;

const ACTIVE_PAGE = 'login';
$loginPage = new BasicPage();
$loginPage->setPageTitleAddition('Login');

$ban = $db->getRecord('ban', ['field' => 'ip', 'value' => USER_IP]);
if (!empty($ban['ip']) && $ban['ip'] === USER_IP) {
    $banned = true;
} else {
    $banned = false;
}

$failedLogins    = ['ip' => 0, 'username' => 0];
$lockedFor5Mins  = false;
$csrfCheckResult = true;
$db->query("DELETE FROM login_attempt WHERE time < ?", [strtotime("-5 minutes")]);

if (!$user->getIsAnonymous()) {
    header('Location: ' . PUB_PATH_CAT);
} else {
    $loginFailed = false;
    if (isset($_POST['username']) && !$banned) {
        $result = $db->query("SELECT COUNT(*) cnt FROM login_attempt WHERE ip = ?", [USER_IP]);
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $failedLogins['ip'] = $row['cnt'];
        }
        $result = $db->query("SELECT COUNT(*) cnt FROM login_attempt WHERE username = ?", [$_POST['username']]);
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $failedLogins['username'] = $row['cnt'];
        }
        if ($failedLogins['ip'] > 30 || $failedLogins['username'] > 10) {
            $loginFailed    = true;
            $lockedFor5Mins = true;
        }
        if (!$loginFailed) {
            $requestCsrfToken = $_POST['csrf_token'] ?? '';
            if (($csrfCheckResult = CSRF::isTokenValid('login', $requestCsrfToken)) === false) {
                $loginFailed = true;
            } else {
                $userRepository = new UserRepository();
                $loginUser      = $userRepository->getUserByUsernameAndPassword($_POST['username'], $_POST['password']);
                if ($loginUser !== null && !$loginUser->getIsBanned()) {
                    $session->logUserIn($loginUser->getId());
                    header('Location: ' . PUB_PATH_CAT);
                } else {
                    $loginFailed = true;
                }
            }
        }

        if ($loginFailed) {
            if ($failedLogins['ip'] > 30) {
                $db->saveArray(
                    'login_attempt',
                    ['id' => uid(), 'username' => '', 'ip' => USER_IP, 'time' => time()]
                );
            } else {
                $db->saveArray(
                    'login_attempt',
                    ['id' => uid(), 'username' => $_POST['username'], 'ip' => USER_IP, 'time' => time()]
                );
            }
        }
    }

    $loginPage->setPageTitleAddition('Log in');

    $pageContents = '<div id="content"><div>';
    $pageContents .= '<h1>Log in</h1>';
    if ($banned) {
        $pageContents .= '<p>Your IP is on the blacklist.</p>';
    } else {
        $pageContents .= '<p>No account? You can register one <a href="' . PUB_PATH_CAT . 'register">here</a>.</p>';
        $pageContents .= '<p>Have you forgotten your password? Reset your password <a href="' . PUB_PATH_CAT .
            'forgotpass">here</a>.</p>';
        $pageContents .= '<form class="labelForm" style="padding:5px 0 0 0;" action="' . PUB_PATH_CAT .
            'login" method="post" accept-charset="utf-8">';
        if ($loginFailed) {
            if ($lockedFor5Mins) {
                $pageContents .= '<div class="error">Too many failed login attempts, try again after 5 minutes.</div>';
            } elseif (!$csrfCheckResult) {
                $pageContents .= '<div class="error">CSRF prevention validation failed, please try again.</div>';
            } else {
                $pageContents .= '<div class="error">Incorrect username or password.</div>';
            }
        }
        $pageContents .= '<div><label>Username:</label>' .
            '<input type="text" autocomplete="off" name="username" style="width:300px;" value="' .
            (!empty($_POST['username']) ? Format::htmlEntities($_POST['username']) : '') . '" /></div>';
        $pageContents .= '<div><label>Password:</label>' .
            '<input type="password" name="password" style="width:300px;" /></div>';

        $pageContents .= '<input type="submit" value="Log in" />';
        $pageContents .= sprintf(
            '<input type="hidden" name="csrf_token" value="%s" />',
            CSRF::getToken('login')
        );
        $pageContents .= '</form>';
    }
    $pageContents .= '</div></div>';

    $loginPage->setHtml($pageContents);

    $response = new Response($loginPage);
    $response->output();
}
