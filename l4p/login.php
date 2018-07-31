<?php
/**
 ***********************************************************************************************
 * Login page - tweaked
 *
 * @copyright 2004-2017 The Admidio Team
 * @see https://www.admidio.org/
 * @license https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License v2.0 only
 ***********************************************************************************************
 */
// @akshay: hiding some elements for the emebeded form in front-end: Starts here
$isEmbeded = false;
if(isset($_GET['is_embed']) && $_GET['is_embed'] == 'true')
{
    $isEmbeded = true;
}
// @akshay: hiding some elements for the emebeded form in front-end: Ends here
require_once (__DIR__ . '/../adm_program/system/common.php');

require_once (__DIR__ . '/engine/bootstrap.php');

$headline = $gL10n->get('SYS_LOGIN');

// remember url (will be removed in cookie_check)
$gNavigation->addUrl(CURRENT_URL, $headline);

// read id of administrator role
$sql = 'SELECT rol_id
          FROM '.TBL_ROLES.'
    INNER JOIN '.TBL_CATEGORIES.'
            ON cat_id = rol_cat_id
         WHERE rol_name LIKE \''.$gL10n->get('SYS_ADMINISTRATOR').'\'
           AND rol_administrator = 1
           AND (  cat_org_id = '. $gCurrentOrganization->getValue('org_id').'
               OR cat_org_id IS NULL )';
$pdoStatement = $gDb->query($sql);

// create role object for administrator
$roleAdministrator = new TableRoles($gDb, $pdoStatement->fetchColumn());

// create html page object
$page = new HtmlPage($headline);
if($isEmbeded)
{
    $embededFrameStyle = '
        <style>
            .navbar-menu, #header-block {
                display: none !important;
            }
        </style>
    ';
    $page->addHtml($embededFrameStyle);
}

// add back link to module menu
$loginMenu = $page->getMenu();
$loginMenu->addItem('menu_item_back', $gNavigation->getPreviousUrl(), $gL10n->get('SYS_BACK'), 'back.png');
// show form
$form = new HtmlForm('login_form', ADMIDIO_URL . '/l4p/login_check.php', $page, array('showRequiredFields' => false));

$form->addInput('usr_login_name', $gL10n->get('SYS_USERNAME'), null,
                array('maxLength' => 35, 'property' => FIELD_REQUIRED, 'class' => 'form-control-small'));
// TODO Future: 'minLength' => PASSWORD_MIN_LENGTH
$form->addInput('usr_password', $gL10n->get('SYS_PASSWORD'), null,
                array('type' => 'password', 'property' => FIELD_REQUIRED, 'class' => 'form-control-small'));

// show selectbox with all organizations of database
if($gPreferences['system_organization_select'] == 1)
{
    $sql = 'SELECT org_id, org_longname
              FROM '.TBL_ORGANIZATIONS.'
          ORDER BY org_longname ASC, org_shortname ASC';
    $form->addSelectBoxFromSql(
        'org_id', $gL10n->get('SYS_ORGANIZATION'), $gDb, $sql,
        array('property' => FIELD_REQUIRED, 'defaultValue' => $gCurrentOrganization->getValue('org_id'))
    );
}

if($gPreferences['enable_auto_login'] == 1)
{
    $form->addCheckbox('auto_login', $gL10n->get('SYS_REMEMBER_ME'), false);
}
$form->addSubmitButton('btn_login', $gL10n->get('SYS_LOGIN'), array('icon' => THEME_URL.'/icons/key.png'));
$page->addHtml($form->show(false));

if($gPreferences['registration_mode'] > 0)
{
    $page->addHtml('
        <div id="login_registration_link">
            <small>
                <a href="' . ADMIDIO_URL .'/l4p/component_membership_2.php">'.$gL10n->get('SYS_WANT_REGISTER').'</a>
            </small>
        </div>');
}

// Link bei Loginproblemen
if($gPreferences['enable_password_recovery'] == 1 && $gPreferences['enable_system_mails'] == 1)
{
    // neues Passwort zusenden
    $forgotPasswordLink = ADMIDIO_URL.'/adm_program/system/lost_password.php';
}
elseif($gPreferences['enable_mail_module'] == 1 && $roleAdministrator->getValue('rol_mail_this_role') == 3)
{
    // show link of message module to send mail to administrator role
    $forgotPasswordLink = ADMIDIO_URL.FOLDER_MODULES.'/messages/messages_write.php?rol_id='.$roleAdministrator->getValue('rol_id').'&amp;subject='.$gL10n->get('SYS_LOGIN_PROBLEMS');
}
else
{
    // show link to send mail with local mail-client to administrator
    $forgotPasswordLink = 'mailto:'.$gPreferences['email_administrator'].'?subject='.$gL10n->get('SYS_LOGIN_PROBLEMS');
}

$page->addHtml('
    <div id="login_forgot_password_link">
        <small><a href="'.$forgotPasswordLink.'">'.$gL10n->get('SYS_FORGOT_MY_PASSWORD').'</a></small>
    </div>
    <div id="login_admidio_link">
        <small>Powered by <a href="'.ADMIDIO_HOMEPAGE.'">Admidio</a></small>
    </div>');

$page->show();