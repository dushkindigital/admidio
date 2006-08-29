<?php
/******************************************************************************
 * Verschiedene Funktionen fuer das Profil
 *
 * Copyright    : (c) 2004 - 2006 The Admidio Team
 * Homepage     : http://www.admidio.org
 * Module-Owner : Markus Fassbender
 *
 * Uebergaben:
 *
 * user_id : ID des Benutzers, der bearbeitet werden soll
 * url :     URL auf die danach weitergeleitet wird
 *
 ******************************************************************************
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 *
 *****************************************************************************/

require("../../system/common.php");
require("../../system/login_valid.php");
require("../../system/email_class.php");

// Uebergabevariablen pruefen

if(isset($_GET["user_id"]) && is_numeric($_GET["user_id"]) == false)
{
    $g_message->show("invalid");
}

$user_id  = $_GET['user_id'];
$err_code = "";
$err_text = "";

if(!isset($_POST['login']))
{
    $_POST['login'] = "";
}
   
// wenn URL uebergeben wurde zu dieser gehen, ansonsten zurueck
if(array_key_exists('url', $_GET))
{
    $url = urlencode($_GET['url']);
}
else
{
    $url = "";
}   

/*------------------------------------------------------------*/
// prueft, ob der User die notwendigen Rechte hat, das entsprechende Profil zu aendern
/*------------------------------------------------------------*/
if(!editUser() && $user_id != $g_current_user->id)
{
    $g_message->show("norights");
}

$user = new User($g_adm_con);

if($user_id > 0)
{
    // Userdaten aus Datenbank holen
    $user->getUser($user_id);
    
    if($user->valid == 1)
    {
        // keine Webanmeldung, dann schauen, ob User überhaupt Mitglied in der Gliedgemeinschaft ist
        if(isMember($user_id) == false)
        {
            $g_message->show("norolle");
        }
    }
}

// Feldinhalte saeubern und der User-Klasse zuordnen
$user->last_name  = strStripTags($_POST['last_name']);
$user->first_name = strStripTags($_POST['first_name']);
$user->login_name = strStripTags($_POST['login_name']);
$user->address    = strStripTags($_POST['address']);
$user->zip_code   = strStripTags($_POST['zip_code']);
$user->city       = strStripTags($_POST['city']);
$user->country    = strStripTags($_POST['country']);
$user->phone      = strStripTags($_POST['phone']);
$user->mobile     = strStripTags($_POST['mobile']);
$user->fax        = strStripTags($_POST['fax']);
$user->email      = strStripTags($_POST['email']);
$user->homepage   = strStripTags($_POST['homepage']);
$user->birthday   = strStripTags($_POST['birthday']);
$user->gender     = $_POST['gender'];
if($user->gender != 1 && $user->gender != 2)
{
    // falls das Geschlecht nicht angegeben wurde, dann neutralen Wert eintragen
    $user->gender = 0;
}

/*------------------------------------------------------------*/
// Felder prüfen
/*------------------------------------------------------------*/
if(strlen($user->last_name) > 0)
{
    if(strlen($user->first_name) > 0)
    {
        if(strlen($user->email) != 0)
        {
            if(!isValidEmailAddress($user->email))
            {
                $err_code = "email_invalid";
            }
        }

        if(strlen($user->login_name) > 0)
        {
            // pruefen, ob der Benutzername bereits vergeben ist
            $sql = "SELECT usr_id FROM ". TBL_USERS. "
                     WHERE usr_login_name = {0} ";
            $sql    = prepareSQL($sql, array($user->login_name));
            $result = mysql_query($sql, $g_adm_con);
            db_error($result);

            if(mysql_num_rows($result) > 0)
            {
                $row = mysql_fetch_array($result);

                if(strcmp($row[0], $user_id) != 0)
                {
                    $err_code = "login_name";
                }
            }
        }

        if(strlen($user->birthday) > 0)
        {
            if(!dtCheckDate($user->birthday))
            {
                $err_code = "datum";
                $err_text = "Geburtstag";
            }
        }

        // Feldinhalt der gruppierungsspezifischen Felder pruefen
        $sql = "SELECT usf_name, usf_type
                  FROM ". TBL_USER_FIELDS. "
                 WHERE usf_org_shortname  = '$g_organization' ";
        if(!isModerator())
        {
            $sql = $sql. " AND usf_locked = 0 ";
        }
        $result_msg = mysql_query($sql, $g_adm_con);
        db_error($result_msg);

        while($row = mysql_fetch_object($result_msg))
        {
            // ein neuer Wert vorhanden
            if(strlen($_POST[urlencode($row->usf_name)]) > 0)
            {
                if($row->usf_type == "NUMERIC"
                && !is_numeric($_POST[urlencode($row->usf_name)]))
                {
                    $err_code = "field_numeric";
                    $err_text = $row->usf_name;
                }
            }
        }
    }
    else
    {
        $err_code = "feld";
        $err_text = "Vorname";
    }
}
else
{
    $err_code = "feld";
    $err_text = "Name";
}

if(strlen($err_code) > 0)
{
    $g_message->show($err_code, $err_text);
}

// Geburtstag fuer die DB formatieren
if(strlen($user->birthday) > 0)
{
    $user->birthday = dtFormatDate($user->birthday, "Y-m-d");
}

/*------------------------------------------------------------*/
// Benutzerdaten in Datenbank schreiben
/*------------------------------------------------------------*/

if($user_id > 0)
{
    $ret_code = $user->update($g_current_user->id);
}
else
{
    $ret_code = $user->insert($g_current_user->id);
}

