<?php
namespace cantabnyc\install;

require_once (__DIR__ . '/../../../../adm_program/system/common.php');

require_once (__DIR__ . '/../bootstrap.php');

/**
 * database - create categories
 * @param object $db
 * @return array of the insert IDs
 */
function db_categories ( $db ) {

	$result = array();

	# application information
	$sql = "INSERT INTO " . \TBL_CATEGORIES . " (`cat_type`, `cat_name_intern`, `cat_name`, `cat_sequence`, `cat_usr_id_create`, `cat_timestamp_create`) VALUES ('USF', 'L4P_DB_CAT_APPLICATION_INFORMATION', 'L4P_DB_CAT_APPLICATION_INFORMATION', '102', '1', NOW() )";

	$db->query($sql, true);

	$result['L4P_DB_CAT_APPLICATION_INFORMATION'] = $db->lastInsertId();

	# professional information
	$sql = "INSERT INTO " . \TBL_CATEGORIES . " (`cat_type`, `cat_name_intern`, `cat_name`, `cat_sequence`, `cat_usr_id_create`, `cat_timestamp_create`) VALUES ('USF', 'L4P_DB_CAT_PROFESSIONAL_INFORMATION', 'L4P_DB_CAT_PROFESSIONAL_INFORMATION', '100', '1', NOW() )";

	$db->query($sql, true);

	$result['L4P_DB_CAT_PROFESSIONAL_INFORMATION'] = $db->lastInsertId();

	# school information
	$sql = "INSERT INTO " . \TBL_CATEGORIES . " (`cat_type`, `cat_name_intern`, `cat_name`, `cat_sequence`, `cat_usr_id_create`, `cat_timestamp_create`) VALUES ('USF', 'L4P_DB_CAT_SCHOOL_INFORMATION', 'L4P_DB_CAT_SCHOOL_INFORMATION', '101', '1', NOW() )";

	$db->query($sql, true);

	$result['L4P_DB_CAT_SCHOOL_INFORMATION'] = $db->lastInsertId();

	return $result;
}

/**
 * database - create user fields
 * @param object $db
 * @param array $list_category_id list of the insert IDs
 */
