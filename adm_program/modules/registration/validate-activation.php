<?php
require_once __DIR__ . '/../../../adm_program/system/common.php';

require_once __DIR__ . '/engine/bootstrap.php';

$userId = trim($_POST['uid']);
$action = trim($_POST['_action']);
header('Content-type: application/json');

$pageContent = <<<HTML
<form name="setNewPasswordForm" onsubmit="setNewPassword(event)" method="post">
    <div class="row form-group admidio-form-group-required">
        <label for="new_password" class="col-sm-3 control-label">New Password</label>
        <div class="col-sm-9">
            <input name="new_password" id="new_password" class="form-control" minlength="3" required type="password" />
            <p class="form-ctrl__response">Minimum 8 characters: Letters, numbers and/ or symbols</p>
            <div id="admidio-password-strength" class="progress form-control-small">
                <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%;"></div>
                <div id="admidio-password-strength-minimum" style="margin-left: calc(74.75px);"></div>
            </div>
        </div>
    </div>
    <div class="row form-group admidio-form-group-required">
        <label for="confirm_password" class="col-sm-3 control-label">Password Confirmation</label>
        <div class="col-sm-9">
            <input name="confirm_password" id="confirm_password" onblur="validateConfirmedPassword(event)" class="form-control" minlength="3" required type="password" />
            <p class="form-ctrl__response"></p>
        </div>
    </div>
    <div class="form__footer">
        <button class="btn btn-default  btn-primary" id="btn_submit" name="btn_submit" type="submit">
            Set Password and Login
        </button>
    </div>
</form>
HTML;
if ($action == 'get_user') {
    $token = trim($_POST['token']);

    $user = $GLOBALS['gDb']->query("SELECT usr_login_name, usr_activation_code
                                FROM adm_users
                                WHERE usr_id = {$userId} ");

    $user = $user->fetch();

    if ($user) {
        if (md5($user['usr_activation_code']) == $token) {
            echo json_encode([
                'status' => 'SUCCESS',
                'msg' => 'User found with given id and matched token',
                'data' => $pageContent,
            ]);
        } else {
            echo json_encode([
                'status' => 'ERROR',
                'msg' => 'The page you are looking is no longer valid.',
            ]);
        }
    }

} else if ($action == 'validate_activation_code') {
    global $gPasswordHashAlgorithm;
    $cost = 10;

    $fetchEmail = $GLOBALS['gDb']->query("SELECT usr_login_name
                                FROM adm_users
                                WHERE usr_id = {$userId} ");

    $userEmail = $fetchEmail->fetch();
    $email = $userEmail['usr_login_name'];

    $htmlContent = <<<HTML
<div class="reponse-container" style="padding: 20px; padding: 20px; background: #ECEFF1;">
    <h1 style="background: unset; border: none; padding: 0;margin: 0; font-size: 30px; "> Welcome to Cantab NYC!</h1>
    <hr>
    <p style="font-size: 18px;">
    A new account for {$email} is all set up.
    </p>
    <button style="border-radius: 0; color: white;text-align: center !important;text-decoration: none;padding: 10px 15px;
    " class="btn btn-primary btn-lg" role="button" id="proceedToLoginBtn" onclick="window.top.location.href='https://www.cantabnyc.org/p/member-pages.html'" data-href="https://www.cantabnyc.org/p/member-pages.html">
    Click Here to Proceed
    </button>
</div>

HTML;

    $password = $_POST['password'];

    $newPasswordHash = PasswordHashing::hash($password, $gPasswordHashAlgorithm, array('cost' => $cost));

    $user = $GLOBALS['gDb']->query("UPDATE adm_users
                                SET usr_password = '{$newPasswordHash}'
                                WHERE usr_id = {$userId} ");
// create user object
    $userObj = new User($GLOBALS['gDb'], $GLOBALS['gProfileFields'], (int) $userId);
    $checkLoginReturn = $userObj->checkLogin($password, true, true);
    $_SESSION['login_forward_url'] = ADMIDIO_URL . '/' . $gPreferences['homepage_login'];
    $gCurrentSession = $_SESSION['gCurrentSession'];
    $gCurrentSession->setValue('ses_usr_id', $userId);
    $gCurrentSession->setAutoLogin();
    if ($gCurrentSession->isValidLogin((int) $userId)) {
        $gValidLogin = true;
    }
    // $$gCurrentSession
    $gCurrentSession->refreshAutoLogin();
    $autoLogin = new AutoLogin($GLOBALS['gDb'], $gCurrentSession->getValue('ses_session_id'));

    $gCurrentUser = new User($GLOBALS['gDb'], $GLOBALS['gProfileFields'], $gCurrentSession->getValue('ses_usr_id'));
    $gCurrentSession->addObject('gCurrentUser', $gCurrentUser);

    if ($user) {
        echo json_encode([
            'status' => 'SUCCESS',
            'msg' => $htmlContent,
            'data' => $user,
        ]);
    }
}
