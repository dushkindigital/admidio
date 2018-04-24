<?php
namespace cantabnyc\install;

require_once (__DIR__ . '/../../../adm_program/system/common.php');

require_once (__DIR__ . '/../bootstrap.php');

/**
 * database - create category
 */
function db_category ( $db ) {
	
	$sql = "INSERT INTO " . \TBL_CATEGORIES . " (`cat_type`, `cat_name_intern`, `cat_name`, `cat_sequence`, `cat_usr_id_create`, `cat_timestamp_create`) VALUES ('USF', 'L4P_DB_DATA', 'L4P_DB_DATA', '100', '1', NOW() )";
	
	$db->query($sql, true);
	
	$result = $db->lastInsertId();
	
	return $result;
}

/**
 * database - create user fields
 */
function db_user_fields ( $db, $category_id ) {
	
	# membership
	$sql = "INSERT INTO " . \TBL_USER_FIELDS . " (`usf_cat_id`, `usf_type`, `usf_name_intern`, `usf_name`, `usf_description`, `usf_value_list`, `usf_sequence`, `usf_usr_id_create`, `usf_timestamp_create`) VALUES ('{$category_id}', 'DROPDOWN', 'L4P_DB_MEMBERSHIP', 'L4P_DB_MEMBERSHIP', '<a href=\"https://www.cantabnyc.org/p/membership_types.html\" target=\"_blank\">membership types</a>', 'Member\nAssociate', '100', '1', NOW() )";
	$db->query($sql, true);
	
	# school
	$sql = "INSERT INTO " . \TBL_USER_FIELDS . " (`usf_cat_id`, `usf_type`, `usf_name_intern`, `usf_name`, `usf_sequence`, `usf_usr_id_create`, `usf_timestamp_create`) VALUES ('{$category_id}', 'TEXT', 'L4P_DB_SCHOOL', 'L4P_DB_SCHOOL', '101', '1', NOW() )";
	$db->query($sql, true);
	
	# affiliation
	$sql = "INSERT INTO " . \TBL_USER_FIELDS . " (`usf_cat_id`, `usf_type`, `usf_name_intern`, `usf_name`, `usf_sequence`, `usf_usr_id_create`, `usf_timestamp_create`) VALUES ('{$category_id}', 'TEXT', 'L4P_DB_AFFILIATION', 'L4P_DB_AFFILIATION', '102', '1', NOW() )";
	$db->query($sql, true);
	
	# matriculation
	$sql = "INSERT INTO " . \TBL_USER_FIELDS . " (`usf_cat_id`, `usf_type`, `usf_name_intern`, `usf_name`, `usf_sequence`, `usf_usr_id_create`, `usf_timestamp_create`) VALUES ('{$category_id}', 'TEXT', 'L4P_DB_MATRICULATION', 'L4P_DB_MATRICULATION', '103', '1', NOW() )";
	$db->query($sql, true);
	
	# message
	$sql = "INSERT INTO " . \TBL_USER_FIELDS . " (`usf_cat_id`, `usf_type`, `usf_name_intern`, `usf_name`, `usf_sequence`, `usf_usr_id_create`, `usf_timestamp_create`) VALUES ('{$category_id}', 'TEXT', 'L4P_DB_MESSAGE', 'L4P_DB_MESSAGE', '104', '1', NOW() )";
	$db->query($sql, true);
	
	# email
	$sql = "UPDATE " . \TBL_USER_FIELDS . " SET `usf_description`='If you are applying as a Member, you must register with your school-issued cantab.net email ID. <a href=\"https://www.alumni.cam.ac.uk/benefits/email-for-life\" target=\"_blank\">more...</a>' WHERE `usf_name_intern`='EMAIL' AND `usf_name`='SYS_EMAIL'";
	$db->query($sql, true);
}

/**
 * install whats needed in the database
 */
function db_installer ($db) {
	
	#$category_id = db_category( $db );
	
	$category_id = '17';
	
	db_user_fields( $db, $category_id );
}

###
db_installer( $GLOBALS['gDb']);