function db_user_fields ( $db, array $list_category_id ) {

	####################################
	# L4P_DB_CAT_APPLICATION_INFORMATION
	$category_id = $list_category_id['L4P_DB_CAT_APPLICATION_INFORMATION'];

	# membership type
	$sql = "INSERT INTO " . \TBL_USER_FIELDS . " (`usf_cat_id`, `usf_type`, `usf_name_intern`, `usf_name`, `usf_description`, `usf_value_list`, `usf_sequence`, `usf_usr_id_create`, `usf_timestamp_create`) VALUES ('{$category_id}', 'DROPDOWN', 'L4P_DB_MEMBERSHIP_TYPE', 'L4P_DB_MEMBERSHIP_TYPE', '<a href=\"https://www.cantabnyc.org/p/membership.html\" target=\"_blank\">membership types</a>', 'Member\nAssociate', '100', '1', NOW() )";
	$db->query($sql, true);

	# message
	$sql = "INSERT INTO " . \TBL_USER_FIELDS . " (`usf_cat_id`, `usf_type`, `usf_name_intern`, `usf_name`, `usf_sequence`, `usf_usr_id_create`, `usf_timestamp_create`) VALUES ('{$category_id}', 'TEXT_BIG', 'L4P_DB_MESSAGE', 'L4P_DB_MESSAGE', '102', '1', NOW() )";
	$db->query($sql, true);

	# references
	$sql = "INSERT INTO " . \TBL_USER_FIELDS . " (`usf_cat_id`, `usf_type`, `usf_name_intern`, `usf_name`, `usf_sequence`, `usf_usr_id_create`, `usf_timestamp_create`) VALUES ('{$category_id}', 'TEXT', 'L4P_DB_REFERENCES', 'L4P_DB_REFERENCES', '103', '1', NOW() )";
	$db->query($sql, true);

	# temp password for the accept email - NB not visible in forms
	$sql = "INSERT INTO " . \TBL_USER_FIELDS . " (`usf_cat_id`, `usf_type`, `usf_name_intern`, `usf_name`, `usf_sequence`, `usf_usr_id_create`, `usf_timestamp_create`, `usf_disabled`) VALUES ('{$category_id}', 'TEXT', 'L4P_DB_TEMP_PASSWORD', 'L4P_DB_TEMP_PASSWORD', '104', '1', NOW(), '1' )";
	$db->query($sql, true);

	# temp password changed - NB not visible in forms
	$sql = "INSERT INTO " . \TBL_USER_FIELDS . " (`usf_cat_id`, `usf_type`, `usf_name_intern`, `usf_name`, `usf_value_list`, `usf_sequence`, `usf_usr_id_create`, `usf_timestamp_create`, `usf_disabled`) VALUES ('{$category_id}', 'DROPDOWN', 'L4P_DB_TEMP_PASS_CHANGED', 'L4P_DB_TEMP_PASS_CHANGED', 'True\nFalse', '105', '1', NOW(), '1' )";
	$db->query($sql, true);

	# temp password expiry - NB not visible in forms
	$sql = "INSERT INTO " . \TBL_USER_FIELDS . " (`usf_cat_id`, `usf_type`, `usf_name_intern`, `usf_name`, `usf_sequence`, `usf_usr_id_create`, `usf_timestamp_create`, `usf_disabled`) VALUES ('{$category_id}', 'DATE', 'L4P_DB_TEMP_PASS_EXPIRATION', 'L4P_DB_TEMP_PASS_EXPIRATION', '106', '1', NOW(), '1' )";
	$db->query($sql, true);

	#####################################
	# L4P_DB_CAT_PROFESSIONAL_INFORMATION
	$category_id = $list_category_id['L4P_DB_CAT_PROFESSIONAL_INFORMATION'];

	# address
	$sql = "INSERT INTO " . \TBL_USER_FIELDS . " (`usf_cat_id`, `usf_type`, `usf_name_intern`, `usf_name`, `usf_sequence`, `usf_usr_id_create`, `usf_timestamp_create`) VALUES ('{$category_id}', 'TEXT', 'L4P_DB_ADDRESS_2', 'L4P_DB_ADDRESS_2', '103', '1', NOW() )";
	$db->query($sql, true);

	# email
	$sql = "INSERT INTO " . \TBL_USER_FIELDS . " (`usf_cat_id`, `usf_type`, `usf_name_intern`, `usf_name`, `usf_sequence`, `usf_usr_id_create`, `usf_timestamp_create`) VALUES ('{$category_id}', 'EMAIL', 'L4P_DB_EMAIL_2', 'L4P_DB_EMAIL_2', '106', '1', NOW() )";
	$db->query($sql, true);

	# employer
	$sql = "INSERT INTO " . \TBL_USER_FIELDS . " (`usf_cat_id`, `usf_type`, `usf_name_intern`, `usf_name`, `usf_sequence`, `usf_usr_id_create`, `usf_timestamp_create`) VALUES ('{$category_id}', 'TEXT', 'L4P_DB_EMPLOYER', 'L4P_DB_EMPLOYER', '102', '1', NOW() )";
	$db->query($sql, true);

	# phone
	$sql = "INSERT INTO " . \TBL_USER_FIELDS . " (`usf_cat_id`, `usf_type`, `usf_name_intern`, `usf_name`, `usf_sequence`, `usf_usr_id_create`, `usf_timestamp_create`) VALUES ('{$category_id}', 'PHONE', 'L4P_DB_PHONE_2', 'L4P_DB_PHONE_2', '105', '1', NOW() )";
	$db->query($sql, true);

	# position
	$sql = "INSERT INTO " . \TBL_USER_FIELDS . " (`usf_cat_id`, `usf_type`, `usf_name_intern`, `usf_name`, `usf_sequence`, `usf_usr_id_create`, `usf_timestamp_create`) VALUES ('{$category_id}', 'TEXT', 'L4P_DB_POSITION', 'L4P_DB_POSITION', '101', '1', NOW() )";
	$db->query($sql, true);

	# website
	$sql = "INSERT INTO " . \TBL_USER_FIELDS . " (`usf_cat_id`, `usf_type`, `usf_name_intern`, `usf_name`, `usf_sequence`, `usf_usr_id_create`, `usf_timestamp_create`) VALUES ('{$category_id}', 'URL', 'L4P_DB_WEBSITE_2', 'L4P_DB_WEBSITE_2', '104', '1', NOW() )";
	$db->query($sql, true);

	###############################
	# L4P_DB_CAT_SCHOOL_INFORMATION
	$category_id = $list_category_id['L4P_DB_CAT_SCHOOL_INFORMATION'];

	# class of
	$sql = "INSERT INTO " . \TBL_USER_FIELDS . " (`usf_cat_id`, `usf_type`, `usf_name_intern`, `usf_name`, `usf_sequence`, `usf_usr_id_create`, `usf_timestamp_create`) VALUES ('{$category_id}', 'TEXT', 'L4P_DB_CLASS_OF', 'L4P_DB_CLASS_OF', '105', '1', NOW() )";
	$db->query($sql, true);

	# degree
	$sql = "INSERT INTO " . \TBL_USER_FIELDS . " (`usf_cat_id`, `usf_type`, `usf_name_intern`, `usf_name`, `usf_sequence`, `usf_usr_id_create`, `usf_timestamp_create`) VALUES ('{$category_id}', 'TEXT', 'L4P_DB_DEGREE', 'L4P_DB_DEGREE', '103', '1', NOW() )";
	$db->query($sql, true);

	# matriculation year
	$sql = "INSERT INTO " . \TBL_USER_FIELDS . " (`usf_cat_id`, `usf_type`, `usf_name_intern`, `usf_name`, `usf_sequence`, `usf_usr_id_create`, `usf_timestamp_create`) VALUES ('{$category_id}', 'TEXT', 'L4P_DB_MATRICULATION_YEAR', 'L4P_DB_MATRICULATION_YEAR', '104', '1', NOW() )";
	$db->query($sql, true);

	# school
	#$school_value_list = \cantabnyc\get_configs()->colleges;
	#$school_value_list = \implode("\n", $school_value_list);
	#$school_value_list = \addslashes($school_value_list);
	#$sql = "INSERT INTO " . \TBL_USER_FIELDS . " (`usf_cat_id`, `usf_type`, `usf_name_intern`, `usf_name`, `usf_value_list`, `usf_sequence`, `usf_usr_id_create`, `usf_timestamp_create`) VALUES ('{$category_id}', 'DROPDOWN', 'L4P_DB_SCHOOL', 'L4P_DB_SCHOOL', '{$school_value_list}', '101', '1', NOW() )";
	$sql = "INSERT INTO " . \TBL_USER_FIELDS . " (`usf_cat_id`, `usf_type`, `usf_name_intern`, `usf_name`, `usf_value_list`, `usf_usr_id_create`, `usf_timestamp_create`) VALUES ('{$category_id}', 'TEXT', 'L4P_DB_SCHOOL', 'L4P_DB_SCHOOL', '101', '1', NOW() )";
	$db->query($sql, true);

	# subject read
	$sql = "INSERT INTO " . \TBL_USER_FIELDS . " (`usf_cat_id`, `usf_type`, `usf_name_intern`, `usf_name`, `usf_sequence`, `usf_usr_id_create`, `usf_timestamp_create`) VALUES ('{$category_id}', 'TEXT', 'L4P_DB_SUBJECT_READ', 'L4P_DB_SUBJECT_READ', '102', '1', NOW() )";
	$db->query($sql, true);

	################
	# standard email
	$sql = "UPDATE " . \TBL_USER_FIELDS . " SET `usf_description`='If you are applying as a Member, you must register with your school-issued cantab.net email ID. <a href=\"https://www.alumni.cam.ac.uk/benefits/email-for-life\" target=\"_blank\">more...</a>' WHERE `usf_name_intern`='EMAIL' AND `usf_name`='SYS_EMAIL'";
	$db->query($sql, true);

}

/**
 * install whats needed in the database
 */
function db_installer ($db) {

	$list_category_id = db_categories( $db );

	db_user_fields( $db, $list_category_id );
}

###
db_installer( $GLOBALS['gDb']);
