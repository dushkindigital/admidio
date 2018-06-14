<?php
/**
 ***********************************************************************************************
 * Change password
 *
 * @copyright 2004-2017 The Admidio Team
 * @see https://www.admidio.org/
 * @license https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License v2.0 only
 *
 * Parameters:
 *
 * usr_id           : Id of the user whose password should be changed
 * mode    - html   : Default mode to show a html form to change the password
 *           change : Change password in database
 ***********************************************************************************************
 */
require_once (__DIR__ . '/../../../adm_program/system/common.php');
require_once (__DIR__ . '/../adm_program/system/login_valid.php');

require_once (__DIR__ . '/engine/bootstrap.php');

// Initialize and check the parameters
$getUserId = admFuncVariableIsValid($_GET, 'usr_id', 'int',    array('requireValue' => true));
$getMode   = admFuncVariableIsValid($_GET, 'mode',   'string', array('defaultValue' => 'html', 'validValues' => array('html', 'change')));

$user = new User($gDb, $gProfileFields, $getUserId);
$currUserId = (int) $gCurrentUser->getValue('usr_id');

// only the own password could be individual set.
// Administrator could only send a generated password or set a password if no password was set before
if((int) $gCurrentUser->getValue('usr_id') !== $getUserId
&& (!isMember($getUserId)
|| (!$gCurrentUser->isAdministrator() && $currUserId !== $getUserId)
|| ($gCurrentUser->isAdministrator() && $user->getValue('EMAIL') !== '' && $gPreferences['enable_system_mails'] == 1)))
{
    $gMessage->show($gL10n->get('SYS_NO_RIGHTS'));
    // => EXIT
}

if($getMode === 'change')
{
    $gMessage->showHtmlTextOnly(true);
}

$phrase = '';

