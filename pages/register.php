<?php

declare(strict_types=1);

use MyLittleWallpaper\classes\Format;
use MyLittleWallpaper\classes\output\BasicPage;
use MyLittleWallpaper\classes\Password;
use MyLittleWallpaper\classes\Response;
use PHPMailer\PHPMailer\PHPMailer;

global $user, $db;

const ACTIVE_PAGE = 'register';
$ban = $db->getRecord('ban', ['field' => 'ip', 'value' => USER_IP]);
if (!empty($ban['ip']) && $ban['ip'] == USER_IP) {
    $banned = true;
} else {
    $banned = false;
}
$redirect = false;
$error    = false;

if (!$user->getIsAnonymous()) {
    header('Location: ' . PUB_PATH_CAT);
} else {
    $registerPage = new BasicPage();
    $registerPage->setPageTitleAddition('Register');

    if (isset($_POST['username']) && !$banned) {
        if (trim($_POST['username']) == '') {
            $error = 'Please give a username.';
        }
        if (filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) === false) {
            if ($error) {
                $error .= '<br />Incorrect email.';
            } else {
                $error = 'Incorrect email.';
            }
        }
        if (mb_strlen($_POST['password'], 'utf-8') < 6) {
            if ($error) {
                $error .= '<br />Password is too short.';
            } else {
                $error = 'Password is too short.';
            }
        } elseif (strcmp($_POST['password'], $_POST['password_confirm']) !== 0) {
            if ($error) {
                $error .= '<br />Password and its confirmation don\'t match.';
            } else {
                $error = 'Password and its confirmation don\'t match.';
            }
        }
        if (!$error) {
            $username_exists = $db->getRecord('user', ['field' => 'username', 'value' => $_POST['username']]);
            $email_exists    = $db->getRecord('user', ['field' => 'email', 'value' => $_POST['email']]);
            if (!empty($username_exists)) {
                $error = 'Given username is already in use.';
            }
            if (!empty($email_exists)) {
                if ($error) {
                    $error .= '<br />Given email is already in use.';
                } else {
                    $error = 'Given email is already in use.';
                }
            }
            if (!$error) {
                $forumspam = check_forumspam(USER_IP, $_POST['email']);
                if ($forumspam) {
                    $phpMailer           = new PHPMailer();
                    $phpMailer->From     = 'noreply@mylittlewallpaper.com';
                    $phpMailer->FromName = 'My Little Wallpaper';
                    $phpMailer->Body     = utf8_decode(
                        'Welcome to My Little Wallpaper.' . "\n\n" .
                        'You have opened an account to My Little Wallpaper with the following information:' . "\n\n" .
                        'Username: ' . trim($_POST['username']) . "\n" .
                        'Password: ' . $_POST['password'] . "\n\n" .
                        'To log in, just go to ' . PROTOCOL . SITE_DOMAIN . '/c/all/login' . "\n\n" .
                        'Best regards,' . "\n" .
                        'My Little Wallpaper Team'
                    );
                    $phpMailer->Subject  = 'My Little Wallpaper account';
                    $phpMailer->Encoding = 'quoted-printable';
                    $phpMailer->addAddress($_POST['email']);
                    $phpMailer->send();

                    $saveData = [
                        'username' => trim($_POST['username']),
                        'password' => Password::hashPassword($_POST['password']),
                        'email'    => $_POST['email'],
                    ];
                    $db->saveArray('user', $saveData);
                    $_SESSION['success'] = true;
                    $redirect            = true;
                    header('Location: ' . PUB_PATH_CAT . 'register');
                } else {
                    $error = 'Registration blocked, IP or email in blacklist.';
                }
            }
        }
    }

    $pageContents = '<div id="content"><div>';
    $pageContents .= '<h1>Register</h1>';
    if ($banned) {
        $pageContents .= '<p>Your IP is on the blacklist.</p>';
    } elseif (!empty($_SESSION['success'])) {
        $pageContents .= '<div class="success">Registeration successful, you can now log in.</div>';
    } else {
        $pageContents .= '<p>Please fill all the fields.</p>';
        $pageContents .= '<form class="labelForm" style="padding:5px 0 0 0;" action="' . PUB_PATH_CAT .
            'register" method="post" accept-charset="utf-8">';
        if ($error) {
            $pageContents .= '<div class="error">' . $error . '</div>';
        }
        $pageContents .= '<div><label>Username:</label>';
        $pageContents .= sprintf(
            '<input type="text" autocomplete="off" name="username" style="width:300px;" value="%s" /></div>',
            !empty($_POST['username']) ? Format::htmlEntities($_POST['username']) : ''
        );
        $pageContents .= '<div><label>Email:</label>';
        $pageContents .= sprintf(
            '<input type="text" autocomplete="off" name="email" style="width:300px;" value="%s" /></div>',
            !empty($_POST['email']) ? Format::htmlEntities($_POST['email']) : ''
        );
        $pageContents .= '<div><label style="float:left;padding-top:2px;">Password:<br />' .
            '<span style="font-size:11px;">At least 6 characters</span></label>' .
            '<input type="password" name="password" style="width:300px;" /><div style="clear:both;"></div></div>';
        $pageContents .= '<div><label>Confirm Password:</label>' .
            '<input type="password" name="password_confirm" style="width:300px;" /></div>';
        $pageContents .= '<br /><input type="submit" value="Register" />';
        $pageContents .= '</form>';
    }
    $pageContents .= '</div></div>';
    if (!$redirect && isset($_SESSION['success'])) {
        unset($_SESSION['success']);
    }

    $registerPage->setHtml($pageContents);

    $response = new Response($registerPage);
    $response->output();
}
