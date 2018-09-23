<?php
/**
 ***********************************************************************************************
 * Import users from a csv file
 *
 * @copyright 2004-2017 The Admidio Team
 * @see https://www.admidio.org/
 * @license https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License v2.0 only
 ***********************************************************************************************
 */
require_once('../../system/common.php');
require_once('../../system/login_valid.php');

$_SESSION['import_csv_request'] = $_POST;

// setzt die Ausfuehrungszeit des Scripts auf 8 Min., falls viele Daten importiert werden
// allerdings darf hier keine Fehlermeldung wg. dem safe_mode kommen
@set_time_limit(500);

// create readable constants for user import mode
define('USER_IMPORT_NOT_EDIT', '1');
define('USER_IMPORT_DUPLICATE', '2');
define('USER_IMPORT_DISPLACE', '3');
define('USER_IMPORT_COMPLETE', '4');

// nur berechtigte User duerfen User importieren
if(!$gCurrentUser->editUsers())
{
    $gMessage->show($gL10n->get('SYS_NO_RIGHTS'));
    // => EXIT
}

// Lastname und firstname are mandatory fields
if(strlen($_POST['usf-'.$gProfileFields->getProperty('LAST_NAME', 'usf_id')]) === 0)
{
    $gMessage->show($gL10n->get('SYS_FIELD_EMPTY', $gProfileFields->getProperty('LAST_NAME', 'usf_name')));
    // => EXIT
}
if(strlen($_POST['usf-'.$gProfileFields->getProperty('FIRST_NAME', 'usf_id')]) === 0)
{
    $gMessage->show($gL10n->get('SYS_FIELD_EMPTY', $gProfileFields->getProperty('FIRST_NAME', 'usf_name')));
    // => EXIT
}

if(array_key_exists('first_row', $_POST))
{
    $firstRowTitle = true;
}
else
{
    $firstRowTitle = false;
}

// jede Zeile aus der Datei einzeln durchgehen und den Benutzer in der DB anlegen
$line = reset($_SESSION['file_lines']);
$user = new User($gDb, $gProfileFields);
$startRow = 0;
$countImportNewUser  = 0;
$countImportEditUser = 0;
$countImportEditRole = 0;
$importedFields = array();
$depRoles = array();

// Abhängige Rollen ermitteln
$depRoles = RoleDependency::getParentRoles($gDb, $_SESSION['rol_id']);

