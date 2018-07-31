
<?php
/**
 ***********************************************************************************************
 * Configuration file of Admidio
 *
 * @copyright 2004-2017 The Admidio Team
 * @see https://www.admidio.org/
 * @license https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License v2.0 only
 ***********************************************************************************************
 */

# debug mode on
$gDebug = 1;

// Select your database system for example 'mysql' or 'pgsql'
$gDbType = 'mysql';

// Table prefix for Admidio-Tables in database
// Example: 'adm'
$g_tbl_praefix = 'adm';
// Access to the database of the MySQL-Server
$g_adm_srv  = 's166-62-85-197.secureserver.net';      // Server
$g_adm_port = 3306;        // Port
$g_adm_usr  = 'admidiouser';        // User
$g_adm_pw   = 'Adm!d!0';    // Password
$g_adm_db   = 'admidio_dev';    // Database
// URL to this Admidio installation
// Example: 'https://www.admidio.org/example'
$g_root_path = 'https://members.cantabnyc.org';
if($_SERVER['SERVER_NAME'] == 'localhost'){
    // Access to the database of the MySQL-Server
    $g_adm_srv  = 'localhost';      // Server
    $g_adm_port = 3306;        // Port
    $g_adm_usr  = 'root';        // User
    $g_adm_pw   = 'root';    // Password
    $g_adm_db   = 'admidio_dev';    // Database
    $g_root_path = 'http://localhost:8888/cantabnyc-github';
}

// Short description of the organization that is running Admidio
// This short description must correspond to your input in the installation wizard !!!
// Example: 'ADMIDIO'
// Maximum of 10 characters !!!
$g_organization = 'CantabNYC';

// The name of the timezone in which your organization is located.
// This must be one of the strings that are defined here https://secure.php.net/manual/en/timezones.php
// Example: 'Europe/Berlin'
$gTimezone = 'America/New_York';

// If this flag is set = 1 then you must enter your loginname and password
// for an update of the Admidio database to a new version of Admidio.
// For a more comfortable and easy update you can set this preference = 0.
$gLoginForUpdate = 1;