if($ret_code != 0)
{
    $g_message->show("mysql", $ret_code);
}

/*------------------------------------------------------------*/
// Messenger-Daten und gruppierungsspezifische Felder anlegen / updaten
/*------------------------------------------------------------*/

$sql = "SELECT usf_id, usf_name, usd_id, usd_value
          FROM ". TBL_USER_FIELDS. " LEFT JOIN ". TBL_USER_DATA. "
            ON usd_usf_id = usf_id
           AND usd_usr_id         = {0}
         WHERE (  usf_org_shortname IS NULL
               OR usf_org_shortname  = '$g_organization' ) ";
if(!isModerator())
{
    $sql = $sql. " AND usf_locked = 0 ";
}
$sql = prepareSQL($sql, array($user->id));
$result_msg = mysql_query($sql, $g_adm_con);
db_error($result_msg);

while($row = mysql_fetch_object($result_msg))
{
    if(is_null($row->usd_value))
    {
        // noch kein Wert vorhanden -> neu einfuegen
        if(strlen(trim($_POST[urlencode($row->usf_name)])) > 0)
        {
            $sql = "INSERT INTO ". TBL_USER_DATA. " (usd_usr_id, usd_usf_id, usd_value)
                                             VALUES ({0}, $row->usf_id, '". $_POST[urlencode($row->usf_name)]. "') ";
            $sql = prepareSQL($sql, array($user->id));
            $result = mysql_query($sql, $g_adm_con);
            db_error($result);
        }
    }
    else
    {
        // auch ein neuer Wert vorhanden
        if(strlen(trim($_POST[urlencode($row->usf_name)])) > 0)
        {
            if($_POST[urlencode($row->usf_name)] != $row->usd_value)
            {
                $sql = "UPDATE ". TBL_USER_DATA. " SET usd_value = {0}
                         WHERE usd_id = $row->usd_id ";
                $sql = prepareSQL($sql, array($_POST[urlencode($row->usf_name)]));
                $result = mysql_query($sql, $g_adm_con);
                db_error($result);
            }
        }
        else
        {
            $sql = "DELETE FROM ". TBL_USER_DATA. "
                     WHERE usd_id = $row->usd_id ";
            $result = mysql_query($sql, $g_adm_con);
            db_error($result);
        }
    }
}

if($user->valid == 0)
{
    /*------------------------------------------------------------*/
    // neuer Benutzer wurde ueber Webanmeldung angelegt
    /*------------------------------------------------------------*/
    if($g_forum == 1)
    {
        mysql_select_db($g_forum_db, $g_forum_con);

        // jetzt noch den neuen User ins Forum eintragen
        $sql    = "SELECT MAX(user_id) as anzahl 
                     FROM ". $g_forum_praefix. "_users";
        $result = mysql_query($sql, $g_forum_con);
        db_error($result);
        $row    = mysql_fetch_array($result);
        $new_user_id = $row[0] + 1;

        $sql    = "INSERT INTO ". $g_forum_praefix. "_users
                               (user_id, username, user_password, user_regdate, user_timezone,
                                user_style, user_lang, user_viewemail, user_attachsig, user_allowhtml,
                                user_dateformat, user_email, user_notify, user_notify_pm, user_popup_pm,
                                user_avatar)
                        VALUES ($user->id, '$user->login_name', '$user->password', ". time(). ", 1.00,
                                2, 'german', 0, 1, 0,
                                'd.m.Y, H:i', '$user->email', 0, 1, 1,
                                '') ";
        $result = mysql_query($sql, $g_forum_con);
        db_error($result);

        mysql_select_db($g_adm_db, $g_adm_con);
    }

    // User auf aktiv setzen
    $user->valid = 1;
    $user->reg_org_shortname = "";
    $user->update($g_current_user->id);

    // nur ausfuehren, wenn E-Mails auch unterstuetzt werden
    if($g_preferences['send_email_extern'] != 1)
    {
        // Mail an den User schicken, um die Anmeldung zu bestaetigen
        $email = new Email();
        $email->setSender($g_preferences['email_administrator']);
        $email->addRecipient($user->email, "$user->first_name $user->last_name");
        $email->setSubject("Anmeldung auf $g_current_organization->homepage");
        $email->setText(utf8_decode("Hallo "). $user->first_name. utf8_decode(",\n\ndeine Anmeldung auf "). 
            $g_current_organization->homepage. utf8_decode("wurde bestätigt.\n\nNun kannst du dich mit deinem Benutzernamen : ").
            $user->login_name. utf8_decode("\nund dem Passwort auf der Homepage einloggen.\n\n".
            "Sollten noch Fragen bestehen, schreib eine E-Mail an "). $g_preferences['email_administrator'].
            utf8_decode(" .\n\nViele Grüße\nDie Webmaster"));
        $email->sendEmail();
    }

    // neuer User -> Rollen zuordnen
    $location = "Location: roles.php?user_id=$user->id&new_user=1&url=$url";
    header($location);
    exit();
}

/*------------------------------------------------------------*/
// auf die richtige Seite weiterleiten
/*------------------------------------------------------------*/

if($user_id == 0)
{
    // neuer User -> Rollen zuordnen
    $location = "Location: roles.php?user_id=$user->id&new_user=1&url=$url";
}
else
{
    // zur Profilseite zurueckkehren und die URL, von der die Profilseite aufgerufen wurde uebergeben
    $g_message->setForwardUrl("$g_root_path/adm_program/modules/profile/profile.php?user_id=$user_id&url=$url", 2000);
    $g_message->show("save");
}
header($location);
exit();
?>