if($firstRowTitle)
{
    // erste Zeile ueberspringen, da hier die Spaltenbezeichnungen stehen
    $line = next($_SESSION['file_lines']);
    $startRow = 1;
}
// Logging first
$gLogger->info('BEGIN: Importing users from the sheet');
for($i = $startRow, $iMax = count($_SESSION['file_lines']); $i < $iMax; ++$i)
{
    $user->clear();
    $columnArray = explode($_SESSION['value_separator'], $line);
    $emailFromCol = null;
    foreach($columnArray as $columnKey => $columnValue)
    {
        $thisIsNew = false;
        // Hochkomma und Spaces entfernen
        $columnValue = trim(strip_tags(str_replace('"', '', $columnValue)));
        $columnValueToLower = admStrToLower($columnValue);

        // nun alle Userfelder durchgehen und schauen, bei welchem
        // die entsprechende Dateispalte ausgewaehlt wurde
        // dieser dann den Wert zuordnen
        foreach($gProfileFields->mProfileFields as $field)
        {
            if(strlen($_POST['usf-'. $field->getValue('usf_id')]) > 0 && $columnKey == $_POST['usf-'. $field->getValue('usf_id')])
            {
                // importiertes Feld merken
                if(!isset($importedFields[$field->getValue('usf_id')]))
                {
                    $importedFields[$field->getValue('usf_id')] = $field->getValue('usf_name_intern');
                }

                if($field->getValue('usf_name_intern') === 'COUNTRY')
                {
                    $user->setValue($field->getValue('usf_name_intern'), $gL10n->getCountryByName($columnValue));
                }
/*
                if($field->getValue('usf_name_intern') === 'COLLEGE' || $field->getValue('usf_name_intern') === 'SCHOOL')
                {
                    try {
                        $colleges = $field->getValue('usf_value_list');
                        $indexOfCollege = array_search($columnValue, $colleges, false);
                        $collegeName = '';
                        if(array_key_exists($indexOfCollege, $colleges)){
                            $collegeName = $colleges[$indexOfCollege];
                        } else {
                            $gLogger->info('ERROR: Unable to get college name.\n Given name: '.$columnValue);
                        }
                        $user->setValue($field->getValue('usf_name_intern'), $collegeName);
                    } catch(\Exception $e) {
                        $gLogger->info('ERROR: Unable to get college name.\n Given name: '.$columnValue);
                    }
                }
                else
                {
*/
                    switch ($field->getValue('usf_type'))
                    {
                        case 'CHECKBOX':
                            if($columnValueToLower === 'j'
                                || $columnValueToLower === admStrToLower($gL10n->get('SYS_YES'))
                                || $columnValueToLower === 'y'
                                || $columnValueToLower === 'yes'
                                || $columnValueToLower === '1')
                            {
                                $user->setValue($field->getValue('usf_name_intern'), '1');
                            }
                            if($columnValueToLower === 'n'
                                || $columnValueToLower === admStrToLower($gL10n->get('SYS_NO'))
                                || $columnValueToLower === 'no'
                                || $columnValueToLower === '0'
                                || $columnValue === '')
                            {
                                $user->setValue($field->getValue('usf_name_intern'), '0');
                            }
                            break;
                        case 'DROPDOWN':
                        case 'RADIO_BUTTON':
                            // save position of combobox
                            $arrListValues = $field->getValue('usf_value_list', 'text');
                            $position = 1;

                            foreach($arrListValues as $key => $value)
                            {
                                if(strcmp(admStrToLower($columnValue), admStrToLower(trim($arrListValues[$position]))) === 0)
                                {
                                    // if col_value is text than save position if text is equal to text of position
                                    $user->setValue($field->getValue('usf_name_intern'), $position);
                                }
                                elseif(is_numeric($columnValue) && !is_numeric($arrListValues[$position]) && $columnValue > 0 && $columnValue < 1000)
                                {
                                    // if col_value is numeric than save position if col_value is equal to position
                                    $user->setValue($field->getValue('usf_name_intern'), $columnValue);
                                }
                                ++$position;
                            }
                            break;
                        case 'EMAIL':
                            $columnValue = admStrToLower($columnValue);
                            if(strValidCharacters($columnValue, 'email'))
                            {
                                $user->setValue($field->getValue('usf_name_intern'), substr($columnValue, 0, 255));
                                $emailFromCol = $columnValue;
                            }
                            break;
                        case 'INTEGER':
                            // number could contain dot and comma
                            if(is_numeric(strtr($columnValue, ',.', '00')))
                            {
                                $user->setValue($field->getValue('usf_name_intern'), $columnValue);
                            }
                            break;
                        case 'TEXT':
                            $user->setValue($field->getValue('usf_name_intern'), substr($columnValue, 0, 50));
                            break;
                        default:
                            $user->setValue($field->getValue('usf_name_intern'), substr($columnValue, 0, 255));
                    }
                //}

            }
        }
    }

    // create new user only if firstname and lastname are filled
    if(strlen($user->getValue('LAST_NAME')) > 0 && strlen($user->getValue('FIRST_NAME')) > 0)
    {
        // search for existing user with same name and read user data
        $sql = 'SELECT MAX(usr_id) AS usr_id
                  FROM '.TBL_USERS.'
            INNER JOIN '.TBL_USER_DATA.' last_name
                    ON last_name.usd_usr_id = usr_id
                   AND last_name.usd_usf_id = '.  $gProfileFields->getProperty('LAST_NAME', 'usf_id'). '
                   AND last_name.usd_value  = '. $gDb->escapeString($user->getValue('LAST_NAME', 'database')). '
            INNER JOIN '.TBL_USER_DATA.' first_name
                    ON first_name.usd_usr_id = usr_id
                   AND first_name.usd_usf_id = '.  $gProfileFields->getProperty('FIRST_NAME', 'usf_id'). '
                   AND first_name.usd_value  = '. $gDb->escapeString($user->getValue('FIRST_NAME', 'database')). '
                 WHERE usr_valid = 1 ';
        $pdoStatement = $gDb->query($sql);
        $maxUserId = (int) $pdoStatement->fetchColumn();
        if($maxUserId > 0)
        {
            $duplicate_user = new User($gDb, $gProfileFields, $maxUserId);
            $gLogger->info('Found duplicate user from adm_users, adm_user_data.');
            $gLogger->info('Duplicate user: '.$duplicate_user->getValue('usr_login_name'));
        }

        if($maxUserId > 0)
        {
            if($_SESSION['user_import_mode'] == USER_IMPORT_DISPLACE)
            {
                // delete all user data of profile fields
                $duplicate_user->deleteUserFieldData();
                $gLogger->info('Deleted data from adm_user_data for exist user. '. $user->getValue('usr_login_name'));
            }

            if($_SESSION['user_import_mode'] == USER_IMPORT_COMPLETE
            || $_SESSION['user_import_mode'] == USER_IMPORT_DISPLACE)
            {
                // edit data of user, if user already exists
                foreach($importedFields as $key => $field_name_intern)
                {
                    if($duplicate_user->getValue($field_name_intern) != $user->getValue($field_name_intern))
                    {
                        if($gProfileFields->getProperty($field_name_intern, 'usf_type') === 'DATE')
                        {
                            // the date must be formated
                            $duplicate_user->setValue($field_name_intern, $user->getValue($field_name_intern, $gPreferences['system_date']));
                        }
                        elseif($field_name_intern === 'COUNTRY')
                        {
                            // we need the iso-code and not the name of the country
                            $duplicate_user->setValue($field_name_intern, $gL10n->getCountryByName($user->getValue($field_name_intern)));
                        }
                        elseif($gProfileFields->getProperty($field_name_intern, 'usf_type') === 'DROPDOWN'
                            || $gProfileFields->getProperty($field_name_intern, 'usf_type') === 'RADIO_BUTTON')
                        {
                            // get number and not value of entry
                            $duplicate_user->setValue($field_name_intern, $user->getValue($field_name_intern, 'database'));
                        }
                        else
                        {
                            $duplicate_user->setValue($field_name_intern, $user->getValue($field_name_intern));
                        }
                    }
                }
                $user = $duplicate_user;
            }
        }

        if($maxUserId === 0 || ($maxUserId > 0 && $_SESSION['user_import_mode'] > USER_IMPORT_NOT_EDIT))
        {
            // if user doesn't exists or should be duplicated then count as new user
            if($maxUserId === 0 || $_SESSION['user_import_mode'] == USER_IMPORT_DUPLICATE)
            {
                $thisIsNew = true;
                $gLogger->info('The new user is here. '. $user->getValue('usr_login_name'));
                ++$countImportNewUser;
            }
            // existing users count as edited if mode is displace or complete
            elseif($maxUserId > 0 && $user->columnsValueChanged())
            {
                $gLogger->info('Updated existed user. '. $user->getValue('usr_login_name'));
                ++$countImportEditUser;
            }

            // save user data
            $user->save();


            // assign role membership to user
            if($user->setRoleMembership($_SESSION['rol_id']))
            {
                ++$countImportEditRole;
            }

            // assign dependent role memberships to user
            foreach($depRoles as $depRole)
            {
                $user->setRoleMembership($depRole);
            }

        }
    }
    // change to invalid
    $user->setValue('usr_valid', '1');
    $uapp_usr_id = $user->getValue('usr_id');

    // Store in User_Applications : Starts here

    // for registration
    if($thisIsNew){

        try {
            $user->setValue('usr_login_name', substr($emailFromCol, 0, 255));
        } catch(\Exception $e) {
            $gLogger->info('Took from sheet and added in adm_users as usr_login_name = '.$e->getMessage());
        }

        $applicationType = 'member';
        $applicationSql = " INSERT INTO " . TBL_APPLICATIONS . "
                        (uapp_usr_id, application_type, message)
                        VALUES (
                            $uapp_usr_id,
                            '$applicationType',
                            '$message'
                        ) ";
        $gLogger->info('Inserting in adm_applications tbl. '. $applicationSql);
        try {
            $privateDataSaved = $gDb->query($applicationSql);
        } catch(\Exception $e) {
            $gLogger->info('Failed! Inserting in adm_applications tbl. EXCEPTION: '. $e->getMessage());
        }

        $reg_org_id = 1;
        $regSql = " INSERT INTO " . TBL_REGISTRATIONS . "
                        (reg_org_id, reg_usr_id)
                        VALUES (
                            '$reg_org_id',
                            '$uapp_usr_id'
                        ) ";
        $gLogger->info('Inserting in adm_registrations tbl. '. $regSql);
        try {
            $registered = $gDb->query($regSql);
        } catch(\Exception $e) {
            $gLogger->info('Failed! Inserting in adm_registrations tbl. EXCEPTION: '. $e->getMessage());
        }

        if(isset($_POST['move_to_new_reg']) && $_POST['move_to_new_reg'] == '1') {

            $UpdateLoginQuery = "UPDATE adm_users SET usr_login_name = '".substr($emailFromCol, 0, 255)."' WHERE usr_id = $uapp_usr_id";
            try {
                $gDb->query($UpdateLoginQuery);
            } catch(\Exception $e) {
                $gLogger->info('Failed! unable to update username of new registrations. EXCEPTION: '. $e->getMessage());
            }

            $gLogger->info('Updating username to email for new registrations. '. $UpdateLoginQuery);


            $toMoveQuery = "UPDATE adm_users SET usr_valid = 0 WHERE usr_id = $uapp_usr_id";
            try {
                $gDb->query($toMoveQuery);
            } catch(\Exception $e) {
                $gLogger->info('Failed! unable to move to new registrations. EXCEPTION: '. $e->getMessage());
            }

            $gLogger->info('Changing user\'s flag to 0 for new registrations. '. $toMoveQuery);
        }
    }

    // Store in User_Applications : Ends here
    $line = next($_SESSION['file_lines']);
}

// initialize session parameters
$_SESSION['role']             = '';
$_SESSION['user_import_mode'] = '';
$_SESSION['file_lines']       = '';
$_SESSION['value_separator']  = '';

$gMessage->setForwardUrl(ADMIDIO_URL.FOLDER_MODULES.'/members/members.php');
$countMovedToNewReg = $countImportNewUser;
$gMessage->show($gL10n->get('MEM_IMPORT_SUCCESSFUL', $countImportNewUser, $countImportEditUser, $countImportEditRole).'<br> '.$countMovedToNewReg.' users have been moved to \'New registrations\'');

// => EXIT