if($getMode === 'change')
{
    if($gCurrentUser->isAdministrator() && $currUserId !== $getUserId)
    {
        $oldPassword = '';
    }
    else
    {
        $oldPassword = $_POST['old_password'];
    }

    $newPassword        = $_POST['new_password'];
    $newPasswordConfirm = $_POST['new_password_confirm'];

    /***********************************************************************/
    /* Handle form input */
    /***********************************************************************/
    if(($oldPassword !== '' || $gCurrentUser->isAdministrator())
    &&  $newPassword !== '' && $newPasswordConfirm !== '')
    {
        if(strlen($newPassword) >= PASSWORD_MIN_LENGTH)
        {
            if (PasswordHashing::passwordStrength($newPassword, $user->getPasswordUserData()) >= $gPreferences['password_min_strength'])
            {
                if ($newPassword === $newPasswordConfirm)
                {
                    // check if old password is correct.
                    // Administrator could change password of other users without this verification.
                    if (PasswordHashing::verify($oldPassword, $user->getValue('usr_password'))
                    || ($gCurrentUser->isAdministrator() && $currUserId !== $getUserId))
                    {
                        $user->saveChangesWithoutRights();
                        $user->setPassword($newPassword);
                        $user->save();

                        # for some reason user->save does'nt save these values
                        $sql = 'UPDATE ' . \TBL_USER_DATA . ' SET usd_value=\'1\'          WHERE usd_usf_id = \'' . $GLOBALS['gProfileFields']->getProperty('L4P_DB_TEMP_PASS_CHANGED',    'usf_id') . '\' AND usd_usr_id=\'' . $user->getValue('usr_id') . '\' LIMIT 1';
                        $gDb->query( $sql );
                        $sql = 'UPDATE ' . \TBL_USER_DATA . ' SET usd_value=\'9999-01-02\' WHERE usd_usf_id = \'' . $GLOBALS['gProfileFields']->getProperty('L4P_DB_TEMP_PASS_EXPIRATION', 'usf_id') . '\' AND usd_usr_id=\'' . $user->getValue('usr_id') . '\' LIMIT 1';
                        $gDb->query( $sql );

                        // if password of current user changed, then update value in current session
                        if ($currUserId === (int) $user->getValue('usr_id'))
                        {
                            $gCurrentUser->setPassword($newPassword);
                        }

                        $phrase = 'success';
                    }
                    else
                    {
                        $phrase = $gL10n->get('PRO_PASSWORD_OLD_WRONG');
                    }
                }
                else
                {
                    $phrase = $gL10n->get('PRO_PASSWORDS_NOT_EQUAL');
                }
            }
            else
            {
                $phrase = $gL10n->get('PRO_PASSWORD_NOT_STRONG_ENOUGH');
            }
        }
        else
        {
            $phrase = $gL10n->get('PRO_PASSWORD_LENGTH');
        }
    }
    else
    {
        $phrase = $gL10n->get('SYS_FIELDS_EMPTY');
    }

    echo $phrase;
} else {

	/***********************************************************************/
	/* Show password form */
	/***********************************************************************/

	$zxcvbnUserInputs = json_encode($user->getPasswordUserData(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

	$passwordStrengthLevel = 1;
	if ($gPreferences['password_min_strength'])
	{
			$passwordStrengthLevel = $gPreferences['password_min_strength'];
	}

	$js_script = '<script type="text/javascript">
			$(function() {
					window.setTimeout(
						function () {
							$("#password_form:first *:input[type!=hidden]:first").focus();

							$("#admidio-password-strength-minimum").css("margin-left", "calc(" + $("#admidio-password-strength").css("width") + " / 4 * '.$passwordStrengthLevel.')");

							$("#new_password").keyup(function(e) {
									var result = zxcvbn(e.target.value, ' . $zxcvbnUserInputs . ');
									var cssClasses = ["progress-bar-danger", "progress-bar-danger", "progress-bar-warning", "progress-bar-info", "progress-bar-success"];

									var progressBar = $("#admidio-password-strength .progress-bar");
									progressBar.attr("aria-valuenow", result.score * 25);
									progressBar.css("width", result.score * 25 + "%");
									progressBar.removeClass(cssClasses.join(" "));
									progressBar.addClass(cssClasses[result.score]);
							});
						},
						2000
					);

					$("#password_form").submit(function(event) {
							var action = $(this).attr("action");
							var passwordFormAlert = $("#password_form .form-alert");
							passwordFormAlert.hide();

							// disable default form submit
							event.preventDefault();

							$.post(action, $(this).serialize(), function(data) {
									if (data === "success") {
											passwordFormAlert.attr("class", "alert alert-success form-alert");
											passwordFormAlert.html("<span class=\"glyphicon glyphicon-ok\"></span><strong>'.$gL10n->get('PRO_PASSWORD_CHANGED').'</strong>");
											passwordFormAlert.fadeIn("slow");
											setTimeout(function () {
													$("#admidio_modal").modal("hide");
											}, 2000);
									} else {
											passwordFormAlert.attr("class", "alert alert-danger form-alert");
											passwordFormAlert.fadeIn();
											passwordFormAlert.html("<span class=\"glyphicon glyphicon-exclamation-sign\"></span>"+data);
									}
							});
					});
			});
	</script>';

	// set headline of the script
	$headline = $gL10n->get('PRO_EDIT_PASSWORD');

	// create html page object
	$page = new HtmlPage($headline);
	$page->enableModal();

	// add back link to module menu
$profileEditMenu = $page->getMenu();
$profileEditMenu->addItem('menu_item_home', ADMIDIO_URL . '/adm_program/index.php', $gL10n->get('SYS_HOMEPAGE'), 'home.png');

	$page->addJavascriptFile('adm_program/libs/zxcvbn/dist/zxcvbn.js');

	$page->addHtml( $js_script );

	// show form
	$form = new HtmlForm('password_form', ADMIDIO_URL . '/adm_custom/password_change.php?usr_id='.$getUserId.'&amp;mode=change');

	$form->addHtml( "<p>Your password is no longer valid. Please choose a new password</p><br />" );

	if($currUserId === $getUserId)
	{
			// to change own password user must enter the valid old password for verification
			// TODO Future: 'minLength' => PASSWORD_MIN_LENGTH
			$form->addInput('old_password', $gL10n->get('PRO_CURRENT_PASSWORD'), null, array('type' => 'password', 'property' => FIELD_REQUIRED));
			$form->addLine();
	}
	$form->addInput(
			'new_password', $gL10n->get('PRO_NEW_PASSWORD'), null,
			array('type' => 'password', 'property' => FIELD_REQUIRED, 'minLength' => PASSWORD_MIN_LENGTH, 'passwordStrength' => true, 'passwordUserData' => $user->getPasswordUserData(), 'helpTextIdInline' => 'PRO_PASSWORD_DESCRIPTION')
	);
	$form->addInput('new_password_confirm', $gL10n->get('SYS_REPEAT'), null, array('type' => 'password', 'property' => FIELD_REQUIRED, 'minLength' => PASSWORD_MIN_LENGTH));
	$form->addSubmitButton('btn_save', $gL10n->get('SYS_SAVE'), array('icon' => THEME_URL.'/icons/disk.png', 'class' => ' col-sm-offset-3'));

	$page->addHtml( $form->show(false) );

	$page->show();
}
