<?php
/**
 ***********************************************************************************************
 * List of all modules and administration pages of Admidio
 *
 * @copyright 2004-2017 The Admidio Team
 * @see https://www.admidio.org/
 * @license https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License v2.0 only
 ***********************************************************************************************
 */

// if config file doesn't exists, than show installation dialog
if(!is_file('../adm_my_files/config.php'))
{
    header('Location: installation/index.php');
    exit();
}

require_once('system/common.php');

$headline = 'Member Pages '.$gL10n->get('SYS_OVERVIEW');

// Navigation of the module starts here
$gNavigation->addStartUrl(CURRENT_URL, $headline);

// create html page object
$page = new HtmlPage($headline);

$menu = new TableMenu($gDb);
$menuSections = array();
subMenu($menuSections, 1, (int) $menu->getValue('men_id'));

// main menu of the page
$mainMenu = $page->getMenu();

if($gValidLogin)
{
    // show link to own profile
    $mainMenu->addItem('adm_menu_item_my_profile', ADMIDIO_URL . FOLDER_MODULES . '/profile/profile.php',
                       $gL10n->get('PRO_MY_PROFILE'), 'profile.png');
    // show logout link
    $mainMenu->addItem('adm_menu_item_logout', ADMIDIO_URL . '/adm_program/system/logout.php',
                       $gL10n->get('SYS_LOGOUT'), 'door_in.png');
}
else
{
    // show login link
    $mainMenu->addItem('adm_menu_item_login', ADMIDIO_URL . '/adm_program/system/login.php',
                       $gL10n->get('SYS_LOGIN'), 'key.png');
}

// menu with links to all modules of Admidio
$moduleMenu = new Menu('index_modules', $gL10n->get('SYS_MODULES'));
$getModuleMenuQuery = "SELECT men_name, men_description, men_id, men_name_intern, men_icon, men_url
                FROM ".TBL_MENU."
                WHERE men_men_id_parent = 1
                ORDER BY men_order";
$getModuleMenusResult = $gDb->query($getModuleMenuQuery);
$getModuleMenus = $getModuleMenusResult->fetchAll();

foreach ($getModuleMenus as $key => $value) {
    if(
	isset($gPreferences['enable_'.$value['men_name_intern'].'_module']) &&
        (
            $gPreferences['enable_'.$value['men_name_intern'].'_module'] == 1 ||
            ($gPreferences['enable_'.$value['men_name_intern'].'_module'] == 2 && $gValidLogin)
        )
    ) {
        $moduleMenu->addItem($value['men_name_intern'], $value['men_url'],
                            trim($gL10n->get($value['men_name']), '#'), '/icons/'.$value['men_icon'],
                            $gL10n->get($value['men_description']));
    }
}

$page->addHtml($moduleMenu->show(true));

// menu with links to all administration pages of Admidio if the user has the right to administrate
if($gCurrentUser->isAdministrator() || $gCurrentUser->manageRoles()
|| $gCurrentUser->approveUsers() || $gCurrentUser->editUsers())
{
    $adminMenu = new Menu('index_administration', $gL10n->get('SYS_ADMINISTRATION'));
    $getAdminMenuQuery = "SELECT men_name, men_description, men_id, men_name_intern, men_icon, men_url
                FROM ".TBL_MENU."
                WHERE men_men_id_parent = 2
                ORDER BY men_order";
    $getAdminMenusResult = $gDb->query($getAdminMenuQuery);
    $getAdminMenus = $getAdminMenusResult->fetchAll();
    foreach ($getAdminMenus as $key => $value) {
        $forOthers = false;
        $adminMenu->addItem($value['men_name_intern'], $value['men_url'],
                        trim($gL10n->get($value['men_name']), '#'), '/icons/'.$value['men_icon'],
                        trim($gL10n->get($value['men_description']), '#'));
    }
    // END Menu

    /*
    if($gCurrentUser->approveUsers() && $gPreferences['registration_mode'] > 0)
    {
        $adminMenu->addItem('newreg', FOLDER_MODULES . '/registration/registration.php',
                            $gL10n->get('NWU_NEW_REGISTRATIONS'), '/icons/new_registrations_big.png',
                            $gL10n->get('NWU_MANAGE_NEW_REGISTRATIONS_DESC'));
    }

    if($gCurrentUser->editUsers())
    {
        $adminMenu->addItem('usrmgt', FOLDER_MODULES . '/members/members.php',
                            $gL10n->get('MEM_USER_MANAGEMENT'), '/icons/user_administration_big.png',
                            $gL10n->get('MEM_USER_MANAGEMENT_DESC'));
    }

    if($gCurrentUser->manageRoles())
    {
        $adminMenu->addItem('roladm', FOLDER_MODULES . '/roles/roles.php',
                            $gL10n->get('ROL_ROLE_ADMINISTRATION'), '/icons/roles_big.png',
                            $gL10n->get('ROL_ROLE_ADMINISTRATION_DESC'));
    }

    if($gCurrentUser->isAdministrator())
    {
        $adminMenu->addItem('dbback', FOLDER_MODULES . '/backup/backup.php',
                            $gL10n->get('BAC_DATABASE_BACKUP'), '/icons/backup_big.png',
                            $gL10n->get('BAC_DATABASE_BACKUP_DESC'));

        $adminMenu->addItem('menu_mod', FOLDER_MODULES . '/menu/menu.php',
                            $gL10n->get('SYS_MENU'), '/icons/application_view_tile.png',
                            $gL10n->get('SYS_MENU_DESC'));

        $adminMenu->addItem('orgprop', FOLDER_MODULES . '/preferences/preferences.php',
                            $gL10n->get('SYS_SETTINGS'), '/icons/options_big.png',
                            $gL10n->get('ORG_ORGANIZATION_PROPERTIES_DESC'));
    }
     */


    $page->addHtml($adminMenu->show(true));
}

$page->show();
